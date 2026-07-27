<main class="auth-card">
    <h1>Kod logowania</h1>
    <p>Wysłaliśmy jednorazowy kod na adres: <strong><?= e($email ?? '') ?></strong>.</p>
    <p>Kod jest ważny przez 10 minut.</p>
    <?php if (!empty($error)): ?><p class="alert"><?= e($error) ?></p><?php endif; ?>
    <form method="post" action="/admin/login/kod">
        <?= csrf_field() ?>
        <label>Kod z emaila <input name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required></label>
        <button class="admin-btn">Potwierdź logowanie</button>
    </form>
    <a href="/admin/login?reset=1">Wróć do logowania</a>
</main>
