<?php

namespace App\Models;

use App\Core\Database;

final class EventVideo
{
    public const DEFAULT_VIDEOS = [
        [
            'title' => 'Film z Inauguracji',
            'youtube_url' => 'https://youtu.be/b2s3jDwBkBQ',
        ],
        [
            'title' => 'Film z Inauguracji',
            'youtube_url' => 'https://youtu.be/WMhhTQeCOrE',
        ],
        [
            'title' => 'Film z Inauguracji',
            'youtube_url' => 'https://youtu.be/q1pEemPqzBA',
        ],
    ];

    public static function forEvent(int $eventId): array
    {
        $stmt = Database::pdo()->prepare("SELECT * FROM event_videos WHERE event_id = ? ORDER BY sort_order, id");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }

    public static function replaceForEvent(int $eventId, array $videos): void
    {
        Database::pdo()->prepare("DELETE FROM event_videos WHERE event_id = ?")->execute([$eventId]);
        $stmt = Database::pdo()->prepare("INSERT INTO event_videos (event_id,youtube_url,title,sort_order) VALUES (?,?,?,?)");
        foreach ($videos as $index => $video) {
            if (trim($video['youtube_url'] ?? '') === '') {
                continue;
            }
            $stmt->execute([$eventId, trim($video['youtube_url']), trim($video['title'] ?? ''), $index]);
        }
    }
}
