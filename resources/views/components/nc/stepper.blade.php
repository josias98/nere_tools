@props(['steps', 'current', 'completed' => []])

<nav class="nc-stepper" aria-label="Progression de la demande">
    <p class="nc-stepper-status">Etape {{ $current }} sur {{ count($steps) }}</p>
    <ol>
        @foreach ($steps as $step)
            @php
                $isCurrent = $current === $step['value'];
                $isComplete = in_array($step['value'], $completed, true);
            @endphp
            <li @class(['is-current' => $isCurrent, 'is-complete' => $isComplete]) @if($isCurrent) aria-current="step" @endif>
                @if ($isComplete && ! $isCurrent)
                    <button type="button" wire:click="editStep({{ $step['value'] }})">
                        <span>{{ $step['value'] }}</span>
                        <strong>{{ $step['label'] }}</strong>
                    </button>
                @else
                    <span aria-hidden="true">{{ $step['value'] }}</span>
                    <strong>{{ $step['label'] }}</strong>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
