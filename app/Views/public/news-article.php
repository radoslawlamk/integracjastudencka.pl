<main class="article-page article-party-page">
    <article class="article">
        <nav class="article-nav-actions" aria-label="Nawigacja artykułu">
            <a href="/#newsy">Wróć do newsów</a>
            <a href="/">Strona główna</a>
            <a href="/#wydarzenia">Kalendarz wydarzeń</a>
        </nav>

        <p class="eyebrow">News</p>
        <h1><?= e($article['title']) ?></h1>
        <?php if (!empty($article['excerpt'])): ?>
            <p class="article-lead"><?= e($article['excerpt']) ?></p>
        <?php endif; ?>
        <?php if (!empty($article['image_url'])): ?>
            <img class="article-image" src="<?= e($article['image_url']) ?>" alt="<?= e($article['title']) ?>">
        <?php endif; ?>
        <div class="article-content">
            <?= nl2br(e($article['content'])) ?>
        </div>
        <div class="article-bottom-actions">
            <a class="btn primary" href="/#wydarzenia">Sprawdź kalendarz wydarzeń</a>
            <a class="btn" href="/#newsy">Wróć do newsów</a>
        </div>
    </article>

    <?php if (!empty($relatedNews)): ?>
        <section class="article-more-news">
            <div class="article-more-head">
                <div>
                    <p class="eyebrow">Czytaj też</p>
                    <h2>Inne newsy studenckie</h2>
                </div>
                <div class="news-controls">
                    <button type="button" data-article-news-prev aria-label="Poprzednie newsy">‹</button>
                    <button type="button" data-article-news-next aria-label="Następne newsy">›</button>
                </div>
            </div>
            <div class="article-more-carousel" id="articleMoreNews">
                <?php foreach ($relatedNews as $item): ?>
                    <article class="article-more-card" style="<?= $item['image_url'] ? 'background-image: linear-gradient(135deg, rgba(0,0,0,.76), rgba(0,0,0,.42)), url(' . e($item['image_url']) . ')' : '' ?>">
                        <div>
                            <span><?= e(date('d.m.Y', strtotime($item['published_at'] ?: $item['created_at']))) ?></span>
                            <h3><?= e($item['title']) ?></h3>
                            <p><?= e($item['excerpt']) ?></p>
                            <a href="/news/<?= e($item['slug']) ?>">Przeczytaj news</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section id="powiadomienia" class="event-notify-block article-notify-block">
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
                <p>Wyślemy tylko informacje o największych i najważniejszych imprezach studenckich w wybranym mieście.</p>
            </article>
            <form method="post" action="/kontakt-marketingowy" class="event-notify-form">
                <?= csrf_field() ?>
                <?= spam_trap_field() ?>
                <input type="hidden" name="redirect_to" value="<?= e($_SERVER['REQUEST_URI'] ?? '/news/' . $article['slug']) ?>">
                <label>Miasto
                    <select name="city" required>
                        <?php foreach (['Białystok','Bydgoszcz','Częstochowa','Katowice','Kielce','Kraków','Łódź','Lublin','Olsztyn','Opole','Poznań','Radom','Rzeszów','Szczecin','Tarnów','Toruń','Trójmiasto','Warszawa','Wrocław','Zielona Góra'] as $city): ?>
                            <option value="<?= e($city) ?>" <?= $city === 'Katowice' ? 'selected' : '' ?>><?= e($city) ?></option>
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
document.addEventListener('click', function (event) {
    const prev = event.target.closest('[data-article-news-prev]');
    const next = event.target.closest('[data-article-news-next]');
    if (!prev && !next) return;
    const carousel = document.getElementById('articleMoreNews');
    if (!carousel) return;
    carousel.scrollLeft += (next ? 1 : -1) * Math.min(520, carousel.clientWidth);
});
</script>
