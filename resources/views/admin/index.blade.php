@extends('layouts.app', ['title' => 'Centre d’administration - Néré Tools'])

@section('content')
<x-admin.layout>
        <header class="admin-topbar">
            <div class="admin-title"><p class="nc-kicker">Administration</p><h1>Vue d’ensemble</h1><p>Les décisions importantes, les anomalies et les accès au même endroit.</p></div>
            <label class="admin-search"><span class="sr-only">Rechercher un paramètre</span><i data-lucide="search"></i><input type="search" placeholder="Rechercher un réglage…" data-admin-search></label>
        </header>

        @include('leaves.partials.flash')

        <section aria-labelledby="health-title">
            <div class="admin-section-heading"><div><p class="nc-kicker">Modules</p><h2 id="health-title">Santé de la configuration</h2></div><span class="nc-badge">{{ $activeUsers }} comptes actifs</span></div>
            <div class="admin-health-grid">
                @foreach ($modules as $key => $module)
                    <a class="admin-health-card" href="{{ $module['url'] }}" data-searchable="{{ $module['name'] }}" data-tooltip-title="Configurer {{ $module['name'] }}" data-tooltip="Ouvre le module et ses points de configuration à vérifier.">
                        <span class="nc-card-symbol"><i data-lucide="{{ $module['icon'] }}"></i></span>
                        <span><strong>{{ $module['name'] }}</strong><small>{{ $module['issues']->count() }} point(s) à vérifier</small></span>
                        <x-admin.configuration-health :health="$module['health']" />
                    </a>
                @endforeach
            </div>
        </section>

        <section class="admin-panel" aria-labelledby="issues-title">
            <div class="admin-section-heading"><div><p class="nc-kicker">À traiter</p><h2 id="issues-title">Recommandations prioritaires</h2></div>
                <form class="admin-filters" method="GET"><select name="module" aria-label="Filtrer par module"><option value="">Tous les modules</option><option value="conges" @selected(request('module') === 'conges')>Congés</option><option value="timesheets" @selected(request('module') === 'timesheets')>Feuilles de temps</option></select><select name="status" aria-label="Filtrer par criticité"><option value="">Toutes les criticités</option><option value="error" @selected(request('status') === 'error')>Erreurs</option><option value="warning" @selected(request('status') === 'warning')>Attention</option><option value="info" @selected(request('status') === 'info')>Information</option></select><button class="nc-ghost" data-tooltip-title="Filtrer les recommandations" data-tooltip="Affiche uniquement les alertes correspondant au module et au niveau de criticité choisis.">Filtrer</button></form>
            </div>
            <div class="admin-issue-list">
                @forelse ($issues as $issue)
                    <x-admin.risk-warning :severity="$issue['severity']" :title="$issue['title']" :description="$issue['description']" :url="$issue['url']" />
                @empty
                    <x-ui.empty-state title="Configuration saine" description="Aucune anomalie ne correspond aux filtres sélectionnés." />
                @endforelse
            </div>
        </section>

        <div class="admin-columns">
            <x-admin.recommendation-card title="Valider les circuits avant ouverture" source="Bonne pratique" recommended="Un valideur principal et un suppléant par étape" risk="Une demande peut rester bloquée pendant une absence." example="Le responsable hiérarchique valide, puis RH contrôle le solde." :url="route('admin.leaves.index').'#workflow'" />
            <section class="admin-panel"><p class="nc-kicker">Historique</p><h2>Dernières modifications</h2><ol class="admin-timeline">@forelse($recentChanges as $change)<li><strong>{{ str_replace('.', ' · ', $change->action) }}</strong><small>{{ $change->created_at->diffForHumans() }}</small></li>@empty<li>Aucune modification enregistrée.</li>@endforelse</ol></section>
        </div>
</x-admin.layout>
@endsection
