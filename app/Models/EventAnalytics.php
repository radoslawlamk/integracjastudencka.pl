<?php

namespace App\Models;

use App\Core\Database;
use PDO;

final class EventAnalytics
{
    private const CLICK_ACTIONS = [
        'ticket' => 'Kup bilet',
        'details' => 'Sprawdź szczegóły',
        'facebook' => 'Dołącz na FB',
        'ticket_notify' => 'Powiadom mnie o biletach',
        'fanpage' => 'Polub nas',
    ];

    public static function trackView(int $eventId): void
    {
        if ($eventId <= 0) {
            return;
        }

        $sessionKey = 'event_view_tracked_' . $eventId;
        $now = time();
        if (!empty($_SESSION[$sessionKey]) && ($now - (int) $_SESSION[$sessionKey]) < 1800) {
            return;
        }

        $_SESSION[$sessionKey] = $now;
        self::record($eventId, 'view', 'event_page');
    }

    public static function trackClick(int $eventId, string $action, ?string $targetUrl = null): void
    {
        if ($eventId <= 0 || !isset(self::CLICK_ACTIONS[$action])) {
            return;
        }

        self::record($eventId, 'click', $action, $targetUrl);
    }

    public static function actionLabels(): array
    {
        return self::CLICK_ACTIONS;
    }

    public static function dashboard(string $sort = 'total_clicks', string $range = 'all', string $eventStatus = 'all', string $season = 'current'): array
    {
        self::ensureTable();
        $allowedSorts = [
            'total_clicks' => 'total_clicks',
            'views' => 'views',
            'ticket_clicks' => 'ticket_clicks',
            'details_clicks' => 'details_clicks',
            'facebook_clicks' => 'facebook_clicks',
            'ticket_notify_clicks' => 'ticket_notify_clicks',
            'ticket_conversion' => 'ticket_conversion',
        ];
        $orderBy = $allowedSorts[$sort] ?? 'total_clicks';
        [$dateSql, $params] = self::dateFilter($range, 'ea.created_at', $season);
        $joinFilter = $dateSql ? ' AND ' . $dateSql : '';
        $eventWhereSql = self::eventStatusFilter($eventStatus, 'e');
        $eventWhereSql = $eventWhereSql ? ' WHERE ' . $eventWhereSql : '';

        $stmt = Database::pdo()->prepare(
            "SELECT
                e.id,
                e.title,
                e.city,
                e.slug,
                COUNT(CASE WHEN ea.kind = 'view' THEN 1 END) AS views,
                COUNT(CASE WHEN ea.kind = 'click' THEN 1 END) AS total_clicks,
                COUNT(CASE WHEN ea.kind = 'click' AND ea.action = 'ticket' THEN 1 END) AS ticket_clicks,
                COUNT(CASE WHEN ea.kind = 'click' AND ea.action = 'details' THEN 1 END) AS details_clicks,
                COUNT(CASE WHEN ea.kind = 'click' AND ea.action = 'facebook' THEN 1 END) AS facebook_clicks,
                COUNT(CASE WHEN ea.kind = 'click' AND ea.action = 'ticket_notify' THEN 1 END) AS ticket_notify_clicks,
                ROUND(
                    COUNT(CASE WHEN ea.kind = 'click' AND ea.action = 'ticket' THEN 1 END)
                    / NULLIF(COUNT(CASE WHEN ea.kind = 'view' THEN 1 END), 0) * 100,
                    1
                ) AS ticket_conversion,
                MAX(ea.created_at) AS last_activity
             FROM events e
             LEFT JOIN event_analytics ea ON ea.event_id = e.id{$joinFilter}
             {$eventWhereSql}
             GROUP BY e.id
             ORDER BY {$orderBy} DESC, e.starts_at ASC
             LIMIT 100"
        );
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function totals(string $range = 'all', string $eventStatus = 'all', string $season = 'current'): array
    {
        self::ensureTable();
        [$dateSql, $params] = self::dateFilter($range, 'ea.created_at', $season);
        $whereParts = array_filter([
            $dateSql,
            self::eventStatusFilter($eventStatus, 'e'),
        ]);
        $whereSql = $whereParts ? ' WHERE ' . implode(' AND ', $whereParts) : '';
        $stmt = Database::pdo()->prepare(
            "SELECT
                COUNT(CASE WHEN ea.kind = 'view' THEN 1 END) AS views,
                COUNT(CASE WHEN ea.kind = 'click' THEN 1 END) AS clicks,
                COUNT(CASE WHEN ea.kind = 'click' AND ea.action = 'ticket' THEN 1 END) AS ticket_clicks,
                COUNT(CASE WHEN ea.kind = 'click' AND ea.action = 'details' THEN 1 END) AS details_clicks,
                COUNT(CASE WHEN ea.kind = 'click' AND ea.action = 'facebook' THEN 1 END) AS facebook_clicks,
                COUNT(CASE WHEN ea.kind = 'click' AND ea.action = 'ticket_notify' THEN 1 END) AS ticket_notify_clicks
             FROM event_analytics ea
             INNER JOIN events e ON e.id = ea.event_id
             {$whereSql}"
        );
        $stmt->execute($params);
        $row = $stmt->fetch();

        return $row ?: [
            'views' => 0,
            'clicks' => 0,
            'ticket_clicks' => 0,
            'details_clicks' => 0,
            'facebook_clicks' => 0,
            'ticket_notify_clicks' => 0,
        ];
    }

    public static function sources(string $range = 'all', string $eventStatus = 'all', string $season = 'current'): array
    {
        self::ensureTable();
        [$dateSql, $params] = self::dateFilter($range, 'ea.created_at', $season);
        $whereSql = "ea.kind = 'view'";
        if ($dateSql) {
            $whereSql .= ' AND ' . $dateSql;
        }
        $eventWhereSql = self::eventStatusFilter($eventStatus, 'e');
        if ($eventWhereSql) {
            $whereSql .= ' AND ' . $eventWhereSql;
        }
        $stmt = Database::pdo()->prepare(
            "SELECT
                e.title,
                e.city,
                e.slug,
                ea.source,
                COUNT(*) AS visits
             FROM event_analytics ea
             INNER JOIN events e ON e.id = ea.event_id
             WHERE {$whereSql}
             GROUP BY e.id, ea.source
             ORDER BY visits DESC, e.title ASC
             LIMIT 200"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function sourceTotals(string $range = 'all', string $eventStatus = 'all', string $season = 'current'): array
    {
        self::ensureTable();
        [$dateSql, $params] = self::dateFilter($range, 'ea.created_at', $season);
        $whereSql = "ea.kind = 'view'";
        if ($dateSql) {
            $whereSql .= ' AND ' . $dateSql;
        }
        $eventWhereSql = self::eventStatusFilter($eventStatus, 'e');
        if ($eventWhereSql) {
            $whereSql .= ' AND ' . $eventWhereSql;
        }

        $stmt = Database::pdo()->prepare(
            "SELECT ea.source, COUNT(*) AS visits
             FROM event_analytics ea
             INNER JOIN events e ON e.id = ea.event_id
             WHERE {$whereSql}
             GROUP BY ea.source
             ORDER BY visits DESC, ea.source ASC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function cityRanking(string $range = 'all', string $eventStatus = 'all', string $season = 'current'): array
    {
        self::ensureTable();
        [$dateSql, $params] = self::dateFilter($range, 'ea.created_at', $season);
        $joinFilter = $dateSql ? ' AND ' . $dateSql : '';
        $eventWhereSql = self::eventStatusFilter($eventStatus, 'e');
        $eventWhereSql = $eventWhereSql ? ' WHERE ' . $eventWhereSql : '';

        $stmt = Database::pdo()->prepare(
            "SELECT
                e.city,
                COUNT(CASE WHEN ea.kind = 'view' THEN 1 END) AS views,
                COUNT(CASE WHEN ea.kind = 'click' THEN 1 END) AS clicks,
                COUNT(CASE WHEN ea.kind = 'click' AND ea.action = 'ticket' THEN 1 END) AS ticket_clicks,
                COUNT(CASE WHEN ea.kind = 'click' AND ea.action = 'details' THEN 1 END) AS details_clicks,
                COUNT(CASE WHEN ea.kind = 'click' AND ea.action = 'ticket_notify' THEN 1 END) AS ticket_notify_clicks,
                ROUND(
                    COUNT(CASE WHEN ea.kind = 'click' AND ea.action = 'ticket' THEN 1 END)
                    / NULLIF(COUNT(CASE WHEN ea.kind = 'view' THEN 1 END), 0) * 100,
                    1
                ) AS ticket_conversion
             FROM events e
             LEFT JOIN event_analytics ea ON ea.event_id = e.id{$joinFilter}
             {$eventWhereSql}
             GROUP BY e.city
             ORDER BY ticket_clicks DESC, views DESC, e.city ASC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function resetSeason(): void
    {
        self::ensureSeasonsTable();
        $now = date('Y-m-d H:i:s');
        $currentStart = self::currentSeasonStart();
        if ($currentStart === '') {
            $currentStart = self::firstAnalyticsDate() ?: $now;
        }

        Database::pdo()->prepare(
            "INSERT INTO analytics_seasons (name, starts_at, ends_at) VALUES (?, ?, ?)"
        )->execute([
            self::seasonName($currentStart, $now),
            $currentStart,
            $now,
        ]);

        AppSetting::set('analytics_started_at', $now);
    }

    public static function currentSeasonStart(): string
    {
        return AppSetting::get('analytics_started_at');
    }

    public static function seasons(): array
    {
        self::ensureSeasonsTable();
        return Database::pdo()
            ->query("SELECT * FROM analytics_seasons ORDER BY starts_at DESC")
            ->fetchAll();
    }

    public static function activeDateSql(string $eventAlias): string
    {
        return "COALESCE({$eventAlias}.ends_at, DATE_ADD({$eventAlias}.starts_at, INTERVAL 6 HOUR)) >= NOW()";
    }

    public static function finishedDateSql(string $eventAlias): string
    {
        return "COALESCE({$eventAlias}.ends_at, DATE_ADD({$eventAlias}.starts_at, INTERVAL 6 HOUR)) < NOW()";
    }

    private static function record(int $eventId, string $kind, string $action, ?string $targetUrl = null): void
    {
        self::ensureTable();

        $stmt = Database::pdo()->prepare(
            "INSERT INTO event_analytics (event_id, kind, action, source, target_url, page_url, referrer, ip_address, user_agent)
             VALUES (?,?,?,?,?,?,?,?,?)"
        );
        $stmt->execute([
            $eventId,
            $kind,
            $action,
            self::detectSource($_SERVER['HTTP_REFERER'] ?? '', $_SERVER['REQUEST_URI'] ?? ''),
            $targetUrl ?: null,
            $_SERVER['REQUEST_URI'] ?? null,
            $_SERVER['HTTP_REFERER'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
        ]);
    }

    private static function detectSource(string $referrer, string $pageUrl = ''): string
    {
        $query = [];
        parse_str((string) (parse_url($pageUrl, PHP_URL_QUERY) ?: ''), $query);
        $utmSource = strtolower((string) ($query['utm_source'] ?? ''));
        if ($utmSource !== '') {
            if (str_contains($utmSource, 'facebook') || $utmSource === 'fb') {
                return 'Facebook';
            }
            if (str_contains($utmSource, 'instagram') || $utmSource === 'ig') {
                return 'Instagram';
            }
            if (str_contains($utmSource, 'google')) {
                return 'Google';
            }
            if (str_contains($utmSource, 'tiktok')) {
                return 'TikTok';
            }
            if (str_contains($utmSource, 'youtube')) {
                return 'YouTube';
            }
            return $utmSource;
        }
        if (!empty($query['fbclid'])) {
            return 'Facebook';
        }
        if (!empty($query['gclid'])) {
            return 'Google';
        }
        if (!empty($query['igshid'])) {
            return 'Instagram';
        }

        $host = strtolower((string) (parse_url($referrer, PHP_URL_HOST) ?: ''));
        if ($host === '') {
            return 'bezpośrednie';
        }
        if (str_contains($host, 'facebook.com') || str_contains($host, 'fb.com') || str_contains($host, 'm.me')) {
            return 'Facebook';
        }
        if (str_contains($host, 'instagram.com')) {
            return 'Instagram';
        }
        if (str_contains($host, 'google.')) {
            return 'Google';
        }
        if (str_contains($host, 'tiktok.com')) {
            return 'TikTok';
        }
        if (str_contains($host, 'youtube.com') || str_contains($host, 'youtu.be')) {
            return 'YouTube';
        }
        if (str_contains($host, 'integracjastudencka.pl') || str_contains($host, '127.0.0.1') || str_contains($host, 'localhost')) {
            return 'wewnętrzne';
        }

        return 'inne';
    }

    private static function dateFilter(string $range, string $column, string $season = 'current'): array
    {
        [$rangeSql, $params] = match ($range) {
            'today' => ["DATE({$column}) = CURDATE()", []],
            'yesterday' => ["DATE({$column}) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)", []],
            '7d' => ["{$column} >= DATE_SUB(NOW(), INTERVAL 7 DAY)", []],
            '30d' => ["{$column} >= DATE_SUB(NOW(), INTERVAL 30 DAY)", []],
            default => ['', []],
        };

        $parts = [];
        if ($rangeSql !== '') {
            $parts[] = $rangeSql;
        }

        if ($season === 'current') {
            $seasonStart = self::currentSeasonStart();
            if ($seasonStart !== '') {
                $parts[] = "{$column} >= ?";
                $params[] = $seasonStart;
            }
        } elseif (ctype_digit($season)) {
            $archivedSeason = self::season((int) $season);
            if ($archivedSeason) {
                $parts[] = "{$column} >= ?";
                $params[] = $archivedSeason['starts_at'];
                $parts[] = "{$column} < ?";
                $params[] = $archivedSeason['ends_at'];
            }
        }

        return [implode(' AND ', $parts), $params];
    }

    private static function eventStatusFilter(string $eventStatus, string $eventAlias): string
    {
        return match ($eventStatus) {
            'upcoming' => self::activeDateSql($eventAlias),
            'finished' => self::finishedDateSql($eventAlias),
            default => '',
        };
    }

    private static function ensureTable(): void
    {
        Database::pdo()->exec(
            "CREATE TABLE IF NOT EXISTS event_analytics (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_id INT NOT NULL,
                kind ENUM('view','click') NOT NULL,
                action VARCHAR(80) NOT NULL,
                source VARCHAR(80) NOT NULL DEFAULT 'bezpośrednie',
                target_url VARCHAR(500) NULL,
                page_url VARCHAR(500) NULL,
                referrer VARCHAR(500) NULL,
                ip_address VARCHAR(80) NULL,
                user_agent VARCHAR(500) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_event_analytics_event (event_id),
                INDEX idx_event_analytics_kind_action (kind, action),
                INDEX idx_event_analytics_source (source),
                INDEX idx_event_analytics_created (created_at),
                FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    private static function ensureSeasonsTable(): void
    {
        Database::pdo()->exec(
            "CREATE TABLE IF NOT EXISTS analytics_seasons (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(190) NOT NULL,
                starts_at DATETIME NOT NULL,
                ends_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_analytics_seasons_dates (starts_at, ends_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    private static function season(int $id): ?array
    {
        self::ensureSeasonsTable();
        $stmt = Database::pdo()->prepare("SELECT * FROM analytics_seasons WHERE id = ?");
        $stmt->execute([$id]);
        $season = $stmt->fetch();
        return $season ?: null;
    }

    private static function firstAnalyticsDate(): string
    {
        self::ensureTable();
        return (string) (Database::pdo()->query("SELECT MIN(created_at) FROM event_analytics")->fetchColumn() ?: '');
    }

    private static function seasonName(string $startsAt, string $endsAt): string
    {
        $start = strtotime($startsAt) ?: time();
        $end = strtotime($endsAt) ?: time();

        return 'Sezon statystyk: '
            . self::monthName((int) date('n', $start)) . ' ' . date('Y', $start)
            . ' - '
            . self::monthName((int) date('n', $end)) . ' ' . date('Y', $end)
            . ' (' . date('d.m.Y', $start) . ' - ' . date('d.m.Y', $end) . ')';
    }

    private static function monthName(int $month): string
    {
        return [
            1 => 'styczen',
            2 => 'luty',
            3 => 'marzec',
            4 => 'kwiecien',
            5 => 'maj',
            6 => 'czerwiec',
            7 => 'lipiec',
            8 => 'sierpien',
            9 => 'wrzesien',
            10 => 'pazdziernik',
            11 => 'listopad',
            12 => 'grudzien',
        ][$month] ?? '';
    }
}
