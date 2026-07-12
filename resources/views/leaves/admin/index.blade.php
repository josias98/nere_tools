@extends('layouts.app', ['title' => 'Pilotage RH des absences - Néré Tools'])

@section('content')
<div class="leave-admin-layout">
    <aside class="leave-admin-sidebar" aria-label="Administration des absences">
        <strong>Congés & absences</strong>
        <a href="#overview">Vue d’ensemble</a><a href="#requests">Demandes</a><a href="#calendar">Calendrier</a>
        <a href="#people">Personnel et soldes</a><a href="#rules">Types et règles</a><a href="#workflow">Circuit de validation</a>
        <a href="#imports">Imports et exports</a><a href="{{ route('admin.leaves.notifications.index') }}">Audit et notifications</a>
    </aside>
    <main class="nc-page leave-admin-main">
        <header class="nc-title-row"><div><p class="nc-kicker">Ressources humaines</p><h1 class="nc-title">Pilotage des absences</h1><p class="nc-lead">Une vue consolidée pour anticiper, décider et maintenir des règles fiables.</p></div></header>
        @include('leaves.partials.flash')
        @include('leaves.admin.partials.overview')
        @include('leaves.admin.partials.requests')
        @include('leaves.admin.partials.people')
        @include('leaves.admin.partials.rules')
    </main>
</div>
@endsection
