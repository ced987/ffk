<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Competition;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetitionInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitation_form_offers_clubs_that_are_not_organizer_and_not_already_invited(): void
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

        $competition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Competition Sprint 1',
        ]);

        Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'status' => Invitation::STATUS_PRE_INVITE,
        ]);

        $response = $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Club C')
            ->assertSee('Rechercher un club')
            ->assertSee('name="club_ids[]"', false)
            ->assertSee('value="'.$clubC->id.'"', false);

        $content = $response->getContent();
        $formStart = strpos($content, 'action="'.route('competitions.invitations.store', $competition).'"');
        $this->assertNotFalse($formStart);
        $formEnd = strpos($content, '</form>', $formStart);
        $this->assertNotFalse($formEnd);
        $invitationForm = substr($content, $formStart, $formEnd - $formStart);

        $this->assertStringContainsString('value="'.$clubC->id.'"', $invitationForm);
        $this->assertStringNotContainsString('value="'.$clubA->id.'"', $invitationForm);
        $this->assertStringNotContainsString('value="'.$clubB->id.'"', $invitationForm);
    }

    public function test_organizer_can_add_a_pre_invited_club_without_making_competition_visible_yet(): void
    {
        $clubA = Club::create(['name' => 'Club A']);
        $clubB = Club::create(['name' => 'Club B']);

        $userA = User::create([
            'club_id' => $clubA->id,
            'name' => 'Utilisateur Club A',
            'email' => 'club-a@example.test',
            'password' => 'password',
        ]);

        $competition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Competition Sprint 1',
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.invitations.store', $competition), [
                'club_id' => $clubB->id,
            ])
            ->assertRedirect(route('competitions.show', ['competition' => $competition, 'tab' => 'clubs']));

        $this->assertDatabaseHas('invitations', [
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'status' => Invitation::STATUS_PRE_INVITE,
        ]);

        $this->assertFalse(
            Competition::visibleForClub($clubB)->whereKey($competition)->exists()
        );
    }

    public function test_organizer_can_mark_pre_invitation_as_sent_and_invited_club_can_then_see_competition(): void
    {
        $clubA = Club::create(['name' => 'Club A']);
        $clubB = Club::create(['name' => 'Club B']);

        $userA = User::create([
            'club_id' => $clubA->id,
            'name' => 'Utilisateur Club A',
            'email' => 'club-a@example.test',
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

        $this->assertFalse(
            Competition::visibleForClub($clubB)->whereKey($competition)->exists()
        );

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.invitations.mark-sent', [$competition, $invitation]))
            ->assertRedirect(route('competitions.show', ['competition' => $competition, 'tab' => 'clubs']));

        $this->assertSame(Invitation::STATUS_INVITE, $invitation->fresh()->status);
        $this->assertTrue(
            Competition::visibleForClub($clubB)->whereKey($competition)->exists()
        );
    }

    public function test_organizer_can_add_multiple_pre_invited_clubs_at_once(): void
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

        $competition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Competition Sprint 1',
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.invitations.store', $competition), [
                'club_ids' => [$clubB->id, $clubC->id],
            ])
            ->assertRedirect(route('competitions.show', ['competition' => $competition, 'tab' => 'clubs']))
            ->assertSessionHas('status', '2 clubs ajoutés en préparation de l’invitation.');

        $this->assertDatabaseHas('invitations', [
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'status' => Invitation::STATUS_PRE_INVITE,
        ]);

        $this->assertDatabaseHas('invitations', [
            'competition_id' => $competition->id,
            'club_id' => $clubC->id,
            'status' => Invitation::STATUS_PRE_INVITE,
        ]);
    }

    public function test_non_organizer_cannot_mark_invitation_as_sent(): void
    {
        $clubA = Club::create(['name' => 'Club A']);
        $clubB = Club::create(['name' => 'Club B']);

        $userB = User::create([
            'club_id' => $clubB->id,
            'name' => 'Utilisateur Club B',
            'email' => 'club-b@example.test',
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
            ->post(route('competitions.invitations.mark-sent', [$competition, $invitation]))
            ->assertForbidden();

        $this->assertSame(Invitation::STATUS_PRE_INVITE, $invitation->fresh()->status);
    }

    public function test_organizer_can_relaunch_declined_invitation(): void
    {
        $clubA = Club::create(['name' => 'Club A']);
        $clubB = Club::create(['name' => 'Club B']);

        $userA = User::create([
            'club_id' => $clubA->id,
            'name' => 'Utilisateur Club A',
            'email' => 'club-a@example.test',
            'password' => 'password',
        ]);

        $competition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Competition Sprint 1',
        ]);

        $invitation = Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'status' => Invitation::STATUS_PARTICIPATION_DECLINED,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Clubs refusés')
            ->assertSee(route('competitions.invitations.relaunch', [$competition, $invitation]), false);

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.invitations.relaunch', [$competition, $invitation]))
            ->assertRedirect(route('competitions.show', ['competition' => $competition, 'tab' => 'clubs']))
            ->assertSessionHas('status', 'Invitation relancée. Le club est de nouveau en attente de réponse.');

        $this->assertSame(Invitation::STATUS_INVITE, $invitation->fresh()->status);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Invitations en attente')
            ->assertSee('Envoyée — en attente')
            ->assertSee('Aucun club refusé.');
    }

    public function test_non_organizer_cannot_relaunch_declined_invitation(): void
    {
        $clubA = Club::create(['name' => 'Club A']);
        $clubB = Club::create(['name' => 'Club B']);

        $userB = User::create([
            'club_id' => $clubB->id,
            'name' => 'Utilisateur Club B',
            'email' => 'club-b@example.test',
            'password' => 'password',
        ]);

        $competition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Competition Sprint 1',
        ]);

        $invitation = Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'status' => Invitation::STATUS_PARTICIPATION_DECLINED,
        ]);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertDontSee(route('competitions.invitations.relaunch', [$competition, $invitation]), false);

        $this->withSession(['current_user_id' => $userB->id])
            ->post(route('competitions.invitations.relaunch', [$competition, $invitation]))
            ->assertForbidden();

        $this->assertSame(Invitation::STATUS_PARTICIPATION_DECLINED, $invitation->fresh()->status);
    }

    public function test_mark_sent_button_is_only_shown_for_pre_invite_invitations_to_organizer(): void
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
            'name' => 'Competition Sprint 1',
        ]);

        $preInvitation = Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'status' => Invitation::STATUS_PRE_INVITE,
        ]);

        $sentInvitation = Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubC->id,
            'status' => Invitation::STATUS_INVITE,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee(route('competitions.invitations.mark-sent', [$competition, $preInvitation]), false)
            ->assertDontSee(route('competitions.invitations.mark-sent', [$competition, $sentInvitation]), false);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertNotFound();

        $this->withSession(['current_user_id' => $userC->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertDontSee('Marquer envoyée');
    }

    public function test_non_organizer_cannot_add_a_pre_invited_club(): void
    {
        $clubA = Club::create(['name' => 'Club A']);
        $clubB = Club::create(['name' => 'Club B']);
        $clubC = Club::create(['name' => 'Club C']);

        $userB = User::create([
            'club_id' => $clubB->id,
            'name' => 'Utilisateur Club B',
            'email' => 'club-b@example.test',
            'password' => 'password',
        ]);

        $competition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Competition Sprint 1',
        ]);

        $this->withSession(['current_user_id' => $userB->id])
            ->post(route('competitions.invitations.store', $competition), [
                'club_id' => $clubC->id,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('invitations', 0);
    }

    public function test_organizer_cannot_invite_same_club_twice_or_invite_itself(): void
    {
        $clubA = Club::create(['name' => 'Club A']);
        $clubB = Club::create(['name' => 'Club B']);

        $userA = User::create([
            'club_id' => $clubA->id,
            'name' => 'Utilisateur Club A',
            'email' => 'club-a@example.test',
            'password' => 'password',
        ]);

        $competition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Competition Sprint 1',
        ]);

        Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'status' => Invitation::STATUS_PRE_INVITE,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->from(route('competitions.show', $competition))
            ->post(route('competitions.invitations.store', $competition), [
                'club_id' => $clubB->id,
            ])
            ->assertRedirect(route('competitions.show', $competition))
            ->assertSessionHasErrors('club_id');

        $this->assertDatabaseCount('invitations', 1);

        $this->withSession(['current_user_id' => $userA->id])
            ->from(route('competitions.show', $competition))
            ->post(route('competitions.invitations.store', $competition), [
                'club_id' => $clubA->id,
            ])
            ->assertRedirect(route('competitions.show', $competition))
            ->assertSessionHasErrors('club_id');

        $this->assertDatabaseCount('invitations', 1);
    }
}
