Specyfikacja funkcjonalna
Portal rekrutacyjny dla jednostek samorządu terytorialnego (JST)
Wersja: 0.1 (robocza) Data: 2026-08-27

1. Cel systemu
Portal ma umożliwiać jednostkom samorządu terytorialnego (urzędom gmin, miast, starostwom powiatowym, urzędom marszałkowskim oraz podległym jednostkom organizacyjnym) prowadzenie procesów naboru na wolne stanowiska urzędnicze zgodnie z ustawą o pracownikach samorządowych, w tym:
publikację ogłoszeń o naborze,
przyjmowanie zgłoszeń kandydatów online,
ocenę formalną i merytoryczną kandydatów,
publikację informacji o wynikach naboru,
archiwizację dokumentacji rekrutacyjnej.
System ma działać w modelu wielo-instancyjnym (multi-tenant) — jedna platforma obsługuje wiele JST, każda z własną, odseparowaną przestrzenią danych.

2. Role użytkowników i uprawnienia
2.1 Kandydat (bez zakładania konta)
przegląda listę aktualnych naborów (z filtrowaniem po JST, lokalizacji, stanowisku, rodzaju umowy, dacie)
przegląda szczegóły ogłoszenia (wymagania, zakres zadań, wymagane dokumenty, termin składania ofert)
składa aplikację online: formularz + załączniki (CV, list motywacyjny, oświadczenia, kopie dokumentów)
otrzymuje potwierdzenie złożenia aplikacji (e-mail)
śledzi status swojej aplikacji w otrzymuj powiadomienia e -mail (złożona / w trakcie oceny formalnej / zakwalifikowana / odrzucona / zaproszenie na rozmowę / wynik końcowy)
może wycofać zgłoszenie przed terminem zakończenia naboru klikając link w mailu
zarządza zgodami RODO i może zażądać usunięcia danych po zakończeniu procesu (zgodnie z okresem retencji)
2.2 Rekruter / Pracownik JST (rola podstawowa w panelu urzędu)
tworzy, edytuje, publikuje i wycofuje ogłoszenia o naborze dla swojej jednostki
definiuje wymagane dokumenty i pola formularza aplikacyjnego dla danego naboru
przegląda listę kandydatów w danym naborze
pobiera/przegląda dokumenty aplikacyjne
prowadzi ocenę formalną (checklista: kompletność dokumentów, spełnienie wymagań formalnych) z odnotowaniem uzasadnienia
prowadzi ocenę merytoryczną (punktacja, notatki z rozmów kwalifikacyjnych)
generuje i publikuje protokół z naboru (zgodnie z wymogami ustawowymi)
publikuje informację o wyniku naboru (na stronie ogłoszeń)
2.3 Moderator (nadzór regionalny / nadzór jakości, opcjonalnie)
posiada wszystkie uprawnienia Rekrutera w obrębie przypisanych mu JST/jednostek
zatwierdza publikację ogłoszeń przed wystawieniem na stronę publiczną (workflow akceptacji, jeśli włączony dla danej JST)
przegląda logi działań rekruterów w przypisanych jednostkach (audyt)
może cofnąć/wstrzymać publikację ogłoszenia w razie niezgodności z wymogami
generuje raporty zbiorcze dla przypisanych jednostek (liczba naborów, czas trwania, liczba kandydatów)
nie ma dostępu do ustawień globalnych systemu ani do danych JST spoza swojego przypisania
2.4 Administrator systemu (superadmin)
zarządza kontami wszystkich JST w systemie (dodawanie/usuwanie/zawieszanie instancji urzędu)
konfiguruje globalne ustawienia systemu: szablony ogłoszeń, szablony e-maili, okresy retencji danych RODO,
ma pełny wgląd w logi systemowe i audytowe (kto, co, kiedy zmienił)
zarządza słownikami globalnymi (stanowiska, jednostki organizacyjne, kategorie ogłoszeń)
ma dostęp do statystyk globalnych (liczba aktywnych JST, liczba naborów, obciążenie systemu)



3. Struktura serwisu
3.1 Część publiczna (bez logowania)
Strona główna: wyszukiwarka naborów + wyróżnione/aktualne ogłoszenia
Lista naborów z filtrami: JST, miejscowość, kategoria stanowiska, typ umowy, data publikacji/zakończenia
Strona szczegółów ogłoszenia (pełna treść, wymagane dokumenty, termin, dane kontaktowe)
Strona wyników zakończonych naborów (archiwum, zgodnie z wymogiem jawności)
Formularz kontaktowy / FAQ
Informacje o RODO / polityka prywatności / deklaracja dostępności (WCAG)
rejestracji kandydata
3.3 Panel JST (Rekruter/Moderator)
Logowanie tylko w sieci lokalnej
możliwość włączenia 2fa (wysyłanie tokena na maila)
Dashboard: aktywne nabory, liczba nowych zgłoszeń, terminy zbliżające się do końca
Zarządzanie ogłoszeniami (lista, kreator/edytor ogłoszenia, podgląd przed publikacją)
Lista kandydatów per nabór z filtrami i sortowaniem
Widok karty kandydata (dane, dokumenty, historia oceny)
Moduł oceny formalnej i merytorycznej
Generator protokołu z naboru
Raporty i eksporty
3.4 Panel administracyjny (Superadmin)
Zarządzanie JST (instancjami)
Zarządzanie użytkownikami i rolami
Ustawienia globalne i szablony
Logi i audyt
Statystyki systemowe
Integracje (API, BIP, SSO)

4. Wygląd i UX
Design zgodny z zasadami dostępności WCAG 2.1 AA (obowiązek dla podmiotów publicznych) — kontrast, obsługa klawiaturą, czytniki ekranu, alternatywne opisy
Responsywność: pełna obsługa desktop / tablet / mobile
Możliwość white-labelingu: każda JST może dodać własne logo i kolorystykę w ramach zdefiniowanych szablonów (bez łamania spójności systemu)
Przejrzysta karta ogłoszenia: nazwa stanowiska, JST, lokalizacja, typ umowy, termin składania ofert, status (aktywny/zakończony) widoczne "na pierwszy rzut oka"
Czytelny, wieloetapowy formularz aplikacyjny (progres bar), z zapisem wersji roboczej
Panel JST w układzie: menu boczne (nawigacja) + główny obszar roboczy + powiadomienia w prawym górnym rogu
Jasne oznaczenia statusów kolorami/etykietami (np. szary = szkic, zielony = aktywny, czerwony = zakończony/wycofany)
Tryb ciemny (opcjonalnie, niski priorytet)

5. Wymagania funkcjonalne dodatkowe
Technologia: laravel 13(lub nowszy), baza danych: mySQL lub postgresql
Struktura: Możliwość podzielenia Frontend(widok dla kandydata) od backend(widok dla admina i rekrutera) - 2 oddzielne kontenery(możliwość skalowania w poziomie)
Wersjonowanie ogłoszeń: historia zmian treści ogłoszenia z możliwością podglądu poprzednich wersji
Automatyczne wygaszanie ogłoszeń po terminie składania dokumentów
Powiadomienia e-mail (konfigurowalne szablony) na każdym etapie procesu
Eksport danych do PDF/XLSX/CSV (protokoły, listy kandydatów, raporty)
Wyszukiwarka pełnotekstowa ogłoszeń
Audyt działań: log każdej istotnej akcji (kto, co, kiedy) z możliwością przeglądu przez Moderatora/Superadmina

6. Bezpieczeństwo i zgodność (RODO / KRI)
Szyfrowanie danych w spoczynku i transmisji (TLS)
Segregacja danych między JST (multi-tenant izolacja na poziomie bazy/logiki aplikacji)
Zgody RODO przy rejestracji i składaniu aplikacji, z rejestrem zgód
Automatyczne/ręczne usuwanie danych kandydatów po upływie okresu retencji (konfigurowalny per JST, zgodnie z przepisami archiwizacyjnymi dla administracji publicznej)
Uwierzytelnianie dwuskładnikowe (2FA) obowiązkowe dla ról Rekruter/Moderator/Administrator
Zgodność z Krajowymi Ramami Interoperacyjności (KRI) dla systemów administracji publicznej
Regularne testy penetracyjne / audyt bezpieczeństwa (zalecenie procesowe, nie funkcja systemu)

7. Wymagania niefunkcjonalne
Dostępność systemu: min. 99,5% (SLA)
Skalowalność: obsługa jednoczesnego dodawania nowych JST bez przestojów
Czas odpowiedzi strony publicznej: < 2 s dla 95% żądań
Logi przechowywane min. przez okres wymagany przepisami (np. 5 lat, do potwierdzenia z prawnikiem/RODO)
Środowiska: dev / staging / produkcja

