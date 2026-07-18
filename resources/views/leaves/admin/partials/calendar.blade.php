<section class="nc-panel" id="calendar">
    <div class="nc-panel-heading"><div><h2>Calendrier des jours fériés</h2><p>Ces dates sont exclues automatiquement des durées en jours ouvrables.</p></div></div>
    <form method="POST" action="{{ route('admin.leaves.holidays.store') }}" class="leave-filter">
        @csrf
        <label class="nc-field"><span>Date</span><input type="date" name="date" required></label>
        <label class="nc-field"><span>Libellé</span><input name="name" required></label>
        <button class="nc-button" data-tooltip-title="Ajouter un jour férié" data-tooltip="Cette date sera exclue des calculs exprimés en jours ouvrables.">Ajouter</button>
    </form>
    <ul class="leave-holiday-list">
        @forelse($holidays as $holiday)
            <li><span>{{ $holiday->date->format('d/m/Y') }} — {{ $holiday->name }}</span><form method="POST" action="{{ route('admin.leaves.holidays.destroy', $holiday) }}">@csrf @method('DELETE')<button class="nc-ghost" aria-label="Supprimer {{ $holiday->name }}" data-tooltip-title="Réintégrer cette date" data-tooltip="Supprime le jour férié du calendrier utilisé par les prochains calculs.">Supprimer</button></form></li>
        @empty
            <li>Aucun jour férié configuré.</li>
        @endforelse
    </ul>
</section>
