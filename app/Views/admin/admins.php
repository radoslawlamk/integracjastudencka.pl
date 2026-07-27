<header class="admin-head">
    <div><p>CRM</p><h1>Administratorzy</h1></div>
</header>

<?php if ($status === 'dodano'): ?>
    <p class="alert success">Dodano nowe konto administratora.</p>
<?php elseif ($status === 'istnieje'): ?>
    <p class="alert">Konto z takim adresem email już istnieje.</p>
<?php elseif ($status === 'blad'): ?>
    <p class="alert">Podaj imię, poprawny email i hasło minimum 8 znaków.</p>
<?php endif; ?>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Dodaj administratora</h2>
            <p class="muted">Nowy admin będzie logował się swoim emailem, hasłem i kodem jednorazowym wysłanym na email.</p>
        </div>
    </div>
    <form method="post" action="/admin/administratorzy" class="form-grid">
        <?= csrf_field() ?>
        <label>Imię <input name="name" required></label>
        <label>Email <input type="email" name="email" required></label>
        <label>Hasło <input type="password" name="password" minlength="8" required></label>
        <div class="wide">
            <button class="admin-btn">Dodaj admina</button>
        </div>
    </form>
</section>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Aktywne konta</h2>
            <p class="muted">Lista kont, które mogą zalogować się do CRM.</p>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Imię</th>
                <th>Email</th>
                <th>Utworzono</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($admins as $admin): ?>
                <tr>
                    <td><?= e($admin['name']) ?></td>
                    <td><?= e($admin['email']) ?></td>
                    <td><?= e($admin['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
