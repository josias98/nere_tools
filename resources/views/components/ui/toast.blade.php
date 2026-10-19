@props(['type' => 'success', 'interactive' => false])
@if ($interactive)
    <div {{ $attributes->class('nc-toast') }} data-toast data-type="{{ $type }}" role="{{ $type === 'error' ? 'alert' : 'status' }}" aria-atomic="true">
        <span class="nc-toast-symbol" aria-hidden="true" data-toast-symbol>i</span>
        <div class="nc-toast-content">{{ $slot }}</div>
        <button class="nc-toast-close" type="button" data-toast-close aria-label="Fermer la notification"><i data-lucide="x" class="nc-icon" aria-hidden="true"></i></button>
    </div>
@else
    <x-ui.alert :type="$type" {{ $attributes->class('ts-toast') }}>{{ $slot }}</x-ui.alert>
@endif
