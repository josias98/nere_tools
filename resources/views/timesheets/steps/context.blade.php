<section aria-labelledby="step-title" class="ts-step-panel">
    <div class="ts-step-heading"><span>Cadre du lot</span><h2 id="step-title">Quelle période doit être couverte&nbsp;?</h2><p>Les dates s’appliqueront à tous les collaborateurs du lot.</p></div>
    <div class="nc-form-grid">
        <div class="nc-field"><label for="period_start">Début de période</label><input id="period_start" type="date" wire:model="periodStart" wire:change="syncZipLabel" aria-describedby="period_start_error">@error('periodStart')<small id="period_start_error" class="nc-error">{{ $message }}</small>@enderror</div>
        <div class="nc-field"><label for="period_end">Fin de période</label><input id="period_end" type="date" wire:model="periodEnd" wire:change="syncZipLabel" aria-describedby="period_end_error">@error('periodEnd')<small id="period_end_error" class="nc-error">{{ $message }}</small>@enderror</div>
        <div class="nc-field ts-span"><label for="zip_label">Nom du livrable</label><input id="zip_label" type="text" wire:model="zipLabel" maxlength="120">@error('zipLabel')<small class="nc-error">{{ $message }}</small>@enderror</div>
    </div>
    <div class="ts-context-preview" aria-live="polite"><span><small>Mode</small><strong>{{ $method === 'csv' ? 'Import CSV' : 'Saisie manuelle' }}</strong></span><span><small>Période retenue</small><strong>{{ \Carbon\Carbon::parse($periodStart)->format('d.m.Y') }} → {{ \Carbon\Carbon::parse($periodEnd)->format('d.m.Y') }}</strong></span></div>
    <div class="ts-context-note"><i data-lucide="shield-check" aria-hidden="true"></i><p><strong>Stockage privé.</strong> Les documents ne sont accessibles qu’au créateur du lot et aux administrateurs.</p></div>
</section>
