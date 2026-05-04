<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Competition;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetitionShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_competition_detail_shows_invited_clubs_summary_with_readable_statuses(): void
    {
        $clubA = Club::create(['name' => 'Club A']);
        $clubB = Club::create(['name' => 'Club B']);
        $clubC = Club::create(['name' => 'Club C']);
        $clubD = Club::create(['name' => 'Club D']);
        $clubE = Club::create(['name' => 'Club E']);

        $userA = User::create([
            'club_id' => $clubA->id,
            'name' => 'Utilisateur Club A',
            'email' => 'club-a@example.test',
            'password' => 'password',
        ]);

        $competition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Competition Sprint 2',
        ]);

        Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'status' => Invitation::STATUS_PRE_INVITE,
        ]);

        Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubC->id,
            'status' => Invitation::STATUS_INVITE,
        ]);

        Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubD->id,
            'status' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
        ]);

        Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubE->id,
            'status' => Invitation::STATUS_PARTICIPATION_DECLINED,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Récapitulatif des clubs')
            ->assertSee('data-tab-target="suivi"', false)
            ->assertSee('data-tab-target="clubs"', false)
            ->assertSee('data-tab-target="participants"', false)
            ->assertSee('data-tab-target="poules"', false)
            ->assertSee('data-tab-target="combats"', false)
            ->assertSee('data-tab-panel="suivi"', false)
            ->assertSee('id="actions"', false)
            ->assertSee('data-tab-panel="clubs"', false)
            ->assertSee('data-tab-panel="participants"', false)
            ->assertSee('data-tab-panel="poules"', false)
            ->assertSee('data-tab-panel="combats"', false)
            ->assertSee('const tabForHash = (hash, fallbackTab =', false)
            ->assertSee("return 'suivi';", false)
            ->assertSee("hash === '#invitation' || hash === '#clubs'", false)
            ->assertSee("hash.startsWith('#participants')", false)
            ->assertSee("hash.startsWith('#poules')", false)
            ->assertSee("hash.startsWith('#combat')", false)
            ->assertSee('Pré-invités')
            ->assertSee('En attente')
            ->assertSee('Acceptés')
            ->assertSee('Refusés')
            ->assertSee('Clubs pré-invités')
            ->assertSee('Clubs ajoutés mais invitation non encore envoyée.')
            ->assertSee('Invitations en attente')
            ->assertSee('Invitation envoyée, en attente de réponse.')
            ->assertSee('Clubs acceptés')
            ->assertSee('Clubs ayant accepté l’invitation.')
            ->assertSee('Clubs refusés')
            ->assertSee('Invitation refusée.')
            ->assertSee('Pré-invité')
            ->assertSee('Envoyée – en attente')
            ->assertSee('Accepté')
            ->assertSee('Refusé')
            ->assertSee('Club B')
            ->assertSee('Club C')
            ->assertSee('Club D')
            ->assertSee('Club E');
    }

    public function test_competition_detail_lists_invited_clubs_and_invitation_statuses(): void
    {
        $clubA = Club::create(['name' => 'Club A']);
        $clubB = Club::create(['name' => 'Club B']);
        Club::create(['name' => 'Club C']);

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
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Clubs pré-invités')
            ->assertSee('Club B')
            ->assertSee('Date non renseignée')
            ->assertSee('Modifier la date')
            ->assertSee('action="'.route('competitions.date.update', $competition).'"', false)
            ->assertSee('Pré-invité')
            ->assertSee('Lancer l’invitation')
            ->assertDontSee(Invitation::STATUS_PRE_INVITE)
            ->assertSee('Rechercher un club');
    }

    public function test_organizer_can_update_competition_date_visible_to_invited_clubs(): void
    {
        $clubA = Club::create(['name' => 'Club A']);
        $clubB = Club::create(['name' => 'Club B']);

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

        $competition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Competition avec date',
        ]);

        Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'status' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.date.update', $competition), [
                'date_competition' => '2026-03-12',
            ])
            ->assertRedirect(route('competitions.show', ['competition' => $competition, 'tab' => 'suivi']).'#actions')
            ->assertSessionHas('status', 'Date de compétition enregistrée.');

        $this->assertSame('2026-03-12', $competition->refresh()->date_competition->toDateString());

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('12/03/2026')
            ->assertDontSee('Modifier la date');
    }

    public function test_only_organizer_can_update_competition_date_and_validation_applies(): void
    {
        $clubA = Club::create(['name' => 'Club A']);
        $clubB = Club::create(['name' => 'Club B']);

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

        $competition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Competition avec date',
            'date_competition' => '2026-03-12',
        ]);

        Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'status' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
        ]);

        $this->withSession(['current_user_id' => $userB->id])
            ->patch(route('competitions.date.update', $competition), [
                'date_competition' => '2026-04-18',
            ])
            ->assertForbidden();

        $this->assertSame('2026-03-12', $competition->refresh()->date_competition->toDateString());

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.date.update', $competition), [
                'date_competition' => 'pas-une-date',
            ])
            ->assertSessionHasErrors(['date_competition']);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.date.update', $competition), [
                'date_competition' => '',
            ])
            ->assertRedirect(route('competitions.show', ['competition' => $competition, 'tab' => 'suivi']).'#actions');

        $this->assertNull($competition->refresh()->date_competition);
    }

    public function test_only_organizer_can_update_competition_name(): void
    {
        $clubA = Club::create(['name' => 'Club A']);
        $clubB = Club::create(['name' => 'Club B']);

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

        $competition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Ancienne compétition',
        ]);

        Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'status' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('data-competition-name-edit', false)
            ->assertSee('action="'.route('competitions.update', $competition).'"', false);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.update', $competition), [
                'name' => 'Nouvelle compétition',
            ])
            ->assertRedirect(route('competitions.show', $competition));

        $this->assertSame('Nouvelle compétition', $competition->refresh()->name);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertDontSee('title="Modifier le nom de la compétition"', false)
            ->assertDontSee('action="'.route('competitions.update', $competition).'"', false);

        $this->withSession(['current_user_id' => $userB->id])
            ->patch(route('competitions.update', $competition), [
                'name' => 'Tentative invitée',
            ])
            ->assertForbidden();

        $this->assertSame('Nouvelle compétition', $competition->refresh()->name);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.update', $competition), [
                'name' => '',
            ])
            ->assertSessionHasErrors(['name']);
    }

    public function test_organizer_can_update_additional_information_visible_to_invited_clubs(): void
    {
        $clubA = Club::create(['name' => 'Club A']);
        $clubB = Club::create(['name' => 'Club B']);

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

        $competition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Competition avec infos',
        ]);

        Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'status' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Modifier les informations complémentaires')
            ->assertSee('action="'.route('competitions.informations-complementaires.update', $competition).'"', false)
            ->assertDontSee('Accueil 8h30');

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.informations-complementaires.update', $competition), [
                'informations_complementaires' => "Accueil 8h30\nPrévoir protections <strong>obligatoires</strong>",
            ])
            ->assertRedirect(route('competitions.show', ['competition' => $competition, 'tab' => 'suivi']).'#actions')
            ->assertSessionHas('status', 'Informations complémentaires enregistrées.');

        $this->assertDatabaseHas('competitions', [
            'id' => $competition->id,
            'informations_complementaires' => "Accueil 8h30\nPrévoir protections <strong>obligatoires</strong>",
        ]);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Informations complémentaires')
            ->assertSee('Accueil 8h30')
            ->assertSee('Prévoir protections')
            ->assertSee('&lt;strong&gt;obligatoires&lt;/strong&gt;', false)
            ->assertDontSee('<strong>obligatoires</strong>', false)
            ->assertDontSee('Modifier les informations complémentaires');
    }

    public function test_only_organizer_can_update_additional_information_and_validation_applies(): void
    {
        $clubA = Club::create(['name' => 'Club A']);
        $clubB = Club::create(['name' => 'Club B']);

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

        $competition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Competition avec infos',
            'informations_complementaires' => 'Info initiale',
        ]);

        Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'status' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
        ]);

        $this->withSession(['current_user_id' => $userB->id])
            ->patch(route('competitions.informations-complementaires.update', $competition), [
                'informations_complementaires' => 'Tentative invitée',
            ])
            ->assertForbidden();

        $this->assertSame('Info initiale', $competition->refresh()->informations_complementaires);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.informations-complementaires.update', $competition), [
                'informations_complementaires' => str_repeat('a', 1001),
            ])
            ->assertSessionHasErrors(['informations_complementaires']);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.informations-complementaires.update', $competition), [
                'informations_complementaires' => '   ',
            ])
            ->assertRedirect(route('competitions.show', ['competition' => $competition, 'tab' => 'suivi']).'#actions');

        $this->assertNull($competition->refresh()->informations_complementaires);
    }

    public function test_competition_detail_is_only_accessible_when_visible_for_current_demo_user_club(): void
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

        $invitation = Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'status' => Invitation::STATUS_PRE_INVITE,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Competition Sprint 1')
            ->assertDontSee('Retour à l’accueil')
            ->assertDontSee('Retour mes compétitions')
            ->assertSee('app-header-badge organizer', false)
            ->assertSee('Organisateur')
            ->assertSee('Club A');

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertNotFound();

        $invitation->markAsSent();

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Competition Sprint 1')
            ->assertSee('app-header-badge invited', false)
            ->assertSee('id="invitation"', false)
            ->assertSee('Invité')
            ->assertDontSee('role-badge participant', false)
            ->assertSee('Club A');

        $invitation->confirmParticipation();

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Competition Sprint 1')
            ->assertSee('app-header-badge invited', false)
            ->assertSee('Invité')
            ->assertSee('Club A');

        $this->withSession(['current_user_id' => $userC->id])
            ->get(route('competitions.show', $competition))
            ->assertNotFound();
    }
}
