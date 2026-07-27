# Checklist przed publikacja

## Repozytorium

- Do GitHuba wrzucamy kod aplikacji, szablony i puste katalogi uploadow.
- Nie wrzucamy pliku `.env`, kopii bazy, logow, sesji, wgranych prywatnych plikow ani danych klientow.
- Wydarzenia, newsy i baza marketingowa maja byc tworzone od zera w CRM po publikacji.

## Serwer i baza danych

- Utworzyc baze MySQL/MariaDB na hostingu.
- Zaimportowac plik `database/schema.sql`.
- Skopiowac `.env.example` jako `.env` juz na serwerze i wpisac prawdziwe dane bazy.
- Ustawic `APP_URL=https://integracjastudencka.pl`.
- Katalog domeny powinien wskazywac na `public`.
- Sprawdzic, czy PHP ma wlaczone PDO MySQL, OpenSSL i obsluge uploadow.

## Poczta SMTP

- Utworzyc skrzynke `integracja@integracjastudencka.pl` na hostingu.
- Pobrac dane SMTP tej skrzynki: serwer, port, login i haslo.
- Wpisac dane SMTP do konfiguracji strony przed uruchomieniem automatycznych powiadomien o biletach i wysylek z CRM.
- Przetestowac wysylke maila z CRM po publikacji.

Bez konfiguracji SMTP automatyczne powiadomienia o biletach moga nie wychodzic poprawnie albo moga trafiac do spamu.

## Po uruchomieniu

- Wejsc na `/admin/install` i utworzyc pierwszego administratora.
- Zalogowac sie do CRM i dodac wydarzenia oraz newsy bezposrednio w panelu.
- Wykonac test formularza kontaktowego.
- Wykonac test zapisu do powiadomien i test wypisania zgody.
- Sprawdzic publiczna strone wydarzenia, obrazek udostepniania oraz linki do biletow/Facebooka.
