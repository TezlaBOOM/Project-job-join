<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <title>Potwierdzenie zgłoszenia</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 24px;">
        <h2 style="color: #1e3a8a; margin-top: 0;">Potwierdzenie przyjęcia zgłoszenia</h2>
        <p>Szanowna Pani / Szanowny Panie <strong>{{ $application->full_name }}</strong>,</p>
        <p>Dziękujemy za złożenie aplikacji w naborze na stanowisko: <strong>{{ $application->jobOffer->title }}</strong>.</p>
        
        <div style="background: #f3f4f6; border-left: 4px solid #1e3a8a; padding: 12px 16px; margin: 20px 0;">
            <p style="margin: 0;"><strong>Numer referencyjny Twojego zgłoszenia:</strong></p>
            <p style="font-size: 1.25rem; font-weight: bold; color: #1e3a8a; margin: 4px 0 0 0;">{{ $application->reference_code }}</p>
        </div>

        <h3 style="color: #374151; font-size: 1.1rem; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px;">Podsumowanie przesłanych informacji:</h3>
        <ul style="list-style: none; padding-left: 0;">
            <li><strong>Imię i nazwisko:</strong> {{ $application->full_name }}</li>
            <li><strong>Adres e-mail:</strong> {{ $application->email }}</li>
            @if($application->phone)
                <li><strong>Telefon:</strong> {{ $application->phone }}</li>
            @endif
            <li><strong>Data złożenia:</strong> {{ $application->created_at->format('d.m.Y H:i') }}</li>
        </ul>

        @if(!empty($attachedFileNames))
            <h4 style="color: #374151; margin-bottom: 6px;">Załączone dokumenty:</h4>
            <ul>
                @foreach($attachedFileNames as $name)
                    <li>{{ $name }}</li>
                @endforeach
            </ul>
        @endif

        <div style="margin: 28px 0; padding: 16px; background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 6px;">
            <h4 style="margin-top: 0; color: #991b1b;">Rezygnacja z udziału w naborze</h4>
            <p style="font-size: 0.9rem; margin-bottom: 12px; color: #7f1d1d;">
                Jeśli zechcesz zrezygnować z udziału w naborze, możesz w każdej chwili anulować swoje zgłoszenie, klikając poniższy link:
            </p>
            <p style="margin: 0;">
                <a href="{{ route('applications.cancel.confirm', ['token' => $cancelToken]) }}" 
                   style="display: inline-block; background-color: #dc2626; color: #ffffff; text-decoration: none; padding: 8px 16px; border-radius: 4px; font-weight: bold; font-size: 0.9rem;">
                    Anuluj moje zgłoszenie
                </a>
            </p>
            <p style="font-size: 0.8rem; color: #991b1b; margin-top: 8px; word-break: break-all;">
                Bezpośredni adres: {{ route('applications.cancel.confirm', ['token' => $cancelToken]) }}
            </p>
        </div>

        <p style="font-size: 0.85rem; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 16px;">
            Wiadomość została wygenerowana automatycznie przez portal rekrutacyjny Urzędu Miasta. W przypadku pytań prosimy o kontakt z Wydziałem Kadr.
        </p>
    </div>
</body>
</html>
