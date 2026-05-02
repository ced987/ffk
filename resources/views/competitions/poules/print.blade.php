<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Impression {{ $poule->name }} - {{ $competition->name }}</title>
    <style>
        @page {
            size: A4;
            margin: 14mm;
        }

        body {
            margin: 0;
            background: #ffffff;
            color: #000000;
            font-family: Arial, sans-serif;
            font-size: 11pt;
        }

        main {
            width: 100%;
        }

        h1 {
            margin: 0 0 3mm;
            font-size: 18pt;
        }

        h2 {
            margin: 8mm 0 3mm;
            padding-bottom: 2mm;
            border-bottom: 1px solid #000000;
            font-size: 13pt;
        }

        p {
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 3mm 2mm;
            border: 1px solid #000000;
            color: #000000;
            text-align: left;
            vertical-align: middle;
        }

        th {
            font-weight: 700;
        }

        tr {
            break-inside: avoid;
        }

        .print-page {
            page-break-after: always;
        }

        .center {
            text-align: center;
        }

        .empty-state {
            margin: 0 0 4mm;
        }

        .screen-actions {
            margin-bottom: 8mm;
        }

        .screen-actions a,
        .screen-actions button {
            display: inline-flex;
            margin-right: 8px;
            padding: 8px 12px;
            border: 1px solid #000000;
            border-radius: 6px;
            background: #ffffff;
            color: #000000;
            cursor: pointer;
            font: inherit;
            text-decoration: none;
        }

        @media print {
            .screen-actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <main class="print-page">
        <div class="screen-actions">
            <button type="button" onclick="window.print()">Imprimer</button>
            <a href="{{ route('competitions.show', $competition) }}">Retour compétition</a>
        </div>

        <h1>{{ $competition->name }}</h1>
        <p>{{ $poule->name }}</p>

        <h2>Classement</h2>
        @if ($poule->registrations->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Participant</th>
                        <th>Club</th>
                        <th>J</th>
                        <th>V</th>
                        <th>N</th>
                        <th>D</th>
                        <th>NF</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($poule->ranking() as $rankingRow)
                        <tr>
                            <td>{{ $rankingRow['rank'] }}</td>
                            <td>
                                {{ $rankingRow['registration']->participantSource->last_name }}
                                {{ $rankingRow['registration']->participantSource->first_name }}
                            </td>
                            <td>{{ $rankingRow['registration']->club->name }}</td>
                            <td>{{ $rankingRow['played'] }}</td>
                            <td>{{ $rankingRow['wins'] }}</td>
                            <td>{{ $rankingRow['draws'] }}</td>
                            <td>{{ $rankingRow['losses'] }}</td>
                            <td>{{ $rankingRow['no_contests'] }}</td>
                            <td>{{ $rankingRow['points'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="empty-state">Aucun participant dans cette poule</p>
        @endif

        <h2>Combats</h2>
        @php
            $pouleCombats = $poule->combats->sortBy('ordre_combat')->values();
        @endphp
        @if ($pouleCombats->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Combattant 1</th>
                        <th>vs</th>
                        <th>Combattant 2</th>
                        <th>Nul</th>
                        <th>Non fait</th>
                        <th>Score</th>
                        <th>Commentaire</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pouleCombats as $combatIndex => $combat)
                        @php
                            $leftWins = $combat->resultat === \App\Models\Combat::RESULT_LEFT_WIN;
                            $rightWins = $combat->resultat === \App\Models\Combat::RESULT_RIGHT_WIN;
                            $leftChecked = $leftWins ? '[X]' : '[ ]';
                            $rightChecked = $rightWins ? '[X]' : '[ ]';
                            $drawChecked = $combat->resultat === \App\Models\Combat::RESULT_DRAW ? '[X]' : '[ ]';
                            $noContestChecked = $combat->resultat === \App\Models\Combat::RESULT_NO_CONTEST ? '[X]' : '[ ]';
                            $score = $combat->score_a !== null || $combat->score_b !== null
                                ? ($combat->score_a ?? '').' / '.($combat->score_b ?? '')
                                : '____ / ____';
                        @endphp
                        <tr>
                            <td>{{ $combatIndex + 1 }}</td>
                            <td>
                                {{ $leftChecked }}
                                @if ($leftWins)
                                    <strong>
                                        {{ $combat->inscriptionA->participantSource->last_name }}
                                        {{ $combat->inscriptionA->participantSource->first_name }}
                                    </strong>
                                @else
                                    {{ $combat->inscriptionA->participantSource->last_name }}
                                    {{ $combat->inscriptionA->participantSource->first_name }}
                                @endif
                            </td>
                            <td class="center">vs</td>
                            <td>
                                {{ $rightChecked }}
                                @if ($rightWins)
                                    <strong>
                                        {{ $combat->inscriptionB->participantSource->last_name }}
                                        {{ $combat->inscriptionB->participantSource->first_name }}
                                    </strong>
                                @else
                                    {{ $combat->inscriptionB->participantSource->last_name }}
                                    {{ $combat->inscriptionB->participantSource->first_name }}
                                @endif
                            </td>
                            <td class="center">{{ $drawChecked }}</td>
                            <td class="center">{{ $noContestChecked }}</td>
                            <td>{{ $score }}</td>
                            <td>{{ $combat->commentaire ?: '____________' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="empty-state">Aucun combat généré</p>
        @endif
    </main>

    <script>
        window.addEventListener('load', () => {
            window.print();
        });
    </script>
</body>
</html>
