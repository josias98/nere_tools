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
<body class="min-h-screen bg-[#f8f6f3] font-sans text-[#2f2925] antialiased">
    <div class="min-h-screen">
        @auth
            <header class="border-b border-[#ded7cf] bg-white">
                <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-6 py-4">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded bg-[#522400] text-sm font-semibold text-white">NT</span>
                        <span>
                            <span class="block text-sm font-semibold text-[#522400]">Nere Tools</span>
                            <span class="block text-xs text-[#756960]">Portail interne</span>
                        </span>
                    </a>
                    <div class="flex items-center gap-4">
                        <div class="hidden text-right sm:block">
                            <div class="text-sm font-medium">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-[#756960]">{{ auth()->user()->email }}</div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded border border-[#cdc6c0] px-3 py-2 text-sm font-medium text-[#522400] transition hover:border-[#522400] hover:bg-[#f8f6f3]">
                                Deconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </header>
        @endauth

        <main>
            @yield('content')
        </main>
    </div>
</body>
</html>
