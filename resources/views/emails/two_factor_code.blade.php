<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <title>Kod logowania dwuskładnikowego</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 20px;">
    <div style="max-width: 500px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 24px;">
        <h2 style="color: #1e3a8a; margin-top: 0;">Logowanie do panelu administracyjnego</h2>
        <p>Witaj <strong>{{ $user->name }}</strong>,</p>
        <p>Oto Twój jednorazowy kod weryfikacyjny (2FA):</p>
        
        <div style="background: #f3f4f6; text-align: center; padding: 16px; margin: 20px 0; border-radius: 6px;">
            <span style="font-family: monospace; font-size: 2.2rem; font-weight: bold; letter-spacing: 6px; color: #1e3a8a;">
                {{ $code }}
            </span>
        </div>

        <p style="font-size: 0.9rem; color: #4b5563;">
            Kod jest ważny przez <strong>{{ $validMinutes }} minut</strong> i może być użyty tylko jeden raz.
        </p>
        <p style="font-size: 0.85rem; color: #6b7280;">
            Próba logowania z adresu IP: <code>{{ $ipAddress }}</code>
        </p>

        <div style="margin-top: 24px; padding: 12px; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 6px; font-size: 0.85rem; color: #92400e;">
            <strong>Ostrzeżenie:</strong> Jeśli to nie Ty próbujesz się zalogować, natychmiast zmień hasło i powiadom administratora systemu.
        </div>
    </div>
</body>
</html>
