<?php

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path): string
{
    return $path;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function spam_trap_field(): string
{
    return '<label class="spam-trap" aria-hidden="true">Strona www <input type="text" name="website" tabindex="-1" autocomplete="off"></label>';
}

function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    if (!hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf'] ?? '')) {
        http_response_code(419);
        exit('Sesja wygasła. Odśwież stronę i spróbuj ponownie.');
    }
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function slugify(string $text): string
{
    $map = ['ą'=>'a','ć'=>'c','ę'=>'e','ł'=>'l','ń'=>'n','ó'=>'o','ś'=>'s','ż'=>'z','ź'=>'z','Ą'=>'a','Ć'=>'c','Ę'=>'e','Ł'=>'l','Ń'=>'n','Ó'=>'o','Ś'=>'s','Ż'=>'z','Ź'=>'z'];
    $text = strtr($text, $map);
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: bin2hex(random_bytes(4));
}

function is_admin(): bool
{
    return isset($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!is_admin()) {
        redirect('/admin/login');
    }
}

function event_sales_state(array $event): array
{
    $now = time();
    $startsAt = strtotime((string) ($event['starts_at'] ?? '')) ?: $now;
    $endsAt = !empty($event['ends_at']) ? (strtotime((string) $event['ends_at']) ?: null) : null;
    if (!$endsAt || $endsAt <= $startsAt) {
        $endsAt = strtotime('+6 hours', $startsAt);
    }

    $phase = 'upcoming';
    if ($now >= $startsAt && $now <= $endsAt) {
        $phase = 'in_progress';
    } elseif ($now > $endsAt) {
        $phase = 'finished';
    }

    $manualStatus = $event['sales_status'] ?? 'auto';
    $hasTicket = trim((string) ($event['ticket_url'] ?? '')) !== '';
    $sales = $manualStatus === 'auto' ? ($hasTicket ? 'on_sale' : 'tickets_soon') : $manualStatus;
    if ($phase === 'finished' || $sales === 'finished') {
        $sales = 'finished';
    }

    return [
        'phase' => $phase,
        'sales' => $sales,
        'starts_at' => $startsAt,
        'ends_at' => $endsAt,
        'has_ticket' => $hasTicket,
        'can_buy' => $hasTicket && $sales === 'on_sale' && $phase !== 'finished',
        'tickets_soon' => $sales === 'tickets_soon' && $phase !== 'finished',
        'sold_out' => $sales === 'sold_out',
        'finished' => $sales === 'finished' || $phase === 'finished',
        'in_progress' => $phase === 'in_progress',
    ];
}
