-- Migration: add id_admin on riwayat_pelanggan
-- Created: 2026-05-09

-- 1) Add id_admin column if not exists
SET @has_col := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'riwayat_pelanggan'
      AND COLUMN_NAME = 'id_admin'
);

SET @sql_add_col := IF(
    @has_col = 0,
    'ALTER TABLE riwayat_pelanggan ADD COLUMN id_admin INT(11) NULL AFTER id_pelanggan',
    'SELECT "Column id_admin already exists"'
);
PREPARE stmt_add_col FROM @sql_add_col;
EXECUTE stmt_add_col;
DEALLOCATE PREPARE stmt_add_col;

-- 2) Add index for join/filter performance
SET @has_idx := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'riwayat_pelanggan'
      AND INDEX_NAME = 'idx_riwayat_pelanggan_id_admin'
);

SET @sql_add_idx := IF(
    @has_idx = 0,
    'ALTER TABLE riwayat_pelanggan ADD INDEX idx_riwayat_pelanggan_id_admin (id_admin)',
    'SELECT "Index idx_riwayat_pelanggan_id_admin already exists"'
);
PREPARE stmt_add_idx FROM @sql_add_idx;
EXECUTE stmt_add_idx;
DEALLOCATE PREPARE stmt_add_idx;

-- 3) Optional foreign key to admin(id_admin)
--    If your data may contain id_admin values not present in admin,
--    clean the data first before enabling this section.
SET @has_fk := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND CONSTRAINT_NAME = 'fk_riwayat_pelanggan_admin'
);

SET @sql_add_fk := IF(
    @has_fk = 0,
    'ALTER TABLE riwayat_pelanggan ADD CONSTRAINT fk_riwayat_pelanggan_admin FOREIGN KEY (id_admin) REFERENCES admin(id_admin) ON UPDATE CASCADE ON DELETE SET NULL',
    'SELECT "Foreign key fk_riwayat_pelanggan_admin already exists"'
);
PREPARE stmt_add_fk FROM @sql_add_fk;
EXECUTE stmt_add_fk;
DEALLOCATE PREPARE stmt_add_fk;
