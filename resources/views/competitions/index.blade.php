@extends('layouts.app')

@section('title', 'Mes compétitions - FFK Interclubs')
@section('page-title', 'Mes compétitions')

@push('styles')
<style>
        body {
            margin: 0;
            background: #f6f7f9;
            color: #17202a;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        main {
            width: min(920px, calc(100% - 32px));
            margin: 48px auto;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 28px;
            line-height: 1.2;
        }

        p {
            margin: 0;
            color: #5f6b7a;
        }

        a {
            color: #1d4ed8;
        }

        section {
            margin-top: 24px;
            padding: 24px;
            background: #ffffff;
            border: 1px solid #dce1e7;
            border-radius: 8px;
        }

        h2 {
            margin: 0 0 16px;
            font-size: 18px;
        }

        .competition-list {
            display: grid;
            gap: 10px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .competition-list li {
            display: grid;
            gap: 8px;
            padding: 12px 14px;
            border: 1px solid #dce1e7;
            border-radius: 8px;
            background: #f8fafc;
        }

        .competition-list li.demo-competition {
            background: #f1f5f9;
        }

        .competition-list strong {
            min-width: 0;
            font-size: 16px;
        }

        .competition-row-main,
        .competition-row-details {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            min-width: 0;
        }

        .competition-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .competition-row-details {
            justify-content: flex-start;
            flex-wrap: wrap;
            color: #5f6b7a;
            font-size: 14px;
        }

        .competition-row-details span {
            white-space: nowrap;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border: 1px solid #cfd6df;
            border-radius: 999px;
            background: #ffffff;
            color: #334155;
            font-size: 14px;
            font-weight: 700;
        }

        .badge-role-organizer {
            border-color: #93c5fd;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .badge-role-participant {
            border-color: #86efac;
            background: #f0fdf4;
            color: #166534;
        }

        .badge-role-invited,
        .badge-role-unrelated {
            border-color: #cfd6df;
            background: #f8fafc;
            color: #475569;
        }

        .badge-actions-pending {
            border-color: #fdba74;
            background: #fff7ed;
            color: #9a3412;
        }

        .badge-actions-current {
            border-color: #86efac;
            background: #f0fdf4;
            color: #166534;
        }

        .competition-status-open {
            color: #166534;
        }

        .competition-status-closed,
        .competition-status-finished {
            color: #475569;
        }

        .primary-action {
            display: inline-block;
            padding: 8px 12px;
            border: 1px solid #1d4ed8;
            border-radius: 8px;
            background: #1d4ed8;
            color: #ffffff;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-weight: 600;
        }

        input {
            width: 100%;
            max-width: 420px;
            padding: 10px 12px;
            border: 1px solid #cfd6df;
            border-radius: 8px;
            background: #ffffff;
        }

        button {
            margin-top: 12px;
            padding: 10px 14px;
            border: 1px solid #1d4ed8;
            border-radius: 8px;
            background: #1d4ed8;
            color: #ffffff;
            cursor: pointer;
            font-weight: 700;
        }

        .error {
            margin-top: 8px;
            color: #b91c1c;
        }

        .secondary-section {
            background: #fbfdff;
        }

        @media (max-width: 760px) {
            .competition-row-main {
                align-items: flex-start;
                flex-direction: column;
            }

            .competition-meta {
                justify-content: flex-start;
            }
        }
</style>
@endpush

@section('content')
<main>
        <h1>Mes compétitions</h1>

        <section>
            <h2>Créer une compétition</h2>

            <form method="POST" action="{{ route('competitions.store') }}">
                @csrf

                <label for="name">Nom de la compétition</label>
                <input id="name" name="name" value="{{ old('name') }}" required>

                @error('name')
                    <div class="error">{{ $message }}</div>
                @enderror

                <button type="submit">Créer la compétition</button>
            </form>
        </section>

        @php
            $today = today();
            $sortCompetitionsForDisplay = fn ($items) => $items
                ->sortBy(fn ($competition) => implode('|', [
                    $competition->name === $demoCompetitionName ? '0' : '1',
                    $competition->date_competition === null ? '1' : '0',
                    $competition->date_competition?->format('Y-m-d') ?? '9999-12-31',
                    $competition->name,
                ]))
                ->values();
            $upcomingCompetitions = $sortCompetitionsForDisplay($competitions
                ->filter(fn ($competition) => $competition->date_competition === null || $competition->date_competition->greaterThanOrEqualTo($today))
                ->values());
            $pastCompetitions = $sortCompetitionsForDisplay($competitions
                ->filter(fn ($competition) => $competition->date_competition !== null && $competition->date_competition->lessThan($today))
                ->values());
        @endphp

        @foreach ([
            'Compétitions à venir' => $upcomingCompetitions,
            'Compétitions passées' => $pastCompetitions,
        ] as $sectionTitle => $sectionCompetitions)
            <section>
                <h2>{{ $sectionTitle }}</h2>

                @if ($sectionCompetitions->isNotEmpty())
                <ul class="competition-list">
                    @foreach ($sectionCompetitions as $competition)
                        @php($isDemoCompetition = $competition->name === $demoCompetitionName)
                        @php($roleLabel = $competition->roleLabelForClub($currentUser->club))
                        @php($actionsCount = collect($competition->actionsToDoForClub($currentUser->club))->reject(fn ($action) => $action === 'Aucune action urgente')->count())
                        @php($competitionUrl = route('competitions.show', $competition).'#'.$competition->detailFragmentForClub($currentUser->club))
                        @php($isPastCompetition = $competition->date_competition !== null && $competition->date_competition->lessThan($today))
                        @php($statusLabel = $isPastCompetition ? '✅ clôturé' : ($competition->inscriptions_closed ? '🔒 fermé' : '🔓 ouvert'))
                        <li @class(['demo-competition' => $isDemoCompetition])>
                            <div class="competition-row-main">
                                <strong>{{ $competition->name }}</strong>
                                <div class="competition-meta">
                                    <a class="primary-action" href="{{ $competitionUrl }}">
                                        {{ $roleLabel === 'Organisateur' ? 'Gérer' : 'Voir' }}
                                    </a>
                                    <span @class([
                                        'badge',
                                        'badge-role-organizer' => $roleLabel === 'Organisateur',
                                        'badge-role-participant' => $roleLabel === 'Participant',
                                        'badge-role-invited' => $roleLabel === 'Invité',
                                        'badge-role-unrelated' => $roleLabel === 'Non concerné',
                                    ])>{{ $roleLabel }}</span>
                                    @if ($actionsCount > 0)
                                        <span class="badge badge-actions-pending">Actions : {{ $actionsCount }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="competition-row-details">
                                <span>📅 {{ $competition->date_competition?->format('d/m/Y') ?? 'Date non renseignée' }}</span>
                                <span>🏢 {{ $competition->organizerClub->name }}</span>
                                <span @class([
                                    'competition-status-open' => $statusLabel === '🔓 ouvert',
                                    'competition-status-closed' => $statusLabel === '🔒 fermé',
                                    'competition-status-finished' => $statusLabel === '✅ clôturé',
                                ])>{{ $statusLabel }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
                @else
                    <p>Aucune compétition dans cette section.</p>
                @endif
            </section>
        @endforeach
    </main>
@endsection
