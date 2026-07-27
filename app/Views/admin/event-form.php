<?php
$isEdit = (bool) $event;
$action = $isEdit ? '/admin/wydarzenia/' . $event['id'] : '/admin/wydarzenia';
$value = fn(string $key, string $default = '') => e($event[$key] ?? $default);
$datetime = $event ? date('Y-m-d\TH:i', strtotime($event['starts_at'])) : date('Y-m-d\TH:i');
$endDatetime = $event && !empty($event['ends_at']) ? date('Y-m-d\TH:i', strtotime($event['ends_at'])) : date('Y-m-d\TH:i', strtotime(($event['starts_at'] ?? 'now') . ' +6 hours'));
$clubImages = $clubImages ?? [];
$partnerLogos = $partnerLogos ?? [];
$ticketWaitlist = $ticketWaitlist ?? [];
$ticketNotifyStatus = $ticketNotifyStatus ?? '';
$ticketNotifyCount = $ticketNotifyCount ?? 0;
$fanpageOptions = [
    'Białystok' => 'https://www.facebook.com/BialostockaIntegracjaStudencka',
    'Bydgoszcz' => 'https://www.facebook.com/BydgoskaIntegracjaStudencka',
    'Częstochowa' => 'https://www.facebook.com/CzestochowskaIntegracjaStudencka',
    'Katowice' => 'https://www.facebook.com/SlaskaIntegracjaStudencka',
    'Kielce' => 'https://www.facebook.com/KieleckaIntegracjaStudencka',
    'Kraków' => 'https://www.facebook.com/KrakowskaIntegracjaStudencka',
    'Łódź' => 'https://www.facebook.com/LodzkaIntegracjaStudencka',
    'Lublin' => 'https://www.facebook.com/LUBELSKAINTEGRACJA',
    'Olsztyn' => 'https://www.facebook.com/OlsztynskaIntegracjaStudencka',
    'Opole' => 'https://www.facebook.com/OpolskaIntegracjaStudencka',
    'Poznań' => 'https://www.facebook.com/PoznanskaIntegracjaStudenckaa',
    'Radom' => 'https://www.facebook.com/RadomskaIntegracjaStudencka',
    'Rzeszów' => 'https://www.facebook.com/RzeszowskaIntegracjaStudencka',
    'Szczecin' => 'https://www.facebook.com/SzczecinskaIntegracjaStudencka',
    'Tarnów' => 'https://www.facebook.com/StudenciTarnow',
    'Toruń' => 'https://www.facebook.com/TorunskaIntegracjaStudencka',
    'Trójmiasto' => 'https://www.facebook.com/TrojmiejskaIntegracjaStudencka',
    'Warszawa' => 'https://www.facebook.com/WarszawskaIntegracjaStudencka',
    'Wrocław' => 'https://www.facebook.com/WroclawskaIntegracjaStudencka',
    'Zielona Góra' => 'https://www.facebook.com/ZielonogorskaIntegracjaStudencka',
];
$selectedFanpageUrl = trim((string) ($event['fanpage_url'] ?? ''));
$selectedFanpageCity = array_search($selectedFanpageUrl, $fanpageOptions, true);
$seoChecklist = [
    'Nazwa wydarzenia' => trim((string) ($event['title'] ?? '')) !== '',
    'Miasto' => trim((string) ($event['city'] ?? '')) !== '',
    'Data startu' => trim((string) ($event['starts_at'] ?? '')) !== '',
    'Data zakończenia' => trim((string) ($event['ends_at'] ?? '')) !== '',
    'Grafika do social media' => trim((string) ($event['hero_image'] ?? '')) !== '',
    'Krótki opis' => trim((string) ($event['short_description'] ?? '')) !== '',
    'SEO title' => trim((string) ($event['seo_title'] ?? '')) !== '',
    'SEO description' => trim((string) ($event['seo_description'] ?? '')) !== '',
    'Link wydarzenia FB' => trim((string) ($event['facebook_event_url'] ?? '')) !== '',
    'Status biletów' => trim((string) ($event['sales_status'] ?? 'auto')) !== '',
];
?>
<header class="admin-head">
    <div><p>CRM</p><h1><?= $isEdit ? 'Edytuj wydarzenie' : 'Dodaj wydarzenie' ?></h1></div>
    <a class="ghost" href="/admin/wydarzenia">Wróć</a>
</header>
<form class="editor" method="post" action="<?= e($action) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <section class="panel form-grid">
        <label>Typ wydarzenia
            <select name="type" id="eventType">
                <option value="regular" <?= ($event['type'] ?? '') === 'regular' ? 'selected' : '' ?>>Wydarzenie zwykłe</option>
                <option value="clubbing" <?= ($event['type'] ?? '') === 'clubbing' ? 'selected' : '' ?>>Clubbing</option>
            </select>
        </label>
        <label>Status
            <select name="status">
                <option value="draft" <?= ($event['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Szkic</option>
                <option value="published" <?= ($event['status'] ?? '') === 'published' ? 'selected' : '' ?>>Opublikowane</option>
                <option value="archived" <?= ($event['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archiwalne</option>
            </select>
        </label>
        <label>Status biletów
            <select name="sales_status">
                <option value="auto" <?= ($event['sales_status'] ?? 'auto') === 'auto' ? 'selected' : '' ?>>Automatycznie</option>
                <option value="tickets_soon" <?= ($event['sales_status'] ?? '') === 'tickets_soon' ? 'selected' : '' ?>>Bilety wkrótce</option>
                <option value="on_sale" <?= ($event['sales_status'] ?? '') === 'on_sale' ? 'selected' : '' ?>>Sprzedaż trwa</option>
                <option value="sold_out" <?= ($event['sales_status'] ?? '') === 'sold_out' ? 'selected' : '' ?>>Wyprzedane</option>
                <option value="finished" <?= ($event['sales_status'] ?? '') === 'finished' ? 'selected' : '' ?>>Zakończone</option>
            </select>
        </label>
        <label>Nazwa wydarzenia <input name="title" value="<?= $value('title') ?>" required></label>
        <label>Adres strony / slug <input name="slug" value="<?= $value('slug') ?>" placeholder="np. trojmiejska-integracja-studencka-trojmiasto"></label>
        <label>Miasto <input name="city" value="<?= $value('city') ?>" required></label>
        <label>Region <input name="region" value="<?= $value('region') ?>" placeholder="np. Trójmiasto"></label>
        <label>Data i godzina startu <input type="datetime-local" name="starts_at" value="<?= e($datetime) ?>" required></label>
        <label>Data i godzina zakończenia <input type="datetime-local" name="ends_at" value="<?= e($endDatetime) ?>" required></label>
        <label>Miejsce główne <input name="venue_name" value="<?= $value('venue_name') ?>"></label>
        <label>Adres główny <input name="venue_address" value="<?= $value('venue_address') ?>"></label>
        <label>Grafika główna z komputera <input type="file" name="hero_upload" accept="image/jpeg,image/png,image/webp,image/gif"></label>
        <label>Grafika główna URL <input name="hero_image" value="<?= $value('hero_image') ?>" placeholder="Opcjonalnie, gdy obraz jest już online"></label>
        <label>Link Kup bilet <input name="ticket_url" value="<?= $value('ticket_url') ?>"></label>
        <label>Link DOŁĄCZ DO WYDARZENIA Facebook <input name="facebook_event_url" value="<?= $value('facebook_event_url') ?>"></label>
        <div class="fanpage-picker">
            <label>POLUB NAS fanpage - wybierz miasto
                <select data-fanpage-select>
                    <option value="">Wybierz fanpage miasta</option>
                    <?php foreach ($fanpageOptions as $cityName => $fanpageUrl): ?>
                        <option value="<?= e($fanpageUrl) ?>" <?= $selectedFanpageCity === $cityName ? 'selected' : '' ?>><?= e($cityName) ?></option>
                    <?php endforeach; ?>
                    <option value="custom" <?= $selectedFanpageUrl !== '' && $selectedFanpageCity === false ? 'selected' : '' ?>>Inne miasto / własny link</option>
                </select>
            </label>
            <label>Link POLUB NAS fanpage
                <input name="fanpage_url" value="<?= $value('fanpage_url') ?>" data-fanpage-input placeholder="https://www.facebook.com/...">
            </label>
            <small>Po wyborze miasta CRM sam uzupełni link. Przy opcji „inne” wpisz własny adres fanpage.</small>
        </div>
        <label class="wide">Krótki opis <textarea name="short_description" rows="3" maxlength="220" data-counter="shortDescription"><?= $value('short_description') ?></textarea><small><span id="shortDescriptionCount">0</span>/220 znaków</small></label>
        <label class="wide">Pełny opis <textarea name="description" rows="9"><?= $value('description') ?></textarea></label>
        <label>SEO title <input name="seo_title" value="<?= $value('seo_title') ?>"></label>
        <label>SEO description <input name="seo_description" value="<?= $value('seo_description') ?>"></label>
    </section>

    <?php if ($isEdit): ?>
        <section class="panel seo-checklist-panel">
            <div class="panel-head">
                <div>
                    <h2>Checklist SEO i publikacji</h2>
                    <p class="muted">Szybka kontrola, czy wydarzenie jest gotowe do promowania i udostępniania.</p>
                </div>
            </div>
            <div class="seo-checklist">
                <?php foreach ($seoChecklist as $label => $ok): ?>
                    <span class="<?= $ok ? 'is-ok' : 'is-missing' ?>"><?= $ok ? 'OK' : 'BRAK' ?> - <?= e($label) ?></span>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="panel relation-panel" data-kind="clubs">
        <div class="panel-head"><h2>Kluby dla clubbingu</h2><button type="button" class="ghost" data-add="club">Dodaj klub</button></div>
        <div id="clubsRows">
            <?php $clubRows = $clubs ?: array_fill(0, 3, []); foreach ($clubRows as $i => $club): ?>
                <div class="mini-grid">
                    <input name="clubs[<?= $i ?>][name]" placeholder="Nazwa klubu" value="<?= e($club['name'] ?? '') ?>">
                    <input name="clubs[<?= $i ?>][address]" placeholder="Adres" value="<?= e($club['address'] ?? '') ?>">
                    <input name="clubs[<?= $i ?>][map_url]" placeholder="Link do mapy" value="<?= e($club['map_url'] ?? '') ?>">
                    <input name="clubs[<?= $i ?>][image_url]" placeholder="Logo/zdjęcie URL" value="<?= e($club['image_url'] ?? '') ?>">
                    <label class="file-inline">Wgraj logo/zdjęcie klubu <input type="file" name="club_uploads[<?= $i ?>]" accept="image/jpeg,image/png,image/webp,image/gif"></label>
                    <select name="clubs[<?= $i ?>][image_library]">
                        <option value="">Wybierz z wgranych zdjęć klubów</option>
                        <?php foreach ($clubImages as $image): ?>
                            <option value="<?= e($image) ?>" <?= ($club['image_url'] ?? '') === $image ? 'selected' : '' ?>><?= e(basename($image)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <textarea name="clubs[<?= $i ?>][description]" placeholder="Opis klubu"><?= e($club['description'] ?? '') ?></textarea>
                    <button type="button" class="remove-row" data-remove-row>Usuń klub</button>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h2>Filmy YouTube</h2>
                <p class="muted">Filmy wyświetlają się jako player na stronie wydarzenia. YouTube musi zezwalać na osadzanie danego filmu.</p>
            </div>
            <button type="button" class="ghost" data-add="video">Dodaj film</button>
        </div>
        <div id="videosRows">
            <?php $videoRows = $videos ?: array_fill(0, 3, []); foreach ($videoRows as $i => $video): ?>
                <div class="mini-grid two">
                    <input name="videos[<?= $i ?>][title]" placeholder="Tytuł filmu" value="<?= e($video['title'] ?? '') ?>">
                    <input name="videos[<?= $i ?>][youtube_url]" placeholder="Link YouTube" value="<?= e($video['youtube_url'] ?? '') ?>">
                    <button type="button" class="remove-row" data-remove-row>Usuń film</button>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Partnerzy wydarzenia</h2><button type="button" class="ghost" data-add="partner">Dodaj partnera</button></div>
        <div id="partnersRows">
            <?php $partnerRows = $partners ?: array_fill(0, 3, []); foreach ($partnerRows as $i => $partner): ?>
                <div class="mini-grid">
                    <input name="partners[<?= $i ?>][name]" placeholder="Nazwa partnera" value="<?= e($partner['name'] ?? '') ?>">
                    <input name="partners[<?= $i ?>][category]" placeholder="Typ, np. partner / patron" value="<?= e($partner['category'] ?? '') ?>">
                    <input name="partners[<?= $i ?>][logo_url]" placeholder="Logo URL" value="<?= e($partner['logo_url'] ?? '') ?>">
                    <input name="partners[<?= $i ?>][website_url]" placeholder="Link" value="<?= e($partner['website_url'] ?? '') ?>">
                    <label class="file-inline">Wgraj logo partnera <input type="file" name="partner_logo_uploads[<?= $i ?>]" accept="image/jpeg,image/png,image/webp,image/gif"></label>
                    <select name="partners[<?= $i ?>][logo_library]">
                        <option value="">Wybierz z wgranych logo partnerów</option>
                        <?php foreach ($partnerLogos as $logo): ?>
                            <option value="<?= e($logo) ?>" <?= ($partner['logo_url'] ?? '') === $logo ? 'selected' : '' ?>><?= e(basename($logo)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="remove-row" data-remove-row>Usuń partnera</button>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <div class="sticky-actions">
        <button class="admin-btn">Zapisz wydarzenie</button>
        <?php if ($isEdit): ?>
            <button class="danger" formaction="/admin/wydarzenia/<?= e((string) $event['id']) ?>/usun" onclick="return confirm('Usunąć wydarzenie?')">Usuń</button>
        <?php endif; ?>
    </div>
</form>
<?php if ($isEdit): ?>
    <section class="panel ticket-admin-panel">
        <div class="panel-head">
            <div>
                <h2>Oczekujący na bilety</h2>
                <p class="muted">Osoby zapisane z podstrony tego wydarzenia na informację „Bilety wkrótce”.</p>
            </div>
            <strong><?= count($ticketWaitlist) ?> zapisów</strong>
        </div>

        <?php if ($ticketNotifyStatus === 'wyslano'): ?>
            <p class="alert success">Wysłano powiadomienia email: <?= e((string) $ticketNotifyCount) ?>.</p>
        <?php elseif ($ticketNotifyStatus === 'auto_wyslano'): ?>
            <p class="alert success">Link do biletów został dodany. Automatycznie wysłano powiadomienia email: <?= e((string) $ticketNotifyCount) ?>.</p>
        <?php elseif ($ticketNotifyStatus === 'brak_linku'): ?>
            <p class="alert">Najpierw dodaj link „Kup bilet”, potem użyj przycisku powiadamiania.</p>
        <?php elseif ($ticketNotifyStatus === 'brak'): ?>
            <p class="alert">Brak nowych osób z adresem email do powiadomienia.</p>
        <?php endif; ?>

        <?php if ($ticketWaitlist): ?>
            <table>
                <thead>
                    <tr>
                        <th>Imię</th>
                        <th>Email</th>
                        <th>Telefon</th>
                        <th>Kanały</th>
                        <th>Status</th>
                        <th>Wysłano</th>
                        <th>Data zapisu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ticketWaitlist as $waiter): ?>
                        <tr>
                            <td><?= e($waiter['name'] ?: '-') ?></td>
                            <td><?= e($waiter['email'] ?: '-') ?></td>
                            <td><?= e($waiter['phone'] ?: '-') ?></td>
                            <td>
                                <?= !empty($waiter['email_consent']) ? 'Email ' : '' ?>
                                <?= !empty($waiter['sms_consent']) ? 'SMS' : '' ?>
                            </td>
                            <td><?= $waiter['notified_at'] ? 'Powiadomiony' : 'Oczekuje' ?></td>
                            <td><?= e($waiter['notified_at'] ?: '-') ?></td>
                            <td><?= e($waiter['created_at'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="muted">Na razie nikt nie zapisał się na powiadomienie o biletach dla tego wydarzenia.</p>
        <?php endif; ?>

        <?php if (!empty($event['ticket_url'])): ?>
            <form method="post" action="/admin/wydarzenia/<?= e((string) $event['id']) ?>/powiadom-bilety" class="ticket-admin-actions">
                <?= csrf_field() ?>
                <button class="admin-btn" onclick="return confirm('Wysłać powiadomienie email do oczekujących?')">Powiadom klientów</button>
                <small>Przycisk wysyła email do osób, które czekają na bilety i nie były jeszcze powiadomione. Numery SMS zostają w CRM do późniejszej wysyłki przez bramkę SMS.</small>
            </form>
        <?php else: ?>
            <p class="muted">Gdy wpiszesz link „Kup bilet” i zapiszesz wydarzenie, pojawi się tutaj przycisk „Powiadom klientów”.</p>
        <?php endif; ?>
    </section>
<?php endif; ?>
<script>
window.crmAssetLibraries = {
  clubImages: <?= json_encode($clubImages, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>,
  partnerLogos: <?= json_encode($partnerLogos, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
};
</script>
