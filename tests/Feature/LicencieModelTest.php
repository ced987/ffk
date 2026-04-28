<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Licencie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicencieModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_licencie_belongs_to_a_club_and_club_has_many_licencies(): void
    {
        $club = Club::create(['name' => 'Club A']);

        $licencie = Licencie::create([
            'club_id' => $club->id,
            'nom' => 'Martin',
            'prenom' => 'Lea',
            'date_naissance' => '2012-05-14',
            'sexe' => 'feminin',
            'poids' => 48,
        ]);

        $this->assertTrue($licencie->club->is($club));
        $this->assertTrue($club->licencies->contains($licencie));
        $this->assertSame('2012-05-14', $licencie->date_naissance->toDateString());
        $this->assertSame(48, $licencie->poids);
    }
}
