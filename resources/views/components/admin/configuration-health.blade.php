@props(['health'])
<span {{ $attributes->class(['configuration-health', 'is-'.$health->value]) }}><span aria-hidden="true"></span>{{ $health->label() }}</span>
