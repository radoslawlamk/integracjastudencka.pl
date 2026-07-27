<?php
$date = strtotime($event['starts_at']);
$eventState = event_sales_state($event);
$eventTypeLabel = $event['type'] === 'clubbing' ? 'Clubbing' : 'Wydarzenie studenckie';
$isClubbing = $event['type'] === 'clubbing';
$city = $event['city'] ?: 'Cała Polska';
$venueName = $event['venue_name'] ?: 'Szczegóły wkrótce';
$venueAddress = $event['venue_address'] ?: '';
$venueMapLine = trim($venueName . ' ' . ($venueAddress ?: $city));
$mapQuery = trim($venueName . ' ' . $venueAddress . ' ' . $city);
$eventImage = $event['hero_image'] ?: '/assets/images/hero-students-party.png';
$cityEventsUrl = '/wydarzenia?city=' . rawurlencode($city);
$eventCityPath = $eventCityPath ?? $cityEventsUrl;
$moreCityEventsUrl = !empty($relatedEvents) ? '#pozostale-imprezy' : $cityEventsUrl . '#wydarzenia';
$weekdays = ['Sunday' => 'Niedziela', 'Monday' => 'Poniedziałek', 'Tuesday' => 'Wtorek', 'Wednesday' => 'Środa', 'Thursday' => 'Czwartek', 'Friday' => 'Piątek', 'Saturday' => 'Sobota'];
$weekday = $weekdays[date('l', $date)] ?? date('l', $date);
$endDate = $eventState['ends_at'];
$endWeekday = $weekdays[date('l', $endDate)] ?? date('l', $endDate);
$youtubeVideoId = static function (string $url): ?string {
    $parts = parse_url($url);
    if (!$parts || empty($parts['host'])) {
        return null;
    }

    $host = strtolower($parts['host']);
    $path = trim($parts['path'] ?? '', '/');
    $id = null;

    if (str_contains($host, 'youtu.be')) {
        $id = explode('/', $path)[0] ?? null;
    } elseif (str_contains($host, 'youtube.com')) {
        if ($path === 'watch') {
            parse_str($parts['query'] ?? '', $query);
            $id = $query['v'] ?? null;
        } elseif (str_starts_with($path, 'embed/')) {
            $id = substr($path, 6);
        } elseif (str_starts_with($path, 'shorts/')) {
            $id = substr($path, 7);
        } elseif (str_starts_with($path, 'live/')) {
            $id = substr($path, 5);
        }
    }

    $id = $id ? preg_replace('/[^A-Za-z0-9_-]/', '', $id) : null;
    return $id ?: null;
};
$youtubeEmbedUrl = static function (string $videoId): string {
    return 'https://www.youtube.com/embed/' . rawurlencode($videoId) . '?rel=0&playsinline=1';
};
$clubbingEmbedVideos = [
    [
        'title' => 'Trójmiejska Integracja Studencka - relacja video',
        'src' => 'https://www.trojmiasto.pl/video/embed/24241',
    ],
    [
        'title' => 'Nocne życie Trójmiasta i studencki clubbing',
        'src' => 'https://www.trojmiasto.pl/video/embed/9724',
    ],
    [
        'title' => 'Trójmiejska Integracja Studencka 2016',
        'src' => 'https://www.trojmiasto.pl/video/embed/17925',
    ],
];
?>
<?php if (in_array($event['type'], ['regular', 'clubbing'], true)): ?>
<main class="regular-event-page">
    <section class="regular-event-shell">
        <div class="regular-event-top">
            <a href="/#wydarzenia">Wróć do imprez w całej Polsce</a>
            <nav aria-label="Szybkie linki wydarzenia">
                <a href="/#wydarzenia">Cała Polska</a>
                <a href="<?= e($eventCityPath) ?>"><?= e($city) ?></a>
                <?php if ($isClubbing): ?>
                    <a href="#kluby">Kluby</a>
                <?php endif; ?>
                <?php if ($eventState['can_buy']): ?>
                    <a href="<?= e($event['ticket_url']) ?>" target="_blank" rel="noopener" data-analytics-event="<?= e((string) $event['id']) ?>" data-analytics-action="ticket">Kup bilet</a>
                <?php elseif ($eventState['finished']): ?>
                    <a href="<?= e($moreCityEventsUrl) ?>">Kolejne imprezy</a>
                <?php endif; ?>
            </nav>
        </div>

        <div class="regular-event-grid">
            <section class="regular-event-main">
                <p class="regular-kicker">
                    <?= e($city) ?> - najważniejsze imprezy akademickie<?= $event['venue_name'] ? ' - ' . e($event['venue_name']) : '' ?>
                </p>
                <h1><?= e($event['title']) ?></h1>

                <div class="regular-alert">
                    <span aria-hidden="true"></span>
                    <?php if ($eventState['can_buy']): ?>
                        <strong>Pospiesz się, kończą się kolejne pule biletów.</strong>
                        <a href="<?= e($event['ticket_url']) ?>" target="_blank" rel="noopener" data-analytics-event="<?= e((string) $event['id']) ?>" data-analytics-action="ticket">Kup teraz</a>
                    <?php elseif ($eventState['sold_out']): ?>
                        <strong>Bilety na to wydarzenie są wyprzedane.</strong>
                        <a href="<?= e($moreCityEventsUrl) ?>">Zobacz kolejne imprezy</a>
                    <?php elseif ($eventState['finished']): ?>
                        <strong>To wydarzenie już się zakończyło.</strong>
                        <a href="<?= e($moreCityEventsUrl) ?>">Zobacz kolejne imprezy</a>
                    <?php else: ?>
                        <strong>Bilety pojawią się niebawem!</strong>
                        <a href="#bilety-wkrotce" data-analytics-event="<?= e((string) $event['id']) ?>" data-analytics-action="ticket_notify">Powiadom mnie</a>
                    <?php endif; ?>
                </div>

                <?php if ($isClubbing): ?>
                    <div class="clubbing-slogan" aria-label="Atuty clubbingu">
                        <strong>1 bilet</strong>
                        <strong>Najlepsze kluby</strong>
                        <strong>Tysiące uczestników</strong>
                    </div>
                <?php endif; ?>

                <article class="regular-description">
                    <span>Opis wydarzenia</span>
                    <?php if ($event['short_description']): ?>
                        <p class="regular-lead"><?= e($event['short_description']) ?></p>
                    <?php endif; ?>
                    <?php if ($event['description'] && trim($event['description']) !== trim($event['short_description'] ?? '')): ?>
                        <button class="regular-read-more" type="button" data-read-more>czytaj więcej</button>
                        <div class="regular-full-description" hidden><?= nl2br(e($event['description'])) ?></div>
                    <?php endif; ?>
                </article>

                <div class="regular-facts">
                    <article>
                        <span>Data</span>
                        <strong><?= e(date('d.m.Y', $date)) ?></strong>
                        <small><?= e($weekday . ' - ' . date('H:i', $date)) ?></small>
                        <small>Koniec: <?= e(date('d.m.Y H:i', $endDate)) ?></small>
                    </article>
                    <article>
                        <span>Miejsce</span>
                        <?php if ($isClubbing): ?>
                            <strong>Najlepsze kluby</strong>
                            <small><?= e($city) ?> · 1 bilet</small>
                        <?php else: ?>
                            <strong><?= e($venueName) ?></strong>
                            <small><?= e($venueAddress ?: $city) ?></small>
                        <?php endif; ?>
                    </article>
                    <article>
                        <span data-countdown-label><?= $eventState['in_progress'] ? 'Status' : 'Start za' ?></span>
                        <strong data-countdown="<?= e(date('c', $date)) ?>" data-countdown-end="<?= e(date('c', $endDate)) ?>">--</strong>
                        <small><?= $eventState['in_progress'] ? 'Impreza właśnie trwa.' : 'Odliczamy do rozpoczęcia imprezy.' ?></small>
                    </article>
                </div>

                <?php if ($eventState['can_buy']): ?>
                    <a class="regular-ticket" href="<?= e($event['ticket_url']) ?>" target="_blank" rel="noopener" data-analytics-event="<?= e((string) $event['id']) ?>" data-analytics-action="ticket">
                        Kup bilet <span>teraz</span>
                    </a>
                <?php elseif ($eventState['sold_out']): ?>
                    <a class="regular-ticket regular-ticket-muted" href="<?= e($moreCityEventsUrl) ?>">
                        Wyprzedane <span>zobacz kolejne</span>
                    </a>
                <?php elseif ($eventState['finished']): ?>
                    <a class="regular-ticket regular-ticket-muted" href="<?= e($moreCityEventsUrl) ?>">
                        Wydarzenie zakończone <span>kolejne terminy</span>
                    </a>
                <?php else: ?>
                    <section class="ticket-waitlist-box" id="bilety-wkrotce">
                        <div>
                            <span>Bilety wkrótce</span>
                            <h2>Powiadom mnie, gdy bilety będą dostępne</h2>
                            <p>Zostaw kontakt do tej konkretnej imprezy, a damy znać od razu po uruchomieniu sprzedaży.</p>
                        </div>
                        <?php if (($_GET['bilety'] ?? '') === 'ok'): ?>
                            <p class="ticket-waitlist-message success">Gotowe. Zapisaliśmy Cię na powiadomienie o biletach.</p>
                        <?php elseif (($_GET['bilety'] ?? '') === 'blad'): ?>
                            <p class="ticket-waitlist-message error">Uzupełnij imię i wybierz przynajmniej jeden kanał powiadomienia.</p>
                        <?php endif; ?>
                        <button class="ticket-waitlist-toggle" type="button" data-ticket-waitlist-toggle aria-expanded="<?= (($_GET['bilety'] ?? '') === 'blad') ? 'true' : 'false' ?>">
                            Zapisz się na powiadomienie
                        </button>
                        <form method="post" action="/powiadom-o-biletach" class="ticket-waitlist-form" data-ticket-waitlist-form <?= (($_GET['bilety'] ?? '') === 'blad') ? '' : 'hidden' ?>>
                            <?= csrf_field() ?>
                            <?= spam_trap_field() ?>
                            <input type="hidden" name="event_id" value="<?= e((string) $event['id']) ?>">
                            <label>Imię <input name="name" placeholder="np. Ola" required></label>
                            <label>Email <input name="email" type="email" placeholder="Adres email"></label>
                            <label class="ticket-phone-field">Telefon <input name="phone" placeholder="Numer do SMS"></label>
                            <label class="ticket-waitlist-check"><input type="checkbox" name="sms_consent" value="1"> Chcę powiadomienie SMS</label>
                            <label class="ticket-waitlist-check"><input type="checkbox" name="email_consent" value="1"> Chcę powiadomienie email</label>
                            <p>Ten zapis dotyczy biletów na wydarzenie: <?= e($event['title']) ?>. Zgodę możesz wycofać w każdej chwili.</p>
                            <button type="submit">Powiadom mnie o biletach</button>
                        </form>
                    </section>
                <?php endif; ?>

                <div class="regular-social">
                    <?php if ($event['facebook_event_url']): ?>
                        <a href="<?= e($event['facebook_event_url']) ?>" target="_blank" rel="noopener" data-analytics-event="<?= e((string) $event['id']) ?>" data-analytics-action="facebook"><span>f</span> Dołącz do wydarzenia</a>
                    <?php endif; ?>
                    <?php if ($event['fanpage_url']): ?>
                        <a href="<?= e($event['fanpage_url']) ?>" target="_blank" rel="noopener" data-analytics-event="<?= e((string) $event['id']) ?>" data-analytics-action="fanpage"><span>f</span> Polub nas</a>
                    <?php endif; ?>
                </div>

                <a class="regular-more" href="<?= e($moreCityEventsUrl) ?>">Zobacz pozostałe imprezy w <?= e($city) ?></a>
            </section>

            <aside class="regular-event-side">
                <img class="regular-poster" src="<?= e($eventImage) ?>" alt="<?= e($event['title']) ?>">

                <section class="regular-map-card">
                    <span>Jak trafić do klubu?</span>
                    <h2 class="regular-map-line"><?= e($venueMapLine) ?></h2>
                    <?php if ($mapQuery): ?>
                        <iframe loading="lazy" src="https://maps.google.com/maps?q=<?= e(urlencode($mapQuery)) ?>&output=embed"></iframe>
                        <a href="https://www.google.com/maps/search/?api=1&query=<?= e(urlencode($mapQuery)) ?>" target="_blank" rel="noopener">Otwórz trasę</a>
                    <?php endif; ?>
                </section>
            </aside>
        </div>
    </section>

    <?php if ($isClubbing): ?>
        <section class="clubbing-clubs-section" id="kluby">
            <div class="clubbing-clubs-head">
                <div>
                    <p class="eyebrow">Kluby</p>
                    <h2>Kluby biorące udział w wydarzeniu</h2>
                    <strong>Pozostałe lokale ogłosimy w trakcie!</strong>
                </div>
                <p>Potwierdzone lokale już są na trasie, a kolejne niespodzianki będziemy odsłaniać bliżej wydarzenia.</p>
            </div>
            <div class="clubbing-clubs-grid">
                <?php foreach ($clubs ?: array_fill(0, 4, ['name' => 'Klub wkrótce', 'address' => $city, 'map_url' => '', 'image_url' => '']) as $club): ?>
                    <?php $clubMapUrl = $club['map_url'] ?: ('https://www.google.com/maps/search/?api=1&query=' . rawurlencode(trim($club['name'] . ' ' . $club['address'] . ' ' . $city))); ?>
                    <article class="clubbing-club-card">
                        <div class="clubbing-club-logo">
                            <?php if ($club['image_url']): ?>
                                <img src="<?= e($club['image_url']) ?>" alt="<?= e($club['name']) ?>">
                            <?php else: ?>
                                <span><?= e(strtoupper(substr($club['name'], 0, 2))) ?></span>
                            <?php endif; ?>
                        </div>
                        <h3><?= e($club['name']) ?></h3>
                        <p><?= e($club['address'] ?: $city) ?></p>
                        <a href="<?= e($clubMapUrl) ?>" target="_blank" rel="noopener">Nawiguj</a>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="clubbing-clubs-actions">
                <?php if ($eventState['can_buy']): ?>
                    <a class="regular-ticket clubbing-ticket" href="<?= e($event['ticket_url']) ?>" target="_blank" rel="noopener" data-analytics-event="<?= e((string) $event['id']) ?>" data-analytics-action="ticket">
                        Kup bilet <span>na clubbing</span>
                    </a>
                <?php elseif ($eventState['sold_out']): ?>
                    <a class="regular-more ticket-soon-link" href="<?= e($moreCityEventsUrl) ?>">Wyprzedane - zobacz kolejne imprezy</a>
                <?php elseif ($eventState['finished']): ?>
                    <a class="regular-more ticket-soon-link" href="<?= e($moreCityEventsUrl) ?>">Wydarzenie zakończone - kolejne terminy</a>
                <?php else: ?>
                    <a class="regular-more ticket-soon-link" href="#bilety-wkrotce" data-analytics-event="<?= e((string) $event['id']) ?>" data-analytics-action="ticket_notify">Powiadom mnie o biletach</a>
                <?php endif; ?>
                <a class="regular-more" href="<?= e($moreCityEventsUrl) ?>">Zobacz pozostałe imprezy w <?= e($city) ?></a>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($relatedEvents)): ?>
        <section class="related-city-events" id="pozostale-imprezy">
            <div class="related-city-head">
                <div>
                    <p class="eyebrow"><?= e($city) ?></p>
                    <h2>Pozostałe imprezy w tym mieście</h2>
                </div>
                <p>Największe i najważniejsze Inauguracje, Integracje i Otrzęsiny w mieście akademickim.</p>
            </div>
            <div class="related-city-grid">
                <?php foreach ($relatedEvents as $relatedEvent): ?>
                    <?php $relatedDate = strtotime($relatedEvent['starts_at']); $relatedState = event_sales_state($relatedEvent); ?>
                    <article class="related-city-card">
                        <a class="related-city-image" href="/wydarzenia/<?= e($relatedEvent['slug']) ?>">
                            <img src="<?= e($relatedEvent['hero_image'] ?: '/assets/images/hero-students-party.png') ?>" alt="<?= e($relatedEvent['title']) ?>">
                        </a>
                        <div>
                            <small><?= e(date('d.m.Y', $relatedDate)) ?> • <?= e($relatedEvent['venue_name'] ?: $relatedEvent['city']) ?></small>
                            <h3><a href="/wydarzenia/<?= e($relatedEvent['slug']) ?>"><?= e($relatedEvent['title']) ?></a></h3>
                            <nav aria-label="Akcje wydarzenia <?= e($relatedEvent['title']) ?>">
                                <?php if ($relatedState['can_buy']): ?>
                                    <a class="related-ticket" href="<?= e($relatedEvent['ticket_url']) ?>" target="_blank" rel="noopener" data-analytics-event="<?= e((string) $relatedEvent['id']) ?>" data-analytics-action="ticket">Kup bilet</a>
                                <?php elseif ($relatedState['sold_out']): ?>
                                    <span>Wyprzedane</span>
                                <?php elseif ($relatedState['finished']): ?>
                                    <span>Zakończone</span>
                                <?php else: ?>
                                    <span>Bilety wkrótce</span>
                                <?php endif; ?>
                                <a href="/wydarzenia/<?= e($relatedEvent['slug']) ?>" data-analytics-event="<?= e((string) $relatedEvent['id']) ?>" data-analytics-action="details">Sprawdź szczegóły</a>
                                <?php if ($relatedEvent['facebook_event_url']): ?>
                                    <a class="related-facebook" href="<?= e($relatedEvent['facebook_event_url']) ?>" target="_blank" rel="noopener" data-analytics-event="<?= e((string) $relatedEvent['id']) ?>" data-analytics-action="facebook"><strong>f</strong> Dołącz do wydarzenia</a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($videos): ?>
        <section class="section regular-extra">
            <div class="section-head">
                <div><p class="eyebrow">Video</p><h2>Zobacz klimat wydarzeń</h2></div>
            </div>
            <div class="event-player-grid">
                <?php foreach (array_slice($videos, 0, 3) as $video): ?>
                    <?php
                    $videoTitle = $video['title'] ?: 'Film z wydarzenia';
                    $videoId = $youtubeVideoId($video['youtube_url']);
                    ?>
                    <article class="event-player-card">
                        <?php if ($videoId): ?>
                            <iframe
                                src="<?= e($youtubeEmbedUrl($videoId)) ?>"
                                title="<?= e($videoTitle) ?>"
                                loading="lazy"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allowfullscreen></iframe>
                        <?php else: ?>
                            <a class="event-video-fallback" href="<?= e($video['youtube_url']) ?>" target="_blank" rel="noopener"><?= e($videoTitle) ?></a>
                        <?php endif; ?>
                        <h3><?= e($videoTitle) ?></h3>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

        <section class="clubbing-explainer-section <?= $isClubbing ? 'is-clubbing' : 'is-regular-party' ?>" id="<?= $isClubbing ? 'jak-dziala-clubbing' : 'jak-wygladaja-imprezy' ?>">
            <div class="clubbing-explainer-head">
                <div>
                    <p class="eyebrow"><?= $isClubbing ? 'Studencki clubbing' : 'Video' ?></p>
                    <h2><?= $isClubbing ? 'Zobacz, na czym polega clubbing' : 'Zobacz, jak wyglądają nasze imprezy' ?></h2>
                </div>
                <p><?= $isClubbing ? 'Jeden bilet, kilka klubów, tysiące uczestników i trasa po najlepszych lokalach w mieście. Tak wygląda klimat naszych największych clubbingów z poprzednich lat.' : 'Zobacz energię, frekwencję i atmosferę wydarzeń, które organizujemy dla studentów w największych miastach akademickich.' ?></p>
            </div>
            <div class="clubbing-embed-grid">
                <?php foreach ($clubbingEmbedVideos as $video): ?>
                    <article class="clubbing-embed-card">
                        <iframe
                            width="640"
                            height="360"
                            src="<?= e($video['src']) ?>"
                            title="<?= e($video['title']) ?>"
                            loading="lazy"
                            allowfullscreen
                            frameborder="0"></iframe>
                        <h3><?= e($video['title']) ?></h3>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

    <section class="event-partners-block">
        <div class="event-partners-head">
            <div>
                <p class="eyebrow">Partnerzy wydarzenia</p>
                <h2>Miejsce dla partnerów wydarzenia</h2>
            </div>
            <p>Tu pojawiają się marki, lokale i partnerzy wspierający największe imprezy studenckie w mieście.</p>
        </div>
        <div class="event-partners-row">
            <?php $visiblePartners = array_slice($partners, 0, 3); ?>
            <?php if ($visiblePartners): ?>
                <?php foreach ($visiblePartners as $index => $partner): ?>
                    <a class="event-partner-tile" href="<?= e($partner['website_url'] ?: '#') ?>" target="_blank" rel="noopener">
                        <?php if ($partner['logo_url']): ?>
                            <img src="<?= e($partner['logo_url']) ?>" alt="<?= e($partner['name']) ?>">
                        <?php else: ?>
                            <span class="event-partner-logo"><?= e(strtoupper(substr($partner['name'], 0, 2))) ?></span>
                        <?php endif; ?>
                        <strong><?= e($partner['name']) ?></strong>
                        <small><?= e($partner['category'] ?: ($index === 0 ? 'Partner główny' : 'Partner wydarzenia')) ?></small>
                    </a>
                <?php endforeach; ?>
                <?php for ($slot = count($visiblePartners); $slot < 3; $slot++): ?>
                    <article class="event-partner-tile is-empty">
                        <span class="event-partner-logo">Logo</span>
                        <strong><?= $slot === 0 ? 'Partner główny' : 'Partner wydarzenia' ?></strong>
                        <small>Wolne miejsce dla partnera</small>
                    </article>
                <?php endfor; ?>
            <?php else: ?>
                <article class="event-partner-tile is-empty">
                    <span class="event-partner-logo">Logo</span>
                    <strong>Partner główny</strong>
                    <small>Dodasz go w CRM</small>
                </article>
                <article class="event-partner-tile is-empty">
                    <span class="event-partner-logo">Logo</span>
                    <strong>Partner wydarzenia</strong>
                    <small>Dodasz go w CRM</small>
                </article>
                <article class="event-partner-tile is-empty">
                    <span class="event-partner-logo">Logo</span>
                    <strong>Partner lokalny</strong>
                    <small>Dodasz go w CRM</small>
                </article>
            <?php endif; ?>
            <article class="event-partner-cta">
                <strong>Chcesz zostać partnerem?</strong>
                <a href="/#partnerzy">Napisz do nas</a>
            </article>
        </div>
    </section>

    <section id="powiadomienia" class="event-notify-block">
        <div class="event-notify-head">
            <div>
                <p class="eyebrow">Powiadomienia</p>
                <h2>Powiadomienia o najważniejszych imprezach studenckich</h2>
            </div>
            <p>Zostaw kontakt i wybierz miasto, z którego chcesz otrzymywać informacje o Inauguracjach Studenckich, Integracjach, Otrzęsinach, Połowinkach i Juwenaliach.</p>
        </div>
        <div class="event-notify-layout">
            <article class="event-notify-promise">
                <span>SMS / EMAIL</span>
                <strong>Nie przegap kolejnych wydarzeń i tańszych biletów</strong>
                <p>Damy znać o najważniejszych wydarzeniach, nowych pulach biletów i dużych studenckich eventach w wybranym mieście.</p>
            </article>
            <form method="post" action="/kontakt-marketingowy" class="event-notify-form">
                <?= csrf_field() ?>
                <?= spam_trap_field() ?>
                <input type="hidden" name="redirect_to" value="<?= e($_SERVER['REQUEST_URI'] ?? '/wydarzenia/' . $event['slug']) ?>">
                <label>Miasto
                    <select name="city" required>
                        <?php foreach (['Białystok','Bydgoszcz','Częstochowa','Katowice','Kielce','Kraków','Łódź','Lublin','Olsztyn','Opole','Poznań','Radom','Rzeszów','Szczecin','Tarnów','Toruń','Trójmiasto','Warszawa','Wrocław','Zielona Góra'] as $notifyCity): ?>
                            <option value="<?= e($notifyCity) ?>" <?= $notifyCity === $city ? 'selected' : '' ?>><?= e($notifyCity) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Imię <input name="name" placeholder="np. Ola" required></label>
                <label>Telefon <input name="phone" placeholder="Numer do SMS"></label>
                <label>Email <input name="email" type="email" placeholder="Adres email"></label>
                <label class="event-notify-check"><input type="checkbox" name="sms_consent" value="1"> Chcę powiadomienia SMS</label>
                <label class="event-notify-check"><input type="checkbox" name="email_consent" value="1"> Chcę powiadomienia email</label>
                <p>Wyrażasz zgodę na otrzymywanie informacji o wydarzeniach, biletach i akcjach promocyjnych wybranym kanałem. Zgodę możesz wycofać w każdej chwili. Szczegóły: <a href="/polityka-prywatnosci">polityka prywatności</a>.</p>
                <button class="btn primary">Zapisz mnie na powiadomienia</button>
            </form>
        </div>
    </section>
</main>
<script>
document.querySelectorAll('[data-countdown]').forEach(function (node) {
    const target = new Date(node.getAttribute('data-countdown')).getTime();
    const endTarget = new Date(node.getAttribute('data-countdown-end') || node.getAttribute('data-countdown')).getTime();
    const label = node.closest('article')?.querySelector('[data-countdown-label]');
    const note = node.closest('article')?.querySelector('small');
    function tick() {
        const now = Date.now();
        const diff = target - now;
        if (diff <= 0 && now <= endTarget) {
            if (label) label.textContent = 'Status';
            node.textContent = 'Impreza w trakcie';
            if (note) note.textContent = 'Wydarzenie już się rozpoczęło.';
            return;
        }
        if (diff <= 0) {
            if (label) label.textContent = 'Status';
            node.textContent = 'Impreza zakończona';
            if (note) note.textContent = 'Sprawdź pozostałe wydarzenia w mieście.';
            return;
        }
        const days = Math.floor(diff / 86400000);
        const hours = Math.floor((diff % 86400000) / 3600000);
        const minutes = Math.floor((diff % 3600000) / 60000);
        if (label) label.textContent = 'Start za';
        if (note) note.textContent = 'Odliczamy do rozpoczęcia imprezy.';
        node.textContent = days > 0 ? days + ' dni ' + hours + ' godz.' : hours + ' godz. ' + minutes + ' min';
    }
    tick();
    setInterval(tick, 60000);
});
document.querySelectorAll('[data-read-more]').forEach(function (button) {
    button.addEventListener('click', function () {
        const fullDescription = button.nextElementSibling;
        const isHidden = fullDescription.hasAttribute('hidden');
        fullDescription.toggleAttribute('hidden', !isHidden);
        button.textContent = isHidden ? 'zwiń opis' : 'czytaj więcej';
    });
});
document.querySelectorAll('[data-ticket-waitlist-toggle]').forEach(function (button) {
    button.addEventListener('click', function () {
        const box = button.closest('.ticket-waitlist-box');
        const form = box ? box.querySelector('[data-ticket-waitlist-form]') : null;
        if (!form) return;
        const isHidden = form.hasAttribute('hidden');
        form.toggleAttribute('hidden', !isHidden);
        button.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
        button.textContent = isHidden ? 'Ukryj formularz' : 'Zapisz się na powiadomienie';
    });
});
</script>
<?php else: ?>
<main class="event-page">
    <section class="event-hero event-hero-v2" style="background-image: linear-gradient(90deg, rgba(0,0,0,.88), rgba(0,0,0,.55) 45%, rgba(0,0,0,.2)), url('<?= e($eventImage) ?>')">
        <div class="event-hero-content">
            <span class="event-label"><?= e($eventTypeLabel) ?></span>
            <h1><?= e($event['title']) ?></h1>
            <p class="event-hero-lead"><?= e($event['short_description']) ?></p>
            <div class="event-cta-row">
                <?php if ($event['ticket_url']): ?><a class="btn primary event-main-cta" href="<?= e($event['ticket_url']) ?>" target="_blank" rel="noopener" data-analytics-event="<?= e((string) $event['id']) ?>" data-analytics-action="ticket">Kup bilet</a><?php endif; ?>
                <?php if ($event['facebook_event_url']): ?><a class="btn event-outline-cta" href="<?= e($event['facebook_event_url']) ?>" target="_blank" rel="noopener" data-analytics-event="<?= e((string) $event['id']) ?>" data-analytics-action="facebook">Dołącz do wydarzenia</a><?php endif; ?>
                <?php if ($event['fanpage_url']): ?><a class="btn event-outline-cta" href="<?= e($event['fanpage_url']) ?>" target="_blank" rel="noopener" data-analytics-event="<?= e((string) $event['id']) ?>" data-analytics-action="fanpage">Polub nas</a><?php endif; ?>
            </div>
        </div>
    </section>

    <section class="event-facts">
        <article>
            <span>Data</span>
            <strong><?= e(date('d.m.Y', $date)) ?></strong>
            <small><?= e(date('H:i', $date)) ?></small>
        </article>
        <article>
            <span>Miasto</span>
            <strong><?= e($event['region'] ?: $city) ?></strong>
            <small><?= e($city) ?></small>
        </article>
        <article>
            <span>Miejsce</span>
            <strong><?= e($venueName) ?></strong>
            <small><?= e($venueAddress) ?></small>
        </article>
    </section>

    <section class="event-content-v2">
        <article class="event-description">
            <p class="eyebrow">Opis wydarzenia</p>
            <h2>Co czeka na miejscu?</h2>
            <div class="prose"><?= nl2br(e($event['description'])) ?></div>
        </article>

        <aside class="event-side-card">
            <img src="<?= e($eventImage) ?>" alt="<?= e($event['title']) ?>">
            <div>
                <h2><?= e($event['title']) ?></h2>
                <p><?= e(date('d.m.Y H:i', $date)) ?> • <?= e($event['region'] ?: $city) ?></p>
                <?php if ($event['ticket_url']): ?><a class="btn primary" href="<?= e($event['ticket_url']) ?>" target="_blank" rel="noopener" data-analytics-event="<?= e((string) $event['id']) ?>" data-analytics-action="ticket">Kup bilet</a><?php endif; ?>
            </div>
        </aside>
    </section>

    <?php if ($event['type'] === 'clubbing' && $clubs): ?>
        <section class="section event-section-alt">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Clubbing</p>
                    <h2>Kluby biorące udział</h2>
                </div>
            </div>
            <div class="club-grid">
                <?php foreach ($clubs as $club): ?>
                    <article class="club-card">
                        <?php if ($club['image_url']): ?><img src="<?= e($club['image_url']) ?>" alt="<?= e($club['name']) ?>"><?php endif; ?>
                        <h3><?= e($club['name']) ?></h3>
                        <p><?= e($club['address']) ?></p>
                        <p><?= e($club['description']) ?></p>
                        <?php if ($club['map_url']): ?><a class="text-link" href="<?= e($club['map_url']) ?>" target="_blank" rel="noopener">Zobacz mapę</a><?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($videos): ?>
        <section class="section">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Video</p>
                    <h2>Zobacz klimat wydarzeń</h2>
                </div>
            </div>
            <div class="event-player-grid">
                <?php foreach (array_slice($videos, 0, 3) as $video): ?>
                    <?php
                    $videoTitle = $video['title'] ?: 'Film z wydarzenia';
                    $videoId = $youtubeVideoId($video['youtube_url']);
                    ?>
                    <article class="event-player-card">
                        <?php if ($videoId): ?>
                            <iframe
                                src="<?= e($youtubeEmbedUrl($videoId)) ?>"
                                title="<?= e($videoTitle) ?>"
                                loading="lazy"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allowfullscreen></iframe>
                        <?php else: ?>
                            <a class="event-video-fallback" href="<?= e($video['youtube_url']) ?>" target="_blank" rel="noopener"><?= e($videoTitle) ?></a>
                        <?php endif; ?>
                        <h3><?= e($videoTitle) ?></h3>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($partners): ?>
        <section class="section event-section-alt">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Partnerzy</p>
                    <h2>Partnerzy wydarzenia</h2>
                </div>
            </div>
            <div class="partner-grid">
                <?php foreach ($partners as $partner): ?>
                    <a class="partner" href="<?= e($partner['website_url'] ?: '#') ?>" target="_blank" rel="noopener">
                        <?php if ($partner['logo_url']): ?><img src="<?= e($partner['logo_url']) ?>" alt="<?= e($partner['name']) ?>"><?php endif; ?>
                        <span><?= e($partner['name']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php endif; ?>
