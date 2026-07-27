<?php

namespace App\Models;

use App\Core\Database;

final class EventClub
{
    public static function forEvent(int $eventId): array
    {
        $stmt = Database::pdo()->prepare("SELECT * FROM event_clubs WHERE event_id = ? ORDER BY sort_order, id");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }

    public static function replaceForEvent(int $eventId, array $clubs): void
    {
        Database::pdo()->prepare("DELETE FROM event_clubs WHERE event_id = ?")->execute([$eventId]);
        $stmt = Database::pdo()->prepare("INSERT INTO event_clubs (event_id,name,address,description,map_url,image_url,sort_order) VALUES (?,?,?,?,?,?,?)");
        foreach ($clubs as $index => $club) {
            if (trim($club['name'] ?? '') === '') {
                continue;
            }
            $stmt->execute([
                $eventId,
                trim($club['name']),
                trim($club['address'] ?? ''),
                trim($club['description'] ?? ''),
                trim($club['map_url'] ?? ''),
                trim($club['image_url'] ?? ''),
                $index,
            ]);
        }
    }
}
