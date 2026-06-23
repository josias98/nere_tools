@extends('emails.layouts.office365', [
    'title' => 'Votre demande de conge a ete approuvee',
    'heading' => 'Demande approuvee',
    'preheader' => 'Votre demande de conge a ete approuvee.',
])

@section('content')
    <p style="margin:0 0 16px; font-size:15px; line-height:1.7;">
        Votre demande de conge du {{ $leaveRequest->start_date?->format('d/m/Y') }}
        au {{ $leaveRequest->end_date?->format('d/m/Y') }} a ete approuvee.
    </p>

    @if ($leaveRequest->reviewer_comment)
        <p style="margin:0 0 24px; padding:16px; background:#eef6f1; border-left:4px solid #2f7d4a; font-size:14px; line-height:1.7;">
            Commentaire du validateur : {{ $leaveRequest->reviewer_comment }}
        </p>
    @endif

    <p style="margin:0 0 24px;">
        <a href="{{ $actionUrl }}" style="display:inline-block; padding:12px 18px; background:#12344d; color:#ffffff; text-decoration:none; border-radius:999px;">Voir ma demande</a>
    </p>
@endsection
