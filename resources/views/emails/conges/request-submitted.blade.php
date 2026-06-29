@extends('emails.layouts.office365', [
    'title' => 'Demande de conge a valider',
    'heading' => 'Demande de conge a valider',
    'preheader' => 'Une demande de conge attend votre validation.',
])

@section('content')
    <p style="margin:0 0 16px; font-size:15px; line-height:1.7;">
        Etape : <strong>{{ $approval?->step_label ?? 'Validation' }}</strong>
    </p>
    <p style="margin:0 0 16px; font-size:15px; line-height:1.7;">
        {{ $leaveRequest->employee?->name() }} demande {{ number_format((float) $leaveRequest->requested_days, 1, ',', ' ') }} jour(s)
        de {{ $leaveRequest->leaveType?->name ?? 'conge' }}, du {{ $leaveRequest->start_date?->format('d/m/Y') }}
        au {{ $leaveRequest->end_date?->format('d/m/Y') }}.
    </p>
    <p style="margin:0 0 24px;">
        <a href="{{ $actionUrl }}" style="display:inline-block; padding:12px 18px; background:#12344d; color:#ffffff; text-decoration:none; border-radius:999px;">Ouvrir la demande</a>
    </p>
@endsection
