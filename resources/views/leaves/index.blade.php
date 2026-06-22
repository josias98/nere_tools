@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Demandes de congé') }}
        </h2>
        <a href="{{ route('leaves.create') }}" class="nc-btn-primary">Nouvelle demande</a>
    </div>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-nc-brown">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-widest">Solde disponible</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($balance['available_balance'], 2) }} <span class="text-lg font-normal text-gray-500">jours</span></p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-400">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-widest">En attente</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($balance['pending_days'], 2) }} <span class="text-lg font-normal text-gray-500">jours</span></p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-gray-400">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-widest">Solde projeté</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($balance['projected_balance'], 2) }} <span class="text-lg font-normal text-gray-500">jours</span></p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold">Mes dernières demandes</h3>
                        <a href="{{ route('leaves.history') }}" class="text-sm text-nc-brown hover:underline">Voir l'historique complet</a>
                    </div>
                    
                    @if($recentRequests->isEmpty())
                        <p class="text-gray-500">Aucune demande récente.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Période</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jours</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                        <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentRequests as $request)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                Du {{ \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $request->requested_days }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($request->status == 'approved') bg-green-100 text-green-800 
                                                    @elseif($request->status == 'rejected') bg-red-100 text-red-800 
                                                    @elseif($request->status == 'submitted') bg-yellow-100 text-yellow-800 
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('leaves.show', $request->uuid) }}" class="text-indigo-600 hover:text-indigo-900">Détails</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Le décompte des congés est effectué en jours calendaires, incluant les week-ends et jours fériés compris dans la période demandée.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
