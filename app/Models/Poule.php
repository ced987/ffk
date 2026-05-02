<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Poule extends Model
{
    public const STATUS_DRAFT = 'brouillon';
    public const STATUS_FROZEN = 'figee';

    public const STATUS_LABELS = [
        self::STATUS_DRAFT => 'Poule en préparation',
        self::STATUS_FROZEN => 'Poule figée',
    ];

    protected $fillable = [
        'competition_id',
        'name',
        'status',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(InscriptionOperationnelle::class);
    }

    public function combats(): HasMany
    {
        return $this->hasMany(Combat::class);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function assignmentBlockedMessage(InscriptionOperationnelle $registration): ?string
    {
        if ($this->status !== self::STATUS_DRAFT) {
            return 'Impossible : poule figée';
        }

        if (! $registration->is_active) {
            return 'Impossible : participation annulée';
        }

        if (! $registration->is_validated) {
            return 'Impossible : participant non validé';
        }

        if ($registration->poule_id !== null) {
            return 'Impossible : participant déjà affecté';
        }

        return null;
    }

    public function freezeBlockedMessage(): ?string
    {
        if ($this->status !== self::STATUS_DRAFT) {
            return 'Impossible : poule déjà figée';
        }

        $participantCount = $this->registrations()
            ->where('is_active', true)
            ->where('is_validated', true)
            ->count();

        if ($participantCount < 2) {
            return 'Impossible : minimum 2 participants';
        }

        return null;
    }

    public function combatGenerationBlockedMessage(): ?string
    {
        if ($this->status !== self::STATUS_FROZEN) {
            return 'Impossible : poule non figée';
        }

        if ($this->combats()->exists()) {
            return 'Impossible : combats déjà générés';
        }

        $participantCount = $this->registrations()
            ->where('is_active', true)
            ->where('is_validated', true)
            ->count();

        if ($participantCount < 2) {
            return 'Impossible : minimum 2 participants';
        }

        return null;
    }

    public function hasScoredCombats(): bool
    {
        return $this->combats()
            ->where('statut', Combat::STATUS_FINISHED)
            ->exists();
    }

    public function generateCombats(): int
    {
        $registrations = $this->registrations()
            ->where('competition_id', $this->competition_id)
            ->where('is_active', true)
            ->where('is_validated', true)
            ->where('poule_id', $this->id)
            ->orderBy('id')
            ->get();

        $ordreCombat = 1;
        $createdCount = 0;

        $slots = $registrations->all();
        if (count($slots) % 2 === 1) {
            $slots[] = null;
        }

        $slotCount = count($slots);
        $roundCount = $slotCount - 1;
        $pairsPerRound = intdiv($slotCount, 2);

        for ($round = 0; $round < $roundCount; $round++) {
            for ($i = 0; $i < $pairsPerRound; $i++) {
                $left = $slots[$i];
                $right = $slots[$slotCount - 1 - $i];

                if ($left === null || $right === null) {
                    continue;
                }

                $registrationA = $left->id < $right->id ? $left : $right;
                $registrationB = $left->id < $right->id ? $right : $left;

                Combat::create([
                    'poule_id' => $this->id,
                    'inscription_a_id' => $registrationA->id,
                    'inscription_b_id' => $registrationB->id,
                    'ordre_combat' => $ordreCombat,
                    'statut' => Combat::STATUS_TO_ENTER,
                ]);

                $ordreCombat++;
                $createdCount++;
            }

            $fixed = $slots[0];
            $rotatingSlots = array_slice($slots, 1);
            array_unshift($rotatingSlots, array_pop($rotatingSlots));
            $slots = array_merge([$fixed], $rotatingSlots);
        }

        return $createdCount;
    }

    public function ranking(): Collection
    {
        $registrations = $this->registrations()
            ->with(['club', 'participantSource'])
            ->orderBy('id')
            ->get();

        $pointsByRegistrationId = $registrations
            ->mapWithKeys(fn (InscriptionOperationnelle $registration) => [$registration->id => 0])
            ->all();

        $statsByRegistrationId = $registrations
            ->mapWithKeys(fn (InscriptionOperationnelle $registration) => [
                $registration->id => [
                    'wins' => 0,
                    'draws' => 0,
                    'losses' => 0,
                    'no_contests' => 0,
                    'played' => 0,
                ],
            ])
            ->all();

        $this->combats()
            ->where('statut', Combat::STATUS_FINISHED)
            ->get()
            ->each(function (Combat $combat) use (&$pointsByRegistrationId, &$statsByRegistrationId) {
                if (! array_key_exists($combat->inscription_a_id, $pointsByRegistrationId)
                    || ! array_key_exists($combat->inscription_b_id, $pointsByRegistrationId)) {
                    return;
                }

                if ($combat->resultat === Combat::RESULT_LEFT_WIN) {
                    $pointsByRegistrationId[$combat->inscription_a_id] += 3;
                    $statsByRegistrationId[$combat->inscription_a_id]['wins']++;
                    $statsByRegistrationId[$combat->inscription_b_id]['losses']++;
                    $statsByRegistrationId[$combat->inscription_a_id]['played']++;
                    $statsByRegistrationId[$combat->inscription_b_id]['played']++;
                } elseif ($combat->resultat === Combat::RESULT_RIGHT_WIN) {
                    $pointsByRegistrationId[$combat->inscription_b_id] += 3;
                    $statsByRegistrationId[$combat->inscription_b_id]['wins']++;
                    $statsByRegistrationId[$combat->inscription_a_id]['losses']++;
                    $statsByRegistrationId[$combat->inscription_a_id]['played']++;
                    $statsByRegistrationId[$combat->inscription_b_id]['played']++;
                } elseif ($combat->resultat === Combat::RESULT_DRAW) {
                    $pointsByRegistrationId[$combat->inscription_a_id] += 1;
                    $pointsByRegistrationId[$combat->inscription_b_id] += 1;
                    $statsByRegistrationId[$combat->inscription_a_id]['draws']++;
                    $statsByRegistrationId[$combat->inscription_b_id]['draws']++;
                    $statsByRegistrationId[$combat->inscription_a_id]['played']++;
                    $statsByRegistrationId[$combat->inscription_b_id]['played']++;
                } elseif ($combat->resultat === Combat::RESULT_NO_CONTEST) {
                    $statsByRegistrationId[$combat->inscription_a_id]['no_contests']++;
                    $statsByRegistrationId[$combat->inscription_b_id]['no_contests']++;
                    $statsByRegistrationId[$combat->inscription_a_id]['played']++;
                    $statsByRegistrationId[$combat->inscription_b_id]['played']++;
                }
            });

        $rankedRows = $registrations
            ->map(fn (InscriptionOperationnelle $registration) => [
                'registration' => $registration,
                'points' => $pointsByRegistrationId[$registration->id],
            ])
            ->sortBy([
                ['points', 'desc'],
                fn (array $a, array $b) => $a['registration']->id <=> $b['registration']->id,
            ])
            ->values();

        $previousPoints = null;
        $currentRank = 0;

        return $rankedRows
            ->map(function (array $row, int $index) use (&$previousPoints, &$currentRank, $statsByRegistrationId) {
                if ($previousPoints !== $row['points']) {
                    $currentRank = $index + 1;
                    $previousPoints = $row['points'];
                }

                return [
                    'rank' => $currentRank,
                    'registration' => $row['registration'],
                    'played' => $statsByRegistrationId[$row['registration']->id]['played'],
                    'wins' => $statsByRegistrationId[$row['registration']->id]['wins'],
                    'draws' => $statsByRegistrationId[$row['registration']->id]['draws'],
                    'losses' => $statsByRegistrationId[$row['registration']->id]['losses'],
                    'no_contests' => $statsByRegistrationId[$row['registration']->id]['no_contests'],
                    'points' => $row['points'],
                ];
            });
    }
}
