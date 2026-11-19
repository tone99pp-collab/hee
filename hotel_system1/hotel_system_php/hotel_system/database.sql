-- ຖານຂໍ້ມູນສຳລັບລະບົບຈັດການໂຮງແຮມ (XAMPP / MySQL)
CREATE DATABASE IF NOT EXISTS hotel_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hotel_system;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ລະຫັດຜ່ານເລີ່ມຕົ້ນ: admin123
INSERT IGNORE INTO users (username, password_hash, full_name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ຜູ້ດູແລລະບົບ (Admin)', 'admin');

CREATE TABLE IF NOT EXISTS site_settings (
    id INT PRIMARY KEY DEFAULT 1,
    hotel_name VARCHAR(200) NOT NULL DEFAULT 'Nikosubila Lodge',
    tagline VARCHAR(255) NOT NULL DEFAULT 'Experience comfort. Experience nature.',
    footer_note VARCHAR(255) NOT NULL DEFAULT 'Nikosubila Lodge - Your Comfort, Our Priority.',
    banner_image VARCHAR(255) NOT NULL DEFAULT '',
    logo_image VARCHAR(255) NOT NULL DEFAULT '',
    primary_color VARCHAR(20) NOT NULL DEFAULT '#0a2618',
    accent_color VARCHAR(20) NOT NULL DEFAULT '#d4a843',
    card_color VARCHAR(20) NOT NULL DEFAULT '#0e2d1d',
    currency VARCHAR(20) NOT NULL DEFAULT '₭',
    phone VARCHAR(60) NOT NULL DEFAULT '+856 20 5555 1234',
    email VARCHAR(150) NOT NULL DEFAULT 'info@lodge-system.la',
    facebook VARCHAR(150) NOT NULL DEFAULT 'Lodge Official',
    instagram VARCHAR(150) NOT NULL DEFAULT '@lodge_official',
    linkedin VARCHAR(150) NOT NULL DEFAULT 'Lodge Hospitality',
    x_handle VARCHAR(150) NOT NULL DEFAULT '@lodge_x',
    bank_name VARCHAR(120) NOT NULL DEFAULT 'BCEL / ທະນາຄານການຄ້າຕ່າງປະເທດລາວ',
    bank_account VARCHAR(120) NOT NULL DEFAULT '010-12-00-12345678-001',
    bank_branch VARCHAR(120) NOT NULL DEFAULT 'ສຳນັກງານໃຫຍ່ ນະຄອນຫຼວງວຽງຈັນ',
    google_sheet_id VARCHAR(255) NOT NULL DEFAULT '',
    google_sheet_webhook VARCHAR(255) NOT NULL DEFAULT '',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO site_settings (id) VALUES (1);

CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_name VARCHAR(150) NOT NULL,
    room_type VARCHAR(100) NOT NULL,
    price_per_night DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_rooms INT NOT NULL DEFAULT 1,
    available_rooms INT NOT NULL DEFAULT 1,
    max_guests INT NOT NULL DEFAULT 2,
    status VARCHAR(50) NOT NULL DEFAULT 'Available',
    description TEXT,
    image_url VARCHAR(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO rooms (room_name, room_type, price_per_night, total_rooms, available_rooms, max_guests, status, description)
SELECT 'Standard Room', 'Standard', 850000, 12, 8, 2, 'Available', 'ຫ້ອງພັກມາດຕະຖານ ວິວທຳມະຊາດ ພ້ອມສິ່ງອຳນວຍຄວາມສະດວກຄົບຄັນ'
WHERE NOT EXISTS (SELECT 1 FROM rooms);

INSERT INTO rooms (room_name, room_type, price_per_night, total_rooms, available_rooms, max_guests, status, description)
SELECT * FROM (
    SELECT 'Deluxe Room', 'Deluxe', 1200000, 10, 6, 2, 'Available', 'ຫ້ອງພັກດີລັກສ໌ ກວ້າງຂວາງ ມີລະບຽງສ່ວນຕົວເບິ່ງເຫັນປ່າໄມ້' UNION ALL
    SELECT 'Executive Suite', 'Executive', 1800000, 6, 2, 3, '2 Rooms Left', 'ຫ້ອງສະວີດລະດັບຜູ້ບໍລິຫານ ພ້ອມພື້ນທີ່ຮັບແຂກ ແລະ ອ່າງອາບນ້ຳ' UNION ALL
    SELECT 'Family Chalet', 'Family Chalet', 2200000, 4, 1, 5, '1 Room Left', 'ເຮືອນພັກຄອບຄົວແບບຊາເລ 2 ຫ້ອງນອນ ເໝາະສຳລັບຄອບຄົວໃຫຍ່'
) AS tmp
WHERE (SELECT COUNT(*) FROM rooms) = 1;

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_code VARCHAR(40) NOT NULL UNIQUE,
    guest_name VARCHAR(150) NOT NULL,
    guest_phone VARCHAR(60) NOT NULL,
    guest_email VARCHAR(150) DEFAULT '',
    room_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    adults INT NOT NULL DEFAULT 2,
    children INT NOT NULL DEFAULT 0,
    payment_method VARCHAR(80) NOT NULL DEFAULT 'Bank Transfer',
    special_requests TEXT,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    status VARCHAR(40) NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO bookings (booking_code, guest_name, guest_phone, guest_email, room_id, check_in, check_out, adults, children, payment_method, special_requests, total_amount, status)
VALUES
('BK-2026-1001', 'ສົມຊາຍ ວົງສະຫວັດ', '+856 20 5511 2233', 'somchai@example.la', 1, '2026-11-18', '2026-11-21', 2, 1, 'Bank Transfer', 'ຂໍຫ້ອງຊັ້ນເທິງ ວິວງາມ', 2550000, 'Checked-in'),
('BK-2026-1002', 'ນາງ ມະລີວັນ ພົມມະຈັນ', '+856 20 9988 7766', 'malivan@example.la', 2, '2026-11-19', '2026-11-22', 2, 0, 'QR Code', 'ໝອນນຸ່ມ (Pillow Type: Soft)', 3600000, 'Confirmed'),
('BK-2026-1003', 'ທ້າວ ອານຸສອນ ແກ້ວມະນີ', '+856 20 7766 5544', 'anousone@example.la', 3, '2026-11-20', '2026-11-22', 2, 0, 'Mobile Payment', 'ອາຫານເຈ (Dietary: Vegetarian)', 3600000, 'Pending'),
('BK-2026-1004', 'ນາງ ຈັນສະໝອນ ສີຫາລາດ', '+856 20 2233 4455', 'chansamone@example.la', 4, '2026-11-15', '2026-11-18', 2, 2, 'Bank Transfer', 'ຄົບຮອບແຕ່ງງານ (Honeymoon)', 6600000, 'Checked-out');
