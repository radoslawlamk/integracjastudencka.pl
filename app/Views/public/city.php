<?php
$cityEventTypes = [
    ['Inauguracja Studencka', 'Największe rozpoczęcie roku akademickiego w mieście. Sprawdź terminy, kluby i wydarzenia dla studentów zaczynających sezon.'],
    ['Integracja Studencka', 'Imprezy integracyjne dla pierwszych lat, starszych roczników i wszystkich studentów z województwa.'],
    ['Studencki Clubbing', 'Clubbing studencki z jednym biletem, wieloma klubami i dużą liczbą uczestników na parkietach.'],
    ['Otrzęsiny Studenckie', 'Klasyczne otrzęsiny dla nowych studentów, ekip z uczelni i osób, które chcą wejść w akademicki klimat.'],
    ['Połowinki Studenckie', 'Wydarzenia dla roczników świętujących połowę studiów, zwykle w klubowej oprawie i większej ekipie.'],
    ['Andrzejki Studenckie', 'Jesienne imprezy akademickie, które łączą muzykę, spotkania znajomych i klubową atmosferę.'],
    ['Walentynki Studenckie', 'Studenckie walentynki dla par, singli i grup znajomych szukających dobrej imprezy w mieście.'],
    ['Mikołajki Studenckie', 'Grudniowe wydarzenia studenckie, które rozkręcają końcówkę roku przed świętami i sesją.'],
    ['Sylwester Studencki', 'Studenckie imprezy sylwestrowe z muzyką, zabawą i wejściem w nowy rok w akademickim stylu.'],
    ['Juwenalia', 'Największe święto studentów: koncerty, wydarzenia plenerowe i imprezy towarzyszące w klubach.'],
    ['Student Party Travel', 'Wyjazdy studenckie i imprezowe tripy dla osób, które chcą połączyć podróż z integracją.'],
];
$eventCount = count($events);
$nextEvent = $events[0] ?? null;
$nextEventDate = $nextEvent ? date('d.m.Y', strtotime($nextEvent['starts_at'])) : 'wkrótce';
?>
<main>
    <section class="city-page-hero">
        <div>
            <p class="eyebrow">Miasto akademickie</p>
            <h1>Imprezy studenckie <?= e($city) ?></h1>
            <p>Największe inauguracje, integracje, otrzęsiny, połowinki, clubbing, juwenalia i wydarzenia akademickie w mieście <?= e($city) ?>. Jeden kalendarz, szybkie bilety i powiadomienia o najważniejszych terminach.</p>
            <div class="hero-actions">
                <a class="btn primary" href="#wydarzenia-miasto">Zobacz wydarzenia</a>
                <a class="btn" href="#typy-imprez-miasto">Zobacz formaty</a>
                <a class="btn city-notify-cta" href="#powiadomienia">Zapisz do powiadomień</a>
            </div>
            <div class="city-hero-stats">
                <article>
                    <strong><?= e((string) $eventCount) ?></strong>
                    <span>wydarzeń w kalendarzu</span>
                </article>
                <article>
                    <strong><?= e($nextEventDate) ?></strong>
                    <span>najbliższy termin</span>
                </article>
                <article>
                    <strong>2010</strong>
                    <span>organizujemy imprezy od</span>
                </article>
            </div>
        </div>
    </section>

    <section class="city-seo-intro">
        <div>
            <p class="eyebrow">Kalendarz imprez <?= e($city) ?></p>
            <h2>Najważniejsze wydarzenia akademickie <?= e($city) ?> w jednym miejscu</h2>
            <p>Szukasz imprezy, na której naprawdę będzie czuć akademicki klimat miasta <?= e($city) ?>? Tutaj zbieramy największe terminy, wydarzenia na Facebooku, linki do biletów i zapisy na powiadomienia, żeby łatwo wejść w sezon i nie przegapić dużych edycji.</p>
            <p>Interesuje Cię <strong>integracja studencka <?= e($city) ?></strong>, <strong>inauguracja studencka <?= e($city) ?></strong>, <strong>otrzęsiny studenckie <?= e($city) ?></strong>, clubbing studencki, połowinki albo juwenalia? Wybierz wydarzenie, sprawdź szczegóły i dołącz do imprezy, o której będzie mówiło miasto.</p>
            <div class="city-sales-grid">
                <article>
                    <span>Duża frekwencja</span>
                    <strong>Studenci z całego miasta i województwa</strong>
                    <p>Naszą największą atrakcją są ludzie. Tworzymy wydarzenia, na których łatwo spotkać znajomych, poznać nowe osoby i wejść w prawdziwy akademicki klimat.</p>
                </article>
                <article>
                    <span>Najważniejsze formaty</span>
                    <strong>Inauguracje, integracje, otrzęsiny i clubbing</strong>
                    <p>Od pierwszych tygodni roku akademickiego po juwenalia i imprezy sezonowe. Wszystkie największe formaty wydarzeń studenckich w mieście <?= e($city) ?> masz w jednym miejscu.</p>
                </article>
                <article>
                    <span>Powiadomienia</span>
                    <strong>Nie przegap kolejnych edycji</strong>
                    <p>Zapisz się na SMS lub email, a damy znać o nowych terminach, dużych wydarzeniach i pulach biletów, zanim zrobi się o nich najgłośniej.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="city-promo-band">
        <div>
            <p class="eyebrow">Nie czekaj na ostatnią chwilę</p>
            <h2><?= e($city) ?> wybiera największe eventy studenckie</h2>
            <p>Najlepsze imprezy szybko zapełniają parkiet. Sprawdzaj aktualne terminy, dołączaj do wydarzeń na Facebooku i zapisuj się na powiadomienia, gdy bilety dopiero mają się pojawić.</p>
        </div>
        <a href="#powiadomienia">Chcę powiadomienia</a>
    </section>

    <section class="section city-events-section" id="wydarzenia-miasto">
        <div class="section-head">
            <div>
                <p class="eyebrow"><?= e($city) ?></p>
                <h2>Najbliższe imprezy studenckie <?= e($city) ?></h2>
            </div>
            <a class="btn small filter-submit" href="/#wydarzenia">Cały kalendarz</a>
        </div>
        <div class="event-grid">
            <?php foreach ($events as $item): ?>
                <?php App\Core\View::partial('public/partials/event-card', ['item' => $item]); ?>
            <?php endforeach; ?>
            <?php if (!$events): ?>
                <div class="empty city-empty-events">
                    <p>Terminy dla miasta <?= e($city) ?> pojawią się wkrótce. Zapisz się na powiadomienia, a damy znać, gdy ogłosimy inauguracje, integracje, otrzęsiny albo kolejne duże imprezy studenckie.</p>
                    <a href="#powiadomienia">Zapisz się do powiadomień</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="city-type-section" id="typy-imprez-miasto">
        <div class="city-type-head">
            <div>
                <p class="eyebrow">Wydarzenia <?= e($city) ?></p>
                <h2>Największe formaty imprez studenckich <?= e($city) ?></h2>
            </div>
            <p>Od rozpoczęcia roku akademickiego po juwenalia. Sprawdź, które wydarzenia najbardziej pasują do Twojej ekipy, kierunku i sezonu.</p>
        </div>
        <div class="city-type-grid">
            <?php foreach ($cityEventTypes as $index => [$typeTitle, $typeDescription]): ?>
                <article class="city-type-card">
                    <span><?= e($typeTitle . ' ' . $city) ?></span>
                    <h3><?= e($typeTitle) ?> <?= e($city) ?></h3>
                    <p><?= e($typeDescription) ?> Sprawdzaj terminy, bilety i wydarzenia typu <?= e(strtolower($typeTitle)) ?> <?= e($city) ?> w aktualnym kalendarzu.</p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="city-faq-section">
        <div class="city-faq-head">
            <p class="eyebrow">FAQ <?= e($city) ?></p>
            <h2>Najczęstsze pytania o imprezy studenckie <?= e($city) ?></h2>
        </div>
        <div class="city-faq-grid">
            <?php foreach ($faqQuestions as $faq): ?>
                <article>
                    <h3><?= e($faq['name']) ?></h3>
                    <p><?= e($faq['text']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="city-links-section">
        <div class="city-links-head">
            <p class="eyebrow">Inne miasta</p>
            <h2>Sprawdź imprezy studenckie w pozostałych miastach</h2>
        </div>
        <div class="city-links-grid">
            <?php foreach ($allCities as $otherSlug => $otherCity): ?>
                <?php if ($otherSlug === $citySlug) continue; ?>
                <a href="/miasta/<?= e($otherSlug) ?>">Imprezy studenckie <?= e($otherCity) ?></a>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="powiadomienia" class="event-notify-block city-notify-block">
        <div class="event-notify-head">
            <div>
                <p class="eyebrow">Powiadomienia</p>
                <h2>Nie przegap imprez studenckich w mieście <?= e($city) ?></h2>
            </div>
            <p>Zostaw kontakt, a damy znać o inauguracjach, integracjach, otrzęsinach, połowinkach, juwenaliach i nowych pulach biletów.</p>
        </div>
        <div class="event-notify-layout">
            <article class="event-notify-promise">
                <span>SMS / EMAIL</span>
                <strong><?= e($city) ?> czeka na najlepsze eventy</strong>
                <p>Wyślemy tylko informacje o największych i najważniejszych imprezach studenckich w tym mieście.</p>
            </article>
            <form method="post" action="/kontakt-marketingowy" class="event-notify-form">
                <?= csrf_field() ?>
                <?= spam_trap_field() ?>
                <input type="hidden" name="redirect_to" value="/miasta/<?= e($citySlug) ?>">
                <input type="hidden" name="city" value="<?= e($city) ?>">
                <label>Miasto <input value="<?= e($city) ?>" disabled></label>
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
