@if (session('success'))
    <p class="nc-alert is-success">{{ session('success') }}</p>
@endif

@if (session('error'))
    <p class="nc-alert">{{ session('error') }}</p>
@endif

@if ($errors->any())
    <div class="nc-alert" role="alert" tabindex="-1" data-validation-summary>
        <strong>La demande n’a pas été envoyée.</strong>
        <ul>
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
