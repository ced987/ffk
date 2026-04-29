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
        @if (filled($helpVideoIframe))
            <div class="guide-video">
                {!! $helpVideoIframe !!}
            </div>
        @endif

        {!! Str::markdown($guide) !!}
    </div>
</main>
@endsection
