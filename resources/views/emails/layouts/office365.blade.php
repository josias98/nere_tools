<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Nere Tools' }}</title>
</head>
<body style="margin:0; padding:24px; background:#f4efe8; color:#1f2937; font-family:'Segoe UI', Arial, sans-serif;">
    <div style="display:none; max-height:0; overflow:hidden;">{{ $preheader ?? ($title ?? 'Nere Tools') }}</div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px; margin:0 auto; background:#ffffff; border:1px solid #d8d2c7; border-radius:16px; overflow:hidden;">
        <tr>
            <td style="padding:24px 28px; background:linear-gradient(135deg, #12344d, #1d5d74); color:#ffffff;">
                <p style="margin:0 0 8px; font-size:12px; letter-spacing:0.14em; text-transform:uppercase;">Nere Tools</p>
                <h1 style="margin:0; font-size:24px; line-height:1.2;">{{ $heading ?? ($title ?? 'Notification') }}</h1>
            </td>
        </tr>
        <tr>
            <td style="padding:28px;">
                @yield('content')
            </td>
        </tr>
        <tr>
            <td style="padding:0 28px 24px; color:#6b7280; font-size:13px; line-height:1.6;">
                <p style="margin:0;">Ce message a ete envoye par Nere Tools. Merci de vous connecter a l'application pour consulter les details complets.</p>
            </td>
        </tr>
    </table>
</body>
</html>
