@extends('layouts.app')

@section('title', $competition->name.' - FFK Interclubs')
@section('page-title', $competition->name)

@push('styles')
<style>
        body {
            margin: 0;
            background: #f6f7f9;
            color: #17202a;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            scroll-behavior: smooth;
        }

        main {
            width: 100%;
            margin: 0 auto 48px;
        }

        .app-page main.competition-page {
            margin-top: 0;
        }

        .competition-content {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
        }

        .competition-overview {
            display: grid;
            gap: 6px;
            margin-bottom: 18px;
            padding: 0 0 16px;
            border-bottom: 1px solid #e5eaf0;
            background: transparent;
        }

        .competition-overview-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .competition-overview-title {
            display: grid;
            gap: 4px;
            min-width: 0;
        }

        .competition-overview-title .competition-name-display {
            align-items: center;
        }

        .competition-overview-title h1 {
            margin: 0;
            font-size: 24px;
            line-height: 1.15;
        }

        .competition-overview-meta {
            color: #64748b;
            font-size: 14px;
            font-weight: 600;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 28px;
            line-height: 1.2;
        }

        .competition-title-row {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 8px;
        }

        .app-page .competition-title-row h1 {
            margin: 0;
            line-height: 1.15;
        }

        .competition-name-display {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            line-height: 1;
        }

        .competition-name-form {
            display: none;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .competition-name-form.is-open {
            display: flex;
        }

        .competition-name-form input {
            min-width: min(420px, 100%);
            border: 1px solid #cfd6df;
            border-radius: 6px;
            padding: 8px 10px;
            font: inherit;
        }

        .app-page .competition-page .title-icon-button,
        .app-page .title-icon-button,
        .app-page .competition-title-row .title-icon-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            align-self: center;
            width: 30px;
            height: 30px;
            min-height: 30px;
            padding: 0;
            border: 1px solid #cfd6df;
            border-radius: 6px;
            background: #ffffff;
            color: #17202a;
            cursor: pointer;
            font-size: 13px;
            line-height: 1;
            text-decoration: none;
        }

        .title-icon-button:hover {
            border-color: #1d4ed8;
        }

        .title-icon-button.cancel:hover {
            border-color: #b91c1c;
            color: #b91c1c;
        }

        .app-page .competition-page .secondary-button {
            border-color: #cbd5e1;
            background: #ffffff;
            color: #334155;
        }

        .app-page .competition-page .secondary-button:hover {
            border-color: #94a3b8;
            background: #f8fafc;
        }

        p {
            margin: 0;
            color: #5f6b7a;
        }

        a {
            color: #1d4ed8;
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
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

        section {
            margin-top: 24px;
            padding: 24px;
            background: #ffffff;
            border: 1px solid #d9e2ec;
            border-radius: 10px;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
        }

        .competition-page .tab-panel {
            margin-top: 16px;
            padding: 0;
            border: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
        }

        .tabs {
            position: sticky;
            top: 58px;
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 0;
            margin-top: 0;
            margin-bottom: 10px;
            padding: 0 18px;
            border: 0;
            border-bottom: 1px solid #e5eaf0;
            border-radius: 0;
            background: #ffffff;
            box-shadow: 0 1px 0 rgba(15, 23, 42, 0.03);
        }

        .tabs + * {
            margin-top: 12px;
        }

        .tabs-list {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0;
            min-width: 0;
        }

        .app-page .competition-page .tab-button {
            appearance: none;
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 0;
            min-height: 34px;
            padding: 0 11px;
            border: 0 !important;
            border-radius: 3px 3px 0 0;
            background: transparent;
            color: #475569;
            box-shadow: none;
            font-size: 13px;
            font-weight: 700;
        }

        .app-page .competition-page .tab-button svg {
            width: 16px;
            height: 16px;
            flex: 0 0 16px;
            color: currentColor;
            stroke: currentColor;
            stroke-width: 1.9;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
        }

        .app-page .competition-page .tab-button::after {
            content: "";
            position: absolute;
            right: 10px;
            bottom: 0;
            left: 10px;
            height: 2px;
            border-radius: 999px 999px 0 0;
            background: transparent;
        }

        .app-page .competition-page .tab-button:hover {
            background: #f8fafc;
            color: #1e3a8a;
        }

        .app-page .competition-page .tab-button.is-active {
            background: #f8fbff;
            color: #1d4ed8;
        }

        .app-page .competition-page .tab-button.is-active::after {
            background: #2563eb;
        }

        .tab-panel[hidden] {
            display: none;
        }

        .tab-hint {
            margin-bottom: 16px;
            color: #64748b;
            font-size: 14px;
        }

        .poule-guidance {
            display: grid;
            gap: 8px;
            margin-bottom: 16px;
        }

        .poule-guidance p,
        .poule-ready,
        .warning-message {
            margin: 0;
            padding: 10px 12px;
            border-radius: 8px;
        }

        .poule-guidance p {
            border: 1px solid #dbeafe;
            background: #f8fafc;
            color: #475569;
        }

        .poule-ready {
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            color: #166534;
            font-weight: 700;
        }

        .warning-message {
            width: fit-content;
            max-width: 560px;
            margin: -2px 0 12px;
            padding: 6px 10px;
            border: 1px solid #fde68a;
            background: #fffbeb;
            color: #92400e;
            font-size: 12px;
        }

        .poule-validation-alert {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 0 0 16px;
            padding: 12px 14px;
            border: 1px solid #fde68a;
            border-radius: 8px;
            background: #fffbeb;
            color: #92400e;
            font-weight: 700;
        }

        .poule-validation-alert a {
            flex: 0 0 auto;
            color: #92400e;
            font-weight: 800;
        }

        .poule-assistant {
            display: grid;
            gap: 9px;
            margin: 0 0 12px;
            padding: 12px;
            border: 1px solid #d8e1ec;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 5px 14px rgba(15, 23, 42, 0.04);
        }

        .poule-assistant-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .poule-assistant-header h3,
        .poule-proposal-card h4 {
            margin: 0;
            color: #17202a;
        }

        .poule-assistant-header h3 {
            font-size: 15px;
        }

        .poule-assistant-header .section-intro {
            margin-top: 2px;
            max-width: 680px;
            font-size: 11px;
        }

        .poule-assistant-form {
            display: grid;
            gap: 8px;
            padding: 8px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
        }

        .poule-assistant-criteria {
            display: grid;
            grid-template-columns: repeat(5, minmax(120px, 1fr));
            gap: 8px;
        }

        .poule-assistant-criteria label {
            margin-bottom: 3px;
            color: #475569;
            font-size: 10px;
            font-weight: 800;
        }

        .poule-assistant-criteria input,
        .poule-assistant-criteria select {
            max-width: none;
            min-height: 30px;
            padding: 5px 7px;
            border-color: #cbd5e1;
            background: #ffffff;
            font-size: 12px;
        }

        .poule-assistant-actions,
        .poule-proposal-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .poule-assistant-actions {
            padding-top: 0;
        }

        .app-page .competition-page .poule-assistant-actions button,
        .app-page .competition-page .poule-assistant-actions .secondary-button,
        .app-page .competition-page .poule-assistant-results-header button,
        .app-page .competition-page .poule-proposal-card button {
            min-height: 28px;
            margin-top: 0;
            padding: 4px 8px;
            font-size: 12px;
        }

        .app-page .competition-page .poule-assistant-actions .secondary-button,
        .app-page .competition-page .poule-proposal-actions .secondary-button,
        .app-page .competition-page .poule-proposal-card .secondary-button {
            border-color: #cbd5e1;
            background: #ffffff;
            color: #334155;
        }

        .app-page .competition-page .poule-assistant-actions .secondary-button:hover,
        .app-page .competition-page .poule-proposal-actions .secondary-button:hover,
        .app-page .competition-page .poule-proposal-card .secondary-button:hover {
            border-color: #94a3b8;
            background: #f8fafc;
        }

        .poule-proposals {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .poule-proposal-card {
            display: grid;
            gap: 6px;
            padding: 9px;
            border: 1px solid #dfe7f1;
            border-left: 3px solid #2563eb;
            border-radius: 8px;
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        }

        .poule-proposal-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .poule-proposal-heading {
            display: grid;
            gap: 2px;
        }

        .poule-proposal-card h4 {
            font-size: 13px;
        }

        .poule-proposal-score {
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
        }

        .poule-proposal-indicator {
            display: inline-flex;
            width: fit-content;
            padding: 3px 8px;
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }

        .poule-proposal-indicator.very-good {
            border-color: #bbf7d0;
            background: #f0fdf4;
            color: #166534;
        }

        .poule-proposal-indicator.arbitrate {
            border-color: #fde68a;
            background: #fffbeb;
            color: #92400e;
        }

        .poule-proposal-explanation {
            margin: -2px 0 0;
            color: #475569;
            font-size: 11px;
            line-height: 1.25;
        }

        .poule-proposal-warning {
            width: fit-content;
            padding: 4px 7px;
            border: 1px solid #fde68a;
            border-radius: 8px;
            background: #fffbeb;
            color: #92400e;
            font-size: 11px;
            font-weight: 700;
        }

        .poule-proposal-list,
        .poule-unassigned-list {
            display: grid;
            gap: 4px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .poule-proposal-list li,
        .poule-unassigned-list li {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 8px;
            padding: 4px 6px;
            border: 1px solid #e5eaf0;
            border-radius: 6px;
            background: #f8fafc;
            color: #334155;
            font-size: 11px;
            line-height: 1.15;
        }

        .poule-proposal-list span,
        .poule-unassigned-list span {
            color: #64748b;
            font-size: 11px;
            white-space: nowrap;
        }

        .poule-assistant-results {
            display: grid;
            gap: 8px;
            padding-top: 0;
        }

        .poule-assistant-results-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            flex-wrap: wrap;
            padding: 2px 0;
        }

        .poule-assistant-results-header strong,
        .poule-unassigned-title {
            color: #17202a;
            font-size: 14px;
            font-weight: 850;
        }

        .poule-unassigned-panel {
            display: grid;
            gap: 6px;
            padding: 8px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
        }

        @media (max-width: 1180px) {
            .poule-assistant-criteria {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }

            .poule-proposals {
                grid-template-columns: 1fr;
            }
        }

        .actions-summary {
            margin-bottom: 20px;
            padding-bottom: 18px;
            border-bottom: 1px solid #e5eaf0;
        }

        .actions-summary h2,
        .info-summary h2 {
            margin: 0 0 10px;
            color: #17202a;
            font-size: 18px;
            line-height: 1.2;
        }

        .actions-todo-list {
            display: grid;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .actions-todo-list li {
            position: relative;
            padding-left: 18px;
            color: #334155;
            font-size: 14px;
            font-weight: 650;
        }

        .actions-todo-list li::before {
            content: "";
            position: absolute;
            top: 0.62em;
            left: 0;
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: #2563eb;
        }

        .info-summary {
            margin-top: 0;
            padding: 0 0 18px;
            border-top: 0;
            border-bottom: 1px solid #e5eaf0;
        }

        .compact-info {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px 14px;
            margin: 0;
            padding: 12px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            background: #ffffff;
            color: #334155;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            font-size: 13px;
        }

        .compact-info div {
            display: inline-flex;
            align-items: baseline;
            gap: 5px;
        }

        .compact-info div + div::before {
            content: "|";
            margin-right: 14px;
            color: #cbd5e1;
        }

        .compact-info dt {
            color: #64748b;
            font-weight: 800;
        }

        .compact-info dd {
            margin: 0;
            color: #17202a;
            font-weight: 700;
        }

        .additional-info {
            position: relative;
            margin-top: 16px;
            padding: 12px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #ffffff;
            color: #334155;
            line-height: 1.5;
            white-space: normal;
        }

        .additional-info strong {
            display: block;
            margin-bottom: 6px;
            color: #475569;
            font-size: 13px;
        }

        .additional-info-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 6px;
        }

        .additional-info-header strong {
            margin-bottom: 0;
        }

        .additional-info-empty {
            color: #94a3b8;
            font-style: italic;
        }

        .additional-info-form {
            display: none;
            margin-top: 12px;
        }

        .additional-info-form.is-open {
            display: block;
        }

        textarea {
            width: 100%;
            max-width: 640px;
            min-height: 120px;
            padding: 10px 12px;
            border: 1px solid #cfd6df;
            border-radius: 8px;
            background: #ffffff;
            font: inherit;
        }

        dl {
            display: grid;
            grid-template-columns: max-content 1fr;
            gap: 10px 16px;
            margin: 0;
        }

        dt {
            color: #5f6b7a;
            font-weight: 600;
        }

        dd {
            margin: 0;
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

        .app-page .competition-page button,
        .app-page .competition-page .primary-action,
        .app-page .competition-page .poule-action-button,
        .app-page .competition-page .print-combats-button {
            min-height: 32px;
            margin-top: 12px;
            padding: 7px 11px;
            border: 1px solid #2563eb;
            border-radius: 6px;
            background: #2563eb;
            color: #ffffff;
            cursor: pointer;
            font: inherit;
            font-weight: 750;
            line-height: 1.2;
            text-decoration: none;
            box-shadow: none;
            transition: background 120ms ease, border-color 120ms ease, color 120ms ease, opacity 120ms ease;
        }

        .app-page .competition-page button:hover,
        .app-page .competition-page .primary-action:hover,
        .app-page .competition-page .poule-action-button:hover,
        .app-page .competition-page .print-combats-button:hover {
            border-color: #1d4ed8;
            background: #1d4ed8;
        }

        .error {
            margin-top: 8px;
            color: #b91c1c;
        }

        .invitation-list {
            display: grid;
            gap: 10px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .invitation-list li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 12px 14px;
            border: 1px solid #dce1e7;
            border-radius: 8px;
            background: #f8fafc;
        }

        .invitation-list li.inactive,
        .participant-table tr.inactive {
            background: #f1f5f9;
            color: #64748b;
        }

        .invitation-list li.inactive strong,
        .participant-table tr.inactive td {
            color: #64748b;
        }

        .invitation-main {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .status {
            color: #5f6b7a;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        }

        .inline-form {
            margin: 0;
        }

        .inline-form button {
            margin-top: 0;
            border-color: #166534;
            background: #166534;
        }

        .response-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }

        .response-actions form {
            margin: 0;
        }

        .response-actions button {
            margin-top: 0;
        }

        .app-page .competition-page .decline-button {
            border-color: #fecaca;
            background: #fff1f2;
            color: #b91c1c;
        }

        .participant-actions {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
        }

        .app-page .competition-page .participant-actions a,
        .app-page .competition-page .participant-actions button {
            min-height: 28px;
            margin-top: 0;
            padding: 4px 8px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background: #ffffff;
            color: #334155;
            font-size: 12px;
            font-weight: 750;
            line-height: 1.1;
            text-decoration: none;
        }

        .app-page .competition-page .participant-actions a:hover,
        .app-page .competition-page .participant-actions button:hover {
            border-color: #94a3b8;
            background: #f8fafc;
        }

        .participant-name-line {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .state-badges {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .state-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border: 1px solid #cfd6df;
            border-radius: 999px;
            background: #f8fafc;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.4;
        }

        .state-badge.validated {
            border-color: #bbf7d0;
            background: #f0fdf4;
            color: #166534;
        }

        .state-badge.pending {
            border-color: #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .state-badge.withdrawn {
            border-color: #e2e8f0;
            background: #f1f5f9;
            color: #64748b;
        }

        .state-badge.poule {
            border-color: #ddd6fe;
            background: #f5f3ff;
            color: #5b21b6;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            margin: 8px 0 6px;
            padding: 4px 10px;
            border: 1px solid #cfd6df;
            border-radius: 999px;
            background: #f8fafc;
            color: #475569;
            font-size: 13px;
            font-weight: 700;
        }

        .role-badge.organizer {
            border-color: #93c5fd;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .role-badge.participant {
            border-color: #86efac;
            background: #f0fdf4;
            color: #166534;
        }

        .inscriptions-badge {
            display: inline-flex;
            width: fit-content;
            margin: 8px 0 6px;
            padding: 4px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            background: #f1f5f9;
            color: #475569;
            font-size: 13px;
            font-weight: 700;
        }

        .inscriptions-badge.open {
            border-color: #86efac;
            background: #f0fdf4;
            color: #166534;
        }

        .app-page .competition-page .withdraw-button {
            margin-top: 0;
            border-color: #fecaca;
            background: #ffffff;
            color: #b91c1c;
        }

        .app-page .competition-page .withdraw-button:hover {
            background: #fff1f2;
            border-color: #fca5a5;
        }

        .app-page .competition-page .reactivate-button {
            margin-top: 0;
            border-color: #bbf7d0;
            background: #f0fdf4;
            color: #166534;
        }

        .participant-section-card {
            margin-top: 18px;
            padding: 0;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.035);
        }

        .participant-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .participant-section-header h3 {
            margin: 0;
            font-size: 16px;
        }

        .participant-section-count {
            display: inline-flex;
            align-items: center;
            padding: 3px 9px;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            background: #ffffff;
            color: #334155;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        .participant-section-body {
            padding: 12px 14px 14px;
        }

        .participant-note {
            margin-top: 14px;
            color: #64748b;
            font-size: 13px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin: 16px 0;
        }

        .summary-item {
            padding: 12px 14px;
            border: 1px solid #dce1e7;
            border-radius: 8px;
            background: #f8fafc;
        }

        .summary-item strong {
            display: block;
            margin-bottom: 4px;
            font-size: 24px;
        }

        .participant-group {
            margin-top: 10px;
        }

        .participant-group h3 {
            margin: 0 0 10px;
            font-size: 16px;
        }

        .poule-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            margin: 0 0 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5eaf0;
            min-width: 0;
        }

        .poule-header h3 {
            margin: 0;
            min-width: 0;
            overflow-wrap: anywhere;
            color: #111827;
            font-size: 15px;
            font-weight: 850;
        }

        .poule-header-main {
            display: grid;
            gap: 6px;
            min-width: 0;
            flex: 1 1 auto;
        }

        .poule-header-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
            flex: 0 0 auto;
            flex-wrap: wrap;
        }

        .poule-title-row,
        .poule-meta-row {
            display: flex;
            align-items: center;
            gap: 7px;
            min-width: 0;
        }

        .poule-title-row {
            justify-content: space-between;
        }

        .poule-meta-row {
            flex-wrap: wrap;
        }

        .rename-poule-toggle,
        .rename-poule-cancel,
        .poule-action-button {
            flex: 0 0 auto;
            margin-top: 0;
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background: #ffffff;
            color: #334155;
            cursor: pointer;
            font-weight: 700;
        }

        .poule-action-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            min-width: 0;
            height: 31px;
            padding: 0 9px;
            font-size: 12px;
            line-height: 1;
            text-decoration: none;
        }

        .app-page .competition-page .rename-poule-toggle,
        .app-page .competition-page .rename-poule-cancel,
        .app-page .competition-page .inline-form .poule-action-button {
            border-color: #cbd5e1;
            background: #ffffff;
            color: #334155;
        }

        .app-page .competition-page .rename-poule-toggle:hover,
        .app-page .competition-page .rename-poule-cancel:hover,
        .app-page .competition-page .inline-form .poule-action-button:hover {
            border-color: #94a3b8;
            background: #f8fafc;
            color: #17202a;
        }

        .app-page .competition-page .inline-form .poule-action-button.danger {
            border-color: #fecaca;
            background: #ffffff;
            color: #b91c1c;
        }

        .app-page .competition-page .inline-form .poule-action-button.danger:hover {
            border-color: #fca5a5;
            background: #fff1f2;
            color: #991b1b;
        }

        .rename-poule-form {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            min-width: 0;
        }

        .poule-title-row[hidden],
        .rename-poule-form[hidden] {
            display: none;
        }

        .rename-poule-form input {
            flex: 1 1 220px;
            min-width: 160px;
            max-width: 360px;
            min-height: 31px;
            padding: 5px 8px;
            font-size: 13px;
        }

        .rename-poule-form button {
            margin-top: 0;
        }

        .poule-count {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border: 1px solid #d9e2ec;
            border-radius: 999px;
            background: #f8fafc;
            color: #334155;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .poule-status-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border: 1px solid #fde68a;
            border-radius: 999px;
            background: #fef9c3;
            color: #854d0e;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .poule-status-badge.frozen {
            border-color: #bbf7d0;
            background: #dcfce7;
            color: #166534;
        }

        .poule-progress-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border: 1px solid #fde68a;
            border-radius: 999px;
            background: #fffbeb;
            color: #92400e;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }

        .poule-progress-badge.complete {
            border-color: #bbf7d0;
            background: #dcfce7;
            color: #166534;
        }

        .poule-assignment-layout {
            display: grid;
            grid-template-columns: minmax(260px, 0.78fr) minmax(420px, 1.42fr);
            gap: 16px;
            align-items: start;
            margin-top: 14px;
        }

        .poule-assignment-layout .subsection {
            margin-top: 0;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
        }

        .poule-assignment-layout .subsection + .subsection {
            margin-top: 12px;
        }

        .poule-assignment-layout .subsection > h3 {
            margin-bottom: 8px;
            padding-left: 2px;
            color: #17202a;
            font-size: 14px;
            font-weight: 850;
        }

        .assignment-column {
            min-width: 0;
        }

        .participant-card-list {
            display: grid;
            gap: 5px;
            margin: 8px 0 0;
        }

        .participant-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            border-radius: 7px;
            background: #ffffff;
            box-shadow: none;
        }

        .participant-card[draggable="true"] {
            cursor: grab;
        }

        .participant-card.is-dragging {
            opacity: 0.45;
        }

        .participant-card-main {
            display: grid;
            gap: 2px;
            min-width: 0;
        }

        .participant-card-main strong {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .participant-card-meta {
            color: #64748b;
            font-size: 11px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .poule-participant-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 5px;
        }

        .participant-card.compact {
            align-items: center;
            padding: 5px 7px;
            border: 1px solid #e5eaf0;
            border-radius: 7px;
            background: #ffffff;
            box-shadow: none;
        }

        .participant-card.compact .participant-card-main {
            display: grid;
            gap: 2px;
            min-width: 0;
            font-size: 12px;
        }

        .participant-card-title {
            color: #17202a;
            font-weight: 700;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .participant-card.compact .participant-card-meta {
            color: #64748b;
            font-size: 11px;
        }

        .app-page .competition-page .visual-remove-button {
            flex: 0 0 auto;
            margin-top: 0;
            padding: 2px 6px;
            border: 0;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            font-size: 14px;
            line-height: 1;
        }

        .app-page .competition-page .visual-remove-button:hover {
            color: #b91c1c;
        }

        .poule-drop-zone {
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #facc15;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.035);
        }

        .poule-drop-zone.frozen {
            border-left-color: #22c55e;
            background: #f8fafc;
        }

        .poule-assignment-layout .section-intro {
            margin-bottom: 8px;
            font-size: 12px;
        }

        .poule-assignment-layout .empty-state {
            margin-top: 8px;
            padding: 8px 9px;
            border: 1px dashed #cbd5e1;
            border-radius: 7px;
            background: #ffffff;
            font-size: 12px;
        }

        .poule-drop-zone.is-drag-over {
            border-color: #1d4ed8;
            border-left-color: #1d4ed8;
            background: #eff6ff;
        }

        .combat-list {
            display: grid;
            gap: 3px;
        }

        .app-page .competition-page .combat-row {
            display: grid;
            grid-template-columns: 58px minmax(120px, 1.15fr) 26px minmax(120px, 1.15fr) 38px 38px 64px 64px minmax(110px, 1fr) 148px;
            align-items: center;
            gap: 4px;
            margin-top: 0;
            padding: 3px 6px 3px 8px;
            border: 1px solid #e5eaf0;
            border-left: 4px solid #2563eb;
            border-radius: 8px;
            background: #ffffff;
            scroll-margin-top: 80px;
        }

        .app-page .competition-page .combat-row.is-finished {
            border-left-color: #22c55e;
            background: #f0fdf4;
        }

        .app-page .competition-page .combat-row.is-editing {
            border-left-color: #f59e0b;
            background: #fffdf6;
        }

        .combat-number {
            color: #334155;
            font-size: 12px;
            font-weight: 800;
            line-height: 1.1;
        }

        .combat-status {
            display: block;
            margin-top: 2px;
            color: #1d4ed8;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .combat-row.is-finished .combat-status {
            color: #15803d;
        }

        .combat-row.is-editing .combat-status {
            color: #c2410c;
        }

        .combat-vs {
            color: #64748b;
            text-align: center;
        }

        .app-page .competition-page .combat-fighter-button,
        .app-page .competition-page .combat-choice-button {
            margin-top: 0;
            border: 1px solid #cfd6df;
            border-radius: 7px;
            background: #ffffff;
            color: #17202a;
            text-align: left;
        }

        .app-page .competition-page .combat-choice-button {
            min-height: 26px;
            padding: 3px 6px;
            text-align: center;
        }

        .app-page .competition-page .combat-fighter-button {
            display: block;
            overflow: hidden;
            min-width: 0;
            min-height: 26px;
            padding: 3px 7px;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 13px;
            font-weight: 500;
        }

        .combat-fighter-name {
            font-weight: 750;
        }

        .combat-fighter-club {
            color: #64748b;
            font-size: 12px;
            font-weight: 500;
        }

        .app-page .competition-page .combat-fighter-button.winner {
            font-weight: 800;
        }

        .app-page .competition-page .combat-fighter-button.muted {
            color: #94a3b8;
        }

        .app-page .competition-page .combat-fighter-button.selected,
        .app-page .competition-page .combat-choice-button.selected {
            border-color: #1d4ed8;
            background: #eff6ff;
            color: #1e3a8a;
        }

        .app-page .competition-page .combat-row:not(.is-finished) .combat-fighter-button:not(:disabled):hover {
            border-color: #1d4ed8;
            background: #eff6ff;
            color: #1e3a8a;
        }

        .app-page .competition-page .combat-row.is-finished .combat-fighter-button:hover {
            border-color: #cfd6df;
            background: #ffffff;
            color: #17202a;
        }

        .app-page .competition-page .combat-row.is-finished .combat-fighter-button.muted:hover {
            color: #94a3b8;
        }

        .app-page .competition-page .combat-row.is-finished .combat-fighter-button.selected:hover {
            border-color: #1d4ed8;
            background: #eff6ff;
            color: #1e3a8a;
        }

        .app-page .competition-page .combat-row input {
            width: 100%;
            max-width: none;
            min-height: 26px;
            padding: 3px 6px;
            border: 1px solid #cfd6df;
            border-radius: 7px;
            font-size: 13px;
        }

        .combat-row input:disabled,
        .app-page .competition-page .combat-fighter-button:disabled,
        .app-page .competition-page .combat-choice-button:disabled {
            opacity: 1;
            cursor: default;
        }

        .combat-actions {
            display: grid;
            grid-template-columns: repeat(4, 34px);
            gap: 4px;
            justify-content: flex-end;
            width: 148px;
        }

        .app-page .competition-page .combat-actions button {
            min-height: 26px;
            margin-top: 0;
            padding: 3px 6px;
            border-color: #cfd6df;
            background: #ffffff;
            color: #17202a;
            text-align: center;
            transition: background-color 140ms ease, border-color 140ms ease, opacity 140ms ease;
        }

        .app-page .competition-page .combat-actions button:disabled {
            visibility: hidden;
            opacity: 0;
            cursor: not-allowed;
            pointer-events: none;
        }

        .app-page .competition-page .combat-actions button:not(:disabled):hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }

        .app-page .competition-page .combat-actions [data-combat-validate]:not(:disabled) {
            border-color: #86efac;
            background: #dcfce7;
            color: #166534;
            font-weight: 800;
        }

        .app-page .competition-page .combat-actions [data-combat-validate]:not(:disabled):hover {
            background: #bbf7d0;
            border-color: #4ade80;
        }

        .app-page .competition-page .combat-clear-button {
            color: #b91c1c;
        }

        .app-page .competition-page [data-combat-cancel]:not(:disabled) {
            color: #475569;
        }

        .print-sheet {
            display: none;
        }

        .print-combats-button {
            margin-bottom: 16px;
            border-color: #cfd6df;
            background: #ffffff;
            color: #17202a;
        }

        .checkbox-line {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-line input {
            width: auto;
        }

        @media (max-width: 860px) {
            .poule-assignment-layout {
                grid-template-columns: 1fr;
            }

            .poule-participant-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .tabs {
                gap: 10px;
            }

            .tabs-list {
                overflow-x: auto;
                flex-wrap: nowrap;
            }

            .tab-button {
                flex: 0 0 auto;
            }

            .poule-header {
                align-items: stretch;
                flex-direction: column;
                gap: 10px;
            }

            .poule-header-actions {
                justify-content: flex-start;
            }

            .poule-title-row {
                align-items: flex-start;
            }

            .rename-poule-form {
                align-items: stretch;
            }

            .combat-row {
                grid-template-columns: 44px minmax(0, 1fr) 28px minmax(0, 1fr);
            }

            .combat-result-draw,
            .combat-result-none,
            .combat-score-red,
            .combat-score-blue {
                grid-column: span 2;
            }

            .combat-comment,
            .combat-actions {
                grid-column: 1 / -1;
            }

            .combat-actions {
                justify-content: flex-start;
            }
        }

        .participant-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .participant-table th,
        .participant-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        .participant-table th {
            background: #f8fafc;
            color: #334155;
            font-size: 12px;
            font-weight: 800;
        }

        .participant-table tr:last-child td {
            border-bottom: 0;
        }

        .section-intro {
            margin-bottom: 16px;
        }

        .subsection {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #e5eaf0;
        }

        .subsection.info-summary {
            margin-top: 0;
            padding: 0 0 18px;
            border-top: 0;
            border-bottom: 1px solid #e5eaf0;
        }

        .subsection h3 {
            margin: 0 0 12px;
            font-size: 16px;
        }

        .empty-state {
            color: #64748b;
        }

        .touch-help {
            display: none;
            margin: -8px 0 16px;
            color: #64748b;
            font-size: 13px;
        }

        .touch-help.is-visible {
            display: block;
        }

        .form-accordion {
            margin-top: 12px;
        }

        .form-accordion summary {
            display: inline-flex;
            width: fit-content;
            padding: 10px 14px;
            border: 1px solid #1d4ed8;
            border-radius: 8px;
            background: #1d4ed8;
            color: #ffffff;
            cursor: pointer;
            font-weight: 700;
            list-style: none;
        }

        .form-accordion summary::-webkit-details-marker {
            display: none;
        }

        .form-accordion form {
            margin-top: 16px;
        }

        .score-accordion summary {
            display: inline-flex;
            width: fit-content;
            padding: 8px 10px;
            border: 1px solid #1d4ed8;
            border-radius: 8px;
            background: #1d4ed8;
            color: #ffffff;
            cursor: pointer;
            font-weight: 700;
            list-style: none;
        }

        .score-accordion summary::-webkit-details-marker {
            display: none;
        }

        .score-form {
            display: grid;
            gap: 10px;
            margin-top: 12px;
        }

        .score-form input {
            max-width: 120px;
        }

        [id^="participants-"],
        [id^="poules-"],
        [id^="combats-"],
        [id^="combat-"] {
            scroll-margin-top: 80px;
        }

        @page {
            size: A4;
            margin: 14mm;
        }

        @media print {
            body {
                background: #ffffff;
                color: #000000;
                font-family: Arial, sans-serif;
                font-size: 11pt;
            }

            main {
                width: auto;
                margin: 0;
            }

            .competition-page > :not(.competition-content) {
                display: none !important;
            }

            .competition-content {
                display: block !important;
                width: auto;
                margin: 0;
            }

            .competition-content > :not(.print-sheet) {
                display: none !important;
            }

            .print-sheet {
                display: block;
            }

            .print-sheet h1 {
                margin: 0 0 4mm;
                font-size: 18pt;
            }

            .print-sheet h2 {
                margin: 6mm 0 3mm;
                padding-bottom: 2mm;
                border-bottom: 1px solid #000000;
                font-size: 13pt;
            }

            .print-fight-table {
                width: 100%;
                border-collapse: collapse;
                color: #000000;
            }

            .print-fight-table th,
            .print-fight-table td {
                padding: 3mm 2mm;
                border: 1px solid #000000;
                color: #000000;
                text-align: left;
                vertical-align: middle;
            }

            .print-fight-table th {
                font-weight: 700;
            }

            .print-fight-table tr {
                break-inside: avoid;
            }

            .print-fight-table .print-center {
                text-align: center;
            }

            .print-empty {
                margin: 0 0 4mm;
                color: #000000;
            }
        }
</style>
@endpush

@section('content')
<main class="competition-page">
        @php
            $allRegistrations = $isOrganizer
                ? $registrationsByClub->flatten(1)->values()
                : $currentClubRegistrations;
            $participantGroups = [
                'Participants validés' => $allRegistrations->filter(fn ($registration) => $registration->is_active && $registration->is_validated)->values(),
                'Participants en attente de validation' => $allRegistrations->filter(fn ($registration) => $registration->is_active && ! $registration->is_validated)->values(),
                'Participants retirés' => $allRegistrations->filter(fn ($registration) => ! $registration->is_active)->values(),
            ];
            $participantGroupEmptyMessages = [
                'Participants validés' => 'Aucun participant validé',
                'Participants en attente de validation' => 'Aucun participant en attente de validation',
                'Participants retirés' => 'Aucun participant retiré',
            ];
            $draftPoules = $competition->poules->where('status', \App\Models\Poule::STATUS_DRAFT)->values();
            $frozenPoules = $competition->poules->where('status', \App\Models\Poule::STATUS_FROZEN)->values();
            $allCombats = $frozenPoules->flatMap(fn ($poule) => $poule->combats)->values();
            $combatsToEnter = $allCombats->where('statut', \App\Models\Combat::STATUS_TO_ENTER)->values();
            $finishedCombats = $allCombats->where('statut', \App\Models\Combat::STATUS_FINISHED)->values();
            $pendingActions = collect($actionsToDo)->reject(fn ($actionToDo) => $actionToDo === 'Aucune action urgente')->values();
            $currentClubSummary = $participantValidationSummary['by_club']->get($currentUser->club_id, ['active' => 0, 'validated' => 0, 'not_validated' => 0]);
            $participantHintCount = $isOrganizer ? $participantValidationSummary['global']['not_validated'] : $currentClubSummary['not_validated'];
            $poulesReady = $eligiblePouleRegistrations->isEmpty() && $draftPoules->isEmpty() && $frozenPoules->isNotEmpty();
            $roleLabel = $competition->roleLabelForClub($currentUser->club);
        @endphp

        @if (session('status'))
            <div class="toast" data-toast>{{ session('status') }}</div>
        @endif

        <nav class="tabs" aria-label="Navigation compétition">
            <div class="tabs-list">
                <button class="tab-button" type="button" data-tab-target="suivi">
                    <svg aria-hidden="true" viewBox="0 0 24 24">
                        <path d="M9 6h11"></path>
                        <path d="M9 12h11"></path>
                        <path d="M9 18h11"></path>
                        <path d="m4 6 1 1 2-2"></path>
                        <path d="m4 12 1 1 2-2"></path>
                        <path d="m4 18 1 1 2-2"></path>
                    </svg>
                    <span>Suivi</span>
                    @if ($pendingActions->isNotEmpty())
                        <span>({{ $pendingActions->count() }})</span>
                    @endif
                </button>
                <button class="tab-button" type="button" data-tab-target="clubs">
                    <svg aria-hidden="true" viewBox="0 0 24 24">
                        <path d="M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path>
                        <path d="M16 8h2a2 2 0 0 1 2 2v11"></path>
                        <path d="M8 7h4"></path>
                        <path d="M8 11h4"></path>
                        <path d="M8 15h4"></path>
                        <path d="M3 21h18"></path>
                    </svg>
                    <span>Clubs</span>
                </button>
                <button class="tab-button" type="button" data-tab-target="participants">
                    <svg aria-hidden="true" viewBox="0 0 24 24">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <span>Participants</span>
                </button>
                @if ($isOrganizer)
                    <button class="tab-button" type="button" data-tab-target="poules">
                        <svg aria-hidden="true" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                            <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                            <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                            <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                        </svg>
                        <span>Poules</span>
                    </button>
                    <button class="tab-button" type="button" data-tab-target="combats">
                        <svg aria-hidden="true" viewBox="0 0 24 24">
                            <path d="M14.5 4.5 19 3l-1.5 4.5L7 18l-3 1 1-3 10.5-10.5Z"></path>
                            <path d="M9.5 4.5 5 3l1.5 4.5L17 18l3 1-1-3L8.5 5.5Z"></path>
                            <path d="M7.5 14.5 9.5 16.5"></path>
                            <path d="M16.5 14.5 14.5 16.5"></path>
                        </svg>
                        <span>Combats</span>
                    </button>
                @endif
            </div>
        </nav>

        <div class="competition-content">

        <section id="actions" class="tab-panel" data-tab-panel="suivi">
            <div class="competition-overview">
                <div class="competition-overview-heading">
                    <div class="competition-overview-title">
                        <div class="competition-name-display" data-competition-name-display>
                            <h1>{{ $competition->name }}</h1>
                            @if ($isOrganizer)
                                <button class="title-icon-button" type="button" title="Modifier le nom de la compétition" data-competition-name-edit>✏️</button>
                            @endif
                        </div>

                        @if ($isOrganizer)
                            <form class="competition-name-form" method="POST" action="{{ route('competitions.update', $competition) }}" data-competition-name-form>
                                @csrf
                                @method('PATCH')
                                <input type="text" name="name" value="{{ old('name', $competition->name) }}" required maxlength="255" aria-label="Nom de la compétition">
                                <button class="title-icon-button" type="submit" title="Enregistrer">✔</button>
                                <button class="title-icon-button cancel" type="button" title="Annuler" data-competition-name-cancel>✖</button>
                            </form>
                        @endif

                        <p class="competition-overview-meta">
                            {{ $competition->date_competition?->format('d/m/Y') ?? 'Date non renseignée' }}
                            · organisé par {{ $competition->organizerClub->name }}
                        </p>
                    </div>

                </div>
            </div>

            <div class="actions-summary">
                <h2>Actions à faire</h2>

                <ul class="actions-todo-list">
                    @foreach ($actionsToDo as $actionToDo)
                        <li>{{ $actionToDo }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="subsection info-summary">
                <h2>Informations compétition</h2>
                <dl class="compact-info">
                    <div>
                        <dt>Organisateur :</dt>
                        <dd>{{ $competition->organizerClub->name }}</dd>
                    </div>

                    <div>
                        <dt>Date :</dt>
                        <dd>{{ $competition->date_competition?->format('d/m/Y') ?? 'Date non renseignée' }}</dd>
                    </div>

                    <div>
                        <dt>Clubs confirmés :</dt>
                        <dd>{{ $invitationSummary[\App\Models\Invitation::STATUS_PARTICIPATION_CONFIRMED] }}</dd>
                    </div>

                    <div>
                        <dt>Participants actifs :</dt>
                        <dd>{{ $participantTotal }}</dd>
                    </div>
                </dl>

                @if ($isOrganizer)
                    <div class="subsection">
                        <h3>Modifier la date</h3>
                        <form method="POST" action="{{ route('competitions.date.update', $competition) }}">
                            @csrf
                            @method('PATCH')

                            <label for="date_competition">Date de la compétition</label>
                            <input id="date_competition" type="date" name="date_competition" value="{{ old('date_competition', $competition->date_competition?->format('Y-m-d')) }}">

                            @error('date_competition')
                                <div class="error">{{ $message }}</div>
                            @enderror

                            <button type="submit">Enregistrer la date</button>
                        </form>
                    </div>
                @endif

                <div class="additional-info">
                    <div class="additional-info-header">
                        <strong>Informations complémentaires</strong>
                        @if ($isOrganizer)
                            <button class="title-icon-button" type="button" title="Modifier les informations complémentaires" data-additional-info-edit>✏️</button>
                        @endif
                    </div>

                    <div data-additional-info-display>
                        @if (filled($competition->informations_complementaires))
                            <p>{!! nl2br(e($competition->informations_complementaires)) !!}</p>
                        @else
                            <p class="additional-info-empty">Aucune information complémentaire renseignée.</p>
                        @endif
                    </div>

                    @if ($isOrganizer)
                        <form class="additional-info-form" method="POST" action="{{ route('competitions.informations-complementaires.update', $competition) }}" data-additional-info-form>
                            @csrf
                            @method('PATCH')

                            <label for="informations_complementaires">Texte visible par les clubs</label>
                            <textarea id="informations_complementaires" name="informations_complementaires" maxlength="1000">{{ old('informations_complementaires', $competition->informations_complementaires) }}</textarea>

                            @error('informations_complementaires')
                                <div class="error">{{ $message }}</div>
                            @enderror

                            <button type="submit">Enregistrer les informations</button>
                            <button type="button" class="secondary-button" data-additional-info-cancel>Annuler</button>
                        </form>
                    @endif
                </div>
            </div>
        </section>

        <section id="clubs" class="tab-panel" data-tab-panel="clubs">
            <h2>Clubs / invitations</h2>

            <h3>Récapitulatif des clubs</h3>

            <div class="summary-grid">
                <div class="summary-item">
                    <strong>{{ $invitationSummary[\App\Models\Invitation::STATUS_PRE_INVITE] }}</strong>
                    <span>Préparation de l’invitation</span>
                </div>

                <div class="summary-item">
                    <strong>{{ $invitationSummary[\App\Models\Invitation::STATUS_INVITE] }}</strong>
                    <span>En attente</span>
                </div>

                <div class="summary-item">
                    <strong>{{ $invitationSummary[\App\Models\Invitation::STATUS_PARTICIPATION_CONFIRMED] }}</strong>
                    <span>Confirmés</span>
                </div>

                <div class="summary-item">
                    <strong>{{ $invitationSummary[\App\Models\Invitation::STATUS_PARTICIPATION_DECLINED] }}</strong>
                    <span>Refusés</span>
                </div>
            </div>

            @if (!$isOrganizer && $currentInvitation)
                <div id="invitation" class="subsection">
                    <h3>Réponse de mon club</h3>
                    <p>Statut : <span class="status">{{ $currentInvitation->statusLabel() }}</span></p>

                    @if ($currentInvitation->status === \App\Models\Invitation::STATUS_INVITE)
                        <div class="response-actions">
                            <form method="POST" action="{{ route('competitions.invitations.confirm', [$competition, $currentInvitation]) }}">
                                @csrf
                                <button type="submit">Confirmer la participation</button>
                            </form>

                            <form method="POST" action="{{ route('competitions.invitations.decline', [$competition, $currentInvitation]) }}">
                                @csrf
                                <button class="decline-button" type="submit">Refuser la participation</button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif

            @if ($isOrganizer)
                <ul class="invitation-list">
                    <li>
                        <strong>{{ $competition->organizerClub->name }}</strong>
                    @php
                        $organizerValidationSummary = $participantValidationSummary['by_club']->get($competition->organizer_club_id, ['active' => 0, 'validated' => 0, 'not_validated' => 0]);
                    @endphp
                        <span>
                            Organisateur -
                            {{ $organizerValidationSummary['active'] }} actif(s),
                            {{ $organizerValidationSummary['validated'] }} participant(s) validé(s),
                            {{ $organizerValidationSummary['not_validated'] }} en attente de validation
                        </span>
                    </li>
                </ul>
            @elseif ($currentInvitation)
                @php
                    $currentClubValidationSummary = $participantValidationSummary['by_club']->get($currentUser->club_id, ['active' => 0, 'validated' => 0, 'not_validated' => 0]);
                @endphp
                <p>
                    Participants de mon club :
                    {{ $currentClubValidationSummary['active'] }} actif(s),
                    {{ $currentClubValidationSummary['validated'] }} participant(s) validé(s),
                    {{ $currentClubValidationSummary['not_validated'] }} en attente de validation
                </p>
            @endif

            <div class="subsection">
                <h3>Clubs invités</h3>

            @if ($competition->invitations->isNotEmpty())
                <ul class="invitation-list">
                    @foreach ($competition->invitations as $invitation)
                        <li>
                            <div class="invitation-main">
                                <strong>{{ $invitation->club->name }}</strong>
                                <span>
                                    {{ $invitationStatusLabels[$invitation->status] }}
                                    @if ($isOrganizer)
                                        @php
                                            $clubValidationSummary = $participantValidationSummary['by_club']->get($invitation->club_id, ['active' => 0, 'validated' => 0, 'not_validated' => 0]);
                                        @endphp
                                        -
                                        {{ $clubValidationSummary['active'] }} actif(s),
                                        {{ $clubValidationSummary['validated'] }} participant(s) validé(s),
                                        {{ $clubValidationSummary['not_validated'] }} en attente de validation
                                    @endif
                                </span>
                            </div>

                            @if ($isOrganizer && $invitation->status === \App\Models\Invitation::STATUS_PRE_INVITE)
                                <form class="inline-form" method="POST" action="{{ route('competitions.invitations.mark-sent', [$competition, $invitation]) }}">
                                    @csrf
                                    <button type="submit">Marquer envoyée</button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p>Aucun club invité.</p>
            @endif
            </div>

            @if ($isOrganizer)
                <div class="subsection">
                    <h3>Ajouter un club pré-invité</h3>

                    @if ($availableClubs->isNotEmpty())
                        <form method="POST" action="{{ route('competitions.invitations.store', $competition) }}">
                            @csrf

                            <label for="club_id">Club</label>
                            <select id="club_id" name="club_id" required>
                                @foreach ($availableClubs as $club)
                                    <option value="{{ $club->id }}" @selected((int) old('club_id') === $club->id)>
                                        {{ $club->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('club_id')
                                <div class="error">{{ $message }}</div>
                            @enderror

                            <button type="submit">Ajouter en pré-invité</button>
                        </form>
                    @else
                        <p class="empty-state">Aucun club disponible à inviter.</p>
                    @endif
                </div>
            @endif
        </section>

        <section id="participants" class="tab-panel" data-tab-panel="participants">
            <h2>Participants inscrits</h2>
            @if ($isOrganizer)
                <div class="subsection">
                    @if ($competition->inscriptions_closed)
                        <form class="inline-form" method="POST" action="{{ route('competitions.open-inscriptions', $competition) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit">Réouvrir les inscriptions</button>
                        </form>
                    @else
                        <form class="inline-form" method="POST" action="{{ route('competitions.close-inscriptions', $competition) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit">Fermer les inscriptions</button>
                        </form>
                    @endif
                </div>
            @elseif ($competition->inscriptions_closed)
                <p class="tab-hint">Inscriptions fermées</p>
            @endif
            @if ($participantHintCount > 0)
                <p class="tab-hint">{{ $participantHintCount }} participant(s) en attente</p>
            @endif
            <p class="section-intro">Ajoutez et validez les participants avant de créer les poules.</p>

            @if ($isOrganizer)
                <div class="summary-grid">
                    <div class="summary-item">
                        <strong>{{ $participantValidationSummary['global']['active'] }}</strong>
                        <span>Participants actifs</span>
                    </div>

                    <div class="summary-item">
                        <strong>{{ $participantValidationSummary['global']['validated'] }}</strong>
                        <span>Participants validés par le club organisateur</span>
                    </div>

                    <div class="summary-item">
                        <strong>{{ $participantValidationSummary['global']['not_validated'] }}</strong>
                        <span>En attente de validation par le club organisateur</span>
                    </div>
                </div>
            @elseif ($currentInvitation)
                @php
                    $currentClubValidationSummary = $participantValidationSummary['by_club']->get($currentUser->club_id, ['active' => 0, 'validated' => 0, 'not_validated' => 0]);
                @endphp
                <p class="section-intro">
                    Participants de mon club :
                    {{ $currentClubValidationSummary['active'] }} actif(s),
                    {{ $currentClubValidationSummary['validated'] }} participant(s) validé(s),
                    {{ $currentClubValidationSummary['not_validated'] }} en attente de validation
                </p>
            @endif

            @if ($canRegisterParticipants)
                <div class="subsection">
                    <details id="participants-ajout" class="form-accordion">
                        <summary>Ajouter un participant</summary>

                        <form method="POST" action="{{ route('competitions.participants.store', $competition) }}">
                            @csrf

                            <div class="form-grid">
                                @if ($isOrganizer)
                                    <div>
                                        <label for="participant_club_id">Club</label>
                                        <select id="participant_club_id" name="club_id" required>
                                            @foreach ($participantClubOptions as $club)
                                                <option value="{{ $club->id }}" @selected((int) old('club_id', $competition->organizer_club_id) === $club->id)>
                                                    {{ $club->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('club_id')
                                            <div class="error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif

                                <div>
                                    <label for="last_name">Nom</label>
                                    <input id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                                    @error('last_name')
                                        <div class="error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div>
                                    <label for="first_name">Prénom</label>
                                    <input id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                    @error('first_name')
                                        <div class="error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div>
                                    <label for="sex">Sexe</label>
                                    <select id="sex" name="sex" required>
                                        <option value="F" @selected(old('sex') === 'F')>F</option>
                                        <option value="M" @selected(old('sex') === 'M')>M</option>
                                    </select>
                                    @error('sex')
                                        <div class="error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div>
                                    <label for="age">Âge</label>
                                    <input id="age" name="age" type="number" min="1" max="120" value="{{ old('age') }}" required>
                                    @error('age')
                                        <div class="error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div>
                                    <label for="approximate_weight">Poids approximatif</label>
                                    <input id="approximate_weight" name="approximate_weight" type="number" min="1" max="300" step="0.1" value="{{ old('approximate_weight') }}" required>
                                    @error('approximate_weight')
                                        <div class="error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div>
                                    <label for="license_number">Numéro de licence optionnel</label>
                                    <input id="license_number" name="license_number" value="{{ old('license_number') }}">
                                    @error('license_number')
                                        <div class="error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <button type="submit">Ajouter un participant</button>
                        </form>
                    </details>

                    <details class="form-accordion">
                        <summary>Ajouter depuis mes licenciés</summary>

                        @if ($currentClubLicencies->isNotEmpty())
                            <form method="POST" action="{{ route('competitions.participants.store-from-licencie', $competition) }}">
                                @csrf

                                <div class="form-grid">
                                    <div>
                                        <label for="licencie_id">Licencié</label>
                                        <select id="licencie_id" name="licencie_id" required>
                                            @foreach ($currentClubLicencies as $licencie)
                                                @php
                                                    $isLicencieAlreadyRegistered = $registeredLicencieIds->contains($licencie->id);
                                                    $licencieLabel = "{$licencie->nom} {$licencie->prenom} - {$licencie->date_naissance->age} ans - {$licencie->poids} kg";
                                                    $licencieLabel .= $isLicencieAlreadyRegistered ? ' — Déjà inscrit' : '';
                                                @endphp
                                                <option value="{{ $licencie->id }}"{{ $isLicencieAlreadyRegistered ? ' disabled' : '' }}>
                                                    {{ $licencieLabel }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('licencie_id')
                                            <div class="error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <button type="submit">Ajouter ce licencié</button>
                            </form>
                        @else
                            <p class="empty-state">Aucun licencié disponible pour ce club.</p>
                        @endif
                    </details>
                </div>
            @elseif (!$isOrganizer && $competition->inscriptions_closed)
                <p class="empty-state">Inscriptions fermées</p>
            @endif

            @if ($allRegistrations->isNotEmpty())
                @foreach ($participantGroups as $groupTitle => $groupRegistrations)
                    @php
                        $participantGroupId = match ($groupTitle) {
                            'Participants validés' => 'participants-valides',
                            'Participants en attente de validation' => 'participants-non-valides',
                            'Participants retirés' => 'participants-retires',
                            default => null,
                        };
                    @endphp
                    <div @if ($participantGroupId) id="{{ $participantGroupId }}" @endif class="participant-section-card">
                        <div class="participant-section-header">
                            <h3>{{ $groupTitle }}</h3>
                            <span class="participant-section-count">{{ $groupRegistrations->count() }} participant(s)</span>
                        </div>
                        <div class="participant-section-body">

                        @if ($groupRegistrations->isNotEmpty())
                            <table class="participant-table">
                                <thead>
                                    <tr>
                                        @if ($isOrganizer)
                                            <th>Club</th>
                                        @endif
                                        <th>Participant</th>
                                        <th>Sexe</th>
                                        <th>Âge</th>
                                        <th>Poids approximatif</th>
                                        <th>Licence</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($groupRegistrations as $registration)
                                        <tr @class(['inactive' => ! $registration->is_active])>
                                            @if ($isOrganizer)
                                                <td>{{ $registration->club->name }}</td>
                                            @endif
                                            <td>
                                                <span class="participant-name-line">
                                                    <strong>{{ $registration->participantSource->last_name }} {{ $registration->participantSource->first_name }}</strong>
	                                                    <span class="state-badges">
	                                                        @if (! $registration->is_active)
	                                                            <span class="state-badge withdrawn">{{ $registration->participationStatusLabel() }}</span>
	                                                        @else
	                                                            <span @class(['state-badge', 'validated' => $registration->is_validated, 'pending' => ! $registration->is_validated])>{{ $registration->participationStatusLabel() }}</span>
	                                                            @if ($registration->poule)
	                                                                <span class="state-badge poule">Poule : {{ $registration->poule->name }}</span>
	                                                                @if ($registration->poule->status === \App\Models\Poule::STATUS_FROZEN)
	                                                                    <span class="state-badge poule">Poule figée</span>
	                                                                @endif
	                                                            @endif
	                                                        @endif
                                                    </span>
                                                </span>
                                            </td>
                                            <td>{{ $registration->participantSource->sex }}</td>
                                            <td>{{ $registration->participantSource->age }}</td>
                                            <td>{{ $registration->participantSource->approximate_weight }}</td>
                                            <td>{{ $registration->participantSource->license_number ?? '-' }}</td>
	                                            <td>
		                                                @if ($isOrganizer)
	                                                        <div class="participant-actions">
		                                                    @if (! $registration->is_active || ! $registration->is_validated)
		                                                        @if (! $registration->validateBlockedMessage())
		                                                            <form class="inline-form" method="POST" action="{{ route('competitions.participants.validate', [$competition, $registration]) }}">
		                                                                @csrf
	                                                                @method('PATCH')
	                                                                <button type="submit">Valider</button>
	                                                            </form>
		                                                        @endif
		                                                    @endif
	                                                            @if (! $registration->withdrawBlockedMessage())
                                                                <form class="inline-form" method="POST" action="{{ route('competitions.participants.withdraw', [$competition, $registration]) }}" onsubmit="return confirm('Retirer ce participant de la compétition ?')">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button class="withdraw-button" type="submit">Retirer</button>
                                                                </form>
                                                            @endif
                                                        </div>
	                                                @else
	                                                    @if ($competition->inscriptions_closed)
	                                                        -
                                                    @else
                                                        <div class="participant-actions">
                                                            @if (! $registration->editBlockedMessage())
                                                                <a href="{{ route('competitions.participants.edit', [$competition, $registration]) }}">Modifier</a>
                                                            @endif

	                                                            @if (! $registration->withdrawBlockedMessage())
	                                                                <form class="inline-form" method="POST" action="{{ route('competitions.participants.withdraw', [$competition, $registration]) }}" onsubmit="return confirm('Retirer ce participant de la compétition ?')">
	                                                                    @csrf
	                                                                    @method('PATCH')
	                                                                    <button class="withdraw-button" type="submit">Retirer</button>
                                                                </form>
                                                            @elseif (! $registration->reactivateBlockedMessage())
                                                                <form class="inline-form" method="POST" action="{{ route('competitions.participants.reactivate', [$competition, $registration]) }}">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button class="reactivate-button" type="submit">Réactiver</button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="empty-state">{{ $participantGroupEmptyMessages[$groupTitle] }}</p>
                        @endif
                        </div>
                    </div>
                @endforeach
                <p class="participant-note">Un participant retiré n’apparaît plus dans les poules et n’est plus comptabilisé comme participant actif.</p>
            @else
                <p class="empty-state">Aucun participant inscrit</p>
            @endif
        </section>

        @if ($isOrganizer)
            <section id="poules" class="tab-panel" data-tab-panel="poules">
                <h2>Organisation des poules</h2>
                @if ($participantValidationSummary['global']['not_validated'] > 0)
                    <div class="poule-validation-alert">
                        <span>⚠️ {{ $participantValidationSummary['global']['not_validated'] }} participant(s) non validé(s) — continuer quand même ?</span>
                        <a href="#participants" data-tab-link-target="participants">👉 Voir les participants</a>
                    </div>
                @endif
                @if ($eligiblePouleRegistrations->isNotEmpty())
                    <p class="tab-hint">{{ $eligiblePouleRegistrations->count() }} participant(s) non affecté(s)</p>
                @endif
                <div class="poule-guidance">
                    @if ($eligiblePouleRegistrations->isNotEmpty())
                        <p>{{ $eligiblePouleRegistrations->count() }} participant(s) non affecté(s)</p>
                    @endif

                    @if ($draftPoules->isNotEmpty())
                        <p>{{ $draftPoules->count() }} poule(s) en préparation (non figée(s))</p>
                    @endif

                    @if ($poulesReady)
                        <div class="poule-ready">Poules prêtes — tous les participants sont affectés et les poules sont figées</div>
                    @endif
                </div>
                <p class="section-intro">Affectez les participants puis figez les poules pour générer les combats.</p>

                <div class="subsection">
                    <div id="assistant-poules" class="poule-assistant">
                        <div class="poule-assistant-header">
                            <div>
                                <h3>Assistant de génération des poules</h3>
                                <p class="section-intro">Le logiciel propose des groupes à partir des participants disponibles. Vous gardez la décision finale.</p>
                            </div>
                        </div>

                        <form class="poule-assistant-form" method="GET" action="{{ route('competitions.show', $competition) }}">
                            <input type="hidden" name="analyze_poules" value="1">
                            <div class="poule-assistant-criteria">
                                <div>
                                    <label for="same_sex_only">Même sexe uniquement</label>
                                    <select id="same_sex_only" name="same_sex_only">
                                        <option value="1" @selected($pouleAssistantCriteria['same_sex_only'])>Oui</option>
                                        <option value="0" @selected(! $pouleAssistantCriteria['same_sex_only'])>Non</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="age_gap_max">Écart d’âge max</label>
                                    <input id="age_gap_max" name="age_gap_max" type="number" min="0" max="50" value="{{ $pouleAssistantCriteria['age_gap_max'] }}">
                                </div>
                                <div>
                                    <label for="weight_gap_max">Écart de poids max</label>
                                    <input id="weight_gap_max" name="weight_gap_max" type="number" min="0" max="200" step="0.5" value="{{ $pouleAssistantCriteria['weight_gap_max'] }}">
                                </div>
                                <div>
                                    <label for="target_size">Taille cible d’une poule</label>
                                    <input id="target_size" name="target_size" type="number" min="2" max="16" value="{{ $pouleAssistantCriteria['target_size'] }}">
                                </div>
                                <div>
                                    <label for="adult_access_age">Accès aux poules adultes à partir de</label>
                                    <input id="adult_access_age" name="adult_access_age" type="number" min="12" max="30" value="{{ $pouleAssistantCriteria['adult_access_age'] }}">
                                </div>
                            </div>
                            <div class="poule-assistant-actions">
                                <button type="submit">Analyser les participants</button>
                                @if ($pouleAssistantResult)
                                    <button class="secondary-button" type="submit">Recalculer</button>
                                    <a class="secondary-button" href="{{ route('competitions.show', $competition) }}#assistant-poules">Fermer les suggestions</a>
                                @endif
                            </div>
                        </form>

                        @if ($pouleAssistantResult)
                            <div class="poule-assistant-results">
                            @if ($pouleAssistantResult['proposals']->isNotEmpty())
                                <div class="poule-assistant-results-header">
                                    <strong>Poules proposées</strong>
                                    <form class="inline-form" method="POST" action="{{ route('competitions.poules.proposals.store', $competition) }}">
                                        @csrf
                                        @foreach ($pouleAssistantResult['proposals'] as $proposal)
                                            <input type="hidden" name="proposal_names[]" value="{{ $proposal['name'] }}">
                                            <input type="hidden" name="proposal_registration_ids[]" value="{{ $proposal['registrations']->pluck('id')->implode(',') }}">
                                        @endforeach
                                        <button type="submit">Créer toutes les propositions</button>
                                    </form>
                                </div>

                                <div class="poule-proposals">
                                    @foreach ($pouleAssistantResult['proposals'] as $proposal)
                                        @php
                                            $indicatorClass = match ($proposal['indicator']) {
                                                'Très cohérent' => 'very-good',
                                                'À arbitrer' => 'arbitrate',
                                                default => '',
                                            };
                                        @endphp
                                        <div class="poule-proposal-card">
                                            <div class="poule-proposal-top">
                                                <div class="poule-proposal-heading">
                                                    <h4>{{ $proposal['name'] }}</h4>
                                                    <span class="poule-proposal-score">Score : {{ $proposal['score'] }}/100</span>
                                                </div>
                                                <span @class(['poule-proposal-indicator', $indicatorClass => $indicatorClass !== ''])>{{ $proposal['indicator'] }}</span>
                                            </div>
                                            <p class="poule-proposal-explanation">{{ $proposal['justification'] }}</p>
                                            @if ($proposal['warning'])
                                                <div class="poule-proposal-warning">{{ $proposal['warning'] }}</div>
                                            @endif
                                            <ul class="poule-proposal-list">
                                                @foreach ($proposal['registrations'] as $registration)
                                                    <li>
                                                        <strong>{{ $registration->participantSource->last_name }} {{ $registration->participantSource->first_name }}</strong>
                                                        <span>{{ $registration->club->name }} · {{ $registration->participantSource->sex }} · {{ $registration->participantSource->age }} ans · {{ $registration->participantSource->approximate_weight }} kg</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <form class="inline-form" method="POST" action="{{ route('competitions.poules.proposals.store', $competition) }}">
                                                @csrf
                                                <input type="hidden" name="proposal_names[]" value="{{ $proposal['name'] }}">
                                                <input type="hidden" name="proposal_registration_ids[]" value="{{ $proposal['registrations']->pluck('id')->implode(',') }}">
                                                <button class="secondary-button" type="submit">Créer cette poule</button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="empty-state">Aucune proposition compatible trouvée.</p>
                            @endif

                            <div class="poule-unassigned-panel">
                                <strong class="poule-unassigned-title">Participants non affectés</strong>
                                @if ($pouleAssistantResult['unassigned']->isNotEmpty())
                                    <ul class="poule-unassigned-list">
                                        @foreach ($pouleAssistantResult['unassigned'] as $unassigned)
                                            <li>
                                                <strong>{{ $unassigned['registration']->participantSource->last_name }} {{ $unassigned['registration']->participantSource->first_name }}</strong>
                                                <span>{{ $unassigned['registration']->club->name }} · {{ $unassigned['registration']->participantSource->sex }} · {{ $unassigned['registration']->participantSource->age }} ans · {{ $unassigned['registration']->participantSource->approximate_weight }} kg · {{ $unassigned['reason'] }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="empty-state">Tous les participants disponibles ont une proposition.</p>
                                @endif
                            </div>
                            </div>
                        @endif
                    </div>

                    <h3>Poules</h3>

                    <details id="creation-poule" class="form-accordion">
                        <summary>Créer une poule</summary>

                        <form method="POST" action="{{ route('competitions.poules.store', $competition) }}">
                            @csrf

                            <div class="form-grid">
                                <div>
                                    <label for="poule_name">Nom de la poule</label>
                                    <input id="poule_name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <button type="submit">Créer une poule</button>
                        </form>
                    </details>
                </div>

                <div class="poule-assignment-layout">
                    <div class="assignment-column">
                <div id="participants-disponibles" class="subsection">
                    <h3>Participants disponibles</h3>
                    <p class="touch-help" data-touch-help>Appui long + glisser pour affecter</p>
                    <p class="section-intro">Participants validés, actifs et pas encore affectés à une poule.</p>

                    @if ($eligiblePouleRegistrations->isNotEmpty())
                        <div id="available-participant-cards" class="participant-card-list" data-available-list>
                            @foreach ($eligiblePouleRegistrations as $registration)
                                <div
                                    class="participant-card"
                                    draggable="true"
                                    data-inscription-id="{{ $registration->id }}"
                                    data-source="available"
                                >
                                    <div class="participant-card-main">
                                        <strong>{{ $registration->participantSource->last_name }} {{ $registration->participantSource->first_name }}</strong>
                                        <span class="participant-card-meta">
                                            ({{ $registration->club->name }})
                                            —
                                            {{ $registration->participantSource->sex }},
                                            {{ $registration->participantSource->age }} ans,
                                            {{ $registration->participantSource->approximate_weight }} kg
                                            @if ($registration->participantSource->license_number)
                                                - {{ $registration->participantSource->license_number }}
                                            @endif
                                        </span>
                                    </div>

                                    @if ($competition->poules->isEmpty())
                                        <span class="participant-card-meta">Créer une poule d'abord</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <p class="empty-state" data-available-empty hidden>Aucun participant disponible pour affectation</p>
                    @else
                        <div id="available-participant-cards" class="participant-card-list" data-available-list></div>
                        <p class="empty-state" data-available-empty>Aucun participant disponible pour affectation</p>
                    @endif
                </div>
                    </div>

                    <div class="assignment-column">

                @if ($competition->poules->isNotEmpty())
                    @foreach (['Poules en préparation' => $draftPoules, 'Poules figées' => $frozenPoules] as $pouleGroupTitle => $poules)
                        @php
                            $pouleGroupId = $pouleGroupTitle === 'Poules en préparation' ? 'poules-brouillon' : 'poules-figees';
                        @endphp
                        <div id="{{ $pouleGroupId }}" class="subsection">
                            <h3>{{ $pouleGroupTitle }}</h3>

                            @if ($poules->isNotEmpty())
                                @foreach ($poules as $poule)
	                                    @php
	                                        $pouleRegistrationsCount = $poule->registrations->count();
                                            $hasCombats = $poule->combats->isNotEmpty();
	                                        $hasScoredCombats = $poule->hasScoredCombats();
	                                    @endphp
                                    <div
                                        @class([
                                            'participant-group',
                                            'poule-drop-zone',
                                            'frozen' => $poule->status === \App\Models\Poule::STATUS_FROZEN,
                                        ])
                                        data-poule-id="{{ $poule->id }}"
                                        data-drop-enabled="{{ $poule->status === \App\Models\Poule::STATUS_DRAFT ? 'true' : 'false' }}"
                                        data-assign-url="{{ route('competitions.poules.registrations.store', [$competition, $poule]) }}"
                                    >
	                                        <div class="poule-header">
	                                            <div class="poule-header-main">
                                                    <div class="poule-title-row" data-rename-display>
	                                                    <h3>{{ $poule->name }}</h3>
                                                        @if ($isOrganizer)
	                                                            <button class="rename-poule-toggle poule-action-button" type="button" title="Renommer la poule" data-rename-open>✏️ Renommer</button>
                                                        @endif
                                                    </div>

                                                    @if ($isOrganizer)
                                                        <form class="rename-poule-form" method="POST" action="{{ route('competitions.poules.rename', [$competition, $poule]) }}" data-rename-form hidden>
                                                            @csrf
                                                            @method('PATCH')
                                                            <input
                                                                name="name"
                                                                value="{{ old('name', $poule->name) }}"
                                                                maxlength="100"
                                                                required
                                                            >
                                                            <button type="submit">Enregistrer</button>
                                                            <button class="rename-poule-cancel" type="button" data-rename-cancel>Annuler</button>
                                                        </form>
                                                    @endif

                                                    <div class="poule-meta-row">
	                                                    <span class="poule-count" data-poule-count>{{ $pouleRegistrationsCount }} participant(s)</span>
	                                                    @if ($poule->status === \App\Models\Poule::STATUS_FROZEN)
	                                                        <span class="poule-status-badge frozen">🟢 Figée</span>
	                                                    @else
	                                                        <span class="poule-status-badge">🟡 En préparation</span>
	                                                    @endif
                                                    </div>
	                                            </div>
	
	                                            <div class="poule-header-actions">
	                                                @if (! $poule->freezeBlockedMessage())
                                                    <form id="freeze_poule_{{ $poule->id }}" class="inline-form" method="POST" action="{{ route('competitions.poules.freeze', [$competition, $poule]) }}">
                                                        @csrf
                                                        @method('PATCH')
	                                                        <button
                                                                class="poule-action-button"
	                                                            type="submit"
	                                                            form="freeze_poule_{{ $poule->id }}"
	                                                            formmethod="post"
	                                                            formaction="{{ route('competitions.poules.freeze', [$competition, $poule]) }}"
                                                                title="Figer et générer combats"
		                                                        >
		                                                            🔒⚔️ Figer
		                                                        </button>
                                                    </form>
                                                @elseif ($poule->status === \App\Models\Poule::STATUS_FROZEN)
                                                    <form
                                                        class="inline-form"
                                                        method="POST"
                                                        action="{{ route('competitions.poules.unfreeze', [$competition, $poule]) }}"
                                                        @if ($hasScoredCombats) onsubmit="return confirm('Des scores ont déjà été saisis. Cette action supprimera les combats et leurs résultats.')" @endif
                                                    >
                                                        @csrf
                                                        @method('PATCH')
	                                                        <button class="withdraw-button" type="submit">Défiger</button>
	                                                    </form>
	                                                @endif

                                                    @if ($isOrganizer)
                                                        <form
                                                            class="inline-form"
                                                            method="POST"
                                                            action="{{ route('competitions.poules.destroy', [$competition, $poule]) }}"
                                                            @if ($hasCombats) onsubmit="return confirm('Cette poule contient des combats. Cette action supprimera les combats liés à la poule.')" @endif
                                                        >
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="poule-action-button danger" type="submit" title="Supprimer la poule">🗑️ Supprimer</button>
                                                        </form>
                                                    @endif
	                                            </div>
	                                        </div>

                                        @if ($poule->status === \App\Models\Poule::STATUS_FROZEN && $hasScoredCombats)
                                            <p class="warning-message">Des scores ont déjà été saisis. Cette action supprimera les combats et leurs résultats.</p>
                                        @endif

                                        @if ($poule->registrations->isNotEmpty())
                                            <div class="participant-card-list poule-participant-grid" data-poule-list>
                                                @foreach ($poule->registrations as $registration)
                                                    <div
                                                        class="participant-card compact"
                                                        data-inscription-id="{{ $registration->id }}"
                                                        data-source="poule"
                                                        data-withdraw-url="{{ route('competitions.registrations.withdraw-assignment', [$competition, $registration]) }}"
                                                    >
                                                        <div class="participant-card-main">
                                                            <span class="participant-card-title">
                                                                {{ $registration->participantSource->last_name }} {{ $registration->participantSource->first_name }}
                                                                —
                                                                {{ $registration->participantSource->age }} ans
                                                                —
                                                                {{ $registration->participantSource->approximate_weight }} kg
                                                            </span>
                                                            <span class="participant-card-meta">({{ $registration->club->name }})</span>
                                                        </div>
                                                        @if ($poule->status === \App\Models\Poule::STATUS_DRAFT)
                                                            <button class="visual-remove-button" type="button" title="Retirer de la poule" data-remove-visual>✖</button>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>

                                        @else
                                            <div class="participant-card-list poule-participant-grid" data-poule-list></div>
                                            <p class="empty-state">Aucun participant inscrit</p>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <p class="empty-state">Aucune poule créée</p>
                            @endif
                        </div>
                    @endforeach
                @else
                    <p class="empty-state">Aucune poule créée</p>
                @endif
                    </div>
                </div>
            </section>

            <section id="combats" class="tab-panel" data-tab-panel="combats">
                <h2>Combats</h2>
                @if ($combatsToEnter->isNotEmpty())
                    <p class="tab-hint">{{ $combatsToEnter->count() }} score(s) à saisir</p>
                @endif
                <p class="section-intro">Saisissez les scores pour générer le classement.</p>
                @if ($allCombats->isNotEmpty())
                    <button class="print-combats-button" type="button" data-print-combats>Imprimer la feuille combats</button>
                @endif

                @if ($competition->poules->isNotEmpty())
                    @foreach ($competition->poules as $poule)
                        @php
                            $pouleCombats = $poule->combats->sortBy('ordre_combat')->values();
                            $pouleCombatsToEnter = $pouleCombats->filter(fn ($combat) => $combat->resultat === null)->count();
                            $pouleCombatsComplete = $pouleCombats->isNotEmpty() && $pouleCombatsToEnter === 0;
                            $pouleCombatsToEnterLabel = $pouleCombatsToEnter.' '.($pouleCombatsToEnter > 1 ? 'combats' : 'combat').' à saisir';
                            $isFrozenPoule = $poule->status === \App\Models\Poule::STATUS_FROZEN;
                        @endphp
                        <div class="subsection">
			                            <div class="poule-title-row">
		                                    <h3>{{ $poule->name }}</h3>
                                            @if ($isFrozenPoule)
                                                <span @class(['poule-progress-badge', 'complete' => $pouleCombatsComplete])>
                                                    {{ $pouleCombatsComplete ? '✅ Terminée' : '⚠️ En cours — '.$pouleCombatsToEnterLabel }}
                                                </span>
                                                <a class="poule-action-button" href="{{ route('competitions.poules.print', [$competition, $poule]) }}" target="_blank" rel="noopener">Imprimer résultat poule</a>
                                            @endif
		                                </div>
		
                                    @if (! $isFrozenPoule)
                                        <p class="empty-state">Aucun combat généré, poule non figée</p>
		                            @elseif ($pouleCombats->isNotEmpty())
	                                    <div class="combat-list">
	                                    @foreach ($pouleCombats as $combatIndex => $combat)
                                            @php
                                                $leftState = '';
                                                $rightState = '';

                                                if ($combat->resultat === \App\Models\Combat::RESULT_LEFT_WIN) {
                                                    $leftState = 'winner';
                                                    $rightState = 'muted';
                                                } elseif ($combat->resultat === \App\Models\Combat::RESULT_RIGHT_WIN) {
                                                    $leftState = 'muted';
                                                    $rightState = 'winner';
                                                } elseif ($combat->resultat === \App\Models\Combat::RESULT_NO_CONTEST) {
                                                    $leftState = 'muted';
                                                    $rightState = 'muted';
                                                }
                                            @endphp
                                            <form id="combat-{{ $combat->id }}" @class(['combat-row', 'is-finished' => $combat->statut === \App\Models\Combat::STATUS_FINISHED]) method="POST" action="{{ route('competitions.combats.update', [$competition, $combat]) }}" data-combat-row>
                                                @csrf
                                                @method('PATCH')

                                                <span class="combat-number">
                                                    #{{ $combatIndex + 1 }}
                                                    <span class="combat-status">
                                                        {{ $combat->statut === \App\Models\Combat::STATUS_FINISHED ? 'Terminé' : 'À saisir' }}
                                                    </span>
                                                </span>
                                                <button @class(['combat-fighter-button', 'selected' => $combat->resultat === \App\Models\Combat::RESULT_LEFT_WIN, $leftState]) type="button" data-result-button data-result-value="{{ \App\Models\Combat::RESULT_LEFT_WIN }}" @disabled($combat->statut === \App\Models\Combat::STATUS_FINISHED)>
                                                    🟥
                                                    <span class="combat-fighter-name">{{ $combat->inscriptionA->participantSource->last_name }} {{ $combat->inscriptionA->participantSource->first_name }}</span>
                                                    <span class="combat-fighter-club">({{ $combat->inscriptionA->club->name }})</span>
                                                </button>
                                                <strong class="combat-vs">vs</strong>
                                                <button @class(['combat-fighter-button', 'selected' => $combat->resultat === \App\Models\Combat::RESULT_RIGHT_WIN, $rightState]) type="button" data-result-button data-result-value="{{ \App\Models\Combat::RESULT_RIGHT_WIN }}" @disabled($combat->statut === \App\Models\Combat::STATUS_FINISHED)>
                                                    🟦
                                                    <span class="combat-fighter-name">{{ $combat->inscriptionB->participantSource->last_name }} {{ $combat->inscriptionB->participantSource->first_name }}</span>
                                                    <span class="combat-fighter-club">({{ $combat->inscriptionB->club->name }})</span>
                                                </button>
                                                <button @class(['combat-choice-button', 'combat-result-draw', 'selected' => $combat->resultat === \App\Models\Combat::RESULT_DRAW]) type="button" data-result-button data-result-value="{{ \App\Models\Combat::RESULT_DRAW }}" title="Nul" @disabled($combat->statut === \App\Models\Combat::STATUS_FINISHED)>🤝</button>
                                                <button @class(['combat-choice-button', 'combat-result-none', 'selected' => $combat->resultat === \App\Models\Combat::RESULT_NO_CONTEST]) type="button" data-result-button data-result-value="{{ \App\Models\Combat::RESULT_NO_CONTEST }}" title="Pas de combat" @disabled($combat->statut === \App\Models\Combat::STATUS_FINISHED)>🚫</button>
                                                <input type="hidden" name="resultat" value="{{ old('resultat', $combat->resultat) }}" data-result-input>
                                                <input class="combat-score-red" id="score_a_{{ $combat->id }}" name="score_a" type="number" min="0" inputmode="numeric" placeholder="Rouge" value="{{ old('score_a', $combat->score_a) }}" data-combat-control @disabled($combat->statut === \App\Models\Combat::STATUS_FINISHED)>
                                                <input class="combat-score-blue" id="score_b_{{ $combat->id }}" name="score_b" type="number" min="0" inputmode="numeric" placeholder="Bleu" value="{{ old('score_b', $combat->score_b) }}" data-combat-control @disabled($combat->statut === \App\Models\Combat::STATUS_FINISHED)>
                                                <input class="combat-comment" id="commentaire_{{ $combat->id }}" name="commentaire" type="text" placeholder="Commentaire" value="{{ old('commentaire', $combat->commentaire) }}" data-combat-control @disabled($combat->statut === \App\Models\Combat::STATUS_FINISHED)>

	                                                <div class="combat-actions">
	                                                    <button type="submit" title="Valider" aria-label="Valider" data-combat-validate @disabled($combat->statut === \App\Models\Combat::STATUS_FINISHED)>✔</button>
	                                                    <button type="button" title="Modifier" aria-label="Modifier" data-combat-edit @disabled($combat->statut !== \App\Models\Combat::STATUS_FINISHED)>✏️</button>
	                                                    <button class="combat-clear-button" type="submit" name="action" value="clear" formnovalidate title="Effacer" aria-label="Effacer" data-combat-clear @disabled($combat->statut !== \App\Models\Combat::STATUS_FINISHED)>🗑️</button>
                                                        <button type="button" title="Annuler" aria-label="Annuler" data-combat-cancel disabled>✖</button>
	                                                </div>
                                            </form>
	                                    @endforeach
                                    </div>
	                            @else
	                                <p class="empty-state">Aucun combat généré</p>
	                            @endif
                        </div>
                    @endforeach
                @else
                    <p class="empty-state">Aucune poule créée</p>
                    <button class="poule-action-button" type="button" data-tab-link-target="poules">Aller aux poules</button>
                @endif
            </section>

            @if ($frozenPoules->isNotEmpty())
                <section class="tab-panel" data-tab-panel="combats">
                    <h2>Classement</h2>

                    @if ($finishedCombats->isEmpty())
                        <p class="empty-state">Aucun score saisi</p>
                    @endif

                    @if ($frozenPoules->where(fn ($poule) => $poule->registrations->isNotEmpty())->isNotEmpty())
                        @foreach ($frozenPoules as $poule)
                            @if ($poule->registrations->isNotEmpty())
                            @php
                                $pouleCombats = $poule->combats->sortBy('ordre_combat')->values();
                                $pouleCombatsToEnter = $pouleCombats->filter(fn ($combat) => $combat->resultat === null)->count();
                                $pouleCombatsComplete = $pouleCombats->isNotEmpty() && $pouleCombatsToEnter === 0;
                                $pouleCombatsToEnterLabel = $pouleCombatsToEnter.' '.($pouleCombatsToEnter > 1 ? 'combats' : 'combat').' à saisir';
                            @endphp
                            <div class="subsection">
                                <div class="poule-title-row">
                                    <h3>{{ $poule->name }}</h3>
                                    <span @class(['poule-progress-badge', 'complete' => $pouleCombatsComplete])>
                                        {{ $pouleCombatsComplete ? '✅ Terminée' : '⚠️ En cours — '.$pouleCombatsToEnterLabel }}
                                    </span>
                                </div>
                                <table class="participant-table">
                                    <thead>
                                        <tr>
                                            <th>Rang</th>
                                            <th>Participant</th>
                                            <th>Club</th>
                                            <th>J</th>
                                            <th>V</th>
                                            <th>N</th>
                                            <th>D</th>
                                            <th>NF</th>
                                            <th>Points</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($poule->ranking() as $rankingRow)
                                            <tr>
                                                <td>{{ $rankingRow['rank'] }}</td>
                                                <td>
                                                    {{ $rankingRow['registration']->participantSource->last_name }}
                                                    {{ $rankingRow['registration']->participantSource->first_name }}
                                                </td>
                                                <td>{{ $rankingRow['registration']->club->name }}</td>
                                                <td>{{ $rankingRow['played'] }}</td>
                                                <td>{{ $rankingRow['wins'] }}</td>
                                                <td>{{ $rankingRow['draws'] }}</td>
                                                <td>{{ $rankingRow['losses'] }}</td>
                                                <td>{{ $rankingRow['no_contests'] }}</td>
                                                <td>{{ $rankingRow['points'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <p class="tab-hint">J = joués · V = victoires · N = nuls · D = défaites · NF = non faits</p>
                            </div>
                            @endif
                        @endforeach
                    @else
                        <p class="empty-state">Aucune poule figée pour le moment.</p>
                    @endif
                </section>
            @endif
        @endif

        <section class="print-sheet" aria-label="Feuille combats imprimable">
            <h1>Feuille combats - {{ $competition->name }}</h1>
            <p>Organisateur : {{ $competition->organizerClub->name }}</p>

            @if ($frozenPoules->isNotEmpty())
                @foreach ($frozenPoules as $poule)
                    @php
                        $pouleCombats = $poule->combats->sortBy('ordre_combat')->values();
                    @endphp
                    <h2>{{ $poule->name }}</h2>

                    @if ($pouleCombats->isNotEmpty())
                        <table class="print-fight-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Poule</th>
                                    <th>Rouge</th>
                                    <th>vs</th>
                                    <th>Bleu</th>
                                    <th>Nul</th>
                                    <th>Non fait</th>
                                    <th>Score rouge</th>
                                    <th>Score bleu</th>
                                    <th>Commentaire</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pouleCombats as $combatIndex => $combat)
                                    @php
                                        $redChecked = $combat->resultat === \App\Models\Combat::RESULT_LEFT_WIN ? '[X]' : '[ ]';
                                        $blueChecked = $combat->resultat === \App\Models\Combat::RESULT_RIGHT_WIN ? '[X]' : '[ ]';
                                        $drawChecked = $combat->resultat === \App\Models\Combat::RESULT_DRAW ? '[X]' : '[ ]';
                                        $noContestChecked = $combat->resultat === \App\Models\Combat::RESULT_NO_CONTEST ? '[X]' : '[ ]';
                                    @endphp
                                    <tr>
                                        <td>{{ $combatIndex + 1 }}</td>
                                        <td>{{ $poule->name }}</td>
                                        <td>
                                            {{ $redChecked }}
                                            @if ($combat->resultat === \App\Models\Combat::RESULT_LEFT_WIN)<strong>@endif
                                                {{ $combat->inscriptionA->participantSource->last_name }}
                                                {{ $combat->inscriptionA->participantSource->first_name }}
                                            @if ($combat->resultat === \App\Models\Combat::RESULT_LEFT_WIN)</strong>@endif
                                        </td>
                                        <td class="print-center">vs</td>
                                        <td>
                                            {{ $blueChecked }}
                                            @if ($combat->resultat === \App\Models\Combat::RESULT_RIGHT_WIN)<strong>@endif
                                                {{ $combat->inscriptionB->participantSource->last_name }}
                                                {{ $combat->inscriptionB->participantSource->first_name }}
                                            @if ($combat->resultat === \App\Models\Combat::RESULT_RIGHT_WIN)</strong>@endif
                                        </td>
                                        <td class="print-center">{{ $drawChecked }}</td>
                                        <td class="print-center">{{ $noContestChecked }}</td>
                                        <td>{{ $combat->score_a ?? '____' }}</td>
                                        <td>{{ $combat->score_b ?? '____' }}</td>
                                        <td>{{ $combat->commentaire ?: '____________' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="print-empty">Aucun combat généré</p>
                    @endif
                @endforeach
            @else
                <p class="print-empty">Aucun combat généré</p>
            @endif
        </section>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toast = document.querySelector('[data-toast]');
            const availableList = document.querySelector('[data-available-list]');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            let draggedCard = null;
            const scrollStorageKey = 'competition-show-scroll-y';
            const tabStorageKey = 'competition-show-active-tab';
            const isTouchDevice = ('ontouchstart' in window) || navigator.maxTouchPoints > 0;
            const tabButtons = Array.from(document.querySelectorAll('[data-tab-target]'));
            const tabPanels = Array.from(document.querySelectorAll('[data-tab-panel]'));
            let autoScrollDirection = 0;
            let autoScrollFrame = null;
            let toastTimeout = null;

            const competitionNameDisplay = document.querySelector('[data-competition-name-display]');
            const competitionNameForm = document.querySelector('[data-competition-name-form]');
            const competitionNameEdit = document.querySelector('[data-competition-name-edit]');
            const competitionNameCancel = document.querySelector('[data-competition-name-cancel]');
            const additionalInfoDisplay = document.querySelector('[data-additional-info-display]');
            const additionalInfoForm = document.querySelector('[data-additional-info-form]');
            const additionalInfoEdit = document.querySelector('[data-additional-info-edit]');
            const additionalInfoCancel = document.querySelector('[data-additional-info-cancel]');

            if (competitionNameDisplay && competitionNameForm && competitionNameEdit) {
                competitionNameEdit.addEventListener('click', () => {
                    competitionNameDisplay.style.display = 'none';
                    competitionNameForm.classList.add('is-open');
                    competitionNameForm.querySelector('input')?.focus();
                });
            }

            if (competitionNameDisplay && competitionNameForm && competitionNameCancel) {
                competitionNameCancel.addEventListener('click', () => {
                    competitionNameDisplay.style.display = '';
                    competitionNameForm.classList.remove('is-open');
                });
            }

            if (additionalInfoDisplay && additionalInfoForm && additionalInfoEdit) {
                additionalInfoEdit.addEventListener('click', () => {
                    additionalInfoDisplay.style.display = 'none';
                    additionalInfoForm.classList.add('is-open');
                    additionalInfoForm.querySelector('textarea')?.focus();
                });
            }

            if (additionalInfoDisplay && additionalInfoForm && additionalInfoCancel) {
                additionalInfoCancel.addEventListener('click', () => {
                    additionalInfoDisplay.style.display = '';
                    additionalInfoForm.classList.remove('is-open');
                });
            }

            const showToast = (message) => {
                let currentToast = document.querySelector('[data-toast]');

                if (! currentToast) {
                    currentToast = document.createElement('div');
                    currentToast.className = 'toast';
                    currentToast.dataset.toast = '';
                    document.body.appendChild(currentToast);
                }

                currentToast.textContent = message;
                currentToast.classList.remove('is-hidden');

                if (toastTimeout) {
                    window.clearTimeout(toastTimeout);
                }

                toastTimeout = window.setTimeout(() => {
                    currentToast.classList.add('is-hidden');
                }, 3500);
            };

            const activateTab = (tabName) => {
                const hasTab = tabButtons.some((button) => button.dataset.tabTarget === tabName);
                const activeTab = hasTab ? tabName : 'suivi';

                tabButtons.forEach((button) => {
                    const isActive = button.dataset.tabTarget === activeTab;
                    button.classList.toggle('is-active', isActive);
                    button.setAttribute('aria-selected', String(isActive));
                });

                tabPanels.forEach((panel) => {
                    panel.hidden = panel.dataset.tabPanel !== activeTab;
                });

                window.localStorage.setItem(tabStorageKey, activeTab);
            };

            tabButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    activateTab(button.dataset.tabTarget);
                    window.scrollTo(0, 0);
                });
            });

            document.querySelectorAll('[data-tab-link-target]').forEach((link) => {
                link.addEventListener('click', (event) => {
                    event.preventDefault();
                    activateTab(link.dataset.tabLinkTarget);
                    window.scrollTo(0, 0);
                });
            });

            document.querySelector('[data-print-combats]')?.addEventListener('click', () => {
                window.print();
            });

            const hashTabMap = {
                '#actions': 'suivi',
                '#invitation': 'clubs',
                '#participants': 'participants',
                '#poules': 'poules',
                '#combats': 'combats',
            };

            activateTab(hashTabMap[window.location.hash] || window.localStorage.getItem(tabStorageKey) || 'suivi');

            document.querySelectorAll('[data-rename-open]').forEach((button) => {
                button.addEventListener('click', () => {
                    document.querySelectorAll('[data-rename-form]').forEach((openForm) => {
                        openForm.hidden = true;
                        openForm.closest('.poule-header-main')?.querySelector('[data-rename-display]')?.removeAttribute('hidden');
                    });

                    const header = button.closest('.poule-header-main');
                    const display = header?.querySelector('[data-rename-display]');
                    const form = header?.querySelector('[data-rename-form]');
                    const input = form?.querySelector('input[name="name"]');

                    if (! display || ! form) {
                        return;
                    }

                    display.hidden = true;
                    form.hidden = false;
                    input?.focus();
                    input?.select();
                });
            });

            document.querySelectorAll('[data-rename-cancel]').forEach((button) => {
                button.addEventListener('click', () => {
                    const header = button.closest('.poule-header-main');
                    const display = header?.querySelector('[data-rename-display]');
                    const form = header?.querySelector('[data-rename-form]');

                    if (! display || ! form) {
                        return;
                    }

                    form.hidden = true;
                    display.hidden = false;
                });
            });

            if (isTouchDevice) {
                document.querySelectorAll('[data-touch-help]').forEach((help) => {
                    help.classList.add('is-visible');
                });
            }

            const storedScrollY = window.sessionStorage.getItem(scrollStorageKey);

            if (storedScrollY !== null) {
                window.sessionStorage.removeItem(scrollStorageKey);
                window.requestAnimationFrame(() => {
                    window.scrollTo(0, Number(storedScrollY));
                });
            }

            const updateAvailableEmptyState = () => {
                const availableEmptyState = document.querySelector('[data-available-empty]');

                if (! availableList || ! availableEmptyState) {
                    return;
                }

                availableEmptyState.hidden = availableList.querySelectorAll('.participant-card').length > 0;
            };

            const updatePouleCount = (dropZone) => {
                const count = dropZone.querySelectorAll('[data-poule-list] .participant-card').length;
                const countElement = dropZone.querySelector('[data-poule-count]');

                if (countElement) {
                    countElement.textContent = `${count} participant(s)`;
                }
            };

            const updateAllPouleCounts = () => {
                document.querySelectorAll('[data-poule-id]').forEach(updatePouleCount);
            };

            const stopAutoScroll = () => {
                autoScrollDirection = 0;

                if (autoScrollFrame !== null) {
                    window.cancelAnimationFrame(autoScrollFrame);
                    autoScrollFrame = null;
                }
            };

            const runAutoScroll = () => {
                if (! draggedCard || autoScrollDirection === 0) {
                    stopAutoScroll();
                    return;
                }

                window.scrollBy(0, autoScrollDirection * 6);
                autoScrollFrame = window.requestAnimationFrame(runAutoScroll);
            };

            const updateAutoScrollDirection = (event) => {
                if (! draggedCard) {
                    stopAutoScroll();
                    return;
                }

                let nextDirection = 0;
                if (event.clientY > window.innerHeight - 100) {
                    nextDirection = 1;
                } else if (event.clientY < 100) {
                    nextDirection = -1;
                }

                autoScrollDirection = nextDirection;

                if (autoScrollDirection !== 0 && autoScrollFrame === null) {
                    autoScrollFrame = window.requestAnimationFrame(runAutoScroll);
                } else if (autoScrollDirection === 0 && autoScrollFrame !== null) {
                    stopAutoScroll();
                }
            };

            document.querySelectorAll('.participant-card').forEach((card) => {
                card.addEventListener('dragstart', (event) => {
                    if (card.dataset.source !== 'available') {
                        event.preventDefault();
                        return;
                    }

                    draggedCard = card;
                    card.classList.add('is-dragging');
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', card.dataset.inscriptionId);
                });

                card.addEventListener('dragend', () => {
                    card.classList.remove('is-dragging');
                    draggedCard = null;
                    stopAutoScroll();
                    document.querySelectorAll('.poule-drop-zone.is-drag-over').forEach((dropZone) => {
                        dropZone.classList.remove('is-drag-over');
                    });
                });
            });

            document.addEventListener('dragover', updateAutoScrollDirection);

            document.querySelectorAll('.poule-drop-zone').forEach((dropZone) => {
                dropZone.addEventListener('dragover', (event) => {
                    if (! draggedCard || draggedCard.dataset.source !== 'available' || dropZone.dataset.dropEnabled !== 'true') {
                        return;
                    }

                    event.preventDefault();
                    dropZone.classList.add('is-drag-over');
                });

                dropZone.addEventListener('dragleave', (event) => {
                    if (! event.relatedTarget || ! dropZone.contains(event.relatedTarget)) {
                        dropZone.classList.remove('is-drag-over');
                    }
                });

                dropZone.addEventListener('drop', (event) => {
                    if (! draggedCard || draggedCard.dataset.source !== 'available' || dropZone.dataset.dropEnabled !== 'true') {
                        return;
                    }

                    event.preventDefault();
                    stopAutoScroll();

                    const assignUrl = dropZone.dataset.assignUrl;

                    if (! assignUrl || ! csrfToken) {
                        return;
                    }

                    const formData = new FormData();
                    formData.append('_token', csrfToken);
                    formData.append('registration_id', draggedCard.dataset.inscriptionId);

                    dropZone.classList.remove('is-drag-over');

                    fetch(assignUrl, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'text/html',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    })
                        .then((response) => {
                            if (! response.ok) {
                                throw new Error('Affectation impossible.');
                            }

                            window.sessionStorage.setItem(scrollStorageKey, String(window.scrollY));
                            window.location.href = response.url || window.location.href;
                        })
                        .catch(() => {
                            window.alert('Affectation impossible.');
                    });
                });
            });

            document.addEventListener('click', (event) => {
                const button = event.target.closest('[data-remove-visual]');

                if (! button || ! availableList) {
                    return;
                }

                const card = button.closest('.participant-card');

                if (! card) {
                    return;
                }

                const withdrawUrl = card.dataset.withdrawUrl;

                if (! withdrawUrl || ! csrfToken) {
                    return;
                }

                const formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('_method', 'PATCH');

                fetch(withdrawUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'text/html',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                })
                    .then((response) => {
                        if (! response.ok) {
                            throw new Error('Retrait impossible.');
                        }

                        window.sessionStorage.setItem(scrollStorageKey, String(window.scrollY));
                        window.location.href = response.url || window.location.href;
                    })
                    .catch(() => {
                        window.alert('Retrait impossible.');
                    });
            });

	            const setCombatResult = (row, result, markAsModified = true) => {
	                const input = row.querySelector('[data-result-input]');

                if (! input) {
                    return;
                }

                input.value = result;

                row.querySelectorAll('[data-result-button]').forEach((button) => {
                    button.classList.toggle('selected', button.dataset.resultValue === result);
                    button.classList.remove('winner', 'muted');
                });

                const leftButton = row.querySelector('[data-result-value="{{ \App\Models\Combat::RESULT_LEFT_WIN }}"]');
                const rightButton = row.querySelector('[data-result-value="{{ \App\Models\Combat::RESULT_RIGHT_WIN }}"]');

                if (result === '{{ \App\Models\Combat::RESULT_LEFT_WIN }}') {
                    leftButton?.classList.add('winner');
                    rightButton?.classList.add('muted');
                } else if (result === '{{ \App\Models\Combat::RESULT_RIGHT_WIN }}') {
                    leftButton?.classList.add('muted');
                    rightButton?.classList.add('winner');
	                } else if (result === '{{ \App\Models\Combat::RESULT_NO_CONTEST }}') {
	                    leftButton?.classList.add('muted');
	                    rightButton?.classList.add('muted');
		                }

	                if (markAsModified) {
	                    setCombatEditing(row, true);
	                }
		            };

	            const getCombatState = (row) => ({
	                result: row.querySelector('[data-result-input]')?.value || '',
	                scoreA: row.querySelector('[name="score_a"]')?.value || '',
	                scoreB: row.querySelector('[name="score_b"]')?.value || '',
	                comment: row.querySelector('[name="commentaire"]')?.value || '',
	                isFinished: row.classList.contains('is-finished'),
	            });

	            const storeCombatState = (row) => {
	                row.dataset.savedState = JSON.stringify(getCombatState(row));
	            };

	            const setCombatEditing = (row, isEditing) => {
	                row.classList.toggle('is-editing', isEditing);

	                const validateButton = row.querySelector('[data-combat-validate]');
	                const editButton = row.querySelector('[data-combat-edit]');
	                const cancelButton = row.querySelector('[data-combat-cancel]');
	                const status = row.querySelector('.combat-status');

	                if (validateButton) {
	                    validateButton.textContent = '✔';
	                    validateButton.title = isEditing ? 'Enregistrer' : 'Valider';
	                    validateButton.setAttribute('aria-label', isEditing ? 'Enregistrer' : 'Valider');
	                }

	                if (editButton && isEditing) {
	                    editButton.disabled = true;
	                }

	                if (cancelButton) {
	                    cancelButton.disabled = ! isEditing;
	                }

	                if (status && isEditing) {
	                    status.textContent = 'Modifié';
	                }
		            };

	            const restoreCombatState = (row) => {
	                if (! row.dataset.savedState) {
	                    return;
	                }

	                const state = JSON.parse(row.dataset.savedState);
		                row.querySelector('[name="score_a"]').value = state.scoreA;
		                row.querySelector('[name="score_b"]').value = state.scoreB;
		                row.querySelector('[name="commentaire"]').value = state.comment;
		                setCombatResult(row, state.result, false);
		                setCombatFinished(row, state.isFinished);
		            };

	            const setCombatFinished = (row, isFinished) => {
	                row.classList.toggle('is-finished', isFinished);
	                setCombatEditing(row, false);

	                row.querySelectorAll('[data-combat-control], [data-result-button]').forEach((control) => {
	                    control.disabled = isFinished;
                });

	                const validateButton = row.querySelector('[data-combat-validate]');
	                const editButton = row.querySelector('[data-combat-edit]');
	                const clearButton = row.querySelector('[data-combat-clear]');
	                const cancelButton = row.querySelector('[data-combat-cancel]');
	                const status = row.querySelector('.combat-status');

                if (validateButton) {
                    validateButton.disabled = isFinished;
                }

                if (editButton) {
                    editButton.disabled = ! isFinished;
                }

	                if (clearButton) {
	                    clearButton.disabled = ! isFinished;
	                }

	                if (cancelButton) {
	                    cancelButton.disabled = true;
	                }

                if (status) {
                    status.textContent = isFinished ? 'Terminé' : 'À saisir';
                }
            };

            const resetCombatRow = (row) => {
                row.querySelector('[data-result-input]').value = '';

                row.querySelectorAll('[data-result-button]').forEach((button) => {
                    button.classList.remove('selected', 'winner', 'muted');
                });

                row.querySelectorAll('[data-combat-control]').forEach((control) => {
                    control.value = '';
                });

	                setCombatFinished(row, false);
	                storeCombatState(row);
	            };

            document.querySelectorAll('[data-result-button]').forEach((button) => {
                button.addEventListener('click', () => {
                    if (button.disabled) {
                        return;
                    }

                    const row = button.closest('[data-combat-row]');

                    if (! row) {
                        return;
                    }

	                    setCombatResult(row, button.dataset.resultValue);
	                });
	            });

	            document.querySelectorAll('[data-combat-control]').forEach((control) => {
	                control.addEventListener('input', () => {
	                    const row = control.closest('[data-combat-row]');

	                    if (! row || control.disabled) {
	                        return;
	                    }

	                    setCombatEditing(row, true);
	                });
	            });

	            document.querySelectorAll('[data-combat-edit]').forEach((button) => {
	                button.addEventListener('click', () => {
	                    const row = button.closest('[data-combat-row]');

                    if (! row) {
                        return;
                    }

                    row.querySelectorAll('[data-combat-control], [data-result-button]').forEach((control) => {
                        control.disabled = false;
                    });

	                    row.querySelector('[data-combat-validate]').disabled = false;
	                    row.querySelector('[data-combat-clear]').disabled = false;
	                    setCombatEditing(row, true);
	                });
	            });

	            document.querySelectorAll('[data-combat-cancel]').forEach((button) => {
	                button.addEventListener('click', () => {
	                    const row = button.closest('[data-combat-row]');

	                    if (! row) {
	                        return;
	                    }

	                    restoreCombatState(row);
	                });
	            });

	            document.querySelectorAll('[data-combat-row]').forEach((row) => {
	                storeCombatState(row);

	                row.addEventListener('submit', (event) => {
                    event.preventDefault();

                    const submitter = event.submitter;

                    if (! submitter || submitter.disabled) {
                        return;
                    }

                    const isClearAction = submitter.matches('[data-combat-clear]');

                    if (! isClearAction && ! row.querySelector('[data-result-input]').value) {
                        showToast('Sélectionnez un résultat.');
                        return;
                    }

                    const formData = new FormData(row);
                    formData.set('_method', 'PATCH');

                    if (csrfToken) {
                        formData.set('_token', csrfToken);
                    }

                    if (isClearAction) {
                        formData.set('action', 'clear');
                    }

                    fetch(row.getAttribute('action'), {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    })
                        .then((response) => {
                            if (! response.ok) {
                                return response.json()
                                    .catch(() => ({ message: `Enregistrement impossible (${response.status}).` }))
                                    .then((data) => {
                                        throw new Error(data.message || `Enregistrement impossible (${response.status}).`);
                                    });
                            }

                            return response.json();
                        })
                        .then((data) => {
	                            if (isClearAction) {
	                                resetCombatRow(row);
	                                storeCombatState(row);
	                                showToast(data.message || 'Résultat du combat effacé.');
	                                return;
	                            }

	                            setCombatFinished(row, true);
	                            storeCombatState(row);
	                            showToast(data.message || 'Résultat du combat enregistré.');
                        })
                        .catch((error) => {
                            showToast(error.message || 'Enregistrement impossible.');
                        });
                });
            });

            updateAvailableEmptyState();
            updateAllPouleCounts();

            if (toast) {
                toastTimeout = window.setTimeout(() => {
                    toast.classList.add('is-hidden');
                }, 3500);
            }
        });
    </script>
@endsection
