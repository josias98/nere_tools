@props(['type' => 'error'])
<div role="{{ $type === 'error' ? 'alert' : 'status' }}" {{ $attributes->class(['nc-alert', 'is-success' => $type === 'success']) }}>{{ $slot }}</div>
