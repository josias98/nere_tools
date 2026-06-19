@extends('layouts.app', ['title' => 'Nere Tools'])

@section('content')
    <section class="mx-auto max-w-6xl px-6 py-10">
        <div class="mb-8">
            <h1 class="text-3xl font-semibold text-[#522400]">Nere Tools</h1>
            <p class="mt-2 text-sm text-[#5f5249]">Bienvenue, {{ auth()->user()->name }}. Selectionnez un outil interne pour demarrer.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($tools as $tool)
                @php
                    $roles = $accessibleRoutes[$tool->route] ?? [];
                    $isActive = $tool->status === \App\Models\Tool::STATUS_ACTIVE;
                    $hasAccess = empty($roles) || auth()->user()->hasAnyRole($roles);
                @endphp

                <article class="rounded border border-[#ded7cf] bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-[#2f2925]">{{ $tool->name }}</h2>
                            <p class="mt-2 min-h-12 text-sm leading-6 text-[#5f5249]">{{ $tool->description }}</p>
                        </div>
                        <span class="rounded px-2 py-1 text-xs font-medium {{ $isActive ? 'bg-[#e8f1ed] text-[#28624d]' : 'bg-[#f2eee9] text-[#756960]' }}">
                            {{ $isActive ? 'Actif' : 'Bientot' }}
                        </span>
                    </div>

                    @if ($isActive && $hasAccess)
                        <a href="{{ $tool->route }}" class="mt-5 inline-flex rounded bg-[#e1580a] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#bd4705]">
                            Acceder
                        </a>
                    @elseif ($isActive)
                        <span class="mt-5 inline-flex rounded border border-[#cdc6c0] px-4 py-2 text-sm font-medium text-[#756960]">
                            Non autorise
                        </span>
                    @else
                        <span class="mt-5 inline-flex rounded border border-[#cdc6c0] px-4 py-2 text-sm font-medium text-[#756960]">
                            Bientot disponible
                        </span>
                    @endif
                </article>
            @endforeach
        </div>
    </section>
@endsection
