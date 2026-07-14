@extends('layouts.app', [
    'title' => 'Modifier utilisateur - Nere Tools',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Administration', 'url' => route('admin.index')],
        ['label' => 'Utilisateurs', 'url' => route('admin.users.index')],
        ['label' => 'Modifier'],
    ],
])

@section('content')
<x-admin.layout>
    <section class="nc-page admin-users">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Administration</p>
                <h1 class="nc-title">{{ $managedUser->name }}</h1>
                <p class="nc-lead">{{ $managedUser->email }}</p>
            </div>
        </div>

        @include('leaves.partials.flash')

        <section class="nc-panel">
            <form method="POST" action="{{ route('admin.users.update', $managedUser) }}" class="admin-user-form">
                @csrf
                @method('PUT')
                <label class="nc-field">
                    <span>Nom</span>
                    <input name="name" value="{{ old('name', $managedUser->name) }}" required>
                </label>
                <label class="nc-field">
                    <span>Email Office 365</span>
                    <input name="email" type="email" value="{{ old('email', $managedUser->email) }}" required>
                </label>
                <label class="nc-field">
                    <span>Role</span>
                    <select name="role" required>
                        @foreach ($roles as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', $managedUser->role) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="nc-mini-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $managedUser->is_active))> Actif</label>
                <div class="admin-access-list">
                    @foreach ($tools as $tool)
                        <label class="nc-mini-check"><input type="checkbox" name="tool_ids[]" value="{{ $tool->id }}" @checked($managedUser->canAccessTool($tool->slug))> {{ $tool->name }}</label>
                    @endforeach
                </div>
                <button class="nc-button" type="submit">
                    <i data-lucide="save" class="nc-icon" aria-hidden="true"></i>
                    Enregistrer
                </button>
            </form>
        </section>

        <section class="nc-panel">
            <div class="nc-panel-heading">
                <div>
                    <h2>Connexion Microsoft 365</h2>
                    <p>L'email ci-dessus doit correspondre au compte Microsoft 365 utilise au moment de la connexion.</p>
                </div>
            </div>
            <dl class="leave-detail-grid">
                <div><dt>Departement</dt><dd>{{ $managedUser->employee?->department?->name ?? 'Non relie a un collaborateur' }}</dd></div>
                <div><dt>Derniere connexion</dt><dd>{{ $managedUser->last_login_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
            </dl>
        </section>

        @if (! auth()->user()->is($managedUser))
            <form method="POST" action="{{ route('admin.users.destroy', $managedUser) }}" class="nc-actions">
                @csrf
                @method('DELETE')
                <button class="nc-ghost danger" type="submit">
                    <i data-lucide="trash-2" class="nc-icon" aria-hidden="true"></i>
                    Supprimer
                </button>
                <a class="nc-ghost" href="{{ route('admin.users.index') }}">
                    <i data-lucide="arrow-left" class="nc-icon" aria-hidden="true"></i>
                    Retour
                </a>
            </form>
        @endif
    </section>
</x-admin.layout>
@endsection
