<?php
require_once __DIR__ . '/../config/db.php';
check_admin('../login.php');

$msg = '';

function upload_img(string $field, string $dir): ?string {
    if (empty($_FILES[$field]['name']) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
        return null;
    }
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $fname = $field . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
    if (move_uploaded_file($_FILES[$field]['tmp_name'], rtrim($dir, '/') . '/' . $fname)) {
        return 'uploads/' . $fname;
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hotelName = trim($_POST['hotel_name'] ?? 'Nikosubila Lodge');
    $tagline = trim($_POST['tagline'] ?? '');
    $footerText = trim($_POST['footer_text'] ?? '');
    $primaryColor = trim($_POST['primary_color'] ?? '#0a2618');
    $accentColor = trim($_POST['accent_color'] ?? '#d4a843');
    $cardColor = trim($_POST['card_color'] ?? '#0e2d1d');
    $currency = trim($_POST['currency'] ?? '₭');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $facebook = trim($_POST['facebook'] ?? '');
    $instagram = trim($_POST['instagram'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    $xAccount = trim($_POST['x_account'] ?? '');
    $bankName = trim($_POST['bank_name'] ?? '');
    $bankAccount = trim($_POST['bank_account'] ?? '');
    $bankBranch = trim($_POST['bank_branch'] ?? '');
    $sheetId = trim($_POST['google_sheet_id'] ?? '');
    $sheetWebhook = trim($_POST['google_sheet_webhook'] ?? '');

    $uploadDir = __DIR__ . '/../uploads';
    $bannerImg = upload_img('banner_file', $uploadDir) ?? ($settings['banner_image'] ?? '');
    $logoImg = upload_img('logo_file', $uploadDir) ?? ($settings['logo_image'] ?? '');

    $stmt = $conn->prepare("UPDATE site_settings SET
        hotel_name=?, tagline=?, footer_text=?, banner_image=?, logo_image=?,
        primary_color=?, accent_color=?, card_color=?, currency=?,
        phone=?, email=?, facebook=?, instagram=?, linkedin=?, x_account=?,
        bank_name=?, bank_account=?, bank_branch=?,
        google_sheet_id=?, google_sheet_webhook=?
        WHERE id=1");
    $stmt->bind_param(
        "ssssssssssssssssssss",
        $hotelName, $tagline, $footerText, $bannerImg, $logoImg,
        $primaryColor, $accentColor, $cardColor, $currency,
        $phone, $email, $facebook, $instagram, $linkedin, $xAccount,
        $bankName, $bankAccount, $bankBranch,
        $sheetId, $sheetWebhook
    );
    $stmt->execute();
    $stmt->close();

    $msg = 'ບັນທຶກການຕັ້ງຄ່າເວັບໄຊສຳເລັດແລ້ວ!';
    $sRes = $conn->query("SELECT * FROM site_settings WHERE id = 1 LIMIT 1");
    if ($sRes && $sRow = $sRes->fetch_assoc()) {
        $settings = array_merge($defaultSettings, $sRow);
    }
}
?>
<!DOCTYPE html>
<html lang="lo">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ຕັ້ງຄ່າເວັບໄຊ - <?= e($settings['hotel_name']) ?></title>
<style>
:root { --panel: <?= e($settings['primary_color']) ?>; --card: <?= e($settings['card_color']) ?>; --accent: <?= e($settings['accent_color']) ?>; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Noto Sans Lao', sans-serif; background: #061810; color: #f4f7f5; }
.layout { display: flex; min-height: 100vh; }
.sidebar { width: 235px; background: var(--panel); border-right: 1px solid rgba(212,168,67,0.3); padding: 18px 14px; }
.sidebar h2 { color: var(--accent); font-size: 1.1rem; text-align: center; margin-bottom: 14px; }
.sidebar a { display: block; padding: 9px 12px; border-radius: 8px; color: #d6e2dc; text-decoration: none; margin-bottom: 4px; font-size: 0.88rem; }
.sidebar a:hover, .sidebar a.active { background: rgba(212,168,67,0.18); color: var(--accent); font-weight: 700; }
.main { flex: 1; padding: 20px; }
.card { background: var(--card); border: 1px solid rgba(212,168,67,0.28); border-radius: 12px; padding: 18px; margin-bottom: 16px; }
.grid3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
label { display: block; font-size: 0.78rem; color: #9eb3a8; margin-bottom: 5px; }
input { width: 100%; padding: 8px 10px; border-radius: 7px; border: 1px solid rgba(212,168,67,0.35); background: #061810; color: #fff; font-family: inherit; }
.btn-gold { background: var(--accent); color: #061810; font-weight: 700; padding: 10px 18px; border-radius: 8px; border: none; cursor: pointer; }
.ok { background: rgba(34,197,94,0.15); border: 1px solid #22c55e; color: #86efac; padding: 10px; border-radius: 8px; margin-bottom: 12px; }
@media (max-width: 900px) { .layout { flex-direction: column; } .grid3 { grid-template-columns: 1fr; } }
</style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <h2><?= e($settings['hotel_name']) ?></h2>
        <a href="index.php">Dashboard ສະຖິຕິ</a>
        <a href="bookings_manage.php">ຈັດການການຈອງ</a>
        <a href="rooms_manage.php">ຈັດການຫ້ອງພັກ</a>
        <a href="site_settings.php" class="active">ຕັ້ງຄ່າເວັບໄຊ</a>
        <a href="../index.php">ເບິ່ງໜ້າຫຼັກ</a>
        <a href="logout.php">ອອກຈາກລະບົບ</a>
    </aside>
    <main class="main">
        <h1 style="font-size:1.3rem;margin-bottom:14px;">Site Settings (ປັບແຕ່ງກຣາຟິກ, ຊື່, Banner, ສີທີມ &amp; Sheets)</h1>
        <?php if ($msg): ?><div class="ok"><?= e($msg) ?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="card">
                <h3 style="margin-bottom:10px;color:var(--accent);">ຂໍ້ມູນໂຮງແຮມ &amp; ອັບໂຫຼດຮູບ (uploads/)</h3>
                <div class="grid3">
                    <div><label>ຊື່ໂຮງແຮມ</label><input type="text" name="hotel_name" value="<?= e($settings['hotel_name']) ?>" required></div>
                    <div><label>Tagline</label><input type="text" name="tagline" value="<?= e($settings['tagline']) ?>"></div>
                    <div><label>Currency</label><input type="text" name="currency" value="<?= e($settings['currency']) ?>"></div>
                    <div><label>Banner Image</label><input type="file" name="banner_file" accept="image/*"></div>
                    <div><label>Logo Image</label><input type="file" name="logo_file" accept="image/*"></div>
                    <div><label>Footer Text</label><input type="text" name="footer_text" value="<?= e($settings['footer_text']) ?>"></div>
                </div>
            </div>

            <div class="card">
                <h3 style="margin-bottom:10px;color:var(--accent);">ສີທີມ (Theme Colors)</h3>
                <div class="grid3">
                    <div><label>Sidebar / Panel Color</label><input type="color" name="primary_color" value="<?= e($settings['primary_color']) ?>" style="height:40px;"></div>
                    <div><label>Accent / Gold Color</label><input type="color" name="accent_color" value="<?= e($settings['accent_color']) ?>" style="height:40px;"></div>
                    <div><label>Card Color</label><input type="color" name="card_color" value="<?= e($settings['card_color']) ?>" style="height:40px;"></div>
                </div>
            </div>

            <div class="card">
                <h3 style="margin-bottom:10px;color:var(--accent);">ຕິດຕໍ່, ທະນາຄານ &amp; Google Sheets</h3>
                <div class="grid3">
                    <div><label>Phone / WhatsApp</label><input type="text" name="phone" value="<?= e($settings['phone']) ?>"></div>
                    <div><label>Email</label><input type="text" name="email" value="<?= e($settings['email']) ?>"></div>
                    <div><label>Facebook</label><input type="text" name="facebook" value="<?= e($settings['facebook']) ?>"></div>
                    <div><label>Instagram</label><input type="text" name="instagram" value="<?= e($settings['instagram']) ?>"></div>
                    <div><label>LinkedIn</label><input type="text" name="linkedin" value="<?= e($settings['linkedin']) ?>"></div>
                    <div><label>X Account</label><input type="text" name="x_account" value="<?= e($settings['x_account']) ?>"></div>
                    <div><label>Bank Name</label><input type="text" name="bank_name" value="<?= e($settings['bank_name']) ?>"></div>
                    <div><label>Bank Account</label><input type="text" name="bank_account" value="<?= e($settings['bank_account']) ?>"></div>
                    <div><label>Bank Branch</label><input type="text" name="bank_branch" value="<?= e($settings['bank_branch']) ?>"></div>
                    <div><label>Google Sheet ID</label><input type="text" name="google_sheet_id" value="<?= e($settings['google_sheet_id']) ?>"></div>
                    <div style="grid-column:span 2;"><label>Google Sheets Webhook URL</label><input type="text" name="google_sheet_webhook" value="<?= e($settings['google_sheet_webhook']) ?>"></div>
                </div>
                <div style="margin-top:16px;text-align:right;">
                    <button type="submit" class="btn-gold">ບັນທຶກການຕັ້ງຄ່າ</button>
                </div>
            </div>
        </form>
    </main>
</div>
</body>
</html>
