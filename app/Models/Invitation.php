<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    public const STATUS_PRE_INVITE = 'pre_invite';
    public const STATUS_INVITE = 'invite';
    public const STATUS_PARTICIPATION_CONFIRMED = 'participation_confirmee';
    public const STATUS_PARTICIPATION_DECLINED = 'participation_refusee';

    public const STATUS_LABELS = [
        self::STATUS_PRE_INVITE => 'Préparation de l’invitation',
        self::STATUS_INVITE => 'En attente de réponse',
        self::STATUS_PARTICIPATION_CONFIRMED => 'Participation confirmée',
        self::STATUS_PARTICIPATION_DECLINED => 'Participation refusée',
    ];

    protected $fillable = [
        'competition_id',
        'club_id',
        'status',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function markAsSent(): void
    {
        $this->update(['status' => self::STATUS_INVITE]);
    }

    public function confirmParticipation(): void
    {
        $this->update(['status' => self::STATUS_PARTICIPATION_CONFIRMED]);
    }

    public function declineParticipation(): void
    {
        $this->update(['status' => self::STATUS_PARTICIPATION_DECLINED]);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
