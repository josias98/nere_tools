<aside class="sig-wizard-rail" aria-label="Étapes de la signature">
    <p class="sig-rail-label"><i data-lucide="file-signature" class="nc-icon" aria-hidden="true"></i> Signature PDF</p>
    <ol class="sig-wizard-path">
        @foreach ([1 => ['Ajouter', 'Choisir votre PDF'], 2 => ['Relire', 'Vérifier le contenu'], 3 => ['Confirmer', 'Donner votre accord'], 4 => ['Récupérer', 'Télécharger le PDF signé']] as $number => [$label, $description])
            <li data-signature-indicator="{{ $number }}" @class(['is-current' => $step === $number, 'is-done' => $step > $number]) @if ($step === $number) aria-current="step" @endif>
                <span class="sig-step-number" aria-hidden="true">{{ str_pad($number, 2, '0', STR_PAD_LEFT) }}</span>
                @if (isset($document) && ! $document->signed_at && in_array($number, [2, 3]))
                    <a href="#{{ $number === 2 ? 'review-title' : 'decision-title' }}" data-signature-go="{{ $number }}"><strong>{{ $label }}</strong><small>{{ $description }}</small></a>
                @elseif ($number === 1 && isset($document))
                    <a href="{{ route('signatures.index') }}"><strong>{{ $label }}</strong><small>{{ $description }}</small></a>
                @else
                    <span><strong>{{ $label }}</strong><small>{{ $description }}</small></span>
                @endif
            </li>
        @endforeach
    </ol>
    <div class="sig-rail-foot"><i data-lucide="lock-keyhole" class="nc-icon" aria-hidden="true"></i><p>Vos documents restent privés.<br>Votre original est conservé.</p></div>
</aside>
