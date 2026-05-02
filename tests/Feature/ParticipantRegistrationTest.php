<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Competition;
use App\Models\InscriptionOperationnelle;
use App\Models\Invitation;
use App\Models\Licencie;
use App\Models\ParticipantSource;
use App\Models\Poule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ParticipantRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_invited_club_can_register_a_participant_for_current_competition_and_club(): void
    {
        [$clubA, $clubB, , $userA, $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);

        $this->withSession(['current_user_id' => $userB->id])
            ->post(route('competitions.participants.store', $competition), $this->participantPayload())
            ->assertRedirect(route('competitions.show', $competition).'#participants-ajout');

        $participant = ParticipantSource::firstOrFail();
        $registration = InscriptionOperationnelle::firstOrFail();

        $this->assertSame($clubB->id, $participant->club_id);
        $this->assertSame($competition->id, $registration->competition_id);
        $this->assertSame($clubB->id, $registration->club_id);
        $this->assertSame($participant->id, $registration->participant_source_id);
        $this->assertTrue($registration->is_active);
        $this->assertFalse($registration->is_validated);

        $this->assertDatabaseHas('participant_sources', [
            'club_id' => $clubB->id,
            'last_name' => 'Martin',
            'first_name' => 'Lea',
            'sex' => 'F',
            'age' => 14,
            'license_number' => 'LIC-123',
        ]);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Participants de mon club :')
            ->assertSee('1 actif(s)')
            ->assertSee('Martin Lea');

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Participants actifs')
            ->assertSee('Club B')
            ->assertSee('1 actif(s)')
            ->assertSee('Martin')
            ->assertSee('Lea');
    }

    public function test_confirmed_club_sees_only_its_licencies_when_adding_participant(): void
    {
        [$clubA, $clubB, , , $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);

        Licencie::create([
            'club_id' => $clubA->id,
            'nom' => 'Organisateur',
            'prenom' => 'Alice',
            'date_naissance' => '2012-03-12',
            'sexe' => 'feminin',
            'poids' => 43,
        ]);
        $licencieB = Licencie::create([
            'club_id' => $clubB->id,
            'nom' => 'Invite',
            'prenom' => 'Boris',
            'date_naissance' => '2011-09-04',
            'sexe' => 'masculin',
            'poids' => 57,
        ]);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Ajouter depuis mes licenciés')
            ->assertSee('Ajouter ce licencié')
            ->assertSee('Invite Boris')
            ->assertSee($licencieB->date_naissance->age.' ans')
            ->assertSee('57 kg')
            ->assertDontSee('Organisateur Alice');
    }

    public function test_confirmed_club_can_create_participant_from_own_licencie(): void
    {
        [$clubA, $clubB, , $userA, $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);

        $licencie = Licencie::create([
            'club_id' => $clubB->id,
            'nom' => 'Diaz',
            'prenom' => 'Dina',
            'date_naissance' => '2013-08-09',
            'sexe' => 'feminin',
            'poids' => 40,
        ]);

        $this->withSession(['current_user_id' => $userB->id])
            ->post(route('competitions.participants.store-from-licencie', $competition), [
                'licencie_id' => $licencie->id,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#participants-ajout');

        $participant = ParticipantSource::firstOrFail();
        $registration = InscriptionOperationnelle::firstOrFail();

        $this->assertSame($clubB->id, $participant->club_id);
        $this->assertSame($licencie->id, $participant->licencie_id);
        $this->assertSame('Diaz', $participant->last_name);
        $this->assertSame('Dina', $participant->first_name);
        $this->assertSame('F', $participant->sex);
        $this->assertSame($licencie->date_naissance->age, $participant->age);
        $this->assertEquals(40, $participant->approximate_weight);
        $this->assertNull($participant->license_number);
        $this->assertSame($competition->id, $registration->competition_id);
        $this->assertSame($clubB->id, $registration->club_id);
        $this->assertSame($participant->id, $registration->participant_source_id);
        $this->assertTrue($registration->is_active);
        $this->assertFalse($registration->is_validated);

        $this->assertDatabaseHas('licencies', [
            'id' => $licencie->id,
            'club_id' => $clubB->id,
            'nom' => 'Diaz',
            'prenom' => 'Dina',
            'poids' => 40,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Club B')
            ->assertSee('Diaz')
            ->assertSee('Dina')
            ->assertSee('40');
    }

    public function test_same_licencie_cannot_be_added_twice_to_same_competition(): void
    {
        [$clubA, $clubB, , $userA, $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);

        $licencie = Licencie::create([
            'club_id' => $clubB->id,
            'nom' => 'Diaz',
            'prenom' => 'Dina',
            'date_naissance' => '2013-08-09',
            'sexe' => 'feminin',
            'poids' => 40,
        ]);

        $this->withSession(['current_user_id' => $userB->id])
            ->post(route('competitions.participants.store-from-licencie', $competition), [
                'licencie_id' => $licencie->id,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#participants-ajout');

        $this->withSession(['current_user_id' => $userB->id])
            ->post(route('competitions.participants.store-from-licencie', $competition), [
                'licencie_id' => $licencie->id,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#participants-ajout')
            ->assertSessionHas('status', 'Ce licencié est déjà inscrit à la compétition.');

        $this->assertDatabaseCount('participant_sources', 1);
        $this->assertDatabaseCount('inscription_operationnelles', 1);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Diaz Dina')
            ->assertSee('Déjà inscrit');

        $otherCompetition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Autre Competition',
        ]);
        Invitation::create([
            'competition_id' => $otherCompetition->id,
            'club_id' => $clubB->id,
            'status' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
        ]);

        $this->withSession(['current_user_id' => $userB->id])
            ->post(route('competitions.participants.store-from-licencie', $otherCompetition), [
                'licencie_id' => $licencie->id,
            ])
            ->assertRedirect(route('competitions.show', $otherCompetition).'#participants-ajout');

        $this->assertDatabaseCount('participant_sources', 2);
        $this->assertDatabaseCount('inscription_operationnelles', 2);
    }

    public function test_club_cannot_create_participant_from_another_club_licencie(): void
    {
        [$clubA, $clubB, , , $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);

        $licencie = Licencie::create([
            'club_id' => $clubA->id,
            'nom' => 'Organisateur',
            'prenom' => 'Alice',
            'date_naissance' => '2012-03-12',
            'sexe' => 'feminin',
            'poids' => 43,
        ]);

        $this->withSession(['current_user_id' => $userB->id])
            ->post(route('competitions.participants.store-from-licencie', $competition), [
                'licencie_id' => $licencie->id,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('participant_sources', 0);
        $this->assertDatabaseCount('inscription_operationnelles', 0);
    }

    public function test_only_participation_confirmed_club_can_register_participant(): void
    {
        foreach ([Invitation::STATUS_INVITE, Invitation::STATUS_PARTICIPATION_DECLINED] as $index => $status) {
            [, , , , $userB, , $competition] = $this->scenario($status, ' '.$index);

            $this->withSession(['current_user_id' => $userB->id])
                ->post(route('competitions.participants.store', $competition), $this->participantPayload())
                ->assertForbidden();

            $this->assertDatabaseCount('participant_sources', 0);
            $this->assertDatabaseCount('inscription_operationnelles', 0);
        }
    }

    public function test_organizer_can_register_own_participant_for_own_competition(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.participants.store', $competition), $this->participantPayload())
            ->assertRedirect(route('competitions.show', $competition).'#participants-ajout');

        $participant = ParticipantSource::firstOrFail();
        $registration = InscriptionOperationnelle::firstOrFail();

        $this->assertSame($clubA->id, $participant->club_id);
        $this->assertSame($clubA->id, $registration->club_id);
        $this->assertSame($competition->id, $registration->competition_id);
    }

    public function test_organizer_can_choose_invited_club_when_registering_participant(): void
    {
        [$clubA, $clubB, $clubC, $userA, $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);

        $response = $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('id="participant_club_id"', false)
            ->assertSee('name="club_id"', false)
            ->assertSee('<option value="'.$clubA->id.'" selected>', false)
            ->assertSee($clubB->name);

        $content = $response->getContent();
        $formStart = strpos($content, 'action="'.route('competitions.participants.store', $competition).'"');
        $this->assertNotFalse($formStart);
        $formEnd = strpos($content, '</form>', $formStart);
        $this->assertNotFalse($formEnd);
        $participantForm = substr($content, $formStart, $formEnd - $formStart);
        $this->assertStringContainsString('<option value="'.$clubA->id.'" selected>', $participantForm);
        $this->assertStringContainsString('<option value="'.$clubB->id.'"', $participantForm);
        $this->assertStringNotContainsString('<option value="'.$clubC->id.'"', $participantForm);

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.participants.store', $competition), [
                ...$this->participantPayload(),
                'club_id' => $clubB->id,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#participants-ajout');

        $participant = ParticipantSource::firstOrFail();
        $registration = InscriptionOperationnelle::firstOrFail();

        $this->assertSame($clubB->id, $participant->club_id);
        $this->assertSame($clubB->id, $registration->club_id);

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.participants.store', $competition), [
                ...$this->participantPayload(),
                'last_name' => 'Noninvite',
                'first_name' => 'Nina',
                'club_id' => $clubC->id,
            ])
            ->assertSessionHasErrors(['club_id']);
    }

    public function test_invited_club_does_not_see_or_control_participant_club_field(): void
    {
        [$clubA, $clubB, , , $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertDontSee('id="participant_club_id"', false);

        $this->withSession(['current_user_id' => $userB->id])
            ->post(route('competitions.participants.store', $competition), [
                ...$this->participantPayload(),
                'club_id' => $clubA->id,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#participants-ajout');

        $participant = ParticipantSource::firstOrFail();
        $registration = InscriptionOperationnelle::firstOrFail();

        $this->assertSame($clubB->id, $participant->club_id);
        $this->assertSame($clubB->id, $registration->club_id);
    }

    public function test_non_invited_club_cannot_register_participant(): void
    {
        [, , , , , $userC, $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);

        $this->withSession(['current_user_id' => $userC->id])
            ->post(route('competitions.participants.store', $competition), $this->participantPayload())
            ->assertForbidden();

        $this->assertDatabaseCount('participant_sources', 0);
        $this->assertDatabaseCount('inscription_operationnelles', 0);
    }

    public function test_confirmed_club_sees_own_participants_and_organizer_sees_read_only_grouped_details(): void
    {
        [$clubA, $clubB, , $userA, $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);

        $organizerParticipant = ParticipantSource::create([
            'club_id' => $clubA->id,
            'last_name' => 'Durand',
            'first_name' => 'Paul',
            'sex' => 'M',
            'age' => 16,
            'approximate_weight' => 61.0,
            'license_number' => null,
        ]);

        $invitedParticipant = ParticipantSource::create([
            'club_id' => $clubB->id,
            'last_name' => 'Martin',
            'first_name' => 'Lea',
            'sex' => 'F',
            'age' => 14,
            'approximate_weight' => 48.5,
            'license_number' => 'LIC-123',
        ]);

        InscriptionOperationnelle::create([
            'competition_id' => $competition->id,
            'club_id' => $clubA->id,
            'participant_source_id' => $organizerParticipant->id,
        ]);

        InscriptionOperationnelle::create([
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'participant_source_id' => $invitedParticipant->id,
        ]);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Participants de mon club :')
            ->assertSee('1 actif(s)')
            ->assertSee('Participants de mon club')
            ->assertSee('Martin Lea')
            ->assertSee('En attente de validation')
            ->assertSee('Modifier')
            ->assertSee('Retirer')
            ->assertDontSee('Durand Paul');

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Participants actifs')
            ->assertSee('Organisateur -')
            ->assertSee('Club B')
            ->assertSee('1 actif(s)')
            ->assertSee('Durand Paul')
            ->assertSee('Martin')
            ->assertSee('Lea')
            ->assertSee('F')
            ->assertSee('14')
            ->assertSee('48.5')
            ->assertSee('LIC-123')
            ->assertSee('En attente de validation')
            ->assertSee('Valider')
            ->assertDontSee('Supprimer');
    }

    public function test_organizer_can_validate_and_unvalidate_active_participant(): void
    {
        [$clubA, $clubB, , $userA, $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $registration = $this->registerParticipant($competition, $clubB);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.participants.validate', [$competition, $registration]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-non-valides');

        $this->assertTrue($registration->refresh()->is_validated);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Participant validé')
            ->assertDontSee('Dévalider');

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Participant validé')
            ->assertSee('Retirer')
            ->assertDontSee('Dévalider');

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.participants.unvalidate', [$competition, $registration]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-valides');

        $this->assertFalse($registration->refresh()->is_validated);
    }

    public function test_unvalidating_participant_assigned_to_draft_poule_clears_assignment(): void
    {
        [, $clubB, , $userA, , , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Brouillon',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $registration = $this->registerParticipant($competition, $clubB, [
            'is_validated' => true,
        ]);
        $registration->update(['poule_id' => $poule->id]);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.participants.unvalidate', [$competition, $registration]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-valides');

        $registration->refresh();

        $this->assertFalse($registration->is_validated);
        $this->assertNull($registration->poule_id);
        $this->assertDatabaseMissing('inscription_operationnelles', [
            'id' => $registration->id,
            'is_validated' => false,
            'poule_id' => $poule->id,
        ]);
    }

    public function test_unvalidating_participant_assigned_to_frozen_poule_is_blocked_with_explicit_message(): void
    {
        [, $clubB, , $userA, , , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Figée',
            'status' => Poule::STATUS_FROZEN,
        ]);
        $registration = $this->registerParticipant($competition, $clubB, [
            'is_validated' => true,
        ]);
        $registration->update(['poule_id' => $poule->id]);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.participants.unvalidate', [$competition, $registration]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-valides')
            ->assertSessionHas('status', 'Impossible : participant dans une poule figée');

        $registration->refresh();

        $this->assertTrue($registration->is_validated);
        $this->assertSame($poule->id, $registration->poule_id);
    }

    public function test_only_organizer_can_validate_and_reactivate_inactive_participant(): void
    {
        [, $clubB, , $userA, $userB, $userC, $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $activeRegistration = $this->registerParticipant($competition, $clubB);
        $inactiveRegistration = $this->registerParticipant($competition, $clubB, [
            'is_active' => false,
        ]);

        $this->withSession(['current_user_id' => $userB->id])
            ->patch(route('competitions.participants.validate', [$competition, $activeRegistration]))
            ->assertForbidden();

        $this->withSession(['current_user_id' => $userC->id])
            ->patch(route('competitions.participants.validate', [$competition, $activeRegistration]))
            ->assertForbidden();

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.participants.validate', [$competition, $inactiveRegistration]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-retires')
            ->assertSessionHas('status', 'Participant validé.');

        $this->assertFalse($activeRegistration->refresh()->is_validated);
        $inactiveRegistration->refresh();
        $this->assertTrue($inactiveRegistration->is_active);
        $this->assertTrue($inactiveRegistration->is_validated);
    }

    public function test_unvalidating_inactive_participant_redirects_with_explicit_message(): void
    {
        [, $clubB, , $userA, , , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $inactiveRegistration = $this->registerParticipant($competition, $clubB, [
            'is_active' => false,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.participants.unvalidate', [$competition, $inactiveRegistration]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-retires')
            ->assertSessionHas('status', 'Impossible : participation annulée');

        $this->assertFalse($inactiveRegistration->refresh()->is_active);
        $this->assertFalse($inactiveRegistration->is_validated);
    }

    public function test_withdrawing_participant_forces_validation_to_false(): void
    {
        [, $clubB, , , $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $registration = $this->registerParticipant($competition, $clubB, [
            'is_validated' => true,
        ]);

        $this->withSession(['current_user_id' => $userB->id])
            ->patch(route('competitions.participants.withdraw', [$competition, $registration]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-valides');

        $registration->refresh();

        $this->assertFalse($registration->is_active);
        $this->assertFalse($registration->is_validated);
        $this->assertDatabaseMissing('inscription_operationnelles', [
            'id' => $registration->id,
            'is_active' => false,
            'is_validated' => true,
        ]);
    }

    public function test_validation_summary_counts_active_participants_globally_and_by_club(): void
    {
        [$clubA, $clubB, , $userA, , , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);

        $this->registerParticipant($competition, $clubA, [
            'last_name' => 'Actif',
            'first_name' => 'Valide',
            'is_validated' => true,
        ]);
        $this->registerParticipant($competition, $clubA, [
            'last_name' => 'Actif',
            'first_name' => 'Nonvalide',
            'is_validated' => false,
        ]);
        $this->registerParticipant($competition, $clubA, [
            'last_name' => 'Annule',
            'first_name' => 'Organisateur',
            'is_active' => false,
            'is_validated' => false,
        ]);
        $this->registerParticipant($competition, $clubB, [
            'last_name' => 'Invite',
            'first_name' => 'Valide',
            'is_validated' => true,
        ]);
        $this->registerParticipant($competition, $clubB, [
            'last_name' => 'Invite',
            'first_name' => 'Annule',
            'is_active' => false,
            'is_validated' => false,
        ]);

        $summary = $competition->participantValidationSummary();

        $this->assertSame([
            'active' => 3,
            'validated' => 2,
            'not_validated' => 1,
        ], $summary['global']);
        $this->assertSame([
            'active' => 2,
            'validated' => 1,
            'not_validated' => 1,
        ], $summary['by_club']->get($clubA->id));
        $this->assertSame([
            'active' => 1,
            'validated' => 1,
            'not_validated' => 0,
        ], $summary['by_club']->get($clubB->id));

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Participants actifs')
            ->assertSee('Participants validés par le club organisateur')
            ->assertSee('En attente de validation par le club organisateur')
            ->assertSee('3')
            ->assertSee('2 actif(s),')
            ->assertSee('1 participant(s) validé(s),')
            ->assertSee('1 en attente de validation')
            ->assertSee('1 actif(s),')
            ->assertSee('0 en attente de validation');
    }

    public function test_owner_can_withdraw_participant_without_physical_deletion_and_counts_ignore_it(): void
    {
        [$clubA, $clubB, , $userA, $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $organizerRegistration = $this->registerParticipant($competition, $clubA, [
            'last_name' => 'Visible',
            'first_name' => 'Anna',
        ]);
        $withdrawnRegistration = $this->registerParticipant($competition, $clubB, [
            'last_name' => 'Cache',
            'first_name' => 'Basile',
        ]);

        $this->withSession(['current_user_id' => $userB->id])
            ->patch(route('competitions.participants.withdraw', [$competition, $withdrawnRegistration]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-non-valides');

        $withdrawnRegistration->refresh();

        $this->assertFalse($withdrawnRegistration->is_active);
        $this->assertDatabaseCount('participant_sources', 2);
        $this->assertDatabaseCount('inscription_operationnelles', 2);
        $this->assertDatabaseHas('inscription_operationnelles', [
            'id' => $withdrawnRegistration->id,
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'participant_source_id' => $withdrawnRegistration->participant_source_id,
            'is_active' => false,
        ]);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Participants de mon club :')
            ->assertSee('0 actif(s)')
            ->assertSee('Cache Basile')
            ->assertSee('Retiré')
            ->assertSee('Réactiver')
            ->assertDontSee('Modifier')
            ->assertDontSee('Retirer');

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Visible')
            ->assertSee('Anna')
            ->assertSee('Organisateur -')
            ->assertSee('Club B')
            ->assertSee('0 actif(s)')
            ->assertSee('Cache')
            ->assertSee('Basile')
            ->assertSee('Retiré');

        $this->assertTrue($organizerRegistration->refresh()->is_active);
    }

    public function test_withdrawing_already_cancelled_participant_redirects_with_explicit_message(): void
    {
        [, $clubB, , , $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $registration = $this->registerParticipant($competition, $clubB, [
            'is_active' => false,
        ]);

        $this->withSession(['current_user_id' => $userB->id])
            ->patch(route('competitions.participants.withdraw', [$competition, $registration]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-retires')
            ->assertSessionHas('status', 'Impossible : participation annulée');

        $this->assertFalse($registration->refresh()->is_active);
    }

    public function test_withdrawing_participant_in_frozen_poule_redirects_with_explicit_message(): void
    {
        [, $clubB, , , $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Figée',
            'status' => Poule::STATUS_FROZEN,
        ]);
        $registration = $this->registerParticipant($competition, $clubB, [
            'last_name' => 'Bloque',
            'first_name' => 'Fige',
            'is_validated' => true,
        ]);
        $registration->update(['poule_id' => $poule->id]);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Bloque Fige')
            ->assertDontSee(route('competitions.participants.withdraw', [$competition, $registration]), false);

        $this->withSession(['current_user_id' => $userB->id])
            ->patch(route('competitions.participants.withdraw', [$competition, $registration]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-valides')
            ->assertSessionHas('status', 'Impossible : participant dans une poule figée');

        $registration->refresh();

        $this->assertTrue($registration->is_active);
        $this->assertSame($poule->id, $registration->poule_id);
    }

    public function test_owner_can_reactivate_withdrawn_participant_and_counts_include_it_again(): void
    {
        [$clubA, $clubB, , $userA, $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $registration = $this->registerParticipant($competition, $clubB, [
            'last_name' => 'Retour',
            'first_name' => 'Mina',
            'is_active' => false,
        ]);

        $this->withSession(['current_user_id' => $userB->id])
            ->patch(route('competitions.participants.reactivate', [$competition, $registration]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-retires');

        $this->assertTrue($registration->refresh()->is_active);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Participants de mon club :')
            ->assertSee('1 actif(s)')
            ->assertSee('Retour Mina')
            ->assertSee('Modifier')
            ->assertSee('Retirer')
            ->assertDontSee('Retiré')
            ->assertDontSee('Réactiver');

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Participants actifs')
            ->assertSee('Club B')
            ->assertSee('1 actif(s)')
            ->assertSee('Retour')
            ->assertSee('Mina')
            ->assertSee('En attente de validation');

        $this->assertDatabaseHas('inscription_operationnelles', [
            'id' => $registration->id,
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'participant_source_id' => $registration->participant_source_id,
            'is_active' => true,
        ]);
    }

    public function test_reactivating_active_participant_redirects_with_explicit_message(): void
    {
        [, $clubB, , , $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $registration = $this->registerParticipant($competition, $clubB);

        $this->withSession(['current_user_id' => $userB->id])
            ->patch(route('competitions.participants.reactivate', [$competition, $registration]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-non-valides')
            ->assertSessionHas('status', 'Impossible : participant déjà actif');

        $this->assertTrue($registration->refresh()->is_active);
    }

    public function test_reactivate_button_is_hidden_and_action_redirects_when_withdrawn_participant_is_in_frozen_poule(): void
    {
        [, $clubB, , , $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Figée',
            'status' => Poule::STATUS_FROZEN,
        ]);
        $registration = $this->registerParticipant($competition, $clubB, [
            'last_name' => 'Annule',
            'first_name' => 'Fige',
            'is_active' => false,
        ]);

        DB::table('inscription_operationnelles')
            ->where('id', $registration->id)
            ->update(['poule_id' => $poule->id]);

        $registration->refresh();

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Annule Fige')
            ->assertDontSee(route('competitions.participants.reactivate', [$competition, $registration]), false);

        $this->withSession(['current_user_id' => $userB->id])
            ->patch(route('competitions.participants.reactivate', [$competition, $registration]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-retires')
            ->assertSessionHas('status', 'Impossible : participant dans une poule figée');

        $this->assertFalse($registration->refresh()->is_active);
        $this->assertSame($poule->id, $registration->poule_id);
    }

    public function test_only_owner_can_reactivate_withdrawn_participant(): void
    {
        [$clubA, $clubB, , $userA, , $userC, $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $registration = $this->registerParticipant($competition, $clubB, [
            'is_active' => false,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.participants.reactivate', [$competition, $registration]))
            ->assertForbidden();

        $this->withSession(['current_user_id' => $userC->id])
            ->patch(route('competitions.participants.reactivate', [$competition, $registration]))
            ->assertForbidden();

        $this->assertFalse($registration->refresh()->is_active);
        $this->assertDatabaseMissing('inscription_operationnelles', [
            'club_id' => $clubA->id,
            'is_active' => true,
        ]);
    }

    public function test_organizer_can_withdraw_any_participant_in_competition(): void
    {
        [$clubA, $clubB, , $userA, , , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $organizerRegistration = $this->registerParticipant($competition, $clubA);
        $invitedRegistration = $this->registerParticipant($competition, $clubB);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.participants.withdraw', [$competition, $organizerRegistration]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-non-valides');

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.participants.withdraw', [$competition, $invitedRegistration]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-non-valides');

        $this->assertFalse($organizerRegistration->refresh()->is_active);
        $this->assertFalse($invitedRegistration->refresh()->is_active);
    }

    public function test_invited_club_must_still_be_confirmed_to_withdraw_own_participant(): void
    {
        foreach ([Invitation::STATUS_INVITE, Invitation::STATUS_PARTICIPATION_DECLINED] as $index => $status) {
            [$clubA, $clubB, , , $userB, , $competition] = $this->scenario($status, ' withdraw '.$index);
            $registration = $this->registerParticipant($competition, $clubB);

            $this->withSession(['current_user_id' => $userB->id])
                ->patch(route('competitions.participants.withdraw', [$competition, $registration]))
                ->assertForbidden();

            $this->assertTrue($registration->refresh()->is_active);
            $this->assertDatabaseMissing('inscription_operationnelles', [
                'club_id' => $clubA->id,
                'is_active' => false,
            ]);
        }
    }

    public function test_non_invited_club_cannot_withdraw_another_club_participant(): void
    {
        [, $clubB, , , , $userC, $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $registration = $this->registerParticipant($competition, $clubB);

        $this->withSession(['current_user_id' => $userC->id])
            ->patch(route('competitions.participants.withdraw', [$competition, $registration]))
            ->assertForbidden();

        $this->assertTrue($registration->refresh()->is_active);
    }

    public function test_confirmed_invited_club_can_edit_own_participant_without_changing_registration_links(): void
    {
        [$clubA, $clubB, , , $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $registration = $this->registerParticipant($competition, $clubB);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.participants.edit', [$competition, $registration]))
            ->assertOk()
            ->assertSee('Modifier participant')
            ->assertSee('Martin');

        $this->withSession(['current_user_id' => $userB->id])
            ->patch(route('competitions.participants.update', [$competition, $registration]), [
                'last_name' => 'Bernard',
                'first_name' => 'Mila',
                'sex' => 'F',
                'age' => 15,
                'approximate_weight' => 50.2,
                'license_number' => 'LIC-456',
            ])
            ->assertRedirect(route('competitions.show', $competition).'#participants-non-valides');

        $registration->refresh();

        $this->assertSame($competition->id, $registration->competition_id);
        $this->assertSame($clubB->id, $registration->club_id);
        $this->assertSame($clubB->id, $registration->participantSource->club_id);
        $this->assertDatabaseHas('participant_sources', [
            'id' => $registration->participant_source_id,
            'club_id' => $clubB->id,
            'last_name' => 'Bernard',
            'first_name' => 'Mila',
            'age' => 15,
            'license_number' => 'LIC-456',
        ]);
        $this->assertDatabaseMissing('participant_sources', [
            'club_id' => $clubA->id,
            'last_name' => 'Bernard',
        ]);
    }

    public function test_modify_button_is_shown_only_for_active_not_validated_unassigned_participant(): void
    {
        [, $clubB, , , $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule A',
            'status' => Poule::STATUS_DRAFT,
        ]);

        $editable = $this->registerParticipant($competition, $clubB, [
            'last_name' => 'Editable',
            'first_name' => 'Alice',
        ]);
        $validated = $this->registerParticipant($competition, $clubB, [
            'last_name' => 'Valide',
            'first_name' => 'Boris',
            'is_validated' => true,
        ]);
        $assigned = $this->registerParticipant($competition, $clubB, [
            'last_name' => 'Affecte',
            'first_name' => 'Chloe',
            'is_validated' => true,
        ]);
        $withdrawn = $this->registerParticipant($competition, $clubB, [
            'last_name' => 'Retire',
            'first_name' => 'Dina',
            'is_active' => false,
        ]);

        $assigned->update(['poule_id' => $poule->id]);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Editable Alice')
            ->assertSee(route('competitions.participants.edit', [$competition, $editable]), false)
            ->assertSee('Valide Boris')
            ->assertDontSee(route('competitions.participants.edit', [$competition, $validated]), false)
            ->assertSee('Affecte Chloe')
            ->assertDontSee(route('competitions.participants.edit', [$competition, $assigned]), false)
            ->assertSee('Retire Dina')
            ->assertDontSee(route('competitions.participants.edit', [$competition, $withdrawn]), false);
    }

    public function test_server_blocks_editing_validated_assigned_or_withdrawn_participant_with_explicit_message(): void
    {
        [, $clubB, , , $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule A',
            'status' => Poule::STATUS_DRAFT,
        ]);

        $validated = $this->registerParticipant($competition, $clubB, [
            'last_name' => 'Valide',
            'is_validated' => true,
        ]);
        $assigned = $this->registerParticipant($competition, $clubB, [
            'last_name' => 'Affecte',
            'is_validated' => true,
        ]);
        $withdrawn = $this->registerParticipant($competition, $clubB, [
            'last_name' => 'Retire',
            'is_active' => false,
        ]);

        $assigned->update(['poule_id' => $poule->id]);

        $blockedCases = [
            [$validated, 'Impossible : participant validé', 'participants-valides'],
            [$assigned, 'Impossible : participant affecté à une poule', 'participants-valides'],
            [$withdrawn, 'Impossible : participation annulée', 'participants-retires'],
        ];

        foreach ($blockedCases as [$registration, $message, $fragment]) {
            $this->withSession(['current_user_id' => $userB->id])
                ->get(route('competitions.participants.edit', [$competition, $registration]))
                ->assertRedirect(route('competitions.show', $competition))
                ->assertSessionHas('status', $message);

            $this->withSession(['current_user_id' => $userB->id])
                ->patch(route('competitions.participants.update', [$competition, $registration]), [
                    'last_name' => 'Interdit',
                    'first_name' => 'Test',
                    'sex' => 'M',
                    'age' => 17,
                    'approximate_weight' => 70,
                    'license_number' => null,
                ])
                ->assertRedirect(route('competitions.show', $competition).'#'.$fragment)
                ->assertSessionHas('status', $message);
        }

        $this->assertDatabaseMissing('participant_sources', [
            'club_id' => $clubB->id,
            'last_name' => 'Interdit',
        ]);
        $this->assertTrue($validated->refresh()->is_validated);
        $this->assertSame($poule->id, $assigned->refresh()->poule_id);
        $this->assertFalse($withdrawn->refresh()->is_active);
    }

    public function test_organizer_can_edit_only_own_participant_not_invited_club_participant(): void
    {
        [$clubA, $clubB, , $userA, , , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $organizerRegistration = $this->registerParticipant($competition, $clubA);
        $invitedRegistration = $this->registerParticipant($competition, $clubB);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.participants.update', [$competition, $organizerRegistration]), [
                'last_name' => 'Moreau',
                'first_name' => 'Nora',
                'sex' => 'F',
                'age' => 13,
                'approximate_weight' => 44.1,
                'license_number' => null,
            ])
            ->assertRedirect(route('competitions.show', $competition).'#participants-non-valides');

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.participants.edit', [$competition, $invitedRegistration]))
            ->assertForbidden();

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.participants.update', [$competition, $invitedRegistration]), [
                'last_name' => 'Interdit',
                'first_name' => 'Test',
                'sex' => 'M',
                'age' => 17,
                'approximate_weight' => 70,
                'license_number' => null,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('participant_sources', [
            'id' => $organizerRegistration->participant_source_id,
            'club_id' => $clubA->id,
            'last_name' => 'Moreau',
            'first_name' => 'Nora',
        ]);
        $this->assertDatabaseMissing('participant_sources', [
            'id' => $invitedRegistration->participant_source_id,
            'last_name' => 'Interdit',
        ]);
    }

    public function test_invited_club_must_still_be_confirmed_to_edit_own_participant(): void
    {
        foreach ([Invitation::STATUS_INVITE, Invitation::STATUS_PARTICIPATION_DECLINED] as $index => $status) {
            [$clubA, $clubB, , , $userB, , $competition] = $this->scenario($status, ' edit '.$index);
            $registration = $this->registerParticipant($competition, $clubB);

            $this->withSession(['current_user_id' => $userB->id])
                ->get(route('competitions.participants.edit', [$competition, $registration]))
                ->assertForbidden();

            $this->withSession(['current_user_id' => $userB->id])
                ->patch(route('competitions.participants.update', [$competition, $registration]), [
                    'last_name' => 'Interdit',
                    'first_name' => 'Test',
                    'sex' => 'M',
                    'age' => 17,
                    'approximate_weight' => 70,
                    'license_number' => null,
                ])
                ->assertForbidden();

            $this->assertDatabaseMissing('participant_sources', [
                'club_id' => $clubB->id,
                'last_name' => 'Interdit',
            ]);
            $this->assertDatabaseMissing('participant_sources', [
                'club_id' => $clubA->id,
                'last_name' => 'Interdit',
            ]);
        }
    }

    public function test_non_invited_club_cannot_edit_another_club_participant(): void
    {
        [$clubA, $clubB, , , , $userC, $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $registration = $this->registerParticipant($competition, $clubB);

        $this->withSession(['current_user_id' => $userC->id])
            ->get(route('competitions.participants.edit', [$competition, $registration]))
            ->assertForbidden();

        $this->withSession(['current_user_id' => $userC->id])
            ->patch(route('competitions.participants.update', [$competition, $registration]), [
                'last_name' => 'Interdit',
                'first_name' => 'Test',
                'sex' => 'M',
                'age' => 17,
                'approximate_weight' => 70,
                'license_number' => null,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('participant_sources', [
            'club_id' => $clubA->id,
            'last_name' => 'Interdit',
        ]);
        $this->assertDatabaseMissing('participant_sources', [
            'club_id' => $clubB->id,
            'last_name' => 'Interdit',
        ]);
    }

    public function test_competition_detail_displays_participant_state_badges_in_order_for_club_and_organizer(): void
    {
        [$clubA, $clubB, , $userA, $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);

        $draftPoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Brouillon',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $frozenPoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Finale',
            'status' => Poule::STATUS_FROZEN,
        ]);

        $validated = $this->registerParticipant($competition, $clubB, [
            'last_name' => 'Valide',
            'first_name' => 'Alice',
            'is_validated' => true,
        ]);
        $notValidated = $this->registerParticipant($competition, $clubB, [
            'last_name' => 'Nonvalide',
            'first_name' => 'Boris',
            'is_validated' => false,
        ]);
        $assigned = $this->registerParticipant($competition, $clubB, [
            'last_name' => 'Affecte',
            'first_name' => 'Chloe',
            'is_validated' => true,
        ]);
        $frozen = $this->registerParticipant($competition, $clubB, [
            'last_name' => 'Figee',
            'first_name' => 'Dina',
            'is_validated' => true,
        ]);
        $withdrawn = $this->registerParticipant($competition, $clubB, [
            'last_name' => 'Retire',
            'first_name' => 'Eli',
            'is_active' => false,
            'is_validated' => true,
        ]);
        $organizerRegistration = $this->registerParticipant($competition, $clubA, [
            'last_name' => 'Orga',
            'first_name' => 'Fanny',
            'is_validated' => true,
        ]);

        $assigned->update(['poule_id' => $draftPoule->id]);
        $frozen->update(['poule_id' => $frozenPoule->id]);

        $clubView = $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSeeInOrder(['Valide Alice', 'Participant validé'])
            ->assertSeeInOrder(['Nonvalide Boris', 'En attente de validation'])
            ->assertSeeInOrder(['Affecte Chloe', 'Participant validé', 'Poule : Poule Brouillon'])
            ->assertSeeInOrder(['Figee Dina', 'Participant validé', 'Poule : Poule Finale', 'Poule figée'])
            ->assertSeeInOrder(['Retire Eli', 'Retiré']);

        $clubViewContent = $clubView->getContent();
        $withdrawnPosition = strpos($clubViewContent, 'Retire Eli');
        $this->assertNotFalse($withdrawnPosition);

        $withdrawnRowStart = strrpos(substr($clubViewContent, 0, $withdrawnPosition), '<tr');
        $withdrawnRowEnd = strpos($clubViewContent, '</tr>', $withdrawnPosition);
        $this->assertNotFalse($withdrawnRowStart);
        $this->assertNotFalse($withdrawnRowEnd);
        $withdrawnRow = substr($clubViewContent, $withdrawnRowStart, $withdrawnRowEnd - $withdrawnRowStart);

        $this->assertStringContainsString('Retiré', $withdrawnRow);
        $this->assertStringNotContainsString('Participant validé', $withdrawnRow);
        $this->assertStringNotContainsString('En attente de validation', $withdrawnRow);
        $this->assertStringNotContainsString('Poule :', $withdrawnRow);
        $this->assertStringNotContainsString('Poule figée', $withdrawnRow);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSeeInOrder(['Orga Fanny', 'Participant validé'])
            ->assertSeeInOrder(['Figee Dina', 'Participant validé', 'Poule : Poule Finale', 'Poule figée'])
            ->assertSeeInOrder(['Retire Eli', 'Retiré']);

        $this->assertFalse($withdrawn->refresh()->is_active);
        $this->assertTrue($validated->refresh()->is_validated);
        $this->assertFalse($notValidated->refresh()->is_validated);
        $this->assertSame($draftPoule->id, $assigned->refresh()->poule_id);
        $this->assertSame($frozenPoule->id, $frozen->refresh()->poule_id);
        $this->assertTrue($organizerRegistration->refresh()->is_validated);
    }

    public function test_organizer_can_close_and_reopen_competition_inscriptions(): void
    {
        [, , , $userA, $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Inscriptions ouvertes')
            ->assertSee('Fermer les inscriptions')
            ->assertSee('action="'.route('competitions.close-inscriptions', $competition).'"', false);

        $this->withSession(['current_user_id' => $userB->id])
            ->patch(route('competitions.close-inscriptions', $competition))
            ->assertForbidden();

        $this->withSession(['current_user_id' => $userB->id])
            ->patch(route('competitions.open-inscriptions', $competition))
            ->assertForbidden();

        $this->assertFalse($competition->refresh()->inscriptions_closed);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.close-inscriptions', $competition))
            ->assertRedirect(route('competitions.show', $competition).'#participants')
            ->assertSessionHas('status', 'Inscriptions fermées.');

        $this->assertTrue($competition->refresh()->inscriptions_closed);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Inscriptions fermées')
            ->assertSee('Réouvrir les inscriptions')
            ->assertSee('action="'.route('competitions.open-inscriptions', $competition).'"', false)
            ->assertDontSee('Fermer les inscriptions');

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Inscriptions fermées')
            ->assertDontSee('action="'.route('competitions.participants.store', $competition).'"', false);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.open-inscriptions', $competition))
            ->assertRedirect(route('competitions.show', $competition).'#participants')
            ->assertSessionHas('status', 'Inscriptions ouvertes.');

        $this->assertFalse($competition->refresh()->inscriptions_closed);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Inscriptions ouvertes')
            ->assertSee('action="'.route('competitions.participants.store', $competition).'"', false);
    }

    public function test_closed_inscriptions_block_invited_club_participant_changes(): void
    {
        [$clubA, $clubB, , $userA, $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $registration = $this->registerParticipant($competition, $clubB);
        $withdrawnRegistration = $this->registerParticipant($competition, $clubB, [
            'last_name' => 'Retire',
            'first_name' => 'Club',
            'is_active' => false,
            'is_validated' => false,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.close-inscriptions', $competition))
            ->assertRedirect(route('competitions.show', $competition).'#participants');

        $this->withSession(['current_user_id' => $userB->id])
            ->post(route('competitions.participants.store', $competition), array_merge($this->participantPayload(), [
                'last_name' => 'Bloque',
                'first_name' => 'Nouveau',
            ]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-ajout')
            ->assertSessionHas('status', 'Inscriptions fermées.');

        $this->assertDatabaseMissing('participant_sources', [
            'club_id' => $clubB->id,
            'last_name' => 'Bloque',
            'first_name' => 'Nouveau',
            'license_number' => 'LIC-123',
        ]);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.participants.edit', [$competition, $registration]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-non-valides')
            ->assertSessionHas('status', 'Inscriptions fermées.');

        $this->withSession(['current_user_id' => $userB->id])
            ->patch(route('competitions.participants.update', [$competition, $registration]), array_merge($this->participantPayload(), [
                'last_name' => 'Modifie',
            ]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-non-valides')
            ->assertSessionHas('status', 'Inscriptions fermées.');

        $this->withSession(['current_user_id' => $userB->id])
            ->patch(route('competitions.participants.withdraw', [$competition, $registration]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-non-valides')
            ->assertSessionHas('status', 'Inscriptions fermées.');

        $this->withSession(['current_user_id' => $userB->id])
            ->patch(route('competitions.participants.reactivate', [$competition, $withdrawnRegistration]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-retires')
            ->assertSessionHas('status', 'Inscriptions fermées.');

        $registration->refresh()->load('participantSource');
        $withdrawnRegistration->refresh();

        $this->assertSame('Martin', $registration->participantSource->last_name);
        $this->assertTrue($registration->is_active);
        $this->assertFalse($withdrawnRegistration->is_active);
    }

    public function test_closed_inscriptions_do_not_block_organizer_participant_changes(): void
    {
        [$clubA, $clubB, , $userA, , , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.close-inscriptions', $competition))
            ->assertRedirect(route('competitions.show', $competition).'#participants');

        $this->withSession(['current_user_id' => $userA->id])
            ->post(route('competitions.participants.store', $competition), array_merge($this->participantPayload(), [
                'club_id' => $clubB->id,
                'last_name' => 'Ajoute',
                'first_name' => 'Orga',
            ]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-ajout');

        $this->assertDatabaseHas('participant_sources', [
            'club_id' => $clubB->id,
            'last_name' => 'Ajoute',
            'first_name' => 'Orga',
        ]);

        $organizerRegistration = $this->registerParticipant($competition, $clubA);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.participants.update', [$competition, $organizerRegistration]), array_merge($this->participantPayload(), [
                'last_name' => 'Orga',
                'first_name' => 'Modifie',
            ]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-non-valides');

        $this->assertDatabaseHas('participant_sources', [
            'id' => $organizerRegistration->participant_source_id,
            'last_name' => 'Orga',
            'first_name' => 'Modifie',
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->patch(route('competitions.participants.withdraw', [$competition, $organizerRegistration]))
            ->assertRedirect(route('competitions.show', $competition).'#participants-non-valides');

        $this->assertFalse($organizerRegistration->refresh()->is_active);
    }

    private function participantPayload(): array
    {
        return [
            'last_name' => 'Martin',
            'first_name' => 'Lea',
            'sex' => 'F',
            'age' => 14,
            'approximate_weight' => 48.5,
            'license_number' => 'LIC-123',
        ];
    }

    private function registerParticipant(Competition $competition, Club $club, array $attributes = []): InscriptionOperationnelle
    {
        $isActive = $attributes['is_active'] ?? true;
        $isValidated = $attributes['is_validated'] ?? false;
        unset($attributes['is_active']);
        unset($attributes['is_validated']);

        $participant = ParticipantSource::create(array_merge([
            'club_id' => $club->id,
            'last_name' => 'Martin',
            'first_name' => 'Lea',
            'sex' => 'F',
            'age' => 14,
            'approximate_weight' => 48.5,
            'license_number' => 'LIC-123',
        ], $attributes));

        return InscriptionOperationnelle::create([
            'competition_id' => $competition->id,
            'club_id' => $club->id,
            'participant_source_id' => $participant->id,
            'is_active' => $isActive,
            'is_validated' => $isValidated,
        ]);
    }

    private function scenario(string $invitationStatus, string $suffix = ''): array
    {
        $clubA = Club::create(['name' => 'Club A'.$suffix]);
        $clubB = Club::create(['name' => 'Club B'.$suffix]);
        $clubC = Club::create(['name' => 'Club C'.$suffix]);

        $userA = User::create([
            'club_id' => $clubA->id,
            'name' => 'Utilisateur Club A',
            'email' => 'club-a'.str_replace(' ', '-', $suffix).'@example.test',
            'password' => 'password',
        ]);

        $userB = User::create([
            'club_id' => $clubB->id,
            'name' => 'Utilisateur Club B',
            'email' => 'club-b'.str_replace(' ', '-', $suffix).'@example.test',
            'password' => 'password',
        ]);

        $userC = User::create([
            'club_id' => $clubC->id,
            'name' => 'Utilisateur Club C',
            'email' => 'club-c'.str_replace(' ', '-', $suffix).'@example.test',
            'password' => 'password',
        ]);

        $competition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Competition Sprint 3',
        ]);

        Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'status' => $invitationStatus,
        ]);

        return [$clubA, $clubB, $clubC, $userA, $userB, $userC, $competition];
    }
}
