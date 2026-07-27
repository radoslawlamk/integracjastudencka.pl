<header class="admin-head">
    <div>
        <p>Witaj, <?= e($_SESSION['admin_name'] ?? 'Admin') ?></p>
        <h1>Panel CRM</h1>
    </div>
    <a class="admin-btn" href="/admin/wydarzenia/nowe">Dodaj wydarzenie</a>
</header>
<section class="stats">
    <article><strong><?= count($events) ?></strong><span>Najbliższe wydarzenia</span></article>
    <article><strong><?= count($contacts) ?></strong><span>Kontakty marketingowe</span></article>
</section>
<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Przed publikacją</h2>
            <p class="muted">Krótka lista rzeczy, które trzeba sprawdzić na serwerze.</p>
        </div>
    </div>
    <?php if (($_GET['test_email'] ?? '') === 'ok'): ?>
        <p class="alert success">Testowy email został wysłany. Sprawdź skrzynkę odbiorczą.</p>
    <?php elseif (($_GET['test_email'] ?? '') === 'blad'): ?>
        <p class="alert">Nie udało się wysłać testowego emaila. Przed publikacją trzeba ustawić pocztę SMTP lub sendmail na serwerze.</p>
    <?php endif; ?>
    <div class="launch-checklist">
        <span class="is-warning">Ustawić wysyłkę z integracja@integracjastudencka.pl</span>
        <span class="is-ok">Meta Pixel dodany</span>
        <span class="is-warning">Po publikacji ustawić APP_URL na https://integracjastudencka.pl</span>
        <span class="is-warning">Podłączyć produkcyjną bazę MySQL na SeoHost</span>
        <span class="is-warning">Przetestować formularz powiadomień i kontakt partnerski</span>
    </div>
    <form method="post" action="/admin/test-email" class="admin-filter test-email-form">
        <?= csrf_field() ?>
        <label>Wyślij test na email
            <input type="email" name="email" placeholder="np. kontakt@integracjastudencka.pl" required>
        </label>
        <button class="admin-btn">Wyślij test</button>
    </form>
</section>
<section class="panel">
    <h2>Najbliższe wydarzenia</h2>
    <?php foreach ($events as $event): ?>
        <div class="row">
            <span><?= e($event['title']) ?></span>
            <small><?= e(date('d.m.Y H:i', strtotime($event['starts_at']))) ?></small>
        </div>
    <?php endforeach; ?>
</section>
