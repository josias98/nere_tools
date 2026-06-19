@extends('layouts.app', ['title' => 'Connexion - Nere Tools'])

@section('content')
    <section class="flex min-h-screen items-center justify-center px-6 py-10">
        <div class="w-full max-w-md rounded border border-[#ded7cf] bg-white p-8 shadow-sm">
            <div class="mb-8">
                <div class="mb-4 flex size-12 items-center justify-center rounded bg-[#522400] text-base font-semibold text-white">NT</div>
                <h1 class="text-2xl font-semibold text-[#522400]">Nere Tools</h1>
                <p class="mt-2 text-sm leading-6 text-[#5f5249]">
                    Connectez-vous avec votre compte Microsoft 365 professionnel pour acceder au portail interne.
                </p>
            </div>

            @if ($errors->has('microsoft'))
                <div class="mb-5 rounded border border-[#e1580a]/30 bg-[#fff7ed] px-4 py-3 text-sm text-[#8a3a06]">
                    {{ $errors->first('microsoft') }}
                </div>
            @endif

            <a href="{{ route('auth.microsoft.redirect') }}" class="flex w-full items-center justify-center rounded bg-[#522400] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#3d1b00]">
                Continuer avec Microsoft 365
            </a>

            <p class="mt-5 text-xs leading-5 text-[#756960]">
                L'acces est reserve aux collaborateurs autorises dans l'application.
            </p>
        </div>
    </section>
@endsection
