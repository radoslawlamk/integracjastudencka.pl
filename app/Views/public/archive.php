<main>
    <section class="archive-hero">
        <p class="eyebrow">Archiwum</p>
        <h1>Zakończone wydarzenia studenckie</h1>
        <p>Tu trafiają imprezy po zakończeniu. Aktualne terminy, bilety i powiadomienia znajdziesz w głównym kalendarzu.</p>
        <div class="hero-actions">
            <a class="btn primary" href="/#wydarzenia">Sprawdź aktualny kalendarz</a>
            <a class="btn" href="/miasta">Wybierz miasto</a>
        </div>
    </section>

    <section class="section archive-section">
        <div class="section-head">
            <div>
                <p class="eyebrow">Historia wydarzeń</p>
                <h2>Archiwalne imprezy</h2>
            </div>
            <p>Podstrony pozostają dostępne dla osób, które mają link, ale nie mieszają się już z aktualną sprzedażą.</p>
        </div>
        <div class="archive-list">
            <?php foreach ($events as $event): ?>
                <article>
                    <a href="/wydarzenia/<?= e($event['slug']) ?>">
                        <span><?= e(date('d.m.Y', strtotime($event['starts_at']))) ?> - <?= e($event['city']) ?></span>
                        <strong><?= e($event['title']) ?></strong>
                        <small>Zobacz archiwalną stronę wydarzenia</small>
                    </a>
                </article>
            <?php endforeach; ?>
            <?php if (!$events): ?>
                <div class="empty">
                    <p>Archiwum jest jeszcze puste. Wydarzenia pojawią się tutaj automatycznie po dacie zakończenia.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
