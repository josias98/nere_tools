@props(['for', 'title', 'help'])

<label class="nc-dropzone" for="{{ $for }}" x-on:dragover.prevent="$el.classList.add('is-dragging')" x-on:dragleave.prevent="$el.classList.remove('is-dragging')" x-on:drop="$el.classList.remove('is-dragging')">
    <i data-lucide="upload-cloud" aria-hidden="true"></i>
    <span>{{ $title }}</span>
    <small>{{ $help }}</small>
    {{ $slot }}
</label>
