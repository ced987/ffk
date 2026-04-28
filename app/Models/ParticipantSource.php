<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParticipantSource extends Model
{
    protected $fillable = [
        'club_id',
        'licencie_id',
        'last_name',
        'first_name',
        'sex',
        'age',
        'approximate_weight',
        'license_number',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function licencie(): BelongsTo
    {
        return $this->belongsTo(Licencie::class);
    }

    public function operationalRegistrations(): HasMany
    {
        return $this->hasMany(InscriptionOperationnelle::class);
    }
}
