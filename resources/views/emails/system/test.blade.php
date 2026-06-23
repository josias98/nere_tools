@extends('emails.layouts.office365', [
    'title' => 'Test de notification Nere Tools',
    'heading' => 'Test Microsoft Graph',
    'preheader' => 'Ce message confirme la configuration de notification.',
])

@section('content')
    <p style="margin:0 0 16px; font-size:15px; line-height:1.7;">
        Ceci est un email de test envoye via Microsoft Graph pour verifier la configuration de Nere Tools.
    </p>

    <p style="margin:0 0 16px; font-size:15px; line-height:1.7;">
        Destinataire cible : <strong>{{ $recipient }}</strong><br>
        Envoye le : <strong>{{ $sentAt->format('d/m/Y H:i:s') }} UTC</strong>
    </p>

    <p style="margin:0 0 24px;">
        <a href="{{ $actionUrl }}" style="display:inline-block; padding:12px 18px; background:#12344d; color:#ffffff; text-decoration:none; border-radius:999px;">Ouvrir Nere Tools</a>
    </p>
@endsection
