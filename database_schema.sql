-- =============================================================
-- CI4 Universal Boilerplate — Database Schema
-- Engine: MySQL 8.x | Charset: utf8mb4
-- =============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';

-- -------------------------------------------------------------
-- 1. roles
-- -------------------------------------------------------------
CREATE TABLE `roles` (
    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(60)      NOT NULL,           -- e.g. super_admin, admin, editor
    `label`       VARCHAR(100)     NOT NULL,           -- Human-readable
    `description` TEXT             DEFAULT NULL,
    `is_active`   TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`  DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_roles_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 2. users
-- -------------------------------------------------------------
CREATE TABLE `users` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `uuid`            CHAR(36)         NOT NULL,
    `first_name`      VARCHAR(80)      NOT NULL,
    `last_name`       VARCHAR(80)      NOT NULL,
    `email`           VARCHAR(180)     NOT NULL,
    `phone`           VARCHAR(20)      DEFAULT NULL,
    `password_hash`   VARCHAR(255)     NOT NULL,
    `avatar`          VARCHAR(255)     DEFAULT NULL,
    `status`          ENUM('active','inactive','banned','pending')
                                       NOT NULL DEFAULT 'pending',
    `email_verified`  TINYINT(1)       NOT NULL DEFAULT 0,
    `email_verify_token` VARCHAR(100)  DEFAULT NULL,
    `reset_token`     VARCHAR(100)     DEFAULT NULL,
    `reset_token_expires` DATETIME     DEFAULT NULL,
    `last_login_at`   DATETIME         DEFAULT NULL,
    `last_login_ip`   VARCHAR(45)      DEFAULT NULL,
    `remember_token`  VARCHAR(100)     DEFAULT NULL,
    `created_at`      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`      DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`),
    UNIQUE KEY `uq_users_uuid`  (`uuid`),
    KEY `idx_users_status`      (`status`),
    KEY `idx_users_deleted`     (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 3. permissions
-- -------------------------------------------------------------
CREATE TABLE `permissions` (
    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100)     NOT NULL,           -- e.g. users.create
    `module`      VARCHAR(60)      NOT NULL,           -- e.g. users
    `action`      VARCHAR(60)      NOT NULL,           -- e.g. create, read, update, delete
    `label`       VARCHAR(150)     NOT NULL,
    `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_permissions_name` (`name`),
    KEY `idx_permissions_module`     (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 4. role_permissions
-- -------------------------------------------------------------
CREATE TABLE `role_permissions` (
    `id`            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `role_id`       INT UNSIGNED   NOT NULL,
    `permission_id` INT UNSIGNED   NOT NULL,
    `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_role_perm` (`role_id`, `permission_id`),
    KEY `idx_rp_role_id`       (`role_id`),
    KEY `idx_rp_permission_id` (`permission_id`),
    CONSTRAINT `fk_rp_role`       FOREIGN KEY (`role_id`)       REFERENCES `roles`       (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 5. user_roles
-- -------------------------------------------------------------
CREATE TABLE `user_roles` (
    `id`         INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED   NOT NULL,
    `role_id`    INT UNSIGNED   NOT NULL,
    `created_at` DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_user_role` (`user_id`, `role_id`),
    KEY `idx_ur_user_id` (`user_id`),
    KEY `idx_ur_role_id` (`role_id`),
    CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 6. modules
-- -------------------------------------------------------------
CREATE TABLE `modules` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(80)     NOT NULL,
    `label`       VARCHAR(120)    NOT NULL,
    `description` TEXT            DEFAULT NULL,
    `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
    `sort_order`  SMALLINT        NOT NULL DEFAULT 0,
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_modules_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 7. menus
-- -------------------------------------------------------------
CREATE TABLE `menus` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(60)     NOT NULL,            -- e.g. admin_sidebar, website_nav
    `label`      VARCHAR(100)    NOT NULL,
    `location`   VARCHAR(60)     NOT NULL DEFAULT 'admin',  -- admin | website | api
    `is_active`  TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_menus_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 8. menu_items
-- -------------------------------------------------------------
CREATE TABLE `menu_items` (
    `id`            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `menu_id`       INT UNSIGNED   NOT NULL,
    `parent_id`     INT UNSIGNED   DEFAULT NULL,       -- Self-referencing for nesting
    `title`         VARCHAR(120)   NOT NULL,
    `route`         VARCHAR(255)   DEFAULT NULL,
    `url`           VARCHAR(255)   DEFAULT NULL,       -- Fallback absolute URL
    `icon`          VARCHAR(80)    DEFAULT NULL,       -- e.g. bi-house
    `badge`         VARCHAR(40)    DEFAULT NULL,
    `permission`    VARCHAR(100)   DEFAULT NULL,       -- Required permission name
    `target`        VARCHAR(10)    NOT NULL DEFAULT '_self',
    `sort_order`    SMALLINT       NOT NULL DEFAULT 0,
    `is_active`     TINYINT(1)     NOT NULL DEFAULT 1,
    `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`    DATETIME       DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_mi_menu_id`   (`menu_id`),
    KEY `idx_mi_parent_id` (`parent_id`),
    CONSTRAINT `fk_mi_menu`   FOREIGN KEY (`menu_id`)   REFERENCES `menus`      (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_mi_parent` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 9. settings
-- -------------------------------------------------------------
CREATE TABLE `settings` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `group`       VARCHAR(60)     NOT NULL DEFAULT 'general',  -- general | email | security
    `key`         VARCHAR(100)    NOT NULL,
    `value`       TEXT            DEFAULT NULL,
    `type`        ENUM('text','number','boolean','json','password','file')
                                  NOT NULL DEFAULT 'text',
    `label`       VARCHAR(150)    NOT NULL,
    `description` VARCHAR(255)    DEFAULT NULL,
    `is_public`   TINYINT(1)      NOT NULL DEFAULT 0,  -- Accessible in views?
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_settings_group_key` (`group`, `key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 10. activity_logs
-- -------------------------------------------------------------
CREATE TABLE `activity_logs` (
    `id`          BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED     DEFAULT NULL,
    `session_id`  VARCHAR(128)     DEFAULT NULL,
    `action`      VARCHAR(120)     NOT NULL,           -- e.g. login, create_user
    `module`      VARCHAR(80)      NOT NULL,
    `description` TEXT             DEFAULT NULL,
    `ip_address`  VARCHAR(45)      DEFAULT NULL,
    `user_agent`  VARCHAR(255)     DEFAULT NULL,
    `url`         VARCHAR(500)     DEFAULT NULL,
    `method`      VARCHAR(10)      DEFAULT NULL,
    `extra`       JSON             DEFAULT NULL,
    `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_al_user_id`   (`user_id`),
    KEY `idx_al_module`    (`module`),
    KEY `idx_al_action`    (`action`),
    KEY `idx_al_created`   (`created_at`),
    CONSTRAINT `fk_al_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 11. notifications
-- -------------------------------------------------------------
CREATE TABLE `notifications` (
    `id`          BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED     NOT NULL,
    `type`        VARCHAR(80)      NOT NULL,           -- e.g. info, warning, success
    `title`       VARCHAR(200)     NOT NULL,
    `message`     TEXT             NOT NULL,
    `data`        JSON             DEFAULT NULL,
    `url`         VARCHAR(500)     DEFAULT NULL,
    `is_read`     TINYINT(1)       NOT NULL DEFAULT 0,
    `read_at`     DATETIME         DEFAULT NULL,
    `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notif_user_id`  (`user_id`),
    KEY `idx_notif_is_read`  (`is_read`),
    CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 12. files
-- -------------------------------------------------------------
CREATE TABLE `files` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`       INT UNSIGNED    DEFAULT NULL,
    `module`        VARCHAR(80)     DEFAULT NULL,      -- Linked module, e.g. products
    `module_id`     INT UNSIGNED    DEFAULT NULL,      -- ID in the linked module
    `disk`          VARCHAR(20)     NOT NULL DEFAULT 'local',  -- local | s3
    `path`          VARCHAR(500)    NOT NULL,
    `filename`      VARCHAR(255)    NOT NULL,
    `original_name` VARCHAR(255)    NOT NULL,
    `mime_type`     VARCHAR(100)    NOT NULL,
    `extension`     VARCHAR(10)     NOT NULL,
    `size`          INT UNSIGNED    NOT NULL DEFAULT 0, -- Bytes
    `type`          ENUM('image','document','video','audio','other')
                                    NOT NULL DEFAULT 'other',
    `is_public`     TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`    DATETIME        DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_files_user_id`     (`user_id`),
    KEY `idx_files_module`      (`module`, `module_id`),
    KEY `idx_files_type`        (`type`),
    CONSTRAINT `fk_files_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 13. audit_trail
-- -------------------------------------------------------------
CREATE TABLE `audit_trail` (
    `id`           BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `user_id`      INT UNSIGNED     DEFAULT NULL,
    `event`        ENUM('created','updated','deleted','restored')
                                    NOT NULL DEFAULT 'updated',
    `auditable_type` VARCHAR(100)   NOT NULL,          -- Model name, e.g. User
    `auditable_id`   INT UNSIGNED   NOT NULL,
    `old_values`   JSON             DEFAULT NULL,
    `new_values`   JSON             DEFAULT NULL,
    `url`          VARCHAR(500)     DEFAULT NULL,
    `ip_address`   VARCHAR(45)      DEFAULT NULL,
    `user_agent`   VARCHAR(255)     DEFAULT NULL,
    `tags`         VARCHAR(255)     DEFAULT NULL,
    `created_at`   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_at_user_id`        (`user_id`),
    KEY `idx_at_auditable`      (`auditable_type`, `auditable_id`),
    KEY `idx_at_event`          (`event`),
    CONSTRAINT `fk_at_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================
-- SEED DATA
-- =============================================================

-- Roles
INSERT INTO `roles` (`name`, `label`, `description`) VALUES
('super_admin', 'Super Administrator', 'Full unrestricted access'),
('admin',       'Administrator',       'Manage all modules'),
('editor',      'Editor',              'Create and edit content'),
('viewer',      'Viewer',              'Read-only access');

-- Permissions (CRUD per module)
INSERT INTO `permissions` (`name`, `module`, `action`, `label`) VALUES
('users.create',  'users', 'create', 'Create Users'),
('users.read',    'users', 'read',   'View Users'),
('users.update',  'users', 'update', 'Edit Users'),
('users.delete',  'users', 'delete', 'Delete Users'),
('roles.create',  'roles', 'create', 'Create Roles'),
('roles.read',    'roles', 'read',   'View Roles'),
('roles.update',  'roles', 'update', 'Edit Roles'),
('roles.delete',  'roles', 'delete', 'Delete Roles'),
('settings.read', 'settings', 'read',   'View Settings'),
('settings.update','settings','update', 'Edit Settings'),
('files.create',  'files', 'create', 'Upload Files'),
('files.read',    'files', 'read',   'View Files'),
('files.delete',  'files', 'delete', 'Delete Files');

-- Assign all permissions to super_admin (role id=1)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions`;

-- Menus
INSERT INTO `menus` (`name`, `label`, `location`) VALUES
('admin_sidebar', 'Admin Sidebar', 'admin'),
('website_nav',   'Website Navigation', 'website');

-- Admin menu items
INSERT INTO `menu_items` (`menu_id`, `parent_id`, `title`, `route`, `icon`, `sort_order`) VALUES
(1, NULL, 'Dashboard',    'admin.dashboard',   'bi-speedometer2',  1),
(1, NULL, 'Users',        'admin.users.index', 'bi-people',        2),
(1, NULL, 'Roles',        'admin.roles.index', 'bi-shield-lock',   3),
(1, NULL, 'Menus',        'admin.menus.index', 'bi-list-nested',   4),
(1, NULL, 'Files',        'admin.files.index', 'bi-folder',        5),
(1, NULL, 'Activity Log', 'admin.logs.index',  'bi-journal-text',  6),
(1, NULL, 'Settings',     'admin.settings',    'bi-gear',          7);

-- Settings seed
INSERT INTO `settings` (`group`, `key`, `value`, `type`, `label`, `is_public`) VALUES
('general', 'site_name',     'CI4 Boilerplate', 'text',    'Site Name',    1),
('general', 'site_tagline',  'Built with CI4',  'text',    'Tagline',      1),
('general', 'site_email',    'admin@example.com','text',   'Admin Email',  0),
('general', 'site_logo',     '',                'file',    'Site Logo',    1),
('general', 'per_page',      '15',              'number',  'Rows Per Page',0),
('email',   'mail_driver',   'smtp',            'text',    'Mail Driver',  0),
('email',   'mail_host',     'smtp.example.com','text',    'SMTP Host',    0),
('email',   'mail_port',     '587',             'number',  'SMTP Port',    0),
('security','login_attempts','5',               'number',  'Max Login Attempts', 0),
('security','session_expire','120',             'number',  'Session Expire (min)',0);
