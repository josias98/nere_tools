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
                <h1 class="nc-title">Congés</h1>
                <p class="nc-lead">Configurez le module, suivez les demandes et intervenez rapidement lorsqu’une action est nécessaire.</p>
            </div>
        </div>

        @include('leaves.partials.flash')

        <nav class="leave-admin-nav" aria-label="Sections de l’administration des congés">
            <a href="#leave-settings">Paramètres</a>
            <a href="#leave-import">Import des soldes</a>
            <a href="#leave-requests">Demandes</a>
            <a href="#leave-validators">Validateurs</a>
            <a href="#leave-employees">Collaborateurs</a>
        </nav>

        @if (session('import_report'))
            <section class="nc-alert is-success">
                @php($report = session('import_report'))
                Import CSV : {{ $report['rows_read'] ?? $report['read'] }} lignes lues,
                {{ $report['rows_imported'] ?? $report['matched'] }} creees,
                {{ $report['rows_updated'] ?? 0 }} mises a jour,
                {{ $report['rows_skipped'] ?? 0 }} ignorees.
                @if (count($report['errors']))
                    <ul>
                        @foreach ($report['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endif

        <div class="leave-admin-grid leave-admin-grid--top">
            <section class="nc-panel" id="leave-settings">
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
                            @foreach (['end_of_month' => 'Fin de mois', 'start_of_month' => 'Debut de mois', 'prorated' => 'Prorata'] as $value => $label)
                                <option value="{{ $value }}" @selected(($settings['accrual_policy']->value ?? 'end_of_month') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="nc-field">
                        <span>Signataire fallback</span>
                        <input type="text" name="LEAVE_CERTIFICATE_SIGNATORY_NAME" value="{{ old('LEAVE_CERTIFICATE_SIGNATORY_NAME', $settings['LEAVE_CERTIFICATE_SIGNATORY_NAME']->value ?? config('leaves.certificate_signatory_name')) }}">
                    </label>
                    <label class="nc-field">
                        <span>Titre fallback</span>
                        <input type="text" name="LEAVE_CERTIFICATE_SIGNATORY_TITLE" value="{{ old('LEAVE_CERTIFICATE_SIGNATORY_TITLE', $settings['LEAVE_CERTIFICATE_SIGNATORY_TITLE']->value ?? config('leaves.certificate_signatory_title')) }}">
                    </label>
                    <p>Signature image : {{ $signatureExists ? 'presente' : 'absente (facultative)' }}</p>
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

        <section class="nc-panel" id="leave-import">
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
                <a href="{{ route('admin.leaves.import.template') }}" class="nc-button is-secondary">
                    <i data-lucide="download" class="nc-icon" aria-hidden="true"></i>
                    Modele CSV
                </a>
            </form>
            <p>Colonnes recommandees : employee_id, email, display_name, date_embauche, total_acquis, total_pris, solde_restant, notes. Virgule ou point-virgule acceptes.</p>
        </section>

        <section class="nc-panel" id="leave-requests">
            <div class="nc-panel-heading">
                <div>
                    <h2>Suivi des demandes</h2>
                    <p>Etat courant, etape, document final et dernier horodatage de decision.</p>
                </div>
            </div>
            <form method="GET" action="{{ route('admin.leaves.index') }}" class="leave-filter leave-filter--admin">
                <label class="nc-field">
                    <span>Statut</span>
                    <select name="status">
                        <option value="">Tous</option>
                        @foreach (\App\Models\LeaveRequest::statusLabels() as $status => $label)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="nc-field">
                    <span>Etape</span>
                    <select name="step">
                        <option value="">Toutes</option>
                        <option value="supervisor" @selected($filters['step'] === 'supervisor')>Superviseur</option>
                        <option value="hr" @selected($filters['step'] === 'hr')>RH / Admin-Finance</option>
                        <option value="dg" @selected($filters['step'] === 'dg')>DG / Direction</option>
                    </select>
                </label>
                <label class="nc-field">
                    <span>Departement</span>
                    <select name="department_id">
                        <option value="">Tous</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected($filters['department_id'] == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="nc-field">
                    <span>Collaborateur</span>
                    <select name="employee_id">
                        <option value="">Tous</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected($filters['employee_id'] == $employee->id)>{{ $employee->name() }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="nc-field">
                    <span>Debut apres</span>
                    <input type="date" name="from" value="{{ $filters['from'] }}">
                </label>
                <label class="nc-field">
                    <span>Fin avant</span>
                    <input type="date" name="to" value="{{ $filters['to'] }}">
                </label>
                <button class="nc-button" type="submit">
                    <i data-lucide="filter" class="nc-icon" aria-hidden="true"></i>
                    Filtrer
                </button>
            </form>

            <div class="nc-table-wrap">
                <table class="nc-table">
                    <thead>
                        <tr>
                            <th>Demandeur</th>
                            <th>Type</th>
                            <th>Periode</th>
                            <th>Jours</th>
                            <th>Statut</th>
                            <th>Etape</th>
                            <th>Groupe attendu</th>
                            <th>Derniere decision</th>
                            <th>PDF</th>
                            <th>Audit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $request)
                            @php($lastDecision = $request->approvals->whereNotNull('decided_at')->sortByDesc('decided_at')->first())
                            <tr>
                                <td>{{ $request->employee?->name() }}</td>
                                <td>{{ $request->leaveType?->name ?? '-' }}</td>
                                <td>{{ $request->start_date->format('d/m/Y') }} au {{ $request->end_date->format('d/m/Y') }}</td>
                                <td>{{ number_format($request->requested_days, 2) }}</td>
                                <td><span class="leave-status is-{{ $request->status }}">{{ $request->statusLabel() }}</span></td>
                                <td>{{ $request->currentApproval?->step_label ?? '-' }}</td>
                                <td>{{ $request->currentApproval?->step_label ?? '-' }}</td>
                                <td>{{ $lastDecision?->decided_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td>{{ $request->document ? 'oui' : 'non' }}</td>
                                <td><a class="nc-link" href="{{ route('leaves.show', $request->uuid) }}">Ouvrir</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $requests->links() }}
        </section>

        <section class="nc-panel" id="leave-validators">
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
                    <span>Etape</span>
                    <select name="step_key" required>
                        <option value="supervisor">Superviseur</option>
                        <option value="hr">RH / Admin-Finance</option>
                        <option value="dg">DG / Direction</option>
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
                            <th>Etape</th>
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
                                <td>{{ $validator->stepLabel() }}</td>
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

        <section class="nc-panel" id="leave-employees">
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
