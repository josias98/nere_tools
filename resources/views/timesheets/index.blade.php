@extends('layouts.app', [
    'title' => 'Feuilles de temps - Néré Tools',
    'breadcrumbs' => [['label' => 'Accueil', 'url' => route('dashboard')], ['label' => 'Feuilles de temps']],
])

@section('content')
    <livewire:timesheets.wizard />
@endsection
