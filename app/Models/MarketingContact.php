<?php

namespace App\Models;

use App\Core\Database;
use PDO;

final class MarketingContact
{
    public static function create(array $data): void
    {
        self::createOrMerge($data);
    }

    public static function createOrMerge(array $data): int
    {
        self::ensureConsentColumns();
        $pdo = Database::pdo();
        $email = trim((string) ($data['email'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $existing = null;

        if ($email !== '') {
            $stmt = $pdo->prepare("SELECT * FROM marketing_contacts WHERE email = ? ORDER BY id ASC LIMIT 1");
            $stmt->execute([$email]);
            $existing = $stmt->fetch() ?: null;
        }

        if (!$existing && $phone !== '') {
            $stmt = $pdo->prepare("SELECT * FROM marketing_contacts WHERE phone = ? ORDER BY id ASC LIMIT 1");
            $stmt->execute([$phone]);
            $existing = $stmt->fetch() ?: null;
        }

        if ($existing) {
            $sql = "UPDATE marketing_contacts
                    SET name = ?, email = ?, phone = ?, city = ?, source = ?, tags = ?,
                        sms_consent = ?, email_consent = ?, marketing_consent = ?,
                        consent_text = ?, ip_address = ?
                    WHERE id = ?";
            $pdo->prepare($sql)->execute([
                trim((string) ($data['name'] ?? '')) ?: ($existing['name'] ?? null),
                $email ?: ($existing['email'] ?? ''),
                $phone ?: ($existing['phone'] ?? null),
                trim((string) ($data['city'] ?? '')) ?: ($existing['city'] ?? null),
                trim((string) ($data['source'] ?? '')) ?: ($existing['source'] ?? null),
                self::mergeTags($existing['tags'] ?? '', $data['tags'] ?? ''),
                (!empty($existing['sms_consent']) || !empty($data['sms_consent'])) ? 1 : 0,
                (!empty($existing['email_consent']) || !empty($data['email_consent'])) ? 1 : 0,
                (!empty($existing['marketing_consent']) || !empty($data['marketing_consent'])) ? 1 : 0,
                trim((string) ($data['consent_text'] ?? '')) ?: ($existing['consent_text'] ?? null),
                $_SERVER['REMOTE_ADDR'] ?? ($existing['ip_address'] ?? null),
                (int) $existing['id'],
            ]);
            self::ensureToken((int) $existing['id']);
            return (int) $existing['id'];
        }

        $sql = "INSERT INTO marketing_contacts (name,email,phone,city,source,tags,sms_consent,email_consent,marketing_consent,consent_text,ip_address) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
        $pdo->prepare($sql)->execute([
            $data['name'] ?? null,
            $email,
            $phone ?: null,
            $data['city'] ?? null,
            $data['source'] ?? null,
            $data['tags'] ?? null,
            !empty($data['sms_consent']) ? 1 : 0,
            !empty($data['email_consent']) ? 1 : 0,
            !empty($data['marketing_consent']) ? 1 : 0,
            $data['consent_text'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
        $id = (int) $pdo->lastInsertId();
        self::ensureToken($id);
        return $id;
    }

    public static function all(): array
    {
        self::ensureConsentColumns();
        self::ensureMissingTokens();
        return Database::pdo()->query("SELECT * FROM marketing_contacts ORDER BY created_at DESC")->fetchAll();
    }

    public static function filter(?string $city = null, bool $emailOnly = false): array
    {
        self::ensureConsentColumns();
        self::ensureMissingTokens();
        $sql = "SELECT * FROM marketing_contacts WHERE 1=1";
        $params = [];
        if ($city) {
            $sql .= " AND city = ?";
            $params[] = $city;
        }
        if ($emailOnly) {
            $sql .= " AND email_consent = 1 AND email <> ''";
        }
        $sql .= " ORDER BY created_at DESC";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function cities(): array
    {
        self::ensureConsentColumns();
        return Database::pdo()->query("SELECT DISTINCT city FROM marketing_contacts WHERE city IS NOT NULL AND city <> '' ORDER BY city")->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function emailRecipients(array $ids): array
    {
        self::ensureConsentColumns();
        self::ensureMissingTokens();
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::pdo()->prepare("SELECT * FROM marketing_contacts WHERE id IN ({$placeholders}) AND email_consent = 1 AND email <> ''");
        $stmt->execute($ids);
        return $stmt->fetchAll();
    }

    public static function findByUnsubscribeToken(string $token): ?array
    {
        self::ensureConsentColumns();
        if (!preg_match('/^[a-f0-9]{48,64}$/', $token)) {
            return null;
        }

        $stmt = Database::pdo()->prepare("SELECT * FROM marketing_contacts WHERE unsubscribe_token = ? LIMIT 1");
        $stmt->execute([$token]);
        $contact = $stmt->fetch();
        return $contact ?: null;
    }

    public static function findForConsentLookup(string $email, string $phone): ?array
    {
        self::ensureConsentColumns();
        self::ensureMissingTokens();

        $email = trim($email);
        $phone = trim($phone);
        if ($email === '' && $phone === '') {
            return null;
        }

        if ($email !== '') {
            $stmt = Database::pdo()->prepare("SELECT * FROM marketing_contacts WHERE email = ? ORDER BY id ASC LIMIT 1");
            $stmt->execute([$email]);
            $contact = $stmt->fetch();
            if ($contact) {
                return $contact;
            }
        }

        if ($phone !== '') {
            $stmt = Database::pdo()->prepare("SELECT * FROM marketing_contacts WHERE phone = ? ORDER BY id ASC LIMIT 1");
            $stmt->execute([$phone]);
            $contact = $stmt->fetch();
            if ($contact) {
                return $contact;
            }
        }

        return null;
    }

    public static function updateConsentsByToken(string $token, bool $emailConsent, bool $smsConsent): bool
    {
        $contact = self::findByUnsubscribeToken($token);
        if (!$contact) {
            return false;
        }

        if (!$emailConsent && !$smsConsent) {
            Database::pdo()->prepare("DELETE FROM ticket_waitlist WHERE marketing_contact_id = ? OR email = ? OR phone = ?")->execute([
                (int) $contact['id'],
                $contact['email'] ?? '',
                $contact['phone'] ?? '',
            ]);
            Database::pdo()->prepare("DELETE FROM marketing_contacts WHERE id = ?")->execute([(int) $contact['id']]);
            return true;
        }

        Database::pdo()->prepare("UPDATE ticket_waitlist SET email_consent = ?, sms_consent = ? WHERE marketing_contact_id = ?")->execute([
            $emailConsent ? 1 : 0,
            $smsConsent ? 1 : 0,
            (int) $contact['id'],
        ]);

        Database::pdo()->prepare(
            "UPDATE marketing_contacts SET email_consent = ?, sms_consent = ?, marketing_consent = 1 WHERE id = ?"
        )->execute([$emailConsent ? 1 : 0, $smsConsent ? 1 : 0, (int) $contact['id']]);

        return true;
    }

    private static function ensureConsentColumns(): void
    {
        $pdo = Database::pdo();
        foreach (['sms_consent', 'email_consent'] as $column) {
            $stmt = $pdo->prepare("SHOW COLUMNS FROM marketing_contacts LIKE ?");
            $stmt->execute([$column]);
            if (!$stmt->fetch()) {
                $pdo->exec("ALTER TABLE marketing_contacts ADD {$column} TINYINT(1) NOT NULL DEFAULT 0 AFTER tags");
            }
        }

        $stmt = $pdo->prepare("SHOW COLUMNS FROM marketing_contacts LIKE ?");
        $stmt->execute(['unsubscribe_token']);
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE marketing_contacts ADD unsubscribe_token VARCHAR(80) NULL AFTER consent_text");
            $pdo->exec("CREATE INDEX idx_marketing_unsubscribe_token ON marketing_contacts (unsubscribe_token)");
        }
    }

    private static function mergeTags(?string $current, ?string $incoming): ?string
    {
        $tags = [];
        foreach (explode(',', (string) $current . ',' . (string) $incoming) as $tag) {
            $tag = trim($tag);
            if ($tag !== '') {
                $tags[$tag] = $tag;
            }
        }

        return $tags ? implode(',', array_values($tags)) : null;
    }

    private static function ensureToken(int $id): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("SELECT unsubscribe_token FROM marketing_contacts WHERE id = ?");
        $stmt->execute([$id]);
        $token = $stmt->fetchColumn();
        if ($token) {
            return;
        }

        $pdo->prepare("UPDATE marketing_contacts SET unsubscribe_token = ? WHERE id = ?")->execute([bin2hex(random_bytes(24)), $id]);
    }

    private static function ensureMissingTokens(): void
    {
        $ids = Database::pdo()->query("SELECT id FROM marketing_contacts WHERE unsubscribe_token IS NULL OR unsubscribe_token = ''")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($ids as $id) {
            self::ensureToken((int) $id);
        }
    }
}
