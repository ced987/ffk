<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Combat;
use App\Models\Competition;
use App\Models\InscriptionOperationnelle;
use App\Models\Invitation;
use App\Models\ParticipantSource;
use App\Models\Poule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoulePreparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_organizer_can_create_draft_poule_for_own_competition(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.poules.store', $competition), [
                'name' => 'Poule Demo A',
            ])
            ->assertRedirect(route('competitions.show', $competition).'#creation-poule');

        $this->assertDatabaseHas('poules', [
            'competition_id' => $competition->id,
            'name' => 'Poule Demo A',
            'status' => Poule::STATUS_DRAFT,
        ]);

        $this->assertSame($clubA->id, $competition->organizer_club_id);
    }

    public function test_organizer_can_rename_poule_even_when_frozen(): void
    {
        [, , , $userA, , , $competition] = $this->scenario();
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Ancien nom',
            'status' => Poule::STATUS_FROZEN,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('title="Renommer la poule"', false)
            ->assertSee('✏️ Renommer')
            ->assertSee('action="'.route('competitions.poules.rename', [$competition, $poule]).'"', false);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.poules.rename', [$competition, $poule]), [
                'name' => 'Nouveau nom',
            ])
            ->assertRedirect(route('competitions.show', $competition).'#poules-figees')
            ->assertSessionHas('status', 'Poule renommée.');

        $this->assertSame('Nouveau nom', $poule->refresh()->name);
        $this->assertSame(Poule::STATUS_FROZEN, $poule->status);
    }

    public function test_non_organizer_cannot_rename_poule(): void
    {
        [, , , , $userB, , $competition] = $this->scenario();
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Club B',
            'status' => Poule::STATUS_DRAFT,
        ]);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertDontSee('action="'.route('competitions.poules.rename', [$competition, $poule]).'"', false);

        $this->withSession(['current_user_id' => $userB->id])
            ->patch(route('competitions.poules.rename', [$competition, $poule]), [
                'name' => 'Nom interdit',
            ])
            ->assertRedirect(route('competitions.show', $competition))
            ->assertSessionHas('status', 'Impossible : action réservée à l’organisateur');

        $this->assertSame('Poule Club B', $poule->refresh()->name);
    }

    public function test_rename_poule_requires_name(): void
    {
        [, , , $userA, , , $competition] = $this->scenario();
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Nom conserve',
            'status' => Poule::STATUS_DRAFT,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->from(route('competitions.show', $competition))
            ->patch(route('competitions.poules.rename', [$competition, $poule]), [
                'name' => '',
            ])
            ->assertRedirect(route('competitions.show', $competition))
            ->assertSessionHasErrors('name');

        $this->assertSame('Nom conserve', $poule->refresh()->name);
    }

    public function test_organizer_can_delete_poule_and_release_participants_and_delete_related_combats(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule a supprimer',
            'status' => Poule::STATUS_FROZEN,
        ]);
        $otherPoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule conservee',
            'status' => Poule::STATUS_FROZEN,
        ]);
        $first = $this->registerParticipant($competition, $clubA, 'Alpha', 'Aline', true, true);
        $second = $this->registerParticipant($competition, $clubA, 'Bravo', 'Boris', true, true);
        $third = $this->registerParticipant($competition, $clubA, 'Charlie', 'Chloe', true, true);
        $fourth = $this->registerParticipant($competition, $clubA, 'Delta', 'Dina', true, true);
        $first->update(['poule_id' => $poule->id]);
        $second->update(['poule_id' => $poule->id]);
        $third->update(['poule_id' => $otherPoule->id]);
        $fourth->update(['poule_id' => $otherPoule->id]);
        $combatToDelete = $this->createCombat($poule, $first, $second, 2, 1, Combat::STATUS_FINISHED);
        $combatToKeep = $this->createCombat($otherPoule, $third, $fourth, 0, 0, Combat::STATUS_FINISHED);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('action="'.route('competitions.poules.destroy', [$competition, $poule]).'"', false)
            ->assertSee('name="_method" value="DELETE"', false)
            ->assertSee('title="Supprimer la poule"', false)
            ->assertSee('🗑️ Supprimer')
            ->assertSee('Cette poule contient des combats. Cette action supprimera les combats liés à la poule.', false);

        $this->withSession(['current_user_id' => $userA->id])
            ->delete(route('competitions.poules.destroy', [$competition, $poule]))
            ->assertRedirect(route('competitions.show', $competition).'#poules-figees')
            ->assertSessionHas('status', 'Poule supprimée.');

        $this->assertDatabaseMissing('poules', ['id' => $poule->id]);
        $this->assertDatabaseMissing('combats', ['id' => $combatToDelete->id]);
        $this->assertDatabaseHas('combats', ['id' => $combatToKeep->id]);
        $this->assertNull($first->refresh()->poule_id);
        $this->assertNull($second->refresh()->poule_id);
        $this->assertSame($otherPoule->id, $third->refresh()->poule_id);
        $this->assertSame($otherPoule->id, $fourth->refresh()->poule_id);
    }

    public function test_non_organizer_cannot_delete_poule(): void
    {
        [$clubA, , , , $userB, , $competition] = $this->scenario();
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule protegee',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $registration = $this->registerParticipant($competition, $clubA, 'Affecte', 'Anna', true, true);
        $registration->update(['poule_id' => $poule->id]);

        $this->withSession(['current_user_id' => $userB->id])
            ->delete(route('competitions.poules.destroy', [$competition, $poule]))
            ->assertRedirect(route('competitions.show', $competition))
            ->assertSessionHas('status', 'Impossible : action réservée à l’organisateur');

        $this->assertDatabaseHas('poules', ['id' => $poule->id]);
        $this->assertSame($poule->id, $registration->refresh()->poule_id);
    }

    public function test_poule_action_get_urls_redirect_instead_of_returning_method_not_allowed(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule A',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $registration = $this->registerParticipant($competition, $clubA, 'Affecte', 'Lina', true, true);
        $registration->update(['poule_id' => $poule->id]);

        $this->withSession(['current_user_id' => $userA->id])
            ->get('/competitions/'.$competition->id.'/poules')
            ->assertRedirect(route('competitions.show', $competition))
            ->assertSessionHas('status', 'Utilisez le formulaire pour créer une poule.');

        $this->withSession(['current_user_id' => $userA->id])
            ->get('/competitions/'.$competition->id.'/poules/'.$poule->id.'/registrations')
            ->assertRedirect(route('competitions.show', $competition))
            ->assertSessionHas('status', 'Utilisez le formulaire pour affecter un participant.');

        $this->withSession(['current_user_id' => $userA->id])
            ->get('/competitions/'.$competition->id.'/registrations/'.$registration->id.'/withdraw-assignment')
            ->assertRedirect(route('competitions.show', $competition))
            ->assertSessionHas('status', 'Utilisez le formulaire pour retirer l affectation.');

        $this->assertSame($poule->id, $registration->refresh()->poule_id);
        $this->assertSame($clubA->id, $competition->organizer_club_id);
    }

    public function test_invited_club_and_non_invited_club_cannot_create_poule(): void
    {
        [, , , , $userB, $userC, $competition] = $this->scenario();

        $this->withSession(['current_user_id' => $userB->id])
            ->post(route('competitions.poules.store', $competition), [
                'name' => 'Poule Interdite Club B',
            ])
            ->assertForbidden();

        $this->withSession(['current_user_id' => $userC->id])
            ->post(route('competitions.poules.store', $competition), [
                'name' => 'Poule Interdite Club C',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('poules', 0);
    }

    public function test_competition_detail_lists_poules_and_only_eligible_current_competition_participants_for_organizer(): void
    {
        [$clubA, $clubB, , $userA, $userB, , $competition] = $this->scenario();
        $otherCompetition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Competition Isolation Poules',
        ]);

        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Existante',
            'status' => Poule::STATUS_DRAFT,
        ]);

        $eligibleA = $this->registerParticipant($competition, $clubA, 'Eligible', 'Alice', true, true);
        $eligibleB = $this->registerParticipant($competition, $clubB, 'Eligible', 'Boris', true, true);
        $notValidated = $this->registerParticipant($competition, $clubA, 'Nonvalide', 'Charlie', true, false);
        $withdrawn = $this->registerParticipant($competition, $clubB, 'Retire', 'Dina', false, false);
        $otherCompetitionRegistration = $this->registerParticipant($otherCompetition, $clubA, 'Autrecompetition', 'Eli', true, true);

        $eligibleRegistrationIds = $competition->eligiblePouleRegistrations()
            ->pluck('id')
            ->all();

        $this->assertContains($eligibleA->id, $eligibleRegistrationIds);
        $this->assertContains($eligibleB->id, $eligibleRegistrationIds);
        $this->assertNotContains($notValidated->id, $eligibleRegistrationIds);
        $this->assertNotContains($withdrawn->id, $eligibleRegistrationIds);
        $this->assertNotContains($otherCompetitionRegistration->id, $eligibleRegistrationIds);

        $eligibleA->update(['poule_id' => $poule->id]);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Poules')
            ->assertSee('Poule Existante')
            ->assertSee('🟡 En préparation')
            ->assertSee('data-poule-id="'.$poule->id.'"', false)
            ->assertSee('data-drop-enabled="true"', false)
            ->assertSee('data-assign-url="'.route('competitions.poules.registrations.store', [$competition, $poule]).'"', false)
            ->assertSee('poule-assignment-layout', false)
            ->assertSee('assignment-column', false)
            ->assertSee('1 participant(s)')
            ->assertDontSee('Participants affectés')
            ->assertSee('poule-participant-grid', false)
            ->assertSee('title="Retirer de la poule"', false)
            ->assertSee('data-remove-visual', false)
            ->assertSee('data-withdraw-url="'.route('competitions.registrations.withdraw-assignment', [$competition, $eligibleA]).'"', false)
            ->assertSee('data-inscription-id="'.$eligibleA->id.'"', false)
            ->assertSee('data-inscription-id="'.$eligibleB->id.'"', false)
            ->assertSee('draggable="true"', false)
            ->assertSee('data-source="available"', false)
            ->assertSee('method="POST"', false)
            ->assertSee('action="'.route('competitions.poules.store', $competition).'"', false)
            ->assertSee('Affectez les participants puis figez les poules pour générer les combats.')
            ->assertSee('<button type="submit">Créer une poule</button>', false)
            ->assertDontSee('<button type="submit">Affecter</button>', false)
            ->assertDontSee('href="'.route('competitions.poules.store', $competition).'"', false)
            ->assertDontSee('href="'.route('competitions.poules.registrations.store', [$competition, $competition->poules->first()]).'"', false)
            ->assertSee('Participants disponibles')
            ->assertSee('Participants validés, actifs et pas encore affectés à une poule.')
            ->assertSee('Eligible Alice')
            ->assertSee('('.$clubA->name.')')
            ->assertSee('Eligible')
            ->assertSee('Alice')
            ->assertSee('Boris')
            ->assertDontSee('Autrecompetition');

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertDontSee('Créer la poule')
            ->assertDontSee('Participants disponibles');
    }

    public function test_non_invited_club_cannot_see_competition_detail(): void
    {
        [, , , , , $userC, $competition] = $this->scenario();

        $this->withSession(['current_user_id' => $userC->id])
            ->get(route('competitions.show', $competition))
            ->assertNotFound();
    }

    public function test_organizer_can_assign_available_participant_to_draft_poule(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule A',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $registration = $this->registerParticipant($competition, $clubA, 'Disponible', 'Lina', true, true);

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.poules.registrations.store', [$competition, $poule]), [
                'registration_id' => $registration->id,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#participants-disponibles');

        $this->assertSame($poule->id, $registration->refresh()->poule_id);
        $this->assertNotContains($registration->id, $competition->eligiblePouleRegistrations()->pluck('id')->all());

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Poule A')
            ->assertSee('Disponible')
            ->assertSee('Lina');
    }

    public function test_assignment_requires_organizer_same_competition_draft_poule_and_available_registration(): void
    {
        [$clubA, $clubB, , $userA, $userB, , $competition] = $this->scenario();
        $otherCompetition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Competition Autre',
        ]);
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule A',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $otherPoule = Poule::create([
            'competition_id' => $otherCompetition->id,
            'name' => 'Poule Autre',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $closedPoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Non Brouillon',
            'status' => 'figee',
        ]);
        $available = $this->registerParticipant($competition, $clubA, 'Disponible', 'Nora', true, true);
        $notValidated = $this->registerParticipant($competition, $clubA, 'Nonvalide', 'Noa', true, false);
        $withdrawn = $this->registerParticipant($competition, $clubA, 'Retire', 'Malo', false, false);
        $alreadyAssigned = $this->registerParticipant($competition, $clubA, 'Affecte', 'Iris', true, true);
        $otherCompetitionRegistration = $this->registerParticipant($otherCompetition, $clubA, 'Autre', 'Competition', true, true);
        $alreadyAssigned->update(['poule_id' => $poule->id]);

        $this->withSession(['current_user_id' => $userB->id])
            ->post(route('competitions.poules.registrations.store', [$competition, $poule]), [
                'registration_id' => $available->id,
            ])
            ->assertForbidden();

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.poules.registrations.store', [$competition, $otherPoule]), [
                'registration_id' => $available->id,
            ])
            ->assertNotFound();

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.poules.registrations.store', [$competition, $closedPoule]), [
                'registration_id' => $available->id,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#participants-disponibles')
            ->assertSessionHas('status', 'Impossible : poule figée');

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.poules.registrations.store', [$competition, $poule]), [
                'registration_id' => $notValidated->id,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#participants-disponibles')
            ->assertSessionHas('status', 'Impossible : participant non validé');

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.poules.registrations.store', [$competition, $poule]), [
                'registration_id' => $withdrawn->id,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#participants-disponibles')
            ->assertSessionHas('status', 'Impossible : participation annulée');

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.poules.registrations.store', [$competition, $poule]), [
                'registration_id' => $alreadyAssigned->id,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#participants-disponibles')
            ->assertSessionHas('status', 'Impossible : participant déjà affecté');

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.poules.registrations.store', [$competition, $poule]), [
                'registration_id' => $otherCompetitionRegistration->id,
            ])
            ->assertForbidden();

        $this->assertNull($available->refresh()->poule_id);
        $this->assertNull($notValidated->refresh()->poule_id);
        $this->assertNull($withdrawn->refresh()->poule_id);
        $this->assertSame($poule->id, $alreadyAssigned->refresh()->poule_id);
        $this->assertNull($otherCompetitionRegistration->refresh()->poule_id);
    }

    public function test_deactivating_registration_clears_poule_assignment_centrally(): void
    {
        [$clubA, , , , , , $competition] = $this->scenario();
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule A',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $registration = $this->registerParticipant($competition, $clubA, 'Affecte', 'Puisretire', true, true);
        $registration->update(['poule_id' => $poule->id]);

        $registration->update(['is_active' => false]);

        $registration->refresh();

        $this->assertFalse($registration->is_active);
        $this->assertFalse($registration->is_validated);
        $this->assertNull($registration->poule_id);
    }

    public function test_organizer_can_withdraw_assignment_from_poule(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule A',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $registration = $this->registerParticipant($competition, $clubA, 'Affecte', 'Lina', true, true);
        $registration->update(['poule_id' => $poule->id]);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.registrations.withdraw-assignment', [$competition, $registration]))
            ->assertRedirect(route('competitions.show', $competition).'#poules-brouillon');

        $this->assertNull($registration->refresh()->poule_id);
        $this->assertContains($registration->id, $competition->eligiblePouleRegistrations()->pluck('id')->all());
    }

    public function test_organizer_can_move_assignment_to_another_draft_poule(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        $sourcePoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Source',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $targetPoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Cible',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $registration = $this->registerParticipant($competition, $clubA, 'Mobile', 'Nora', true, true);
        $registration->update(['poule_id' => $sourcePoule->id]);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.registrations.move-assignment', [$competition, $registration]), [
                'poule_id' => $targetPoule->id,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#poules-brouillon');

        $this->assertSame($targetPoule->id, $registration->refresh()->poule_id);
        $this->assertNotContains($registration->id, $competition->eligiblePouleRegistrations()->pluck('id')->all());

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Poule Cible')
            ->assertSee('Mobile')
            ->assertSee('Nora')
            ->assertSee('title="Retirer de la poule"', false)
            ->assertDontSee('Déplacer');
    }

    public function test_move_assignment_to_same_poule_is_refused(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule A',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $registration = $this->registerParticipant($competition, $clubA, 'Meme', 'Poule', true, true);
        $registration->update(['poule_id' => $poule->id]);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.registrations.move-assignment', [$competition, $registration]), [
                'poule_id' => $poule->id,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#poules-brouillon')
            ->assertSessionHas('status', 'Impossible : même poule');

        $this->assertSame($poule->id, $registration->refresh()->poule_id);
    }

    public function test_inactive_participant_is_not_movable_or_listed_as_available_or_movable(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        $sourcePoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Source',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $targetPoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Cible',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $registration = $this->registerParticipant($competition, $clubA, 'Inactif', 'Cache', true, true);
        $registration->update(['poule_id' => $sourcePoule->id]);
        $registration->update(['is_active' => false]);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.registrations.move-assignment', [$competition, $registration]), [
                'poule_id' => $targetPoule->id,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#participants-disponibles')
            ->assertSessionHas('status', 'Impossible : participation annulée');

        $this->assertNotContains($registration->id, $competition->eligiblePouleRegistrations()->pluck('id')->all());
        $this->assertFalse($sourcePoule->refresh()->registrations->contains('id', $registration->id));

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertDontSee('Retirer de la poule');

        $this->assertNull($registration->refresh()->poule_id);
    }

    public function test_invited_club_cannot_withdraw_or_move_assignment(): void
    {
        [$clubA, , , , $userB, , $competition] = $this->scenario();
        $sourcePoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Source',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $targetPoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Cible',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $registration = $this->registerParticipant($competition, $clubA, 'Affecte', 'Invitebloque', true, true);
        $registration->update(['poule_id' => $sourcePoule->id]);

        $this->withSession(['current_user_id' => $userB->id])
            ->patch(route('competitions.registrations.withdraw-assignment', [$competition, $registration]))
            ->assertForbidden();

        $this->withSession(['current_user_id' => $userB->id])
            ->patch(route('competitions.registrations.move-assignment', [$competition, $registration]), [
                'poule_id' => $targetPoule->id,
            ])
            ->assertForbidden();

        $this->assertSame($sourcePoule->id, $registration->refresh()->poule_id);
    }

    public function test_move_assignment_requires_same_competition_draft_poule_and_eligible_registration(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        $otherCompetition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Autre Competition',
        ]);
        $sourcePoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Source',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $targetPoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Cible',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $closedPoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Non Brouillon',
            'status' => 'figee',
        ]);
        $otherPoule = Poule::create([
            'competition_id' => $otherCompetition->id,
            'name' => 'Poule Autre',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $validRegistration = $this->registerParticipant($competition, $clubA, 'Valide', 'Affecte', true, true);
        $notValidated = $this->registerParticipant($competition, $clubA, 'Nonvalide', 'Affecte', true, false);
        $unassigned = $this->registerParticipant($competition, $clubA, 'Sans', 'Poule', true, true);
        $otherRegistration = $this->registerParticipant($otherCompetition, $clubA, 'Autre', 'Competition', true, true);
        $validRegistration->update(['poule_id' => $sourcePoule->id]);
        $notValidated->update(['poule_id' => $sourcePoule->id]);
        $otherRegistration->update(['poule_id' => $otherPoule->id]);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.registrations.move-assignment', [$competition, $validRegistration]), [
                'poule_id' => $otherPoule->id,
            ])
            ->assertForbidden();

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.registrations.move-assignment', [$competition, $validRegistration]), [
                'poule_id' => $closedPoule->id,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#poules-brouillon')
            ->assertSessionHas('status', 'Impossible : poule figée');

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.registrations.move-assignment', [$competition, $notValidated]), [
                'poule_id' => $targetPoule->id,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#participants-disponibles')
            ->assertSessionHas('status', 'Impossible : participant non validé');

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.registrations.move-assignment', [$competition, $unassigned]), [
                'poule_id' => $targetPoule->id,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#participants-disponibles')
            ->assertSessionHas('status', 'Impossible : participant déjà affecté');

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.registrations.move-assignment', [$competition, $otherRegistration]), [
                'poule_id' => $targetPoule->id,
            ])
            ->assertNotFound();

        $this->assertSame($sourcePoule->id, $validRegistration->refresh()->poule_id);
        $this->assertNull($notValidated->refresh()->poule_id);
        $this->assertNull($unassigned->refresh()->poule_id);
        $this->assertSame($otherPoule->id, $otherRegistration->refresh()->poule_id);
    }

    public function test_organizer_can_freeze_valid_draft_poule(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule A',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $first = $this->registerParticipant($competition, $clubA, 'Premier', 'Valide', true, true);
        $second = $this->registerParticipant($competition, $clubA, 'Deuxieme', 'Valide', true, true);
        $first->update(['poule_id' => $poule->id]);
        $second->update(['poule_id' => $poule->id]);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('id="freeze_poule_'.$poule->id.'"', false)
            ->assertSee('method="POST"', false)
            ->assertSee('action="'.route('competitions.poules.freeze', [$competition, $poule]).'"', false)
            ->assertSee('name="_method" value="PATCH"', false)
            ->assertSee('form="freeze_poule_'.$poule->id.'"', false)
            ->assertSee('formmethod="post"', false)
            ->assertSee('formaction="'.route('competitions.poules.freeze', [$competition, $poule]).'"', false)
            ->assertSee('title="Figer et générer combats"', false)
            ->assertSee('🔒⚔️ Figer')
            ->assertDontSee('Figer et générer les combats')
            ->assertDontSee('Générer les combats')
            ->assertDontSee('href="'.route('competitions.poules.freeze', [$competition, $poule]).'"', false);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.poules.freeze', [$competition, $poule]))
            ->assertRedirect(route('competitions.show', $competition).'#poules-brouillon');

        $this->assertSame(Poule::STATUS_FROZEN, $poule->refresh()->status);
        $this->assertDatabaseHas('combats', [
            'poule_id' => $poule->id,
            'inscription_a_id' => $first->id,
            'inscription_b_id' => $second->id,
            'ordre_combat' => 1,
            'statut' => Combat::STATUS_TO_ENTER,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Poule figée')
            ->assertSee('🟢 Figée')
            ->assertSee('2 participant(s)')
            ->assertSee('Défiger')
            ->assertDontSee('title="Retirer de la poule"', false)
            ->assertDontSee('Figer la poule');
    }

    public function test_competition_detail_shows_poule_guidance_states(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        $draftPoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule En Preparation',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $frozenPoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Figee',
            'status' => Poule::STATUS_FROZEN,
        ]);
        $available = $this->registerParticipant($competition, $clubA, 'Disponible', 'Alice', true, true);
        $assigned = $this->registerParticipant($competition, $clubA, 'Affecte', 'Boris', true, true);
        $this->registerParticipant($competition, $clubA, 'Attente', 'Nora', true, false);
        $assigned->update(['poule_id' => $frozenPoule->id]);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('⚠️ 1 participant(s) non validé(s) — continuer quand même ?')
            ->assertSee('👉 Voir les participants')
            ->assertSee('href="#participants"', false)
            ->assertSee('data-tab-link-target="participants"', false)
            ->assertSee('1 participant(s) non affecté(s)')
            ->assertSee('1 poule(s) en préparation (non figée(s))')
            ->assertDontSee('Poules prêtes — tous les participants sont affectés et les poules sont figées');

        $available->update(['poule_id' => $draftPoule->id]);
        $draftPoule->update(['status' => Poule::STATUS_FROZEN]);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Poules prêtes — tous les participants sont affectés et les poules sont figées');
    }

    public function test_organizer_can_unfreeze_poule_and_delete_combats_with_score_warning(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        [$poule, $first, $second] = $this->rankingScenario($competition, $clubA);
        $combat = $this->createCombat($poule, $first, $second, 3, 1, Combat::STATUS_FINISHED);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Défiger')
            ->assertSee('Des scores ont déjà été saisis. Cette action supprimera les combats et leurs résultats.')
            ->assertSeeInOrder([
                'Poule Classement',
                'Défiger',
                'Des scores ont déjà été saisis. Cette action supprimera les combats et leurs résultats.',
            ])
            ->assertSee('onsubmit="return confirm(\'Des scores ont déjà été saisis. Cette action supprimera les combats et leurs résultats.\')"', false);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.poules.unfreeze', [$competition, $poule]))
            ->assertRedirect(route('competitions.show', $competition).'#poules-figees')
            ->assertSessionHas('status', 'Poule remise en préparation.');

        $this->assertSame(Poule::STATUS_DRAFT, $poule->refresh()->status);
        $this->assertDatabaseMissing('combats', ['id' => $combat->id]);
        $this->assertSame($poule->id, $first->refresh()->poule_id);
        $this->assertSame($poule->id, $second->refresh()->poule_id);
    }

    public function test_freeze_form_uses_forwarded_https_url_behind_ngrok_proxy(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Ngrok',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $first = $this->registerParticipant($competition, $clubA, 'Premier', 'Ngrok', true, true);
        $second = $this->registerParticipant($competition, $clubA, 'Second', 'Ngrok', true, true);
        $first->update(['poule_id' => $poule->id]);
        $second->update(['poule_id' => $poule->id]);

        $expectedAction = 'https://surrogate-subtext-sabotage.ngrok-free.dev/competitions/'.$competition->id.'/poules/'.$poule->id.'/freeze';

        $this->withServerVariables([
            'HTTP_HOST' => 'surrogate-subtext-sabotage.ngrok-free.dev',
            'REMOTE_ADDR' => '127.0.0.1',
        ])
            ->withHeader('X-Forwarded-Proto', 'https')
            ->withHeader('X-Forwarded-Host', 'surrogate-subtext-sabotage.ngrok-free.dev')
            ->withSession(['current_user_id' => $userA->id])
            ->get('/competitions/'.$competition->id)
            ->assertOk()
            ->assertSee('action="'.$expectedAction.'"', false)
            ->assertSee('formaction="'.$expectedAction.'"', false)
            ->assertSee('method="POST"', false)
            ->assertSee('name="_method" value="PATCH"', false);
    }

    public function test_freezing_requires_organizer_draft_status_and_at_least_two_participants(): void
    {
        [$clubA, , , $userA, $userB, , $competition] = $this->scenario();
        $oneParticipantPoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Incomplete',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $frozenPoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Deja Figee',
            'status' => Poule::STATUS_FROZEN,
        ]);
        $participant = $this->registerParticipant($competition, $clubA, 'Seul', 'Participant', true, true);
        $participant->update(['poule_id' => $oneParticipantPoule->id]);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.poules.freeze', [$competition, $oneParticipantPoule]))
            ->assertRedirect(route('competitions.show', $competition).'#poules-brouillon')
            ->assertSessionHas('status', 'Impossible : minimum 2 participants');

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.poules.freeze', [$competition, $frozenPoule]))
            ->assertRedirect(route('competitions.show', $competition).'#poules-figees')
            ->assertSessionHas('status', 'Impossible : poule déjà figée');

        $this->withSession(['current_user_id' => $userB->id])
            ->patch(route('competitions.poules.freeze', [$competition, $oneParticipantPoule]))
            ->assertForbidden();

        $this->assertSame(Poule::STATUS_DRAFT, $oneParticipantPoule->refresh()->status);
        $this->assertSame(Poule::STATUS_FROZEN, $frozenPoule->refresh()->status);
    }

    public function test_no_assignment_changes_are_possible_after_poule_is_frozen(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        $frozenPoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Figee',
            'status' => Poule::STATUS_FROZEN,
        ]);
        $draftPoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Brouillon',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $assigned = $this->registerParticipant($competition, $clubA, 'Affecte', 'Fige', true, true);
        $available = $this->registerParticipant($competition, $clubA, 'Disponible', 'Valide', true, true);
        $assigned->update(['poule_id' => $frozenPoule->id]);

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.poules.registrations.store', [$competition, $frozenPoule]), [
                'registration_id' => $available->id,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#participants-disponibles')
            ->assertSessionHas('status', 'Impossible : poule figée');

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.registrations.withdraw-assignment', [$competition, $assigned]))
            ->assertRedirect(route('competitions.show', $competition).'#poules-figees')
            ->assertSessionHas('status', 'Impossible : poule figée');

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.registrations.move-assignment', [$competition, $assigned]), [
                'poule_id' => $draftPoule->id,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#poules-figees')
            ->assertSessionHas('status', 'Impossible : poule figée');

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.participants.withdraw', [$competition, $assigned]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-valides')
            ->assertSessionHas('status', 'Impossible : participant dans une poule figée');

        $this->assertSame($frozenPoule->id, $assigned->refresh()->poule_id);
        $this->assertTrue($assigned->is_active);
        $this->assertNull($available->refresh()->poule_id);
    }

    public function test_organizer_can_generate_round_robin_combats_for_frozen_poule(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Figee',
            'status' => Poule::STATUS_FROZEN,
        ]);
        $first = $this->registerParticipant($competition, $clubA, 'Alpha', 'Aline', true, true);
        $second = $this->registerParticipant($competition, $clubA, 'Bravo', 'Boris', true, true);
        $third = $this->registerParticipant($competition, $clubA, 'Charlie', 'Chloe', true, true);
        foreach ([$third, $first, $second] as $registration) {
            $registration->update(['poule_id' => $poule->id]);
        }

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.poules.combats.generate', [$competition, $poule]))
            ->assertRedirect(route('competitions.show', $competition).'#poules-figees');

        $combats = Combat::where('poule_id', $poule->id)
            ->orderBy('ordre_combat')
            ->get();

        $this->assertCount(3, $combats);
        $this->assertSame([$second->id, $first->id, $first->id], $combats->pluck('inscription_a_id')->all());
        $this->assertSame([$third->id, $third->id, $second->id], $combats->pluck('inscription_b_id')->all());
        $this->assertSame([1, 2, 3], $combats->pluck('ordre_combat')->all());
        $this->assertSame([
            Combat::STATUS_TO_ENTER,
            Combat::STATUS_TO_ENTER,
            Combat::STATUS_TO_ENTER,
        ], $combats->pluck('statut')->all());

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Combats')
            ->assertSee('Alpha Aline')
            ->assertSee('vs')
            ->assertSee('Bravo Boris')
            ->assertSee('À saisir')
            ->assertDontSee('Générer les combats');
    }

    public function test_generated_combats_are_ordered_by_round_without_repeating_participant_in_same_round(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Tours',
            'status' => Poule::STATUS_FROZEN,
        ]);
        $first = $this->registerParticipant($competition, $clubA, 'Premier', 'A', true, true);
        $second = $this->registerParticipant($competition, $clubA, 'Deuxieme', 'B', true, true);
        $third = $this->registerParticipant($competition, $clubA, 'Troisieme', 'C', true, true);
        $fourth = $this->registerParticipant($competition, $clubA, 'Quatrieme', 'D', true, true);

        foreach ([$first, $second, $third, $fourth] as $registration) {
            $registration->update(['poule_id' => $poule->id]);
        }

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.poules.combats.generate', [$competition, $poule]))
            ->assertRedirect(route('competitions.show', $competition).'#poules-figees');

        $combats = Combat::where('poule_id', $poule->id)
            ->orderBy('ordre_combat')
            ->get();

        $this->assertCount(6, $combats);
        $this->assertSame([1, 2, 3, 4, 5, 6], $combats->pluck('ordre_combat')->all());

        $pairs = $combats
            ->map(fn (Combat $combat) => [$combat->inscription_a_id, $combat->inscription_b_id])
            ->all();

        $this->assertSame([
            [$first->id, $fourth->id],
            [$second->id, $third->id],
            [$first->id, $third->id],
            [$second->id, $fourth->id],
            [$first->id, $second->id],
            [$third->id, $fourth->id],
        ], $pairs);
        $this->assertCount(6, collect($pairs)->map(fn (array $pair) => implode('-', $pair))->unique());

        foreach ($combats->chunk(2) as $roundCombats) {
            $participantsInRound = $roundCombats
                ->flatMap(fn (Combat $combat) => [$combat->inscription_a_id, $combat->inscription_b_id])
                ->all();

            $this->assertCount(count($participantsInRound), array_unique($participantsInRound));
        }
    }

    public function test_competition_detail_groups_combats_by_poule_with_local_numbering(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        $pouleA = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Alpha',
            'status' => Poule::STATUS_FROZEN,
        ]);
        $pouleB = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Beta',
            'status' => Poule::STATUS_FROZEN,
        ]);
        $pouleEmpty = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Vide',
            'status' => Poule::STATUS_DRAFT,
        ]);

        $alphaOne = $this->registerParticipant($competition, $clubA, 'Aone', 'Alice', true, true);
        $alphaTwo = $this->registerParticipant($competition, $clubA, 'Atwo', 'Aline', true, true);
        $alphaThree = $this->registerParticipant($competition, $clubA, 'Athree', 'Anna', true, true);
        $betaOne = $this->registerParticipant($competition, $clubA, 'Bone', 'Boris', true, true);
        $betaTwo = $this->registerParticipant($competition, $clubA, 'Btwo', 'Bella', true, true);

        foreach ([$alphaOne, $alphaTwo, $alphaThree] as $registration) {
            $registration->update(['poule_id' => $pouleA->id]);
        }
        foreach ([$betaOne, $betaTwo] as $registration) {
            $registration->update(['poule_id' => $pouleB->id]);
        }

        $this->createCombat($pouleA, $alphaOne, $alphaTwo, 3, 1, Combat::STATUS_FINISHED);
        $this->createCombat($pouleA, $alphaOne, $alphaThree, null, null, Combat::STATUS_TO_ENTER);
        $this->createCombat($pouleB, $betaOne, $betaTwo, null, null, Combat::STATUS_TO_ENTER);

        $response = $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Poule Alpha')
            ->assertSee('Poule Beta')
            ->assertSee('Poule Vide')
            ->assertSee('Aone')
            ->assertSee('Atwo')
            ->assertSee('Athree')
            ->assertSee('Bone')
            ->assertSee('Btwo')
            ->assertSee('value="3"', false)
            ->assertSee('value="1"', false)
            ->assertSee('🟥')
            ->assertSee('Imprimer résultat poule')
            ->assertDontSee('Imprimer poule')
            ->assertSee(route('competitions.poules.print', [$competition, $pouleA]), false)
            ->assertSee('Imprimer la feuille combats')
            ->assertSee('⚠️ En cours — 1 combat à saisir')
            ->assertSee('Feuille combats - '.$competition->name)
            ->assertSee('<th>Poule</th>', false)
            ->assertSee('<th>Rouge</th>', false)
            ->assertSee('<th>Bleu</th>', false)
            ->assertSee('<th>Nul</th>', false)
            ->assertSee('<th>Non fait</th>', false)
            ->assertSee('<th>Score rouge</th>', false)
            ->assertSee('<th>Score bleu</th>', false)
            ->assertSee('<th>Commentaire</th>', false)
            ->assertSee('[ ]')
            ->assertSee('Alpha')
            ->assertSee('Aone')
            ->assertSee('Atwo')
            ->assertSee('____')
            ->assertSee('____________')
            ->assertSee('Poule Alpha');

        $html = $response->getContent();
        $combatsSectionPosition = strpos($html, '<section id="combats"');
        $alphaPosition = strpos($html, 'Poule Alpha', $combatsSectionPosition);
        $alphaFirstCombatPosition = strpos($html, 'Aone', $alphaPosition);
        $alphaSecondCombatPosition = strpos($html, 'Athree', $alphaPosition);
        $betaPosition = strpos($html, 'Poule Beta', $combatsSectionPosition);
        $betaCombatPosition = strpos($html, 'Bone', $betaPosition);
        $rankingSectionPosition = strpos($html, '<section class="tab-panel" data-tab-panel="combats">', $combatsSectionPosition + 1);

        $this->assertLessThan($alphaFirstCombatPosition, $alphaPosition);
        $this->assertLessThan($alphaSecondCombatPosition, $alphaFirstCombatPosition);
        $this->assertLessThan($betaPosition, $alphaSecondCombatPosition);
        $this->assertLessThan($betaCombatPosition, $betaPosition);

        $alphaSection = substr($html, $alphaPosition, $betaPosition - $alphaPosition);
        $betaSection = substr($html, $betaPosition, $rankingSectionPosition - $betaPosition);
        $combatsSection = substr($html, $combatsSectionPosition, $rankingSectionPosition - $combatsSectionPosition);

        $this->assertStringContainsString('#1', $alphaSection);
        $this->assertStringContainsString('#2', $alphaSection);
        $this->assertStringContainsString('#1', $betaSection);
        $this->assertStringNotContainsString('Bone', $alphaSection);
        $this->assertStringNotContainsString('Aone', $betaSection);
        $this->assertStringContainsString('Poule Vide', $combatsSection);
        $this->assertStringContainsString('Aucun combat généré, poule non figée', $combatsSection);
    }

    public function test_competition_detail_combats_tab_shows_draft_poules_without_ranking_results(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule En Préparation',
            'status' => Poule::STATUS_DRAFT,
        ]);

        $response = $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Poule En Préparation')
            ->assertSee('Aucun combat généré, poule non figée');

        $html = $response->getContent();
        $combatsSectionPosition = strpos($html, '<section id="combats"');
        $rankingSectionPosition = strpos($html, '<section class="tab-panel" data-tab-panel="combats">', $combatsSectionPosition + 1);
        $printSheetPosition = strpos($html, '<section class="print-sheet"', $combatsSectionPosition);
        $combatsSectionEnd = $rankingSectionPosition === false ? $printSheetPosition : $rankingSectionPosition;
        $combatsSection = substr($html, $combatsSectionPosition, $combatsSectionEnd - $combatsSectionPosition);
        $afterCombatsSection = substr($html, $combatsSectionEnd, $printSheetPosition - $combatsSectionEnd);

        $this->assertStringContainsString('Poule En Préparation', $combatsSection);
        $this->assertStringNotContainsString('Poule En Préparation', $afterCombatsSection);
    }

    public function test_poule_print_page_shows_ranking_and_field_fight_sheet(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        [$poule, $first, $second, $third] = $this->rankingScenario($competition, $clubA);

        $combatOne = $this->createCombat($poule, $first, $second, 3, 1, Combat::STATUS_FINISHED);
        $combatOne->update(['commentaire' => 'Final rapide']);
        $this->createCombat($poule, $first, $third, null, null, Combat::STATUS_TO_ENTER);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.poules.print', [$competition, $poule]))
            ->assertOk()
            ->assertSee($competition->name)
            ->assertSee($poule->name)
            ->assertSee('Classement')
            ->assertSee('<th>#</th>', false)
            ->assertSee('<th>Participant</th>', false)
            ->assertSee('<th>Club</th>', false)
            ->assertSee('<th>J</th>', false)
            ->assertSee('<th>V</th>', false)
            ->assertSee('<th>N</th>', false)
            ->assertSee('<th>D</th>', false)
            ->assertSee('<th>NF</th>', false)
            ->assertSee('<th>Points</th>', false)
            ->assertSee('Combats')
            ->assertSee('<th>Combattant 1</th>', false)
            ->assertSee('<th>Combattant 2</th>', false)
            ->assertSee('<th>Nul</th>', false)
            ->assertSee('<th>Non fait</th>', false)
            ->assertSee('[X]')
            ->assertSee('[ ]')
            ->assertSee('<strong>', false)
            ->assertSee('Bravo')
            ->assertSee('Boris')
            ->assertSee('3 / 1')
            ->assertSee('Final rapide')
            ->assertSee('____ / ____')
            ->assertSee('____________')
            ->assertSee('window.print()', false);
    }

    public function test_combat_generation_requires_organizer_frozen_poule_two_participants_and_no_existing_combats(): void
    {
        [$clubA, , , $userA, $userB, , $competition] = $this->scenario();
        $draftPoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Brouillon',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $singlePoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Seule',
            'status' => Poule::STATUS_FROZEN,
        ]);
        $readyPoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Prete',
            'status' => Poule::STATUS_FROZEN,
        ]);
        $first = $this->registerParticipant($competition, $clubA, 'Premier', 'Valide', true, true);
        $second = $this->registerParticipant($competition, $clubA, 'Second', 'Valide', true, true);
        $single = $this->registerParticipant($competition, $clubA, 'Unique', 'Valide', true, true);
        $first->update(['poule_id' => $readyPoule->id]);
        $second->update(['poule_id' => $readyPoule->id]);
        $single->update(['poule_id' => $singlePoule->id]);

        $this->withSession(['current_user_id' => $userB->id])
            ->post(route('competitions.poules.combats.generate', [$competition, $readyPoule]))
            ->assertRedirect(route('competitions.show', $competition).'#poules-figees')
            ->assertSessionHas('status', 'Impossible : action réservée à l’organisateur');

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.poules.combats.generate', [$competition, $draftPoule]))
            ->assertRedirect(route('competitions.show', $competition).'#poules-brouillon')
            ->assertSessionHas('status', 'Impossible : poule non figée');

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.poules.combats.generate', [$competition, $singlePoule]))
            ->assertRedirect(route('competitions.show', $competition).'#poules-figees')
            ->assertSessionHas('status', 'Impossible : minimum 2 participants');

        Combat::create([
            'poule_id' => $readyPoule->id,
            'inscription_a_id' => $first->id,
            'inscription_b_id' => $second->id,
            'ordre_combat' => 1,
            'statut' => Combat::STATUS_TO_ENTER,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.poules.combats.generate', [$competition, $readyPoule]))
            ->assertRedirect(route('competitions.show', $competition).'#poules-figees')
            ->assertSessionHas('status', 'Impossible : combats déjà générés');

        $this->assertDatabaseCount('combats', 1);
    }

    public function test_combat_generation_uses_only_active_validated_registrations_in_same_poule(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Figee',
            'status' => Poule::STATUS_FROZEN,
        ]);
        $otherPoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Autre Poule',
            'status' => Poule::STATUS_FROZEN,
        ]);
        $first = $this->registerParticipant($competition, $clubA, 'Eligible', 'Un', true, true);
        $second = $this->registerParticipant($competition, $clubA, 'Eligible', 'Deux', true, true);
        $notValidated = $this->registerParticipant($competition, $clubA, 'Nonvalide', 'Trois', true, false);
        $withdrawn = $this->registerParticipant($competition, $clubA, 'Retire', 'Quatre', false, false);
        $otherPouleRegistration = $this->registerParticipant($competition, $clubA, 'Autrepoule', 'Cinq', true, true);
        $first->update(['poule_id' => $poule->id]);
        $second->update(['poule_id' => $poule->id]);
        $notValidated->update(['poule_id' => $poule->id]);
        $withdrawn->update(['poule_id' => $poule->id]);
        $otherPouleRegistration->update(['poule_id' => $otherPoule->id]);

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.poules.combats.generate', [$competition, $poule]))
            ->assertRedirect(route('competitions.show', $competition).'#poules-figees');

        $combat = Combat::firstOrFail();

        $this->assertSame($first->id, $combat->inscription_a_id);
        $this->assertSame($second->id, $combat->inscription_b_id);
        $this->assertDatabaseCount('combats', 1);
        $this->assertDatabaseMissing('combats', ['inscription_a_id' => $notValidated->id]);
        $this->assertDatabaseMissing('combats', ['inscription_b_id' => $notValidated->id]);
        $this->assertDatabaseMissing('combats', ['inscription_a_id' => $withdrawn->id]);
        $this->assertDatabaseMissing('combats', ['inscription_b_id' => $withdrawn->id]);
        $this->assertDatabaseMissing('combats', ['inscription_a_id' => $otherPouleRegistration->id]);
        $this->assertDatabaseMissing('combats', ['inscription_b_id' => $otherPouleRegistration->id]);
    }

    public function test_organizer_can_select_result_and_finish_combat(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        $combat = $this->combatScenario($competition, $clubA);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Alpha Aline')
            ->assertSee('Bravo Boris')
            ->assertSee('🟥')
            ->assertSee('title="Nul"', false)
            ->assertSee('🟦')
            ->assertSee('title="Pas de combat"', false)
            ->assertSee('action="'.route('competitions.combats.update', [$competition, $combat]).'"', false)
            ->assertSee('id="combat-'.$combat->id.'"', false)
            ->assertSee('data-combat-validate', false)
            ->assertSee('data-combat-edit disabled', false)
            ->assertSee('data-combat-clear disabled', false)
            ->assertDontSee('href="'.route('competitions.combats.edit', [$competition, $combat]).'"', false);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.combats.update', [$competition, $combat]), [
                'resultat' => Combat::RESULT_LEFT_WIN,
                'score_a' => 12,
                'score_b' => 4,
                'commentaire' => 'Combat maitrise',
            ])
            ->assertRedirect(route('competitions.show', $competition).'#combat-'.$combat->id);

        $combat->refresh();

        $this->assertSame(Combat::RESULT_LEFT_WIN, $combat->resultat);
        $this->assertSame(12, $combat->score_a);
        $this->assertSame(4, $combat->score_b);
        $this->assertSame('12 - 4', $combat->score_texte);
        $this->assertSame('Combat maitrise', $combat->commentaire);
        $this->assertFalse($combat->absence_forfait);
        $this->assertSame(Combat::STATUS_FINISHED, $combat->statut);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('value="12"', false)
            ->assertSee('value="4"', false)
            ->assertSee('Terminé')
            ->assertSee('winner', false)
            ->assertSee('muted', false)
            ->assertSee('is-finished', false)
            ->assertSee('data-combat-validate disabled', false)
            ->assertSee('data-combat-edit', false)
            ->assertSee('data-combat-clear', false)
            ->assertSee('Modifier')
            ->assertSee('Effacer');
    }

    public function test_organizer_can_enter_draw_and_modify_finished_combat_result(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        $combat = $this->combatScenario($competition, $clubA);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.combats.update', [$competition, $combat]), [
                'resultat' => Combat::RESULT_DRAW,
                'score_a' => 2,
                'score_b' => 2,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#combat-'.$combat->id);

        $this->assertSame(Combat::RESULT_DRAW, $combat->refresh()->resultat);
        $this->assertSame(2, $combat->score_a);
        $this->assertSame(2, $combat->score_b);
        $this->assertSame('2 - 2', $combat->score_texte);
        $this->assertSame(Combat::STATUS_FINISHED, $combat->statut);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.combats.update', [$competition, $combat]), [
                'resultat' => Combat::RESULT_RIGHT_WIN,
                'score_a' => 0,
                'score_b' => 4,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#combat-'.$combat->id);

        $combat->refresh();

        $this->assertSame(Combat::RESULT_RIGHT_WIN, $combat->resultat);
        $this->assertSame(0, $combat->score_a);
        $this->assertSame(4, $combat->score_b);
        $this->assertSame('0 - 4', $combat->score_texte);
        $this->assertSame(Combat::STATUS_FINISHED, $combat->statut);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.combats.update', [$competition, $combat]), [
                'action' => 'clear',
            ])
            ->assertRedirect(route('competitions.show', $competition).'#combat-'.$combat->id);

        $combat->refresh();

        $this->assertNull($combat->resultat);
        $this->assertNull($combat->score_a);
        $this->assertNull($combat->score_b);
        $this->assertNull($combat->score_texte);
        $this->assertSame(Combat::STATUS_TO_ENTER, $combat->statut);
    }

    public function test_score_entry_requires_organizer_and_valid_result(): void
    {
        [$clubA, , , $userA, $userB, , $competition] = $this->scenario();
        $combat = $this->combatScenario($competition, $clubA);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.combats.edit', [$competition, $combat]))
            ->assertRedirect(route('competitions.show', $competition))
            ->assertSessionHas('status', 'Impossible : action réservée à l’organisateur');

        $this->withSession(['current_user_id' => $userB->id])
            ->from(route('competitions.show', $competition))
            ->patch(route('competitions.combats.update', [$competition, $combat]), [
                'resultat' => Combat::RESULT_LEFT_WIN,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#combat-'.$combat->id)
            ->assertSessionHas('status', 'Impossible : action réservée à l’organisateur');

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertDontSee('🟥 Victoire gauche')
            ->assertDontSee('🟦 Victoire droite')
            ->assertDontSee('Générer les combats');

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.combats.update', [$competition, $combat]), [
                'score_a' => 1,
                'score_b' => 0,
            ])
            ->assertSessionHasErrors('resultat');

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.combats.update', [$competition, $combat]), [
                'resultat' => 'score_technique',
            ])
            ->assertSessionHasErrors('resultat');

        $combat->refresh();

        $this->assertNull($combat->resultat);
        $this->assertNull($combat->score_texte);
        $this->assertSame(Combat::STATUS_TO_ENTER, $combat->statut);
    }

    public function test_poule_ranking_counts_wins_draws_and_losses(): void
    {
        [$clubA, , , , , , $competition] = $this->scenario();
        [$poule, $first, $second, $third] = $this->rankingScenario($competition, $clubA);

        $this->createCombat($poule, $first, $second, 3, 0, Combat::STATUS_FINISHED);
        $this->createCombat($poule, $first, $third, 1, 1, Combat::STATUS_FINISHED);
        $this->createCombat($poule, $second, $third, 0, 2, Combat::STATUS_FINISHED);

        $ranking = $poule->ranking();

        $this->assertSame([$first->id, $third->id, $second->id], $ranking->pluck('registration.id')->all());
        $this->assertSame([4, 4, 0], $ranking->pluck('points')->all());
        $this->assertSame([1, 1, 3], $ranking->pluck('rank')->all());
        $this->assertSame([2, 2, 2], $ranking->pluck('played')->all());
        $this->assertSame([1, 1, 0], $ranking->pluck('wins')->all());
        $this->assertSame([1, 1, 0], $ranking->pluck('draws')->all());
        $this->assertSame([0, 0, 2], $ranking->pluck('losses')->all());
        $this->assertSame([0, 0, 0], $ranking->pluck('no_contests')->all());
    }

    public function test_poule_ranking_uses_result_not_optional_score(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        [$poule, $first, $second, $third] = $this->rankingScenario($competition, $clubA);

        $combat = $this->createCombat($poule, $first, $second, null, null, Combat::STATUS_TO_ENTER);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.combats.update', [$competition, $combat]), [
                'resultat' => Combat::RESULT_LEFT_WIN,
                'score_a' => 0,
                'score_b' => 99,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#combat-'.$combat->id);

        $ranking = $poule->ranking();

        $this->assertSame([$first->id, $second->id, $third->id], $ranking->pluck('registration.id')->all());
        $this->assertSame([3, 0, 0], $ranking->pluck('points')->all());
        $this->assertSame([1, 2, 2], $ranking->pluck('rank')->all());
        $this->assertSame([1, 1, 0], $ranking->pluck('played')->all());
        $this->assertSame([1, 0, 0], $ranking->pluck('wins')->all());
        $this->assertSame([0, 0, 0], $ranking->pluck('draws')->all());
        $this->assertSame([0, 1, 0], $ranking->pluck('losses')->all());

        $this->createCombat($poule, $first, $third, null, null, Combat::STATUS_FINISHED)
            ->update(['resultat' => Combat::RESULT_NO_CONTEST]);

        $ranking = $poule->ranking();

        $this->assertSame([2, 1, 1], $ranking->pluck('played')->all());
        $this->assertSame([1, 0, 1], $ranking->pluck('no_contests')->all());
    }

    public function test_poule_ranking_is_stable_by_registration_id_and_ignores_unfinished_combats(): void
    {
        [$clubA, , , , , , $competition] = $this->scenario();
        [$poule, $first, $second, $third] = $this->rankingScenario($competition, $clubA);

        $this->createCombat($poule, $first, $second, 2, 0, Combat::STATUS_FINISHED);
        $this->createCombat($poule, $second, $third, null, null, Combat::STATUS_TO_ENTER);

        $ranking = $poule->ranking();

        $this->assertSame([$first->id, $second->id, $third->id], $ranking->pluck('registration.id')->all());
        $this->assertSame([3, 0, 0], $ranking->pluck('points')->all());
        $this->assertSame([1, 2, 2], $ranking->pluck('rank')->all());
    }

    public function test_poule_ranking_lists_all_participants_with_zero_points_when_no_finished_combat(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        [$poule, $first, $second, $third] = $this->rankingScenario($competition, $clubA);

        $this->createCombat($poule, $first, $second, null, null, Combat::STATUS_TO_ENTER);

        $ranking = $poule->ranking();

        $this->assertSame([$first->id, $second->id, $third->id], $ranking->pluck('registration.id')->all());
        $this->assertSame([0, 0, 0], $ranking->pluck('points')->all());
        $this->assertSame([1, 1, 1], $ranking->pluck('rank')->all());

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Classement')
            ->assertSee('J = joués · V = victoires · N = nuls · D = défaites · NF = non faits')
            ->assertSee('Alpha Aline')
            ->assertSee('Bravo Boris')
            ->assertSee('Charlie Chloe')
            ->assertSee('0');
    }

    private function scenario(): array
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
            'name' => 'Competition Sprint 10',
        ]);

        Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'status' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
        ]);

        return [$clubA, $clubB, $clubC, $userA, $userB, $userC, $competition];
    }

    private function registerParticipant(
        Competition $competition,
        Club $club,
        string $lastName,
        string $firstName,
        bool $isActive,
        bool $isValidated,
    ): InscriptionOperationnelle {
        $participant = ParticipantSource::create([
            'club_id' => $club->id,
            'last_name' => $lastName,
            'first_name' => $firstName,
            'sex' => 'F',
            'age' => 14,
            'approximate_weight' => 48.5,
            'license_number' => null,
        ]);

        return InscriptionOperationnelle::create([
            'competition_id' => $competition->id,
            'club_id' => $club->id,
            'participant_source_id' => $participant->id,
            'is_active' => $isActive,
            'is_validated' => $isValidated,
        ]);
    }

    private function combatScenario(Competition $competition, Club $club): Combat
    {
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Combat',
            'status' => Poule::STATUS_FROZEN,
        ]);
        $first = $this->registerParticipant($competition, $club, 'Alpha', 'Aline', true, true);
        $second = $this->registerParticipant($competition, $club, 'Bravo', 'Boris', true, true);
        $first->update(['poule_id' => $poule->id]);
        $second->update(['poule_id' => $poule->id]);

        return Combat::create([
            'poule_id' => $poule->id,
            'inscription_a_id' => $first->id,
            'inscription_b_id' => $second->id,
            'ordre_combat' => 1,
            'statut' => Combat::STATUS_TO_ENTER,
        ]);
    }

    private function rankingScenario(Competition $competition, Club $club): array
    {
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Classement',
            'status' => Poule::STATUS_FROZEN,
        ]);
        $first = $this->registerParticipant($competition, $club, 'Alpha', 'Aline', true, true);
        $second = $this->registerParticipant($competition, $club, 'Bravo', 'Boris', true, true);
        $third = $this->registerParticipant($competition, $club, 'Charlie', 'Chloe', true, true);

        foreach ([$first, $second, $third] as $registration) {
            $registration->update(['poule_id' => $poule->id]);
        }

        return [$poule, $first, $second, $third];
    }

    private function createCombat(
        Poule $poule,
        InscriptionOperationnelle $registrationA,
        InscriptionOperationnelle $registrationB,
        ?int $scoreA,
        ?int $scoreB,
        string $status,
    ): Combat {
        $result = null;

        if ($status === Combat::STATUS_FINISHED && $scoreA !== null && $scoreB !== null) {
            $result = match (true) {
                $scoreA > $scoreB => Combat::RESULT_LEFT_WIN,
                $scoreB > $scoreA => Combat::RESULT_RIGHT_WIN,
                default => Combat::RESULT_DRAW,
            };
        }

        return Combat::create([
            'poule_id' => $poule->id,
            'inscription_a_id' => $registrationA->id,
            'inscription_b_id' => $registrationB->id,
            'ordre_combat' => Combat::where('poule_id', $poule->id)->count() + 1,
            'statut' => $status,
            'resultat' => $result,
            'score_a' => $scoreA,
            'score_b' => $scoreB,
            'score_texte' => $scoreA !== null && $scoreB !== null ? $scoreA.' - '.$scoreB : null,
        ]);
    }
}
