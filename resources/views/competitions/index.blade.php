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
            width: min(1180px, calc(100% - 32px));
            margin: 30px auto 40px;
        }

        h1 {
            margin: 0 0 6px;
            color: #0f172a;
            font-size: 34px;
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
            margin-top: 16px;
            padding: 0;
            background: #ffffff;
            border: 1px solid #dce1e7;
            border-radius: 12px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.035);
            overflow: hidden;
        }

        h2 {
            margin: 0;
            font-size: 18px;
        }

        .page-intro {
            margin-bottom: 22px;
            color: #64748b;
            font-size: 17px;
        }

        .create-competition-card {
            padding: 12px 16px;
        }

        .create-competition-form {
            display: grid;
            grid-template-columns: 52px minmax(180px, 240px) minmax(300px, 1fr) auto;
            gap: 12px;
            align-items: center;
        }

        .create-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 26px;
            font-weight: 500;
        }

        .create-title {
            display: flex;
            align-items: center;
            min-height: 44px;
            color: #17202a;
            font-size: 18px;
            font-weight: 850;
        }

        .create-field {
            min-width: 0;
        }

        .section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 18px 18px 12px;
        }

        .section-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px 9px;
            border: 1px solid #dbe3ef;
            border-radius: 999px;
            background: #f8fafc;
            color: #64748b;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        .competition-list {
            display: grid;
            gap: 0;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .competition-list li {
            display: grid;
            grid-template-columns: 52px minmax(0, 1fr) auto 18px;
            gap: 12px;
            align-items: center;
            min-height: 58px;
            padding: 7px 16px;
            border: 0;
            border-top: 1px solid #e2e8f0;
            border-radius: 0;
            background: #ffffff;
            box-shadow: none;
        }

        .competition-list li[data-card-url] {
            cursor: pointer;
            transition: background 140ms ease, box-shadow 140ms ease;
        }

        .competition-list li[data-card-url]:hover {
            background: #f8fbff;
        }

        .competition-list li[data-card-url]:focus-visible {
            position: relative;
            z-index: 1;
            outline: 2px solid #2563eb;
            outline-offset: -2px;
        }

        .competition-list li.demo-competition {
            background: #fbfdff;
        }

        .competition-list strong {
            min-width: 0;
            color: #17202a;
            font-size: 18px;
            line-height: 1.2;
        }

        .competition-date-card {
            display: inline-grid;
            grid-template-rows: 14px 20px 12px;
            align-items: center;
            justify-items: center;
            width: 44px;
            height: 48px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: #ffffff;
            color: #17202a;
            font-size: 20px;
            font-weight: 900;
            line-height: 1;
            box-shadow: 0 2px 5px rgba(15, 23, 42, 0.06);
        }

        .competition-date-card strong {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: #dc2626;
            color: #ffffff;
            font-size: 9px;
            line-height: 1;
            text-transform: uppercase;
        }

        .competition-date-card span {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            color: #17202a;
            font-size: 20px;
            font-weight: 900;
            text-transform: lowercase;
            line-height: 1;
        }

        .competition-date-card em {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            color: #475569;
            font-size: 9px;
            font-style: normal;
            font-weight: 900;
            text-transform: uppercase;
            line-height: 1;
        }

        .competition-actions .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .competition-content {
            display: flex;
            align-items: flex-start;
            flex-direction: column;
            gap: 3px;
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
            display: flex;
            align-items: center;
            justify-content: flex-start;
            flex-wrap: wrap;
            gap: 10px;
            color: #475569;
            font-size: 14px;
            font-weight: 650;
        }

        .competition-row-details span {
            white-space: nowrap;
        }

        .competition-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 360px;
        }

        .competition-actions .primary-action {
            min-width: 88px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px 9px;
            border: 1px solid #cfd6df;
            border-radius: 999px;
            background: #ffffff;
            color: #334155;
            font-size: 12px;
            font-weight: 800;
            line-height: 1.4;
            white-space: nowrap;
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
            border-color: #fed7aa;
            background: #fff7ed;
            color: #9a3412;
            font-size: 12px;
        }

        .badge-actions-current {
            border-color: #86efac;
            background: #f0fdf4;
            color: #166534;
        }

        .competition-status-open {
            color: #166534;
        }

        .competition-status-open::before {
            content: "";
            display: inline-block;
            width: 8px;
            height: 8px;
            margin-right: 6px;
            border-radius: 999px;
            background: #16a34a;
        }

        .competition-status-closed,
        .competition-status-finished {
            color: #475569;
        }

        .primary-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 36px;
            padding: 8px 15px;
            border: 1px solid #1d4ed8;
            border-radius: 8px;
            background: #1d4ed8;
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #334155;
            font-size: 12px;
            font-weight: 800;
        }

        input {
            width: 100%;
            max-width: none;
            min-height: 36px;
            padding: 8px 12px;
            border: 1px solid #cfd6df;
            border-radius: 8px;
            background: #ffffff;
        }

        button {
            min-height: 36px;
            margin-top: 0;
            padding: 7px 14px;
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

        .competition-title-line {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }

        .competition-chevron {
            color: #64748b;
            font-size: 24px;
            font-weight: 300;
        }

        @media (max-width: 760px) {
            .create-competition-form,
            .competition-list li {
                grid-template-columns: 1fr;
            }

            .create-title,
            .create-field {
                padding-right: 0;
                border-right: 0;
            }

            .competition-actions {
                align-items: flex-start;
                flex-direction: column;
                min-width: 0;
            }

        }
</style>
@endpush

@section('content')
<main>
        <h1>Mes compétitions</h1>
        <p class="page-intro">Créez et gérez vos compétitions.</p>

        <section class="create-competition-card">
            <form class="create-competition-form" method="POST" action="{{ route('competitions.store') }}">
                @csrf

                <span class="create-icon">+</span>
                <h2 class="create-title">Créer une compétition</h2>
                <div class="create-field">
                    <label for="name">Nom de la compétition</label>
                    <input id="name" name="name" value="{{ old('name') }}" placeholder="Ex. : Championnat Interclubs 2026" required>

                    @error('name')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
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
            $dateMonths = [
                1 => 'JAN',
                2 => 'FÉV',
                3 => 'MAR',
                4 => 'AVR',
                5 => 'MAI',
                6 => 'JUIN',
                7 => 'JUIL',
                8 => 'AOÛT',
                9 => 'SEP',
                10 => 'OCT',
                11 => 'NOV',
                12 => 'DÉC',
            ];
            $dateWeekdays = [
                1 => 'LUN',
                2 => 'MAR',
                3 => 'MER',
                4 => 'JEU',
                5 => 'VEN',
                6 => 'SAM',
                7 => 'DIM',
            ];
        @endphp

        @foreach ([
            'Compétitions à venir' => $upcomingCompetitions,
            'Compétitions passées' => $pastCompetitions,
        ] as $sectionTitle => $sectionCompetitions)
            <section>
                <div class="section-heading">
                    <h2>{{ $sectionTitle }}</h2>
                    <span class="section-count">{{ $sectionCompetitions->count() }} compétition(s)</span>
                </div>

                @if ($sectionCompetitions->isNotEmpty())
                <ul class="competition-list">
                    @foreach ($sectionCompetitions as $competition)
                        @php($isDemoCompetition = $competition->name === $demoCompetitionName)
                        @php($roleLabel = $competition->roleLabelForClub($currentUser->club))
                        @php($actionsCount = collect($competition->actionsToDoForClub($currentUser->club))->reject(fn ($action) => $action === 'Aucune action urgente')->count())
                        @php($competitionUrl = route('competitions.show', $competition))
                        @php($isPastCompetition = $competition->date_competition !== null && $competition->date_competition->lessThan($today))
                        @php($statusLabel = $isPastCompetition ? '✅ clôturé' : ($competition->inscriptions_closed ? '🔒 fermé' : '🔓 ouvert'))
                        @php($date = $competition->date_competition)
                        @php($dateMonth = $date ? $dateMonths[$date->month] : 'DATE')
                        @php($dateWeekday = $date ? $dateWeekdays[$date->dayOfWeekIso] : 'N/R')
                        <li
                            @class(['demo-competition' => $isDemoCompetition])
                            data-card-url="{{ $competitionUrl }}"
                            role="link"
                            tabindex="0"
                            aria-label="Ouvrir {{ $competition->name }}"
                        >
                            <span class="competition-date-card">
                                <strong>{{ $dateMonth }}</strong>
                                <span>{{ $date?->format('d') ?? '--' }}</span>
                                <em>{{ $dateWeekday }}</em>
                            </span>
                            <div class="competition-content">
                                <div class="competition-title-line">
                                    <strong>{{ $competition->name }}</strong>
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
                            </div>
                            <div class="competition-actions">
                                <span @class([
                                    'badge',
                                    'badge-role-organizer' => $roleLabel === 'Organisateur',
                                    'badge-role-participant' => $roleLabel === 'Participant',
                                    'badge-role-invited' => $roleLabel === 'Invité',
                                    'badge-role-unrelated' => $roleLabel === 'Non concerné',
                                ])>{{ $roleLabel }}</span>
                                @if ($actionsCount > 0)
                                    <span class="badge badge-actions-pending"><span class="sr-only">Actions : {{ $actionsCount }}</span>{{ $actionsCount }} action{{ $actionsCount > 1 ? 's' : '' }}</span>
                                @endif
                                <a class="primary-action" href="{{ $competitionUrl }}">
                                    {{ $roleLabel === 'Organisateur' ? 'Gérer' : 'Voir' }}
                                </a>
                            </div>
                            <span class="competition-chevron">›</span>
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

@push('scripts')
<script>
    document.querySelectorAll('[data-card-url]').forEach((card) => {
        const interactiveSelector = 'a, button, input, select, textarea, label, [role="button"]';

        const openCard = (event) => {
            if (event.target.closest(interactiveSelector)) {
                return;
            }

            window.location.href = card.dataset.cardUrl;
        };

        card.addEventListener('click', openCard);
        card.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            event.preventDefault();
            openCard(event);
        });
    });
</script>
@endpush
