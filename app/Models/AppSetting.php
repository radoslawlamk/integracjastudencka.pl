<?php

namespace App\Models;

use App\Core\Database;

final class AppSetting
{
    public static function get(string $key, string $default = ''): string
    {
        self::ensureTable();
        $stmt = Database::pdo()->prepare("SELECT value FROM app_settings WHERE name = ?");
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? (string) $value : $default;
    }

    public static function set(string $key, string $value): void
    {
        self::ensureTable();
        Database::pdo()
            ->prepare("INSERT INTO app_settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)")
            ->execute([$key, $value]);
    }

    private static function ensureTable(): void
    {
        Database::pdo()->exec(
            "CREATE TABLE IF NOT EXISTS app_settings (
                name VARCHAR(120) PRIMARY KEY,
                value TEXT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }
}
