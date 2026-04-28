<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Club extends Model
{
    protected $fillable = [
        'name',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function organizedCompetitions(): HasMany
    {
        return $this->hasMany(Competition::class, 'organizer_club_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function participantSources(): HasMany
    {
        return $this->hasMany(ParticipantSource::class);
    }

    public function licencies(): HasMany
    {
        return $this->hasMany(Licencie::class);
    }

    public function operationalRegistrations(): HasMany
    {
        return $this->hasMany(InscriptionOperationnelle::class);
    }
}
