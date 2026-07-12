<section id="overview" aria-labelledby="overview-title">
    <div class="nc-panel-heading"><div><h2 id="overview-title">Vue d’ensemble</h2><p>Situation opérationnelle à cet instant.</p></div></div>
    <div class="leave-metrics">
        @foreach ([['Absents aujourd’hui', $summary['absent_today'], 'users'], ['Prévus sous 30 jours', $summary['next_30_days'], 'calendar-clock'], ['Décisions en attente', $summary['pending'], 'git-pull-request'], ['Justificatifs manquants', $summary['missing_attachments'], 'file-warning']] as [$label, $value, $icon])
            <article class="leave-metric"><i data-lucide="{{ $icon }}" aria-hidden="true"></i><strong>{{ $value }}</strong><span>{{ $label }}</span></article>
        @endforeach
    </div>
</section>
