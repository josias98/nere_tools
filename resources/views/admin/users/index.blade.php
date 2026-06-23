@extends('layouts.app', [
    'title' => 'Utilisateurs - Nere Tools',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Administration', 'url' => route('admin.index')],
        ['label' => 'Utilisateurs'],
    ],
])

@section('content')
    <section class="nc-page admin-users">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Administration</p>
                <h1 class="nc-title">Utilisateurs</h1>
                <p class="nc-lead">Decidez simplement qui peut entrer dans le portail, quel role lui donner et quels modules lui ouvrir.</p>
            </div>
        </div>

        @include('leaves.partials.flash')

        <section class="nc-panel">
            <div class="nc-panel-heading">
                <div>
                    <h2>Ajouter un utilisateur</h2>
                    <p>Le compte sera reconnu a la connexion via son adresse Microsoft 365 professionnelle.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.users.store') }}" class="admin-user-form">
                @csrf
                <label class="nc-field">
                    <span>Nom</span>
                    <input name="name" value="{{ old('name') }}" required>
                </label>
                <label class="nc-field">
                    <span>Email Office 365</span>
                    <input name="email" type="email" value="{{ old('email') }}" required>
                </label>
                <label class="nc-field">
                    <span>Role</span>
                    <select name="role" required>
                        @foreach ($roles as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', \App\Models\User::ROLE_USER) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="nc-mini-check"><input type="checkbox" name="is_active" value="1" checked> Actif</label>
                <div class="admin-access-list">
                    @foreach ($tools as $tool)
                        <label class="nc-mini-check"><input type="checkbox" name="tool_ids[]" value="{{ $tool->id }}" @checked(old('tool_ids') && in_array($tool->id, old('tool_ids', [])))> {{ $tool->name }}</label>
                    @endforeach
                </div>
                <button class="nc-button" type="submit">
                    <i data-lucide="plus" class="nc-icon" aria-hidden="true"></i>
                    Creer le compte
                </button>
            </form>
        </section>

        <section class="nc-panel">
            <div class="nc-panel-heading">
                <div>
                    <h2>Comptes autorises</h2>
                    <p>Retrouvez ici les personnes deja ouvertes au portail et les modules qui leur sont attribues.</p>
                </div>
            </div>
            <div class="nc-table-wrap">
                <table class="nc-table admin-user-table">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Role</th>
                            <th>Departement</th>
                            <th>Applications</th>
                            <th>Etat</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td><strong>{{ $user->name }}</strong><br><span class="nc-muted">{{ $user->email }}</span></td>
                                <td>{{ $roles[$user->role] ?? $user->role }}</td>
                                <td>{{ $user->employee?->department?->name ?? '-' }}</td>
                                <td>
                                    @foreach ($tools as $tool)
                                        @if ($user->canAccessTool($tool->slug))
                                            <span class="nc-badge active">{{ $tool->name }}</span>
                                        @endif
                                    @endforeach
                                </td>
                                <td><span class="leave-status {{ $user->is_active ? 'is-approved' : 'is-cancelled' }}">{{ $user->is_active ? 'actif' : 'inactif' }}</span></td>
                                <td>
                                    <a class="nc-ghost" href="{{ route('admin.users.edit', $user) }}">
                                        <i data-lucide="settings" class="nc-icon" aria-hidden="true"></i>
                                        Modifier
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </section>
@endsection
