<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InscriptionOperationnelle extends Model
{
    protected $fillable = [
        'competition_id',
        'club_id',
        'participant_source_id',
        'poule_id',
        'is_active',
        'is_validated',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_validated' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (InscriptionOperationnelle $registration) {
            if (! $registration->is_validated) {
                $registration->poule_id = null;
            }

            if (! $registration->is_active) {
                $registration->poule_id = null;
                $registration->is_validated = false;
            }
        });
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function participantSource(): BelongsTo
    {
        return $this->belongsTo(ParticipantSource::class);
    }

    public function poule(): BelongsTo
    {
        return $this->belongsTo(Poule::class);
    }

    public function participationStatusLabel(): string
    {
        if (! $this->is_active) {
            return 'Retiré';
        }

        return $this->is_validated ? 'Participant validé' : 'En attente de validation';
    }

    public function editBlockedMessage(): ?string
    {
        if (! $this->is_active) {
            return 'Impossible : participation annulée';
        }

        if ($this->poule_id !== null) {
            return 'Impossible : participant affecté à une poule';
        }

        if ($this->is_validated) {
            return 'Impossible : participant validé';
        }

        return null;
    }

    public function withdrawBlockedMessage(): ?string
    {
        if (! $this->is_active) {
            return 'Impossible : participation annulée';
        }

        $this->loadMissing('poule');

        if ($this->poule?->status === Poule::STATUS_FROZEN) {
            return 'Impossible : participant dans une poule figée';
        }

        return null;
    }

    public function reactivateBlockedMessage(): ?string
    {
        if ($this->is_active) {
            return 'Impossible : participant déjà actif';
        }

        $this->loadMissing('poule');

        if ($this->poule?->status === Poule::STATUS_FROZEN) {
            return 'Impossible : participant dans une poule figée';
        }

        return null;
    }

    public function validateBlockedMessage(): ?string
    {
        if ($this->is_active && $this->is_validated) {
            return 'Impossible : participant déjà validé';
        }

        $this->loadMissing('poule');

        if (! $this->is_active && $this->poule?->status === Poule::STATUS_FROZEN) {
            return 'Impossible : participant dans une poule figée';
        }

        return null;
    }

    public function unvalidateBlockedMessage(): ?string
    {
        if (! $this->is_active) {
            return 'Impossible : participation annulée';
        }

        if (! $this->is_validated) {
            return 'Impossible : participant non validé';
        }

        $this->loadMissing('poule');

        if ($this->poule?->status === Poule::STATUS_FROZEN) {
            return 'Impossible : participant dans une poule figée';
        }

        return null;
    }

    public function withdrawAssignmentBlockedMessage(): ?string
    {
        $this->loadMissing('poule');

        if ($this->poule?->status === Poule::STATUS_FROZEN) {
            return 'Impossible : poule figée';
        }

        if (! $this->is_active) {
            return 'Impossible : participation annulée';
        }

        if (! $this->is_validated) {
            return 'Impossible : participant non validé';
        }

        if ($this->poule_id === null) {
            return 'Impossible : participant déjà affecté';
        }

        return null;
    }

    public function moveAssignmentBlockedMessage(?Poule $targetPoule): ?string
    {
        $this->loadMissing('poule');

        if ($this->poule?->status === Poule::STATUS_FROZEN || $targetPoule?->status === Poule::STATUS_FROZEN) {
            return 'Impossible : poule figée';
        }

        if (! $this->is_active) {
            return 'Impossible : participation annulée';
        }

        if (! $this->is_validated) {
            return 'Impossible : participant non validé';
        }

        if ($this->poule_id === null) {
            return 'Impossible : participant déjà affecté';
        }

        if ($targetPoule !== null && $this->poule_id === $targetPoule->id) {
            return 'Impossible : même poule';
        }

        return null;
    }

    public function combatsAsA(): HasMany
    {
        return $this->hasMany(Combat::class, 'inscription_a_id');
    }

    public function combatsAsB(): HasMany
    {
        return $this->hasMany(Combat::class, 'inscription_b_id');
    }
}
