@extends('layouts.app')

@section('title', 'Saisir score - FFK Interclubs')
@section('page-title', 'Saisir score')

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

        input {
            width: 100%;
            max-width: 220px;
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
        <h1>Saisir le score</h1>
        <p>{{ $competition->name }} - {{ $combat->poule->name }}</p>
        <p>
            {{ $combat->inscriptionA->participantSource->last_name }}
            {{ $combat->inscriptionA->participantSource->first_name }}
            vs
            {{ $combat->inscriptionB->participantSource->last_name }}
            {{ $combat->inscriptionB->participantSource->first_name }}
        </p>
        <p><a href="{{ route('competitions.show', $competition) }}">Retour compétition</a></p>

        @if (session('status'))
            <div class="toast" data-toast>{{ session('status') }}</div>
        @endif

        <section>
            <form method="POST" action="{{ route('competitions.combats.update', [$competition, $combat]) }}">
                @csrf
                @method('PATCH')

                <div class="form-grid">
                    <div>
                        <label>Résultat</label>
                        <button type="submit" name="resultat" value="{{ \App\Models\Combat::RESULT_LEFT_WIN }}">🟥 Victoire gauche</button>
                        <button type="submit" name="resultat" value="{{ \App\Models\Combat::RESULT_DRAW }}">🤝 Nul</button>
                        <button type="submit" name="resultat" value="{{ \App\Models\Combat::RESULT_RIGHT_WIN }}">🟦 Victoire droite</button>
                        <button type="submit" name="resultat" value="{{ \App\Models\Combat::RESULT_NO_CONTEST }}">🚫 Pas de combat</button>
                        @error('resultat')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="score_a">Score rouge facultatif</label>
                        <input id="score_a" name="score_a" type="number" min="0" value="{{ old('score_a', $combat->score_a) }}">
                    </div>

                    <div>
                        <label for="score_b">Score bleu facultatif</label>
                        <input id="score_b" name="score_b" type="number" min="0" value="{{ old('score_b', $combat->score_b) }}">
                    </div>

                    <div>
                        <label for="commentaire">Commentaire facultatif</label>
                        <input id="commentaire" name="commentaire" type="text" value="{{ old('commentaire', $combat->commentaire) }}">
                    </div>
                </div>
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
