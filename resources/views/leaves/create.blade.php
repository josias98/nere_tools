@extends('layouts.app', [
    'title' => 'Nouvelle demande - Nere Tools',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Conges', 'url' => route('leaves.index')],
        ['label' => 'Nouvelle demande'],
    ],
])

@section('content')
    <livewire:leaves.request-wizard />
@endsection
