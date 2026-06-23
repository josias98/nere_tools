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
                        <!-- <span class="nc-muted">Portail interne</span> -->
                    </a>

                    <div class="nc-nav">
                        
                        <div class="nc-user">
                            <strong>{{ auth()->user()->name }}</strong>
                            <span class="nc-muted">{{ auth()->user()->email }}</span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nc-ghost">
                                <i data-lucide="log-out" class="nc-icon" aria-hidden="true"></i>
                                Deconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </header>
        @endauth

        <main>
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
