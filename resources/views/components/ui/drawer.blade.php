@props(['title'])
<aside {{ $attributes->class('ui-drawer') }} aria-label="{{ $title }}"><h2>{{ $title }}</h2>{{ $slot }}</aside>
