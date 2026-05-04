<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Competition;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_invited_club_can_confirm_participation_once_invitation_is_sent(): void
    {
        [$clubA, $clubB, $clubC, $userA, $userB, $userC, $competition, $invitation] = $this->scenario();

        $this->withSession(['current_user_id' => $userB->id])
            ->post(route('competitions.invitations.confirm', [$competition, $invitation]))
            ->assertRedirect(route('competitions.show', ['competition' => $competition, 'tab' => 'clubs']));

        $this->assertSame(Invitation::STATUS_PARTICIPATION_CONFIRMED, $invitation->fresh()->status);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.index'))
            ->assertOk()
            ->assertSee('Competition Sprint 2')
            ->assertSee('🏢 Club A');

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Participation confirmée')
            ->assertDontSee('participation_confirmee')
            ->assertDontSee('Confirmer la participation')
            ->assertDontSee('Refuser la participation');

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Club B')
            ->assertSee('Accepté')
            ->assertDontSee('participation_confirmee');

        $this->withSession(['current_user_id' => $userC->id])
            ->get(route('competitions.index'))
            ->assertOk()
            ->assertDontSee('Competition Sprint 2');

        $this->withSession(['current_user_id' => $userC->id])
            ->get(route('competitions.show', $competition))
            ->assertNotFound();
    }

    public function test_invited_club_can_decline_participation_once_invitation_is_sent(): void
    {
        [, $clubB, , , $userB, , $competition, $invitation] = $this->scenario();

        $this->withSession(['current_user_id' => $userB->id])
            ->post(route('competitions.invitations.decline', [$competition, $invitation]))
            ->assertRedirect(route('competitions.show', ['competition' => $competition, 'tab' => 'clubs']));

        $this->assertSame(Invitation::STATUS_PARTICIPATION_DECLINED, $invitation->fresh()->status);
        $this->assertTrue(Competition::visibleForClub($clubB)->whereKey($competition)->exists());
    }

    public function test_only_invited_club_with_invite_status_can_answer(): void
    {
        [$clubA, $clubB, $clubC, $userA, $userB, $userC, $competition, $invitation] = $this->scenario(Invitation::STATUS_PRE_INVITE);

        $this->withSession(['current_user_id' => $userB->id])
            ->post(route('competitions.invitations.confirm', [$competition, $invitation]))
            ->assertForbidden();

        $this->assertSame(Invitation::STATUS_PRE_INVITE, $invitation->fresh()->status);

        $invitation->markAsSent();

        $this->withSession(['current_user_id' => $userC->id])
            ->post(route('competitions.invitations.confirm', [$competition, $invitation]))
            ->assertForbidden();

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.invitations.confirm', [$competition, $invitation]))
            ->assertForbidden();

        $this->assertSame(Invitation::STATUS_INVITE, $invitation->fresh()->status);
    }

    public function test_response_is_final_for_sprint_2(): void
    {
        [, , , , $userB, , $competition, $invitation] = $this->scenario();

        $invitation->confirmParticipation();

        $this->withSession(['current_user_id' => $userB->id])
            ->post(route('competitions.invitations.decline', [$competition, $invitation]))
            ->assertForbidden();

        $this->assertSame(Invitation::STATUS_PARTICIPATION_CONFIRMED, $invitation->fresh()->status);
    }

    private function scenario(string $status = Invitation::STATUS_INVITE): array
    {
        $clubA = Club::create(['name' => 'Club A']);
        $clubB = Club::create(['name' => 'Club B']);
        $clubC = Club::create(['name' => 'Club C']);

        $userA = User::create([
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
            'name' => 'Competition Sprint 2',
        ]);

        $invitation = Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'status' => $status,
        ]);

        return [$clubA, $clubB, $clubC, $userA, $userB, $userC, $competition, $invitation];
    }
}
