<div class="admin-workspace" data-admin-workspace>
    <aside class="admin-sidebar" aria-label="Navigation de l’administration">
        <div class="admin-sidebar-heading">
            <span class="admin-sidebar-mark" aria-hidden="true"><i data-lucide="settings-2"></i></span>
            <div><span>Administration</span><strong>Centre de pilotage</strong></div>
        </div>
        <nav>
            <a @class(['is-active' => request()->routeIs('admin.index')]) href="{{ route('admin.index') }}" data-tooltip-title="Vue d’ensemble" data-tooltip="Surveiller la santé des modules et ouvrir les réglages qui demandent votre attention."><i data-lucide="layout-dashboard"></i> Vue d’ensemble</a>
            <a @class(['is-active' => request()->routeIs('admin.users.*')]) href="{{ route('admin.users.index') }}" data-tooltip-title="Utilisateurs et accès" data-tooltip="Gérer les comptes, rôles et outils accessibles à chaque utilisateur."><i data-lucide="users"></i> Utilisateurs et accès</a>
            <a @class(['is-active' => request()->routeIs('admin.leaves.*') && ! request()->routeIs('admin.leaves.notifications.*')]) href="{{ route('admin.leaves.index') }}" data-tooltip-title="Pilotage des congés" data-tooltip="Consulter les demandes, les soldes, les types de congés et les paramètres RH."><i data-lucide="calendar-range"></i> Congés</a>
            <a href="{{ route('timesheets.index') }}" data-tooltip-title="Feuilles de temps" data-tooltip="Ouvrir la génération et le suivi des feuilles de temps mensuelles."><i data-lucide="file-text"></i> Feuilles de temps</a>
            <a href="{{ route('admin.leaves.index') }}#workflow" data-tooltip-title="Circuit de validation" data-tooltip="Définir qui valide chaque demande : responsable hiérarchique, RH / Admin-Finance, puis Direction générale."><i data-lucide="git-branch"></i> Workflows et validations</a>
            <a @class(['is-active' => request()->routeIs('admin.leaves.notifications.*')]) href="{{ route('admin.leaves.notifications.index') }}" data-tooltip-title="Notifications" data-tooltip="Contrôler les envois Office 365 et relancer uniquement les notifications éligibles."><i data-lucide="bell"></i> Notifications</a>
            <a href="{{ route('admin.leaves.index') }}#imports" data-tooltip-title="Imports et exports" data-tooltip="Initialiser les soldes par CSV ou exporter la situation RH filtrée."><i data-lucide="arrow-left-right"></i> Imports et exports</a>
        </nav>
    </aside>

    <main class="admin-content">
        {{ $slot }}
    </main>
</div>
