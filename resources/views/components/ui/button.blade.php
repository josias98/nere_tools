@props(['variant' => 'primary', 'type' => 'button'])
<button type="{{ $type }}" {{ $attributes->class($variant === 'primary' ? 'nc-button' : 'nc-ghost') }}>{{ $slot }}</button>
