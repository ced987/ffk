<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Competition;
use App\Models\InscriptionOperationnelle;
use App\Models\Licencie;
use App\Models\ParticipantSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicencieIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_club_sees_only_its_licencies(): void
    {
        [$clubA, $clubB, $userA] = $this->scenario();

        $licencie = Licencie::create([
            'club_id' => $clubA->id,
            'nom' => 'Martin',
            'prenom' => 'Lea',
            'date_naissance' => '2012-05-14',
            'sexe' => 'feminin',
            'poids' => 48,
        ]);
        Licencie::create([
            'club_id' => $clubB->id,
            'nom' => 'Durand',
            'prenom' => 'Noa',
            'date_naissance' => '2011-03-22',
            'sexe' => 'masculin',
            'poids' => 52,
        ]);

        $expectedAge = (string) \Carbon\Carbon::parse('2012-05-14')->age;

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('licencies.index'))
            ->assertOk()
            ->assertSee('Mes licenciés')
            ->assertSee('Martin')
            ->assertSee('Lea')
            ->assertSee('14/05/2012')
            ->assertSee('Âge')
            ->assertSee($expectedAge)
            ->assertSee('F')
            ->assertSee('48 kg')
            ->assertSee('Modifier')
            ->assertSee('href="'.route('licencies.edit', $licencie).'"', false)
            ->assertSee('Supprimer')
            ->assertSee('action="'.route('licencies.destroy', $licencie).'"', false)
            ->assertDontSee('Durand')
            ->assertDontSee('Noa');
    }

    public function test_club_b_does_not_see_club_a_licencies(): void
    {
        [$clubA, $clubB, , $userB] = $this->scenario();

        Licencie::create([
            'club_id' => $clubA->id,
            'nom' => 'Martin',
            'prenom' => 'Lea',
            'date_naissance' => '2012-05-14',
            'sexe' => 'feminin',
            'poids' => 48,
        ]);
        Licencie::create([
            'club_id' => $clubB->id,
            'nom' => 'Durand',
            'prenom' => 'Noa',
            'date_naissance' => '2011-03-22',
            'sexe' => 'masculin',
            'poids' => 52,
        ]);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('licencies.index'))
            ->assertOk()
            ->assertSee('Durand')
            ->assertSee('Noa')
            ->assertSee('52 kg')
            ->assertDontSee('Martin')
            ->assertDontSee('Lea');
    }

    public function test_home_page_links_to_current_club_licencies(): void
    {
        [$clubA, $clubB, $userA] = $this->scenario();

        Licencie::create([
            'club_id' => $clubA->id,
            'nom' => 'Martin',
            'prenom' => 'Lea',
            'date_naissance' => '2012-05-14',
            'sexe' => 'feminin',
            'poids' => 48,
        ]);
        Licencie::create([
            'club_id' => $clubA->id,
            'nom' => 'Bernard',
            'prenom' => 'Nina',
            'date_naissance' => '2013-01-10',
            'sexe' => 'feminin',
            'poids' => 44,
        ]);
        Licencie::create([
            'club_id' => $clubB->id,
            'nom' => 'Durand',
            'prenom' => 'Noa',
            'date_naissance' => '2011-03-22',
            'sexe' => 'masculin',
            'poids' => 52,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Mes licenciés')
            ->assertSee('Voir mes licenciés')
            ->assertSee('href="'.route('licencies.index').'"', false);
    }

    public function test_current_club_can_create_a_licencie(): void
    {
        [$clubA, , $userA] = $this->scenario();

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('licencies.index'))
            ->assertOk()
            ->assertSee('Ajouter un licencié')
            ->assertSee('href="'.route('licencies.create').'"', false);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('licencies.create'))
            ->assertOk()
            ->assertSee('Ajouter un licencié')
            ->assertSee('Masculin')
            ->assertSee('Féminin')
            ->assertSee('Poids');

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('licencies.store'), [
                'nom' => 'Moreau',
                'prenom' => 'Nora',
                'date_naissance' => '2012-10-05',
                'sexe' => 'feminin',
                'poids' => 44,
            ])
            ->assertRedirect(route('licencies.index'));

        $this->assertDatabaseHas('licencies', [
            'club_id' => $clubA->id,
            'nom' => 'Moreau',
            'prenom' => 'Nora',
            'date_naissance' => '2012-10-05 00:00:00',
            'sexe' => 'feminin',
            'poids' => 44,
        ]);
    }

    public function test_club_id_is_deduced_from_current_user_and_not_request_payload(): void
    {
        [$clubA, $clubB, $userA] = $this->scenario();

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('licencies.store'), [
                'club_id' => $clubB->id,
                'nom' => 'Payload',
                'prenom' => 'Ignore',
                'date_naissance' => '2011-01-15',
                'sexe' => 'masculin',
                'poids' => 50,
            ])
            ->assertRedirect(route('licencies.index'));

        $this->assertDatabaseHas('licencies', [
            'club_id' => $clubA->id,
            'nom' => 'Payload',
            'prenom' => 'Ignore',
        ]);
        $this->assertDatabaseMissing('licencies', [
            'club_id' => $clubB->id,
            'nom' => 'Payload',
            'prenom' => 'Ignore',
        ]);
    }

    public function test_licencie_creation_validates_required_fields_birth_date_and_sex(): void
    {
        [, , $userA] = $this->scenario();

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('licencies.store'), [])
            ->assertSessionHasErrors(['nom', 'prenom', 'date_naissance', 'sexe', 'poids']);

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('licencies.store'), [
                'nom' => 'Futur',
                'prenom' => 'Date',
                'date_naissance' => now()->addDay()->toDateString(),
                'sexe' => 'autre',
                'poids' => 0,
            ])
            ->assertSessionHasErrors(['date_naissance', 'sexe', 'poids']);

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('licencies.store'), [
                'nom' => 'Decimal',
                'prenom' => 'Poids',
                'date_naissance' => '2012-10-05',
                'sexe' => 'feminin',
                'poids' => 44.5,
            ])
            ->assertSessionHasErrors(['poids']);

        $this->assertDatabaseMissing('licencies', [
            'nom' => 'Futur',
            'prenom' => 'Date',
        ]);
    }

    public function test_current_club_can_update_own_licencie(): void
    {
        [$clubA, , $userA] = $this->scenario();

        $licencie = Licencie::create([
            'club_id' => $clubA->id,
            'nom' => 'Ancien',
            'prenom' => 'Prenom',
            'date_naissance' => '2012-05-14',
            'sexe' => 'feminin',
            'poids' => 48,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('licencies.edit', $licencie))
            ->assertOk()
            ->assertSee('Modifier un licencié')
            ->assertSee('Ancien')
            ->assertSee('Masculin')
            ->assertSee('Féminin')
            ->assertSee('48');

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('licencies.update', $licencie), [
                'nom' => 'Nouveau',
                'prenom' => 'Nom',
                'date_naissance' => '2011-09-18',
                'sexe' => 'masculin',
                'poids' => 51,
            ])
            ->assertRedirect(route('licencies.index'));

        $this->assertDatabaseHas('licencies', [
            'id' => $licencie->id,
            'club_id' => $clubA->id,
            'nom' => 'Nouveau',
            'prenom' => 'Nom',
            'date_naissance' => '2011-09-18 00:00:00',
            'sexe' => 'masculin',
            'poids' => 51,
        ]);
    }

    public function test_current_club_cannot_edit_or_update_another_club_licencie(): void
    {
        [, $clubB, $userA] = $this->scenario();

        $licencie = Licencie::create([
            'club_id' => $clubB->id,
            'nom' => 'Durand',
            'prenom' => 'Noa',
            'date_naissance' => '2011-03-22',
            'sexe' => 'masculin',
            'poids' => 52,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('licencies.edit', $licencie))
            ->assertForbidden();

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('licencies.update', $licencie), [
                'nom' => 'Tentative',
                'prenom' => 'Interdite',
                'date_naissance' => '2012-01-01',
                'sexe' => 'feminin',
                'poids' => 45,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('licencies', [
            'id' => $licencie->id,
            'club_id' => $clubB->id,
            'nom' => 'Durand',
            'prenom' => 'Noa',
        ]);
        $this->assertDatabaseMissing('licencies', [
            'id' => $licencie->id,
            'nom' => 'Tentative',
            'prenom' => 'Interdite',
        ]);
    }

    public function test_club_id_is_not_modifiable_when_updating_licencie(): void
    {
        [$clubA, $clubB, $userA] = $this->scenario();

        $licencie = Licencie::create([
            'club_id' => $clubA->id,
            'nom' => 'Original',
            'prenom' => 'Club',
            'date_naissance' => '2012-05-14',
            'sexe' => 'feminin',
            'poids' => 48,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('licencies.update', $licencie), [
                'club_id' => $clubB->id,
                'nom' => 'Payload',
                'prenom' => 'Ignore',
                'date_naissance' => '2010-04-12',
                'sexe' => 'masculin',
                'poids' => 57,
            ])
            ->assertRedirect(route('licencies.index'));

        $this->assertDatabaseHas('licencies', [
            'id' => $licencie->id,
            'club_id' => $clubA->id,
            'nom' => 'Payload',
            'prenom' => 'Ignore',
        ]);
        $this->assertDatabaseMissing('licencies', [
            'id' => $licencie->id,
            'club_id' => $clubB->id,
        ]);
    }

    public function test_licencie_update_validates_required_fields_birth_date_sex_and_weight(): void
    {
        [$clubA, , $userA] = $this->scenario();

        $licencie = Licencie::create([
            'club_id' => $clubA->id,
            'nom' => 'Stable',
            'prenom' => 'Valeur',
            'date_naissance' => '2012-05-14',
            'sexe' => 'feminin',
            'poids' => 48,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('licencies.update', $licencie), [])
            ->assertSessionHasErrors(['nom', 'prenom', 'date_naissance', 'sexe', 'poids']);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('licencies.update', $licencie), [
                'nom' => 'Futur',
                'prenom' => 'Date',
                'date_naissance' => now()->addDay()->toDateString(),
                'sexe' => 'autre',
                'poids' => 0,
            ])
            ->assertSessionHasErrors(['date_naissance', 'sexe', 'poids']);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('licencies.update', $licencie), [
                'nom' => 'Decimal',
                'prenom' => 'Poids',
                'date_naissance' => '2012-10-05',
                'sexe' => 'feminin',
                'poids' => 48.5,
            ])
            ->assertSessionHasErrors(['poids']);

        $this->assertDatabaseHas('licencies', [
            'id' => $licencie->id,
            'nom' => 'Stable',
            'prenom' => 'Valeur',
            'date_naissance' => '2012-05-14 00:00:00',
            'sexe' => 'feminin',
            'poids' => 48,
        ]);
    }

    public function test_current_club_can_update_own_club_name(): void
    {
        [$clubA, $clubB, $userA] = $this->scenario();

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('licencies.index'))
            ->assertOk()
            ->assertSee('Mon club')
            ->assertSee('action="'.route('club.update').'"', false)
            ->assertSee('Club A');

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('club.update'), [
                'name' => 'Club A Renommé',
                'club_id' => $clubB->id,
            ])
            ->assertRedirect(route('licencies.index'));

        $this->assertDatabaseHas('clubs', [
            'id' => $clubA->id,
            'name' => 'Club A Renommé',
        ]);
        $this->assertDatabaseHas('clubs', [
            'id' => $clubB->id,
            'name' => 'Club B',
        ]);
    }

    public function test_club_name_update_validates_required_name(): void
    {
        [, , $userA] = $this->scenario();

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('club.update'), [
                'name' => '',
            ])
            ->assertSessionHasErrors(['name']);
    }

    public function test_current_club_can_delete_own_licencie_without_deleting_existing_participant(): void
    {
        [$clubA, , $userA] = $this->scenario();

        $competition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Competition test',
        ]);
        $licencie = Licencie::create([
            'club_id' => $clubA->id,
            'nom' => 'Martin',
            'prenom' => 'Lea',
            'date_naissance' => '2012-05-14',
            'sexe' => 'feminin',
            'poids' => 48,
        ]);
        $participant = ParticipantSource::create([
            'club_id' => $clubA->id,
            'licencie_id' => $licencie->id,
            'last_name' => 'Martin',
            'first_name' => 'Lea',
            'sex' => 'feminin',
            'age' => 14,
            'approximate_weight' => 48,
            'license_number' => null,
        ]);
        $registration = InscriptionOperationnelle::create([
            'competition_id' => $competition->id,
            'club_id' => $clubA->id,
            'participant_source_id' => $participant->id,
            'is_active' => true,
            'is_validated' => false,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->delete(route('licencies.destroy', $licencie))
            ->assertRedirect(route('licencies.index'));

        $this->assertDatabaseMissing('licencies', [
            'id' => $licencie->id,
        ]);
        $this->assertDatabaseHas('participant_sources', [
            'id' => $participant->id,
            'club_id' => $clubA->id,
            'licencie_id' => null,
            'last_name' => 'Martin',
            'first_name' => 'Lea',
        ]);
        $this->assertDatabaseHas('inscription_operationnelles', [
            'id' => $registration->id,
            'competition_id' => $competition->id,
            'club_id' => $clubA->id,
            'participant_source_id' => $participant->id,
        ]);
    }

    public function test_current_club_cannot_delete_another_club_licencie(): void
    {
        [, $clubB, $userA] = $this->scenario();

        $licencie = Licencie::create([
            'club_id' => $clubB->id,
            'nom' => 'Durand',
            'prenom' => 'Noa',
            'date_naissance' => '2011-03-22',
            'sexe' => 'masculin',
            'poids' => 52,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->delete(route('licencies.destroy', $licencie))
            ->assertForbidden();

        $this->assertDatabaseHas('licencies', [
            'id' => $licencie->id,
            'club_id' => $clubB->id,
            'nom' => 'Durand',
            'prenom' => 'Noa',
        ]);
    }

    private function scenario(): array
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

        return [$clubA, $clubB, $userA, $userB];
    }
}
