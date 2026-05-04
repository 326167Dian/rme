-- Add photo column for pelanggan riwayat upload feature
ALTER TABLE riwayat_pelanggan
ADD COLUMN foto VARCHAR(255) NULL AFTER followup;
