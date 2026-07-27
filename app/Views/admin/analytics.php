<?php
$sortOptions = [
    'total_clicks' => 'Wszystkie kliknięcia',
    'views' => 'Wejścia na wydarzenie',
    'ticket_clicks' => 'Kup bilet',
    'details_clicks' => 'Szczegóły',
    'facebook_clicks' => 'Dołącz na FB',
    'ticket_notify_clicks' => 'Powiadom mnie',
    'ticket_conversion' => 'Konwersja do biletu',
];
$rangeOptions = [
    'today' => 'Dzisiaj',
    'yesterday' => 'Wczoraj',
    '7d' => 'Ostatnie 7 dni',
    '30d' => 'Ostatnie 30 dni',
    'all' => 'Cały okres',
];
$eventStatusOptions = [
    'all' => 'Wszystkie wydarzenia',
    'upcoming' => 'Tylko nadchodzące',
    'finished' => 'Tylko zakończone',
];
$seasonOptions = [
    'current' => 'Aktualny sezon',
    'all' => 'Cala historia',
];
foreach (($seasons ?? []) as $archivedSeason) {
    $seasonOptions[(string) $archivedSeason['id']] = $archivedSeason['name'];
}
$ticketConversion = !empty($totals['views']) ? round(((int) ($totals['ticket_clicks'] ?? 0) / (int) $totals['views']) * 100, 1) : 0;
?>
<header class="admin-head">
    <div>
        <p>CRM</p>
        <h1>Analityka wydarzeń</h1>
    </div>
    <a class="admin-btn" href="/">Zobacz stronę</a>
</header>

<section class="panel analytics-toolbar">
    <?php if (($resetStatus ?? '') === 'ok'): ?>
        <p class="alert success">Rozpoczęto nowy sezon statystyk. Stare dane zostały w bazie, ale nie są już liczone w bieżących rankingach.</p>
    <?php endif; ?>
    <form class="admin-filter" method="get" action="/admin/analityka">
        <label>Sezon
            <select name="season" onchange="this.form.submit()">
                <?php foreach ($seasonOptions as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= ($season ?? 'current') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Zakres dat
            <select name="range" onchange="this.form.submit()">
                <?php foreach ($rangeOptions as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $range === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Typ wydarzeń
            <select name="event_status" onchange="this.form.submit()">
                <?php foreach ($eventStatusOptions as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $eventStatus === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Sortuj ranking wydarzeń
            <select name="sort" onchange="this.form.submit()">
                <?php foreach ($sortOptions as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $sort === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </form>
    <div class="analytics-season-actions">
        <p class="muted">
            <?= !empty($seasonStart) ? 'Aktualny sezon statystyk liczony od: ' . e(date('d.m.Y H:i', strtotime($seasonStart))) : 'Aktualnie liczysz statystyki od początku zbierania danych.' ?>
        </p>
        <form method="post" action="/admin/analityka/reset-sezonu" onsubmit="return confirm('Rozpocząć nowy sezon statystyk? Stare dane zostaną w bazie, ale znikną z bieżących rankingów.');">
            <?= csrf_field() ?>
            <button class="danger" type="submit">Rozpocznij nowy sezon statystyk</button>
        </form>
    </div>
</section>

<section class="stats analytics-stats">
    <article><strong><?= e((string) ($totals['views'] ?? 0)) ?></strong><span>Wejścia na strony wydarzeń</span></article>
    <article><strong><?= e((string) ($totals['clicks'] ?? 0)) ?></strong><span>Wszystkie kliknięcia</span></article>
    <article><strong><?= e((string) ($totals['ticket_clicks'] ?? 0)) ?></strong><span>Kliknięcia Kup bilet</span></article>
    <article><strong><?= e((string) ($totals['details_clicks'] ?? 0)) ?></strong><span>Kliknięcia w szczegóły</span></article>
    <article><strong><?= e((string) ($totals['facebook_clicks'] ?? 0)) ?></strong><span>Kliknięcia Dołącz na FB</span></article>
    <article><strong><?= e((string) $ticketConversion) ?>%</strong><span>Konwersja wejście → Kup bilet</span></article>
</section>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Ranking wydarzeń</h2>
            <p class="muted">Sortowanie pokazuje, które wydarzenia najlepiej pracują sprzedażowo.</p>
        </div>
    </div>

    <table>
        <thead>
        <tr>
            <th>Wydarzenie</th>
            <th>Miasto</th>
            <th>Wejścia</th>
            <th>Kliknięcia</th>
            <th>Kup bilet</th>
            <th>Szczegóły</th>
            <th>FB</th>
            <th>Powiadom</th>
            <th>Konwersja</th>
            <th>Ostatnia aktywność</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($ranking as $row): ?>
            <tr>
                <td><a href="/wydarzenia/<?= e($row['slug']) ?>" target="_blank"><?= e($row['title']) ?></a></td>
                <td><?= e($row['city']) ?></td>
                <td><?= e((string) $row['views']) ?></td>
                <td><?= e((string) $row['total_clicks']) ?></td>
                <td><?= e((string) $row['ticket_clicks']) ?></td>
                <td><?= e((string) $row['details_clicks']) ?></td>
                <td><?= e((string) $row['facebook_clicks']) ?></td>
                <td><?= e((string) $row['ticket_notify_clicks']) ?></td>
                <td><?= $row['ticket_conversion'] !== null ? e((string) $row['ticket_conversion']) . '%' : '<small>brak</small>' ?></td>
                <td><?= $row['last_activity'] ? e(date('d.m.Y H:i', strtotime($row['last_activity']))) : '<small>brak</small>' ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$ranking): ?>
            <tr><td colspan="10">Brak danych analitycznych. Pojawią się po pierwszych wejściach i kliknięciach.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</section>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Ranking miast</h2>
            <p class="muted">Pokazuje, które miasta generują najwięcej wejść i kliknięć sprzedażowych w wybranym okresie.</p>
        </div>
    </div>
    <table>
        <thead>
        <tr>
            <th>Miasto</th>
            <th>Wejścia</th>
            <th>Kliknięcia</th>
            <th>Kup bilet</th>
            <th>Szczegóły</th>
            <th>Powiadom</th>
            <th>Konwersja</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($cityRanking as $city): ?>
            <tr>
                <td><?= e($city['city']) ?></td>
                <td><?= e((string) $city['views']) ?></td>
                <td><?= e((string) $city['clicks']) ?></td>
                <td><?= e((string) $city['ticket_clicks']) ?></td>
                <td><?= e((string) $city['details_clicks']) ?></td>
                <td><?= e((string) $city['ticket_notify_clicks']) ?></td>
                <td><?= $city['ticket_conversion'] !== null ? e((string) $city['ticket_conversion']) . '%' : '<small>brak</small>' ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$cityRanking): ?>
            <tr><td colspan="7">Brak danych dla miast.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</section>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Najlepsze źródła ogólnie</h2>
            <p class="muted">Szybki podgląd, które kanały dają najwięcej wejść na wydarzenia.</p>
        </div>
    </div>
    <div class="analytics-source-grid">
        <?php foreach ($sourceTotals as $source): ?>
            <article>
                <strong><?= e((string) $source['visits']) ?></strong>
                <span><?= e($source['source']) ?></span>
            </article>
        <?php endforeach; ?>
        <?php if (!$sourceTotals): ?>
            <p class="muted">Brak źródeł wejść w wybranym okresie.</p>
        <?php endif; ?>
    </div>
</section>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Źródła wejść na wydarzenia</h2>
            <p class="muted">Źródło rozpoznajemy po stronie odsyłającej, np. Facebook, Instagram, Google albo wejście bezpośrednie.</p>
        </div>
    </div>
    <table>
        <thead>
        <tr>
            <th>Wydarzenie</th>
            <th>Miasto</th>
            <th>Źródło</th>
            <th>Wejścia</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($sources as $source): ?>
            <tr>
                <td><a href="/wydarzenia/<?= e($source['slug']) ?>" target="_blank"><?= e($source['title']) ?></a></td>
                <td><?= e($source['city']) ?></td>
                <td><span class="analytics-source"><?= e($source['source']) ?></span></td>
                <td><?= e((string) $source['visits']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$sources): ?>
            <tr><td colspan="4">Brak wejść na podstrony wydarzeń.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</section>
