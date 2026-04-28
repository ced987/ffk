<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Licencie extends Model
{
    protected $fillable = [
        'club_id',
        'nom',
        'prenom',
        'date_naissance',
        'sexe',
        'poids',
    ];

    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
            'poids' => 'integer',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
