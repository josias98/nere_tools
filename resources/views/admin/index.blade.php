@extends('layouts.app', [
    'title' => 'Administration - Nere Tools',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Administration'],
    ],
])

@section('content')
    <section class="nc-page">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Administration</p>
                <h1 class="nc-title">Reglages du portail</h1>
                <p class="nc-lead">Ici, vous pilotez ce qui s'applique a tout le monde, puis ce qui reste propre a chaque module. L'objectif est simple: savoir qui peut entrer, qui peut faire quoi, et ou regler chaque sujet sans hesitation.</p>
            </div>
        </div>

        <div class="nc-overview-grid">
            @foreach ($userStats as $stat)
                <article class="nc-panel nc-overview-card">
                    <span class="nc-overview-icon" aria-hidden="true">
                        <i data-lucide="{{ $stat['icon'] }}" class="nc-icon"></i>
                    </span>
                    <span class="nc-kicker">{{ $stat['label'] }}</span>
                    <strong class="nc-overview-value">{{ $stat['value'] }}</strong>
                    <p>{{ $stat['helper'] }}</p>
                </article>
            @endforeach
        </div>

        <div class="nc-admin-shell">
            <div class="nc-admin-stack">
                <section class="nc-panel nc-panel-premium">
                    <div class="nc-panel-heading">
                        <div>
                            <p class="nc-kicker">Reglages globaux</p>
                            <h2>Acces, roles et droits</h2>
                            <p>Cet espace sert a autoriser les connexions, attribuer un role lisible et ouvrir les bons modules a la bonne personne.</p>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="nc-button">
                            <i data-lucide="users" class="nc-icon" aria-hidden="true"></i>
                            Gerer les utilisateurs
                        </a>
                    </div>

                    <div class="nc-role-grid">
                        @foreach ($roleCards as $role)
                            <article class="nc-card nc-role-card">
                                <div class="nc-role-head">
                                    <span class="nc-role-icon" aria-hidden="true">
                                        <i data-lucide="{{ $role['icon'] }}" class="nc-icon"></i>
                                    </span>
                                    <div>
                                        <h3>{{ $role['label'] }}</h3>
                                        <span class="nc-role-count">{{ $role['count'] }} compte(s)</span>
                                    </div>
                                </div>
                                <p>{{ $role['summary'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>
            </div>

            <aside class="nc-admin-stack">
                <section class="nc-panel nc-admin-note">
                    <div class="nc-panel-heading">
                        <div>
                            <p class="nc-kicker">Mode d'emploi</p>
                            <h2>Par ou commencer</h2>
                        </div>
                    </div>
                    <ul class="nc-list">
                        <li>Commencez par verifier que la personne est bien autorisee a se connecter avec son adresse Microsoft 365.</li>
                        <li>Choisissez ensuite un role simple a comprendre, puis ouvrez seulement les modules utiles a son travail.</li>
                        <li>Quand un module a sa propre logique, ouvrez ses reglages dedies juste en dessous plutot que de tout melanger ici.</li>
                    </ul>
                </section>

                <section class="nc-panel nc-admin-note">
                    <div class="nc-panel-heading">
                        <div>
                            <p class="nc-kicker">Bon reflexe</p>
                            <h2>Ce qui reste global</h2>
                        </div>
                    </div>
                    <ul class="nc-list">
                        <li>Les roles cadrent le niveau de responsabilite dans le portail.</li>
                        <li>Les acces par module servent a ouvrir ou fermer les espaces de travail.</li>
                        <li>Les parametrages metier restent dans leur module pour eviter les confusions.</li>
                    </ul>
                </section>
            </aside>
        </div>

        <section class="nc-panel">
            <div class="nc-panel-heading">
                <div>
                    <p class="nc-kicker">Reglages par module</p>
                    <h2>Chaque module garde son espace</h2>
                    <p>Vous retrouvez ici les reglages vraiment utiles par domaine. Quand un module n'a pas encore de back-office dedie, on vous le dit clairement au lieu de simuler des options vides.</p>
                </div>
            </div>

            <div class="nc-admin-module-grid">
                @foreach ($moduleCards as $module)
                    <article class="nc-card nc-admin-card">
                        <div class="nc-admin-card-head">
                            <span class="nc-card-symbol" aria-hidden="true">
                                <i data-lucide="{{ $module['icon'] }}" class="nc-icon"></i>
                            </span>
                            <div>
                                <h3>{{ $module['name'] }}</h3>
                                <p>{{ $module['summary'] }}</p>
                            </div>
                        </div>

                        <dl class="nc-admin-facts">
                            @foreach ($module['facts'] as $label => $value)
                                <div>
                                    <dt>{{ $label }}</dt>
                                    <dd>{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>

                        <div class="nc-actions">
                            <a href="{{ $module['action_url'] }}" class="nc-button is-secondary">
                                <i data-lucide="arrow-right" class="nc-icon" aria-hidden="true"></i>
                                {{ $module['action_label'] }}
                            </a>
                            @if ($module['secondary_url'])
                                <a href="{{ $module['secondary_url'] }}" class="nc-ghost">
                                    <i data-lucide="eye" class="nc-icon" aria-hidden="true"></i>
                                    {{ $module['secondary_label'] }}
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    </section>
@endsection
