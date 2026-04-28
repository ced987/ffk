@extends('layouts.app')

@section('title', 'Accueil - FFK Interclubs')
@section('page-title', 'Accueil')

@push('styles')
<style>
    main {
        width: min(1040px, calc(100% - 32px));
        margin: 32px auto;
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

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-top: 24px;
    }

    section {
        padding: 22px;
        border: 1px solid #dce1e7;
        border-radius: 8px;
        background: #ffffff;
    }

    h2 {
        margin: 0 0 14px;
        font-size: 18px;
    }

    ul {
        display: grid;
        gap: 10px;
        margin: 0;
        padding: 0;
        list-style: none;
        color: #334155;
    }

    .summary-list {
        gap: 12px;
    }

    .summary-list strong {
        display: block;
        color: #17202a;
        font-size: 24px;
        line-height: 1.1;
    }

    .quick-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 24px;
    }

    .primary-action,
    .secondary-action {
        display: inline-block;
        padding: 9px 12px;
        border: 1px solid #1d4ed8;
        border-radius: 8px;
        font-weight: 700;
        text-decoration: none;
    }

    .primary-action {
        background: #1d4ed8;
        color: #ffffff;
    }

    .secondary-action {
        background: #ffffff;
        color: #1d4ed8;
    }

    @media (max-width: 900px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<main>
    <h1>Tableau de bord</h1>
    <p>Vue rapide de la démonstration MVP.</p>

    <div class="dashboard-grid">
        <section>
            <h2>Actions à faire</h2>
            <ul>
                <li>2 inscriptions à valider</li>
                <li>1 poule à créer</li>
                <li>4 combats à saisir</li>
            </ul>
        </section>

        <section>
            <h2>Prochaines compétitions</h2>
            <ul>
                <li>Competition Demo MVP - 28/05/2026</li>
                <li>Competition Demo Aujourd’hui - 28/04/2026</li>
                <li>Open Interclubs Test - Date non renseignée</li>
            </ul>
        </section>

        <section>
            <h2>Résumé</h2>
            <ul class="summary-list">
                <li><strong>3</strong> Compétitions</li>
                <li><strong>48</strong> Participants</li>
                <li><strong>12</strong> Combats restants</li>
            </ul>
        </section>
    </div>

    <div class="quick-actions">
        <a class="primary-action" href="{{ route('competitions.index') }}">Voir les compétitions</a>
        <a class="secondary-action" href="{{ route('licencies.index') }}">Voir mes licenciés</a>
        <a class="secondary-action" href="{{ route('guide') }}">Aide / Comment ça marche</a>
    </div>
</main>
@endsection
