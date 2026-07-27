<main>
    <section class="sitemap-hero">
        <p class="eyebrow">Mapa strony</p>
        <h1>Wszystkie ważne podstrony Integracja Studencka</h1>
        <p>Miasta akademickie, typy imprez, aktualne wydarzenia i newsy w jednym miejscu.</p>
    </section>

    <section class="sitemap-grid">
        <article>
            <h2>Najważniejsze strony</h2>
            <a href="/">Strona główna</a>
            <a href="/miasta">Miasta akademickie</a>
            <a href="/#wydarzenia">Kalendarz wydarzeń</a>
            <a href="/#newsy">Newsy</a>
            <a href="/#powiadomienia">Powiadomienia</a>
            <a href="/regulamin">Regulamin</a>
            <a href="/polityka-prywatnosci">Polityka prywatności</a>
            <a href="/polityka-cookies">Polityka cookies</a>
            <a href="/wycofaj-zgody">Wycofaj zgody</a>
        </article>

        <article>
            <h2>Typy imprez</h2>
            <?php foreach ($eventTypes as $slug => $type): ?>
                <a href="/<?= e($slug) ?>"><?= e($type['name']) ?></a>
            <?php endforeach; ?>
        </article>

        <article>
            <h2>Miasta akademickie</h2>
            <?php foreach ($cities as $slug => $city): ?>
                <a href="/miasta/<?= e($slug) ?>">Imprezy studenckie <?= e($city) ?></a>
            <?php endforeach; ?>
        </article>

        <article>
            <h2>Wydarzenia</h2>
            <?php foreach ($events as $event): ?>
                <a href="/wydarzenia/<?= e($event['slug']) ?>"><?= e($event['title']) ?></a>
            <?php endforeach; ?>
        </article>

        <article>
            <h2>Newsy</h2>
            <?php foreach ($newsArticles as $article): ?>
                <a href="/news/<?= e($article['slug']) ?>"><?= e($article['title']) ?></a>
            <?php endforeach; ?>
        </article>
    </section>
</main>
