<?php
$date = strtotime($item['starts_at']);
$state = event_sales_state($item);
$months = [
    '01' => 'STY', '02' => 'LUT', '03' => 'MAR', '04' => 'KWI',
    '05' => 'MAJ', '06' => 'CZE', '07' => 'LIP', '08' => 'SIE',
    '09' => 'WRZ', '10' => 'PAŹ', '11' => 'LIS', '12' => 'GRU',
];
$daysLeft = (int) ceil(($date - time()) / 86400);
$countdownText = $daysLeft > 0 ? 'Zostało ' . $daysLeft . ' dni' : 'Impreza w trakcie';
if ($state['finished']) {
    $countdownText = 'Wydarzenie zakończone';
} elseif ($state['in_progress']) {
    $countdownText = 'Impreza w trakcie';
}
$placeLine = trim(($item['city'] ?: '') . ' • ' . ($item['venue_name'] ?: '') . ($item['venue_address'] ? ', ' . $item['venue_address'] : ''));
?>
<article class="event-card calendar-event-card <?= $item['type'] === 'clubbing' ? 'is-clubbing' : '' ?>">
    <div class="calendar-date">
        <strong><?= e(date('d', $date)) ?></strong>
        <span><?= e($months[date('m', $date)] ?? date('M', $date)) ?></span>
    </div>

    <div class="calendar-event-body">
        <?php if ($item['type'] === 'clubbing'): ?>
            <span class="calendar-type-badge">Clubbing</span>
        <?php endif; ?>
        <h3><a href="/wydarzenia/<?= e($item['slug']) ?>"><?= e($item['title']) ?></a></h3>
        <p class="calendar-meta"><?= e($placeLine) ?> • <?= e(date('d.m.Y H:i', $date)) ?></p>
        <span class="calendar-countdown"><?= e($countdownText) ?></span>

        <nav class="calendar-actions" aria-label="Akcje wydarzenia <?= e($item['title']) ?>">
            <?php if ($state['can_buy']): ?>
                <a class="calendar-ticket" href="<?= e($item['ticket_url']) ?>" target="_blank" rel="noopener" data-analytics-event="<?= e((string) $item['id']) ?>" data-analytics-action="ticket">Kup bilet</a>
            <?php elseif ($state['tickets_soon']): ?>
                <a class="calendar-ticket calendar-ticket-soon calendar-notify" href="/wydarzenia/<?= e($item['slug']) ?>#bilety-wkrotce" data-analytics-event="<?= e((string) $item['id']) ?>" data-analytics-action="ticket_notify">Bilety wkrótce - powiadom mnie!</a>
            <?php elseif ($state['sold_out']): ?>
                <span class="calendar-ticket calendar-ticket-soon">Wyprzedane</span>
            <?php else: ?>
                <a class="calendar-ticket calendar-ticket-soon calendar-notify" href="/miasta/<?= e(slugify($item['city'])) ?>#powiadomienia" data-analytics-event="<?= e((string) $item['id']) ?>" data-analytics-action="ticket_notify">Zobacz kolejne imprezy</a>
            <?php endif; ?>
            <a class="calendar-details" href="/wydarzenia/<?= e($item['slug']) ?>" data-analytics-event="<?= e((string) $item['id']) ?>" data-analytics-action="details">Sprawdź szczegóły</a>
            <?php if ($item['facebook_event_url']): ?>
                <a class="calendar-facebook" href="<?= e($item['facebook_event_url']) ?>" target="_blank" rel="noopener" data-analytics-event="<?= e((string) $item['id']) ?>" data-analytics-action="facebook"><strong>f</strong> Dołącz na FB</a>
            <?php endif; ?>
        </nav>
    </div>

    <a class="calendar-image" href="/wydarzenia/<?= e($item['slug']) ?>" aria-label="Zobacz wydarzenie <?= e($item['title']) ?>" data-analytics-event="<?= e((string) $item['id']) ?>" data-analytics-action="details">
        <img src="<?= e($item['hero_image'] ?: '/assets/images/hero-students-party.png') ?>" alt="<?= e($item['title']) ?>">
    </a>
</article>
