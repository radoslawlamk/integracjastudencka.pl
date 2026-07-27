<?php
$sortOptions = [
    'starts_desc' => 'Data: najnowsze pierwsze',
    'starts_asc' => 'Data: najbliższe pierwsze',
    'title_asc' => 'Nazwa A-Z',
    'city_asc' => 'Miasto A-Z',
    'type_asc' => 'Typ wydarzenia',
    'status_asc' => 'Status',
];
$showOptions = [
    'upcoming' => 'Nadchodzące / trwające',
    'all' => 'Wszystkie, także archiwalne',
    'missing_any' => 'Z brakami linków',
    'missing_ticket' => 'Bez linku do biletów',
    'missing_fb' => 'Bez wydarzenia FB',
    'finished' => 'Zakończone',
    'published' => 'Opublikowane',
    'draft' => 'Szkice',
    'tickets_soon' => 'Bilety wkrótce',
    'on_sale' => 'Sprzedaż trwa',
    'sold_out' => 'Wyprzedane',
];
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl = rtrim(getenv('APP_URL') ?: ($scheme . '://' . ($_SERVER['HTTP_HOST'] ?? '127.0.0.1:8000')), '/');
?>
<header class="admin-head">
    <div><p>CRM</p><h1>Wydarzenia</h1></div>
    <a class="admin-btn" href="/admin/wydarzenia/nowe">Dodaj wydarzenie</a>
</header>
<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Lista wydarzeń</h2>
            <p class="muted">Sortuj wydarzenia i szybko kopiuj najważniejsze linki.</p>
        </div>
        <div class="events-toolbar">
            <button type="button" class="admin-btn ghost" data-open-selected-facebook>Otwórz zaznaczone FB</button>
            <form class="admin-filter" method="get" action="/admin/wydarzenia">
                <label>Pokaż tylko
                    <select name="show" onchange="this.form.submit()">
                        <?php foreach ($showOptions as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= ($selectedShowOnly ?? 'upcoming') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Miasto
                    <select name="city" onchange="this.form.submit()">
                        <option value="">Wszystkie miasta</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?= e($city) ?>" <?= ($selectedCity ?? '') === $city ? 'selected' : '' ?>><?= e($city) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Sortuj po
                    <select name="sort" onchange="this.form.submit()">
                        <?php foreach ($sortOptions as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= ($selectedSort ?? 'starts_asc') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </form>
        </div>
    </div>
    <table>
        <thead>
        <tr>
            <th><input type="checkbox" data-select-facebook-events aria-label="Zaznacz wydarzenia z FB"></th>
            <th>Nazwa</th>
            <th>Typ</th>
            <th>Miasto</th>
            <th>Data</th>
            <th>Status</th>
            <th>Bilety</th>
            <th>Szybkie linki</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($events as $event): ?>
            <?php
            $eventUrl = $baseUrl . '/wydarzenia/' . $event['slug'];
            $ticketUrl = trim((string) ($event['ticket_url'] ?? ''));
            $facebookUrl = trim((string) ($event['facebook_event_url'] ?? ''));
            $missingTicket = $ticketUrl === '';
            $missingFacebook = $facebookUrl === '';
            ?>
            <tr>
                <td>
                    <input type="checkbox" data-facebook-event-check value="<?= e($facebookUrl) ?>" aria-label="Zaznacz wydarzenie FB <?= e($event['title']) ?>" <?= $facebookUrl === '' ? 'disabled' : '' ?>>
                </td>
                <td>
                    <strong><?= e($event['title']) ?></strong>
                    <small><?= e($event['slug']) ?></small>
                    <?php if ($missingTicket || $missingFacebook): ?>
                        <div class="event-warnings">
                            <?php if ($missingTicket): ?><span>Brak biletów</span><?php endif; ?>
                            <?php if ($missingFacebook): ?><span>Brak FB</span><?php endif; ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td><?= $event['type'] === 'clubbing' ? 'Clubbing' : 'Zwykłe' ?></td>
                <td><?= e($event['city']) ?></td>
                <td><?= e(date('d.m.Y H:i', strtotime($event['starts_at']))) ?></td>
                <td><?= e($event['status']) ?></td>
                <td><span class="analytics-source"><?= e($event['sales_status'] ?? 'auto') ?></span></td>
                <td>
                    <div class="copy-actions">
                        <button type="button" class="copy-btn" data-copy-url="<?= e($eventUrl) ?>" title="Skopiuj link do strony wydarzenia" aria-label="Skopiuj link do strony wydarzenia">URL</button>
                        <button type="button" class="copy-btn" data-copy-url="<?= e($ticketUrl) ?>" title="<?= $ticketUrl ? 'Skopiuj link do biletów' : 'Brak linku do biletów' ?>" aria-label="Skopiuj link do biletów" <?= $ticketUrl === '' ? 'disabled' : '' ?>>B</button>
                        <button type="button" class="copy-btn" data-copy-url="<?= e($facebookUrl) ?>" title="<?= $facebookUrl ? 'Skopiuj link do wydarzenia na FB' : 'Brak linku do wydarzenia na FB' ?>" aria-label="Skopiuj link do wydarzenia na FB" <?= $facebookUrl === '' ? 'disabled' : '' ?>>f</button>
                    </div>
                </td>
                <td><a href="/admin/wydarzenia/<?= e((string) $event['id']) ?>/edycja">Edytuj</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$events): ?>
            <tr><td colspan="9">Brak wydarzeń.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</section>
