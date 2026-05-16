@extends('layouts.app')

@section('title', 'Mes licenciés - FFK Interclubs')
@section('page-title', 'Mes licenciés')

@push('styles')
<style>
    .licencies-page {
        width: min(1120px, calc(100% - 32px));
        margin: 28px auto 40px;
    }

    .licencies-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 14px;
    }

    .licencies-header h1 {
        margin: 0 0 5px;
        color: #17202a;
        font-size: 28px;
        line-height: 1.15;
    }

    .licencies-header p,
    .licencies-muted {
        margin: 0;
        color: #64748b;
        font-size: 14px;
    }

    .primary-action,
    .secondary-action,
    .danger-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 7px 11px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        background: #ffffff;
        color: #334155;
        font: inherit;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .primary-action {
        border-color: #1d4ed8;
        background: #1d4ed8;
        color: #ffffff;
    }

    .danger-button {
        color: #b91c1c;
    }

    .icon-action {
        width: 30px;
        min-width: 30px;
        min-height: 30px;
        padding: 0;
        font-size: 14px;
        line-height: 1;
    }

    .licencie-kpis {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 12px;
    }

    .licencie-kpi {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 10px;
        align-items: center;
        padding: 10px 12px;
        border: 1px solid #dbe3ef;
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.03);
    }

    .licencie-kpi-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: #eff6ff;
        color: #1d4ed8;
        font-weight: 900;
    }

    .licencie-kpi.feminine .licencie-kpi-icon {
        background: #fdf2f8;
        color: #be185d;
    }

    .licencie-kpi.masculine .licencie-kpi-icon {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .licencie-kpi strong {
        display: block;
        color: #17202a;
        font-size: 21px;
        line-height: 1.05;
    }

    .licencie-kpi span:last-child {
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
    }

    .status-message {
        margin: 0 0 12px;
        padding: 10px 12px;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        background: #f0fdf4;
        color: #166534;
    }

    .licencie-card {
        margin-bottom: 12px;
        padding: 14px;
        border: 1px solid #dbe3ef;
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.03);
    }

    .club-card {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 12px;
        align-items: end;
    }

    .club-card h2,
    .licencie-card h2 {
        margin: 0 0 4px;
        color: #17202a;
        font-size: 17px;
    }

    .club-form {
        display: flex;
        align-items: flex-end;
        justify-content: flex-end;
        gap: 8px;
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
        min-height: 34px;
        padding: 7px 10px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        background: #ffffff;
        font: inherit;
    }

    .club-form input {
        width: 280px;
    }

    .error {
        margin-top: 5px;
        color: #b91c1c;
        font-size: 13px;
    }

    .licencie-toolbar {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
    }

    .licencie-search {
        width: min(360px, 100%);
    }

    .licencie-table-wrap {
        overflow-x: auto;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background: #ffffff;
    }

    th,
    td {
        padding: 8px 10px;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
        vertical-align: middle;
    }

    th {
        background: #f8fafc;
        color: #334155;
        font-size: 12px;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    tbody tr:hover {
        background: #fbfdff;
    }

    tbody tr:last-child td {
        border-bottom: 0;
    }

    .licencie-name {
        color: #17202a;
        font-weight: 850;
    }

    .sex-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        padding: 3px 8px;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        background: #f8fafc;
        color: #334155;
        font-size: 12px;
        font-weight: 850;
    }

    .sex-badge.f {
        border-color: #fbcfe8;
        background: #fdf2f8;
        color: #be185d;
    }

    .sex-badge.m {
        border-color: #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .actions-cell {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        align-items: center;
    }

    .inline-form {
        display: inline;
    }

    .empty-state {
        margin: 0;
        padding: 12px;
        border: 1px dashed #cbd5e1;
        border-radius: 9px;
        color: #64748b;
        background: #f8fafc;
        font-size: 14px;
    }

    @media (max-width: 840px) {
        .licencies-header,
        .club-card,
        .club-form,
        .licencie-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .club-card {
            grid-template-columns: 1fr;
        }

        .club-form input {
            width: 100%;
        }

        .licencie-kpis {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<main class="licencies-page">
    @php
        $femaleCount = $licencies->where('sexe', 'feminin')->count();
        $maleCount = $licencies->where('sexe', 'masculin')->count();
    @endphp

    <div class="licencies-header">
        <div>
            <h1>Mes licenciés</h1>
            <p>{{ $currentUser->club?->name }}</p>
        </div>
        <a class="btn btn-primary" href="{{ route('licencies.create') }}">Ajouter un licencié</a>
    </div>

    <div class="licencie-kpis">
        <div class="licencie-kpi">
            <span class="licencie-kpi-icon">#</span>
            <span>
                <strong>{{ $licencies->count() }}</strong>
                <span>Total licenciés</span>
            </span>
        </div>
        <div class="licencie-kpi feminine">
            <span class="licencie-kpi-icon">F</span>
            <span>
                <strong>{{ $femaleCount }}</strong>
                <span>Féminines</span>
            </span>
        </div>
        <div class="licencie-kpi masculine">
            <span class="licencie-kpi-icon">M</span>
            <span>
                <strong>{{ $maleCount }}</strong>
                <span>Masculins</span>
            </span>
        </div>
    </div>

    @if (session('status'))
        <p class="status-message">{{ session('status') }}</p>
    @endif

    <section class="licencie-card club-card">
        <div>
            <h2>Mon club</h2>
            <p class="licencies-muted">Nom utilisé dans les compétitions et les éditions de démonstration.</p>
        </div>
        <form class="club-form" method="POST" action="{{ route('club.update') }}">
            @csrf
            @method('PATCH')
            <div>
                <label for="club-name">Nom du club</label>
                <input id="club-name" type="text" name="name" value="{{ old('name', $currentUser->club?->name) }}" required maxlength="255">
                @error('name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
            <button class="btn btn-success" type="submit">Enregistrer</button>
        </form>
    </section>

    <section class="licencie-card">
        <div class="licencie-toolbar">
            <div>
                <h2>Liste des licenciés</h2>
                <p class="licencies-muted">Recherche rapide par nom ou prénom.</p>
            </div>
            <div class="licencie-search">
                <label for="licencie-search">Rechercher un licencié</label>
                <input id="licencie-search" type="search" placeholder="Nom ou prénom" data-licencie-search>
            </div>
        </div>

        @if ($licencies->isNotEmpty())
            <div class="licencie-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Date de naissance</th>
                            <th>Âge</th>
                            <th>Sexe</th>
                            <th>Poids</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($licencies as $licencie)
                            @php
                                $sexeCourt = match ($licencie->sexe) {
                                    'masculin' => 'M',
                                    'feminin' => 'F',
                                    default => $licencie->sexe,
                                };
                            @endphp
                            <tr data-licencie-row data-search-text="{{ Str::lower($licencie->nom.' '.$licencie->prenom) }}">
                                <td class="licencie-name">{{ $licencie->nom }}</td>
                                <td>{{ $licencie->prenom }}</td>
                                <td>{{ $licencie->date_naissance->format('d/m/Y') }}</td>
                                <td>{{ $licencie->date_naissance->age }} ans</td>
                                <td><span @class(['sex-badge', 'm' => $sexeCourt === 'M', 'f' => $sexeCourt === 'F'])>{{ $sexeCourt }}</span></td>
                                <td>{{ $licencie->poids }} kg</td>
                                <td>
                                    <div class="actions-cell">
                                        <a class="btn btn-icon btn-sm" href="{{ route('licencies.edit', $licencie) }}" title="Modifier" aria-label="Modifier">✏️</a>
                                        <form class="inline-form" method="POST" action="{{ route('licencies.destroy', $licencie) }}" onsubmit="return confirm('Supprimer ce licencié ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-icon btn-sm btn-icon-danger" type="submit" title="Supprimer" aria-label="Supprimer">🗑️</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="empty-state" data-licencie-empty hidden>Aucun licencié ne correspond à la recherche.</p>
        @else
            <p class="empty-state">Aucun licencié pour ce club.</p>
        @endif
    </section>
</main>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-licencie-search]').forEach((input) => {
        const rows = Array.from(document.querySelectorAll('[data-licencie-row]'));
        const emptyState = document.querySelector('[data-licencie-empty]');

        input.addEventListener('input', () => {
            const query = input.value.trim().toLowerCase();
            let visibleCount = 0;

            rows.forEach((row) => {
                const matches = row.dataset.searchText.includes(query);
                row.hidden = ! matches;
                visibleCount += matches ? 1 : 0;
            });

            if (emptyState) {
                emptyState.hidden = visibleCount > 0;
            }
        });
    });
</script>
@endpush
