@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Détail de la demande') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-2xl font-bold mb-2">Demande de congé</h3>
                            <p class="text-gray-500">Du {{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('d/m/Y') }}</p>
                            <p class="text-sm font-medium mt-1">{{ $leaveRequest->requested_days }} jours demandés</p>
                        </div>
                        <div>
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                @if($leaveRequest->status == 'approved') bg-green-100 text-green-800 
                                @elseif($leaveRequest->status == 'rejected') bg-red-100 text-red-800 
                                @elseif($leaveRequest->status == 'submitted') bg-yellow-100 text-yellow-800 
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($leaveRequest->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h4 class="text-lg font-semibold mb-4">Informations supplémentaires</h4>
                    
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Date de soumission</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($leaveRequest->submitted_at)->format('d/m/Y H:i') }}</dd>
                        </div>
                        
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Type de congé</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $leaveRequest->leaveType->name }}</dd>
                        </div>

                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Commentaire du demandeur</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $leaveRequest->requester_comment ?: 'Aucun commentaire' }}</dd>
                        </div>

                        @if($leaveRequest->status !== 'submitted')
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Revu le</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $leaveRequest->reviewed_at ? \Carbon\Carbon::parse($leaveRequest->reviewed_at)->format('d/m/Y H:i') : '-' }}</dd>
                            </div>

                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Commentaire du validateur</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $leaveRequest->reviewer_comment ?: 'Aucun commentaire' }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="mt-6 flex justify-start">
                <a href="{{ route('leaves.index') }}" class="text-indigo-600 hover:text-indigo-900">&larr; Retour au tableau de bord</a>
            </div>
        </div>
    </div>
@endsection
