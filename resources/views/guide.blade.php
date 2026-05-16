@extends('layouts.app')

@section('title', 'Aide - Comment ça marche')
@section('page-title', 'Aide - Comment ça marche')

@push('styles')
<style>
        body {
            margin: 0;
            background: #f6f7f9;
            color: #17202a;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        main {
            width: min(780px, calc(100% - 32px));
            margin: 48px auto;
        }

        .guide {
            padding: 32px;
            border: 1px solid #dce1e7;
            border-radius: 8px;
            background: #ffffff;
        }

        .guide-video {
            margin-bottom: 24px;
        }

        .guide-video iframe {
            display: block;
            width: 100%;
            height: 400px;
            border: 0;
            border-radius: 8px;
        }

        .guide-links {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .guide-links a {
            display: inline-flex;
            align-items: center;
            min-height: 34px;
            padding: 6px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background: #f8fafc;
            font-weight: 700;
            text-decoration: none;
        }

        .status-message {
            margin-bottom: 18px;
            padding: 10px 12px;
            border: 1px solid #86efac;
            border-radius: 8px;
            background: #f0fdf4;
            color: #166534;
            font-weight: 700;
        }

        a {
            color: #1d4ed8;
        }

        h1 {
            margin: 28px 0 16px;
            font-size: 28px;
            line-height: 1.2;
        }

        h1:first-of-type {
            margin-top: 0;
        }

        h2 {
            margin: 22px 0 12px;
            font-size: 20px;
        }

        p {
            margin: 8px 0;
            color: #334155;
            line-height: 1.6;
        }

        ul {
            margin: 8px 0 18px;
            padding-left: 24px;
            color: #334155;
            line-height: 1.6;
        }

        hr {
            margin: 28px 0;
            border: 0;
            border-top: 1px solid #e5eaf0;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 24px;
            font-weight: 700;
        }

        .step {
            font-weight: 700;
            color: #17202a;
        }
</style>
@endpush

@section('content')
<main>
<div class="guide">

    @if (session('status'))
        <div class="status-message">{{ session('status') }}</div>
    @endif

    @if (filled($helpVideoIframe))
        <div class="guide-video">
            {!! $helpVideoIframe !!}
        </div>
    @endif

    @if ($showGuideExtras ?? true)
        {{-- IMAGE --}}
        <div style="margin-bottom: 24px;">
            <img src="/images/guide-preview.png" style="width:100%; border-radius:8px;">
        </div>

        <div class="guide-links">
            <a class="btn btn-export" href="/docs/guide-utilisateur.pdf" target="_blank">
                📄 Télécharger le guide complet (PDF)
            </a>
            <a class="btn btn-secondary" href="{{ route('guide.jeu-test-demo') }}">
                🧪 Jeu de test démo
            </a>
            <a class="btn btn-danger" href="{{ route('demo.reset') }}">
                ♻️ Réinitialiser cette démo
            </a>
        </div>
    @else
        <p class="back-link">
            <a href="{{ route('guide') }}">← Retour à l’aide</a>
        </p>
    @endif

    {{-- MARKDOWN --}}
    {!! Str::markdown($guide) !!}

</div>
</main>
@endsection
