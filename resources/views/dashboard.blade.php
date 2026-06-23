@extends('layouts.app', [
    'title' => 'Nere Tools',
    'breadcrumbs' => [
        ['label' => 'Accueil'],
    ],
])

@section('content')
    <section class="nc-page">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Portail interne</p>
                <h1 class="nc-title">Nere Tools</h1>
                <p class="nc-lead">Bienvenue, {{ auth()->user()->name }}. Choisissez l'outil dont vous avez besoin pour commencer.</p>
            </div>
        </div>

        <div class="nc-grid tools">
            @foreach ($tools as $tool)
                @php
                    $isActive = $tool->status === \App\Models\Tool::STATUS_ACTIVE;
                    $hasAccess = auth()->user()->canAccessTool($tool->slug);
                @endphp

                <article class="nc-card">
                    <div class="nc-card-top">
                        <div>
                            <h2>{{ $tool->name }}</h2>
                            <p>{{ $tool->description }}</p>
                        </div>
                    </div>

                    <div class="nc-actions">
                        @if ($isActive && $hasAccess)
                            <a href="{{ $tool->route }}" class="nc-button">Ouvrir l'outil</a>
                        @elseif ($isActive)
                            <span class="nc-ghost" aria-disabled="true">Accès non autorisé</span>
                        @else
                            <span class="nc-ghost" aria-disabled="true">Bientôt disponible</span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endsection
