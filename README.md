# Integracja Studencka

Nowa aplikacja strony wydarzeń z panelem CRM. Projekt jest przygotowany pod hosting PHP + MySQL/MariaDB, np. SeoHost.

## Co zawiera

- publiczną stronę wydarzeń,
- dwa typy wydarzeń: zwykłe oraz specjalne clubbing,
- clubbing z listą wielu klubów,
- linki: kup bilet, dołącz do wydarzenia Facebook, polub fanpage,
- filmy YouTube,
- partnerów wydarzenia,
- panel admina `/admin`,
- bazę marketingową i eksport CSV,
- instalator pierwszego konta admina `/admin/install`.

## Konfiguracja

1. Skopiuj `.env.example` jako `.env`.
2. Uzupełnij dane bazy z DirectAdmin:
   - `DB_HOST=localhost`,
   - `DB_DATABASE`,
   - `DB_USERNAME`,
   - `DB_PASSWORD`.
3. Na serwerze katalog domeny powinien wskazywać na folder `public`.
4. Wejdź na `/admin/install` i utwórz pierwsze konto administratora.
5. Po instalacji logujesz się przez `/admin`.

## Publikacja na SeoHost

Najbezpieczniej wgrać projekt przez Git albo FTP/SFTP. Publicznie dostępny powinien być tylko katalog `public`. Jeżeli panel DirectAdmin nie pozwala ustawić katalogu domeny na `public`, trzeba zastosować konfigurację po stronie hostingu albo przenieść zawartość `public` do katalogu domeny i poprawić ścieżki w `index.php`.

## Praca w Codex

Kod można dalej zmieniać lokalnie w tym katalogu. Wydarzenia nie są dodawane w kodzie: dodajesz je wyłącznie w CRM-ie.
## Poczta i formularze

Formularze kontaktowe oraz wysylki z CRM korzystaja z jednej konfiguracji poczty w pliku `.env`.
Na serwerze ustaw:

- `MAIL_MAILER=smtp`,
- `MAIL_FROM_ADDRESS=integracja@integracjastudencka.pl`,
- `MAIL_FROM_NAME=Integracja Studencka`,
- `CONTACT_TO_ADDRESS=kontakt@integracjastudencka.pl`,
- `SMTP_HOST`,
- `SMTP_PORT`,
- `SMTP_USERNAME`,
- `SMTP_PASSWORD`,
- `SMTP_ENCRYPTION`.

Lokalnie mozna zostawic `MAIL_MAILER=log`, wtedy wiadomosci testowe trafiaja do `storage/logs/mail.log`.
Po publikacji wejdz w CRM i wykonaj test wysylki maila z kokpitu.
