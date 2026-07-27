<header class="admin-head">
    <div><p>CRM</p><h1>Newsy</h1></div>
    <a class="admin-btn" href="/admin/newsy/nowy">Dodaj news</a>
</header>
<section class="panel">
    <table>
        <thead><tr><th>Tytuł</th><th>Status</th><th>Data publikacji</th><th>Adres</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($articles as $article): ?>
            <tr>
                <td><?= e($article['title']) ?></td>
                <td><?= e($article['status']) ?></td>
                <td><?= e($article['published_at'] ?: $article['created_at']) ?></td>
                <td><a href="/news/<?= e($article['slug']) ?>" target="_blank">/news/<?= e($article['slug']) ?></a></td>
                <td><a href="/admin/newsy/<?= e((string) $article['id']) ?>/edycja">Edytuj</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$articles): ?>
            <tr><td colspan="5">Nie ma jeszcze artykułów.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</section>
