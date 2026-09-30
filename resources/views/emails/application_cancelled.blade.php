<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <title>Zgłoszenie zostało anulowane</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 24px;">
        <h2 style="color: #991b1b; margin-top: 0;">Potwierdzenie wycofania zgłoszenia</h2>
        <p>Szanowna Pani / Szanowny Panie <strong>{{ $application->full_name }}</strong>,</p>
        <p>Twoje zgłoszenie o numerze referencyjnym <strong>{{ $application->reference_code }}</strong> na stanowisko <strong>{{ $application->jobOffer->title }}</strong> zostało pomyślnie anulowane na Twoją prośbę.</p>
        <p>Twoje dane nie będą dalej przetwarzane w ramach tego naboru.</p>
        <p style="font-size: 0.85rem; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 16px; margin-top: 24px;">
            Wiadomość została wygenerowana automatycznie przez portal rekrutacyjny Urzędu Miasta.
        </p>
    </div>
</body>
</html>
