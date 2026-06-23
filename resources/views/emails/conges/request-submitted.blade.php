@extends('emails.layouts.office365', [
    'title' => 'Nouvelle demande de conge a valider',
    'heading' => 'Nouvelle demande de conge',
    'preheader' => 'Une demande de conge attend votre validation.',
])

@section('content')
    <p style="margin:0 0 16px; font-size:15px; line-height:1.7;">
        {{ $leaveRequest->employee?->name() }} a soumis une demande de conge du
        {{ $leaveRequest->start_date?->format('d/m/Y') }} au {{ $leaveRequest->end_date?->format('d/m/Y') }}.
    </p>

    <p style="margin:0 0 24px; font-size:15px; line-height:1.7;">
        Nombre de jours demandes : <strong>{{ number_format((float) $leaveRequest->requested_days, 1, ',', ' ') }}</strong>
    </p>

    <p style="margin:0 0 24px;">
        <a href="{{ $actionUrl }}" style="display:inline-block; padding:12px 18px; background:#12344d; color:#ffffff; text-decoration:none; border-radius:999px;">Ouvrir la demande</a>
    </p>
@endsection
