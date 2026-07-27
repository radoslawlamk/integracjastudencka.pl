<main>
    <section class="type-page-hero">
        <div>
            <p class="eyebrow">Jakie imprezy organizujemy?</p>
            <h1><?= e($type['name']) ?></h1>
            <p><?= e($type['description']) ?> Wybierz miasto, sprawdź najbliższe terminy i zapisz się na powiadomienia, jeśli czekasz na kolejną edycję.</p>
            <div class="hero-actions">
                <a class="btn primary" href="#wydarzenia-typu">Zobacz wydarzenia</a>
                <a class="btn" href="#miasta-typu">Wybierz miasto</a>
            </div>
        </div>
    </section>

    <section class="type-page-sales">
        <div>
            <p class="eyebrow">Od 2010 roku</p>
            <h2><?= e($type['name']) ?> w miastach akademickich</h2>
            <p>Od 2010 roku tworzymy największe wydarzenia studenckie w Polsce. Integrujemy tysiące studentów podczas imprez, które na długo zostają w pamięci.</p>
        </div>
        <strong>Ogromna frekwencja i wyjątkowa atmosfera - to nasz znak rozpoznawczy.</strong>
    </section>

    <section class="section" id="wydarzenia-typu">
        <div class="section-head">
            <div>
                <p class="eyebrow">Kalendarz</p>
                <h2>Najbliższe wydarzenia: <?= e($type['name']) ?></h2>
            </div>
            <a class="btn small filter-submit" href="/#wydarzenia">Cały kalendarz</a>
        </div>
        <div class="event-grid">
            <?php foreach ($events as $item): ?>
                <?php App\Core\View::partial('public/partials/event-card', ['item' => $item]); ?>
            <?php endforeach; ?>
            <?php if (!$events): ?>
                <div class="empty city-empty-events">
                    <p>Nowe terminy dla formatu <?= e($type['name']) ?> pojawią się wkrótce. Zapisz się na powiadomienia w swoim mieście, a damy znać jako pierwsi.</p>
                    <a href="/#powiadomienia">Zapisz się na powiadomienia</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="type-city-links" id="miasta-typu">
        <div>
            <p class="eyebrow">Miasta akademickie</p>
            <h2><?= e($type['name']) ?> w Twoim mieście</h2>
            <p>Sprawdź lokalne podstrony i zobacz, gdzie pojawiają się inauguracje, integracje, otrzęsiny, clubbing, juwenalia i inne największe wydarzenia akademickie.</p>
        </div>
        <div class="city-links-grid">
            <?php foreach ($cities as $citySlug => $cityName): ?>
                <a href="/miasta/<?= e($citySlug) ?>"><?= e($type['name']) ?> <?= e($cityName) ?></a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="type-more-links">
        <p class="eyebrow">Pozostałe formaty</p>
        <div>
            <?php foreach ($eventTypes as $otherSlug => $otherType): ?>
                <?php if ($otherSlug === $slug) continue; ?>
                <a href="/<?= e($otherSlug) ?>"><?= e($otherType['name']) ?></a>
            <?php endforeach; ?>
        </div>
    </section>
</main>
