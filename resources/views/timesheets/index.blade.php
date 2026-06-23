@extends('layouts.app', [
    'title' => 'Feuilles de temps - Nere Tools',
    'breadcrumbs' => [
        ['label' => 'Accueil', 'url' => route('dashboard')],
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
                <p class="nc-lead">Importez votre clé de répartition, vérifiez les informations, puis générez vos PDF sur une période commune.</p>
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
                    <h2><span>Option 1.</span> Générer à partir d'un CSV</h2>
                    <p>Choisissez cette option si vous partez d'une clé de répartition. Une fois le fichier chargé, vous pouvez relire et ajuster chaque ligne avant la génération.</p>
                </div>
                <form method="POST" action="{{ route('timesheets.csv') }}" enctype="multipart/form-data" class="ts-import-form">
                    @csrf
                    <div class="ts-upload-stack">
                        <label class="ts-upload-button" for="csv_file">Choisir un fichier CSV</label>
                        <input id="csv_file" name="csv_file" type="file" accept=".csv,text/csv" required>
                        <span class="ts-upload-meta" id="csv_file_name">{{ $rows ? count($rows).' ligne(s) chargée(s).' : 'Aucun fichier CSV sélectionné.' }}</span>
                    </div>
                    <button class="nc-button" type="submit">
                        <i data-lucide="upload" class="nc-icon" aria-hidden="true"></i>
                        Charger le fichier
                    </button>
                    @if ($rows)
                        <a class="nc-ghost" href="{{ route('timesheets.csv.clear') }}">
                            <i data-lucide="refresh-cw" class="nc-icon" aria-hidden="true"></i>
                            Vider le CSV
                        </a>
                    @endif
                </form>
            </section>

            <section class="ts-card">
                <div class="ts-step-head">
                    <h2><span>Option 2.</span> Génération manuelle</h2>
                    <p>Utilisez ce mode pour créer un lot simple à partir des collaborateurs déjà présents dans Nere Tools, sans fichier source.</p>
                </div>
                @if ($rows)
                    <p class="nc-muted">Un CSV est actuellement chargé. Videz-le si vous souhaitez revenir au formulaire manuel.</p>
                @else
                    <form method="POST" action="{{ route('timesheets.generate') }}" class="nc-form-grid">
                        @csrf
                        <div class="nc-field">
                            <label for="period_type">Type de periode</label>
                            <select id="period_type" name="period_type">
                                <option value="month">Mois</option>
                                <option value="quarter">Trimestre</option>
                                <option value="semester" selected>Semestre</option>
                                <option value="custom">Personnalisée</option>
                            </select>
                        </div>
                        <div class="nc-field">
                            <label for="year">Année</label>
                            <input id="year" name="year" type="number" min="2020" max="2100" value="{{ old('year', now()->year) }}">
                        </div>
                        <div class="nc-field">
                            <label for="start_month">Mois de début</label>
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
                            <label for="entity_label">Entité affichée</label>
                            <input id="entity_label" name="entity_label" type="text" value="{{ old('entity_label', 'Nere Capital') }}">
                        </div>
                        <div class="nc-field">
                            <label for="signature_date">Date de signature forcée</label>
                            <input id="signature_date" name="signature_date" type="date" value="{{ old('signature_date') }}">
                        </div>
                        <div class="nc-field">
                            <label for="signatory_name">Nom du responsable</label>
                            <input id="signatory_name" name="signatory_name" type="text" value="{{ old('signatory_name') }}" placeholder="Par défaut, la valeur du collaborateur sera utilisée">
                        </div>
                        <div class="nc-field">
                            <label for="comments_label">Libellé des commentaires</label>
                            <input id="comments_label" name="comments_label" type="text" value="{{ old('comments_label', 'Commentaires / Détails') }}">
                        </div>
                        <label class="nc-field" style="grid-column: 1 / -1">
                            <span>Options PDF</span>
                            <span>
                                <input name="include_comments" type="hidden" value="0">
                                <input name="include_comments" type="checkbox" value="1" @checked(old('include_comments', '1'))>
                                Afficher la colonne des commentaires
                            </span>
                        </label>
                        <div class="nc-actions">
                            <button class="nc-button is-secondary" type="submit">Générer les feuilles</button>
                            <a class="nc-ghost" href="{{ route('timesheets.index') }}">Réinitialiser</a>
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
                        <h2><span>1.</span> Vérifier la clé de répartition</h2>
                        <p>Chaque ligne peut être relue et modifiée avant la génération. Seuls les profils cochés seront exportés.</p>
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
                                            <input name="rows[{{ $index }}][comments_label]" type="hidden" value="{{ $row['comments_label'] ?? 'Commentaires / Détails' }}">
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
                        <label>Collaborateurs sélectionnés</label>
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
                        <h2><span>2.</span> Définir la période et les options</h2>
                        <p>Ces paramètres s'appliquent à l'ensemble du lot. La date de signature évite automatiquement les week-ends et les dates exclues.</p>
                    </div>

                    <div class="ts-parameter-grid">
                        <label class="ts-field">
                            <span>Date de début</span>
                            <input id="period_start" name="period_start" type="date" value="{{ old('period_start', $csvPeriodStart) }}" required>
                        </label>
                        <label class="ts-field">
                            <span>Date de fin</span>
                            <input id="period_end" name="period_end" type="date" value="{{ old('period_end', $csvPeriodEnd) }}" required>
                        </label>
                        <label class="ts-field">
                            <span>Nom du fichier ZIP</span>
                            <input id="zip_label" name="zip_label" type="text" value="{{ old('zip_label', $csvZipLabel) }}">
                        </label>
                    </div>

                    <label class="ts-field">
                        <span>Dates à exclure pour la signature, une par ligne, au format YYYY-MM-DD ou DD/MM/YYYY (jours fériés, fermetures, etc.)</span>
                        <textarea name="excluded_signature_dates" rows="4">{{ old('excluded_signature_dates') }}</textarea>
                    </label>

                    <!-- <div class="ts-field">
                        <span>Logo PDF</span>
                        <div class="ts-logo-upload">
                            <p id="logo_preview_text">Le logo IP fourni sera appliqué automatiquement dans les PDF générés.</p>
                        </div>
                    </div> -->
                </section>

                <section class="ts-card">
                    <div class="ts-step-head">
                        <h2><span>3.</span> Lancer la génération</h2>
                        <p>Le ZIP contiendra un PDF par collaborateur sélectionné. Chaque mois de la période correspondra à une page dans son document.</p>
                    </div>
                    <button class="ts-generate-button" type="submit">Générer les feuilles de temps en PDF</button>
                </section>
            </form>
        @else
            <section class="ts-card ts-empty-card">
                <div class="ts-step-head">
                    <h2><span>1.</span> Charger une clé de répartition</h2>
                    <p>Importez un CSV pour afficher la table source, choisir les collaborateurs à inclure et définir les paramètres globaux.</p>
                </div>
            </section>
        @endif

        <div class="nc-timesheet-workbench">
            <aside>
                <h2>Guide rapide</h2>
                <div class="ts-help-list">
                    <details open>
                        <summary>Quel mode choisir ?</summary>
                        <p>Choisissez l'option 1 si vous avez un CSV à contrôler ligne par ligne. L'option 2 est plus adaptée si vous voulez générer rapidement des feuilles à partir des collaborateurs déjà actifs dans l'outil.</p>
                    </details>
                    <details>
                        <summary>Comment préparer le CSV ?</summary>
                        <p>Le fichier doit permettre d'identifier chaque collaborateur via son identifiant, son nom complet, ou ses champs prénom/nom. Les colonnes reconnues incluent notamment prénom, nom, entité, pays, fonction, rôle, fonds, ipde, catal, autre_projets, code_analytique, lieu, nom_signature, responsable_hierarchique et signature_droite_titre.</p>
                    </details>
                    <details>
                        <summary>Que vérifier après l'import du CSV ?</summary>
                        <p>Relisez les noms, entités, taux de répartition, codes analytiques, lieux et signataires. Décochez les collaborateurs à exclure du ZIP. Les modifications faites ici ne s'appliquent qu'à la génération en cours.</p>
                    </details>
                    <details>
                        <summary>Comment régler la période ?</summary>
                        <p>En mode CSV, la date de début et la date de fin s'appliquent à tout le lot. Chaque mois compris dans cette plage devient une page dans le PDF du collaborateur. Le nom du ZIP est proposé automatiquement, mais vous pouvez le modifier avant la génération.</p>
                    </details>
                    <details>
                        <summary>À quoi servent les dates exclues ?</summary>
                        <p>Ajoutez une date par ligne pour éviter qu'une signature tombe un jour fermé. Les formats acceptés sont YYYY-MM-DD et DD/MM/YYYY. Les week-ends sont déjà exclus automatiquement.</p>
                    </details>
                    <details>
                        <summary>Que contient le ZIP généré ?</summary>
                        <p>Le ZIP contient un PDF par collaborateur sélectionné. Chaque PDF regroupe toutes les pages mensuelles de la période demandée. Après une génération CSV réussie, le fichier chargé est vidé pour éviter une relance accidentelle.</p>
                    </details>
                    <details>
                        <summary>Quand utiliser la génération manuelle ?</summary>
                        <p>Utilisez-la pour un besoin ponctuel sans clé de répartition. Sélectionnez les collaborateurs, la période, l'entité affichée et les options PDF. Les informations manquantes seront reprises depuis les profils déjà enregistrés.</p>
                    </details>
                </div>
                <div class="nc-actions">
                    <a class="nc-ghost" href="{{ route('timesheets.history') }}">
                        <i data-lucide="history" class="nc-icon" aria-hidden="true"></i>
                        Voir l'historique
                    </a>
                </div>
            </aside>
        </div>
    </section>
@endsection
