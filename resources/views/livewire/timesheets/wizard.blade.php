<section class="nc-page ts-assistant" x-data="timesheetWizard" data-timesheet-wizard wire:key="timesheet-wizard">
    <header class="ts-wizard-header">
        <div>
            <p class="nc-kicker">Module finance</p>
            <h1 class="nc-title">Créer des feuilles de temps</h1>
            <p class="nc-lead">Un parcours guidé, avec vérification avant toute génération.</p>
        </div>
        <a class="nc-ghost" href="{{ route('timesheets.history') }}">Voir l’historique</a>
    </header>

    <nav class="ts-stepper" aria-label="Progression">
        @foreach (['Méthode', 'Contexte', 'Données', 'Vérification', 'Génération', 'Livrable'] as $number => $label)
            <div @class(['is-current' => $step === $number + 1, 'is-complete' => $step > $number + 1]) @if($step === $number + 1) aria-current="step" @endif>
                <span>{{ $step > $number + 1 ? '✓' : $number + 1 }}</span><small>{{ $label }}</small>
            </div>
        @endforeach
    </nav>

    <div aria-live="polite" aria-atomic="true">
        @if ($notice)<div class="nc-alert is-success ts-toast" role="status">{{ $notice }}</div>@endif
        @error('generation')<div class="nc-alert ts-toast" role="alert">{{ $message }}</div>@enderror
    </div>

    <div class="ts-stage" wire:loading.class="is-loading">
        <div class="ts-loading" wire:loading.flex><span class="ts-spinner" aria-hidden="true"></span><span>Traitement en cours…</span></div>

        @if ($step === 1)
            @include('timesheets.steps.method')
        @elseif ($step === 2)
            @include('timesheets.steps.context')
        @elseif ($step === 3)
            @include('timesheets.steps.data')
        @elseif ($step === 4)
            @include('timesheets.steps.review')
        @elseif ($step === 5)
            @include('timesheets.steps.generate')
        @else
            @include('timesheets.steps.deliverable')
        @endif
    </div>

    @if ($step > 1 && $step < 6)
        <footer class="ts-wizard-actions">
            <button class="nc-ghost" type="button" wire:click="previous">Précédent</button>
            @if ($step < 5 && ! ($step === 3 && $method === 'csv'))
                <button class="nc-button" type="button" wire:click="next">Suivant</button>
            @endif
        </footer>
    @endif
</section>
