@extends('layouts.app', [
    'title' => 'Administration congés - Nere Tools',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Administration', 'url' => route('admin.index')],
        ['label' => 'Congés'],
    ],
])

@section('content')
    <section class="nc-page leave-workbench">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Administration</p>
                <h1 class="nc-title">Congés</h1>
                <p class="nc-lead">Complétez les emails Microsoft, configurez les validateurs et importez les soldes initiaux.</p>
            </div>
        </div>

        @include('leaves.partials.flash')

        @if (session('import_report'))
            <section class="nc-alert is-success">
                @php($report = session('import_report'))
                Import CSV : {{ $report['read'] }} lignes lues,
                {{ $report['matched'] }} collaborateurs matchés.
                @if (count($report['errors']))
                    Erreurs : {{ implode(' | ', $report['errors']) }}
                @endif
            </section>
        @endif

        <div class="leave-admin-grid">
            <section class="nc-panel">
                <div class="nc-panel-heading">
                    <div>
                        <h2>Paramètres</h2>
                        <p>Règles utilisées dans le calcul des soldes.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.leaves.settings.update') }}" class="nc-form-grid">
                    @csrf
                    @method('PUT')
                    <label class="nc-field">
                        <span>Jours acquis par mois</span>
                        <input type="number" step="0.01" name="monthly_accrual_days" value="{{ old('monthly_accrual_days', $settings['monthly_accrual_days']->value ?? '2.5') }}" required>
                    </label>
                    <label class="nc-field">
                        <span>Politique d'acquisition</span>
                        <select name="accrual_policy" required>
                            @foreach (['end_of_month' => 'Fin de mois', 'start_of_month' => 'Début de mois', 'prorated' => 'Prorata'] as $value => $label)
                                <option value="{{ $value }}" @selected(($settings['accrual_policy']->value ?? 'end_of_month') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button class="nc-button" type="submit">Enregistrer</button>
                </form>
            </section>

            <section class="nc-panel">
                <div class="nc-panel-heading">
                    <div>
                        <h2>Import CSV</h2>
                        <p>Point de départ des soldes historiques.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.leaves.import') }}" enctype="multipart/form-data" class="nc-form-grid">
                    @csrf
                    <label class="nc-field">
                        <span>Date de référence</span>
                        <input type="date" name="reference_date" value="2025-07-31" required>
                    </label>
                    <label class="nc-field">
                        <span>Fichier CSV</span>
                        <input type="file" name="file" accept=".csv,text/csv" required>
                    </label>
                    <button class="nc-button" type="submit">Importer</button>
                </form>
            </section>
        </div>

        <section class="nc-panel">
            <div class="nc-panel-heading">
                <div>
                    <h2>Validateurs</h2>
                    <p>Les validateurs initiaux peuvent être modifiés sans toucher au code.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.leaves.validators.store') }}" class="leave-filter">
                @csrf
                <label class="nc-field">
                    <span>Collaborateur</span>
                    <select name="employee_id" required>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name() }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="nc-field">
                    <span>Périmètre</span>
                    <select name="scope" data-scope-select required>
                        <option value="global">Global</option>
                        <option value="department">Département</option>
                        <option value="employee">Collaborateur</option>
                    </select>
                </label>
                <label class="nc-field">
                    <span>Département</span>
                    <select name="department_id">
                        <option value="">-</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="nc-field">
                    <span>Collaborateur ciblé</span>
                    <select name="target_employee_id">
                        <option value="">-</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name() }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="nc-mini-check"><input type="checkbox" name="notify_by_email" value="1" checked> Notifier</label>
                <button class="nc-button" type="submit">Ajouter</button>
            </form>

            <div class="nc-table-wrap">
                <table class="nc-table">
                    <thead>
                        <tr>
                            <th>Validateur</th>
                            <th>Périmètre</th>
                            <th>Cible</th>
                            <th>Etat</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($validators as $validator)
                            <tr>
                                <td>{{ $validator->employee?->name() }}</td>
                                <td>{{ $validator->scope }}</td>
                                <td>{{ $validator->department?->name ?? $validator->targetEmployee?->name() ?? 'Toutes les demandes' }}</td>
                                <td><span class="leave-status {{ $validator->is_active ? 'is-approved' : 'is-cancelled' }}">{{ $validator->is_active ? 'actif' : 'inactif' }}</span></td>
                                <td>
                                    <form method="POST" action="{{ route('admin.leaves.validators.update', $validator) }}" class="leave-inline-form">
                                        @csrf
                                        @method('PUT')
                                        <label><input type="checkbox" name="notify_by_email" value="1" @checked($validator->notify_by_email)> Email</label>
                                        <label><input type="checkbox" name="is_active" value="1" @checked($validator->is_active)> Actif</label>
                                        <button class="nc-ghost" type="submit">Sauver</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="nc-panel">
            <div class="nc-panel-heading">
                <div>
                    <h2>Collaborateurs</h2>
                    <p>Un email Microsoft est nécessaire pour relier le compte connecté au profil RH.</p>
                </div>
            </div>

            @foreach ($employees as $employee)
                <form id="employee-leave-form-{{ $employee->id }}" method="POST" action="{{ route('admin.leaves.employees.update', $employee) }}">
                    @csrf
                    @method('PUT')
                </form>
            @endforeach

            <div class="nc-table-wrap">
                <table class="nc-table leave-admin-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Département</th>
                            <th>Email Microsoft</th>
                            <th>Embauche</th>
                            <th>Eligible</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $employee)
                            <tr>
                                <td>{{ $employee->name() }}</td>
                                <td>{{ $employee->department?->name ?? '-' }}</td>
                                <td><input form="employee-leave-form-{{ $employee->id }}" name="email" type="email" value="{{ $employee->email }}"></td>
                                <td><input form="employee-leave-form-{{ $employee->id }}" name="hire_date" type="date" value="{{ $employee->hire_date?->format('Y-m-d') }}"></td>
                                <td><input form="employee-leave-form-{{ $employee->id }}" name="leave_eligible" type="checkbox" value="1" @checked($employee->leave_eligible)></td>
                                <td><button form="employee-leave-form-{{ $employee->id }}" class="nc-ghost" type="submit">Sauver</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </section>
@endsection
