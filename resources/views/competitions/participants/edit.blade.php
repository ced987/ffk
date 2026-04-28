@extends('layouts.app')

@section('title', 'Modifier participant - FFK Interclubs')
@section('page-title', 'Modifier participant')

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

        .form-grid {
            display: grid;
            gap: 14px;
            max-width: 460px;
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

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 50;
            max-width: min(360px, calc(100vw - 40px));
            padding: 14px 16px;
            border: 1px solid #86efac;
            border-radius: 8px;
            background: #f0fdf4;
            color: #166534;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.16);
            font-weight: 700;
            transition: opacity 180ms ease, transform 180ms ease;
        }

        .toast.is-hidden {
            opacity: 0;
            transform: translateY(-8px);
            pointer-events: none;
        }
</style>
@endpush

@section('content')
<main>
        <h1>Modifier participant</h1>
        <p>{{ $competition->name }} - {{ $currentUser->club?->name }}</p>
        <p><a href="{{ route('competitions.show', $competition) }}">Retour compétition</a></p>

        @if (session('status'))
            <div class="toast" data-toast>{{ session('status') }}</div>
        @endif

        <section>
            <form method="POST" action="{{ route('competitions.participants.update', [$competition, $registration]) }}">
                @csrf
                @method('PATCH')

                <div class="form-grid">
                    <div>
                        <label for="last_name">Nom</label>
                        <input id="last_name" name="last_name" value="{{ old('last_name', $participant->last_name) }}" required>
                        @error('last_name')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="first_name">Prénom</label>
                        <input id="first_name" name="first_name" value="{{ old('first_name', $participant->first_name) }}" required>
                        @error('first_name')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="sex">Sexe</label>
                        <select id="sex" name="sex" required>
                            <option value="F" @selected(old('sex', $participant->sex) === 'F')>F</option>
                            <option value="M" @selected(old('sex', $participant->sex) === 'M')>M</option>
                        </select>
                        @error('sex')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="age">Âge</label>
                        <input id="age" name="age" type="number" min="1" max="120" value="{{ old('age', $participant->age) }}" required>
                        @error('age')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="approximate_weight">Poids approximatif</label>
                        <input id="approximate_weight" name="approximate_weight" type="number" min="1" max="300" step="0.1" value="{{ old('approximate_weight', $participant->approximate_weight) }}" required>
                        @error('approximate_weight')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="license_number">Numéro de licence optionnel</label>
                        <input id="license_number" name="license_number" value="{{ old('license_number', $participant->license_number) }}">
                        @error('license_number')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit">Enregistrer les modifications</button>
            </form>
        </section>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toast = document.querySelector('[data-toast]');

            if (! toast) {
                return;
            }

            window.setTimeout(() => {
                toast.classList.add('is-hidden');
            }, 3500);
        });
    </script>
@endsection
