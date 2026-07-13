<section class="nc-page ts-assistant ts-workbench" x-data="timesheetWizard" data-timesheet-wizard wire:key="timesheet-wizard">
    <header class="ts-wizard-header">
        <div>
            <p class="nc-kicker">Finance · Feuilles de temps</p>
            <h1 class="nc-title">Créer des feuilles de temps</h1>
            <p class="nc-lead">Préparez le lot, contrôlez les répartitions, puis générez les documents.</p>
        </div>
        <a class="nc-ghost" href="{{ route('timesheets.history') }}">Voir l’historique</a>
    </header>

    <div aria-live="polite" aria-atomic="true">
        @if ($notice)<div class="nc-alert is-success ts-toast" role="status">{{ $notice }}</div>@endif
        @error('generation')<div class="nc-alert ts-toast" role="alert">{{ $message }}</div>@enderror
    </div>

    <div class="ts-workbench-shell">
    <nav class="ts-stepper" aria-label="Progression du lot">
        @foreach (['Méthode', 'Contexte', 'Données', 'Vérification', 'Génération', 'Livrable'] as $number => $label)
            <div @class(['is-current' => $step === $number + 1, 'is-complete' => $step > $number + 1]) @if($step === $number + 1) aria-current="step" @endif>
                <span>{{ str_pad($number + 1, 2, '0', STR_PAD_LEFT) }}</span><small>{{ $label }}</small>
            </div>
        @endforeach
        <p class="ts-stepper-status"><span>Étape {{ $step }} / 6</span><strong>{{ ['Choisir le point de départ', 'Cadre du lot', 'Constituer les données', 'Contrôler les répartitions', 'Confirmer le traitement', 'Récupérer les fichiers'][$step - 1] }}</strong></p>
    </nav>

    <div class="ts-stage" wire:loading.class="is-loading" aria-busy="{{ $this->getErrorBag()->isNotEmpty() ? 'false' : 'false' }}">
        <div class="ts-loading" wire:loading.flex><span class="ts-spinner" aria-hidden="true"></span><span>Traitement en cours…</span></div>

        @if ($step === 1)
            <div wire:transition.opacity.duration.180ms>@include('timesheets.steps.method')</div>
        @elseif ($step === 2)
            <div wire:transition.opacity.duration.180ms>@include('timesheets.steps.context')</div>
        @elseif ($step === 3)
            <div wire:transition.opacity.duration.180ms>@include('timesheets.steps.data')</div>
        @elseif ($step === 4)
            <div wire:transition.opacity.duration.180ms>@include('timesheets.steps.review')</div>
        @elseif ($step === 5)
            <div wire:transition.opacity.duration.180ms>@include('timesheets.steps.generate')</div>
        @else
            <div wire:transition.opacity.duration.180ms>@include('timesheets.steps.deliverable')</div>
        @endif
    </div></div>

    @if ($step > 1 && $step < 6)
        <footer class="ts-wizard-actions" aria-label="Navigation dans l’assistant">
            <button class="nc-ghost" type="button" wire:click="previous">Précédent</button>
            @if ($step < 5 && ! ($step === 3 && $method === 'csv'))
                <button class="nc-button" type="button" wire:click="next">Suivant</button>
            @endif
        </footer>
    @endif
</section>
