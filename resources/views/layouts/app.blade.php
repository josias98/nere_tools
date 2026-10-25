<!DOCTYPE html>
<html lang="{{ $lang ?? 'fr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'Nere Tools') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('brand/favicon.png') }}">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body>
    <a class="nc-skip-link" href="#main-content">Aller au contenu</a>
    <div class="nc-shell">
        @auth
            <header class="nc-header nc-app-header">
                <div class="nc-header-inner">
                    <a href="{{ route('dashboard') }}" class="nc-brand" aria-label="Nere Tools - Accueil">
                        <img class="nc-logo" src="{{ asset('brand/nere-capital-rgb.png') }}" alt="Nere Capital">
                    </a>

                    <nav class="nc-nav-links" aria-label="Navigation principale">
                        @foreach ([
                            ['route' => 'leaves.index', 'match' => 'leaves.*', 'tool' => 'conges', 'label' => 'Congés', 'icon' => 'calendar-range'],
                            ['route' => 'timesheets.index', 'match' => 'timesheets.*', 'tool' => 'timesheets', 'label' => 'Feuilles de temps', 'icon' => 'file-text'],
                            ['route' => 'signatures.index', 'match' => 'signatures.*', 'tool' => 'signatures', 'label' => 'Signature PDF', 'icon' => 'file-signature'],
                        ] as $item)
                            @if (auth()->user()->canAccessTool($item['tool']))
                                <a href="{{ route($item['route']) }}" @class(['is-active' => request()->routeIs($item['match'])]) @if (request()->routeIs($item['match'])) aria-current="page" @endif><i data-lucide="{{ $item['icon'] }}" class="nc-icon" aria-hidden="true"></i>{{ $item['label'] }}</a>
                            @endif
                        @endforeach
                        @if (auth()->user()->canAccessAdmin())
                            <a href="{{ route('admin.index') }}" @class(['is-active' => request()->routeIs('admin.*')]) @if (request()->routeIs('admin.*')) aria-current="page" @endif><i data-lucide="settings" class="nc-icon" aria-hidden="true"></i>Administration</a>
                        @endif
                    </nav>

                    <details class="nc-account">
                        <summary aria-label="Compte de {{ auth()->user()->name }}"><span class="nc-account-avatar" aria-hidden="true">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><span class="nc-account-name">{{ auth()->user()->name }}</span><i data-lucide="chevron-down" class="nc-icon" aria-hidden="true"></i></summary>
                        <div class="nc-account-menu"><strong>{{ auth()->user()->name }}</strong><span>{{ auth()->user()->email }}</span><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="nc-ghost"><i data-lucide="log-out" class="nc-icon" aria-hidden="true"></i>Déconnexion</button></form></div>
                    </details>
                </div>
            </header>
        @endauth

        <main id="main-content" tabindex="-1">
            @if (isset($breadcrumbs) && count($breadcrumbs) > 1)
                <nav class="nc-breadcrumb" aria-label="Fil d'Ariane">
                    @foreach ($breadcrumbs as $breadcrumb)
                        @if (! empty($breadcrumb['url']) && ! $loop->last)
                            <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['label'] }}</a>
                        @else
                            <span aria-current="{{ $loop->last ? 'page' : 'false' }}">{{ $breadcrumb['label'] }}</span>
                        @endif
                    @endforeach
                </nav>
            @endif

            @yield('content')
        </main>
    </div>
    <div class="nc-footer-bar" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span></div>
</body>
</html>
