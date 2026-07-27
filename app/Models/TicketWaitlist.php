<?php

namespace App\Models;

use App\Core\Database;
use PDO;

final class TicketWaitlist
{
    public static function createOrUpdate(array $data): void
    {
        self::ensureTable();
        $pdo = Database::pdo();
        $eventId = (int) $data['event_id'];
        $email = trim((string) ($data['email'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $existing = null;

        if ($email !== '') {
            $stmt = $pdo->prepare("SELECT * FROM ticket_waitlist WHERE event_id = ? AND email = ? ORDER BY id ASC LIMIT 1");
            $stmt->execute([$eventId, $email]);
            $existing = $stmt->fetch() ?: null;
        }

        if (!$existing && $phone !== '') {
            $stmt = $pdo->prepare("SELECT * FROM ticket_waitlist WHERE event_id = ? AND phone = ? ORDER BY id ASC LIMIT 1");
            $stmt->execute([$eventId, $phone]);
            $existing = $stmt->fetch() ?: null;
        }

        if ($existing) {
            $sql = "UPDATE ticket_waitlist
                    SET marketing_contact_id = ?, name = ?, email = ?, phone = ?, city = ?,
                        email_consent = ?, sms_consent = ?, ip_address = ?
                    WHERE id = ?";
            $pdo->prepare($sql)->execute([
                $data['marketing_contact_id'] ?? ($existing['marketing_contact_id'] ?? null),
                trim((string) ($data['name'] ?? '')) ?: ($existing['name'] ?? null),
                $email ?: ($existing['email'] ?? ''),
                $phone ?: ($existing['phone'] ?? null),
                trim((string) ($data['city'] ?? '')) ?: ($existing['city'] ?? null),
                (!empty($existing['email_consent']) || !empty($data['email_consent'])) ? 1 : 0,
                (!empty($existing['sms_consent']) || !empty($data['sms_consent'])) ? 1 : 0,
                $_SERVER['REMOTE_ADDR'] ?? ($existing['ip_address'] ?? null),
                (int) $existing['id'],
            ]);
            return;
        }

        $sql = "INSERT INTO ticket_waitlist
                (event_id,marketing_contact_id,name,email,phone,city,email_consent,sms_consent,ip_address)
                VALUES (?,?,?,?,?,?,?,?,?)";
        $pdo->prepare($sql)->execute([
            $eventId,
            $data['marketing_contact_id'] ?? null,
            $data['name'] ?? null,
            $email,
            $phone ?: null,
            $data['city'] ?? null,
            !empty($data['email_consent']) ? 1 : 0,
            !empty($data['sms_consent']) ? 1 : 0,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    public static function forEvent(int $eventId): array
    {
        self::ensureTable();
        $stmt = Database::pdo()->prepare("SELECT * FROM ticket_waitlist WHERE event_id = ? ORDER BY created_at DESC");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }

    public static function all(?int $eventId = null, ?string $status = null): array
    {
        self::ensureTable();
        $sql = "SELECT tw.*, e.title AS event_title, e.slug AS event_slug, e.ticket_url, e.starts_at
                FROM ticket_waitlist tw
                INNER JOIN events e ON e.id = tw.event_id
                WHERE 1=1";
        $params = [];

        if ($eventId) {
            $sql .= " AND tw.event_id = ?";
            $params[] = $eventId;
        }

        if ($status === 'pending') {
            $sql .= " AND tw.notified_at IS NULL";
        } elseif ($status === 'notified') {
            $sql .= " AND tw.notified_at IS NOT NULL";
        }

        $sql .= " ORDER BY tw.created_at DESC";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function eventsWithWaitlist(): array
    {
        self::ensureTable();
        return Database::pdo()->query(
            "SELECT e.id, e.title, e.city, e.starts_at, e.ticket_url,
                    COUNT(tw.id) AS waitlist_count,
                    SUM(CASE WHEN tw.notified_at IS NULL THEN 1 ELSE 0 END) AS pending_count,
                    SUM(CASE WHEN tw.notified_at IS NOT NULL THEN 1 ELSE 0 END) AS notified_count
             FROM ticket_waitlist tw
             INNER JOIN events e ON e.id = tw.event_id
             GROUP BY e.id, e.title, e.city, e.starts_at, e.ticket_url
             ORDER BY e.starts_at ASC"
        )->fetchAll();
    }

    public static function pendingEmailRecipientsForEvent(int $eventId): array
    {
        self::ensureTable();
        $stmt = Database::pdo()->prepare(
            "SELECT tw.*, mc.unsubscribe_token
             FROM ticket_waitlist tw
             LEFT JOIN marketing_contacts mc ON mc.id = tw.marketing_contact_id
             WHERE tw.event_id = ? AND tw.notified_at IS NULL AND tw.email_consent = 1 AND tw.email <> ''
             ORDER BY tw.created_at ASC"
        );
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }

    public static function markNotified(array $ids): void
    {
        self::ensureTable();
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        Database::pdo()->prepare("UPDATE ticket_waitlist SET notified_at = NOW() WHERE id IN ({$placeholders})")->execute($ids);
    }

    public static function ensureTable(): void
    {
        Database::pdo()->exec("
            CREATE TABLE IF NOT EXISTS ticket_waitlist (
              id INT AUTO_INCREMENT PRIMARY KEY,
              event_id INT NOT NULL,
              marketing_contact_id INT NULL,
              name VARCHAR(120) NULL,
              email VARCHAR(190) NOT NULL DEFAULT '',
              phone VARCHAR(60) NULL,
              city VARCHAR(120) NULL,
              email_consent TINYINT(1) NOT NULL DEFAULT 0,
              sms_consent TINYINT(1) NOT NULL DEFAULT 0,
              notified_at DATETIME NULL,
              ip_address VARCHAR(80) NULL,
              created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              INDEX idx_ticket_waitlist_event (event_id),
              INDEX idx_ticket_waitlist_email (email),
              INDEX idx_ticket_waitlist_phone (phone),
              FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
              FOREIGN KEY (marketing_contact_id) REFERENCES marketing_contacts(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
}
