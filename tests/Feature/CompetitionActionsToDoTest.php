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

class CompetitionActionsToDoTest extends TestCase
{
    use RefreshDatabase;

    public function test_organizer_sees_relevant_actions_to_do_in_business_order(): void
    {
        [$clubA, $clubB, , $userA, , , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $clubC = Club::create(['name' => 'Club C Bis']);
        Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubC->id,
            'status' => Invitation::STATUS_PRE_INVITE,
        ]);

        $this->registerParticipant($competition, $clubB, ['is_validated' => false]);
        $this->registerParticipant($competition, $clubB, ['is_validated' => true]);

        $draftPoule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Brouillon',
            'status' => Poule::STATUS_DRAFT,
        ]);
        $this->registerParticipant($competition, $clubA, ['is_validated' => true, 'poule_id' => $draftPoule->id]);
        $this->registerParticipant($competition, $clubB, ['is_validated' => true, 'poule_id' => $draftPoule->id]);

        Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Figée Sans Combat',
            'status' => Poule::STATUS_FROZEN,
        ]);

        $frozenPouleWithCombat = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Figée Avec Combat',
            'status' => Poule::STATUS_FROZEN,
        ]);
        $first = $this->registerParticipant($competition, $clubA, ['is_validated' => true, 'poule_id' => $frozenPouleWithCombat->id]);
        $second = $this->registerParticipant($competition, $clubB, ['is_validated' => true, 'poule_id' => $frozenPouleWithCombat->id]);
        Combat::create([
            'poule_id' => $frozenPouleWithCombat->id,
            'inscription_a_id' => $first->id,
            'inscription_b_id' => $second->id,
            'ordre_combat' => 1,
            'statut' => Combat::STATUS_TO_ENTER,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Actions à faire')
            ->assertSeeInOrder([
                'Marquer 1 invitation(s) envoyée(s)',
                'Valider 1 participant(s)',
                'Affecter 1 participant(s) à une poule',
                'Figer 1 poule(s)',
                'Générer les combats',
                'Saisir 1 score(s)',
            ]);
    }

    public function test_invited_club_with_invite_status_sees_response_action(): void
    {
        [, , , , $userB, , $competition] = $this->scenario(Invitation::STATUS_INVITE);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Actions à faire')
            ->assertSee('Confirmer ou refuser votre participation');
    }

    public function test_confirmed_club_without_participants_sees_registration_action(): void
    {
        [, , , , $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Actions à faire')
            ->assertSee('Inscrire vos participants');
    }

    public function test_confirmed_club_with_not_validated_participants_sees_validation_waiting_action(): void
    {
        [, $clubB, , , $userB, , $competition] = $this->scenario(Invitation::STATUS_PARTICIPATION_CONFIRMED);
        $this->registerParticipant($competition, $clubB, ['is_validated' => false]);

        $this->withSession(['current_user_id' => $userB->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Actions à faire')
            ->assertSee('Vos participants sont en attente de validation')
            ->assertDontSee('Inscrire vos participants');
    }

    public function test_no_relevant_action_shows_empty_message(): void
    {
        [$clubA, , , $userA, , , $competition] = $this->scenario();
        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => 'Poule Finale',
            'status' => Poule::STATUS_FROZEN,
        ]);
        $first = $this->registerParticipant($competition, $clubA, ['is_validated' => true, 'poule_id' => $poule->id]);
        $second = $this->registerParticipant($competition, $clubA, ['is_validated' => true, 'poule_id' => $poule->id]);
        Combat::create([
            'poule_id' => $poule->id,
            'inscription_a_id' => $first->id,
            'inscription_b_id' => $second->id,
            'ordre_combat' => 1,
            'statut' => Combat::STATUS_FINISHED,
            'score_a' => 2,
            'score_b' => 0,
        ]);

        $this->withSession(['current_user_id' => $userA->id])
            ->get(route('competitions.show', $competition))
            ->assertOk()
            ->assertSee('Actions à faire')
            ->assertSee('Aucune action urgente');
    }

    private function scenario(string $invitationStatus = Invitation::STATUS_PARTICIPATION_CONFIRMED): array
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
            'name' => 'Competition Actions',
        ]);

        Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'status' => $invitationStatus,
        ]);

        return [$clubA, $clubB, $clubC, $userA, $userB, $userC, $competition];
    }

    private function registerParticipant(Competition $competition, Club $club, array $attributes = []): InscriptionOperationnelle
    {
        $participant = ParticipantSource::create([
            'club_id' => $club->id,
            'last_name' => $attributes['last_name'] ?? 'Participant',
            'first_name' => $attributes['first_name'] ?? uniqid('Test'),
            'sex' => $attributes['sex'] ?? 'F',
            'age' => $attributes['age'] ?? 14,
            'approximate_weight' => $attributes['approximate_weight'] ?? 48.5,
            'license_number' => $attributes['license_number'] ?? null,
        ]);

        return InscriptionOperationnelle::create([
            'competition_id' => $competition->id,
            'club_id' => $club->id,
            'participant_source_id' => $participant->id,
            'poule_id' => $attributes['poule_id'] ?? null,
            'is_active' => $attributes['is_active'] ?? true,
            'is_validated' => $attributes['is_validated'] ?? false,
        ]);
    }
}
