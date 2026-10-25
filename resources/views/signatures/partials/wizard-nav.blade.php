<nav class="sig-wizard-rail" aria-label="Étapes de la signature">
    <ol class="sig-wizard-path">
        @foreach ([1 => 'Ajouter', 2 => 'Relire', 3 => 'Signer', 4 => 'Télécharger'] as $number => $label)
            <li data-signature-indicator="{{ $number }}" @class(['is-current' => $step === $number, 'is-done' => $step > $number]) @if ($step === $number) aria-current="step" @endif>
                @if (isset($document) && ! $document->signed_at && in_array($number, [2, 3]))
                    <a href="#{{ $number === 2 ? 'review-title' : 'decision-title' }}" data-signature-go="{{ $number }}"><span class="sig-step-number" aria-hidden="true">{{ $number }}</span><strong>{{ $label }}</strong></a>
                @elseif ($number === 1 && isset($document))
                    <a href="{{ route('signatures.index', [], false) }}"><span class="sig-step-number" aria-hidden="true">{{ $number }}</span><strong>{{ $label }}</strong></a>
                @else
                    <span @if ($number > $step) aria-disabled="true" @endif><span class="sig-step-number" aria-hidden="true">{{ $number }}</span><strong>{{ $label }}</strong></span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
