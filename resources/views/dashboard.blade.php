@extends('layouts.app', [
    'title' => 'Nere Tools',
    'breadcrumbs' => [
        ['label' => 'Accueil'],
    ],
])

@section('content')
    @php
        $toolIcons = [
            'timesheets' => 'file-text',
            'conges' => 'calendar-range',
        ];
    @endphp

    <section class="nc-page">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Portail interne</p>
                <h1 class="nc-title">Nere Tools</h1>
                <p class="nc-lead">Bonjour {{ auth()->user()->name }}. Accédez directement à votre espace de travail.</p>
            </div>
        </div>

        <div class="nc-grid tools">
            @if (auth()->user()->canAccessAdmin())
                <article class="nc-card nc-card-feature">
                    <div class="nc-card-top">
                        <div class="nc-card-identity">
                            <span class="nc-card-symbol" aria-hidden="true">
                                <i data-lucide="settings" class="nc-icon"></i>
                            </span>
                            <div>
                                <h2>Administration</h2>
                                <p>Gérez les accès, les rôles et les réglages métier depuis un espace de pilotage unique.</p>
                            </div>
                        </div>
                        <span class="nc-badge active">
                            <i data-lucide="shield-check" class="nc-icon" aria-hidden="true"></i>
                            Admin
                        </span>
                    </div>

                    <div class="nc-actions">
                        <a href="{{ route('admin.index') }}" class="nc-button">
                            <i data-lucide="arrow-right" class="nc-icon" aria-hidden="true"></i>
                            Ouvrir l'administration
                        </a>
                    </div>
                </article>
            @endif

            @foreach ($tools as $tool)
                @php
                    $isActive = $tool->status === \App\Models\Tool::STATUS_ACTIVE;
                    $hasAccess = auth()->user()->canAccessTool($tool->slug);
                    $toolIcon = $toolIcons[$tool->slug] ?? 'layout-grid';
                @endphp

                <article class="nc-card">
                    <div class="nc-card-top">
                        <div class="nc-card-identity">
                            <span class="nc-card-symbol" aria-hidden="true">
                                <i data-lucide="{{ $toolIcon }}" class="nc-icon"></i>
                            </span>
                            <div>
                                <h2>{{ $tool->name }}</h2>
                                <p>{{ $tool->description }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="nc-actions">
                        @if ($isActive && $hasAccess)
                            <a href="{{ $tool->route }}" class="nc-button">
                                <i data-lucide="arrow-right" class="nc-icon" aria-hidden="true"></i>
                                Ouvrir l'outil
                            </a>
                        @elseif ($isActive)
                            <span class="nc-ghost" aria-disabled="true">
                                <i data-lucide="circle-slash" class="nc-icon" aria-hidden="true"></i>
                                Accès non autorisé
                            </span>
                        @else
                            <span class="nc-ghost" aria-disabled="true">
                                <i data-lucide="clock-3" class="nc-icon" aria-hidden="true"></i>
                                Bientôt disponible
                            </span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endsection
