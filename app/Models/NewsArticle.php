<?php

namespace App\Models;

use App\Core\Database;
use PDO;

final class NewsArticle
{
    public static function published(int $limit = 12): array
    {
        self::ensureTable();
        $stmt = Database::pdo()->prepare("SELECT * FROM news_articles WHERE status = 'published' ORDER BY COALESCE(published_at, created_at) DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function publishedAll(): array
    {
        self::ensureTable();
        return Database::pdo()->query("SELECT * FROM news_articles WHERE status = 'published' ORDER BY COALESCE(published_at, created_at) DESC")->fetchAll();
    }

    public static function all(): array
    {
        self::ensureTable();
        return Database::pdo()->query("SELECT * FROM news_articles ORDER BY COALESCE(published_at, created_at) DESC")->fetchAll();
    }

    public static function find(int $id): ?array
    {
        self::ensureTable();
        $stmt = Database::pdo()->prepare("SELECT * FROM news_articles WHERE id = ?");
        $stmt->execute([$id]);
        $article = $stmt->fetch();
        return $article ?: null;
    }

    public static function findPublishedBySlug(string $slug): ?array
    {
        self::ensureTable();
        $stmt = Database::pdo()->prepare("SELECT * FROM news_articles WHERE slug = ? AND status = 'published'");
        $stmt->execute([$slug]);
        $article = $stmt->fetch();
        return $article ?: null;
    }

    public static function related(string $slug, int $limit = 6): array
    {
        self::ensureTable();
        $stmt = Database::pdo()->prepare("SELECT * FROM news_articles WHERE status = 'published' AND slug <> ? ORDER BY COALESCE(published_at, created_at) DESC LIMIT ?");
        $stmt->bindValue(1, $slug);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        self::ensureTable();
        $sql = "INSERT INTO news_articles (status,title,slug,excerpt,content,image_url,seo_title,seo_description,published_at)
                VALUES (:status,:title,:slug,:excerpt,:content,:image_url,:seo_title,:seo_description,:published_at)";
        Database::pdo()->prepare($sql)->execute($data);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        self::ensureTable();
        $data['id'] = $id;
        $sql = "UPDATE news_articles SET status=:status,title=:title,slug=:slug,excerpt=:excerpt,content=:content,image_url=:image_url,seo_title=:seo_title,seo_description=:seo_description,published_at=:published_at WHERE id=:id";
        Database::pdo()->prepare($sql)->execute($data);
    }

    public static function delete(int $id): void
    {
        self::ensureTable();
        Database::pdo()->prepare("DELETE FROM news_articles WHERE id = ?")->execute([$id]);
    }

    public static function ensureTable(): void
    {
        Database::pdo()->exec("
            CREATE TABLE IF NOT EXISTS news_articles (
              id INT AUTO_INCREMENT PRIMARY KEY,
              status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
              title VARCHAR(190) NOT NULL,
              slug VARCHAR(220) NOT NULL UNIQUE,
              excerpt TEXT NULL,
              content MEDIUMTEXT NULL,
              image_url VARCHAR(500) NULL,
              seo_title VARCHAR(190) NULL,
              seo_description VARCHAR(255) NULL,
              published_at DATETIME NULL,
              created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
}
