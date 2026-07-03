@extends('layouts.app')

@section('content')
    <section class="nc-page">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Erreur {{ $status }}</p>
                <h1 class="nc-title">{{ $heading }}</h1>
                <p class="nc-lead">{{ $message }}</p>
            </div>
        </div>

        <div class="nc-panel">
            <h2>{{ $summary }}</h2>
            <p>{{ $detail }}</p>
            <div class="nc-actions">
                @auth
                    <a class="nc-button" href="{{ route('dashboard') }}">Retour au dashboard</a>
                @else
                    <a class="nc-button" href="{{ route('login') }}">Se connecter</a>
                @endauth
            </div>
        </div>
    </section>
@endsection
