@extends('emails.layouts.office365', [
    'title' => 'Demande de conge rejetee',
    'heading' => 'Demande rejetee',
    'preheader' => 'Une demande de conge a ete rejetee.',
])

@section('content')
    <p style="margin:0 0 16px; font-size:15px; line-height:1.7;">
        La demande de {{ $leaveRequest->employee?->name() }} a ete rejetee a l'etape
        <strong>{{ $approval?->step_label ?? '-' }}</strong> par <strong>{{ $approval?->validatorUser?->name ?? '-' }}</strong>.
    </p>
    <p style="margin:0 0 16px; font-size:15px; line-height:1.7;">
        {{ $leaveRequest->leaveType?->name ?? 'Conge' }}, {{ number_format((float) $leaveRequest->requested_days, 1, ',', ' ') }} jour(s),
        du {{ $leaveRequest->start_date?->format('d/m/Y') }} au {{ $leaveRequest->end_date?->format('d/m/Y') }}.
    </p>
    <p style="margin:0 0 24px; padding:16px; background:#fbefef; border-left:4px solid #b42318; font-size:14px; line-height:1.7;">
        Motif : {{ $approval?->comment ?: $leaveRequest->reviewer_comment }}
    </p>
    <p style="margin:0 0 24px;">
        <a href="{{ $actionUrl }}" style="display:inline-block; padding:12px 18px; background:#12344d; color:#ffffff; text-decoration:none; border-radius:999px;">Voir la demande</a>
    </p>
@endsection
