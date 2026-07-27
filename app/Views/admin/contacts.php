<header class="admin-head">
    <div><p>CRM</p><h1>Baza marketingowa</h1></div>
    <a class="admin-btn" href="/admin/kontakty/export">Eksport CSV</a>
</header>

<?php if ($emailStatus === 'wyslano'): ?>
    <p class="alert success">Wysyłka zakończona. Wysłane wiadomości: <?= e($_GET['ile'] ?? '0') ?>. Lokalnie niewysłane maile mogą trafić do logu.</p>
<?php elseif ($emailStatus === 'blad'): ?>
    <p class="alert">Wybierz odbiorców ze zgodą email oraz wpisz temat i treść wiadomości.</p>
<?php endif; ?>

<section class="panel">
    <form method="get" action="/admin/kontakty" class="admin-filter">
        <label>Filtruj po mieście
            <select name="city">
                <option value="">Wszystkie miasta</option>
                <?php foreach ($cities as $city): ?>
                    <option value="<?= e($city) ?>" <?= $selectedCity === $city ? 'selected' : '' ?>><?= e($city) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="admin-btn">Pokaż kontakty</button>
        <?php if ($selectedCity): ?><a class="ghost" href="/admin/kontakty">Wyczyść filtr</a><?php endif; ?>
    </form>
</section>

<form method="post" action="/admin/kontakty/email" id="marketingEmailForm">
    <?= csrf_field() ?>
    <section class="panel">
        <div class="panel-head">
            <h2>Kontakty<?= $selectedCity ? ' - ' . e($selectedCity) : '' ?></h2>
            <button type="button" class="ghost" data-select-visible-emails>Zaznacz widoczne maile</button>
        </div>
        <table>
            <thead>
                <tr>
                    <th><input type="checkbox" data-select-all-emails aria-label="Zaznacz wszystkie widoczne kontakty email"></th>
                    <th>Imię</th>
                    <th>Email</th>
                    <th>Telefon</th>
                    <th>Miasto</th>
                    <th>Zgody</th>
                    <th>Tagi</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($contacts as $contact): ?>
                <?php $canEmail = !empty($contact['email_consent']) && !empty($contact['email']); ?>
                <tr>
                    <td>
                        <?php if ($canEmail): ?>
                            <input type="checkbox" name="contact_ids[]" value="<?= e((string) $contact['id']) ?>" data-email-contact>
                        <?php endif; ?>
                    </td>
                    <td><?= e($contact['name']) ?></td>
                    <td><?= e($contact['email']) ?></td>
                    <td><?= e($contact['phone']) ?></td>
                    <td><?= e($contact['city']) ?></td>
                    <td><?= !empty($contact['sms_consent']) ? 'SMS ' : '' ?><?= !empty($contact['email_consent']) ? 'Email' : '' ?></td>
                    <td><?= e($contact['tags']) ?></td>
                    <td><?= e($contact['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$contacts): ?>
                <tr><td colspan="8">Brak kontaktów dla wybranego filtra.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>

    <section class="panel form-grid">
        <div class="wide">
            <h2>Wyślij maila do zaznaczonych</h2>
            <p class="muted">Wiadomość zostanie wysłana tylko do kontaktów z aktywną zgodą email. System automatycznie doda na początku: „Cześć Imię,”.</p>
        </div>
        <label class="wide">Temat wiadomości <input name="subject" placeholder="np. Nowe wydarzenia studenckie w Twoim mieście"></label>
        <label class="wide">Treść wiadomości <textarea name="message" rows="8" placeholder="Tu wpisz treść wiadomości. Powitanie z imieniem doda się automatycznie."></textarea></label>
        <button class="admin-btn">Wyślij maila</button>
    </section>
</form>
