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
            background: #eef2f6;
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
            display: flex;
            flex-direction: column;
            width: 252px;
            height: 100vh;
            flex: 0 0 252px;
            padding: 18px 16px;
            border-right: 1px solid rgba(148, 163, 184, 0.18);
            background: #0b1733;
            box-shadow: 10px 0 28px rgba(15, 23, 42, 0.16);
        }

        .app-brand {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin-bottom: 22px;
            padding: 8px 4px 14px;
            text-decoration: none;
        }

        .app-brand img {
            display: block;
            width: auto;
            max-width: 100%;
            height: 98px;
            object-fit: contain;
        }

        .app-nav {
            display: grid;
            gap: 8px;
        }

        .app-nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 42px;
            padding: 10px 12px;
            border: 1px solid transparent;
            border-radius: 12px;
            color: #d8e3f2;
            font-weight: 700;
            text-decoration: none;
            transition: background 140ms ease, border-color 140ms ease, color 140ms ease;
        }

        .app-nav-link:hover {
            border-color: rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }

        .app-nav-link.is-active {
            border-color: rgba(96, 165, 250, 0.42);
            background: #1d4ed8;
            color: #ffffff;
            box-shadow: 0 10px 22px rgba(29, 78, 216, 0.28);
        }

        .app-nav-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            flex: 0 0 20px;
            color: currentColor;
        }

        .app-nav-icon svg {
            display: block;
            width: 20px;
            height: 20px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .nav-label-mobile {
            display: none;
        }

        .app-sidebar-footer {
            display: grid;
            gap: 12px;
            margin-top: auto;
            padding-top: 18px;
        }

        .app-sidebar-club {
            display: grid;
            gap: 6px;
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.07);
            color: #e5edf8;
        }

        .app-sidebar-club-label {
            color: #93a4bb;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .app-sidebar-club-name {
            font-size: 14px;
            font-weight: 800;
            line-height: 1.25;
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
            min-height: 58px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 10px 24px;
            border-bottom: 1px solid #e5eaf0;
            background: #ffffff;
            box-shadow: 0 1px 0 rgba(15, 23, 42, 0.04);
        }

        .app-header-title {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            color: #111827;
            font-size: 15px;
            font-weight: 800;
        }

        .app-header-title-text {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .app-header-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            flex: 0 0 20px;
            color: #475569;
        }

        .app-header-icon svg {
            display: block;
            width: 20px;
            height: 20px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .app-header-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
        }

        .app-header-breadcrumb-parent {
            color: #64748b;
            font-weight: 700;
        }

        .app-header-breadcrumb-separator {
            color: #94a3b8;
        }

        .app-header-user {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #475569;
            font-size: 13px;
        }

        .app-header-badges {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: wrap;
        }

        .app-header-badge {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            padding: 4px 9px;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            background: #f8fafc;
            color: #475569;
            font-size: 12px;
            font-weight: 800;
            line-height: 1.2;
            white-space: nowrap;
        }

        .app-header-badge.organizer {
            border-color: #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .app-header-badge.invited {
            border-color: #c7d2fe;
            background: #eef2ff;
            color: #4338ca;
        }

        .app-header-badge.open {
            border-color: #86efac;
            background: #f0fdf4;
            color: #166534;
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
                padding: 6px 8px;
                border-right: 0;
                border-bottom: 1px solid #dce1e7;
            }

            .app-brand {
                width: 100%;
                margin-bottom: 4px;
                padding: 0;
            }

            .app-brand img {
                max-height: 54px;
                height: 54px;
                max-width: 180px;
            }

            .app-nav {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 5px;
            }

            .app-nav-link {
                justify-content: center;
                gap: 5px;
                min-height: 34px;
                padding: 6px 5px;
                border-radius: 9px;
                font-size: 12px;
                line-height: 1.1;
            }

            .app-nav-icon {
                width: 16px;
                height: 16px;
                flex-basis: 16px;
            }

            .app-nav-icon svg {
                width: 16px;
                height: 16px;
            }

            .app-sidebar-footer {
                display: flex;
                align-items: center;
                justify-content: center;
                flex-wrap: wrap;
                gap: 3px 10px;
                margin-top: 4px;
                padding-top: 4px;
                border-top: 1px solid rgba(255, 255, 255, 0.08);
            }

            .app-sidebar-footer .app-nav-link {
                width: auto;
                min-height: 0;
                padding: 0;
                border: 0;
                border-radius: 0;
                background: transparent;
                color: #a8b5c8;
                font-size: 10.5px;
                font-weight: 650;
                box-shadow: none;
            }

            .app-sidebar-footer .app-nav-link .app-nav-icon {
                display: none;
            }

            .app-sidebar-footer .app-nav-link.is-active {
                background: transparent;
                color: #ffffff;
                box-shadow: none;
            }

            .app-sidebar-club {
                order: -1;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 4px;
                width: 100%;
                min-height: 0;
                padding: 0;
                border: 0;
                border-radius: 0;
                background: transparent;
                text-align: center;
            }

            .app-sidebar-club-label {
                color: #8393aa;
                font-size: 10px;
                font-weight: 650;
                letter-spacing: 0;
                text-transform: none;
            }

            .app-sidebar-club-label::after {
                content: " :";
            }

            .app-sidebar-club-name {
                min-width: 0;
                overflow: hidden;
                color: #cbd5e1;
                font-size: 10.5px;
                font-weight: 700;
                text-overflow: ellipsis;
                white-space: nowrap;
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

            .app-header-title-text {
                white-space: normal;
            }

            .nav-label-desktop {
                display: none;
            }

            .nav-label-mobile {
                display: inline;
            }
        }
    </style>
    @stack('styles')
    <style>
        .app-page {
            --ui-navy: #0b1733;
            --ui-text: #17202a;
            --ui-muted: #64748b;
            --ui-border: #dbe3ef;
            --ui-soft: #f8fafc;
            --ui-blue: #1d4ed8;
            --ui-blue-soft: #eff6ff;
            --ui-green: #15803d;
            --ui-green-soft: #f0fdf4;
            --ui-orange: #b45309;
            --ui-orange-soft: #fff7ed;
            --ui-red: #b91c1c;
            --ui-red-soft: #fef2f2;
        }

        .app-page main {
            margin-top: 28px;
            margin-bottom: 32px;
        }

        .app-page h1 {
            margin: 0 0 10px;
            color: var(--ui-text);
            font-size: 28px;
            font-weight: 850;
            line-height: 1.16;
            letter-spacing: 0;
        }

        .app-page h2 {
            margin: 0 0 10px;
            color: var(--ui-text);
            font-size: 18px;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: 0;
        }

        .app-page h3 {
            margin: 0 0 8px;
            color: var(--ui-text);
            font-size: 16px;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: 0;
        }

        .app-page :is(p, li) {
            color: #334155;
            font-size: 14px;
            line-height: 1.5;
        }

        .app-page :is(label, .form-label) {
            color: #475569;
            font-size: 12px;
            font-weight: 750;
            line-height: 1.25;
        }

        .app-page section {
            margin-top: 16px;
            padding: 16px;
            border-radius: 10px;
        }

        .app-page :is(.subsection, .secondary-section, .poule-card, .participant-card, .combat-row) {
            margin-top: 16px;
        }

        .app-page .btn.btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            width: fit-content;
            min-height: 34px;
            padding: 7px 12px;
            border: 1px solid transparent;
            border-radius: 8px;
            background: #ffffff;
            color: var(--ui-text);
            font: inherit;
            font-size: 13px;
            font-weight: 750;
            line-height: 1.2;
            text-align: center;
            text-decoration: none;
            vertical-align: middle;
            white-space: nowrap;
            cursor: pointer;
            box-shadow: none;
            transition: background-color 140ms ease, border-color 140ms ease, color 140ms ease, box-shadow 140ms ease, transform 140ms ease;
        }

        .app-page .btn.btn:hover {
            text-decoration: none;
        }

        .app-page .btn.btn:focus-visible {
            outline: 2px solid #93c5fd;
            outline-offset: 2px;
        }

        .app-page .btn.btn:disabled,
        .app-page .btn.btn[disabled],
        .app-page .btn.btn[aria-disabled="true"] {
            opacity: 0.55;
            cursor: not-allowed;
            pointer-events: none;
            transform: none;
        }

        .app-page .btn.btn-primary {
            border-color: var(--ui-navy);
            background: var(--ui-navy);
            color: #ffffff;
        }

        .app-page .btn.btn-primary:hover {
            border-color: #111f42;
            background: #111f42;
            color: #ffffff;
        }

        .app-page .btn.btn-secondary {
            border-color: #cbd5e1;
            background: #ffffff;
            color: var(--ui-navy);
        }

        .app-page .btn.btn-secondary:hover {
            border-color: #94a3b8;
            background: var(--ui-soft);
            color: var(--ui-navy);
        }

        .app-page .btn.btn-ghost {
            border-color: transparent;
            background: transparent;
            color: #475569;
        }

        .app-page .btn.btn-ghost:hover {
            background: var(--ui-soft);
            color: var(--ui-text);
        }

        .app-page .btn.btn-success {
            border-color: #bbf7d0;
            background: #ffffff;
            color: var(--ui-green);
        }

        .app-page .btn.btn-success:hover {
            border-color: #86efac;
            background: var(--ui-green-soft);
            color: #166534;
        }

        .app-page .btn.btn-danger {
            border-color: #fecaca;
            background: #ffffff;
            color: var(--ui-red);
        }

        .app-page .btn.btn-danger:hover {
            border-color: #fca5a5;
            background: var(--ui-red-soft);
            color: #991b1b;
        }

        .app-page .btn.btn-state {
            border-color: #dbe3ef;
            background: #ffffff;
            color: #334155;
        }

        .app-page .btn.btn-state:hover {
            border-color: #bfdbfe;
            background: var(--ui-blue-soft);
            color: #1e3a8a;
        }

        .app-page .btn.btn-export {
            border-color: #cbd5e1;
            background: #ffffff;
            color: #334155;
        }

        .app-page .btn.btn-export:hover {
            border-color: #94a3b8;
            background: #f8fafc;
            color: var(--ui-navy);
        }

        .app-page .btn.btn-sm {
            min-height: 30px;
            padding: 5px 10px;
            border-radius: 7px;
            font-size: 12px;
        }

        .app-page .btn.btn-xs {
            min-height: 26px;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 800;
        }

        .app-page .btn.btn-icon {
            width: 34px;
            min-width: 34px;
            height: 34px;
            min-height: 34px;
            padding: 0;
            border-color: #cbd5e1;
            background: #ffffff;
            color: #334155;
        }

        .app-page .btn.btn-icon:hover {
            border-color: #94a3b8;
            background: var(--ui-soft);
        }

        .app-page .btn.btn-icon.btn-sm {
            width: 30px;
            min-width: 30px;
            height: 30px;
            min-height: 30px;
            padding: 0;
        }

        .app-page .btn.btn-icon.btn-xs {
            width: 26px;
            min-width: 26px;
            height: 26px;
            min-height: 26px;
            padding: 0;
        }

        .app-page .btn.btn-icon-danger {
            border-color: #fecaca;
            color: var(--ui-red);
        }

        .app-page .btn.btn-icon-danger:hover {
            border-color: #fca5a5;
            background: var(--ui-red-soft);
            color: #991b1b;
        }

        .app-page .btn.btn-icon-success {
            border-color: #bbf7d0;
            color: var(--ui-green);
        }

        .app-page .btn.btn-icon-success:hover {
            border-color: #86efac;
            background: var(--ui-green-soft);
            color: #166534;
        }

        .app-page :is(button, .primary-action, .secondary-action, .poule-action-button, .print-combats-button) {
            min-height: 34px;
            padding: 7px 11px;
            border-width: 1px;
            border-style: solid;
            border-radius: 8px;
            font: inherit;
            font-size: 13px;
            font-weight: 750;
            line-height: 1.2;
            text-decoration: none;
            cursor: pointer;
            box-shadow: none;
            transition: background-color 140ms ease, border-color 140ms ease, color 140ms ease, box-shadow 140ms ease;
        }

        .app-page :is(.primary-action, .primary-button) {
            border-color: var(--ui-blue);
            background: var(--ui-blue);
            color: #ffffff;
        }

        .app-page :is(.primary-action, .primary-button):hover {
            background: #1e40af;
            border-color: #1e40af;
        }

        .app-page :is(.secondary-action, .secondary-button) {
            border-color: #cbd5e1;
            background: #ffffff;
            color: #334155;
        }

        .app-page :is(.secondary-action, .secondary-button):hover {
            border-color: #94a3b8;
            background: var(--ui-soft);
        }

        .app-page :is(.danger-button, .withdraw-button, .decline-button, .combat-clear-button) {
            border-color: #fecaca;
            background: #ffffff;
            color: var(--ui-red);
        }

        .app-page :is(.danger-button, .withdraw-button, .decline-button, .combat-clear-button):hover {
            background: var(--ui-red-soft);
            border-color: #fca5a5;
        }

        .app-page :is(.text-link, .link-action) {
            color: var(--ui-blue);
            font-weight: 750;
            text-decoration: none;
        }

        .app-page :is(.text-link, .link-action):hover {
            text-decoration: underline;
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
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 750;
            line-height: 1.2;
            white-space: nowrap;
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

        .app-page :is(.home-kpi, .licencie-kpi, .summary-item, .dashboard-card, .home-card, .licencie-card, .participant-section-card, .club-section-card, .club-kpi-card) {
            border: 1px solid var(--ui-border);
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.035);
        }

        .app-page :is(.home-kpi, .licencie-kpi, .summary-item) {
            padding: 14px;
        }

        .app-page :is(.home-kpi, .licencie-kpi, .summary-item) strong {
            color: var(--ui-text);
            font-size: 22px;
            font-weight: 850;
            line-height: 1.1;
        }

        .app-page :is(.home-kpi, .licencie-kpi, .summary-item) span:last-child {
            color: var(--ui-muted);
            font-size: 12px;
            font-weight: 750;
            line-height: 1.3;
        }

        .app-page :is(.home-kpi-icon, .licencie-kpi-icon) {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: var(--ui-blue-soft);
            color: var(--ui-blue);
            font-size: 14px;
            font-weight: 850;
        }

        .app-page table {
            width: 100%;
            border-collapse: collapse;
        }

        .app-page th {
            background: var(--ui-soft);
            color: #475569;
            font-size: 12px;
            font-weight: 750;
            line-height: 1.25;
            text-align: left;
        }

        .app-page td {
            color: #334155;
            font-size: 13px;
            line-height: 1.35;
        }

        .app-page th,
        .app-page td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5eaf0;
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <aside class="app-sidebar">
            @php
                $club = $layoutCurrentUser?->club;
                $routeCompetition = request()->route('competition');
                $headerCompetition = $routeCompetition instanceof \App\Models\Competition ? $routeCompetition : null;
                $headerCompetitionRole = null;

                if ($headerCompetition && $club) {
                    $roleLabel = $headerCompetition->roleLabelForClub($club);
                    $headerCompetitionRole = $roleLabel === 'Organisateur'
                        ? 'Organisateur'
                        : ($roleLabel !== 'Non concerné' ? 'Invité' : null);
                }

                $headerIcon = request()->routeIs('home')
                    ? 'home'
                    : (request()->routeIs('licencies.*')
                        ? 'users'
                        : (request()->routeIs('guide')
                            ? 'help'
                            : 'trophy'));
                $navItems = [
                    [
                        'label' => 'Accueil',
                        'route' => route('home'),
                        'active' => request()->routeIs('home'),
                        'icon' => 'home',
                    ],
                    [
                        'label' => 'Compétitions',
                        'route' => route('competitions.index'),
                        'active' => request()->routeIs('competitions.*'),
                        'icon' => 'trophy',
                    ],
                    [
                        'label' => 'Mes licenciés',
                        'route' => route('licencies.index'),
                        'active' => request()->routeIs('licencies.*'),
                        'icon' => 'users',
                    ],
                ];
            @endphp

            <a class="app-brand" href="{{ route('competitions.index') }}">
                <img src="{{ asset('images/interclub_ffk.png') }}" alt="Interclub FFK">
            </a>

            <nav class="app-nav" aria-label="Navigation principale">
                @foreach ($navItems as $item)
                    <a @class(['app-nav-link', 'is-active' => $item['active']]) href="{{ $item['route'] }}">
                        <span class="app-nav-icon" aria-hidden="true">
                            @if ($item['icon'] === 'home')
                                <svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5"></path><path d="M5 10.5V20h5v-5h4v5h5v-9.5"></path></svg>
                            @elseif ($item['icon'] === 'trophy')
                                <svg viewBox="0 0 24 24"><path d="M8 4h8v4a4 4 0 0 1-8 0V4Z"></path><path d="M8 6H4v2a4 4 0 0 0 4 4"></path><path d="M16 6h4v2a4 4 0 0 1-4 4"></path><path d="M12 12v5"></path><path d="M9 20h6"></path><path d="M10 17h4"></path></svg>
                            @else
                                <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            @endif
                        </span>
                        @if ($item['label'] === 'Mes licenciés')
                            <span class="nav-label-desktop">{{ $item['label'] }}</span>
                            <span class="nav-label-mobile">Licenciés</span>
                        @else
                            <span>{{ $item['label'] }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <div class="app-sidebar-footer">
                <a @class(['app-nav-link', 'is-active' => request()->routeIs('guide')]) href="{{ route('guide') }}">
                    <span class="app-nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M9.5 9a2.8 2.8 0 0 1 5.1 1.6c0 1.8-2.6 2.4-2.6 4"></path><path d="M12 18h.01"></path></svg>
                    </span>
                    <span>Aide</span>
                </a>

                <a @class(['app-nav-link', 'is-active' => request()->routeIs('switch-user')]) href="{{ route('switch-user') }}">
                    <span class="app-nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 0 1-15.3 6.4"></path><path d="M3 12A9 9 0 0 1 18.3 5.6"></path><path d="M6 18.5H2.5V22"></path><path d="M18 5.5h3.5V2"></path></svg>
                    </span>
                    <span>Changer d’utilisateur</span>
                </a>

                @if ($club)
                    <div class="app-sidebar-club">
                        <span class="app-sidebar-club-label">Club courant</span>
                        <span class="app-sidebar-club-name">{{ $club->name }}</span>
                    </div>
                @endif
            </div>
        </aside>

        <div class="app-main">
            <header class="app-header">
                <div class="app-header-title">
                    <span class="app-header-icon" aria-hidden="true">
                        @if ($headerIcon === 'home')
                            <svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5"></path><path d="M5 10.5V20h5v-5h4v5h5v-9.5"></path></svg>
                        @elseif ($headerIcon === 'users')
                            <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        @elseif ($headerIcon === 'help')
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M9.5 9a2.8 2.8 0 0 1 5.1 1.6c0 1.8-2.6 2.4-2.6 4"></path><path d="M12 18h.01"></path></svg>
                        @else
                            <svg viewBox="0 0 24 24"><path d="M8 4h8v4a4 4 0 0 1-8 0V4Z"></path><path d="M8 6H4v2a4 4 0 0 0 4 4"></path><path d="M16 6h4v2a4 4 0 0 1-4 4"></path><path d="M12 12v5"></path><path d="M9 20h6"></path><path d="M10 17h4"></path></svg>
                        @endif
                    </span>

                    @if ($headerCompetition)
                        <span class="app-header-breadcrumb">
                            <span class="app-header-breadcrumb-parent">Compétitions</span>
                            <span class="app-header-breadcrumb-separator">&gt;</span>
                            <span class="app-header-title-text">{{ $headerCompetition->name }}</span>
                        </span>
                    @else
                        <span class="app-header-title-text">@yield('page-title', 'FFK Compétitions interclubs')</span>
                    @endif
                </div>
                <div class="app-header-badges">
                    @if ($headerCompetition && $headerCompetitionRole)
                        <span @class([
                            'app-header-badge',
                            'organizer' => $headerCompetitionRole === 'Organisateur',
                            'invited' => $headerCompetitionRole === 'Invité',
                        ])>{{ $headerCompetitionRole }}</span>

                        <span @class(['app-header-badge', 'open' => ! $headerCompetition->inscriptions_closed])>
                            {{ $headerCompetition->inscriptions_closed ? 'Inscriptions fermées' : 'Inscriptions ouvertes' }}
                        </span>
                    @endif
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
