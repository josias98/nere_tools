@extends('layouts.app', ['title' => 'Acces refuse - Nere Tools'])

@section('content')
    <section class="flex min-h-screen items-center justify-center px-6 py-10">
        <div class="w-full max-w-lg rounded border border-[#ded7cf] bg-white p-8 shadow-sm">
            <div class="mb-4 inline-flex rounded bg-[#fff7ed] px-3 py-1 text-xs font-semibold uppercase text-[#8a3a06]">
                Acces refuse
            </div>
            <h1 class="text-2xl font-semibold text-[#522400]">Compte non autorise</h1>
            <p class="mt-3 text-sm leading-6 text-[#5f5249]">{{ $reason }}</p>

            @if ($email)
                <p class="mt-4 rounded bg-[#f8f6f3] px-4 py-3 text-sm text-[#5f5249]">
                    Compte Microsoft detecte : <span class="font-medium">{{ $email }}</span>
                </p>
            @endif

            <a href="{{ route('login') }}" class="mt-6 inline-flex rounded bg-[#522400] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#3d1b00]">
                Retour a la connexion
            </a>
        </div>
    </section>
@endsection
