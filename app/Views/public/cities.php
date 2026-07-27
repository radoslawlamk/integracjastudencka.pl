<main>
    <section class="city-index-hero">
        <div>
            <p class="eyebrow">Miasta akademickie</p>
            <h1>Wybierz miasto i sprawdź największe imprezy studenckie</h1>
            <p>Inauguracje, integracje, otrzęsiny, clubbing, połowinki, juwenalia i najważniejsze wydarzenia akademickie w całej Polsce. Wybierz swoje miasto i zobacz, gdzie zaczyna się sezon.</p>
            <div class="hero-actions">
                <a class="btn primary" href="#lista-miast">Wybierz miasto</a>
                <a class="btn city-notify-cta" href="/#powiadomienia">Zapisz się na powiadomienia</a>
            </div>
        </div>
    </section>

    <section class="city-index-sales">
        <div>
            <p class="eyebrow">Cała Polska</p>
            <h2>Największe wydarzenia studenckie podzielone na miasta</h2>
            <p>Każde miasto ma własny kalendarz imprez, podstronę z wydarzeniami, zapisami na powiadomienia i formatami takimi jak inauguracja studencka, integracja studencka, otrzęsiny, clubbing czy juwenalia.</p>
        </div>
        <div class="city-index-sales-cards">
            <article><strong>20</strong><span>miast akademickich</span></article>
            <article><strong>2010</strong><span>imprezy organizujemy od</span></article>
            <article><strong>SMS / email</strong><span>powiadomienia o terminach</span></article>
        </div>
    </section>

    <section class="city-index-section" id="lista-miast">
        <div class="city-index-head">
            <p class="eyebrow">Wybierz kierunek</p>
            <h2>Sprawdź imprezy studenckie w swoim mieście</h2>
        </div>
        <div class="city-index-grid">
            <?php foreach ($cities as $slug => $city): ?>
                <?php
                $cityInitials = implode('', array_map(static fn (string $part): string => mb_substr($part, 0, 1), explode(' ', str_replace('-', ' ', $city))));
                ?>
                <a href="/miasta/<?= e($slug) ?>" class="city-index-card" data-city-slug="<?= e($slug) ?>">
                    <span>Miasto akademickie</span>
                    <b aria-hidden="true"><?= e(mb_strtoupper($cityInitials)) ?></b>
                    <strong><?= e($city) ?></strong>
                    <small>Imprezy studenckie <?= e($city) ?></small>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</main>
