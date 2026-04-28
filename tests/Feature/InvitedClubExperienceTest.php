<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Competition;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitedClubExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_invited_club_experience_follows_invitation_status(): void
    {
        $clubA = Club::create(['name' => 'Club A']);
        $clubB = Club::create(['name' => 'Club B']);
        $clubC = Club::create(['name' => 'Club C']);

        User::create([
            'club_id' => $clubA->id,
            'name' => 'Utilisateur Club A',
            'email' => 'club-a@example.test',
            'password' => 'password',
        ]);

        $userB = User::create([
            'club_id' => $clubB->id,
            'name' => 'Utilisateur Club B',
            'email' => 'club-b@example.test',
            'password' => 'password',
        ]);

        $userC = User::create([
            'club_id' => $clubC->id,
            'name' => 'Utilisateur Club C',
            'email' => 'club-c@example.test',
            'password' => 'password',
        ]);

        $competition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Competition Sprint 1',
        ]);

        $invitation = Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'status' => Invitation::STATUS_PRE_INVITE,
        ]);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.index'))
            ->assertOk()
            ->assertDontSee('Competition Sprint 1');

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertNotFound();

        $invitation->markAsSent();

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.index'))
            ->assertOk()
            ->assertSee('Competition Sprint 1')
            ->assertSee('🏢 Club A');

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Competition Sprint 1')
            ->assertSee('Organisateur')
            ->assertSee('Club A');

        $this->withSession(['current_user_id' => $userC->id])
            ->get(route('competitions.index'))
            ->assertOk()
            ->assertDontSee('Competition Sprint 1');

        $this->withSession(['current_user_id' => $userC->id])
            ->get(route('competitions.show', $competition))
            ->assertNotFound();
    }
}
