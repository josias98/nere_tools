@props(['type' => 'success'])
<x-ui.alert :type="$type" {{ $attributes->class('ts-toast') }}>{{ $slot }}</x-ui.alert>
