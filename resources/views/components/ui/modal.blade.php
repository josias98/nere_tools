@props(['name', 'title'])
<dialog id="{{ $name }}" {{ $attributes->class('ui-modal') }}><header><h2>{{ $title }}</h2><button type="button" onclick="this.closest('dialog').close()" aria-label="Fermer">×</button></header>{{ $slot }}</dialog>
