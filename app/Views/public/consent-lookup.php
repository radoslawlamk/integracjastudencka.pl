<main class="legal-page">
    <section>
        <p class="eyebrow">Zgody marketingowe</p>
        <h1>Wycofaj zgody</h1>
        <p>Wpisz adres e-mail lub numer telefonu użyty przy zapisie na powiadomienia. Jeżeli znajdziemy kontakt w bazie, pokażemy formularz, w którym możesz wyłączyć powiadomienia email, SMS albo oba kanały.</p>

        <?php if ($status === 'nie_znaleziono'): ?>
            <p class="legal-error">Nie znaleźliśmy takiego kontaktu w bazie powiadomień. Sprawdź wpisane dane albo skontaktuj się z nami: kontakt@integracjastudencka.pl.</p>
        <?php endif; ?>

        <form method="post" action="/wycofaj-zgody" class="consent-manage-form">
            <?= csrf_field() ?>
            <?= spam_trap_field() ?>
            <label>
                Adres e-mail
                <input type="email" name="email" placeholder="np. ola@email.pl">
            </label>
            <label>
                Telefon
                <input type="tel" name="phone" placeholder="Numer do SMS">
            </label>
            <button class="btn primary">Przejdź do zarządzania zgodami</button>
        </form>
    </section>
</main>
