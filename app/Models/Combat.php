<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Combat extends Model
{
    public const STATUS_TO_ENTER = 'a_saisir';
    public const STATUS_FINISHED = 'termine';

    public const RESULT_LEFT_WIN = 'victoire_gauche';
    public const RESULT_DRAW = 'nul';
    public const RESULT_RIGHT_WIN = 'victoire_droite';
    public const RESULT_NO_CONTEST = 'pas_de_combat';

    public const STATUS_LABELS = [
        self::STATUS_TO_ENTER => 'Score à saisir',
        self::STATUS_FINISHED => 'Combat terminé',
    ];

    public const RESULT_LABELS = [
        self::RESULT_LEFT_WIN => 'Victoire gauche',
        self::RESULT_DRAW => 'Nul',
        self::RESULT_RIGHT_WIN => 'Victoire droite',
        self::RESULT_NO_CONTEST => 'Pas de combat',
    ];

    protected $fillable = [
        'poule_id',
        'inscription_a_id',
        'inscription_b_id',
        'ordre_combat',
        'statut',
        'resultat',
        'score_a',
        'score_b',
        'score_texte',
        'commentaire',
        'absence_forfait',
    ];

    protected function casts(): array
    {
        return [
            'score_a' => 'integer',
            'score_b' => 'integer',
            'absence_forfait' => 'boolean',
        ];
    }

    public function poule(): BelongsTo
    {
        return $this->belongsTo(Poule::class);
    }

    public function inscriptionA(): BelongsTo
    {
        return $this->belongsTo(InscriptionOperationnelle::class, 'inscription_a_id');
    }

    public function inscriptionB(): BelongsTo
    {
        return $this->belongsTo(InscriptionOperationnelle::class, 'inscription_b_id');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->statut] ?? $this->statut;
    }

    public function resultLabel(): string
    {
        return self::RESULT_LABELS[$this->resultat] ?? 'Résultat à saisir';
    }

    public function scoreAccessBlockedMessage(int $organizerClubId, ?int $currentClubId): ?string
    {
        if ($currentClubId !== $organizerClubId) {
            return 'Impossible : action réservée à l’organisateur';
        }

        return null;
    }
}
