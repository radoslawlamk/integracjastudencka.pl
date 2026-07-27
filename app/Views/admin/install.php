<main class="auth-card">
    <h1>Instalacja CRM</h1>
    <p>Ten ekran tworzy tabele bazy danych i pierwsze konto administratora.</p>
    <?php if (!empty($error)): ?><p class="alert"><?= e($error) ?></p><?php endif; ?>
    <form method="post" action="/admin/install">
        <?= csrf_field() ?>
        <label>Imię <input name="name" value="Administrator" required></label>
        <label>Email <input type="email" name="email" required></label>
        <label>Hasło <input type="password" name="password" minlength="8" required></label>
        <button class="admin-btn">Utwórz CRM</button>
    </form>
</main>
