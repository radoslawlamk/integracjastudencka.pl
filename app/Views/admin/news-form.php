<?php
$isEdit = (bool) $article;
$action = $isEdit ? '/admin/newsy/' . $article['id'] : '/admin/newsy';
$value = fn(string $key, string $default = '') => e($article[$key] ?? $default);
$publishedAt = $article && !empty($article['published_at']) ? date('Y-m-d\TH:i', strtotime($article['published_at'])) : date('Y-m-d\TH:i');
?>
<header class="admin-head">
    <div><p>CRM</p><h1><?= $isEdit ? 'Edytuj news' : 'Dodaj news' ?></h1></div>
    <a class="ghost" href="/admin/newsy">Wróć</a>
</header>
<form class="editor" method="post" action="<?= e($action) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <section class="panel form-grid">
        <label>Status
            <select name="status">
                <option value="draft" <?= ($article['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Szkic</option>
                <option value="published" <?= ($article['status'] ?? '') === 'published' ? 'selected' : '' ?>>Opublikowany</option>
                <option value="archived" <?= ($article['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archiwalny</option>
            </select>
        </label>
        <label>Data publikacji <input type="datetime-local" name="published_at" value="<?= e($publishedAt) ?>"></label>
        <label>Tytuł <input name="title" value="<?= $value('title') ?>" required></label>
        <label>Adres / slug <input name="slug" value="<?= $value('slug') ?>" placeholder="np. terminy-inauguracji-integracji-studenckich"></label>
        <label>Grafika z komputera <input type="file" name="news_image_upload" accept="image/jpeg,image/png,image/webp,image/gif"></label>
        <label>Grafika URL <input name="image_url" value="<?= $value('image_url') ?>" placeholder="Opcjonalnie, gdy grafika jest już online"></label>
        <label class="wide">Zajawka <textarea name="excerpt" rows="3" maxlength="260" data-counter="newsExcerpt"><?= $value('excerpt') ?></textarea><small><span id="newsExcerptCount">0</span>/260 znaków</small></label>
        <label class="wide">Treść artykułu <textarea name="content" rows="14" required><?= $value('content') ?></textarea></label>
        <label>SEO title <input name="seo_title" value="<?= $value('seo_title') ?>"></label>
        <label>SEO description <input name="seo_description" value="<?= $value('seo_description') ?>"></label>
    </section>
    <div class="sticky-actions">
        <button class="admin-btn">Zapisz news</button>
        <?php if ($isEdit): ?>
            <button class="danger" formaction="/admin/newsy/<?= e((string) $article['id']) ?>/usun" onclick="return confirm('Usunąć news?')">Usuń</button>
        <?php endif; ?>
    </div>
</form>
