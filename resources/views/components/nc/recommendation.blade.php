@props(['kind' => 'advice', 'title' => 'Recommandation', 'origin' => null])

<aside {{ $attributes->class(['nc-recommendation', 'is-'.$kind]) }}>
    <div>
        <span>{{ match($kind) {
            'legal' => 'Obligation legale',
            'internal' => 'Regle interne Nere Capital',
            'practice' => 'Bonne pratique',
            default => 'Conseil',
        } }}</span>
        <strong>{{ $title }}</strong>
    </div>
    <p>{{ $slot }}</p>
    @if ($origin)
        <small>Origine : {{ $origin }}</small>
    @endif
</aside>
