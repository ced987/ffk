<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Combat;
use App\Models\Competition;
use App\Models\InscriptionOperationnelle;
use App\Models\Invitation;
use App\Models\Licencie;
use App\Models\ParticipantSource;
use App\Models\Poule;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Setting::create([
            'key' => 'help_video_iframe',
            'value' => null,
        ]);

        $clubs = $this->createClubsAndUsers();

        $licencies = [
            'paris' => $this->createLicencies($clubs['paris'], [
                ['Martin', 'Lucas', '2012-03-12', 'masculin', 42],
                ['Dupont', 'Emma', '2011-09-04', 'feminin', 48],
                ['Leroy', 'Nathan', '2010-06-18', 'masculin', 56],
                ['Garcia', 'Chloe', '2013-11-21', 'feminin', 39],
                ['Moreau', 'Hugo', '2009-01-22', 'masculin', 64],
                ['Petit', 'Lea', '2012-05-09', 'feminin', 44],
                ['Roux', 'Enzo', '2011-12-30', 'masculin', 51],
                ['Fournier', 'Ines', '2014-04-14', 'feminin', 34],
                ['Girard', 'Noah', '2010-08-03', 'masculin', 60],
                ['Lambert', 'Mila', '2013-10-17', 'feminin', 38],
                ['Bonnet', 'Tom', '2008-07-22', 'masculin', 72],
                ['Francois', 'Lina', '2012-02-27', 'feminin', 45],
            ]),
            'lyon' => $this->createLicencies($clubs['lyon'], [
                ['Mercier', 'Enzo', '2012-08-09', 'masculin', 43],
                ['Blanc', 'Alice', '2011-11-30', 'feminin', 47],
                ['Guerin', 'Mathis', '2010-07-15', 'masculin', 58],
                ['Perrin', 'Jade', '2013-04-03', 'feminin', 40],
                ['Faure', 'Ethan', '2009-02-19', 'masculin', 66],
                ['Andre', 'Manon', '2014-09-24', 'feminin', 35],
                ['Robin', 'Mael', '2011-01-08', 'masculin', 52],
                ['Clement', 'Sarah', '2012-12-05', 'feminin', 44],
                ['Morin', 'Louis', '2010-05-28', 'masculin', 61],
                ['Gauthier', 'Camille', '2013-03-16', 'feminin', 37],
                ['Chevalier', 'Raphael', '2008-10-20', 'masculin', 74],
                ['Masson', 'Nina', '2012-06-01', 'feminin', 46],
            ]),
            'marseille' => $this->createLicencies($clubs['marseille'], [
                ['Sanchez', 'Hugo', '2011-10-27', 'masculin', 50],
                ['Lopez', 'Lola', '2012-12-06', 'feminin', 43],
                ['Martinez', 'Adam', '2010-02-14', 'masculin', 59],
                ['Rossi', 'Eva', '2013-06-01', 'feminin', 38],
                ['Fernandez', 'Liam', '2009-09-18', 'masculin', 68],
                ['Navarro', 'Zoé', '2014-11-11', 'feminin', 33],
                ['Da Silva', 'Noé', '2011-07-04', 'masculin', 53],
                ['Costa', 'Anna', '2012-03-25', 'feminin', 42],
                ['Aubert', 'Sacha', '2010-10-02', 'masculin', 62],
                ['Rey', 'Nora', '2013-01-13', 'feminin', 36],
                ['Vidal', 'Eliott', '2008-04-07', 'masculin', 76],
                ['Marty', 'Lou', '2011-05-29', 'feminin', 49],
            ]),
            'bordeaux' => $this->createLicencies($clubs['bordeaux'], [
                ['Colin', 'Noam', '2012-02-20', 'masculin', 45],
                ['Brun', 'Lena', '2011-05-11', 'feminin', 49],
                ['Henry', 'Jules', '2013-06-02', 'masculin', 41],
                ['Roussel', 'Clara', '2010-04-23', 'feminin', 56],
                ['Mathieu', 'Gabriel', '2014-07-07', 'masculin', 34],
                ['Garnier', 'Julia', '2009-02-12', 'feminin', 63],
                ['Leclerc', 'Maxime', '2011-09-29', 'masculin', 54],
                ['Barbier', 'Ambre', '2012-12-19', 'feminin', 43],
                ['Renard', 'Oscar', '2010-08-15', 'masculin', 60],
                ['Marchand', 'Rose', '2013-11-06', 'feminin', 37],
                ['Philippe', 'Arthur', '2008-03-23', 'masculin', 73],
                ['Benoit', 'Iris', '2012-01-18', 'feminin', 45],
            ]),
            'lille' => $this->createLicencies($clubs['lille'], [
                ['Muller', 'Timéo', '2013-09-25', 'masculin', 40],
                ['Ribeiro', 'Maia', '2012-05-10', 'feminin', 46],
                ['Carre', 'Nolan', '2011-01-31', 'masculin', 52],
                ['Meunier', 'Elise', '2010-03-20', 'feminin', 55],
                ['Schmitt', 'Aaron', '2014-09-09', 'masculin', 32],
                ['Lemoine', 'Agathe', '2009-06-27', 'feminin', 61],
                ['Picard', 'Bastien', '2012-08-08', 'masculin', 44],
                ['Roger', 'Louna', '2011-12-01', 'feminin', 50],
                ['Schneider', 'Victor', '2013-04-18', 'masculin', 39],
                ['Leger', 'Jeanne', '2008-07-22', 'feminin', 59],
                ['Boyer', 'Samuel', '2010-09-14', 'masculin', 65],
                ['Hubert', 'Lise', '2012-11-30', 'feminin', 43],
            ]),
        ];

        $this->seedCompetition($clubs, $licencies, [
            'name' => 'Interclubs Samouraï 2026',
            'date' => today()->addWeeks(3)->toDateString(),
            'invitations' => [
                'lyon' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
                'marseille' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
                'bordeaux' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
                'lille' => Invitation::STATUS_INVITE,
            ],
            'participants' => [
                ['paris', 0], ['paris', 1], ['paris', 2],
                ['lyon', 0], ['lyon', 1], ['lyon', 2],
                ['marseille', 0], ['marseille', 1], ['marseille', 2],
                ['bordeaux', 0], ['bordeaux', 1], ['bordeaux', 2],
                ['lille', 0, false], ['lille', 1, false], ['paris', 3, false],
            ],
            'poules' => [
                ['name' => 'Poule Minimes A', 'status' => Poule::STATUS_FROZEN, 'registrations' => [0, 3, 6, 9], 'scores' => 'all'],
                ['name' => 'Poule Minimes B', 'status' => Poule::STATUS_FROZEN, 'registrations' => [1, 4, 7, 10], 'scores' => 'all'],
                ['name' => 'Poule Cadets', 'status' => Poule::STATUS_FROZEN, 'registrations' => [2, 5, 8, 11], 'scores' => 'all'],
            ],
        ]);

        $this->seedCompetition($clubs, $licencies, [
            'name' => 'Coupe de la Joie',
            'date' => today()->addWeeks(6)->toDateString(),
            'invitations' => [
                'lyon' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
                'marseille' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
                'bordeaux' => Invitation::STATUS_INVITE,
                'lille' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
            ],
            'participants' => [
                ['paris', 4], ['paris', 5],
                ['lyon', 3], ['lyon', 4], ['lyon', 5],
                ['marseille', 3], ['marseille', 4], ['marseille', 5],
                ['lille', 2], ['lille', 3], ['lille', 4],
                ['bordeaux', 3, false],
            ],
            'poules' => [
                ['name' => 'Poule Espoirs', 'status' => Poule::STATUS_FROZEN, 'registrations' => [0, 2, 5, 8], 'scores' => 3],
                ['name' => 'Poule Juniors', 'status' => Poule::STATUS_FROZEN, 'registrations' => [1, 3, 6, 9], 'scores' => 1],
                ['name' => 'Poule à compléter', 'status' => Poule::STATUS_DRAFT, 'registrations' => [4, 7]],
            ],
        ]);

        $this->seedCompetition($clubs, $licencies, [
            'name' => 'Open Dojo National',
            'date' => today()->addMonths(2)->toDateString(),
            'invitations' => [
                'lyon' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
                'marseille' => Invitation::STATUS_INVITE,
                'bordeaux' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
                'lille' => Invitation::STATUS_PRE_INVITE,
            ],
            'participants' => [
                ['paris', 6], ['paris', 7],
                ['lyon', 6], ['lyon', 7],
                ['bordeaux', 4], ['bordeaux', 5],
                ['marseille', 6, false], ['lille', 5, false],
            ],
            'poules' => [
                ['name' => 'Poule Découverte', 'status' => Poule::STATUS_FROZEN, 'registrations' => [0, 2, 4], 'scores' => 0],
                ['name' => 'Poule en constitution', 'status' => Poule::STATUS_DRAFT, 'registrations' => [1, 3]],
            ],
        ]);

        $this->seedCompetition($clubs, $licencies, [
            'name' => 'Challenge des Dojos 2026',
            'date' => today()->subWeeks(2)->toDateString(),
            'invitations' => [
                'lyon' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
                'marseille' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
                'bordeaux' => Invitation::STATUS_PARTICIPATION_DECLINED,
            ],
            'participants' => [
                ['paris', 8], ['paris', 9],
                ['lyon', 8], ['lyon', 9], ['lyon', 10],
                ['marseille', 7], ['marseille', 8], ['marseille', 9],
                ['bordeaux', 6, false], ['lille', 6, false],
            ],
            'poules' => [
                ['name' => 'Poule Honneur', 'status' => Poule::STATUS_FROZEN, 'registrations' => [0, 2, 5, 7], 'scores' => 'all'],
                ['name' => 'Poule Excellence', 'status' => Poule::STATUS_FROZEN, 'registrations' => [1, 3, 4, 6], 'scores' => 'all'],
            ],
        ]);

        $this->seedCompetition($clubs, $licencies, [
            'name' => 'Trophée Kata Kumité',
            'date' => today()->addMonth()->toDateString(),
            'invitations' => [
                'lyon' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
                'marseille' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
                'bordeaux' => Invitation::STATUS_INVITE,
                'lille' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
            ],
            'participants' => [
                ['paris', 10], ['lyon', 11], ['marseille', 10],
                ['lille', 7], ['lille', 8], ['lille', 9],
                ['bordeaux', 7, false], ['marseille', 11], ['paris', 11],
            ],
            'poules' => [
                ['name' => 'Poule Kumité', 'status' => Poule::STATUS_FROZEN, 'registrations' => [0, 1, 2, 3], 'scores' => 2],
                ['name' => 'Poule Kata', 'status' => Poule::STATUS_FROZEN, 'registrations' => [4, 5, 7, 8], 'scores' => 0],
            ],
        ]);

        $this->seedCompetition($clubs, $licencies, [
            'name' => 'Rencontre Interclubs Aquitaine',
            'date' => today()->toDateString(),
            'invitations' => [
                'lyon' => Invitation::STATUS_INVITE,
                'marseille' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
                'bordeaux' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
                'lille' => Invitation::STATUS_PARTICIPATION_CONFIRMED,
            ],
            'participants' => [
                ['paris', 0], ['marseille', 0], ['marseille', 1],
                ['bordeaux', 8], ['bordeaux', 9], ['lille', 10],
                ['lille', 11], ['lyon', 0, false],
            ],
            'poules' => [
                ['name' => 'Poule Aquitaine', 'status' => Poule::STATUS_FROZEN, 'registrations' => [0, 1, 3, 5], 'scores' => 0],
                ['name' => 'Poule préparation', 'status' => Poule::STATUS_DRAFT, 'registrations' => [2, 4]],
            ],
        ]);
    }

    /**
     * @return array<string, Club>
     */
    private function createClubsAndUsers(): array
    {
        $clubs = [
            'paris' => Club::create(['name' => 'Karaté Club Paris Centre']),
            'lyon' => Club::create(['name' => 'Dojo Shotokan Lyon']),
            'marseille' => Club::create(['name' => 'Karaté Club Marseille Sud']),
            'bordeaux' => Club::create(['name' => 'Dojo Bushido Bordeaux']),
            'lille' => Club::create(['name' => 'Karaté Club Lille Métropole']),
        ];

        foreach ($clubs as $club) {
            User::create([
                'club_id' => $club->id,
                'name' => $club->name,
                'email' => Str::slug(Str::ascii($club->name)).'@example.test',
                'password' => 'password',
            ]);
        }

        return $clubs;
    }

    /**
     * @param  array<int, array{0: string, 1: string, 2: string, 3: string, 4: int}>  $licencies
     */
    private function createLicencies(Club $club, array $licencies): Collection
    {
        $createdLicencies = new Collection();

        foreach ($licencies as [$nom, $prenom, $dateNaissance, $sexe, $poids]) {
            $createdLicencies->push(Licencie::create([
                'club_id' => $club->id,
                'nom' => $nom,
                'prenom' => $prenom,
                'date_naissance' => $dateNaissance,
                'sexe' => $sexe,
                'poids' => $poids,
            ]));
        }

        return $createdLicencies;
    }

    /**
     * @param  array<string, Club>  $clubs
     * @param  array<string, Collection<int, Licencie>>  $licencies
     * @param  array{
     *     name: string,
     *     date: string,
     *     invitations: array<string, string>,
     *     participants: array<int, array{0: string, 1: int, 2?: bool}>,
     *     poules: array<int, array{name: string, status: string, registrations: array<int, int>, scores?: string|int}>
     * }  $data
     */
    private function seedCompetition(array $clubs, array $licencies, array $data): void
    {
        $competition = Competition::create([
            'organizer_club_id' => $clubs['paris']->id,
            'name' => $data['name'],
            'date_competition' => $data['date'],
        ]);

        foreach ($data['invitations'] as $clubKey => $status) {
            Invitation::create([
                'competition_id' => $competition->id,
                'club_id' => $clubs[$clubKey]->id,
                'status' => $status,
            ]);
        }

        $registrations = [];
        foreach ($data['participants'] as $participantIndex => $participantData) {
            [$clubKey, $licencieIndex] = $participantData;
            $isValidated = $participantData[2] ?? true;

            $registrations[$participantIndex] = $this->registerLicencieParticipant(
                $competition,
                $licencies[$clubKey][$licencieIndex],
                $isValidated,
            );
        }

        foreach ($data['poules'] as $pouleData) {
            $poule = Poule::create([
                'competition_id' => $competition->id,
                'name' => $pouleData['name'],
                'status' => $pouleData['status'],
            ]);

            foreach ($pouleData['registrations'] as $registrationIndex) {
                $registrations[$registrationIndex]->update(['poule_id' => $poule->id]);
            }

            if ($poule->status === Poule::STATUS_FROZEN) {
                $poule->generateCombats();
                $this->scoreGeneratedCombats($poule, $pouleData['scores'] ?? 0);
            }
        }
    }

    private function registerLicencieParticipant(
        Competition $competition,
        Licencie $licencie,
        bool $isValidated,
    ): InscriptionOperationnelle {
        $participant = ParticipantSource::create([
            'club_id' => $licencie->club_id,
            'licencie_id' => $licencie->id,
            'last_name' => $licencie->nom,
            'first_name' => $licencie->prenom,
            'sex' => $licencie->sexe === 'masculin' ? 'M' : 'F',
            'age' => $licencie->date_naissance->age,
            'approximate_weight' => $licencie->poids,
            'license_number' => null,
        ]);

        return InscriptionOperationnelle::create([
            'competition_id' => $competition->id,
            'club_id' => $licencie->club_id,
            'participant_source_id' => $participant->id,
            'is_active' => true,
            'is_validated' => $isValidated,
        ]);
    }

    private function scoreGeneratedCombats(Poule $poule, string|int $scoreMode): void
    {
        $combats = $poule->combats()->orderBy('ordre_combat')->get();
        $scoreCount = $scoreMode === 'all' ? $combats->count() : (int) $scoreMode;

        foreach ($combats->take($scoreCount)->values() as $index => $combat) {
            $scoreA = [3, 2, 4, 1, 5, 2][$index % 6];
            $scoreB = [1, 2, 0, 3, 2, 4][$index % 6];

            $result = match (true) {
                $scoreA > $scoreB => Combat::RESULT_LEFT_WIN,
                $scoreB > $scoreA => Combat::RESULT_RIGHT_WIN,
                default => Combat::RESULT_DRAW,
            };

            $combat->update([
                'score_a' => $scoreA,
                'score_b' => $scoreB,
                'score_texte' => $scoreA.' - '.$scoreB,
                'commentaire' => $index % 3 === 0 ? 'Combat engagé' : null,
                'resultat' => $result,
                'statut' => Combat::STATUS_FINISHED,
            ]);
        }
    }
}
