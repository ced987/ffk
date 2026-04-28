@extends('layouts.app')

@section('title', 'Changer d\'utilisateur - FFK Interclubs')
@section('page-title', 'Changer d\'utilisateur')

@push('styles')
<style>
        body {
            margin: 0;
            background: #f6f7f9;
            color: #17202a;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        main {
            width: min(760px, calc(100% - 32px));
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

        .back-link {
            display: inline-block;
            margin-bottom: 24px;
            color: #1d4ed8;
            font-weight: 700;
            text-decoration: none;
        }

        .user-list {
            display: grid;
            gap: 10px;
            margin-top: 24px;
        }

        .user-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 16px;
            border: 1px solid #cfd6df;
            border-radius: 8px;
            background: #ffffff;
        }

        .user-card:hover {
            border-color: #1d4ed8;
        }

        .user-card[aria-current="true"] {
            border-color: #166534;
            background: #f0fdf4;
        }

        .user-link {
            color: #17202a;
            text-decoration: none;
        }

        .user-name {
            font-weight: 700;
        }

        .user-club,
        .current-label {
            color: #5f6b7a;
        }

        .current-label {
            font-size: 14px;
            font-weight: 700;
        }

        .user-main {
            display: flex;
            align-items: flex-start;
            flex: 1;
            gap: 4px;
            flex-direction: column;
            min-width: 0;
        }

        .club-display {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .club-edit-form {
            display: none;
            align-items: center;
            gap: 6px;
            margin-top: 4px;
        }

        .club-edit-form.is-open {
            display: flex;
        }

        .club-edit-form input {
            width: 180px;
            max-width: 100%;
            border: 1px solid #cfd6df;
            border-radius: 6px;
            padding: 6px 8px;
        }

        .icon-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 30px;
            min-height: 30px;
            border: 1px solid #cfd6df;
            border-radius: 6px;
            background: #ffffff;
            color: #17202a;
            cursor: pointer;
        }

        .icon-button:hover {
            border-color: #1d4ed8;
        }

        .icon-button.cancel:hover {
            border-color: #b91c1c;
            color: #b91c1c;
        }

        @media (max-width: 640px) {
            .user-card {
                align-items: flex-start;
                flex-direction: column;
            }

            .club-edit-form {
                flex-wrap: wrap;
            }
        }
</style>
@endpush

@section('content')
<main>
        <a class="back-link" href="{{ route('home') }}">Retour à l'accueil</a>

        <h1>Changer d'utilisateur</h1>
        <p>Choisissez l'utilisateur de démonstration à utiliser.</p>

        <div class="user-list">
            @foreach ($users as $user)
                <div class="user-card" aria-current="{{ $currentUser?->is($user) ? 'true' : 'false' }}">
                    <div class="user-main">
                        <a class="user-link" href="{{ route('demo.users.select', $user) }}">
                            <span class="user-name">{{ $user->name }}</span>
                        </a>

                        @if ($user->club)
                            <span class="user-club club-display" data-club-display="{{ $user->club->id }}">
                                <span>{{ $user->club->name }}</span>
                                <button class="icon-button" type="button" title="Modifier le nom du club" data-club-edit="{{ $user->club->id }}">✏️</button>
                            </span>

                            <form class="club-edit-form" method="POST" action="{{ route('demo.clubs.update', $user->club) }}" data-club-form="{{ $user->club->id }}">
                                @csrf
                                @method('PATCH')
                                <input type="text" name="name" value="{{ old('name', $user->club->name) }}" required maxlength="255" aria-label="Nom du club">
                                <button class="icon-button" type="submit" title="Enregistrer">✔</button>
                                <button class="icon-button cancel" type="button" title="Annuler" data-club-cancel="{{ $user->club->id }}">✖</button>
                            </form>
                        @else
                            <span class="user-club">Aucun club</span>
                        @endif
                    </div>

                    @if ($currentUser?->is($user))
                        <span class="current-label">Actuel</span>
                    @endif
                </div>
            @endforeach
        </div>
    </main>
@endsection

@push('scripts')
<script>
    document.addEventListener('click', (event) => {
        const editButton = event.target.closest('[data-club-edit]');
        const cancelButton = event.target.closest('[data-club-cancel]');

        if (! editButton && ! cancelButton) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const clubId = editButton
            ? editButton.getAttribute('data-club-edit')
            : cancelButton.getAttribute('data-club-cancel');
        const display = document.querySelector(`[data-club-display="${clubId}"]`);
        const form = document.querySelector(`[data-club-form="${clubId}"]`);

        if (! display || ! form) {
            return;
        }

        if (editButton) {
            document.querySelectorAll('[data-club-display]').forEach((element) => {
                element.style.display = '';
            });
            document.querySelectorAll('[data-club-form]').forEach((element) => {
                element.classList.remove('is-open');
            });

            display.style.display = 'none';
            form.classList.add('is-open');
            form.querySelector('input')?.focus();
        }

        if (cancelButton) {
            display.style.display = '';
            form.classList.remove('is-open');
        }
    });
</script>
@endpush
