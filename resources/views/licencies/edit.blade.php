@extends('layouts.app')

@section('title', 'Modifier un licencié - FFK Interclubs')
@section('page-title', 'Modifier un licencié')

@push('styles')
<style>
        body {
            margin: 0;
            background: #f6f7f9;
            color: #17202a;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        main {
            width: min(720px, calc(100% - 32px));
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

        label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-weight: 600;
        }

        input,
        select {
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

        .form-grid {
            display: grid;
            gap: 14px;
        }

        .error {
            margin-top: 8px;
            color: #b91c1c;
        }
</style>
@endpush

@section('content')
<main>
        <h1>Modifier un licencié</h1>
        <p>{{ $currentUser->name }} - {{ $currentUser->club?->name }}</p>
        <p><a href="{{ route('licencies.index') }}">Retour à mes licenciés</a></p>

        <section>
            <form method="POST" action="{{ route('licencies.update', $licencie) }}">
                @csrf
                @method('PATCH')

                <div class="form-grid">
                    <div>
                        <label for="nom">Nom</label>
                        <input id="nom" name="nom" value="{{ old('nom', $licencie->nom) }}" required>
                        @error('nom')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="prenom">Prénom</label>
                        <input id="prenom" name="prenom" value="{{ old('prenom', $licencie->prenom) }}" required>
                        @error('prenom')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="date_naissance">Date de naissance</label>
                        <input id="date_naissance" name="date_naissance" type="date" value="{{ old('date_naissance', $licencie->date_naissance->toDateString()) }}" required>
                        @error('date_naissance')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="sexe">Sexe</label>
                        <select id="sexe" name="sexe" required>
                            <option value="">Sélectionner</option>
                            <option value="masculin" @selected(old('sexe', $licencie->sexe) === 'masculin')>Masculin</option>
                            <option value="feminin" @selected(old('sexe', $licencie->sexe) === 'feminin')>Féminin</option>
                        </select>
                        @error('sexe')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="poids">Poids</label>
                        <input id="poids" name="poids" type="number" min="1" step="1" value="{{ old('poids', $licencie->poids) }}" required>
                        @error('poids')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit">Enregistrer les modifications</button>
            </form>
        </section>
    </main>
@endsection
