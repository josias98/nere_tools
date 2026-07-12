<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'Nere Tools') }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body>
    <a class="nc-skip-link" href="#main-content">Aller au contenu</a>
    <div class="nc-shell">
        @auth
            <header class="nc-header">
                <div class="nc-header-inner">
                    <a href="{{ route('dashboard') }}" class="nc-brand" aria-label="Nere Tools - Accueil">
                        <img class="nc-logo" src="{{ asset('brand/nere-capital-rgb.png') }}" alt="Nere Capital">
                    </a>

                    <nav class="nc-nav-links" aria-label="Navigation principale">
                        <a href="{{ route('dashboard') }}" @class(['is-active' => request()->routeIs('dashboard')]) @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                            <i data-lucide="layout-grid" class="nc-icon" aria-hidden="true"></i>
                            Accueil
                        </a>
                        @if (auth()->user()->canAccessTool('conges'))
                            <a href="{{ route('leaves.index') }}" @class(['is-active' => request()->routeIs('leaves.*') && ! request()->routeIs('admin.leaves.*')]) @if(request()->routeIs('leaves.*') && ! request()->routeIs('admin.leaves.*')) aria-current="page" @endif>
                                <i data-lucide="calendar-range" class="nc-icon" aria-hidden="true"></i>
                                Congés
                            </a>
                        @endif
                        @if (auth()->user()->canAccessTool('timesheets'))
                            <a href="{{ route('timesheets.index') }}" @class(['is-active' => request()->routeIs('timesheets.*')]) @if(request()->routeIs('timesheets.*')) aria-current="page" @endif>
                                <i data-lucide="file-text" class="nc-icon" aria-hidden="true"></i>
                                Feuilles de temps
                            </a>
                        @endif
                        @if (auth()->user()->canAccessAdmin())
                            <a href="{{ route('admin.index') }}" @class(['is-active' => request()->routeIs('admin.*')]) @if(request()->routeIs('admin.*')) aria-current="page" @endif>
                                <i data-lucide="settings" class="nc-icon" aria-hidden="true"></i>
                                Administration
                            </a>
                        @endif
                    </nav>

                    <div class="nc-nav">
                        <div class="nc-user">
                            <strong>{{ auth()->user()->name }}</strong>
                            <span class="nc-muted">{{ auth()->user()->role }}</span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nc-ghost">
                                <i data-lucide="log-out" class="nc-icon" aria-hidden="true"></i>
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </header>
        @endauth

        <main id="main-content" tabindex="-1">
            @isset($breadcrumbs)
                <nav class="nc-breadcrumb" aria-label="Fil d'Ariane">
                    @foreach ($breadcrumbs as $breadcrumb)
                        @if (! empty($breadcrumb['url']) && ! $loop->last)
                            <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['label'] }}</a>
                        @else
                            <span aria-current="{{ $loop->last ? 'page' : 'false' }}">{{ $breadcrumb['label'] }}</span>
                        @endif
                    @endforeach
                </nav>
            @endisset

            @yield('content')
        </main>
    </div>
    <div class="nc-footer-bar" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span></div>
</body>
</html>
