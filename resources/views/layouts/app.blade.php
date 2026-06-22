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
    <div class="nc-shell">
        @auth
            <header class="nc-header">
                <div class="nc-header-inner">
                    <a href="{{ route('dashboard') }}" class="nc-brand" aria-label="Nere Tools - Accueil">
                        <img class="nc-logo" src="{{ asset('brand/nere-capital-rgb.png') }}" alt="Nere Capital">
                        <span class="nc-muted">Portail interne</span>
                    </a>

                    <nav class="nc-nav" aria-label="Navigation principale">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                        <a href="{{ route('leaves.index') }}">Conges</a>
                        <a href="{{ route('timesheets.index') }}">Feuilles de temps</a>
                        @if (auth()->user()->role === \App\Models\User::ROLE_ADMIN)
                            <a href="{{ route('admin.index') }}">Administration</a>
                        @endif
                    </nav>

                    <div class="nc-nav">
                        <div class="nc-user">
                            <strong>{{ auth()->user()->name }}</strong>
                            <span class="nc-muted">{{ auth()->user()->email }}</span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nc-ghost">Deconnexion</button>
                        </form>
                    </div>
                </div>
            </header>
        @endauth

        <main>
            @yield('content')
        </main>
    </div>
    <div class="nc-footer-bar" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span></div>
</body>
</html>
