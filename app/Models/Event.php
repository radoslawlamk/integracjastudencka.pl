<?php

namespace App\Models;

use App\Core\Database;
use PDO;

final class Event
{
    private static function cityTokens(string $city): array
    {
        $tokens = array_map('trim', explode(',', $city));
        $tokens = array_filter($tokens, static fn (string $token): bool => $token !== '');
        return array_values(array_unique($tokens));
    }

    private static function cityFilterSql(string $city, array &$params): string
    {
        $params[] = $city;
        return "CONCAT(',', REPLACE(city, ', ', ','), ',') LIKE CONCAT('%,', ?, ',%')";
    }

    private static function cityListFromRows(array $rows): array
    {
        $cities = [];
        foreach ($rows as $row) {
            foreach (self::cityTokens((string) $row) as $city) {
                $cities[$city] = $city;
            }
        }
        natcasesort($cities);
        return array_values($cities);
    }

    private static function activeDateSql(): string
    {
        return "COALESCE(ends_at, DATE_ADD(starts_at, INTERVAL 6 HOUR)) >= NOW()";
    }

    private static function finishedDateSql(): string
    {
        return "COALESCE(ends_at, DATE_ADD(starts_at, INTERVAL 6 HOUR)) < NOW()";
    }

    public static function published(?string $city = null, ?string $month = null, bool $includeFinished = false): array
    {
        $sql = "SELECT * FROM events WHERE status = 'published'";
        $params = [];
        if (!$includeFinished) {
            $sql .= " AND " . self::activeDateSql();
        }
        if ($city) {
            $sql .= " AND " . self::cityFilterSql($city, $params);
        }
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            $sql .= " AND DATE_FORMAT(starts_at, '%Y-%m') = ?";
            $params[] = $month;
        }
        $sql .= " ORDER BY starts_at ASC";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function all(string $sort = 'starts_desc', ?string $city = null, string $showOnly = 'all'): array
    {
        $orders = [
            'starts_asc' => 'starts_at ASC',
            'starts_desc' => 'starts_at DESC',
            'ends_desc' => 'ends_at DESC',
            'title_asc' => 'title ASC',
            'city_asc' => 'city ASC, starts_at ASC',
            'type_asc' => 'type ASC, starts_at ASC',
            'status_asc' => 'status ASC, starts_at ASC',
            'sales_status_asc' => 'sales_status ASC, starts_at ASC',
        ];
        $orderBy = $orders[$sort] ?? $orders['starts_desc'];
        $sql = "SELECT * FROM events WHERE 1=1";
        $params = [];

        if ($city) {
            $sql .= " AND " . self::cityFilterSql($city, $params);
        }

        if ($showOnly === 'missing_ticket') {
            $sql .= " AND (ticket_url IS NULL OR ticket_url = '')";
        } elseif ($showOnly === 'missing_fb') {
            $sql .= " AND (facebook_event_url IS NULL OR facebook_event_url = '')";
        } elseif ($showOnly === 'missing_any') {
            $sql .= " AND ((ticket_url IS NULL OR ticket_url = '') OR (facebook_event_url IS NULL OR facebook_event_url = ''))";
        } elseif ($showOnly === 'upcoming') {
            $sql .= " AND " . self::activeDateSql();
        } elseif ($showOnly === 'finished') {
            $sql .= " AND " . self::finishedDateSql();
        } elseif ($showOnly === 'published') {
            $sql .= " AND status = 'published'";
        } elseif ($showOnly === 'draft') {
            $sql .= " AND status = 'draft'";
        } elseif (in_array($showOnly, ['tickets_soon', 'on_sale', 'sold_out'], true)) {
            $sql .= " AND sales_status = ?";
            $params[] = $showOnly;
        }

        $sql .= " ORDER BY {$orderBy}";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function upcomingLimit(int $limit = 5): array
    {
        $stmt = Database::pdo()->prepare("SELECT * FROM events WHERE " . self::activeDateSql() . " ORDER BY starts_at ASC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function relatedByCity(string $city, int $excludeId, int $limit = 6): array
    {
        $params = [];
        $cityConditions = [];
        foreach (self::cityTokens($city) as $cityToken) {
            $cityConditions[] = self::cityFilterSql($cityToken, $params);
        }
        if (!$cityConditions) {
            $cityConditions[] = "city = ?";
            $params[] = $city;
        }

        $sql = "SELECT * FROM events WHERE status = 'published' AND (" . implode(' OR ', $cityConditions) . ") AND id <> ? AND " . self::activeDateSql() . " ORDER BY starts_at ASC LIMIT ?";
        $stmt = Database::pdo()->prepare($sql);
        $index = 1;
        foreach ($params as $value) {
            $stmt->bindValue($index++, $value);
        }
        $stmt->bindValue($index++, $excludeId, PDO::PARAM_INT);
        $stmt->bindValue($index, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function publishedByKeyword(string $keyword, int $limit = 12): array
    {
        $like = '%' . $keyword . '%';
        $stmt = Database::pdo()->prepare("SELECT * FROM events WHERE status = 'published' AND " . self::activeDateSql() . " AND (title LIKE ? OR short_description LIKE ? OR description LIKE ?) ORDER BY starts_at ASC LIMIT ?");
        $stmt->bindValue(1, $like);
        $stmt->bindValue(2, $like);
        $stmt->bindValue(3, $like);
        $stmt->bindValue(4, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function finishedPublished(int $limit = 30): array
    {
        $stmt = Database::pdo()->prepare("SELECT * FROM events WHERE status = 'published' AND " . self::finishedDateSql() . " ORDER BY starts_at DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);
        $event = $stmt->fetch();
        return $event ?: null;
    }

    public static function findPublishedBySlug(string $slug): ?array
    {
        $stmt = Database::pdo()->prepare("SELECT * FROM events WHERE slug = ? AND status = 'published'");
        $stmt->execute([$slug]);
        $event = $stmt->fetch();
        return $event ?: null;
    }

    public static function create(array $data): int
    {
        $sql = "INSERT INTO events (type,status,sales_status,title,slug,city,region,starts_at,ends_at,venue_name,venue_address,short_description,description,ticket_url,facebook_event_url,fanpage_url,hero_image,seo_title,seo_description)
                VALUES (:type,:status,:sales_status,:title,:slug,:city,:region,:starts_at,:ends_at,:venue_name,:venue_address,:short_description,:description,:ticket_url,:facebook_event_url,:fanpage_url,:hero_image,:seo_title,:seo_description)";
        Database::pdo()->prepare($sql)->execute($data);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $sql = "UPDATE events SET type=:type,status=:status,sales_status=:sales_status,title=:title,slug=:slug,city=:city,region=:region,starts_at=:starts_at,ends_at=:ends_at,venue_name=:venue_name,venue_address=:venue_address,short_description=:short_description,description=:description,ticket_url=:ticket_url,facebook_event_url=:facebook_event_url,fanpage_url=:fanpage_url,hero_image=:hero_image,seo_title=:seo_title,seo_description=:seo_description WHERE id=:id";
        Database::pdo()->prepare($sql)->execute($data);
    }

    public static function delete(int $id): void
    {
        Database::pdo()->prepare("DELETE FROM events WHERE id = ?")->execute([$id]);
    }

    public static function cities(): array
    {
        $rows = Database::pdo()->query("SELECT city FROM events WHERE status = 'published' AND " . self::activeDateSql() . " ORDER BY city")->fetchAll(PDO::FETCH_COLUMN);
        return self::cityListFromRows($rows);
    }

    public static function allCities(): array
    {
        $rows = Database::pdo()->query("SELECT city FROM events WHERE city IS NOT NULL AND city <> '' ORDER BY city")->fetchAll(PDO::FETCH_COLUMN);
        return self::cityListFromRows($rows);
    }

    public static function months(): array
    {
        return Database::pdo()->query("SELECT DISTINCT DATE_FORMAT(starts_at, '%Y-%m') AS month_value, DATE_FORMAT(starts_at, '%m.%Y') AS month_label FROM events WHERE status = 'published' AND " . self::activeDateSql() . " ORDER BY month_value")->fetchAll();
    }
}
