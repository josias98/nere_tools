@props(['severity' => 'warning', 'title', 'description', 'url'])
<article class="risk-warning is-{{ $severity }}" data-searchable="{{ $title }} {{ $description }}">
    <i data-lucide="{{ $severity === 'critical' ? 'octagon-alert' : 'triangle-alert' }}" aria-hidden="true"></i>
    <div><strong>{{ $title }}</strong><p>{{ $description }}</p></div>
    <a class="nc-ghost" href="{{ $url }}" data-tooltip-title="Corriger ce point" data-tooltip="Ouvre directement le réglage concerné sans modifier la configuration automatiquement.">Corriger <i data-lucide="arrow-right" aria-hidden="true"></i></a>
</article>
