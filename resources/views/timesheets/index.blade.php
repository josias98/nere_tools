@extends('layouts.app', [
    'title' => 'Feuilles de temps - Nere Tools',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Feuilles de temps'],
    ],
])

@section('content')
    @php($rows = old('rows', $csvRows))

    <section class="nc-page nc-timesheet-studio">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Module finance</p>
                <h1 class="nc-title">Feuilles de temps</h1>
                <p class="nc-lead">Importez votre cle de repartition, verifiez les profils, puis generez le lot PDF sur une periode commune.</p>
            </div>
        </div>

        @if ($errors->any())
            <p class="nc-alert">{{ $errors->first() }}</p>
        @endif

        @if (session('status'))
            <p class="nc-alert is-success">{{ session('status') }}</p>
        @endif

        @if ($rows)
            <form method="POST" action="{{ route('timesheets.generate') }}" class="ts-wizard">
                @csrf
                <input name="download_zip" type="hidden" value="1">

                <section class="ts-card">
                    <div class="ts-step-head">
                        <h2><span>1.</span> Cle de repartition</h2>
                        <p>Chaque ligne reste editable avant generation. Les profils coches seront exportes.</p>
                    </div>

                    

                    <div class="ts-grid-wrap">
                        <table class="ts-grid">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>prenom</th>
                                    <th>nom</th>
                                    <th>entite</th>
                                    <th>pays</th>
                                    <th>fonction</th>
                                    <th>role</th>
                                    <th>fonds</th>
                                    <th>ipde</th>
                                    <th>catal</th>
                                    <th>autre_projets</th>
                                    <th>code_analytique</th>
                                    <th>lieu</th>
                                    <th>nom_signature</th>
                                    <th>responsable_hierarchique</th>
                                    <th>signature_droite_titre</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $index => $row)
                                    <tr>
                                        <td class="ts-grid-select">
                                            <input name="rows[{{ $index }}][selected]" type="hidden" value="0">
                                            <input class="ts-row-toggle" data-chip-label="{{ trim(($row['first_name'] ?? '').' '.($row['last_name'] ?? '')) }}" name="rows[{{ $index }}][selected]" type="checkbox" value="1" @checked((bool) ($row['selected'] ?? true))>
                                            <input name="rows[{{ $index }}][employee_id]" type="hidden" value="{{ $row['employee_id'] }}">
                                            <input name="rows[{{ $index }}][comments_label]" type="hidden" value="{{ $row['comments_label'] ?? 'Commentaires / Details' }}">
                                            <input name="rows[{{ $index }}][include_comments]" type="hidden" value="{{ (int) ($row['include_comments'] ?? 1) }}">
                                        </td>
                                        <td><input name="rows[{{ $index }}][first_name]" type="text" value="{{ $row['first_name'] ?? '' }}"></td>
                                        <td><input name="rows[{{ $index }}][last_name]" type="text" value="{{ $row['last_name'] ?? '' }}"></td>
                                        <td><input name="rows[{{ $index }}][entity_name]" type="text" value="{{ $row['entity_name'] ?? '' }}"></td>
                                        <td><input name="rows[{{ $index }}][country]" type="text" value="{{ $row['country'] ?? '' }}"></td>
                                        <td><input name="rows[{{ $index }}][function_title]" type="text" value="{{ $row['function_title'] ?? '' }}"></td>
                                        <td><input name="rows[{{ $index }}][role]" type="text" value="{{ $row['role'] ?? '' }}"></td>
                                        <td><input name="rows[{{ $index }}][funds]" type="text" value="{{ $row['funds'] ?? '' }}"></td>
                                        <td><input name="rows[{{ $index }}][ipde_rate]" type="number" step="0.01" min="0" max="100" value="{{ $row['ipde_rate'] ?? '' }}"></td>
                                        <td><input name="rows[{{ $index }}][catal_rate]" type="number" step="0.01" min="0" max="100" value="{{ $row['catal_rate'] ?? '' }}"></td>
                                        <td><input name="rows[{{ $index }}][other_projects_rate]" type="number" step="0.01" min="0" max="100" value="{{ $row['other_projects_rate'] ?? '' }}"></td>
                                        <td><input name="rows[{{ $index }}][analytic_code]" type="text" value="{{ $row['analytic_code'] ?? '' }}"></td>
                                        <td><input name="rows[{{ $index }}][location]" type="text" value="{{ $row['location'] ?? '' }}"></td>
                                        <td><input name="rows[{{ $index }}][employee_signature_name]" type="text" value="{{ $row['employee_signature_name'] ?? '' }}"></td>
                                        <td><input name="rows[{{ $index }}][signatory_name]" type="text" value="{{ $row['signatory_name'] ?? '' }}"></td>
                                        <td><input name="rows[{{ $index }}][signature_title]" type="text" value="{{ $row['signature_title'] ?? '' }}"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="ts-chip-block">
                        <label>Salaries a generer</label>
                        <div class="ts-chip-tray" id="ts-chip-tray">
                            @foreach ($rows as $row)
                                @if ((bool) ($row['selected'] ?? true))
                                    <span class="ts-chip">{{ trim(($row['first_name'] ?? '').' '.($row['last_name'] ?? '')) }}</span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </section>

                <section class="ts-card">
                    <div class="ts-step-head">
                        <h2><span>2.</span> Periode et parametres</h2>
                        <p>Ces champs s'appliquent a tout le lot. La date de signature saute les week-ends et les dates exclues.</p>
                    </div>

                    <div class="ts-parameter-grid">
                        <label class="ts-field">
                            <span>Date de debut</span>
                            <input id="period_start" name="period_start" type="date" value="{{ old('period_start', $csvPeriodStart) }}" required>
                        </label>
                        <label class="ts-field">
                            <span>Date de fin</span>
                            <input id="period_end" name="period_end" type="date" value="{{ old('period_end', $csvPeriodEnd) }}" required>
                        </label>
                        <label class="ts-field">
                            <span>Libelle du fichier ZIP</span>
                            <input id="zip_label" name="zip_label" type="text" value="{{ old('zip_label', $csvZipLabel) }}">
                        </label>
                    </div>

                    <label class="ts-field">
                        <span>Jours feries ou non ouvres a exclure des dates de signature, un par ligne, format YYYY-MM-DD ou DD/MM/YYYY</span>
                        <textarea name="excluded_signature_dates" rows="4">{{ old('excluded_signature_dates') }}</textarea>
                    </label>

                    <div class="ts-field">
                        <span>Logo PDF</span>
                        <div class="ts-logo-upload">
                            <p id="logo_preview_text">Le logo IP joint est applique automatiquement dans les PDF generes.</p>
                        </div>
                    </div>
                </section>

                <section class="ts-card ts-card-action">
                    <div class="ts-step-head">
                        <h2><span>3.</span> Generation</h2>
                        <p>Le ZIP contiendra un PDF par salarie selectionne. Chaque mois de la periode devient une page dans son PDF.</p>
                    </div>
                    <button class="ts-generate-button" type="submit">Generer les feuilles de temps PDF</button>
                </section>
            </form>
        @else
            <section class="ts-card ts-empty-card">
                <div class="ts-step-head">
                    <h2><span>1.</span> Cle de repartition</h2>
                    <p>Chargez un export CSV pour afficher la table source, les salaries a generer et les parametres globaux.</p>
                </div>
            </section>
        @endif

        <div class="nc-timesheet-workbench">
            @if (! $rows)
                <section class="nc-panel">
                    <div class="nc-panel-heading">
                        <div>
                            <h2>Generation manuelle</h2>
                            <p>Conservez ce mode pour un lot simple sans CSV.</p>
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
            @endif

            <aside class="nc-panel">
                <h2>FAQ et manuel rapide</h2>
                <div class="ts-help-list">
                    <article>
                        <h3>Comment utiliser un CSV ?</h3>
                        <p>Chargez le fichier, verifiez les lignes, decochez les salaries a exclure, puis lancez la generation.</p>
                    </article>
                    <article>
                        <h3>Que fait la periode ?</h3>
                        <p>Les dates de debut et de fin determinent les mois generes. Le PDF final contient une page par mois.</p>
                    </article>
                    <article>
                        <h3>Que contient le ZIP ?</h3>
                        <p>Un PDF par salarie selectionne, groupe par utilisateur, avec toutes ses pages de periode.</p>
                    </article>
                    <article>
                        <h3>Comment sont calculees les signatures ?</h3>
                        <p>La date tombe au premier jour ouvrable apres la fin du mois, en sautant les week-ends et les dates exclues.</p>
                    </article>
                    <article>
                        <h3>Quand utiliser la generation manuelle ?</h3>
                        <p>Quand aucun CSV n'est charge et que le lot repose seulement sur les profils collaborateurs deja en base.</p>
                    </article>
                </div>
                <div class="nc-actions">
                    @if ($rows)
                        <a class="nc-ghost" href="{{ route('timesheets.csv.clear') }}">Vider le CSV</a>
                    @endif
                    <a class="nc-ghost" href="{{ route('timesheets.history') }}">Voir l'historique</a>
                </div>
            </aside>
        </div>
    </section>
@endsection
