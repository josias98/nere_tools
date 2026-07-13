<section aria-labelledby="step-title" class="ts-step-panel ts-wide">
    <div class="ts-step-heading"><span>Contrôle</span><h2 id="step-title">Tout doit tomber juste</h2><p>Ouvrez un collaborateur pour ajuster sa répartition. Chaque total doit atteindre 100&nbsp;%.</p></div>
    <div class="ts-summary"><span><small>Période</small><strong>{{ \Carbon\Carbon::parse($periodStart)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($periodEnd)->format('d/m/Y') }}</strong></span><span><small>Collaborateurs</small><strong>{{ count($rows) }}</strong></span><span><small>Mode</small><strong>{{ $method === 'csv' ? 'Import CSV' : 'Saisie manuelle' }}</strong></span></div>
    <div class="ts-review-list">
        @foreach ($rows as $index => $row)
            @php($total = $this->rowTotal($index))
            <details class="ts-allocation" wire:key="allocation-{{ $row['employee_id'] }}" @if(abs($total - 100) >= .01) open @endif>
                <summary><span class="ts-person-mark">{{ mb_strtoupper(mb_substr($row['first_name'], 0, 1).mb_substr($row['last_name'], 0, 1)) }}</span><span><strong>{{ $row['first_name'] }} {{ $row['last_name'] }}</strong><small>{{ $row['entity_name'] ?: 'Entité non renseignée' }}</small></span><span @class(['ts-total', 'is-valid' => abs($total - 100) < .01, 'is-invalid' => abs($total - 100) >= .01])><i data-lucide="{{ abs($total - 100) < .01 ? 'check' : 'alert-triangle' }}" aria-hidden="true"></i>{{ round($total, 2) }} %</span><i class="ts-chevron" data-lucide="chevron-down" aria-hidden="true"></i></summary>
                <div class="ts-allocation-body">
                <div class="ts-rate-grid">@foreach(['ipas_rate'=>'IPAS','catal_rate'=>'CATAL','ipde_rate'=>'IPDE','other_projects_rate'=>'Autres projets'] as $field => $label)<div class="nc-field"><label for="row_{{ $index }}_{{ $field }}">{{ $label }}</label><div class="ts-percent"><input id="row_{{ $index }}_{{ $field }}" type="number" min="0" max="100" step="0.01" wire:model.live.debounce.250ms="rows.{{ $index }}.{{ $field }}"><span>%</span></div>@error("rows.$index.$field")<small class="nc-error">{{ $message }}</small>@enderror</div>@endforeach</div>
                <div class="nc-field"><label for="code_{{ $index }}">Code analytique</label><input id="code_{{ $index }}" type="text" wire:model="rows.{{ $index }}.analytic_code" maxlength="120"></div>
                <footer><button type="button" class="nc-ghost" wire:click="duplicateAllocation({{ $index }})">Appliquer aux autres</button><button type="button" class="nc-ghost" wire:click="removeRow({{ $index }})">Retirer du lot</button></footer></div>
            </details>
        @endforeach
    </div>
</section>
