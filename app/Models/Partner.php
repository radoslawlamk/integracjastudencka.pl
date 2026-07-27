<?php

namespace App\Models;

use App\Core\Database;

final class Partner
{
    public static function all(): array
    {
        return Database::pdo()->query("SELECT * FROM partners ORDER BY name")->fetchAll();
    }

    public static function forEvent(int $eventId): array
    {
        $stmt = Database::pdo()->prepare("SELECT p.* FROM partners p JOIN event_partners ep ON ep.partner_id = p.id WHERE ep.event_id = ? ORDER BY ep.sort_order, p.name");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }

    public static function replaceForEvent(int $eventId, array $partners): void
    {
        Database::pdo()->prepare("DELETE FROM event_partners WHERE event_id = ?")->execute([$eventId]);
        $insertPartner = Database::pdo()->prepare("INSERT INTO partners (name,logo_url,website_url,category) VALUES (?,?,?,?)");
        $link = Database::pdo()->prepare("INSERT IGNORE INTO event_partners (event_id,partner_id,sort_order) VALUES (?,?,?)");
        foreach ($partners as $index => $partner) {
            if (trim($partner['name'] ?? '') === '') {
                continue;
            }
            $insertPartner->execute([
                trim($partner['name']),
                trim($partner['logo_url'] ?? ''),
                trim($partner['website_url'] ?? ''),
                trim($partner['category'] ?? ''),
            ]);
            $link->execute([$eventId, (int) Database::pdo()->lastInsertId(), $index]);
        }
    }
}
