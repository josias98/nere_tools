@props(['title'])
<div {{ $attributes->class('ts-empty-state') }}><strong>{{ $title }}</strong><span>{{ $slot }}</span></div>
