@props(['label', 'for', 'error' => null, 'hint' => null])
<div class="nc-field"><label for="{{ $for }}">{{ $label }}</label>{{ $slot }}@if($hint)<small>{{ $hint }}</small>@endif @if($error)<small class="nc-error" role="alert">{{ $error }}</small>@endif</div>
