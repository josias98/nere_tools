@extends('layouts.app', [
    'title' => 'Nere Tools',
    'breadcrumbs' => [
        ['label' => 'Dashboard'],
    ],
])

@section('content')
    <section class="nc-page">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Portail interne</p>
                <h1 class="nc-title">Nere Tools</h1>
                <p class="nc-lead">Bienvenue, {{ auth()->user()->name }}. Selectionnez un outil interne pour demarrer.</p>
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
                        <!-- <span class="nc-badge {{ $isActive ? 'active' : '' }}">{{ $isActive ? 'Actif' : 'Bientot' }}</span> -->
                    </div>

                    <div class="nc-actions">
                        @if ($isActive && $hasAccess)
                            <a href="{{ $tool->route }}" class="nc-button">Accéder</a>
                        @elseif ($isActive)
                            <span class="nc-ghost" aria-disabled="true">Non autorise</span>
                        @else
                            <span class="nc-ghost" aria-disabled="true">Bientot disponible</span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endsection
