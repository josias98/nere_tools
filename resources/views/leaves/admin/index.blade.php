@extends('layouts.app', [
    'title' => 'Administration conges - Nere Tools',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Administration', 'url' => route('admin.index')],
        ['label' => 'Conges'],
    ],
])

@section('content')
    <section class="nc-page leave-workbench">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Administration</p>
                <h1 class="nc-title">Conges</h1>
                <p class="nc-lead">Completez les emails Microsoft, configurez les validateurs et surveillez le circuit de notification.</p>
            </div>
        </div>

        @include('leaves.partials.flash')

        @if (session('import_report'))
            <section class="nc-alert is-success">
                @php($report = session('import_report'))
                Import CSV : {{ $report['read'] }} lignes lues,
                {{ $report['matched'] }} collaborateurs matches.
                @if (count($report['errors']))
                    Erreurs : {{ implode(' | ', $report['errors']) }}
                @endif
            </section>
        @endif

        <div class="leave-admin-grid leave-admin-grid--top">
            <section class="nc-panel">
                <div class="nc-panel-heading">
                    <div>
                        <h2>Parametres</h2>
                        <p>Regles utilisees dans le calcul des soldes.</p>
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
                            @foreach (['end_of_month' => 'Fin de mois', 'start_of_month' => 'Debut de mois', 'prorated' => 'Prorata'] as $value => $label)
                                <option value="{{ $value }}" @selected(($settings['accrual_policy']->value ?? 'end_of_month') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button class="nc-button" type="submit">
                        <i data-lucide="save" class="nc-icon" aria-hidden="true"></i>
                        Enregistrer
                    </button>
                </form>
            </section>

            <section class="nc-panel">
                <div class="nc-panel-heading">
                    <div>
                        <h2>Notifications Office 365</h2>
                        <p>Consultez les logs, erreurs et relances du mecanisme email.</p>
                    </div>
                </div>
                <p>La page de debug montre les statuts `queued`, `sent`, `failed` et `skipped`, avec les erreurs Graph et les relances sures.</p>
                <div class="nc-actions">
                    <a href="{{ route('admin.leaves.notifications.index') }}" class="nc-button is-secondary">
                        <i data-lucide="bell" class="nc-icon" aria-hidden="true"></i>
                        Ouvrir les notifications
                    </a>
                </div>
            </section>
        </div>

        <section class="nc-panel">
            <div class="nc-panel-heading">
                <div>
                    <h2>Import CSV</h2>
                    <p>Point de depart des soldes historiques.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.leaves.import') }}" enctype="multipart/form-data" class="nc-form-grid">
                @csrf
                <label class="nc-field">
                    <span>Date de reference</span>
                    <input type="date" name="reference_date" value="2025-07-31" required>
                </label>
                <label class="nc-field">
                    <span>Fichier CSV</span>
                    <input type="file" name="file" accept=".csv,text/csv" required>
                </label>
                <button class="nc-button" type="submit">
                    <i data-lucide="upload" class="nc-icon" aria-hidden="true"></i>
                    Importer
                </button>
            </form>
        </section>

        <section class="nc-panel">
            <div class="nc-panel-heading">
                <div>
                    <h2>Validateurs</h2>
                    <p>Les validateurs initiaux peuvent etre modifies sans toucher au code.</p>
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
                    <span>Perimetre</span>
                    <select name="scope" data-scope-select required>
                        <option value="global">Global</option>
                        <option value="department">Departement</option>
                        <option value="employee">Collaborateur</option>
                    </select>
                </label>
                <label class="nc-field">
                    <span>Departement</span>
                    <select name="department_id">
                        <option value="">-</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="nc-field">
                    <span>Collaborateur cible</span>
                    <select name="target_employee_id">
                        <option value="">-</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name() }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="nc-mini-check"><input type="checkbox" name="notify_by_email" value="1" checked> Notifier</label>
                <button class="nc-button" type="submit">
                    <i data-lucide="plus" class="nc-icon" aria-hidden="true"></i>
                    Ajouter
                </button>
            </form>

            <div class="nc-table-wrap">
                <table class="nc-table">
                    <thead>
                        <tr>
                            <th>Validateur</th>
                            <th>Perimetre</th>
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
                                        <button class="nc-ghost" type="submit">
                                            <i data-lucide="save" class="nc-icon" aria-hidden="true"></i>
                                            Sauver
                                        </button>
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
                    <p>Un email Microsoft est necessaire pour relier le compte connecte au profil RH.</p>
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
                            <th>Departement</th>
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
                                <td>
                                    <button form="employee-leave-form-{{ $employee->id }}" class="nc-ghost" type="submit">
                                        <i data-lucide="mail" class="nc-icon" aria-hidden="true"></i>
                                        Sauver
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </section>
@endsection
