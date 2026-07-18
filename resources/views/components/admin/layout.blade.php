<div class="admin-workspace" data-admin-workspace>
    @php
        $leaveSection = request()->routeIs('admin.leaves.notifications.*')
            ? 'notifications'
            : request()->query('section', 'overview');
        $leaveSection = in_array($leaveSection, ['overview', 'requests', 'people', 'workflow', 'settings', 'rules', 'calendar', 'data', 'notifications'], true)
            ? $leaveSection
            : 'overview';
    @endphp
    <aside class="admin-sidebar" aria-label="Navigation de l’administration">
        <div class="admin-sidebar-heading">
            <span class="admin-sidebar-mark" aria-hidden="true"><i data-lucide="settings-2"></i></span>
            <div><span>Administration</span><strong>Centre de pilotage</strong></div>
        </div>
        <nav>
            <a @class(['is-active' => request()->routeIs('admin.index')]) href="{{ route('admin.index') }}" data-tooltip-title="Vue d’ensemble" data-tooltip="Surveiller la santé des modules et ouvrir les réglages qui demandent votre attention."><i data-lucide="layout-dashboard"></i> Vue d’ensemble</a>
            <a @class(['is-active' => request()->routeIs('admin.users.*')]) href="{{ route('admin.users.index') }}" data-tooltip-title="Utilisateurs et accès" data-tooltip="Gérer les comptes, rôles et outils accessibles à chaque utilisateur."><i data-lucide="users"></i> Utilisateurs et accès</a>
            <div class="admin-sidebar-section">
                <a @class(['is-active' => request()->routeIs('admin.leaves.*')]) href="{{ route('admin.leaves.index') }}" data-tooltip-title="Pilotage des congés" data-tooltip="Consulter les demandes, les soldes, les types de congés et les paramètres RH."><i data-lucide="calendar-range"></i> Congés</a>
                @if (request()->routeIs('admin.leaves.*'))
                    <div class="admin-sidebar-subnav" role="group" aria-label="Rubriques du module Congés">
                        <details @if (in_array($leaveSection, ['overview', 'requests'], true)) open @endif>
                            <summary>Pilotage</summary>
                            <div>
                                <a @class(['is-active' => $leaveSection === 'overview']) href="{{ route('admin.leaves.index', ['section' => 'overview']) }}">Synthèse</a>
                                <a @class(['is-active' => $leaveSection === 'requests']) href="{{ route('admin.leaves.index', ['section' => 'requests']) }}">Demandes</a>
                            </div>
                        </details>
                        <details @if (in_array($leaveSection, ['people', 'workflow'], true)) open @endif>
                            <summary>Organisation</summary>
                            <div>
                                <a @class(['is-active' => $leaveSection === 'people']) href="{{ route('admin.leaves.index', ['section' => 'people']) }}">Collaborateurs</a>
                                <a @class(['is-active' => $leaveSection === 'workflow']) href="{{ route('admin.leaves.index', ['section' => 'workflow']) }}">Circuit de validation</a>
                            </div>
                        </details>
                        <details @if (in_array($leaveSection, ['settings', 'rules', 'calendar', 'data', 'notifications'], true)) open @endif>
                            <summary>Configuration</summary>
                            <div>
                                <a @class(['is-active' => $leaveSection === 'settings']) href="{{ route('admin.leaves.index', ['section' => 'settings']) }}">Paramètres de calcul</a>
                                <a @class(['is-active' => $leaveSection === 'rules']) href="{{ route('admin.leaves.index', ['section' => 'rules']) }}">Types de congé</a>
                                <a @class(['is-active' => $leaveSection === 'calendar']) href="{{ route('admin.leaves.index', ['section' => 'calendar']) }}">Jours fériés</a>
                                <a @class(['is-active' => $leaveSection === 'data']) href="{{ route('admin.leaves.index', ['section' => 'data']) }}">Imports et exports</a>
                                <a @class(['is-active' => $leaveSection === 'notifications']) href="{{ route('admin.leaves.notifications.index') }}">Notifications</a>
                            </div>
                        </details>
                    </div>
                @endif
            </div>
            <a href="{{ route('timesheets.index') }}" data-tooltip-title="Feuilles de temps" data-tooltip="Ouvrir la génération et le suivi des feuilles de temps mensuelles."><i data-lucide="file-text"></i> Feuilles de temps</a>
        </nav>
    </aside>

    <main class="admin-content">
        {{ $slot }}
    </main>
</div>
