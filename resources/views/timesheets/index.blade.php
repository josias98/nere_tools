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

        <div class="ts-option-grid">
            <section class="ts-card">
                <div class="ts-step-head">
                    <h2><span>Option 1.</span> Charger un fichier CSV</h2>
                    <p>Utilisez ce mode pour generer un lot controle depuis une cle de repartition. Apres chargement, chaque ligne reste editable avant export.</p>
                </div>
                <form method="POST" action="{{ route('timesheets.csv') }}" enctype="multipart/form-data" class="ts-import-form">
                    @csrf
                    <div class="ts-upload-stack">
                        <label class="ts-upload-button" for="csv_file">Choisir un CSV</label>
                        <input id="csv_file" name="csv_file" type="file" accept=".csv,text/csv" required>
                        <span class="ts-upload-meta" id="csv_file_name">{{ $rows ? count($rows).' ligne(s) chargee(s).' : 'CSV non selectionne.' }}</span>
                    </div>
                    <button class="nc-button" type="submit">Charger le fichier</button>
                    @if ($rows)
                        <a class="nc-ghost" href="{{ route('timesheets.csv.clear') }}">Vider le CSV</a>
                    @endif
                </form>
            </section>

            <section class="ts-card">
                <div class="ts-step-head">
                    <h2><span>Option 2.</span> Generation manuelle</h2>
                    <p>Utilisez ce mode pour un lot simple a partir des collaborateurs deja presents dans Nere Tools, sans fichier source.</p>
                </div>
                @if ($rows)
                    <p class="nc-muted">Un CSV est charge. Videz-le si vous voulez revenir au formulaire manuel.</p>
                @else
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
                @endif
            </section>
        </div>

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
            <aside class="nc-panel">
                <h2>FAQ et manuel d'utilisation</h2>
                <div class="ts-help-list">
                    <details open>
                        <summary>Quel mode choisir ?</summary>
                        <p>Choisissez l'option 1 si vous avez une cle de repartition CSV a verifier ligne par ligne. Choisissez l'option 2 si vous voulez generer rapidement des feuilles depuis les collaborateurs actifs deja en base.</p>
                    </details>
                    <details>
                        <summary>Comment preparer le CSV ?</summary>
                        <p>Le fichier doit permettre d'identifier chaque collaborateur par id, nom complet, prenom/nom ou nom affiche. Les colonnes reconnues incluent notamment prenom, nom, entite, pays, fonction, role, fonds, ipde, catal, autre_projets, code_analytique, lieu, nom_signature, responsable_hierarchique et signature_droite_titre.</p>
                    </details>
                    <details>
                        <summary>Que verifier apres chargement du CSV ?</summary>
                        <p>Controlez les noms, entites, taux de repartition, codes analytiques, lieux et signataires. Decochez les salaries a exclure du ZIP. Les modifications faites dans le tableau sont prises en compte uniquement pour la generation en cours.</p>
                    </details>
                    <details>
                        <summary>Comment regler la periode ?</summary>
                        <p>En mode CSV, la date de debut et la date de fin s'appliquent a tout le lot. Chaque mois compris dans cette periode devient une page dans le PDF du salarie. Le libelle ZIP est calcule automatiquement, mais il peut etre modifie avant generation.</p>
                    </details>
                    <details>
                        <summary>A quoi servent les dates exclues ?</summary>
                        <p>Ajoutez une date par ligne pour eviter qu'une signature tombe sur un jour ferme. Les formats acceptes sont YYYY-MM-DD et DD/MM/YYYY. Les week-ends sont deja sautes automatiquement.</p>
                    </details>
                    <details>
                        <summary>Que contient le ZIP genere ?</summary>
                        <p>Le ZIP contient un PDF par salarie selectionne. Chaque PDF regroupe toutes les pages mensuelles de la periode demandee. Apres une generation CSV reussie, le CSV charge est vide pour eviter une regeneration accidentelle.</p>
                    </details>
                    <details>
                        <summary>Quand utiliser la generation manuelle ?</summary>
                        <p>Utilisez-la pour une generation ponctuelle sans cle de repartition. Selectionnez les collaborateurs, la periode, l'entite affichee et les options PDF. Les valeurs manquantes viennent des profils collaborateurs en base.</p>
                    </details>
                </div>
                <div class="nc-actions">
                    <a class="nc-ghost" href="{{ route('timesheets.history') }}">Voir l'historique</a>
                </div>
            </aside>
        </div>
    </section>
@endsection
