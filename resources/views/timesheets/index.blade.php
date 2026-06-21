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

        <div class="nc-grid two">
            <section class="nc-panel">
                <h2>Generation</h2>
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
                        <tr><th scope="row">Signature</th><td>Cas particuliers appliques depuis la base.</td></tr>
                    </tbody>
                </table>
                <div class="nc-actions">
                    <a class="nc-ghost" href="{{ route('timesheets.history') }}">Voir l'historique</a>
                </div>
            </aside>
        </div>
    </section>
@endsection
