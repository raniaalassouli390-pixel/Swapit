
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `users` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username`     VARCHAR(50) NOT NULL UNIQUE,
  `first_name`   VARCHAR(50) NOT NULL,
  `last_name`    VARCHAR(50),
  `email`        VARCHAR(150) NOT NULL UNIQUE,
  `phone`        VARCHAR(20),
  `password`     VARCHAR(255) NOT NULL,
  `avatar`       VARCHAR(255) DEFAULT NULL,
  `bio`          TEXT,
  `city`         VARCHAR(100),
  `role`         ENUM('user','admin') DEFAULT 'user',
  `status`       ENUM('active','suspended','unverified') DEFAULT 'unverified',
  `is_verified`  TINYINT(1) DEFAULT 0,
  `verify_token` VARCHAR(6),
  `reset_token`  VARCHAR(100),
  `reset_expires` DATETIME,
  `rating`       DECIMAL(3,2) DEFAULT 0.00,
  `review_count` INT DEFAULT 0,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `categories` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(100) NOT NULL,
  `slug`       VARCHAR(100) NOT NULL UNIQUE,
  `icon`       VARCHAR(10),
  `parent_id`  INT UNSIGNED DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `listings` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`      INT UNSIGNED NOT NULL,
  `category_id`  INT UNSIGNED,
  `title`        VARCHAR(255) NOT NULL,
  `description`  TEXT,
  `type`         ENUM('sale','swap','auction') NOT NULL DEFAULT 'sale',
  `price`        DECIMAL(10,2) DEFAULT NULL,
  `condition`    ENUM('new','like_new','good','fair','for_parts') NOT NULL DEFAULT 'good',
  `city`         VARCHAR(100),
  `swap_notes`   TEXT,
  `status`       ENUM('active','sold','ended','draft','removed') DEFAULT 'active',
  `views`        INT DEFAULT 0,
  `is_featured`  TINYINT(1) DEFAULT 0,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  INDEX `idx_type` (`type`),
  INDEX `idx_status` (`status`),
  INDEX `idx_city` (`city`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `listing_images` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `listing_id` INT UNSIGNED NOT NULL,
  `path`       VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) DEFAULT 0,
  `sort_order` INT DEFAULT 0,
  FOREIGN KEY (`listing_id`) REFERENCES `listings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `auctions` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `listing_id`    INT UNSIGNED NOT NULL UNIQUE,
  `start_price`   DECIMAL(10,2) NOT NULL,
  `current_bid`   DECIMAL(10,2) DEFAULT NULL,
  `winner_id`     INT UNSIGNED DEFAULT NULL,
  `starts_at`     DATETIME NOT NULL,
  `ends_at`       DATETIME NOT NULL,
  `status`        ENUM('upcoming','live','ended') DEFAULT 'upcoming',
  FOREIGN KEY (`listing_id`) REFERENCES `listings`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`winner_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `bids` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `auction_id` INT UNSIGNED NOT NULL,
  `user_id`    INT UNSIGNED NOT NULL,
  `amount`     DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`auction_id`) REFERENCES `auctions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_auction` (`auction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `swap_offers` (
  `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `sender_id`       INT UNSIGNED NOT NULL,
  `receiver_id`     INT UNSIGNED NOT NULL,
  `offered_listing` INT UNSIGNED NOT NULL,
  `wanted_listing`  INT UNSIGNED NOT NULL,
  `message`         TEXT,
  `cash_addition`   DECIMAL(10,2) DEFAULT 0,
  `status`          ENUM('pending','accepted','rejected','cancelled','countered') DEFAULT 'pending',
  `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`receiver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`offered_listing`) REFERENCES `listings`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`wanted_listing`) REFERENCES `listings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `conversations` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user1_id`   INT UNSIGNED NOT NULL,
  `user2_id`   INT UNSIGNED NOT NULL,
  `listing_id` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user1_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user2_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`listing_id`) REFERENCES `listings`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `messages` (
  `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `conversation_id` INT UNSIGNED NOT NULL,
  `sender_id`       INT UNSIGNED NOT NULL,
  `body`            TEXT NOT NULL,
  `is_read`         TINYINT(1) DEFAULT 0,
  `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `wishlist` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT UNSIGNED NOT NULL,
  `listing_id` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_wish` (`user_id`, `listing_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`listing_id`) REFERENCES `listings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `reviews` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `reviewer_id`  INT UNSIGNED NOT NULL,
  `reviewed_id`  INT UNSIGNED NOT NULL,
  `listing_id`   INT UNSIGNED DEFAULT NULL,
  `rating`       TINYINT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `comment`      TEXT,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`reviewer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reviewed_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`listing_id`) REFERENCES `listings`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `notifications` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT UNSIGNED NOT NULL,
  `type`       ENUM('bid','swap','message','review','system','price_drop') NOT NULL,
  `title`      VARCHAR(255) NOT NULL,
  `body`       TEXT,
  `link`       VARCHAR(255),
  `is_read`    TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_unread` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `reports` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `reporter_id`  INT UNSIGNED NOT NULL,
  `reported_id`  INT UNSIGNED,
  `listing_id`   INT UNSIGNED,
  `type`         ENUM('fraud','fake_item','spam','harassment','other') NOT NULL,
  `description`  TEXT,
  `status`       ENUM('open','reviewing','resolved') DEFAULT 'open',
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`reporter_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reported_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`listing_id`) REFERENCES `listings`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
