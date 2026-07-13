<section aria-labelledby="step-title" class="ts-step-panel ts-wide">
    <div class="ts-step-heading"><span>Étape 4 sur 6</span><h2 id="step-title">Vérifier les répartitions</h2><p>Chaque ligne doit totaliser exactement 100 %. Les champs restent modifiables.</p></div>
    <div class="ts-summary"><span><small>Période</small><strong>{{ \Carbon\Carbon::parse($periodStart)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($periodEnd)->format('d/m/Y') }}</strong></span><span><small>Collaborateurs</small><strong>{{ count($rows) }}</strong></span><span><small>Mode</small><strong>{{ $method === 'csv' ? 'Import CSV' : 'Saisie manuelle' }}</strong></span></div>
    <div class="ts-review-list">
        @foreach ($rows as $index => $row)
            @php($total = $this->rowTotal($index))
            <article class="ts-allocation" wire:key="allocation-{{ $row['employee_id'] }}">
                <header><div><h3>{{ $row['first_name'] }} {{ $row['last_name'] }}</h3><p>{{ $row['entity_name'] ?: 'Entité non renseignée' }}</p></div><span @class(['nc-badge', 'active' => abs($total - 100) < .01, 'warning' => abs($total - 100) >= .01])>{{ round($total, 2) }} %</span></header>
                <div class="ts-rate-grid">@foreach(['ipas_rate'=>'IPAS','catal_rate'=>'CATAL','ipde_rate'=>'IPDE','other_projects_rate'=>'Autres projets'] as $field => $label)<div class="nc-field"><label for="row_{{ $index }}_{{ $field }}">{{ $label }}</label><div class="ts-percent"><input id="row_{{ $index }}_{{ $field }}" type="number" min="0" max="100" step="0.01" wire:model.live.debounce.250ms="rows.{{ $index }}.{{ $field }}"><span>%</span></div>@error("rows.$index.$field")<small class="nc-error">{{ $message }}</small>@enderror</div>@endforeach</div>
                <div class="nc-field"><label for="code_{{ $index }}">Code analytique</label><input id="code_{{ $index }}" type="text" wire:model="rows.{{ $index }}.analytic_code" maxlength="120"></div>
                <footer><button type="button" class="nc-ghost" wire:click="duplicateAllocation({{ $index }})">Appliquer cette répartition aux autres</button><button type="button" class="nc-ghost" wire:click="removeRow({{ $index }})">Retirer</button></footer>
            </article>
        @endforeach
    </div>
</section>
