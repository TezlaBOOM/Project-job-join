Potwierdzenie przyjęcia zgłoszenia rekrutacyjnego

Szanowna Pani / Szanowny Panie {{ $application->full_name }},

Dziękujemy za złożenie aplikacji w naborze na stanowisko: {{ $application->jobOffer->title }}.

Numer referencyjny Twojego zgłoszenia: {{ $application->reference_code }}
Data złożenia: {{ $application->created_at->format('d.m.Y H:i') }}

Podsumowanie:
- Imię i nazwisko: {{ $application->full_name }}
- E-mail: {{ $application->email }}
@if($application->phone)- Telefon: {{ $application->phone }}@endif

@if(!empty($attachedFileNames))
Załączone dokumenty:
@foreach($attachedFileNames as $name)
- {{ $name }}
@endforeach
@endif

Anulowanie zgłoszenia:
Jeśli zechcesz zrezygnować z udziału w naborze, możesz anulować zgłoszenie pod poniższym adresem:
{{ route('applications.cancel.confirm', ['token' => $cancelToken]) }}

---
Wiadomość wygenerowana automatycznie przez portal rekrutacyjny Urzędu Miasta.
