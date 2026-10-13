SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `categories` (
    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(255)     NOT NULL,
    `description` TEXT             DEFAULT NULL,
    `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_categories_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `posts` (
    `id`           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `title`        VARCHAR(500)     NOT NULL,
    `description`  TEXT             DEFAULT NULL,
    `content`      LONGTEXT         DEFAULT NULL,
    `image`        VARCHAR(500)     DEFAULT NULL,
    `views_count`  INT UNSIGNED     NOT NULL DEFAULT 0,
    `published_at` DATETIME         NOT NULL,
    `created_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_posts_published_at` (`published_at`),
    INDEX `idx_posts_views_count`  (`views_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `post_categories` (
    `post_id`     INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`post_id`, `category_id`),
    INDEX `idx_pc_category_id` (`category_id`),
    CONSTRAINT `fk_pc_post`     FOREIGN KEY (`post_id`)     REFERENCES `posts` (`id`)      ON DELETE CASCADE,
    CONSTRAINT `fk_pc_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;