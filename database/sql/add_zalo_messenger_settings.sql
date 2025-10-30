-- Thêm cài đặt Zalo và Messenger vào bảng settings
-- Chạy script này để thêm 2 trường mới vào database

-- Thêm trường contact_zalo
INSERT INTO `settings` (`key_name`, `value`, `created_at`, `updated_at`) 
VALUES ('contact_zalo', '', NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- Thêm trường contact_messenger
INSERT INTO `settings` (`key_name`, `value`, `created_at`, `updated_at`) 
VALUES ('contact_messenger', '', NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- Kiểm tra kết quả
SELECT * FROM `settings` WHERE `key_name` IN ('contact_zalo', 'contact_messenger');
