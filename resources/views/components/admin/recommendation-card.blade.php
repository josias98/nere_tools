@props(['title', 'source', 'recommended', 'risk', 'example', 'url'])
<aside class="recommendation-card" data-searchable="{{ $title }} {{ $recommended }}">
    <div class="admin-section-heading"><div><p class="nc-kicker">Recommandation</p><h2>{{ $title }}</h2></div><span class="recommended-badge">{{ $source }}</span></div>
    <dl><div><dt>Valeur conseillée</dt><dd>{{ $recommended }}</dd></div><div><dt>Risque à connaître</dt><dd>{{ $risk }}</dd></div><div><dt>Exemple concret</dt><dd>{{ $example }}</dd></div></dl>
    <a href="{{ $url }}" class="nc-ghost" data-tooltip-title="Appliquer la recommandation" data-tooltip="Ouvre la section concernée pour vous laisser contrôler le réglage avant enregistrement.">Voir la configuration <i data-lucide="arrow-right"></i></a>
</aside>
