@extends('layouts.app', ['title' => 'Administration - Nere Tools'])

@section('content')
    <section class="nc-page">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Administration</p>
                <h1 class="nc-title">Parametres du portail</h1>
                <p class="nc-lead">Gestion des acces, collaborateurs et modules internes.</p>
            </div>
        </div>

        <div class="nc-grid two">
            <section class="nc-card">
                <h2>Utilisateurs autorises</h2>
                <p>La connexion Microsoft ne suffit pas : l'email doit rester autorise ici.</p>
                <div class="nc-actions"><span class="nc-ghost" aria-disabled="true">CRUD phase admin</span></div>
            </section>
            <section class="nc-card">
                <h2>Collaborateurs</h2>
                <p>Base des salaries, coefficients et regles de signature.</p>
                <div class="nc-actions"><span class="nc-ghost" aria-disabled="true">Ecran phase Timesheets</span></div>
            </section>
        </div>
    </section>
@endsection
