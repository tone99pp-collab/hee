<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/sheets_api.php';

$rooms = [];
$rRes = $conn->query("SELECT * FROM rooms ORDER BY id ASC");
while ($r = $rRes->fetch_assoc()) {
    $rooms[] = $r;
}

$selectedRoomId = (int)($_GET['room_id'] ?? ($rooms[0]['id'] ?? 1));
$checkIn = $_GET['check_in'] ?? date('Y-m-d');
$checkOut = $_GET['check_out'] ?? date('Y-m-d', strtotime('+2 days'));

$message = '';
$error = '';
$receipt = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guestName = trim($_POST['guest_name'] ?? '');
    $guestPhone = trim($_POST['guest_phone'] ?? '');
    $guestEmail = trim($_POST['guest_email'] ?? '');
    $roomId = (int)($_POST['room_id'] ?? 0);
    $checkIn = $_POST['check_in'] ?? date('Y-m-d');
    $checkOut = $_POST['check_out'] ?? date('Y-m-d', strtotime('+1 day'));
    $adults = max(1, (int)($_POST['adults'] ?? 2));
    $children = max(0, (int)($_POST['children'] ?? 0));
    $paymentMethod = trim($_POST['payment_method'] ?? 'Bank Transfer');
    $requests = trim($_POST['special_requests'] ?? '');

    $rStmt = $conn->prepare("SELECT * FROM rooms WHERE id = ? LIMIT 1");
    $rStmt->bind_param("i", $roomId);
    $rStmt->execute();
    $roomInfo = $rStmt->get_result()->fetch_assoc();
    $rStmt->close();

    $t1 = strtotime($checkIn);
    $t2 = strtotime($checkOut);
    $nights = ($t1 && $t2 && $t2 > $t1) ? (int)ceil(($t2 - $t1) / 86400) : 1;

    if ($guestName === '' || $guestPhone === '' || !$roomInfo) {
        $error = 'ກະລຸນາປ້ອນຊື່ຜູ້ຈອງ, ເບີໂທ ແລະ ເລືອກຫ້ອງພັກ.';
    } else {
        $total = (float)$roomInfo['price_per_night'] * $nights;
        $code = 'BK-' . date('Ymd') . '-' . rand(1000, 9999);

        $ins = $conn->prepare("INSERT INTO bookings (booking_code, guest_name, guest_phone, guest_email, room_id, check_in, check_out, adults, children, payment_method, special_requests, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
        $ins->bind_param("ssssissiiisd", $code, $guestName, $guestPhone, $guestEmail, $roomId, $checkIn, $checkOut, $adults, $children, $paymentMethod, $requests, $total);
        $ins->execute();
        $ins->close();

        $receipt = [
            'booking_code' => $code,
            'guest_name' => $guestName,
            'guest_phone' => $guestPhone,
            'room_name' => $roomInfo['room_name'],
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'nights' => $nights,
            'total_amount' => $total
        ];

        $sheetSync = push_to_google_sheets($receipt, $settings);
        $extra = $sheetSync['success'] ? ' (Synced to Google Sheets)' : '';
        $message = "ບັນທຶກການຈອງສຳເລັດ! ລະຫັດການຈອງ: {$code}{$extra}";
    }
}
?>
<!DOCTYPE html>
<html lang="lo">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ຈອງຫ້ອງພັກ - <?= e($settings['hotel_name']) ?></title>
<style>
:root { --panel: <?= e($settings['primary_color']) ?>; --card: <?= e($settings['card_color']) ?>; --accent: <?= e($settings['accent_color']) ?>; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Noto Sans Lao', sans-serif; background: #061810; color: #f4f7f5; padding: 20px; }
.wrap { max-width: 960px; margin: 0 auto; }
.card { background: var(--card); border: 1px solid rgba(212,168,67,0.3); border-radius: 14px; padding: 20px; margin-bottom: 16px; }
.grid2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
label { display: block; font-size: 0.8rem; color: #9eb3a8; margin-bottom: 5px; }
input, select, textarea {
    width: 100%; padding: 9px 11px; border-radius: 8px;
    border: 1px solid rgba(212,168,67,0.35); background: #061810; color: #fff; font-family: inherit;
}
.btn-gold { background: var(--accent); color: #061810; font-weight: 700; padding: 10px 18px; border-radius: 8px; border: none; cursor: pointer; text-decoration: none; display: inline-block; }
.btn-link { color: var(--accent); text-decoration: none; font-size: 0.88rem; }
.ok { background: rgba(34,197,94,0.15); border: 1px solid #22c55e; color: #86efac; padding: 12px; border-radius: 8px; margin-bottom: 14px; }
.err { background: rgba(239,68,68,0.15); border: 1px solid #ef4444; color: #fca5a5; padding: 12px; border-radius: 8px; margin-bottom: 14px; }
@media (max-width: 720px) { .grid2 { grid-template-columns: 1fr; } }
</style>
</head>
<body>
<div class="wrap">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <h2 style="color:var(--accent);"><?= e($settings['hotel_name']) ?> — ຈອງຫ້ອງພັກ</h2>
        <a href="index.php" class="btn-link">← ກັບໄປໜ້າຫຼັກ</a>
    </div>

    <?php if ($message): ?><div class="ok"><?= e($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="err"><?= e($error) ?></div><?php endif; ?>

    <div class="card">
        <form method="POST" class="grid2">
            <div>
                <label>ຊື່ ແລະ ນາມສະກຸນ *</label>
                <input type="text" name="guest_name" required placeholder="ສົມໃຈ ວົງສະຫວັດ">
            </div>
            <div>
                <label>ເບີໂທລະສັບ *</label>
                <input type="text" name="guest_phone" required placeholder="+856 20 ...">
            </div>
            <div>
                <label>Email</label>
                <input type="email" name="guest_email" placeholder="guest@example.com">
            </div>
            <div>
                <label>ປະເພດຫ້ອງພັກ *</label>
                <select name="room_id">
                    <?php foreach ($rooms as $rm): ?>
                        <option value="<?= (int)$rm['id'] ?>" <?= ((int)$rm['id'] === $selectedRoomId) ? 'selected' : '' ?>>
                            <?= e($rm['room_name']) ?> (<?= money_fmt((float)$rm['price_per_night'], $settings['currency']) ?>/night)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Check In</label>
                <input type="date" name="check_in" value="<?= e($checkIn) ?>" required>
            </div>
            <div>
                <label>Check Out</label>
                <input type="date" name="check_out" value="<?= e($checkOut) ?>" required>
            </div>
            <div>
                <label>Adults</label>
                <input type="number" name="adults" value="2" min="1">
            </div>
            <div>
                <label>Children</label>
                <input type="number" name="children" value="1" min="0">
            </div>
            <div style="grid-column:span 2;">
                <label>ຊ່ອງທາງຊຳລະເງິນ</label>
                <select name="payment_method">
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="QR Code">QR Code</option>
                    <option value="Mobile Payment">Mobile Payment</option>
                    <option value="Pay at Hotel">Pay at Hotel</option>
                </select>
            </div>
            <div style="grid-column:span 2;">
                <label>ຄວາມຕ້ອງການເພີ່ມເຕີມ</label>
                <textarea name="special_requests" rows="3" placeholder="ເຊັ່ນ: ໝອນນຸ່ມ, ອາຫານເຈ..."></textarea>
            </div>
            <div style="grid-column:span 2;text-align:right;">
                <button type="submit" class="btn-gold">ຢືນຢັນການຈອງ</button>
            </div>
        </form>
    </div>

    <?php if ($receipt): ?>
        <div class="card" style="border-color:#22c55e;">
            <h3 style="color:#4ade80;margin-bottom:8px;">ລາຍລະອຽດການຈອງ</h3>
            <div>ລະຫັດ: <strong><?= e($receipt['booking_code']) ?></strong></div>
            <div>ຜູ້ຈອງ: <?= e($receipt['guest_name']) ?> (<?= e($receipt['guest_phone']) ?>)</div>
            <div>ຫ້ອງພັກ: <?= e($receipt['room_name']) ?> | <?= (int)$receipt['nights'] ?> ຄືນ</div>
            <div>ຍອດລວມ: <strong style="color:var(--accent);"><?= money_fmt((float)$receipt['total_amount'], $settings['currency']) ?></strong></div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
