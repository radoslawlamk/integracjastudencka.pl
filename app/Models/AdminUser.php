<?php

namespace App\Models;

use App\Core\Database;
use PDO;

final class AdminUser
{
    public static function ensureTables(): void
    {
        $pdo = Database::pdo();
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS admins (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(190) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                name VARCHAR(120) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS admin_email_codes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                admin_id INT NOT NULL,
                code_hash VARCHAR(255) NOT NULL,
                expires_at DATETIME NOT NULL,
                consumed_at DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_admin_email_codes_admin (admin_id),
                INDEX idx_admin_email_codes_expires (expires_at),
                FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public static function count(): int
    {
        self::ensureTables();
        return (int) Database::pdo()->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    }

    public static function all(): array
    {
        self::ensureTables();
        return Database::pdo()
            ->query("SELECT id, email, name, created_at FROM admins ORDER BY created_at ASC, id ASC")
            ->fetchAll();
    }

    public static function findByEmail(string $email): ?array
    {
        self::ensureTables();
        $stmt = Database::pdo()->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        return $admin ?: null;
    }

    public static function find(int $id): ?array
    {
        self::ensureTables();
        $stmt = Database::pdo()->prepare("SELECT * FROM admins WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $admin = $stmt->fetch();
        return $admin ?: null;
    }

    public static function create(string $name, string $email, string $password): bool
    {
        self::ensureTables();
        $stmt = Database::pdo()->prepare("INSERT INTO admins (email, password_hash, name) VALUES (?, ?, ?)");
        try {
            return $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT), $name]);
        } catch (\Throwable) {
            return false;
        }
    }

    public static function createEmailCode(int $adminId, string $code): void
    {
        self::ensureTables();
        Database::pdo()->prepare("UPDATE admin_email_codes SET consumed_at = NOW() WHERE admin_id = ? AND consumed_at IS NULL")
            ->execute([$adminId]);
        Database::pdo()->prepare("INSERT INTO admin_email_codes (admin_id, code_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))")
            ->execute([$adminId, password_hash($code, PASSWORD_DEFAULT)]);
    }

    public static function verifyEmailCode(int $adminId, string $code): bool
    {
        self::ensureTables();
        $stmt = Database::pdo()->prepare(
            "SELECT * FROM admin_email_codes
             WHERE admin_id = ? AND consumed_at IS NULL AND expires_at >= NOW()
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([$adminId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || !password_verify($code, $row['code_hash'])) {
            return false;
        }

        Database::pdo()->prepare("UPDATE admin_email_codes SET consumed_at = NOW() WHERE id = ?")
            ->execute([(int) $row['id']]);
        return true;
    }
}
