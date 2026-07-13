@props(['value' => null, 'max' => 100, 'label' => 'Progression'])
<div class="ts-progress" role="status"><span>{{ $label }}</span><progress @if($value !== null) value="{{ $value }}" @endif max="{{ $max }}"></progress></div>
