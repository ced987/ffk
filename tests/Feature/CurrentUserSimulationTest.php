<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Competition;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrentUserSimulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_user_can_be_selected_from_seeded_users(): void
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
            'name' => 'Competition Demo MVP',
        ]);

        $hiddenCompetition = Competition::create([
            'organizer_club_id' => $clubB->id,
            'name' => 'Competition Cachee Club B',
        ]);

        Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'status' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Tableau de bord')
            ->assertSee('Actions à faire')
            ->assertSee('Prochaines compétitions')
            ->assertSee('Résumé')
            ->assertSee($userA->name)
            ->assertSee($clubA->name)
            ->assertSee($userA->name.' - '.$clubA->name)
            ->assertDontSee('Vous êtes')
            ->assertDontSee('Organisateur - Club A')
            ->assertSee('href="'.route('switch-user').'"', false)
            ->assertDontSee('Utilisateur courant')
            ->assertDontSee('Compétitions organisées par le club courant')
            ->assertDontSee('Utilisateur Club B')
            ->assertDontSee('Competition Cachee Club B');

        $this->get(route('demo.users.index'))
            ->assertOk()
            ->assertSee("Changer d'utilisateur", false)
            ->assertSee($userA->name)
            ->assertSee($clubA->name)
            ->assertSee($userB->name)
            ->assertSee($clubB->name)
            ->assertSee('Actuel')
            ->assertSee('href="'.route('demo.users.select', $userB).'"', false);

        $this->get(route('demo.users.select', $userB))
            ->assertRedirect(route('competitions.index'))
            ->assertSessionHas('current_user_id', $userB->id);

        $this->get('/')
            ->assertOk()
            ->assertSee($userB->name)
            ->assertSee($clubB->name)
            ->assertSee($userB->name.' - '.$clubB->name)
            ->assertDontSee('Participant - Club B')
            ->assertSee('Tableau de bord')
            ->assertDontSee('Voir la compétition de démonstration');

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.index'))
            ->assertOk()
            ->assertSee('Competition Demo MVP')
            ->assertSee('Voir')
            ->assertSee('Competition Cachee Club B')
            ->assertSeeInOrder(['Competition Demo MVP', 'Competition Cachee Club B']);
    }

    public function test_demo_club_name_can_be_updated_from_user_switch_page(): void
    {
        $clubA = Club::create(['name' => 'Club A']);

        User::create([
            'club_id' => $clubA->id,
            'name' => 'Utilisateur Club A',
            'email' => 'club-a@example.test',
            'password' => 'password',
        ]);

        $this->get(route('demo.users.index'))
            ->assertOk()
            ->assertSee('data-club-edit="'.$clubA->id.'"', false)
            ->assertSee('action="'.route('demo.clubs.update', $clubA).'"', false)
            ->assertSee('Club A');

        $this->patch(route('demo.clubs.update', $clubA), [
            'name' => 'Club A Renommé',
        ])
            ->assertRedirect(route('demo.users.index'));

        $this->assertDatabaseHas('clubs', [
            'id' => $clubA->id,
            'name' => 'Club A Renommé',
        ]);
    }
}
