@extends('layouts.app', [
    'title' => 'Notifications des congés - Néré Tools',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Administration', 'url' => route('admin.index')],
        ['label' => 'Congés', 'url' => route('admin.leaves.index')],
        ['label' => 'Notifications'],
    ],
])

@section('content')
@php($statusLabels = ['queued' => 'En attente', 'sent' => 'Envoyée', 'failed' => 'En échec', 'skipped' => 'Ignorée'])
<x-admin.layout>
    <section class="nc-page leave-workbench">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Administration · Congés</p>
                <h1 class="nc-title">Notifications</h1>
                <p class="nc-lead">Suivre les envois Office 365 et relancer uniquement ceux qui peuvent l’être sans risque.</p>
            </div>
        </div>

        @include('leaves.partials.flash')

        @error('notification')
            <section class="nc-alert">{{ $message }}</section>
        @enderror

        <div class="leave-metrics leave-metrics--compact">
            <article class="nc-panel leave-metric">
                <span>En attente</span>
                <strong>{{ $counts['queued'] ?? 0 }}</strong>
                <small>notifications</small>
            </article>
            <article class="nc-panel leave-metric">
                <span>Envoyées</span>
                <strong>{{ $counts['sent'] ?? 0 }}</strong>
                <small>notifications</small>
            </article>
            <article class="nc-panel leave-metric">
                <span>En échec</span>
                <strong>{{ $counts['failed'] ?? 0 }}</strong>
                <small>notifications</small>
            </article>
            <article class="nc-panel leave-metric">
                <span>Ignorées</span>
                <strong>{{ $counts['skipped'] ?? 0 }}</strong>
                <small>notifications</small>
            </article>
        </div>

        <section class="nc-panel">
            <div class="nc-panel-heading">
                <div>
                    <h2>Filtres</h2>
                    <p>Seules les métadonnées utiles sont conservées ; le contenu des emails n’est pas stocké.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.leaves.notifications.index') }}" class="leave-filter leave-filter--notifications">
                <label class="nc-field">
                    <span>Statut</span>
                    <select name="status">
                        <option value="">Tous</option>
                        @foreach ($statusLabels as $status => $label)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="nc-field">
                    <span>Événement</span>
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
                    <p>La relance est disponible uniquement pour les notifications en échec ou ignorées.</p>
                </div>
            </div>

            <div class="nc-table-wrap">
                <table class="nc-table notification-table">
                    <thead>
                        <tr>
                            <th>Événement</th>
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
                                        {{ $statusLabels[$notification->status] ?? $notification->status }}
                                    </span>
                                    <div class="notification-meta">
                                        <span>Tentatives: {{ $notification->attempts }}</span>
                                        @if ($notification->sent_at)
                                            <span>Envoyée : {{ $notification->sent_at->format('d/m/Y H:i') }}</span>
                                        @elseif ($notification->queued_at)
                                            <span>Mise en attente : {{ $notification->queued_at->format('d/m/Y H:i') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if ($leaveRequest)
                                        <strong>Demande #{{ $leaveRequest->id }}</strong><br>
                                        <span class="nc-muted">{{ $leaveRequest->employee?->name() ?? 'Collaborateur inconnu' }}</span><br>
                                        <span class="nc-muted">État actuel : {{ $leaveRequest->status }}</span>
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
