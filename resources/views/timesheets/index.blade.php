@extends('layouts.app', ['title' => 'Feuilles de temps - Nere Tools'])

@section('content')
    <section class="nc-page">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Module finance</p>
                <h1 class="nc-title">Feuilles de temps</h1>
                <p class="nc-lead">La date de signature correspond au premier jour ouvre suivant la fin du mois.</p>
            </div>
        </div>

        @if ($errors->any())
            <p class="nc-alert">{{ $errors->first() }}</p>
        @endif

        @if (session('status'))
            <p class="nc-alert is-success">{{ session('status') }}</p>
        @endif

        <div class="nc-timesheet-workbench">
            <section class="nc-panel nc-panel-premium">
                <div class="nc-panel-heading">
                    <div>
                        <h2>Importer un parametrage CSV</h2>
                        <p>Televersez, verifiez les lignes, ajustez au besoin, puis genereez le ZIP.</p>
                    </div>
                    <span class="nc-badge">CSV</span>
                </div>

                <form method="POST" action="{{ route('timesheets.csv') }}" enctype="multipart/form-data" class="nc-upload">
                    @csrf
                    <label class="nc-dropzone" for="csv_file">
                        <span>Choisir un fichier CSV</span>
                        <small>Colonnes: employee_id ou collaborateur, year, start_month, end_month, entity_label, signature_date, signatory_name, comments_label, include_comments.</small>
                    </label>
                    <input id="csv_file" name="csv_file" type="file" accept=".csv,text/csv" required>
                    <button class="nc-button" type="submit">Previsualiser</button>
                </form>
            </section>

            @if ($csvRows)
                <section class="nc-panel nc-panel-premium">
                    <div class="nc-panel-heading">
                        <div>
                            <h2>Apercu editable</h2>
                            <p>{{ count($csvRows) }} ligne(s) importee(s). Chaque cellule ci-dessous sera utilisee pour generer les PDF.</p>
                        </div>
                        <span class="nc-badge active">Pret</span>
                    </div>
                    <form method="POST" action="{{ route('timesheets.generate') }}">
                        @csrf
                        <input name="download_zip" type="hidden" value="1">
                        <div class="nc-table-wrap">
                            <table class="nc-table nc-edit-table">
                                <thead>
                                    <tr>
                                        <th>Collaborateur</th>
                                        <th>Annee</th>
                                        <th>Debut</th>
                                        <th>Fin</th>
                                        <th>Entite</th>
                                        <th>Date signature</th>
                                        <th>Responsable</th>
                                        <th>Commentaires</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($csvRows as $index => $row)
                                        <tr>
                                            <td>
                                                <select name="rows[{{ $index }}][employee_id]">
                                                    @foreach ($employees as $employee)
                                                        <option value="{{ $employee->id }}" @selected((int) $row['employee_id'] === $employee->id)>{{ $employee->name() }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input name="rows[{{ $index }}][year]" type="number" min="2020" max="2100" value="{{ $row['year'] }}"></td>
                                            <td><input name="rows[{{ $index }}][start_month]" type="number" min="1" max="12" value="{{ $row['start_month'] }}"></td>
                                            <td><input name="rows[{{ $index }}][end_month]" type="number" min="1" max="12" value="{{ $row['end_month'] }}"></td>
                                            <td><input name="rows[{{ $index }}][entity_label]" type="text" value="{{ $row['entity_label'] }}"></td>
                                            <td><input name="rows[{{ $index }}][signature_date]" type="date" value="{{ $row['signature_date'] }}"></td>
                                            <td><input name="rows[{{ $index }}][signatory_name]" type="text" value="{{ $row['signatory_name'] }}"></td>
                                            <td>
                                                <input name="rows[{{ $index }}][comments_label]" type="text" value="{{ $row['comments_label'] }}">
                                                <label class="nc-mini-check">
                                                    <input name="rows[{{ $index }}][include_comments]" type="hidden" value="0">
                                                    <input name="rows[{{ $index }}][include_comments]" type="checkbox" value="1" @checked((bool) $row['include_comments'])>
                                                    Afficher
                                                </label>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="nc-actions">
                            <button class="nc-button is-secondary" type="submit">Generer et telecharger</button>
                            <a class="nc-ghost" href="{{ route('timesheets.csv.clear') }}">Abandonner l'aperçu</a>
                        </div>
                    </form>
                </section>
            @endif

            <div class="nc-grid two">
                <section class="nc-panel">
                    <div class="nc-panel-heading">
                        <div>
                            <h2>Generation manuelle</h2>
                            <p>Flux rapide si toutes les feuilles partagent les memes options.</p>
                        </div>
                    </div>
                <form method="POST" action="{{ route('timesheets.generate') }}" class="nc-form-grid">
                    @csrf
                    <div class="nc-field">
                        <label for="period_type">Type de periode</label>
                        <select id="period_type" name="period_type">
                            <option value="month">Mois</option>
                            <option value="quarter">Trimestre</option>
                            <option value="semester" selected>Semestre</option>
                            <option value="custom">Personnalisee</option>
                        </select>
                    </div>
                    <div class="nc-field">
                        <label for="year">Annee</label>
                        <input id="year" name="year" type="number" min="2020" max="2100" value="{{ old('year', now()->year) }}">
                    </div>
                    <div class="nc-field">
                        <label for="start_month">Mois de debut</label>
                        <input id="start_month" name="start_month" type="number" min="1" max="12" value="{{ old('start_month', 1) }}">
                    </div>
                    <div class="nc-field">
                        <label for="end_month">Mois de fin</label>
                        <input id="end_month" name="end_month" type="number" min="1" max="12" value="{{ old('end_month', 6) }}">
                    </div>
                    <div class="nc-field" style="grid-column: 1 / -1">
                        <label for="employee_ids">Collaborateurs</label>
                        <select id="employee_ids" name="employee_ids[]" multiple size="6" required>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="nc-field">
                        <label for="entity_label">Entite affichee</label>
                        <input id="entity_label" name="entity_label" type="text" value="{{ old('entity_label', 'Nere Capital') }}">
                    </div>
                    <div class="nc-field">
                        <label for="signature_date">Date de signature forcee</label>
                        <input id="signature_date" name="signature_date" type="date" value="{{ old('signature_date') }}">
                    </div>
                    <div class="nc-field">
                        <label for="signatory_name">Responsable force</label>
                        <input id="signatory_name" name="signatory_name" type="text" value="{{ old('signatory_name') }}" placeholder="Valeur collaborateur par defaut">
                    </div>
                    <div class="nc-field">
                        <label for="comments_label">Libelle commentaires</label>
                        <input id="comments_label" name="comments_label" type="text" value="{{ old('comments_label', 'Commentaires / Details') }}">
                    </div>
                    <label class="nc-field" style="grid-column: 1 / -1">
                        <span>Options PDF</span>
                        <span>
                            <input name="include_comments" type="hidden" value="0">
                            <input name="include_comments" type="checkbox" value="1" @checked(old('include_comments', '1'))>
                            Afficher la colonne commentaires
                        </span>
                    </label>
                    <div class="nc-actions">
                        <button class="nc-button is-secondary" type="submit">Generer les feuilles</button>
                        <a class="nc-ghost" href="{{ route('timesheets.index') }}">Reinitialiser</a>
                    </div>
                </form>
                </section>

                <aside class="nc-panel">
                    <h2>Regles appliquees</h2>
                    <table class="nc-table">
                        <tbody>
                            <tr><th scope="row">IPAS</th><td>Conserve dans la repartition.</td></tr>
                            <tr><th scope="row">Autres projets</th><td>Ajoute pour Job et Germaine.</td></tr>
                            <tr><th scope="row">Signature</th><td>Cas particuliers appliques depuis la base ou le CSV.</td></tr>
                        </tbody>
                    </table>
                    <div class="nc-actions">
                        <a class="nc-ghost" href="{{ route('timesheets.history') }}">Voir l'historique</a>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
