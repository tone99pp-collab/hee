<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'hotel_system';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    $conn = new mysqli($host, $user, $pass);
    $conn->set_charset('utf8mb4');
    $conn->query("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db($dbname);
}

$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL DEFAULT 'Administrator',
    role VARCHAR(50) NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$conn->query("CREATE TABLE IF NOT EXISTS site_settings (
    id INT PRIMARY KEY DEFAULT 1,
    hotel_name VARCHAR(200) NOT NULL DEFAULT 'Nikosubila Lodge',
    tagline VARCHAR(255) NOT NULL DEFAULT 'Experience comfort. Experience nature.',
    footer_text VARCHAR(255) NOT NULL DEFAULT 'Nikosubila Lodge - Your Comfort, Our Priority.',
    banner_image VARCHAR(255) NOT NULL DEFAULT '',
    logo_image VARCHAR(255) NOT NULL DEFAULT '',
    primary_color VARCHAR(20) NOT NULL DEFAULT '#0a2618',
    accent_color VARCHAR(20) NOT NULL DEFAULT '#d4a843',
    card_color VARCHAR(20) NOT NULL DEFAULT '#0e2d1d',
    currency VARCHAR(20) NOT NULL DEFAULT '₭',
    phone VARCHAR(80) NOT NULL DEFAULT '+856 20 5555 1234',
    email VARCHAR(150) NOT NULL DEFAULT 'info@lodge-system.la',
    facebook VARCHAR(150) NOT NULL DEFAULT 'Lodge Official',
    instagram VARCHAR(150) NOT NULL DEFAULT '@lodge_official',
    linkedin VARCHAR(150) NOT NULL DEFAULT 'Lodge Hospitality',
    x_account VARCHAR(150) NOT NULL DEFAULT '@lodge_x',
    bank_name VARCHAR(150) NOT NULL DEFAULT 'BCEL / ທະນາຄານການຄ້າຕ່າງປະເທດລາວ',
    bank_account VARCHAR(150) NOT NULL DEFAULT '010-12-00-12345678-001',
    bank_branch VARCHAR(150) NOT NULL DEFAULT 'ສຳນັກງານໃຫຍ່ ນະຄອນຫຼວງວຽງຈັນ',
    google_sheet_id VARCHAR(255) NOT NULL DEFAULT '',
    google_sheet_webhook VARCHAR(255) NOT NULL DEFAULT '',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$conn->query("CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_name VARCHAR(150) NOT NULL,
    room_type VARCHAR(100) NOT NULL,
    price_per_night DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_rooms INT NOT NULL DEFAULT 1,
    available_rooms INT NOT NULL DEFAULT 1,
    max_guests INT NOT NULL DEFAULT 2,
    status VARCHAR(50) NOT NULL DEFAULT 'Available',
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$conn->query("CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_code VARCHAR(40) NOT NULL UNIQUE,
    guest_name VARCHAR(150) NOT NULL,
    guest_phone VARCHAR(80) NOT NULL,
    guest_email VARCHAR(150) DEFAULT '',
    room_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    adults INT NOT NULL DEFAULT 2,
    children INT NOT NULL DEFAULT 0,
    payment_method VARCHAR(80) NOT NULL DEFAULT 'Bank Transfer',
    special_requests TEXT,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    status VARCHAR(40) NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$conn->query("INSERT IGNORE INTO site_settings (id) VALUES (1)");

$adminCheck = $conn->query("SELECT id FROM users WHERE username = 'admin' LIMIT 1");
if ($adminCheck && $adminCheck->num_rows === 0) {
    $defaultHash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password_hash, full_name, role) VALUES ('admin', ?, 'ຜູ້ດູແລລະບົບ (Admin)', 'admin')");
    $stmt->bind_param("s", $defaultHash);
    $stmt->execute();
    $stmt->close();
}

$roomCountRow = $conn->query("SELECT COUNT(*) AS cnt FROM rooms")->fetch_assoc();
if ((int)($roomCountRow['cnt'] ?? 0) === 0) {
    $conn->query("INSERT INTO rooms (room_name, room_type, price_per_night, total_rooms, available_rooms, max_guests, status, description) VALUES
        ('Standard Room', 'Standard', 850000, 12, 8, 2, 'Available', 'ຫ້ອງພັກມາດຕະຖານ ວິວທຳມະຊາດ ພ້ອມສິ່ງອຳນວຍຄວາມສະດວກຄົບຄັນ'),
        ('Deluxe Room', 'Deluxe', 1200000, 10, 6, 2, 'Available', 'ຫ້ອງພັກດີລັກສ໌ ກວ້າງຂວາງ ມີລະບຽງສ່ວນຕົວເບິ່ງເຫັນປ່າໄມ້'),
        ('Executive Suite', 'Executive', 1800000, 6, 2, 3, '2 Rooms Left', 'ຫ້ອງສະວີດລະດັບຜູ້ບໍລິຫານ ພ້ອມພື້ນທີ່ຮັບແຂກ ແລະ ອ່າງອາບນ້ຳ'),
        ('Family Chalet', 'Family Chalet', 2200000, 4, 1, 5, '1 Room Left', 'ເຮືອນພັກຄອບຄົວແບບຊາເລ 2 ຫ້ອງນອນ ເໝາະສຳລັບຄອບຄົວໃຫຍ່')");
}

$bookingCountRow = $conn->query("SELECT COUNT(*) AS cnt FROM bookings")->fetch_assoc();
if ((int)($bookingCountRow['cnt'] ?? 0) === 0) {
    $conn->query("INSERT INTO bookings (booking_code, guest_name, guest_phone, guest_email, room_id, check_in, check_out, adults, children, payment_method, special_requests, total_amount, status) VALUES
        ('BK-2026-1001', 'ສົມຊາຍ ວົງສະຫວັດ', '+856 20 5511 2233', 'somchai@example.la', 1, '2026-11-18', '2026-11-21', 2, 1, 'Bank Transfer', 'ຂໍຫ້ອງຊັ້ນເທິງ ວິວງາມ', 2550000, 'Checked-in'),
        ('BK-2026-1002', 'ນາງ ມະລີວັນ ພົມມະຈັນ', '+856 20 9988 7766', 'malivan@example.la', 2, '2026-11-19', '2026-11-22', 2, 0, 'QR Code', 'ໝອນນຸ່ມ', 3600000, 'Confirmed'),
        ('BK-2026-1003', 'ທ້າວ ອານຸສອນ ແກ້ວມະນີ', '+856 20 7766 5544', 'anousone@example.la', 3, '2026-11-20', '2026-11-22', 2, 0, 'Mobile Payment', 'ອາຫານເຈ', 3600000, 'Pending'),
        ('BK-2026-1004', 'ນາງ ຈັນສະໝອນ ສີຫາລາດ', '+856 20 2233 4455', 'chansamone@example.la', 4, '2026-11-15', '2026-11-18', 2, 2, 'Bank Transfer', 'Honeymoon', 6600000, 'Checked-out')");
}

$defaultSettings = [
    'hotel_name' => 'Nikosubila Lodge',
    'tagline' => 'Experience comfort. Experience nature.',
    'footer_text' => 'Nikosubila Lodge - Your Comfort, Our Priority.',
    'banner_image' => '',
    'logo_image' => '',
    'primary_color' => '#0a2618',
    'accent_color' => '#d4a843',
    'card_color' => '#0e2d1d',
    'currency' => '₭',
    'phone' => '+856 20 5555 1234',
    'email' => 'info@lodge-system.la',
    'facebook' => 'Lodge Official',
    'instagram' => '@lodge_official',
    'linkedin' => 'Lodge Hospitality',
    'x_account' => '@lodge_x',
    'bank_name' => 'BCEL / ທະນາຄານການຄ້າຕ່າງປະເທດລາວ',
    'bank_account' => '010-12-00-12345678-001',
    'bank_branch' => 'ສຳນັກງານໃຫຍ່ ນະຄອນຫຼວງວຽງຈັນ',
    'google_sheet_id' => '',
    'google_sheet_webhook' => ''
];

$settings = $defaultSettings;
$sRes = $conn->query("SELECT * FROM site_settings WHERE id = 1 LIMIT 1");
if ($sRes && $sRow = $sRes->fetch_assoc()) {
    $settings = array_merge($defaultSettings, $sRow);
}

function e(?string $val): string {
    return htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8');
}

function money_fmt(float $amount, string $curr = '₭'): string {
    return e($curr) . ' ' . number_format($amount, 0, '.', ',');
}

function check_admin(string $loginUrl = 'login.php'): void {
    if (empty($_SESSION['admin_logged_in'])) {
        header("Location: {$loginUrl}");
        exit;
    }
}
