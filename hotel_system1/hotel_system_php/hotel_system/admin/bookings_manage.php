<?php
require_once __DIR__ . '/../config/db.php';
check_admin('../login.php');

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['booking_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($id > 0 && in_array($action, ['Confirmed', 'Checked-in', 'Checked-out', 'Cancelled'], true)) {
        $q = $conn->prepare("SELECT room_id, status, booking_code FROM bookings WHERE id = ?");
        $q->bind_param("i", $id);
        $q->execute();
        $bk = $q->get_result()->fetch_assoc();
        $q->close();

        if ($bk) {
            $roomId = (int)$bk['room_id'];
            $prevStatus = $bk['status'];

            $upd = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $upd->bind_param("si", $action, $id);
            $upd->execute();
            $upd->close();

            if ($action === 'Checked-in' && $prevStatus !== 'Checked-in') {
                $conn->query("UPDATE rooms SET available_rooms = GREATEST(0, available_rooms - 1) WHERE id = {$roomId}");
            } elseif ($action === 'Checked-out' && $prevStatus === 'Checked-in') {
                $conn->query("UPDATE rooms SET available_rooms = LEAST(total_rooms, available_rooms + 1) WHERE id = {$roomId}");
            }
            $msg = "ປ່ຽນສະຖານະ {$bk['booking_code']} ເປັນ {$action} ແລ້ວ.";
        }
    } elseif ($action === 'delete' && $id > 0) {
        $del = $conn->prepare("DELETE FROM bookings WHERE id = ?");
        $del->bind_param("i", $id);
        $del->execute();
        $del->close();
        $msg = "ລົບລາຍການຈອງແລ້ວ.";
    }
}

$rows = [];
$res = $conn->query("SELECT b.*, r.room_name FROM bookings b LEFT JOIN rooms r ON b.room_id = r.id ORDER BY b.id DESC");
while ($row = $res->fetch_assoc()) {
    $rows[] = $row;
}
?>
<!DOCTYPE html>
<html lang="lo">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ຈັດການການຈອງ - <?= e($settings['hotel_name']) ?></title>
<style>
:root { --panel: <?= e($settings['primary_color']) ?>; --card: <?= e($settings['card_color']) ?>; --accent: <?= e($settings['accent_color']) ?>; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Noto Sans Lao', sans-serif; background: #061810; color: #f4f7f5; }
.layout { display: flex; min-height: 100vh; }
.sidebar { width: 235px; background: var(--panel); border-right: 1px solid rgba(212,168,67,0.3); padding: 18px 14px; }
.sidebar h2 { color: var(--accent); font-size: 1.1rem; text-align: center; margin-bottom: 14px; }
.sidebar a { display: block; padding: 9px 12px; border-radius: 8px; color: #d6e2dc; text-decoration: none; margin-bottom: 4px; font-size: 0.88rem; }
.sidebar a:hover, .sidebar a.active { background: rgba(212,168,67,0.18); color: var(--accent); font-weight: 700; }
.main { flex: 1; padding: 20px; overflow-x: auto; }
.card { background: var(--card); border: 1px solid rgba(212,168,67,0.28); border-radius: 12px; padding: 16px; }
table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
th, td { padding: 9px 10px; border-bottom: 1px solid rgba(255,255,255,0.08); text-align: left; }
th { color: var(--accent); }
.btn { padding: 4px 8px; border-radius: 6px; border: 1px solid var(--accent); background: transparent; color: var(--accent); cursor: pointer; font-size: 0.75rem; }
.btn-gold { background: var(--accent); color: #061810; font-weight: 700; }
.ok { background: rgba(34,197,94,0.15); border: 1px solid #22c55e; color: #86efac; padding: 10px; border-radius: 8px; margin-bottom: 12px; }
@media (max-width: 900px) { .layout { flex-direction: column; } }
</style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <h2><?= e($settings['hotel_name']) ?></h2>
        <a href="index.php">Dashboard ສະຖິຕິ</a>
        <a href="bookings_manage.php" class="active">ຈັດການການຈອງ</a>
        <a href="rooms_manage.php">ຈັດການຫ້ອງພັກ</a>
        <a href="site_settings.php">ຕັ້ງຄ່າເວັບໄຊ</a>
        <a href="../index.php">ເບິ່ງໜ້າຫຼັກ</a>
        <a href="logout.php">ອອກຈາກລະບົບ</a>
    </aside>
    <main class="main">
        <h1 style="font-size:1.3rem;margin-bottom:14px;">ຈັດການການຈອງ (Check-in / Check-out)</h1>
        <?php if ($msg): ?><div class="ok"><?= e($msg) ?></div><?php endif; ?>
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>ລະຫັດ</th><th>ຜູ້ຈອງ</th><th>ເບີໂທ</th><th>ຫ້ອງ</th><th>ວັນທີ</th><th>ຍອດລວມ</th><th>ສະຖານະ</th><th>ຈັດການ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $b): ?>
                        <tr>
                            <td><strong><?= e($b['booking_code']) ?></strong></td>
                            <td><?= e($b['guest_name']) ?></td>
                            <td><?= e($b['guest_phone']) ?></td>
                            <td><?= e($b['room_name'] ?? '') ?></td>
                            <td><?= e($b['check_in']) ?> → <?= e($b['check_out']) ?></td>
                            <td style="color:var(--accent);"><?= money_fmt((float)$b['total_amount'], $settings['currency']) ?></td>
                            <td><strong><?= e($b['status']) ?></strong></td>
                            <td>
                                <form method="POST" style="display:flex;gap:4px;flex-wrap:wrap;">
                                    <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                                    <button type="submit" name="action" value="Checked-in" class="btn btn-gold">Check-in</button>
                                    <button type="submit" name="action" value="Checked-out" class="btn">Check-out</button>
                                    <button type="submit" name="action" value="Confirmed" class="btn">Confirm</button>
                                    <button type="submit" name="action" value="delete" class="btn" style="border-color:#ef4444;color:#fca5a5;">ລົບ</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
