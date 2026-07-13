@props(['type' => 'neutral'])
<span {{ $attributes->class(['nc-badge', 'active' => $type === 'success', 'warning' => $type === 'warning']) }}>{{ $slot }}</span>
