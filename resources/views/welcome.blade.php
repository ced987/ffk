@extends('layouts.app')

@section('title', 'Accueil - FFK Interclubs')
@section('page-title', 'Accueil')

@push('styles')
<style>
    .home-dashboard {
        width: min(1180px, calc(100% - 32px));
        margin: 22px auto 32px;
    }

    .home-hero {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 14px;
    }

    .home-hero h1 {
        margin: 0 0 6px;
        color: #17202a;
        font-size: 28px;
        line-height: 1.15;
    }

    .home-hero p {
        margin: 0;
        color: #64748b;
        font-size: 15px;
    }

    .home-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 12px;
    }

    .home-kpi {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 9px;
        align-items: center;
        padding: 10px 12px;
        border: 1px solid #dbe3ef;
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.035);
    }

    .home-kpi-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 17px;
        font-weight: 900;
    }

    .home-kpi.warning .home-kpi-icon {
        background: #fff7ed;
        color: #c2410c;
    }

    .home-kpi.success .home-kpi-icon {
        background: #f0fdf4;
        color: #166534;
    }

    .home-kpi strong {
        display: block;
        overflow: hidden;
        color: #17202a;
        font-size: 20px;
        line-height: 1.05;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .home-kpi span:last-child {
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
    }

    .home-content-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(360px, 0.92fr);
        gap: 12px;
        margin-bottom: 12px;
    }

    .home-card {
        padding: 14px;
        border: 1px solid #dbe3ef;
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.03);
    }

    .home-card h2 {
        margin: 0 0 10px;
        color: #17202a;
        font-size: 18px;
    }

    .home-list {
        display: grid;
        gap: 6px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .home-action-row,
    .home-competition-row,
    .home-quick-link {
        display: grid;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        background: #fbfdff;
        text-decoration: none;
    }

    .home-action-row {
        grid-template-columns: auto minmax(0, 1fr) auto;
    }

    .home-action-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 30px;
        height: 30px;
        padding: 0 8px;
        border-radius: 10px;
        background: #fff7ed;
        color: #c2410c;
        font-weight: 900;
    }

    .home-action-row strong,
    .home-competition-row strong,
    .home-quick-link strong {
        display: block;
        overflow: hidden;
        color: #17202a;
        font-size: 14px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .home-action-row span,
    .home-competition-row span,
    .home-quick-link span {
        color: #64748b;
        font-size: 12px;
    }

    .home-chevron {
        color: #94a3b8;
        font-size: 18px;
        font-weight: 900;
    }

    .home-competition-row {
        grid-template-columns: 52px minmax(0, 1fr) auto;
    }

    .home-date-tile {
        display: grid;
        place-items: center;
        min-height: 42px;
        border-radius: 9px;
        background: #eff6ff;
        color: #1d4ed8;
        font-weight: 900;
        line-height: 1.05;
        text-align: center;
    }

    .home-date-tile small {
        color: #475569;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .home-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 8px;
        border: 1px solid #bbf7d0;
        border-radius: 999px;
        background: #f0fdf4;
        color: #166534;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .home-status-badge.muted {
        border-color: #cbd5e1;
        background: #f8fafc;
        color: #475569;
    }

    .home-quick-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        margin-top: 10px;
    }

    .home-quick-link {
        grid-template-columns: auto minmax(0, 1fr) auto;
        color: inherit;
    }

    .home-quick-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 9px;
        background: #eff6ff;
        color: #1d4ed8;
        font-weight: 900;
    }

    .home-empty {
        margin: 0;
        padding: 12px;
        border: 1px dashed #cbd5e1;
        border-radius: 9px;
        color: #64748b;
        background: #f8fafc;
        font-size: 14px;
    }

    @media (max-width: 980px) {
        .home-kpi-grid,
        .home-content-grid,
        .home-quick-grid {
            grid-template-columns: 1fr;
        }

        .home-hero {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<main class="home-dashboard">
    <div class="home-hero">
        <div>
            <h1>Tableau de bord</h1>
            <p>Retrouvez vos actions prioritaires et vos prochaines compétitions.</p>
        </div>
    </div>

    <div class="home-kpi-grid">
        <div class="home-kpi">
            <span class="home-kpi-icon">🏆</span>
            <span>
                <strong>{{ $competitionCount }}</strong>
                <span>Compétitions</span>
            </span>
        </div>
        <div @class(['home-kpi', 'warning' => $urgentActions->isNotEmpty(), 'success' => $urgentActions->isEmpty()])>
            <span class="home-kpi-icon">!</span>
            <span>
                <strong>{{ $urgentActions->count() }}</strong>
                <span>Actions urgentes</span>
            </span>
        </div>
        <div class="home-kpi">
            <span class="home-kpi-icon">📅</span>
            <span>
                <strong>{{ $nextCompetition?->date_competition?->format('d/m') ?? 'À planifier' }}</strong>
                <span>Prochaine compétition</span>
            </span>
        </div>
        <div class="home-kpi">
            <span class="home-kpi-icon">👥</span>
            <span>
                <strong>{{ $licencieCount }}</strong>
                <span>Licenciés du club</span>
            </span>
        </div>
    </div>

    <div class="home-content-grid">
        <section class="home-card">
            <h2>Actions à faire</h2>
            @if ($urgentActions->isNotEmpty())
                <ul class="home-list">
                    @foreach ($urgentActions->take(5) as $action)
                        @php
                            preg_match('/\d+/', $action['label'], $matches);
                            $actionCount = $matches[0] ?? '!';
                        @endphp
                        <li>
                            <a class="home-action-row" href="{{ route('competitions.show', ['competition' => $action['competition'], 'tab' => 'suivi']) }}">
                                <span class="home-action-count">{{ $actionCount }}</span>
                                <span>
                                    <strong>{{ $action['label'] }}</strong>
                                    <span>{{ $action['competition']->name }}</span>
                                </span>
                                <span class="home-chevron">›</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="home-empty">Tout est à jour pour le moment.</p>
            @endif
        </section>

        <section class="home-card">
            <h2>Prochaines compétitions</h2>
            @if ($upcomingCompetitions->isNotEmpty())
                <ul class="home-list">
                    @foreach ($upcomingCompetitions as $competition)
                        <li>
                            <a class="home-competition-row" href="{{ route('competitions.show', ['competition' => $competition, 'tab' => 'suivi']) }}">
                                <span class="home-date-tile">
                                    @if ($competition->date_competition)
                                        {{ $competition->date_competition->format('d') }}
                                        <small>{{ $competition->date_competition->translatedFormat('M') }}</small>
                                    @else
                                        --
                                        <small>Date</small>
                                    @endif
                                </span>
                                <span>
                                    <strong>{{ $competition->name }}</strong>
                                    <span>{{ $competition->organizerClub->name }}</span>
                                </span>
                                <span @class(['home-status-badge', 'muted' => $competition->inscriptions_closed])>
                                    {{ $competition->inscriptions_closed ? 'À venir' : 'Ouverte' }}
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="home-empty">Aucune compétition à venir.</p>
            @endif
        </section>
    </div>

    <section class="home-card">
        <h2>Accès rapides</h2>
        <div class="home-quick-grid">
            <a class="home-quick-link" href="{{ route('competitions.index') }}">
                <span class="home-quick-icon">🏆</span>
                <span>
                    <strong>Voir les compétitions</strong>
                    <span>Suivre, organiser et saisir les résultats</span>
                </span>
                <span class="home-chevron">›</span>
            </a>
            <a class="home-quick-link" href="{{ route('licencies.index') }}">
                <span class="home-quick-icon">👥</span>
                <span>
                    <strong>Voir mes licenciés</strong>
                    <span>Consulter la base du club</span>
                </span>
                <span class="home-chevron">›</span>
            </a>
            <a class="home-quick-link" href="{{ route('guide') }}">
                <span class="home-quick-icon">?</span>
                <span>
                    <strong>Aide / Comment ça marche</strong>
                    <span>Comprendre le parcours de démonstration</span>
                </span>
                <span class="home-chevron">›</span>
            </a>
        </div>
    </section>
</main>
@endsection
