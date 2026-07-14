@props(['errors'])

@if ($errors->any())
    <div class="nc-alert nc-error-summary" role="alert" tabindex="-1" data-validation-summary>
        <strong>Corrigez {{ $errors->count() }} point(s) avant de continuer.</strong>
        <ul>
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
