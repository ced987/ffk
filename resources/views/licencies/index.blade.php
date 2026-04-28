@extends('layouts.app')

@section('title', 'Mes licenciés - FFK Interclubs')
@section('page-title', 'Mes licenciés')

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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #dce1e7;
            text-align: left;
        }

        th {
            background: #f8fafc;
            color: #334155;
        }

        .empty-state {
            color: #64748b;
        }

        .primary-action {
            display: inline-block;
            margin-top: 18px;
            padding: 10px 14px;
            border: 1px solid #1d4ed8;
            border-radius: 8px;
            background: #1d4ed8;
            color: #ffffff;
            font-weight: 700;
            text-decoration: none;
        }

        .status-message {
            margin-top: 18px;
            padding: 10px 12px;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            background: #f0fdf4;
            color: #166534;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
            margin-top: 14px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 700;
        }

        input {
            min-width: min(340px, 100%);
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font: inherit;
        }

        button {
            min-height: 40px;
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            color: #17202a;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        .actions-cell {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .inline-form {
            display: inline;
        }

        .danger-button {
            color: #b91c1c;
        }

        .error {
            margin-top: 6px;
            color: #b91c1c;
            font-size: 14px;
        }
</style>
@endpush

@section('content')
<main>
        <h1>Mes licenciés</h1>
        <p>{{ $currentUser->name }} - {{ $currentUser->club?->name }}</p>
        <p><a class="primary-action" href="{{ route('licencies.create') }}">Ajouter un licencié</a></p>

        @if (session('status'))
            <p class="status-message">{{ session('status') }}</p>
        @endif

        <section>
            <h2>Mon club</h2>
            <form method="POST" action="{{ route('club.update') }}">
                @csrf
                @method('PATCH')
                <div class="form-row">
                    <div>
                        <label for="club-name">Nom du club</label>
                        <input id="club-name" type="text" name="name" value="{{ old('name', $currentUser->club?->name) }}" required maxlength="255">
                        @error('name')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit">Enregistrer</button>
                </div>
            </form>
        </section>

        <section>
            @if ($licencies->isNotEmpty())
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Date de naissance</th>
                            <th>Âge</th>
                            <th>Sexe</th>
                            <th>Poids</th>
                            <th>Action</th>
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
                            <tr>
                                <td>{{ $licencie->nom }}</td>
                                <td>{{ $licencie->prenom }}</td>
                                <td>{{ $licencie->date_naissance->format('d/m/Y') }}</td>
                                <td>{{ $licencie->date_naissance->age }}</td>
                                <td>{{ $sexeCourt }}</td>
                                <td>{{ $licencie->poids }} kg</td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="{{ route('licencies.edit', $licencie) }}">Modifier</a>
                                        <form class="inline-form" method="POST" action="{{ route('licencies.destroy', $licencie) }}" onsubmit="return confirm('Supprimer ce licencié ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="danger-button" type="submit">Supprimer</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="empty-state">Aucun licencié pour ce club.</p>
            @endif
        </section>
    </main>
@endsection
