<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

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
