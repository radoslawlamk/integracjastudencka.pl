<header class="admin-head">
    <div><p>CRM</p><h1>Powiadomienia o biletach</h1></div>
    <a class="ghost" href="/admin/wydarzenia">Wydarzenia</a>
</header>

<section class="panel">
    <form method="get" action="/admin/powiadomienia-bilety" class="admin-filter">
        <label>Wydarzenie
            <select name="event_id">
                <option value="">Wszystkie wydarzenia</option>
                <?php foreach ($events as $event): ?>
                    <option value="<?= e((string) $event['id']) ?>" <?= $selectedEventId === (int) $event['id'] ? 'selected' : '' ?>>
                        <?= e($event['title']) ?> (<?= e($event['city']) ?>, <?= e((string) $event['waitlist_count']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Status wysyłki
            <select name="status">
                <option value="">Wszyscy</option>
                <option value="pending" <?= $selectedStatus === 'pending' ? 'selected' : '' ?>>Oczekuje</option>
                <option value="notified" <?= $selectedStatus === 'notified' ? 'selected' : '' ?>>Powiadomiony</option>
            </select>
        </label>
        <button class="admin-btn">Filtruj</button>
        <?php if ($selectedEventId || $selectedStatus): ?><a class="ghost" href="/admin/powiadomienia-bilety">Wyczyść filtr</a><?php endif; ?>
    </form>
</section>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Wysyłka według wydarzeń</h2>
            <p class="muted">Tu najłatwiej sprawdzisz, przy których wydarzeniach ktoś czeka na start sprzedaży.</p>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Wydarzenie</th>
                <th>Wszystkich</th>
                <th>Oczekuje</th>
                <th>Powiadomionych</th>
                <th>Link do biletów</th>
                <th>Akcja</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($events as $event): ?>
                <tr>
                    <td>
                        <strong><?= e($event['title']) ?></strong><br>
                        <small><?= e($event['city']) ?>, <?= e(date('d.m.Y H:i', strtotime($event['starts_at']))) ?></small>
                    </td>
                    <td><?= e((string) $event['waitlist_count']) ?></td>
                    <td><?= e((string) $event['pending_count']) ?></td>
                    <td><?= e((string) $event['notified_count']) ?></td>
                    <td><?= !empty($event['ticket_url']) ? 'Dodany' : 'Brak' ?></td>
                    <td>
                        <?php if (!empty($event['ticket_url']) && (int) $event['pending_count'] > 0): ?>
                            <form method="post" action="/admin/wydarzenia/<?= e((string) $event['id']) ?>/powiadom-bilety">
                                <?= csrf_field() ?>
                                <button class="admin-btn" onclick="return confirm('Wysłać powiadomienia email dla tego wydarzenia?')">Powiadom klientów</button>
                            </form>
                        <?php else: ?>
                            <a class="ghost" href="/admin/wydarzenia/<?= e((string) $event['id']) ?>/edycja">Edytuj wydarzenie</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$events): ?>
                <tr><td colspan="6">Nie ma jeszcze zapisów na powiadomienia o biletach.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Lista oczekujących</h2>
            <p class="muted">To osobna baza osób, które zapisały się na powiadomienie o biletach dla konkretnego wydarzenia.</p>
        </div>
        <strong><?= count($waitlist) ?> zapisów</strong>
    </div>

    <table>
        <thead>
            <tr>
                <th>Wydarzenie</th>
                <th>Imię</th>
                <th>Email</th>
                <th>Telefon</th>
                <th>Miasto</th>
                <th>Kanały</th>
                <th>Status</th>
                <th>Wysłano</th>
                <th>Data zapisu</th>
                <th>Akcja</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($waitlist as $row): ?>
                <tr>
                    <td>
                        <strong><?= e($row['event_title']) ?></strong><br>
                        <small><?= e(date('d.m.Y H:i', strtotime($row['starts_at']))) ?></small>
                    </td>
                    <td><?= e($row['name'] ?: '-') ?></td>
                    <td><?= e($row['email'] ?: '-') ?></td>
                    <td><?= e($row['phone'] ?: '-') ?></td>
                    <td><?= e($row['city'] ?: '-') ?></td>
                    <td><?= !empty($row['sms_consent']) ? 'SMS ' : '' ?><?= !empty($row['email_consent']) ? 'Email' : '' ?></td>
                    <td><?= $row['notified_at'] ? 'Powiadomiony' : 'Oczekuje' ?></td>
                    <td><?= e($row['notified_at'] ?: '-') ?></td>
                    <td><?= e($row['created_at'] ?? '-') ?></td>
                    <td>
                        <a class="ghost" href="/admin/wydarzenia/<?= e((string) $row['event_id']) ?>/edycja">Edytuj wydarzenie</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$waitlist): ?>
                <tr><td colspan="10">Brak zapisów dla wybranego filtra.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>
