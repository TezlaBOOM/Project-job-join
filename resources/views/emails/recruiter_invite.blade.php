<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <title>Zaproszenie do panelu</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 24px;">
        <h2 style="color: #1e3a8a; margin-top: 0;">Zaproszenie do panelu rekrutacyjnego</h2>
        <p>Witaj <strong>{{ $user->name }}</strong>,</p>
        <p>Dla Twojego adresu e-mail zostało utworzone konto w panelu rekrutacyjnym Urzędu Miasta z rolą: <strong>{{ $user->role }}</strong>.</p>
        <p>Aby ustawić hasło i uzyskać dostęp do konta, kliknij w poniższy link:</p>
        <p style="margin: 24px 0;">
            <a href="{{ $resetUrl }}" style="display: inline-block; background-color: #1e3a8a; color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: bold;">
                Ustaw hasło do konta
            </a>
        </p>
        <p style="font-size: 0.85rem; color: #6b7280; word-break: break-all;">
            Link jest ważny przez 60 minut. Bezpośredni URL: {{ $resetUrl }}
        </p>
        <p style="font-size: 0.85rem; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 16px;">
            Pamiętaj, że dostęp do panelu możliwy jest wyłącznie z sieci lokalnej Urzędu Miasta.
        </p>
    </div>
</body>
</html>
