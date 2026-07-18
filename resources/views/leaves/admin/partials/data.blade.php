<section class="nc-panel" id="imports">
    <div class="nc-panel-heading">
        <div>
            <h2>Imports et exports</h2>
            <p>Initialisez les soldes depuis un CSV, puis exportez la situation RH.</p>
        </div>
    </div>
    @if (session('import_report'))
        @php($report = session('import_report'))
        <div class="nc-alert is-success" role="status">
            <strong>Résultat du dernier import</strong>
            <p>{{ $report['rows_read'] ?? $report['read'] ?? 0 }} lignes lues · {{ $report['rows_imported'] ?? $report['matched'] ?? 0 }} créées · {{ $report['rows_updated'] ?? 0 }} mises à jour · {{ $report['rows_skipped'] ?? 0 }} ignorées.</p>
            @if ($report['errors'] ?? [])<ul>@foreach ($report['errors'] as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
        </div>
    @endif
    <form method="POST" action="{{ route('admin.leaves.import') }}" enctype="multipart/form-data" class="nc-form-grid">
        @csrf
        <label class="nc-field"><span>Date de référence</span><input type="date" name="reference_date" value="{{ old('reference_date') }}" required><small>Date à laquelle les soldes du fichier ont été arrêtés.</small></label>
        <label class="nc-field"><span>Fichier des soldes</span><input type="file" name="file" accept=".csv,.txt,text/csv,text/plain" required><small>CSV ou TXT, avec une ligne par collaborateur.</small></label>
        <button class="nc-button" type="submit" data-tooltip-title="Importer les soldes" data-tooltip="Crée ou met à jour les soldes initiaux depuis le fichier sélectionné, à la date de référence."><i data-lucide="upload" aria-hidden="true"></i>Importer les soldes initiaux</button>
    </form>
    <div class="nc-actions">
        <a class="nc-button is-secondary" href="{{ route('admin.leaves.export') }}" data-tooltip-title="Exporter la situation" data-tooltip="Télécharge la situation RH au format Excel.">Exporter .xlsx</a>
        <a class="nc-ghost" href="{{ route('admin.leaves.import.template') }}" data-tooltip-title="Préparer l’import" data-tooltip="Télécharge un fichier modèle avec les colonnes attendues pour les soldes initiaux.">Modèle d’import</a>
    </div>
</section>
