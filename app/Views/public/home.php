<main>
    <section class="hero">
        <div>
            <p class="hero-kicker">Największe i najważniejsze imprezy w miastach akademickich</p>
            <h1 class="hero-title">Integracja Studencka</h1>
            <p class="hero-lead">Jedno miejsce dla studentów, którzy chcą szybko znaleźć Inauguracje, Integracje, Otrzęsiny, Połowinki i inne duże wydarzenia akademickie w swoim mieście.</p>
            <div class="hero-actions">
                <a class="btn primary" href="#wydarzenia">Zobacz wydarzenia</a>
                <a class="btn" href="#powiadomienia">Bądź na bieżąco</a>
            </div>
        </div>
    </section>

    <section class="sales-strip">
        <a href="#wydarzenia">
            <span>Największe imprezy studenckie</span>
            <strong>Sprawdź najbliższe terminy</strong>
        </a>
        <a href="#wydarzenia">
            <span>Miasta akademickie w całej Polsce</span>
            <strong>Znajdź wydarzenie w swoim mieście</strong>
        </a>
        <a href="#powiadomienia">
            <span>Powiadomienia SMS / email</span>
            <strong>Nie przegap żadnych imprez</strong>
        </a>
    </section>

    <section id="wydarzenia" class="section">
        <div class="section-head">
            <div>
                <p class="eyebrow">Najbliższe terminy</p>
                <h2>Wydarzenia</h2>
            </div>
            <form class="filters" method="get" action="/#wydarzenia" data-auto-filter>
                <select name="city">
                    <option value="">Wszystkie miasta</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?= e($city) ?>" <?= ($_GET['city'] ?? '') === $city ? 'selected' : '' ?>><?= e($city) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="month">
                    <option value="">Wszystkie miesiące</option>
                    <?php foreach ($months as $month): ?>
                        <option value="<?= e($month['month_value']) ?>" <?= ($_GET['month'] ?? '') === $month['month_value'] ? 'selected' : '' ?>><?= e($month['month_label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="type" hidden>
                    <option value="">Każdy typ</option>
                    <option value="regular" <?= ($_GET['type'] ?? '') === 'regular' ? 'selected' : '' ?>>Zwykłe</option>
                    <option value="clubbing" <?= ($_GET['type'] ?? '') === 'clubbing' ? 'selected' : '' ?>>Clubbing</option>
                </select>
                <button class="btn small filter-submit">Pokaż imprezy</button>
                <?php if (!empty($_GET['city']) || !empty($_GET['month'])): ?>
                    <a class="filter-reset" href="/#wydarzenia">Wyczyść</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="event-grid">
            <?php foreach ($events as $item): ?>
                <?php App\Core\View::partial('public/partials/event-card', ['item' => $item]); ?>
            <?php endforeach; ?>
            <?php if (!$events): ?>
                <p class="empty">Brak opublikowanych wydarzeń w wybranych filtrach.</p>
            <?php endif; ?>
        </div>
        <?php if (count($events) > 12): ?>
            <div class="calendar-load-more-wrap">
                <button class="calendar-load-more" type="button" data-calendar-load-more>Pokaż kolejne wydarzenia</button>
            </div>
        <?php endif; ?>
    </section>

    <section class="event-types-section" id="rodzaje-imprez">
        <div class="event-types-head">
            <div>
                <p class="eyebrow">Jakie imprezy organizujemy?</p>
                <h2>Największe formaty wydarzeń studenckich</h2>
                <p class="event-types-promo"><span>Od 2010 roku</span> Tworzymy największe wydarzenia studenckie w Polsce. Integrujemy tysiące studentów podczas imprez, które na długo zostają w pamięci.</p>
                <p class="event-types-crowd">Ogromna frekwencja i wyjątkowa atmosfera - to nasz znak rozpoznawczy.</p>
            </div>
            <p>Od pierwszych tygodni roku akademickiego po juwenalia i wyjazdy studenckie. W jednym miejscu zbieramy wydarzenia, których studenci szukają najczęściej.</p>
        </div>
        <div class="event-types-grid">
            <?php
            $eventTypes = [
                ['Inauguracja Studencka', 'Największe rozpoczęcia roku akademickiego w klubach i miastach studenckich. Sprawdź terminy inauguracji studenckich i kup bilet na start sezonu.'],
                ['Integracja Studencka', 'Imprezy integracyjne dla studentów pierwszych lat, innych roczników i wszystkich studentów z województwa. Integracja studencka to szybki sposób na wejście w akademicki klimat miasta.'],
                ['Studencki Clubbing', 'Jeden bilet, kilka klubów i tysiące uczestników na trasie po najlepszych lokalach. Clubbing studencki to idealna opcja na dużą noc w mieście.'],
                ['Otrzęsiny Studenckie', 'Klasyczne otrzęsiny dla nowych studentów, pełne muzyki, konkursów i akademickiej atmosfery. Tu zaczyna się prawdziwe życie po zajęciach.'],
                ['Połowinki Studenckie', 'Imprezy w połowie studiów dla roczników, które chcą świętować razem przed kolejnym etapem akademickiej drogi.'],
                ['Andrzejki Studenckie', 'Jesienne wydarzenia klubowe z mocnym klimatem, muzyką i spotkaniami ekip z różnych uczelni.'],
                ['Walentynki Studenckie', 'Lutowe imprezy dla par, singli i całych paczek znajomych. Studenckie walentynki łączą zabawę, muzykę i luźny klimat.'],
                ['Mikołajki Studenckie', 'Grudniowe imprezy akademickie, które rozkręcają końcówkę roku przed świętami i sesją.'],
                ['Sylwester Studencki', 'Studenckie wejście w nowy rok z muzyką, klubową oprawą i dużą ekipą uczestników.'],
                ['Juwenalia', 'Największe święto studentów w miastach akademickich. Koncerty, plenerowe wydarzenia i imprezy towarzyszące w klubach.'],
                ['Student Party Travel', 'Wyjazdy imprezowe i studenckie tripy dla osób, które chcą połączyć podróż, integrację i wydarzenia klubowe.'],
            ];
            ?>
            <?php foreach ($eventTypes as $index => [$typeTitle, $typeDescription]): ?>
                <article class="event-type-card" style="--type-index: <?= $index + 1 ?>">
                    <span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    <?php if ($index < 3): ?>
                        <strong class="event-type-bestseller">Bestseller</strong>
                    <?php endif; ?>
                    <h3><?= e($typeTitle) ?></h3>
                    <p><?= e($typeDescription) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="legacy-video-section" id="legendarne-clubbingi">
        <div class="legacy-video-head">
            <div>
                <p class="eyebrow">Video i reportaże</p>
                <h2>Zobacz, jak bawiliśmy się w zeszłych latach</h2>
            </div>
            <p>Legendarne clubbingi, tysiące studentów i nocne życie Trójmiasta w materiałach z poprzednich edycji.</p>
        </div>
        <div class="legacy-video-grid">
            <?php
            $legacyVideos = [
                [
                    'label' => 'Reportaż',
                    'title' => 'Trójmiejska Integracja Studencka po raz szósty',
                    'meta' => 'Relacja z jednej z legendarnych edycji Integracji Studenckiej.',
                    'url' => 'https://rozrywka.trojmiasto.pl/Trojmiejska-integracja-studencka-po-raz-szosty-n117605.html',
                    'embed_url' => 'https://www.trojmiasto.pl/video/embed/24241',
                ],
                [
                    'label' => 'Video 2014',
                    'title' => 'Nocne życie Trójmiasta i studencki clubbing',
                    'meta' => 'Zobacz klimat Trójmiejskiej Integracji Studenckiej z 2014 roku.',
                    'url' => 'https://tv.trojmiasto.pl/Trojmiejska-Integracja-Studencka-Nocne-zycie-Trojmiasta-Sopot-10-10-2014-video-9724.html?fbclid=1',
                    'embed_url' => 'https://www.trojmiasto.pl/video/embed/9724',
                ],
                [
                    'label' => 'Video 2016',
                    'title' => 'Trójmiejska Integracja Studencka 2016',
                    'meta' => 'Kolejna edycja, pełne kluby i studencka energia na parkiecie.',
                    'url' => 'https://tv.trojmiasto.pl/Trojmiejska-Integracja-Studencka-2016-Nocne-zycie-Trojmiasta-Sopot-15-10-2016-video-17925.html',
                    'embed_url' => 'https://www.trojmiasto.pl/video/embed/17925',
                ],
            ];
            ?>
            <?php foreach ($legacyVideos as $index => $video): ?>
                <article class="legacy-video-card legacy-video-embed-card" style="--legacy-index: <?= $index + 1 ?>">
                    <div class="legacy-video-frame">
                        <iframe width="640" height="360" src="<?= e($video['embed_url']) ?>" title="<?= e($video['title']) ?>" loading="lazy" allowfullscreen frameborder="0"></iframe>
                    </div>
                    <div class="legacy-video-copy">
                    <span><?= e($video['label']) ?></span>
                    <strong><?= e($video['title']) ?></strong>
                    <p><?= e($video['meta']) ?></p>
                    <em>Obejrzyj materiał</em>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="news-section" id="newsy">
        <div class="section-head">
            <div>
                <p class="eyebrow">News</p>
                <h2>Aktualności studenckie</h2>
            </div>
            <div class="news-controls">
                <button type="button" data-news-prev aria-label="Poprzednie newsy">‹</button>
                <button type="button" data-news-next aria-label="Następne newsy">›</button>
            </div>
        </div>
        <div class="news-carousel" id="newsCarousel">
            <?php foreach ($newsArticles as $article): ?>
                <article class="news-feature" style="<?= $article['image_url'] ? 'background-image: linear-gradient(135deg, rgba(0,0,0,.78), rgba(0,0,0,.42)), url(' . e($article['image_url']) . ')' : '' ?>">
                    <div>
                        <span><?= e(date('d.m.Y', strtotime($article['published_at'] ?: $article['created_at']))) ?></span>
                        <h3><?= e($article['title']) ?></h3>
                        <p><?= e($article['excerpt']) ?></p>
                        <a class="btn primary" href="/news/<?= e($article['slug']) ?>">Przeczytaj news</a>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php if (!$newsArticles): ?>
                <article class="news-feature">
                    <div>
                        <span>News</span>
                        <h3>Wkrótce pojawią się aktualności studenckie</h3>
                        <p>Dodaj pierwszy artykuł w CRM, a pojawi się tutaj automatycznie.</p>
                    </div>
                </article>
            <?php endif; ?>
        </div>
    </section>

    <section class="facebook-band">
        <div class="facebook-head">
            <p class="eyebrow">Facebook</p>
            <h2>Zaobserwuj nas w swoim mieście</h2>
            <p>Wybierz miasto akademickie i obserwuj lokalny profil Integracji Studenckiej. Linki podepniemy później do właściwych fanpage’y.</p>
        </div>
        <div class="city-carousel">
            <button type="button" class="city-nav" data-city-prev aria-label="Poprzednie miasta">‹</button>
            <div class="city-links" id="cityCarousel">
            <?php
            $facebookCities = [
                'Białystok' => ['Branicki i studencki kampus', 'https://www.facebook.com/BialostockaIntegracjaStudencka'],
                'Bydgoszcz' => ['Wyspa Młyńska i uczelnie', 'https://www.facebook.com/BydgoskaIntegracjaStudencka'],
                'Częstochowa' => ['Jasna Góra i akademickie miasto', 'https://www.facebook.com/CzestochowskaIntegracjaStudencka'],
                'Katowice' => ['Spodek i śląskie uczelnie', 'https://www.facebook.com/SlaskaIntegracjaStudencka'],
                'Kielce' => ['Centrum i życie studenckie', 'https://www.facebook.com/KieleckaIntegracjaStudencka'],
                'Kraków' => ['Rynek, Kazimierz i kluby', 'https://www.facebook.com/KrakowskaIntegracjaStudencka'],
                'Łódź' => ['Piotrkowska i akademickie noce', 'https://www.facebook.com/LodzkaIntegracjaStudencka'],
                'Lublin' => ['Stare Miasto i miasteczko akademickie', 'https://www.facebook.com/LUBELSKAINTEGRACJA'],
                'Olsztyn' => ['Kortowo i jeziora', 'https://www.facebook.com/OlsztynskaIntegracjaStudencka'],
                'Opole' => ['Rynek i studenckie wydarzenia', 'https://www.facebook.com/OpolskaIntegracjaStudencka'],
                'Poznań' => ['Stary Rynek i kampusy', 'https://www.facebook.com/PoznanskaIntegracjaStudenckaa'],
                'Radom' => ['Centrum i lokalne imprezy', 'https://www.facebook.com/RadomskaIntegracjaStudencka'],
                'Rzeszów' => ['Rynek i Politechnika', 'https://www.facebook.com/RzeszowskaIntegracjaStudencka'],
                'Szczecin' => ['Wały Chrobrego i portowe miasto', 'https://www.facebook.com/SzczecinskaIntegracjaStudencka'],
                'Tarnów' => ['Stare Miasto i młoda scena', 'https://www.facebook.com/StudenciTarnow'],
                'Toruń' => ['Bulwary i studencki klimat', 'https://www.facebook.com/TorunskaIntegracjaStudencka'],
                'Trójmiasto' => ['Gdańsk, Gdynia, Sopot', 'https://www.facebook.com/TrojmiejskaIntegracjaStudencka'],
                'Warszawa' => ['Centrum i największe uczelnie', 'https://www.facebook.com/WarszawskaIntegracjaStudencka'],
                'Wrocław' => ['Rynek, Odra i kampusy', 'https://www.facebook.com/WroclawskaIntegracjaStudencka'],
                'Zielona Góra' => ['Deptak i akademickie wydarzenia', 'https://www.facebook.com/ZielonogorskaIntegracjaStudencka'],
            ];
            ?>
            <?php foreach ($facebookCities as $city => [$hint, $link]): ?>
                <a href="<?= e($link) ?>" class="city-facebook-card" data-city="<?= e($city) ?>" aria-label="Profil Facebook Integracja Studencka <?= e($city) ?>" target="_blank" rel="noopener">
                    <span class="fb-mark">f</span>
                    <span class="city-like" aria-hidden="true">Like</span>
                    <span class="city-photo-label"><?= e($hint) ?></span>
                    <strong><?= e($city) ?></strong>
                    <small>Integracja Studencka</small>
                    <span class="city-action">Polub profil</span>
                </a>
            <?php endforeach; ?>
            </div>
            <button type="button" class="city-nav" data-city-next aria-label="Następne miasta">›</button>
        </div>
    </section>

    <section id="partnerzy" class="partners-section">
        <div class="partners-head">
            <div>
                <p class="eyebrow">Partnerzy</p>
                <h2>Partnerzy największych imprez studenckich</h2>
            </div>
            <p>Współpracujemy z markami, mediami i projektami, które chcą być blisko najważniejszych wydarzeń akademickich w całej Polsce.</p>
        </div>

        <div class="partners-layout">
            <a class="partner-feature" href="https://www.facebook.com/StudenckaPolska" target="_blank" rel="noopener">
                <span class="partner-logo">SP</span>
                <div>
                    <span class="partner-tag">Główny partner</span>
                    <h3>Studenci Polska</h3>
                    <p>Ogólnopolski profil studencki wspierający komunikację największych wydarzeń akademickich.</p>
                </div>
            </a>

            <article class="partner-cta">
                <span>Zostań partnerem wydarzeń</span>
                <h3>Czy chcesz promować markę przy wydarzeniach studenckich?</h3>
                <p>Napisz do nas i opisz, w jakich miastach albo przy jakich wydarzeniach chcesz się pojawić.</p>
                <button class="btn primary partner-open" type="button" data-show-partner-form>Napisz do nas</button>
            </article>
        </div>

        <form class="partner-form" method="post" action="/kontakt-partnerski" id="partnerForm" hidden>
            <?= csrf_field() ?>
            <?= spam_trap_field() ?>
            <label>Imię i nazwisko <input name="name" required></label>
            <label>Email <input type="email" name="email" required></label>
            <label>Firma / projekt <input name="company"></label>
            <label>Telefon <input name="phone"></label>
            <label class="wide">Wiadomość <textarea name="message" rows="5" required placeholder="Napisz, w jakich miastach lub przy jakich wydarzeniach chcesz się pojawić."></textarea></label>
            <button class="btn primary">Wyślij wiadomość</button>
        </form>
    </section>

    <script>
    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-show-partner-form]');
        if (button) {
            const form = document.getElementById('partnerForm');
            if (!form) return;
            form.hidden = false;
            form.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        const prev = event.target.closest('[data-city-prev]');
        const next = event.target.closest('[data-city-next]');
        if (prev || next) {
            const carousel = document.getElementById('cityCarousel');
            if (!carousel) return;
            const direction = next ? 1 : -1;
            carousel.scrollLeft += direction * 360;
            return;
        }

        const newsPrev = event.target.closest('[data-news-prev]');
        const newsNext = event.target.closest('[data-news-next]');
        if (newsPrev || newsNext) {
            const carousel = document.getElementById('newsCarousel');
            if (!carousel) return;
            const direction = newsNext ? 1 : -1;
            carousel.scrollLeft += direction * Math.min(760, carousel.clientWidth);
            return;
        }

        const calendarButton = event.target.closest('[data-calendar-load-more]');
        if (calendarButton) {
            const cards = Array.from(document.querySelectorAll('#wydarzenia .calendar-event-card'));
            const hiddenCards = cards.filter(card => card.hidden);
            hiddenCards.slice(0, 12).forEach(card => card.hidden = false);
            const remaining = cards.filter(card => card.hidden).length;
            calendarButton.textContent = remaining > 0 ? 'Pokaż kolejne wydarzenia' : 'Wszystkie wydarzenia są już widoczne';
            if (remaining === 0) {
                calendarButton.disabled = true;
            }
        }
    });

    const calendarCards = Array.from(document.querySelectorAll('#wydarzenia .calendar-event-card'));
    const calendarButton = document.querySelector('[data-calendar-load-more]');
    if (calendarCards.length > 12 && calendarButton) {
        calendarCards.slice(12).forEach(card => card.hidden = true);
        calendarButton.textContent = 'Pokaż kolejne wydarzenia';
    }

    if (calendarButton) {
        calendarButton.addEventListener('click', function () {
            const hiddenCards = calendarCards.filter(card => card.hidden);
            hiddenCards.slice(0, 12).forEach(card => card.hidden = false);
            const remaining = calendarCards.filter(card => card.hidden).length;
            calendarButton.textContent = remaining > 0 ? 'Pokaż kolejne wydarzenia' : 'Wszystkie wydarzenia są już widoczne';
            if (remaining === 0) {
                calendarButton.disabled = true;
            }
        });
    }

    document.querySelectorAll('[data-auto-filter] select').forEach(function (select) {
        select.addEventListener('change', function () {
            select.form.submit();
        });
    });

    const cityCarousel = document.getElementById('cityCarousel');
    if (cityCarousel) {
        let cityAutoplayPaused = false;
        let resumeTimer = null;
        const pause = () => { cityAutoplayPaused = true; };
        const resume = () => { cityAutoplayPaused = false; };
        const pauseTemporarily = () => {
            pause();
            clearTimeout(resumeTimer);
            resumeTimer = setTimeout(resume, 3000);
        };

        cityCarousel.addEventListener('mouseenter', pause);
        cityCarousel.addEventListener('mouseleave', resume);
        cityCarousel.addEventListener('touchstart', pause, { passive: true });
        cityCarousel.addEventListener('touchend', pauseTemporarily, { passive: true });
        cityCarousel.addEventListener('wheel', pauseTemporarily, { passive: true });
        cityCarousel.addEventListener('scroll', function () {
            if (cityAutoplayPaused) return;
        }, { passive: true });

        function autoScrollCities() {
            if (!cityAutoplayPaused && cityCarousel.scrollWidth > cityCarousel.clientWidth) {
                const maxScroll = cityCarousel.scrollWidth - cityCarousel.clientWidth - 2;
                if (cityCarousel.scrollLeft >= maxScroll) {
                    cityCarousel.scrollLeft = 0;
                } else {
                    cityCarousel.scrollLeft += 0.25;
                }
            }
            requestAnimationFrame(autoScrollCities);
        }

        requestAnimationFrame(autoScrollCities);
    }
    </script>

    <section id="powiadomienia" class="notify-section">
        <div class="notify-intro">
            <p class="notify-kicker">Powiadomienia</p>
            <h2>Powiadomienia o najważniejszych imprezach studenckich</h2>
            <article class="notify-promise">
                <span>SMS / EMAIL</span>
                <strong>Nie przegap kolejnych wydarzeń i tańszych biletów</strong>
                <p>Wyślemy tylko informacje o największych i najważniejszych imprezach studenckich w wybranym mieście.</p>
            </article>
        </div>

        <div class="notify-copy">
            <p>Zostaw kontakt i wybierz miasto, z którego chcesz otrzymywać informacje o Inauguracjach Studenckich, Integracjach, Otrzęsinach, Połowinkach i Juwenaliach.</p>
        </div>

        <form method="post" action="/kontakt-marketingowy" class="notify-form">
            <?= csrf_field() ?>
            <?= spam_trap_field() ?>
            <label>Miasto
                <select name="city" required>
                    <option value="Katowice">Katowice</option>
                    <option value="Trójmiasto">Trójmiasto</option>
                    <option value="Warszawa">Warszawa</option>
                    <option value="Kraków">Kraków</option>
                    <option value="Wrocław">Wrocław</option>
                    <option value="Poznań">Poznań</option>
                    <option value="Łódź">Łódź</option>
                    <option value="Radom">Radom</option>
                    <option value="Częstochowa">Częstochowa</option>
                    <option value="Bielsko-Biała">Bielsko-Biała</option>
                    <option value="Zielona Góra">Zielona Góra</option>
                </select>
            </label>
            <label>Imię <input name="name" placeholder="np. Ola" required></label>
            <label>Telefon <input name="phone" placeholder="Numer do SMS"></label>
            <label>Email <input name="email" type="email" placeholder="Adres email"></label>
            <label class="notify-check"><input type="checkbox" name="sms_consent" value="1"> Chcę powiadomienia SMS</label>
            <label class="notify-check"><input type="checkbox" name="email_consent" value="1"> Chcę powiadomienia email</label>
            <p class="notify-consent">Wyrażasz zgodę na otrzymywanie informacji o wydarzeniach, biletach i akcjach promocyjnych wybranym kanałem. Zgodę możesz wycofać w każdej chwili. Szczegóły: <a href="/polityka-prywatnosci">polityka prywatności</a>.</p>
            <button class="btn primary notify-submit">Zapisz mnie na powiadomienia</button>
        </form>
    </section>
</main>
