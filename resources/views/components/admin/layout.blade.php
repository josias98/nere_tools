<div class="admin-workspace" data-admin-workspace>
    <aside class="admin-sidebar" aria-label="Navigation de l’administration">
        <div class="admin-sidebar-heading">
            <span class="admin-sidebar-mark" aria-hidden="true"><i data-lucide="settings-2"></i></span>
            <div><span>Administration</span><strong>Centre de pilotage</strong></div>
        </div>
        <nav>
            <a @class(['is-active' => request()->routeIs('admin.index')]) href="{{ route('admin.index') }}"><i data-lucide="layout-dashboard"></i> Vue d’ensemble</a>
            <a @class(['is-active' => request()->routeIs('admin.users.*')]) href="{{ route('admin.users.index') }}"><i data-lucide="users"></i> Utilisateurs et accès</a>
            <a @class(['is-active' => request()->routeIs('admin.leaves.*') && ! request()->routeIs('admin.leaves.notifications.*')]) href="{{ route('admin.leaves.index') }}"><i data-lucide="calendar-range"></i> Congés</a>
            <a href="{{ route('timesheets.index') }}"><i data-lucide="file-text"></i> Feuilles de temps</a>
            <a href="{{ route('admin.leaves.index') }}#workflow"><i data-lucide="git-branch"></i> Workflows et validations</a>
            <a @class(['is-active' => request()->routeIs('admin.leaves.notifications.*')]) href="{{ route('admin.leaves.notifications.index') }}"><i data-lucide="bell"></i> Notifications</a>
            <a href="{{ route('admin.leaves.index') }}#imports"><i data-lucide="arrow-left-right"></i> Imports et exports</a>
        </nav>
    </aside>

    <main class="admin-content">
        {{ $slot }}
    </main>
</div>
