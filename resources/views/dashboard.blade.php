@extends('layouts.app', [
    'title' => 'Nere Tools',
    'breadcrumbs' => [
        ['label' => 'Accueil'],
    ],
])

@section('content')
    @php
        $toolIcons = [
            'timesheets' => 'file-text',
            'conges' => 'calendar-range',
            'signatures' => 'file-signature',
        ];
    @endphp

    <section class="nc-page nc-home">
        <div class="nc-title-row">
            <div>
                <h1 class="nc-title">Vos outils</h1>
            </div>
        </div>

        <div class="nc-tool-list">
            @foreach ($tools as $tool)
                @php
                    $isActive = $tool->status === \App\Models\Tool::STATUS_ACTIVE;
                    $hasAccess = auth()->user()->canAccessTool($tool->slug);
                    $toolIcon = $toolIcons[$tool->slug] ?? 'layout-grid';
                @endphp

                @if ($isActive && $hasAccess)
                    <a class="nc-tool-entry" href="{{ $tool->route }}"><span class="nc-tool-symbol"><i data-lucide="{{ $toolIcon }}" class="nc-icon" aria-hidden="true"></i></span><h2>{{ $tool->name }}</h2><i data-lucide="arrow-up-right" class="nc-icon nc-tool-arrow" aria-hidden="true"></i></a>
                @else
                    <div class="nc-tool-entry is-unavailable" aria-disabled="true"><span class="nc-tool-symbol"><i data-lucide="{{ $toolIcon }}" class="nc-icon" aria-hidden="true"></i></span><h2>{{ $tool->name }}</h2><span class="nc-tool-state">{{ $isActive ? 'Accès non autorisé' : 'Bientôt disponible' }}</span></div>
                @endif
            @endforeach
        </div>
        @if (auth()->user()->canAccessAdmin())
            <a class="nc-home-admin" href="{{ route('admin.index') }}"><i data-lucide="settings" class="nc-icon" aria-hidden="true"></i>Administration<i data-lucide="arrow-right" class="nc-icon" aria-hidden="true"></i></a>
        @endif
    </section>
@endsection
