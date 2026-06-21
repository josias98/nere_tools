@extends('layouts.app', ['title' => 'Feuilles de temps - Nere Tools'])

@section('content')
    <section class="nc-page">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Module finance</p>
                <h1 class="nc-title">Feuilles de temps</h1>
                <p class="nc-lead">Preparez une generation mensuelle ou trimestrielle. Les controles et PDF arrivent en phase 3.</p>
            </div>
        </div>

        <div class="nc-grid two">
            <section class="nc-panel">
                <h2>Parametres de generation</h2>
                <p>Formulaire pret pour brancher la logique metier.</p>
                <form class="nc-form-grid">
                    <div class="nc-field">
                        <label for="period_start">Date de debut</label>
                        <input id="period_start" type="date" disabled>
                    </div>
                    <div class="nc-field">
                        <label for="period_end">Date de fin</label>
                        <input id="period_end" type="date" disabled>
                    </div>
                    <div class="nc-field">
                        <label for="employees">Collaborateurs</label>
                        <select id="employees" disabled><option>Selection a venir</option></select>
                    </div>
                </form>
                <div class="nc-actions"><button class="nc-button" disabled>Generer</button></div>
            </section>

            <section class="nc-panel">
                <h2>Etat du module</h2>
                <table class="nc-table">
                    <tbody>
                        <tr><th scope="row">Acces</th><td>Admin, finance, direction</td></tr>
                        <tr><th scope="row">PDF</th><td>Phase suivante</td></tr>
                        <tr><th scope="row">ZIP</th><td>Phase suivante</td></tr>
                    </tbody>
                </table>
            </section>
        </div>
    </section>
@endsection
