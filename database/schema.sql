CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(120) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_email_codes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT NOT NULL,
  code_hash VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  consumed_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_admin_email_codes_admin (admin_id),
  INDEX idx_admin_email_codes_expires (expires_at),
  FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS app_settings (
  name VARCHAR(120) PRIMARY KEY,
  value TEXT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('regular','clubbing') NOT NULL DEFAULT 'regular',
  status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  sales_status ENUM('auto','tickets_soon','on_sale','sold_out','finished') NOT NULL DEFAULT 'auto',
  title VARCHAR(190) NOT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  city VARCHAR(120) NOT NULL,
  region VARCHAR(120) NULL,
  starts_at DATETIME NOT NULL,
  ends_at DATETIME NULL,
  venue_name VARCHAR(190) NULL,
  venue_address VARCHAR(255) NULL,
  short_description TEXT NULL,
  description MEDIUMTEXT NULL,
  ticket_url VARCHAR(500) NULL,
  facebook_event_url VARCHAR(500) NULL,
  fanpage_url VARCHAR(500) NULL,
  hero_image VARCHAR(500) NULL,
  seo_title VARCHAR(190) NULL,
  seo_description VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS event_clubs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  name VARCHAR(190) NOT NULL,
  address VARCHAR(255) NULL,
  description TEXT NULL,
  map_url VARCHAR(500) NULL,
  image_url VARCHAR(500) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS event_videos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  youtube_url VARCHAR(500) NOT NULL,
  title VARCHAR(190) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS partners (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(190) NOT NULL,
  logo_url VARCHAR(500) NULL,
  website_url VARCHAR(500) NULL,
  category VARCHAR(120) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS event_partners (
  event_id INT NOT NULL,
  partner_id INT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (event_id, partner_id),
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
  FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketing_contacts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(60) NULL,
  city VARCHAR(120) NULL,
  source VARCHAR(120) NULL,
  tags VARCHAR(500) NULL,
  sms_consent TINYINT(1) NOT NULL DEFAULT 0,
  email_consent TINYINT(1) NOT NULL DEFAULT 0,
  marketing_consent TINYINT(1) NOT NULL DEFAULT 0,
  consent_text TEXT NULL,
  unsubscribe_token VARCHAR(80) NULL,
  ip_address VARCHAR(80) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_marketing_unsubscribe_token (unsubscribe_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS event_analytics (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS analytics_seasons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(190) NOT NULL,
  starts_at DATETIME NOT NULL,
  ends_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_analytics_seasons_dates (starts_at, ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
