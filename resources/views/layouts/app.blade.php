<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'FFK Interclubs')</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #f6f7f9;
            color: #17202a;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .app-layout {
            min-height: 100vh;
            display: flex;
        }

        .app-sidebar {
            position: sticky;
            top: 0;
            width: 200px;
            height: 100vh;
            flex: 0 0 200px;
            padding: 18px 14px;
            border-right: 1px solid #dce1e7;
            background: #ffffff;
        }

        .app-brand {
            display: inline-flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 16px;
            text-decoration: none;
        }

        .app-brand img {
            display: block;
            width: auto;
            height: 120px;
        }

        .app-nav {
            display: grid;
            gap: 8px;
        }

        .app-nav a {
            display: block;
            padding: 9px 10px;
            border-radius: 8px;
            color: #334155;
            font-weight: 700;
            text-decoration: none;
        }

        .app-nav a:hover {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .app-main {
            min-width: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .app-header {
            position: sticky;
            top: 0;
            z-index: 3000;
            min-height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 12px 24px;
            border-bottom: 1px solid #e5eaf0;
            background: #ffffff;
        }

        .app-header-title {
            font-weight: 800;
        }

        .app-header-link {
            color: #475569;
            font-weight: 700;
            text-decoration: none;
        }

        .app-header-link:hover {
            color: #1d4ed8;
        }

        .app-header-user {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #475569;
            font-size: 14px;
        }

        .app-page {
            min-width: 0;
            flex: 1;
        }

        @media (max-width: 760px) {
            .app-layout {
                display: block;
            }

            .app-sidebar {
                position: static;
                width: auto;
                height: auto;
                border-right: 0;
                border-bottom: 1px solid #dce1e7;
            }

            .app-nav {
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            }

            .app-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .app-header-user {
                align-items: flex-start;
                flex-direction: column;
                gap: 4px;
            }
        }
    </style>
    @stack('styles')
    <style>
        .app-page main {
            margin-top: 32px;
            margin-bottom: 32px;
        }

        .app-page h1 {
            margin: 0 0 12px;
            font-size: 26px;
            line-height: 1.2;
        }

        .app-page h2 {
            margin: 0 0 12px;
            font-size: 18px;
            line-height: 1.25;
        }

        .app-page h3 {
            margin: 0 0 10px;
            font-size: 16px;
            line-height: 1.25;
        }

        .app-page section {
            margin-top: 16px;
            padding: 12px;
        }

        .app-page :is(.subsection, .secondary-section, .poule-card, .participant-card, .combat-row) {
            margin-top: 16px;
        }

        .app-page :is(button, .primary-action, .secondary-action, .poule-action-button, .print-combats-button) {
            min-height: 36px;
            padding: 8px 12px;
            border-width: 1px;
            border-style: solid;
            border-radius: 8px;
            font: inherit;
            font-weight: 700;
            line-height: 1.2;
            text-decoration: none;
            cursor: pointer;
        }

        .app-page :is(.inline-form, .combat-actions, .poule-actions, .competition-meta, .actions-cell) {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .app-page :is(.badge, .role-badge, .state-badge, .inscriptions-badge, .status-badge, .poule-status-badge) {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            padding: 3px 7px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
        }

        .app-page :is(.visual-remove-button, .combat-choice-button, .combat-fighter-button, .combat-actions button) {
            min-height: 32px;
            padding: 6px 8px;
        }

        .app-page :is(.form-row, .response-actions, .quick-actions) {
            display: flex;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 8px;
        }

        .app-page :is(.competition-list, .dashboard-grid, .poule-list, .participant-list, .combat-list) {
            gap: 12px;
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <aside class="app-sidebar">
            <a class="app-brand" href="{{ route('competitions.index') }}">
                <img src="{{ asset('images/interclub-logo.png') }}" alt="Interclub">
            </a>

            <nav class="app-nav" aria-label="Navigation principale">
                <a href="{{ route('home') }}">Accueil</a>
                <a href="{{ route('competitions.index') }}">Compétitions</a>
                <a href="{{ route('licencies.index') }}">Mes licenciés</a>
            </nav>
        </aside>

        <div class="app-main">
            <header class="app-header">
                <div class="app-header-title">@yield('page-title', 'FFK Compétitions interclubs')</div>
                <div class="app-header-user">
                    @if ($layoutCurrentUser)
                        <span>{{ $layoutCurrentUser->name }} - {{ $layoutCurrentUser->club?->name ?? 'Aucun club' }}</span>
                    @endif
                    <a class="app-header-link" href="{{ route('switch-user') }}">🔄 Changer d’utilisateur</a>
                    <a class="app-header-link" href="{{ route('guide') }}">❓ Aide</a>
                </div>
            </header>

            <div class="app-page">
                @yield('content')
            </div>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
