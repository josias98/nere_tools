<div class="nc-toast-stack" data-notifications role="region" aria-label="Notifications"></div>
<template data-notification-template>
    <x-ui.toast :interactive="true" type="info">
        <strong data-toast-title></strong>
        <p data-toast-message></p>
        <button class="nc-toast-action" type="button" data-toast-action hidden></button>
    </x-ui.toast>
</template>
@if (session('success') && ! ($integrityError ?? false))
    <p class="sig-fallback-notice" data-signature-flash role="status">{{ session('success') }}</p>
@endif
