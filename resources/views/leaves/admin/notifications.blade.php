@extends('layouts.app', [
    'title' => 'Notifications conges - Nere Tools',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Administration', 'url' => route('admin.index')],
        ['label' => 'Conges', 'url' => route('admin.leaves.index')],
        ['label' => 'Notifications'],
    ],
])

@section('content')
<x-admin.layout>
    <section class="nc-page leave-workbench">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Debug Office 365</p>
                <h1 class="nc-title">Notifications conges</h1>
                <p class="nc-lead">Lecture des statuts, erreurs Graph et relances sures des notifications du module Conges.</p>
            </div>
        </div>

        @include('leaves.partials.flash')

        @error('notification')
            <section class="nc-alert">{{ $message }}</section>
        @enderror

        <div class="leave-metrics leave-metrics--compact">
            <article class="nc-panel leave-metric">
                <span>Queued</span>
                <strong>{{ $counts['queued'] ?? 0 }}</strong>
                <small>en attente</small>
            </article>
            <article class="nc-panel leave-metric">
                <span>Sent</span>
                <strong>{{ $counts['sent'] ?? 0 }}</strong>
                <small>envoyees</small>
            </article>
            <article class="nc-panel leave-metric">
                <span>Failed</span>
                <strong>{{ $counts['failed'] ?? 0 }}</strong>
                <small>en echec</small>
            </article>
            <article class="nc-panel leave-metric">
                <span>Skipped</span>
                <strong>{{ $counts['skipped'] ?? 0 }}</strong>
                <small>ignorees</small>
            </article>
        </div>

        <section class="nc-panel">
            <div class="nc-panel-heading">
                <div>
                    <h2>Filtres</h2>
                    <p>Le debug reste volontairement simple: pas de corps d'email stocke, seulement les metadonnees utiles.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.leaves.notifications.index') }}" class="leave-filter leave-filter--notifications">
                <label class="nc-field">
                    <span>Statut</span>
                    <select name="status">
                        <option value="">Tous</option>
                        @foreach (['queued', 'sent', 'failed', 'skipped'] as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="nc-field">
                    <span>Evenement</span>
                    <select name="event">
                        <option value="">Tous</option>
                        @foreach ($events as $event)
                            <option value="{{ $event }}" @selected($filters['event'] === $event)>{{ $event }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="nc-field">
                    <span>Recherche</span>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Sujet ou erreur">
                </label>
                <button class="nc-button" type="submit" data-tooltip-title="Filtrer le journal" data-tooltip="Recherche les notifications par statut, événement, sujet ou message d’erreur.">
                    <i data-lucide="filter" class="nc-icon" aria-hidden="true"></i>
                    Filtrer
                </button>
            </form>
        </section>

        <section class="nc-panel">
            <div class="nc-panel-heading">
                <div>
                    <h2>Journal</h2>
                    <p>Relancer est permis seulement pour les notifications Conges en echec ou ignorees.</p>
                </div>
            </div>

            <div class="nc-table-wrap">
                <table class="nc-table notification-table">
                    <thead>
                        <tr>
                            <th>Evenement</th>
                            <th>Destinataires</th>
                            <th>Statut</th>
                            <th>Contexte</th>
                            <th>Erreur</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($notifications as $notification)
                            @php($leaveRequest = $notification->related instanceof \App\Models\LeaveRequest ? $notification->related : null)
                            @php($canRetry = in_array($notification->status, ['failed', 'skipped'], true) && $leaveRequest)
                            <tr>
                                <td>
                                    <strong>{{ $notification->event ?? 'system' }}</strong><br>
                                    <span class="nc-muted">{{ $notification->subject }}</span>
                                </td>
                                <td>
                                    {{ implode(', ', $notification->to_recipients ?? []) ?: '-' }}
                                </td>
                                <td>
                                    <span class="leave-status leave-status--with-icon {{ 'is-'.$notification->status }}">
                                        @if ($notification->status === 'sent')
                                            <i data-lucide="check-circle-2" class="nc-icon" aria-hidden="true"></i>
                                        @elseif ($notification->status === 'queued')
                                            <i data-lucide="clock-3" class="nc-icon" aria-hidden="true"></i>
                                        @elseif ($notification->status === 'failed')
                                            <i data-lucide="alert-triangle" class="nc-icon" aria-hidden="true"></i>
                                        @else
                                            <i data-lucide="circle-slash" class="nc-icon" aria-hidden="true"></i>
                                        @endif
                                        {{ $notification->status }}
                                    </span>
                                    <div class="notification-meta">
                                        <span>Tentatives: {{ $notification->attempts }}</span>
                                        @if ($notification->sent_at)
                                            <span>Envoye: {{ $notification->sent_at->format('d/m/Y H:i') }}</span>
                                        @elseif ($notification->queued_at)
                                            <span>Queue: {{ $notification->queued_at->format('d/m/Y H:i') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if ($leaveRequest)
                                        <strong>Demande #{{ $leaveRequest->id }}</strong><br>
                                        <span class="nc-muted">{{ $leaveRequest->employee?->name() ?? 'Collaborateur inconnu' }}</span><br>
                                        <span class="nc-muted">Etat actuel: {{ $leaveRequest->status }}</span>
                                    @else
                                        <span class="nc-muted">Aucun lien metier</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($notification->error_message)
                                        <details class="notification-error">
                                            <summary>Voir l'erreur</summary>
                                            <pre>{{ $notification->error_message }}</pre>
                                        </details>
                                    @else
                                        <span class="nc-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($canRetry)
                                        <form method="POST" action="{{ route('admin.leaves.notifications.retry', $notification) }}">
                                            @csrf
                                            <button class="nc-ghost" type="submit" data-tooltip-title="Relancer la notification" data-tooltip="Rejoue l’envoi uniquement si l’état actuel de la demande correspond encore à cet événement.">
                                                <i data-lucide="refresh-cw" class="nc-icon" aria-hidden="true"></i>
                                                Relancer
                                            </button>
                                        </form>
                                    @else
                                        <span class="nc-muted">Aucune action</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="nc-empty">Aucune notification ne correspond aux filtres.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="notification-pagination">
                {{ $notifications->links() }}
            </div>
        </section>
    </section>
</x-admin.layout>
@endsection
