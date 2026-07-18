@php
    $sectionMeta = [
        'overview' => ['title' => 'Pilotage des absences', 'lead' => 'Les indicateurs utiles pour décider où intervenir.'],
        'requests' => ['title' => 'Demandes de congé', 'lead' => 'Filtrer, contrôler et ouvrir les demandes enregistrées.'],
        'people' => ['title' => 'Collaborateurs et soldes', 'lead' => 'Suivre les droits disponibles et maintenir les profils RH.'],
        'workflow' => ['title' => 'Circuit de validation', 'lead' => 'Définir qui intervient à chaque étape et sur quel périmètre.'],
        'settings' => ['title' => 'Paramètres de calcul', 'lead' => 'Régler l’acquisition des droits et les documents générés.'],
        'rules' => ['title' => 'Types de congé', 'lead' => 'Faire évoluer le catalogue sans modifier les demandes déjà enregistrées.'],
        'calendar' => ['title' => 'Jours fériés', 'lead' => 'Maintenir les dates exclues des calculs en jours ouvrables.'],
        'data' => ['title' => 'Données du module', 'lead' => 'Importer les soldes initiaux et exporter la situation RH.'],
    ][$section];
@endphp

@extends('layouts.app', ['title' => $sectionMeta['title'].' - Néré Tools'])

@section('content')
<x-admin.layout>
<main class="nc-page leave-admin-main leave-workbench">
    <header class="nc-title-row"><div><p class="nc-kicker">Administration · Congés</p><h1 class="nc-title">{{ $sectionMeta['title'] }}</h1><p class="nc-lead">{{ $sectionMeta['lead'] }}</p></div></header>
    @include('leaves.partials.flash')
    @include('leaves.admin.partials.'.$section)
</main>
</x-admin.layout>
@endsection
