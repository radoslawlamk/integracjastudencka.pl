<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Mailer;
use App\Core\View;
use App\Models\AdminUser;
use App\Models\Event;
use App\Models\EventAnalytics;
use App\Models\EventClub;
use App\Models\EventVideo;
use App\Models\MarketingContact;
use App\Models\NewsArticle;
use App\Models\Partner;
use App\Models\TicketWaitlist;

final class AdminController
{
    public function install(): string
    {
        if (AdminUser::count() > 0) {
            redirect('/admin/login');
        }

        return View::render('admin/install', ['title' => 'Instalacja CRM'], 'admin-auth');
    }

    public function installRun(): string
    {
        verify_csrf();
        $pdo = Database::pdo();
        $schema = file_get_contents(__DIR__ . '/../../database/schema.sql');
        foreach (array_filter(array_map('trim', explode(';', $schema))) as $statement) {
            $pdo->exec($statement);
        }

        if (AdminUser::count() > 0) {
            redirect('/admin/login');
        }

        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $name = trim($_POST['name'] ?? 'Administrator');

        if (!$email || strlen($password) < 8) {
            return View::render('admin/install', [
                'title' => 'Instalacja CRM',
                'error' => 'Podaj poprawny email i hasło o długości minimum 8 znaków.',
            ], 'admin-auth');
        }

        AdminUser::create($name, $email, $password);
        redirect('/admin/login');
    }

    public function dashboard(): string
    {
        require_admin();
        return View::render('admin/dashboard', [
            'title' => 'Panel CRM',
            'events' => Event::upcomingLimit(),
            'contacts' => MarketingContact::all(),
        ], 'admin');
    }

    public function events(): string
    {
        require_admin();
        $sort = $_GET['sort'] ?? 'starts_asc';
        $city = trim((string) ($_GET['city'] ?? ''));
        $showOnly = $_GET['show'] ?? 'upcoming';
        if (!in_array($showOnly, ['all', 'missing_ticket', 'missing_fb', 'missing_any', 'upcoming', 'finished', 'published', 'draft', 'tickets_soon', 'on_sale', 'sold_out'], true)) {
            $showOnly = 'all';
        }

        return View::render('admin/events', [
            'title' => 'Wydarzenia',
            'events' => Event::all($sort, $city ?: null, $showOnly),
            'selectedSort' => $sort,
            'selectedCity' => $city,
            'selectedShowOnly' => $showOnly,
            'cities' => Event::allCities(),
        ], 'admin');
    }

    public function eventCreate(): string
    {
        require_admin();
        return View::render('admin/event-form', [
            'title' => 'Nowe wydarzenie',
            'event' => null,
            'clubs' => [],
            'videos' => EventVideo::DEFAULT_VIDEOS,
            'partners' => [],
            'clubImages' => $this->imageLibrary('clubs'),
            'partnerLogos' => $this->imageLibrary('partners'),
        ], 'admin');
    }

    public function eventStore(): string
    {
        require_admin();
        verify_csrf();
        $data = $this->eventData();
        $id = Event::create($data);
        $this->syncRelations($id);
        redirect('/admin/wydarzenia');
    }

    public function eventEdit(array $params): string
    {
        require_admin();
        $event = Event::find((int) $params['id']);
        if (!$event) {
            redirect('/admin/wydarzenia');
        }
        return View::render('admin/event-form', [
            'title' => 'Edycja wydarzenia',
            'event' => $event,
            'clubs' => EventClub::forEvent((int) $event['id']),
            'videos' => EventVideo::forEvent((int) $event['id']),
            'partners' => Partner::forEvent((int) $event['id']),
            'ticketWaitlist' => TicketWaitlist::forEvent((int) $event['id']),
            'ticketNotifyStatus' => $_GET['bilety'] ?? '',
            'ticketNotifyCount' => (int) ($_GET['ile'] ?? 0),
            'clubImages' => $this->imageLibrary('clubs'),
            'partnerLogos' => $this->imageLibrary('partners'),
        ], 'admin');
    }

    public function eventUpdate(array $params): string
    {
        require_admin();
        verify_csrf();
        $id = (int) $params['id'];
        $previousEvent = Event::find($id);
        $data = $this->eventData($id);
        Event::update($id, $data);
        $this->syncRelations($id);

        if ($previousEvent && empty($previousEvent['ticket_url']) && !empty($data['ticket_url'])) {
            $updatedEvent = Event::find($id);
            $sent = $updatedEvent ? $this->sendTicketNotifications($updatedEvent) : 0;
            redirect('/admin/wydarzenia/' . $id . '/edycja?bilety=auto_wyslano&ile=' . $sent);
        }

        redirect('/admin/wydarzenia');
    }

    public function eventDelete(array $params): string
    {
        require_admin();
        verify_csrf();
        Event::delete((int) $params['id']);
        redirect('/admin/wydarzenia');
    }

    public function eventNotifyTickets(array $params): string
    {
        require_admin();
        verify_csrf();

        $event = Event::find((int) $params['id']);
        if (!$event) {
            redirect('/admin/wydarzenia');
        }

        if (empty($event['ticket_url'])) {
            redirect('/admin/wydarzenia/' . (int) $event['id'] . '/edycja?bilety=brak_linku');
        }

        $sent = $this->sendTicketNotifications($event);
        if ($sent === 0) {
            redirect('/admin/wydarzenia/' . (int) $event['id'] . '/edycja?bilety=brak');
        }

        redirect('/admin/wydarzenia/' . (int) $event['id'] . '/edycja?bilety=wyslano&ile=' . $sent);
    }

    private function sendTicketNotifications(array $event): int
    {
        $recipients = TicketWaitlist::pendingEmailRecipientsForEvent((int) $event['id']);
        if (!$recipients) {
            return 0;
        }

        $sent = 0;
        $sentIds = [];
        foreach ($recipients as $recipient) {
            $name = trim($recipient['name'] ?? '');
            $greeting = $name !== '' ? "Czesc {$name},\n\n" : "Czesc,\n\n";
            $subject = 'Bilety juz dostepne - ' . $event['title'];
            $unsubscribeUrl = $this->unsubscribeUrl($recipient['unsubscribe_token'] ?? '');
            $body = $greeting
                . 'Bilety na wydarzenie "' . $event['title'] . "\" sa juz dostepne.\n\n"
                . "Kup bilet tutaj: " . $event['ticket_url'] . "\n\n"
                . ($unsubscribeUrl ? "Zarzadzanie powiadomieniami email/SMS: {$unsubscribeUrl}\n\n" : '')
                . "Do zobaczenia,\nIntegracja Studencka";
            $ok = Mailer::send($recipient['email'], $subject, $body);
            if (!$ok) {
                continue;
            }

            $sent++;
            $sentIds[] = (int) $recipient['id'];
        }

        TicketWaitlist::markNotified($sentIds);
        return $sent;
    }

    public function contacts(): string
    {
        require_admin();
        $city = trim($_GET['city'] ?? '');
        return View::render('admin/contacts', [
            'title' => 'Baza marketingowa',
            'contacts' => MarketingContact::filter($city ?: null),
            'cities' => MarketingContact::cities(),
            'selectedCity' => $city,
            'emailStatus' => $_GET['email'] ?? '',
        ], 'admin');
    }

    public function ticketWaitlist(): string
    {
        require_admin();
        $eventId = (int) ($_GET['event_id'] ?? 0);
        $status = in_array($_GET['status'] ?? '', ['pending', 'notified'], true) ? $_GET['status'] : '';

        return View::render('admin/ticket-waitlist', [
            'title' => 'Powiadomienia o biletach',
            'waitlist' => TicketWaitlist::all($eventId ?: null, $status ?: null),
            'events' => TicketWaitlist::eventsWithWaitlist(),
            'selectedEventId' => $eventId,
            'selectedStatus' => $status,
        ], 'admin');
    }

    public function analytics(): string
    {
        require_admin();
        $sort = $_GET['sort'] ?? 'total_clicks';
        $range = $_GET['range'] ?? 'all';
        if (!in_array($range, ['today', 'yesterday', '7d', '30d', 'all'], true)) {
            $range = 'all';
        }
        $eventStatus = $_GET['event_status'] ?? 'all';
        if (!in_array($eventStatus, ['all', 'upcoming', 'finished'], true)) {
            $eventStatus = 'all';
        }
        $season = $_GET['season'] ?? 'current';
        if ($season !== 'all' && $season !== 'current' && !ctype_digit((string) $season)) {
            $season = 'current';
        }

        return View::render('admin/analytics', [
            'title' => 'Analityka wydarzeń',
            'sort' => $sort,
            'range' => $range,
            'eventStatus' => $eventStatus,
            'season' => $season,
            'seasons' => EventAnalytics::seasons(),
            'resetStatus' => $_GET['reset'] ?? '',
            'seasonStart' => EventAnalytics::currentSeasonStart(),
            'totals' => EventAnalytics::totals($range, $eventStatus, $season),
            'ranking' => EventAnalytics::dashboard($sort, $range, $eventStatus, $season),
            'sources' => EventAnalytics::sources($range, $eventStatus, $season),
            'sourceTotals' => EventAnalytics::sourceTotals($range, $eventStatus, $season),
            'cityRanking' => EventAnalytics::cityRanking($range, $eventStatus, $season),
            'actionLabels' => EventAnalytics::actionLabels(),
        ], 'admin');
    }

    public function analyticsReset(): string
    {
        require_admin();
        verify_csrf();

        EventAnalytics::resetSeason();
        redirect('/admin/analityka?reset=ok');
    }

    public function admins(): string
    {
        require_admin();

        return View::render('admin/admins', [
            'title' => 'Administratorzy',
            'admins' => AdminUser::all(),
            'status' => $_GET['status'] ?? '',
        ], 'admin');
    }

    public function adminStore(): string
    {
        require_admin();
        verify_csrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = (string) ($_POST['password'] ?? '');

        if ($name === '' || !$email || strlen($password) < 8) {
            redirect('/admin/administratorzy?status=blad');
        }

        $created = AdminUser::create($name, $email, $password);
        redirect('/admin/administratorzy?status=' . ($created ? 'dodano' : 'istnieje'));
    }

    public function contactsEmail(): string
    {
        require_admin();
        verify_csrf();

        $ids = $_POST['contact_ids'] ?? [];
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $recipients = MarketingContact::emailRecipients($ids);

        if (!$recipients || $subject === '' || $message === '') {
            redirect('/admin/kontakty?email=blad');
        }

        $sent = 0;
        foreach ($recipients as $recipient) {
            $name = trim($recipient['name'] ?? '');
            $greeting = $name !== '' ? "Cześć {$name},\n\n" : "Cześć,\n\n";
            $unsubscribeUrl = $this->unsubscribeUrl($recipient['unsubscribe_token'] ?? '');
            $body = $greeting . $message . "\n\n"
                . ($unsubscribeUrl ? "Zarzadzanie zgoda na email/SMS: {$unsubscribeUrl}\n\n" : '')
                . "Pozdrawiamy,\nIntegracja Studencka";
            $ok = Mailer::send($recipient['email'], $subject, $body);
            if ($ok) {
                $sent++;
                continue;
            }
        }

        redirect('/admin/kontakty?email=wyslano&ile=' . $sent);
    }

    public function news(): string
    {
        require_admin();
        return View::render('admin/news', ['title' => 'Newsy', 'articles' => NewsArticle::all()], 'admin');
    }

    public function newsCreate(): string
    {
        require_admin();
        return View::render('admin/news-form', ['title' => 'Nowy news', 'article' => null], 'admin');
    }

    public function newsStore(): string
    {
        require_admin();
        verify_csrf();
        NewsArticle::create($this->newsData());
        redirect('/admin/newsy');
    }

    public function newsEdit(array $params): string
    {
        require_admin();
        $article = NewsArticle::find((int) $params['id']);
        if (!$article) {
            redirect('/admin/newsy');
        }
        return View::render('admin/news-form', ['title' => 'Edycja newsa', 'article' => $article], 'admin');
    }

    public function newsUpdate(array $params): string
    {
        require_admin();
        verify_csrf();
        $id = (int) $params['id'];
        NewsArticle::update($id, $this->newsData($id));
        redirect('/admin/newsy');
    }

    public function newsDelete(array $params): string
    {
        require_admin();
        verify_csrf();
        NewsArticle::delete((int) $params['id']);
        redirect('/admin/newsy');
    }

    public function contactsExport(): string
    {
        require_admin();
        $city = trim($_GET['city'] ?? '');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=kontakty-marketingowe.csv');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Imię', 'Email', 'Telefon', 'Miasto', 'Tagi', 'SMS', 'Email zgoda', 'Zgoda', 'Data']);
        foreach (MarketingContact::filter($city ?: null) as $contact) {
            fputcsv($out, [$contact['name'], $contact['email'], $contact['phone'], $contact['city'], $contact['tags'], $contact['sms_consent'] ? 'tak' : 'nie', $contact['email_consent'] ? 'tak' : 'nie', $contact['marketing_consent'] ? 'tak' : 'nie', $contact['created_at']]);
        }
        exit;
    }

    public function testEmail(): string
    {
        require_admin();
        verify_csrf();

        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        if (!$email) {
            redirect('/admin?test_email=blad');
        }

        $subject = 'Test wysylki - Integracja Studencka';
        $body = "To jest test wysylki z panelu CRM.\n\n"
            . "Jesli ta wiadomosc dotarla, serwer potrafi wyslac email z adresu integracja@integracjastudencka.pl.\n\n"
            . "Integracja Studencka";
        $sent = Mailer::send($email, $subject, $body);

        redirect('/admin?test_email=' . ($sent ? 'ok' : 'blad'));
    }

    private function eventData(?int $id = null): array
    {
        $current = $id ? Event::find($id) : null;
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: slugify($title . '-' . ($_POST['city'] ?? ''));
        $shortDescription = trim($_POST['short_description'] ?? '');
        if (strlen($shortDescription) > 220) {
            $shortDescription = substr($shortDescription, 0, 220);
        }
        $startsAt = str_replace('T', ' ', $_POST['starts_at'] ?? '') . ':00';
        $endsAt = trim((string) ($_POST['ends_at'] ?? ''));
        $endsAt = $endsAt !== '' ? str_replace('T', ' ', $endsAt) . ':00' : date('Y-m-d H:i:s', strtotime($startsAt . ' +6 hours'));

        return [
            'type' => in_array($_POST['type'] ?? 'regular', ['regular', 'clubbing'], true) ? $_POST['type'] : 'regular',
            'status' => in_array($_POST['status'] ?? 'draft', ['draft', 'published', 'archived'], true) ? $_POST['status'] : 'draft',
            'sales_status' => in_array($_POST['sales_status'] ?? 'auto', ['auto', 'tickets_soon', 'on_sale', 'sold_out', 'finished'], true) ? $_POST['sales_status'] : 'auto',
            'title' => $title,
            'slug' => $slug,
            'city' => trim($_POST['city'] ?? ''),
            'region' => trim($_POST['region'] ?? ''),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'venue_name' => trim($_POST['venue_name'] ?? ''),
            'venue_address' => trim($_POST['venue_address'] ?? ''),
            'short_description' => $shortDescription,
            'description' => trim($_POST['description'] ?? ''),
            'ticket_url' => trim($_POST['ticket_url'] ?? ''),
            'facebook_event_url' => trim($_POST['facebook_event_url'] ?? ''),
            'fanpage_url' => trim($_POST['fanpage_url'] ?? ''),
            'hero_image' => $this->uploadedHeroImage($current['hero_image'] ?? trim($_POST['hero_image'] ?? '')),
            'seo_title' => trim($_POST['seo_title'] ?? ''),
            'seo_description' => trim($_POST['seo_description'] ?? ''),
        ];
    }

    private function uploadedHeroImage(string $fallback = ''): string
    {
        if (empty($_FILES['hero_upload']) || ($_FILES['hero_upload']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $fallback;
        }

        if ($_FILES['hero_upload']['error'] !== UPLOAD_ERR_OK || $_FILES['hero_upload']['size'] > 5 * 1024 * 1024) {
            return $fallback;
        }

        $tmp = $_FILES['hero_upload']['tmp_name'];
        $mime = mime_content_type($tmp) ?: '';
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];
        if (!isset($extensions[$mime])) {
            return $fallback;
        }

        return $this->moveUploadedImage($tmp, $extensions[$mime], 'events') ?: $fallback;
    }

    private function syncRelations(int $eventId): void
    {
        EventClub::replaceForEvent($eventId, $this->clubsData($_POST['clubs'] ?? []));
        EventVideo::replaceForEvent($eventId, $_POST['videos'] ?? []);
        Partner::replaceForEvent($eventId, $this->partnersData($_POST['partners'] ?? []));
    }

    private function newsData(?int $id = null): array
    {
        $current = $id ? NewsArticle::find($id) : null;
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: slugify($title);
        $excerpt = trim($_POST['excerpt'] ?? '');
        if (strlen($excerpt) > 260) {
            $excerpt = substr($excerpt, 0, 260);
        }

        $publishedAt = trim($_POST['published_at'] ?? '');
        if ($publishedAt !== '') {
            $publishedAt = str_replace('T', ' ', $publishedAt) . ':00';
        }

        return [
            'status' => in_array($_POST['status'] ?? 'draft', ['draft', 'published', 'archived'], true) ? $_POST['status'] : 'draft',
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => trim($_POST['content'] ?? ''),
            'image_url' => $this->uploadedNewsImage($current['image_url'] ?? trim($_POST['image_url'] ?? '')),
            'seo_title' => trim($_POST['seo_title'] ?? ''),
            'seo_description' => trim($_POST['seo_description'] ?? ''),
            'published_at' => $publishedAt ?: null,
        ];
    }

    private function uploadedNewsImage(string $fallback = ''): string
    {
        if (empty($_FILES['news_image_upload']) || ($_FILES['news_image_upload']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $fallback;
        }

        if ($_FILES['news_image_upload']['error'] !== UPLOAD_ERR_OK || $_FILES['news_image_upload']['size'] > 5 * 1024 * 1024) {
            return $fallback;
        }

        $tmp = $_FILES['news_image_upload']['tmp_name'];
        $mime = mime_content_type($tmp) ?: '';
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];
        if (!isset($extensions[$mime])) {
            return $fallback;
        }

        return $this->moveUploadedImage($tmp, $extensions[$mime], 'news') ?: $fallback;
    }

    private function clubsData(array $clubs): array
    {
        foreach ($clubs as $index => $club) {
            $image = trim($club['image_url'] ?? '');
            if (!empty($club['image_library'])) {
                $image = trim($club['image_library']);
            }
            $uploaded = $this->uploadedRelationImage('club_uploads', (int) $index, 'clubs');
            if ($uploaded) {
                $image = $uploaded;
            }
            $clubs[$index]['image_url'] = $image;
        }
        return $clubs;
    }

    private function partnersData(array $partners): array
    {
        foreach ($partners as $index => $partner) {
            $logo = trim($partner['logo_url'] ?? '');
            if (!empty($partner['logo_library'])) {
                $logo = trim($partner['logo_library']);
            }
            $uploaded = $this->uploadedRelationImage('partner_logo_uploads', (int) $index, 'partners');
            if ($uploaded) {
                $logo = $uploaded;
            }
            $partners[$index]['logo_url'] = $logo;
        }
        return $partners;
    }

    private function uploadedRelationImage(string $field, int $index, string $folder): string
    {
        if (empty($_FILES[$field]['tmp_name'][$index]) || ($_FILES[$field]['error'][$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return '';
        }

        if ($_FILES[$field]['error'][$index] !== UPLOAD_ERR_OK || $_FILES[$field]['size'][$index] > 5 * 1024 * 1024) {
            return '';
        }

        $tmp = $_FILES[$field]['tmp_name'][$index];
        $mime = mime_content_type($tmp) ?: '';
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];
        if (!isset($extensions[$mime])) {
            return '';
        }

        return $this->moveUploadedImage($tmp, $extensions[$mime], $folder);
    }

    private function moveUploadedImage(string $tmp, string $extension, string $folder): string
    {
        $dir = __DIR__ . '/../../public/uploads/' . $folder;
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $filename = date('YmdHis') . '-' . bin2hex(random_bytes(5)) . '.' . $extension;
        if (!move_uploaded_file($tmp, $dir . '/' . $filename)) {
            return '';
        }

        return '/uploads/' . $folder . '/' . $filename;
    }

    private function imageLibrary(string $folder): array
    {
        $dir = __DIR__ . '/../../public/uploads/' . $folder;
        if (!is_dir($dir)) {
            return [];
        }

        $files = glob($dir . '/*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE) ?: [];
        rsort($files);
        return array_map(fn(string $file): string => '/uploads/' . $folder . '/' . basename($file), $files);
    }

    private function unsubscribeUrl(?string $token): string
    {
        if (!$token) {
            return '';
        }

        $baseUrl = rtrim(getenv('APP_URL') ?: $this->requestBaseUrl(), '/');
        return $baseUrl . '/rezygnacja/' . rawurlencode($token);
    }

    private function requestBaseUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8000';
        return $scheme . '://' . $host;
    }
}
