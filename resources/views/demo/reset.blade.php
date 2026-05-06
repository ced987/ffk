@extends('layouts.app')

@section('title', 'Réinitialiser cette démo')
@section('page-title', 'Réinitialiser cette démo')

@push('styles')
<style>
        main {
            width: min(780px, calc(100% - 32px));
            margin: 48px auto;
        }

        .reset-card {
            display: grid;
            gap: 18px;
            padding: 28px;
            border: 1px solid #dce1e7;
            border-radius: 10px;
            background: #ffffff;
        }

        .reset-card h1 {
            margin: 0;
            font-size: 28px;
            line-height: 1.2;
        }

        .reset-card p,
        .reset-card li {
            color: #334155;
            line-height: 1.6;
        }

        .reset-warning {
            padding: 12px 14px;
            border: 1px solid #fde68a;
            border-radius: 8px;
            background: #fffbeb;
            color: #92400e;
            font-weight: 700;
        }

        .reset-form {
            display: grid;
            gap: 10px;
            max-width: 460px;
        }

        .reset-form label {
            color: #334155;
            font-weight: 700;
        }

        .reset-form input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cfd6df;
            border-radius: 8px;
        }

        .reset-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .reset-button {
            min-height: 36px;
            padding: 8px 12px;
            border: 1px solid #b91c1c;
            border-radius: 7px;
            background: #ffffff;
            color: #b91c1c;
            cursor: pointer;
            font-weight: 800;
        }

        .reset-confirmation[hidden] {
            display: none;
        }

        .reset-confirmation {
            display: grid;
            gap: 12px;
            margin-top: 4px;
            padding: 14px;
            border: 1px solid #fecaca;
            border-radius: 8px;
            background: #fff1f2;
        }

        .reset-confirmation p {
            margin: 0;
            color: #7f1d1d;
            font-weight: 800;
            line-height: 1.5;
        }

        .reset-confirmation-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .reset-confirm-button,
        .reset-cancel-button {
            min-height: 34px;
            padding: 7px 10px;
            border-radius: 7px;
            cursor: pointer;
            font-weight: 800;
        }

        .reset-confirm-button {
            border: 1px solid #b91c1c;
            background: #b91c1c;
            color: #ffffff;
        }

        .reset-cancel-button {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
        }

        .reset-link {
            font-weight: 700;
        }

        .status-message {
            padding: 10px 12px;
            border: 1px solid #86efac;
            border-radius: 8px;
            background: #f0fdf4;
            color: #166534;
            font-weight: 700;
        }

        .error {
            color: #b91c1c;
            font-weight: 700;
        }
</style>
@endpush

@section('content')
<main>
    <div class="reset-card">
        <p class="reset-link"><a href="{{ route('guide') }}">← Retour à l’aide</a></p>

        <h1>Réinitialiser cette démo</h1>

        @if (session('status'))
            <div class="status-message">{{ session('status') }}</div>
        @endif

        <p>Cette action remet les données de démonstration dans leur état initial.</p>

        <div class="reset-warning">
            Cette action est réservée à la démonstration et nécessite le mot de passe défini dans l’environnement.
        </div>

        <div>
            <p>Le reset recrée notamment :</p>
            <ul>
                <li>les clubs et utilisateurs de test ;</li>
                <li>les licenciés et participants de démonstration ;</li>
                <li>les compétitions de test ;</li>
                <li>les invitations, poules, combats, scores et classements prévus par le seed.</li>
            </ul>
        </div>

        <p>
            Pour savoir quelle compétition utiliser après reset, consultez aussi
            <a href="{{ route('guide.jeu-test-demo') }}">le jeu de test démo</a>.
        </p>

        <form
            class="reset-form"
            method="POST"
            action="{{ route('demo.reset.run') }}"
            data-reset-form
        >
            @csrf

            <div>
                <label for="password">Mot de passe de réinitialisation</label>
                <input id="password" name="password" type="password" required autocomplete="current-password">
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="reset-actions">
                <button class="reset-button" type="button" data-reset-open-confirmation>Remettre à zéro la démo</button>
                <a class="reset-link" href="{{ route('guide') }}">Annuler</a>
            </div>

            <div class="reset-confirmation" data-reset-confirmation hidden>
                <p>Vous allez effacer toutes les données et revenir au jeu de démonstration initial. Voulez-vous continuer ?</p>
                <div class="reset-confirmation-actions">
                    <button class="reset-confirm-button" type="submit">Oui, réinitialiser</button>
                    <button class="reset-cancel-button" type="button" data-reset-cancel-confirmation>Non, annuler</button>
                </div>
            </div>
        </form>
    </div>
</main>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('[data-reset-form]');
        const openButton = document.querySelector('[data-reset-open-confirmation]');
        const confirmation = document.querySelector('[data-reset-confirmation]');
        const cancelButton = document.querySelector('[data-reset-cancel-confirmation]');
        const passwordInput = document.querySelector('#password');

        openButton?.addEventListener('click', () => {
            if (passwordInput && ! passwordInput.reportValidity()) {
                return;
            }

            confirmation.hidden = false;
            confirmation.querySelector('button[type="submit"]')?.focus();
        });

        cancelButton?.addEventListener('click', () => {
            confirmation.hidden = true;
            openButton?.focus();
        });

        form?.addEventListener('submit', () => {
            confirmation.hidden = true;
        });
    });
</script>
@endpush
