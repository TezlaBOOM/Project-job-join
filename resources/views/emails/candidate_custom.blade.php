<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <title>{{ $customSubject }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 24px;">
        <h2 style="color: #1e3a8a; margin-top: 0;">Wiadomość w sprawie naboru: {{ $application->jobOffer->title }}</h2>
        <p>Szanowna Pani / Szanowny Panie <strong>{{ $application->full_name }}</strong>,</p>
        
        <div style="background: #f9fafb; border-left: 4px solid #1e3a8a; padding: 16px; margin: 20px 0;">
            {!! nl2br(e($customBody)) !!}
        </div>

        <p style="font-size: 0.85rem; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 16px;">
            Numer referencyjny Twojego zgłoszenia: <strong>{{ $application->reference_code }}</strong><br>
            Wiadomość z portalu rekrutacyjnego Urzędu Miasta.
        </p>
    </div>
</body>
</html>
