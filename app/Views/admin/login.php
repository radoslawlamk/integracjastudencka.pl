<main class="auth-card">
    <h1>Logowanie do CRM</h1>
    <?php if (!empty($error)): ?><p class="alert"><?= e($error) ?></p><?php endif; ?>
    <form method="post" action="/admin/login">
        <?= csrf_field() ?>
        <label>Email <input type="email" name="email" required></label>
        <label>Hasło <input type="password" name="password" required></label>
        <button class="admin-btn">Wyślij kod logowania</button>
    </form>
    <a href="/admin/install">Pierwsza instalacja</a>
</main>
