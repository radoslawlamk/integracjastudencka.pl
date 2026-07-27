<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'CRM') ?></title>
    <link rel="stylesheet" href="/assets/admin.css">
</head>
<body>
<aside class="admin-side">
    <a class="admin-brand" href="/admin">CRM</a>
    <a href="/admin/wydarzenia">Wydarzenia</a>
    <a href="/admin/wydarzenia/nowe">Dodaj wydarzenie</a>
    <a href="/admin/newsy">Newsy</a>
    <a href="/admin/newsy/nowy">Dodaj news</a>
    <a href="/admin/kontakty">Baza marketingowa</a>
    <a href="/admin/powiadomienia-bilety">Powiadomienia o biletach</a>
    <a href="/admin/analityka">Analityka</a>
    <a href="/admin/administratorzy">Administratorzy</a>
    <a href="/" target="_blank">Strona publiczna</a>
    <form method="post" action="/admin/logout"><?= csrf_field() ?><button>Wyloguj</button></form>
</aside>
<main class="admin-main">
    <?= $content ?>
</main>
<script src="/assets/admin.js"></script>
</body>
</html>
