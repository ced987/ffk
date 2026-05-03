<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Competition extends Model
{
    protected $fillable = [
        'organizer_club_id',
        'name',
        'date_competition',
        'inscriptions_closed',
        'informations_complementaires',
    ];

    protected function casts(): array
    {
        return [
            'date_competition' => 'date',
            'inscriptions_closed' => 'boolean',
        ];
    }

    public function organizerClub(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'organizer_club_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function operationalRegistrations(): HasMany
    {
        return $this->hasMany(InscriptionOperationnelle::class);
    }

    public function poules(): HasMany
    {
        return $this->hasMany(Poule::class);
    }

    public function eligiblePouleRegistrations(): Collection
    {
        return $this->operationalRegistrations()
            ->with(['club', 'participantSource'])
            ->where('is_active', true)
            ->where('is_validated', true)
            ->whereNull('poule_id')
            ->get()
            ->sortBy(fn (InscriptionOperationnelle $registration) => sprintf(
                '%s %s',
                $registration->participantSource->last_name,
                $registration->participantSource->first_name,
            ))
            ->values();
    }

    public function pouleAssistantProposals(array $criteria = []): array
    {
        $criteria = array_merge([
            'same_sex_only' => true,
            'age_gap_max' => 2,
            'weight_gap_max' => 5,
            'target_size' => 4,
            'adult_access_age' => 18,
        ], $criteria);

        $availableRegistrations = $this->eligiblePouleRegistrations()
            ->sortBy(fn (InscriptionOperationnelle $registration) => sprintf(
                '%s-%03d-%06.2f-%06d',
                $criteria['same_sex_only'] ? $registration->participantSource->sex : 'mixte',
                $registration->participantSource->age,
                (float) $registration->participantSource->approximate_weight,
                $registration->id,
            ))
            ->values();

        $remaining = $availableRegistrations->values();
        $proposals = collect();
        $unassigned = collect();
        $proposalNumber = 1;

        $buckets = $remaining
            ->groupBy(fn (InscriptionOperationnelle $registration) => $this->pouleAssistantBucketKey($registration, $criteria))
            ->map(fn (Collection $bucket) => $bucket
                ->sortBy(fn (InscriptionOperationnelle $registration) => sprintf(
                    '%03d-%06.2f-%06d',
                    $registration->participantSource->age,
                    (float) $registration->participantSource->approximate_weight,
                    $registration->id,
                ))
                ->values());

        foreach ($buckets as $bucket) {
            foreach ($this->pouleAssistantBalancedGroups($bucket, (int) $criteria['target_size']) as $group) {
                if ($group->count() < 2) {
                    $unassigned->push([
                        'registration' => $group->first(),
                        'reason' => 'Aucun regroupement compatible trouvé',
                    ]);

                    continue;
                }

                $analysis = $this->pouleAssistantGroupAnalysis($group, $criteria);

                $proposals->push([
                    'name' => 'Poule proposée '.$proposalNumber,
                    'registrations' => $group->values(),
                    'justification' => $analysis['justification'],
                    'indicator' => $analysis['indicator'],
                    'score' => $analysis['score'],
                    'warning' => $analysis['warning'],
                ]);

                $proposalNumber++;
            }
        }

        return [
            'criteria' => $criteria,
            'proposals' => $proposals,
            'unassigned' => $unassigned,
        ];
    }

    private function pouleAssistantBucketKey(InscriptionOperationnelle $registration, array $criteria): string
    {
        $source = $registration->participantSource;
        $sexKey = $criteria['same_sex_only'] ? $source->sex : 'mixte';
        $adultAccessAge = (int) $criteria['adult_access_age'];

        if ($source->age >= 18 || ($adultAccessAge < 18 && $source->age >= $adultAccessAge)) {
            return $sexKey.'-adultes';
        }

        $ageBandSize = max(1, (int) $criteria['age_gap_max'] + 1);
        $ageBand = intdiv(max(0, $source->age - 10), $ageBandSize);

        return $sexKey.'-jeunes-'.$ageBand;
    }

    private function pouleAssistantBalancedGroups(Collection $bucket, int $targetSize): array
    {
        $count = $bucket->count();

        if ($count === 0) {
            return [];
        }

        if ($count <= $targetSize + 1) {
            return [$bucket->values()];
        }

        $groupCount = (int) ceil($count / $targetSize);
        $baseSize = intdiv($count, $groupCount);
        $largerGroups = $count % $groupCount;
        $groups = [];
        $offset = 0;

        for ($i = 0; $i < $groupCount; $i++) {
            $size = $baseSize + ($i < $largerGroups ? 1 : 0);
            $groups[] = $bucket->slice($offset, $size)->values();
            $offset += $size;
        }

        return $groups;
    }

    private function pouleAssistantBestCandidateIndex(Collection $group, Collection $remaining, array $criteria): int|false
    {
        $scoredCandidates = $remaining
            ->map(function (InscriptionOperationnelle $candidate, int $index) use ($group, $criteria) {
                $analysis = $this->pouleAssistantGroupAnalysis($group->concat([$candidate]), $criteria);

                return [
                    'index' => $index,
                    'score' => $analysis['score'],
                ];
            })
            ->filter(fn (array $candidate) => $candidate['score'] >= 45)
            ->sortByDesc('score')
            ->values();

        return $scoredCandidates->first()['index'] ?? false;
    }

    private function pouleAssistantCandidateFits(Collection $group, InscriptionOperationnelle $candidate, array $criteria): bool
    {
        $registrations = $group->concat([$candidate]);
        $sources = $registrations->pluck('participantSource');

        if ($criteria['same_sex_only'] && $sources->pluck('sex')->unique()->count() > 1) {
            return false;
        }

        if ($sources->max('approximate_weight') - $sources->min('approximate_weight') > (float) $criteria['weight_gap_max']) {
            return false;
        }

        return $this->pouleAssistantAgesFit($sources->pluck('age'), (int) $criteria['age_gap_max'], (int) $criteria['adult_access_age']);
    }

    private function pouleAssistantAgesFit(Collection $ages, int $ageGapMax, int $adultAccessAge): bool
    {
        $adultCount = $ages->filter(fn (int $age) => $age >= 18)->count();
        $minorAges = $ages->filter(fn (int $age) => $age < 18);

        if ($adultCount === $ages->count()) {
            return true;
        }

        if ($adultCount > 0) {
            return $minorAges->every(fn (int $age) => $age >= $adultAccessAge);
        }

        return $ages->max() - $ages->min() <= $ageGapMax;
    }

    private function pouleAssistantGroupAnalysis(Collection $group, array $criteria): array
    {
        $sources = $group->pluck('participantSource');
        $sexCount = $sources->pluck('sex')->unique()->count();
        $weightGap = $sources->max('approximate_weight') - $sources->min('approximate_weight');
        $ages = $sources->pluck('age');
        $hasAdults = $ages->contains(fn (int $age) => $age >= 18);
        $minorWithAdults = $hasAdults && $ages->contains(fn (int $age) => $age < 18);
        $adultCount = $ages->filter(fn (int $age) => $age >= 18)->count();
        $minorAges = $ages->filter(fn (int $age) => $age < 18);
        $ageGap = $minorAges->count() > 1 ? $minorAges->max() - $minorAges->min() : 0;
        $targetGap = abs($group->count() - (int) $criteria['target_size']);
        $ageCompatible = $this->pouleAssistantAgesFit($ages, (int) $criteria['age_gap_max'], (int) $criteria['adult_access_age']);
        $weightCompatible = $weightGap <= (float) $criteria['weight_gap_max'];
        $sexCompatible = ! $criteria['same_sex_only'] || $sexCount === 1;
        $score = 20;
        $reasons = [];

        if ($sexCompatible) {
            $score += 20;
            $reasons[] = $criteria['same_sex_only'] ? 'même sexe' : 'mixité autorisée';
        } else {
            $score -= 20;
            $reasons[] = 'sexes mélangés';
        }

        if ($ageCompatible) {
            $score += 20;
            $reasons[] = $hasAdults && $adultCount === $group->count() ? 'adultes' : 'âge compatible';
        } else {
            $score -= 20;
            $reasons[] = 'écart d’âge dépassé';
        }

        if ($weightCompatible) {
            $score += 20;
            $reasons[] = 'poids compatible';
        } else {
            $score -= min(20, 8 + (int) floor($weightGap - (float) $criteria['weight_gap_max']));
            $reasons[] = 'écart de poids limite';
        }

        if ($targetGap === 0) {
            $score += 20;
            $reasons[] = 'taille cible respectée';
        } elseif ($targetGap === 1) {
            $score += 10;
            $reasons[] = 'taille proche de la cible';
        } elseif ($targetGap === 2) {
            $score -= 10;
            $reasons[] = 'taille acceptable';
        } else {
            $score -= min(45, $targetGap * 12);
            $reasons[] = 'poule trop petite';
        }

        if ($minorWithAdults) {
            $score -= 25;
            $reasons[] = 'mineur proposé avec adultes';
        }

        if (! $hasAdults && $ageGap === (int) $criteria['age_gap_max']) {
            $score -= 5;
            $reasons[] = 'écart d’âge limite';
        }

        $score = max(0, min(100, $score));
        $indicator = match (true) {
            $score >= 85 => 'Très cohérent',
            $score >= 60 => 'À vérifier',
            default => 'À arbitrer',
        };

        return [
            'indicator' => $indicator,
            'score' => $score,
            'justification' => Str::ucfirst(collect($reasons)->unique()->take(4)->implode(', ')),
            'warning' => $minorWithAdults ? 'À vérifier : participant mineur proposé avec adultes' : null,
        ];
    }

    public function participantValidationSummary(): array
    {
        $activeRegistrations = $this->operationalRegistrations()
            ->where('is_active', true)
            ->get(['club_id', 'is_validated']);

        $byClub = $activeRegistrations
            ->groupBy('club_id')
            ->map(fn ($registrations) => [
                'active' => $registrations->count(),
                'validated' => $registrations->where('is_validated', true)->count(),
                'not_validated' => $registrations->where('is_validated', false)->count(),
            ]);

        return [
            'global' => [
                'active' => $activeRegistrations->count(),
                'validated' => $activeRegistrations->where('is_validated', true)->count(),
                'not_validated' => $activeRegistrations->where('is_validated', false)->count(),
            ],
            'by_club' => $byClub,
        ];
    }

    public function actionsToDoForClub(Club $club): array
    {
        $actions = [];

        if ($club->id === $this->organizer_club_id) {
            $preInviteCount = $this->invitations()
                ->where('status', Invitation::STATUS_PRE_INVITE)
                ->count();

            if ($preInviteCount > 0) {
                $actions[] = "Marquer {$preInviteCount} invitation(s) envoyée(s)";
            }

            $notValidatedCount = $this->operationalRegistrations()
                ->where('is_active', true)
                ->where('is_validated', false)
                ->count();

            if ($notValidatedCount > 0) {
                $actions[] = "Valider {$notValidatedCount} participant(s)";
            }

            $availableRegistrationCount = $this->operationalRegistrations()
                ->where('is_active', true)
                ->where('is_validated', true)
                ->whereNull('poule_id')
                ->count();

            if ($availableRegistrationCount > 0) {
                $actions[] = "Affecter {$availableRegistrationCount} participant(s) à une poule";
            }

            $draftPoulesReadyCount = $this->poules()
                ->where('status', Poule::STATUS_DRAFT)
                ->withCount(['registrations as eligible_registrations_count' => function (Builder $query) {
                    $query->where('is_active', true)
                        ->where('is_validated', true);
                }])
                ->get()
                ->where('eligible_registrations_count', '>=', 2)
                ->count();

            if ($draftPoulesReadyCount > 0) {
                $actions[] = "Figer {$draftPoulesReadyCount} poule(s)";
            }

            $frozenPoulesWithoutCombatsCount = $this->poules()
                ->where('status', Poule::STATUS_FROZEN)
                ->doesntHave('combats')
                ->count();

            if ($frozenPoulesWithoutCombatsCount > 0) {
                $actions[] = 'Générer les combats';
            }

            $scoresToEnterCount = Combat::query()
                ->where('statut', Combat::STATUS_TO_ENTER)
                ->whereHas('poule', fn (Builder $query) => $query->where('competition_id', $this->id))
                ->count();

            if ($scoresToEnterCount > 0) {
                $actions[] = "Saisir {$scoresToEnterCount} score(s)";
            }

            return $actions ?: ['Aucune action urgente'];
        }

        $invitation = $this->invitations()
            ->where('club_id', $club->id)
            ->first();

        if ($invitation?->status === Invitation::STATUS_INVITE) {
            $actions[] = 'Confirmer ou refuser votre participation';
        }

        if ($invitation?->status === Invitation::STATUS_PARTICIPATION_CONFIRMED) {
            $activeParticipantCount = $this->operationalRegistrations()
                ->where('club_id', $club->id)
                ->where('is_active', true)
                ->count();

            if ($activeParticipantCount === 0) {
                $actions[] = 'Inscrire vos participants';
            }

            $notValidatedCount = $this->operationalRegistrations()
                ->where('club_id', $club->id)
                ->where('is_active', true)
                ->where('is_validated', false)
                ->count();

            if ($notValidatedCount > 0) {
                $actions[] = 'Vos participants sont en attente de validation';
            }
        }

        return $actions ?: ['Aucune action urgente'];
    }

    public function roleLabelForClub(Club $club): string
    {
        if ($club->id === $this->organizer_club_id) {
            return 'Organisateur';
        }

        $invitation = $this->relationLoaded('invitations')
            ? $this->invitations->firstWhere('club_id', $club->id)
            : $this->invitations()->where('club_id', $club->id)->first();

        if ($invitation?->status === Invitation::STATUS_PARTICIPATION_CONFIRMED) {
            return 'Participant';
        }

        if ($invitation !== null) {
            return 'Invité';
        }

        return 'Non concerné';
    }

    public function detailFragmentForClub(Club $club): string
    {
        if ($club->id === $this->organizer_club_id) {
            $actions = collect($this->actionsToDoForClub($club))
                ->reject(fn (string $action) => $action === 'Aucune action urgente');

            return $actions->isNotEmpty() ? 'actions' : 'poules';
        }

        $invitation = $this->relationLoaded('invitations')
            ? $this->invitations->firstWhere('club_id', $club->id)
            : $this->invitations()->where('club_id', $club->id)->first();

        if ($invitation?->status === Invitation::STATUS_PARTICIPATION_CONFIRMED) {
            return 'participants';
        }

        return 'invitation';
    }

    public function scopeVisibleForClub(Builder $query, Club|int $club): Builder
    {
        $clubId = $club instanceof Club ? $club->id : $club;

        return $query->where(function (Builder $query) use ($clubId) {
            $query->where('organizer_club_id', $clubId)
                ->orWhereHas('invitations', function (Builder $query) use ($clubId) {
                    $query->where('club_id', $clubId)
                        ->whereIn('status', [
                            Invitation::STATUS_INVITE,
                            Invitation::STATUS_PARTICIPATION_CONFIRMED,
                            Invitation::STATUS_PARTICIPATION_DECLINED,
                        ]);
                });
        });
    }
}
