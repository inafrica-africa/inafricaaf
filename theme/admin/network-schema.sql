-- Networking module schema.
--
-- The app's own DB user (inafrica_app) has no ALTER/CREATE privilege, so this
-- isn't applied automatically by any PHP code — it's a tracked reference/
-- redeploy script. Apply it with a privileged user, e.g.:
--   sudo mysql inafrica < theme/admin/network-schema.sql

-- One row per registered person/org. Never hard-deleted (Is_Active=0 instead)
-- so message history (UserId FK) stays intact after an admin removal.
CREATE TABLE IF NOT EXISTS tblnetworkusers (
    id INT NOT NULL AUTO_INCREMENT,
    Name VARCHAR(255) NOT NULL,
    WhatsApp VARCHAR(50) NOT NULL,
    Email VARCHAR(255) NOT NULL,
    Status ENUM('NGO','Individual','Initiative/Movement') NOT NULL,
    CountryId INT NOT NULL,
    CreatedDate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    LastSeenDate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Is_Active TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    KEY idx_email (Email),
    KEY idx_whatsapp (WhatsApp),
    KEY idx_country (CountryId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Multiple devices per user, so "restore access" on a new browser never
-- invalidates an existing one.
CREATE TABLE IF NOT EXISTS tblnetworkdevices (
    id INT NOT NULL AUTO_INCREMENT,
    UserId INT NOT NULL,
    DeviceToken VARCHAR(64) NOT NULL,
    CreatedDate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    LastSeenDate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_device_token (DeviceToken),
    KEY idx_user (UserId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Per-country Status availability, admin-configurable. Seeded below with
-- every country x every status active, so nothing is broken out of the box.
CREATE TABLE IF NOT EXISTS tblnetworkstatuscountry (
    id INT NOT NULL AUTO_INCREMENT,
    CountryId INT NOT NULL,
    Status ENUM('NGO','Individual','Initiative/Movement') NOT NULL,
    Is_Active TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_country_status (CountryId, Status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The chat feed. IsDeletedForEveryone (user-initiated) is deliberately a
-- separate flag from Is_Active (admin moderation) so the two are
-- distinguishable in the admin moderation UI.
CREATE TABLE IF NOT EXISTS tblnetworkmessages (
    id INT NOT NULL AUTO_INCREMENT,
    UserId INT NOT NULL,
    MessageType ENUM('text','image','video') NOT NULL,
    MessageText MEDIUMTEXT NULL,
    MediaPath VARCHAR(255) NULL,
    MediaDurationSeconds INT NULL,
    ReplyToMessageId INT NULL,
    ForwardedFromMessageId INT NULL,
    IsDeletedForEveryone TINYINT(1) NOT NULL DEFAULT 0,
    Is_Active TINYINT(1) NOT NULL DEFAULT 1,
    CreatedDate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_created (CreatedDate),
    KEY idx_reply_to (ReplyToMessageId),
    KEY idx_user (UserId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Per-user "delete for me".
CREATE TABLE IF NOT EXISTS tblnetworkmessagehidden (
    id INT NOT NULL AUTO_INCREMENT,
    MessageId INT NOT NULL,
    UserId INT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_message_user (MessageId, UserId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed: every country x every status, active by default.
INSERT IGNORE INTO tblnetworkstatuscountry (CountryId, Status)
SELECT c.id, s.Status
FROM tblcountries c
CROSS JOIN (
    SELECT 'NGO' AS Status
    UNION ALL SELECT 'Individual'
    UNION ALL SELECT 'Initiative/Movement'
) s;
