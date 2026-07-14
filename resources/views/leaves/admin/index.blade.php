@extends('layouts.app', ['title' => 'Pilotage RH des absences - Néré Tools'])

@section('content')
<x-admin.layout>
<main class="nc-page leave-admin-main">
    <header class="nc-title-row"><div><p class="nc-kicker">Ressources humaines</p><h1 class="nc-title">Pilotage des absences</h1><p class="nc-lead">Une vue consolidée pour anticiper, décider et maintenir des règles fiables.</p></div></header>
    @include('leaves.partials.flash')
    @include('leaves.admin.partials.overview')
    @include('leaves.admin.partials.requests')
    @include('leaves.admin.partials.people')
    @include('leaves.admin.partials.rules')
</main>
</x-admin.layout>
@endsection
