-- ==========================================================
-- SocialNet Database Schema
-- Charset: utf8mb4 / utf8mb4_unicode_ci
-- Engine: InnoDB
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `socialnet` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `socialnet`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Roles Table
DROP TABLE IF EXISTS `role_permissions`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `description` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Permissions Table
CREATE TABLE `permissions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Role Permissions (Pivot)
CREATE TABLE `role_permissions` (
  `role_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Users Table
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(30) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role_id` INT UNSIGNED NOT NULL DEFAULT 3,
  `is_private` TINYINT(1) NOT NULL DEFAULT 0,
  `is_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `is_banned` TINYINT(1) NOT NULL DEFAULT 0,
  `ban_reason` TEXT NULL,
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `verification_token` VARCHAR(100) NULL,
  `reset_token` VARCHAR(100) NULL,
  `reset_token_expires_at` TIMESTAMP NULL DEFAULT NULL,
  `remember_token` VARCHAR(100) NULL,
  `last_active_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_username` (`username`),
  INDEX `idx_users_email` (`email`),
  INDEX `idx_users_role` (`role_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. User Profiles Table
DROP TABLE IF EXISTS `user_profiles`;
CREATE TABLE `user_profiles` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL UNIQUE,
  `bio` VARCHAR(250) NULL,
  `website` VARCHAR(255) NULL,
  `avatar` VARCHAR(255) DEFAULT 'default-avatar.png',
  `cover_image` VARCHAR(255) DEFAULT 'default-cover.jpg',
  `location` VARCHAR(100) NULL,
  `birthdate` DATE NULL,
  `gender` ENUM('unspecified', 'male', 'female', 'custom') DEFAULT 'unspecified',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_profile_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. User Settings Table
DROP TABLE IF EXISTS `user_settings`;
CREATE TABLE `user_settings` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL UNIQUE,
  `theme` ENUM('dark', 'light') NOT NULL DEFAULT 'dark',
  `notification_email` TINYINT(1) NOT NULL DEFAULT 1,
  `notification_likes` TINYINT(1) NOT NULL DEFAULT 1,
  `notification_comments` TINYINT(1) NOT NULL DEFAULT 1,
  `notification_messages` TINYINT(1) NOT NULL DEFAULT 1,
  `allow_tagging` TINYINT(1) NOT NULL DEFAULT 1,
  `show_activity_status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_settings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Posts Table
DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `content` TEXT NULL,
  `type` ENUM('text', 'image', 'video', 'carousel') NOT NULL DEFAULT 'text',
  `visibility` ENUM('public', 'followers', 'private') NOT NULL DEFAULT 'public',
  `location` VARCHAR(100) NULL,
  `likes_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `comments_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `shares_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `views_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_pinned` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_posts_user` (`user_id`),
  INDEX `idx_posts_created` (`created_at` DESC),
  INDEX `idx_posts_visibility` (`visibility`),
  CONSTRAINT `fk_posts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Post Media Table
DROP TABLE IF EXISTS `post_media`;
CREATE TABLE `post_media` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `post_id` BIGINT UNSIGNED NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_type` ENUM('image', 'video') NOT NULL DEFAULT 'image',
  `mime_type` VARCHAR(100) NOT NULL,
  `file_size` INT UNSIGNED NOT NULL DEFAULT 0,
  `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_post_media_post` (`post_id`),
  CONSTRAINT `fk_media_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Post Likes Table
DROP TABLE IF EXISTS `post_likes`;
CREATE TABLE `post_likes` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `post_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_post_user_like` (`post_id`, `user_id`),
  INDEX `idx_likes_post` (`post_id`),
  INDEX `idx_likes_user` (`user_id`),
  CONSTRAINT `fk_pl_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pl_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Comments Table
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `post_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `parent_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `content` TEXT NOT NULL,
  `likes_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_comments_post` (`post_id`),
  INDEX `idx_comments_parent` (`parent_id`),
  INDEX `idx_comments_user` (`user_id`),
  CONSTRAINT `fk_comments_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_parent` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Comment Likes Table
DROP TABLE IF EXISTS `comment_likes`;
CREATE TABLE `comment_likes` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `comment_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_comment_user_like` (`comment_id`, `user_id`),
  CONSTRAINT `fk_cl_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cl_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Followers Table
DROP TABLE IF EXISTS `followers`;
CREATE TABLE `followers` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `follower_id` BIGINT UNSIGNED NOT NULL,
  `following_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_follow` (`follower_id`, `following_id`),
  INDEX `idx_followers_follower` (`follower_id`),
  INDEX `idx_followers_following` (`following_id`),
  CONSTRAINT `fk_fl_follower` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fl_following` FOREIGN KEY (`following_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Follow Requests Table
DROP TABLE IF EXISTS `follow_requests`;
CREATE TABLE `follow_requests` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `requester_id` BIGINT UNSIGNED NOT NULL,
  `target_id` BIGINT UNSIGNED NOT NULL,
  `status` ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_request` (`requester_id`, `target_id`),
  CONSTRAINT `fk_fr_requester` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fr_target` FOREIGN KEY (`target_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Stories Table
DROP TABLE IF EXISTS `stories`;
CREATE TABLE `stories` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `media_path` VARCHAR(255) NOT NULL,
  `media_type` ENUM('image', 'video') NOT NULL DEFAULT 'image',
  `caption` VARCHAR(255) NULL,
  `views_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `expires_at` TIMESTAMP NOT NULL,
  `is_highlight` TINYINT(1) NOT NULL DEFAULT 0,
  `highlight_title` VARCHAR(100) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_stories_user` (`user_id`),
  INDEX `idx_stories_expires` (`expires_at`),
  CONSTRAINT `fk_stories_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Story Views Table
DROP TABLE IF EXISTS `story_views`;
CREATE TABLE `story_views` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `story_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `viewed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_story_view` (`story_id`, `user_id`),
  CONSTRAINT `fk_sv_story` FOREIGN KEY (`story_id`) REFERENCES `stories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sv_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Story Reactions Table
DROP TABLE IF EXISTS `story_reactions`;
CREATE TABLE `story_reactions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `story_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `reaction_type` VARCHAR(30) NOT NULL DEFAULT 'heart',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_sr_story` FOREIGN KEY (`story_id`) REFERENCES `stories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Conversations Table
DROP TABLE IF EXISTS `conversations`;
CREATE TABLE `conversations` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `type` ENUM('direct', 'group') NOT NULL DEFAULT 'direct',
  `title` VARCHAR(100) NULL,
  `creator_id` BIGINT UNSIGNED NOT NULL,
  `last_message_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_conv_creator` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Conversation Members Table
DROP TABLE IF EXISTS `conversation_members`;
CREATE TABLE `conversation_members` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `conversation_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `role` ENUM('admin', 'member') NOT NULL DEFAULT 'member',
  `last_read_message_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `is_muted` TINYINT(1) NOT NULL DEFAULT 0,
  `joined_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_conv_member` (`conversation_id`, `user_id`),
  CONSTRAINT `fk_cm_conv` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Messages Table
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `conversation_id` BIGINT UNSIGNED NOT NULL,
  `sender_id` BIGINT UNSIGNED NOT NULL,
  `message_type` ENUM('text', 'image', 'video', 'audio') NOT NULL DEFAULT 'text',
  `body` TEXT NULL,
  `media_path` VARCHAR(255) NULL,
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_messages_conv` (`conversation_id`, `created_at` DESC),
  CONSTRAINT `fk_msg_conv` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. Notifications Table
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `sender_id` BIGINT UNSIGNED NOT NULL,
  `type` ENUM('follow', 'follow_request', 'like_post', 'comment_post', 'reply_comment', 'message', 'story_reaction', 'live_stream') NOT NULL,
  `target_type` VARCHAR(50) NULL,
  `target_id` BIGINT UNSIGNED NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_notif_user` (`user_id`, `is_read`, `created_at` DESC),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notif_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. Hashtags Table
DROP TABLE IF EXISTS `hashtags`;
CREATE TABLE `hashtags` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tag` VARCHAR(100) NOT NULL UNIQUE,
  `posts_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_hashtags_tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 22. Post Hashtags (Pivot)
DROP TABLE IF EXISTS `post_hashtags`;
CREATE TABLE `post_hashtags` (
  `post_id` BIGINT UNSIGNED NOT NULL,
  `hashtag_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`post_id`, `hashtag_id`),
  CONSTRAINT `fk_ph_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ph_hashtag` FOREIGN KEY (`hashtag_id`) REFERENCES `hashtags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23. Saved Posts Table
DROP TABLE IF EXISTS `saved_posts`;
CREATE TABLE `saved_posts` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `post_id` BIGINT UNSIGNED NOT NULL,
  `collection_name` VARCHAR(50) DEFAULT 'All Posts',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_saved_post` (`user_id`, `post_id`),
  CONSTRAINT `fk_sp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sp_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 24. Blocked Users Table
DROP TABLE IF EXISTS `blocked_users`;
CREATE TABLE `blocked_users` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `blocker_id` BIGINT UNSIGNED NOT NULL,
  `blocked_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_block` (`blocker_id`, `blocked_id`),
  CONSTRAINT `fk_bu_blocker` FOREIGN KEY (`blocker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bu_blocked` FOREIGN KEY (`blocked_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 25. Reports Table
DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `reporter_id` BIGINT UNSIGNED NOT NULL,
  `reported_type` ENUM('user', 'post', 'comment', 'story') NOT NULL,
  `reported_id` BIGINT UNSIGNED NOT NULL,
  `reason` VARCHAR(100) NOT NULL,
  `details` TEXT NULL,
  `status` ENUM('pending', 'reviewed', 'dismissed', 'resolved') NOT NULL DEFAULT 'pending',
  `action_taken` VARCHAR(255) NULL,
  `reviewed_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_reports_status` (`status`),
  CONSTRAINT `fk_rep_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rep_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 26. Live Streams Table
DROP TABLE IF EXISTS `live_streams`;
CREATE TABLE `live_streams` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `stream_key` VARCHAR(100) NOT NULL UNIQUE,
  `status` ENUM('live', 'ended') NOT NULL DEFAULT 'live',
  `viewer_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `ended_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_live_status` (`status`),
  CONSTRAINT `fk_ls_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 27. Live Stream Viewers Table
DROP TABLE IF EXISTS `live_stream_viewers`;
CREATE TABLE `live_stream_viewers` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `stream_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `joined_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `left_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `fk_lsv_stream` FOREIGN KEY (`stream_id`) REFERENCES `live_streams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lsv_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 28. Audit Logs Table
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `details` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_audit_user` (`user_id`),
  INDEX `idx_audit_action` (`action`),
  INDEX `idx_audit_created` (`created_at` DESC),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================================
-- Initial Seed Data
-- ==========================================================

-- Insert Roles
INSERT INTO `roles` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Administrator', 'admin', 'Full system access and control'),
(2, 'Moderator', 'moderator', 'Content moderation and user supervision'),
(3, 'User', 'user', 'Regular platform user')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Insert Permissions
INSERT INTO `permissions` (`id`, `name`, `slug`, `description`) VALUES
(1, 'View Admin Panel', 'view_admin', 'Can access administration area'),
(2, 'Manage Users', 'manage_users', 'Can ban, verify, edit users'),
(3, 'Manage Posts', 'manage_posts', 'Can remove posts and comments'),
(4, 'Manage Reports', 'manage_reports', 'Can handle user and content reports'),
(5, 'System Settings', 'manage_settings', 'Can modify system configuration')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Assign Permissions to Admin
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5),
(2, 1), (2, 3), (2, 4)
ON DUPLICATE KEY UPDATE `role_id`=VALUES(`role_id`);

-- Default Administrator User: admin / Admin12345!
-- Password hash generated via password_hash('Admin12345!', PASSWORD_BCRYPT)
INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role_id`, `is_verified`, `email_verified_at`) VALUES
(1, 'admin', 'admin@socialnet.test', '$2y$10$3PE8mGTUNCbP0ZqO8lmoOeL2S3gdrU7UygRK/ap8akaiJQuIDJW8S', 'SocialNet Administrator', 1, 1, NOW())
ON DUPLICATE KEY UPDATE `username`=VALUES(`username`);

-- Profile for Admin
INSERT INTO `user_profiles` (`user_id`, `bio`, `website`, `avatar`, `cover_image`, `location`) VALUES
(1, 'Official administrator account of SocialNet.', 'http://socialnet.test', 'default-avatar.png', 'default-cover.jpg', 'Istanbul, Turkey')
ON DUPLICATE KEY UPDATE `user_id`=VALUES(`user_id`);

-- Settings for Admin
INSERT INTO `user_settings` (`user_id`, `theme`) VALUES
(1, 'dark')
ON DUPLICATE KEY UPDATE `user_id`=VALUES(`user_id`);
