<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Competition;
use App\Models\InscriptionOperationnelle;
use App\Models\Invitation;
use App\Models\Licencie;
use App\Models\ParticipantSource;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class OpenInterclubsMediterraneeSeeder extends Seeder
{
    public function run(): void
    {
        $clubs = $this->clubs();
        $organizer = $clubs['KC Marseille 13'];

        $competition = Competition::updateOrCreate(
            ['name' => 'Open Interclubs Méditerranée 2026'],
            [
                'organizer_club_id' => $organizer->id,
                'date_competition' => '2026-06-14',
                'inscriptions_closed' => false,
            ],
        );

        foreach ($clubs as $name => $club) {
            if ($club->is($organizer)) {
                continue;
            }

            Invitation::updateOrCreate(
                [
                    'competition_id' => $competition->id,
                    'club_id' => $club->id,
                ],
                [
                    'status' => in_array($name, [
                        'Dojo Shotokan Lyon',
                        'Karaté Club Provence',
                        'Dojo Bushido Bordeaux',
                        'Budokan Nice',
                        'AS Karaté Rennes',
                        'Dojo Nantais',
                    ], true)
                        ? Invitation::STATUS_PARTICIPATION_CONFIRMED
                        : Invitation::STATUS_INVITE,
                ],
            );
        }

        foreach ($this->participants() as $index => $data) {
            $club = $clubs[$data['club']];
            $licencie = $this->upsertLicencie($club, $data);
            $participant = $this->upsertParticipantSource($licencie, $index);

            InscriptionOperationnelle::updateOrCreate(
                [
                    'competition_id' => $competition->id,
                    'participant_source_id' => $participant->id,
                ],
                [
                    'club_id' => $club->id,
                    'is_active' => $data['active'] ?? true,
                    'is_validated' => $data['validated'] ?? true,
                    'poule_id' => null,
                ],
            );
        }
    }

    /**
     * @return array<string, Club>
     */
    private function clubs(): array
    {
        $clubNames = [
            'KC Marseille 13',
            'Dojo Shotokan Lyon',
            'Karaté Club Provence',
            'Dojo Bushido Bordeaux',
            'Karaté Club Lille Métropole',
            'Budokan Nice',
            'AS Karaté Rennes',
            'Dojo Nantais',
            'Sen No Sen Strasbourg',
            'JC Vaulx-en-Velin',
        ];

        $clubs = [];

        foreach ($clubNames as $name) {
            $club = Club::firstOrCreate(['name' => $name]);

            User::updateOrCreate(
                ['email' => Str::slug(Str::ascii($name)).'@example.test'],
                [
                    'club_id' => $club->id,
                    'name' => $club->name,
                    'password' => 'password',
                ],
            );

            $clubs[$name] = $club;
        }

        return $clubs;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function participants(): array
    {
        return [
            ['club' => 'KC Marseille 13', 'nom' => 'Martin', 'prenom' => 'Lucas', 'age' => 10, 'sexe' => 'masculin', 'poids' => 32],
            ['club' => 'Dojo Shotokan Lyon', 'nom' => 'Dupont', 'prenom' => 'Emma', 'age' => 10, 'sexe' => 'feminin', 'poids' => 31],
            ['club' => 'Karaté Club Provence', 'nom' => 'Leroy', 'prenom' => 'Nathan', 'age' => 11, 'sexe' => 'masculin', 'poids' => 35],
            ['club' => 'Dojo Bushido Bordeaux', 'nom' => 'Garcia', 'prenom' => 'Chloé', 'age' => 11, 'sexe' => 'feminin', 'poids' => 34],
            ['club' => 'Budokan Nice', 'nom' => 'Moreau', 'prenom' => 'Hugo', 'age' => 12, 'sexe' => 'masculin', 'poids' => 38],
            ['club' => 'AS Karaté Rennes', 'nom' => 'Petit', 'prenom' => 'Léa', 'age' => 12, 'sexe' => 'feminin', 'poids' => 37],
            ['club' => 'Dojo Nantais', 'nom' => 'Roux', 'prenom' => 'Enzo', 'age' => 12, 'sexe' => 'masculin', 'poids' => 44],
            ['club' => 'Sen No Sen Strasbourg', 'nom' => 'Fournier', 'prenom' => 'Inès', 'age' => 10, 'sexe' => 'feminin', 'poids' => 40],

            ['club' => 'JC Vaulx-en-Velin', 'nom' => 'Girard', 'prenom' => 'Noah', 'age' => 13, 'sexe' => 'masculin', 'poids' => 45],
            ['club' => 'KC Marseille 13', 'nom' => 'Lambert', 'prenom' => 'Mila', 'age' => 13, 'sexe' => 'feminin', 'poids' => 43],
            ['club' => 'Dojo Shotokan Lyon', 'nom' => 'Bonnet', 'prenom' => 'Tom', 'age' => 14, 'sexe' => 'masculin', 'poids' => 49],
            ['club' => 'Karaté Club Provence', 'nom' => 'François', 'prenom' => 'Lina', 'age' => 14, 'sexe' => 'feminin', 'poids' => 48],
            ['club' => 'Dojo Bushido Bordeaux', 'nom' => 'Mercier', 'prenom' => 'Ethan', 'age' => 15, 'sexe' => 'masculin', 'poids' => 53],
            ['club' => 'Karaté Club Lille Métropole', 'nom' => 'Blanc', 'prenom' => 'Alice', 'age' => 15, 'sexe' => 'feminin', 'poids' => 52],
            ['club' => 'Budokan Nice', 'nom' => 'Guerin', 'prenom' => 'Mathis', 'age' => 15, 'sexe' => 'masculin', 'poids' => 61],
            ['club' => 'AS Karaté Rennes', 'nom' => 'Perrin', 'prenom' => 'Jade', 'age' => 13, 'sexe' => 'feminin', 'poids' => 56],

            ['club' => 'Dojo Nantais', 'nom' => 'Faure', 'prenom' => 'Raphaël', 'age' => 16, 'sexe' => 'masculin', 'poids' => 58],
            ['club' => 'Sen No Sen Strasbourg', 'nom' => 'André', 'prenom' => 'Manon', 'age' => 16, 'sexe' => 'feminin', 'poids' => 55],
            ['club' => 'JC Vaulx-en-Velin', 'nom' => 'Robin', 'prenom' => 'Maël', 'age' => 17, 'sexe' => 'masculin', 'poids' => 66],
            ['club' => 'KC Marseille 13', 'nom' => 'Clément', 'prenom' => 'Sarah', 'age' => 17, 'sexe' => 'feminin', 'poids' => 60],
            ['club' => 'Dojo Shotokan Lyon', 'nom' => 'Morin', 'prenom' => 'Louis', 'age' => 17, 'sexe' => 'masculin', 'poids' => 73],
            ['club' => 'Karaté Club Provence', 'nom' => 'Gauthier', 'prenom' => 'Camille', 'age' => 16, 'sexe' => 'feminin', 'poids' => 64],

            ['club' => 'Dojo Bushido Bordeaux', 'nom' => 'Chevalier', 'prenom' => 'Adam', 'age' => 18, 'sexe' => 'masculin', 'poids' => 68],
            ['club' => 'Karaté Club Lille Métropole', 'nom' => 'Masson', 'prenom' => 'Nina', 'age' => 18, 'sexe' => 'feminin', 'poids' => 61],
            ['club' => 'Budokan Nice', 'nom' => 'Colin', 'prenom' => 'Jules', 'age' => 19, 'sexe' => 'masculin', 'poids' => 70],
            ['club' => 'AS Karaté Rennes', 'nom' => 'Brun', 'prenom' => 'Léna', 'age' => 20, 'sexe' => 'feminin', 'poids' => 63],
            ['club' => 'Dojo Nantais', 'nom' => 'Henry', 'prenom' => 'Sacha', 'age' => 22, 'sexe' => 'masculin', 'poids' => 74],
            ['club' => 'Sen No Sen Strasbourg', 'nom' => 'Roussel', 'prenom' => 'Clara', 'age' => 24, 'sexe' => 'feminin', 'poids' => 65],
            ['club' => 'JC Vaulx-en-Velin', 'nom' => 'Mathieu', 'prenom' => 'Gabriel', 'age' => 28, 'sexe' => 'masculin', 'poids' => 78],
            ['club' => 'KC Marseille 13', 'nom' => 'Garnier', 'prenom' => 'Julia', 'age' => 31, 'sexe' => 'feminin', 'poids' => 68],
            ['club' => 'Dojo Shotokan Lyon', 'nom' => 'Leclerc', 'prenom' => 'Maxime', 'age' => 35, 'sexe' => 'masculin', 'poids' => 86],
            ['club' => 'Karaté Club Provence', 'nom' => 'Barbier', 'prenom' => 'Ambre', 'age' => 34, 'sexe' => 'feminin', 'poids' => 72],

            ['club' => 'Dojo Bushido Bordeaux', 'nom' => 'Renard', 'prenom' => 'Oscar', 'age' => 11, 'sexe' => 'masculin', 'poids' => 51],
            ['club' => 'Karaté Club Lille Métropole', 'nom' => 'Marchand', 'prenom' => 'Rose', 'age' => 14, 'sexe' => 'feminin', 'poids' => 62],
            ['club' => 'Budokan Nice', 'nom' => 'Philippe', 'prenom' => 'Arthur', 'age' => 16, 'sexe' => 'masculin', 'poids' => 81],
            ['club' => 'AS Karaté Rennes', 'nom' => 'Benoit', 'prenom' => 'Iris', 'age' => 17, 'sexe' => 'feminin', 'poids' => 74],
            ['club' => 'Dojo Nantais', 'nom' => 'Muller', 'prenom' => 'Timéo', 'age' => 25, 'sexe' => 'masculin', 'poids' => 92],
            ['club' => 'Sen No Sen Strasbourg', 'nom' => 'Ribeiro', 'prenom' => 'Maïa', 'age' => 29, 'sexe' => 'feminin', 'poids' => 82],

            ['club' => 'JC Vaulx-en-Velin', 'nom' => 'Carré', 'prenom' => 'Nolan', 'age' => 12, 'sexe' => 'masculin', 'poids' => 36, 'validated' => false],
            ['club' => 'KC Marseille 13', 'nom' => 'Meunier', 'prenom' => 'Élise', 'age' => 15, 'sexe' => 'feminin', 'poids' => 50, 'validated' => false],
            ['club' => 'Dojo Shotokan Lyon', 'nom' => 'Schmitt', 'prenom' => 'Aaron', 'age' => 18, 'sexe' => 'masculin', 'poids' => 65, 'validated' => false],
            ['club' => 'Karaté Club Provence', 'nom' => 'Lemoine', 'prenom' => 'Agathe', 'age' => 13, 'sexe' => 'feminin', 'poids' => 44, 'active' => false, 'validated' => false],
            ['club' => 'Dojo Bushido Bordeaux', 'nom' => 'Picard', 'prenom' => 'Bastien', 'age' => 21, 'sexe' => 'masculin', 'poids' => 83, 'active' => false, 'validated' => false],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function upsertLicencie(Club $club, array $data): Licencie
    {
        return Licencie::updateOrCreate(
            [
                'club_id' => $club->id,
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
            ],
            [
                'date_naissance' => $this->birthDateForAge((int) $data['age']),
                'sexe' => $data['sexe'],
                'poids' => $data['poids'],
            ],
        );
    }

    private function upsertParticipantSource(Licencie $licencie, int $index): ParticipantSource
    {
        $licenseNumber = 'MED-2026-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);

        return ParticipantSource::updateOrCreate(
            [
                'license_number' => $licenseNumber,
            ],
            [
                'club_id' => $licencie->club_id,
                'licencie_id' => $licencie->id,
                'last_name' => $licencie->nom,
                'first_name' => $licencie->prenom,
                'sex' => $licencie->sexe === 'masculin' ? 'M' : 'F',
                'age' => $licencie->date_naissance->age,
                'approximate_weight' => $licencie->poids,
            ],
        );
    }

    private function birthDateForAge(int $age): string
    {
        return Carbon::today()
            ->subYears($age)
            ->subMonths(($age % 7) + 1)
            ->subDays(($age % 17) + 3)
            ->toDateString();
    }
}
