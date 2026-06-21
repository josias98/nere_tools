@extends('layouts.app', ['title' => 'Acces refuse - Nere Tools'])

@section('content')
    <section class="nc-auth">
        <div class="nc-auth-inner">
            <div class="nc-auth-card">
                <img class="nc-logo" src="{{ asset('brand/nere-capital-rgb.png') }}" alt="Nere Capital">
                <span class="nc-badge warning">Acces refuse</span>
                <h1 class="nc-title">Compte non autorise</h1>
                <p class="nc-lead">{{ $reason }}</p>

                @if ($email)
                    <p class="nc-alert">Compte Microsoft detecte : <strong>{{ $email }}</strong></p>
                @endif

                <div class="nc-actions">
                    <a href="{{ route('login') }}" class="nc-button">Retour a la connexion</a>
                </div>
            </div>
        </div>
    </section>
@endsection
