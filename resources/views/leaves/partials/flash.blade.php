@if (session('success'))
    <p class="nc-alert is-success">{{ session('success') }}</p>
@endif

@if (session('error'))
    <p class="nc-alert">{{ session('error') }}</p>
@endif
