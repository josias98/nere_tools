@extends('emails.layouts.office365', [
    'title' => 'Demande de conge approuvee',
    'heading' => 'Demande approuvee',
    'preheader' => 'La demande de conge a ete approuvee.',
])

@section('content')
    <p style="margin:0 0 16px; font-size:15px; line-height:1.7;">
        La demande de {{ $leaveRequest->employee?->name() }} est approuvee :
        {{ $leaveRequest->leaveType?->name ?? 'conge' }}, {{ $leaveRequest->durationLabel() }},
        {{ $leaveRequest->periodLabel() }}.
    </p>
    @if ($pdfUrl)
        <p style="margin:0 0 16px; font-size:14px; line-height:1.7;">
            PDF final disponible : <a href="{{ $pdfUrl }}">{{ $pdfUrl }}</a>
        </p>
    @endif
    <p style="margin:0 0 24px;">
        <a href="{{ $actionUrl }}" style="display:inline-block; padding:12px 18px; background:#12344d; color:#ffffff; text-decoration:none; border-radius:999px;">Voir la demande</a>
    </p>
@endsection
