Logowanie do panelu administracyjnego - Kod 2FA

Witaj {{ $user->name }},

Twój jednorazowy kod weryfikacyjny do panelu administracyjnego: {{ $code }}

Kod jest ważny przez {{ $validMinutes }} minut i może być użyty tylko jeden raz.
Adres IP żądania: {{ $ipAddress }}

OSTRZEŻENIE: Jeśli to nie Ty próbujesz się zalogować, natychmiast zmień hasło i powiadom administratora.
