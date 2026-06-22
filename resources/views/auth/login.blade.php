@extends('layouts.app', ['title' => 'Connexion - Nere Tools'])

@section('content')
    <section class="nc-auth">
        <div class="nc-auth-inner">
            <div class="nc-auth-card">
                <img class="nc-logo" src="{{ asset('brand/nere-capital-rgb.png') }}" alt="Nere Capital">
                <div class="nc-title-row">
                    <div>
                        <p class="nc-kicker">Connexion securisee</p>
                        <h1 class="nc-title">Nere Tools</h1>
                        <p class="nc-lead">Connectez-vous avec votre compte Microsoft 365 professionnel pour acceder au portail interne.</p>
                    </div>
                </div>

                @if ($errors->has('microsoft'))
                    <p class="nc-alert">{{ $errors->first('microsoft') }}</p>
                @endif

                <div class="nc-actions">
                    <a href="{{ route('auth.microsoft.redirect') }}" class="nc-button">Se connecter avec Microsoft 365</a>
                </div>
            </div>
        </div>
    </section>
@endsection
