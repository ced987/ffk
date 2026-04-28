<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Competition;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetitionIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_competitions_index_only_shows_competitions_visible_for_current_demo_user_club(): void
    {
        $clubA = Club::create(['name' => 'Club A']);
        $clubB = Club::create(['name' => 'Club B']);
        $clubC = Club::create(['name' => 'Club C']);
        $clubD = Club::create(['name' => 'Club D']);

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
        $userD = User::create([
            'club_id' => $clubD->id,
            'name' => 'Utilisateur Club D',
            'email' => 'club-d@example.test',
            'password' => 'password',
        ]);

        $competition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Competition Sprint 1',
            'date_competition' => today()->addDay()->toDateString(),
        ]);

        $demoCompetition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Competition Demo MVP',
        ]);

        Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Competition Passée',
            'date_competition' => today()->subDay()->toDateString(),
        ]);

        Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Competition Aujourd’hui',
            'date_competition' => today()->toDateString(),
        ]);

        $invitation = Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'status' => Invitation::STATUS_PRE_INVITE,
        ]);
        Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubC->id,
            'status' => Invitation::STATUS_INVITE,
        ]);

        $this->get(route('competitions.index'))
            ->assertOk();

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.index'))
            ->assertOk()
            ->assertSee('Compétitions à venir')
            ->assertSee('Compétitions passées')
            ->assertSeeInOrder(['Créer une compétition', 'Compétitions à venir', 'Competition Demo MVP', 'Competition Aujourd’hui', 'Competition Sprint 1', 'Compétitions passées', 'Competition Passée'])
            ->assertDontSee('Démo')
            ->assertSee('Competition Sprint 1')
            ->assertSee('Competition Passée')
            ->assertSee('Competition Aujourd’hui')
            ->assertDontSee('Organisateur : Club A')
            ->assertSee('📅 '.today()->addDay()->format('d/m/Y'))
            ->assertSee('📅 '.today()->format('d/m/Y'))
            ->assertSee('📅 '.today()->subDay()->format('d/m/Y'))
            ->assertSee('Date non renseignée')
            ->assertSee('badge-role-organizer', false)
            ->assertSee('Organisateur')
            ->assertSee('Actions : 1')
            ->assertSee('href="'.route('competitions.show', $competition).'#actions"', false)
            ->assertSee('href="'.route('competitions.show', $demoCompetition).'#poules"', false)
            ->assertSee('Gérer')
            ->assertDontSee('Participant')
            ->assertDontSee('Voir / gérer mes participants');

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.index'))
            ->assertOk()
            ->assertDontSee('Competition Sprint 1');

        $this->withSession(['current_user_id' => $userC->id])
            ->get(route('competitions.index'))
            ->assertOk()
            ->assertSee('Competition Sprint 1')
            ->assertSee('badge-role-invited', false)
            ->assertSee('Invité')
            ->assertSee('href="'.route('competitions.show', $competition).'#invitation"', false);

        $invitation->markAsSent();

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.index'))
            ->assertOk()
            ->assertSee('Competition Sprint 1')
            ->assertSee('🏢 Club A')
            ->assertSee('badge-role-invited', false)
            ->assertSee('Invité')
            ->assertSee('href="'.route('competitions.show', $competition).'#invitation"', false)
            ->assertSee('Actions : 1')
            ->assertSee('Voir')
            ->assertDontSee('Gérer');

        $invitation->confirmParticipation();

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.index'))
            ->assertOk()
            ->assertSee('Competition Sprint 1')
            ->assertSee('🏢 Club A')
            ->assertSee('badge-role-participant', false)
            ->assertSee('Participant')
            ->assertSee('href="'.route('competitions.show', $competition).'#participants"', false)
            ->assertSee('Actions : 1')
            ->assertSee('Voir')
            ->assertDontSee('Gérer');

        $this->withSession(['current_user_id' => $userD->id])
            ->get(route('competitions.index'))
            ->assertOk()
            ->assertDontSee('Competition Sprint 1');
    }
}
