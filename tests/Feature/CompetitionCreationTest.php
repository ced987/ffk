<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Competition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetitionCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_demo_user_creates_competition_for_their_own_club(): void
    {
        $clubA = Club::create(['name' => 'Club A']);
        $clubB = Club::create(['name' => 'Club B']);

        $userA = User::create([
            'club_id' => $clubA->id,
            'name' => 'Utilisateur Club A',
            'email' => 'club-a@example.test',
            'password' => 'password',
        ]);

        $this
            ->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.index'))
            ->assertOk()
            ->assertSee('Créer une compétition')
            ->assertSee('Nom de la compétition')
            ->assertSee('Créer la compétition');

        $this
            ->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.store'), [
                'name' => 'Open Kumite Sprint 1',
                'organizer_club_id' => $clubB->id,
            ])
            ->assertRedirect(route('competitions.index'));

        $this->assertDatabaseHas('competitions', [
            'name' => 'Open Kumite Sprint 1',
            'organizer_club_id' => $clubA->id,
        ]);

        $this->assertDatabaseMissing('competitions', [
            'name' => 'Open Kumite Sprint 1',
            'organizer_club_id' => $clubB->id,
        ]);

        $competition = Competition::where('name', 'Open Kumite Sprint 1')->firstOrFail();

        $this->assertTrue(Competition::visibleForClub($clubA)->whereKey($competition)->exists());
        $this->assertFalse(Competition::visibleForClub($clubB)->whereKey($competition)->exists());
    }
}
