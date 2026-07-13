<section aria-labelledby="step-title" class="ts-step-panel">
    <div class="ts-step-heading"><span>Étape 3 sur 6</span><h2 id="step-title">{{ $method === 'csv' ? 'Importer la clé de répartition' : 'Ajouter les collaborateurs' }}</h2></div>
    @if ($method === 'csv')
        <form wire:submit="analyzeCsv">
            <label class="ts-dropzone" for="csv-upload" x-on:dragover.prevent="$el.classList.add('is-dragging')" x-on:dragleave.prevent="$el.classList.remove('is-dragging')" x-on:drop="$el.classList.remove('is-dragging')">
                <i data-lucide="upload-cloud" aria-hidden="true"></i><strong>Déposez votre CSV ici</strong><span>ou choisissez un fichier · CSV/TXT · 1 Mo max · 500 lignes max</span>
                <input id="csv-upload" type="file" wire:model="csvFile" accept=".csv,text/csv,text/plain" required>
            </label>
            <div wire:loading wire:target="csvFile" class="ts-progress" role="status"><span>Import du fichier…</span><progress max="100"></progress></div>
            @if ($csvFile)<p class="ts-file-ready"><i data-lucide="file-check" aria-hidden="true"></i>{{ $csvFile->getClientOriginalName() }}</p>@endif
            @error('csvFile')<p class="nc-alert" role="alert">{{ $message }}</p>@enderror
            <div class="nc-actions"><button class="nc-button" type="submit" wire:loading.attr="disabled">Analyser le fichier</button></div>
        </form>
    @else
        <div class="nc-field ts-combobox"><label for="employee_search">Rechercher un collaborateur</label><input id="employee_search" type="search" wire:model.live.debounce.250ms="employeeSearch" autocomplete="off" placeholder="Nom ou prénom" role="combobox" aria-controls="employee-results" aria-expanded="{{ $employeeSearch !== '' ? 'true' : 'false' }}">
            @if ($employeeSearch !== '')<ul id="employee-results" class="ts-combobox-results" role="listbox">@forelse($this->employees as $employee)<li><button type="button" wire:click="addEmployee({{ $employee->id }})"><strong>{{ $employee->name() }}</strong><span>{{ $employee->job_title ?: 'Fonction non renseignée' }}</span></button></li>@empty<li class="ts-empty-row">Aucun collaborateur actif trouvé.</li>@endforelse</ul>@endif
        </div>
        <div class="ts-selected-list">@forelse($rows as $index => $row)<div wire:key="employee-{{ $row['employee_id'] }}"><span><strong>{{ $row['first_name'] }} {{ $row['last_name'] }}</strong><small>{{ $row['entity_name'] }}</small></span><button type="button" class="nc-ghost" wire:click="removeRow({{ $index }})" aria-label="Retirer {{ $row['first_name'] }} {{ $row['last_name'] }}"><i data-lucide="x" aria-hidden="true"></i></button></div>@empty<div class="ts-empty-state"><i data-lucide="user-plus" aria-hidden="true"></i><strong>Aucun collaborateur ajouté</strong><span>Utilisez la recherche ci-dessus pour constituer le lot.</span></div>@endforelse</div>
        @error('rows')<p class="nc-alert">{{ $message }}</p>@enderror
    @endif
</section>
