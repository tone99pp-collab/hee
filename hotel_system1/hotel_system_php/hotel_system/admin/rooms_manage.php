<?php
require_once __DIR__ . '/../config/db.php';
check_admin('../login.php');

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    if ($act === 'add') {
        $name = trim($_POST['room_name'] ?? '');
        $type = trim($_POST['room_type'] ?? 'Deluxe');
        $price = (float)($_POST['price_per_night'] ?? 1000000);
        $total = max(1, (int)($_POST['total_rooms'] ?? 4));
        $avail = max(0, min($total, (int)($_POST['available_rooms'] ?? $total)));
        $guests = max(1, (int)($_POST['max_guests'] ?? 2));
        $status = $avail >= 3 ? 'Available' : ($avail > 0 ? "{$avail} Room Left" : 'Full');
        $desc = trim($_POST['description'] ?? '');
        if ($name !== '') {
            $stmt = $conn->prepare("INSERT INTO rooms (room_name, room_type, price_per_night, total_rooms, available_rooms, max_guests, status, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdiiiss", $name, $type, $price, $total, $avail, $guests, $status, $desc);
            $stmt->execute();
            $stmt->close();
            $msg = "ເພີ່ມຫ້ອງພັກ '{$name}' ແລ້ວ.";
        }
    } elseif ($act === 'update') {
        $id = (int)($_POST['room_id'] ?? 0);
        $price = (float)($_POST['price_per_night'] ?? 0);
        $total = max(1, (int)($_POST['total_rooms'] ?? 1));
        $avail = max(0, min($total, (int)($_POST['available_rooms'] ?? 0)));
        $status = trim($_POST['status'] ?? 'Available');
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE rooms SET price_per_night=?, total_rooms=?, available_rooms=?, status=? WHERE id=?");
            $stmt->bind_param("diisi", $price, $total, $avail, $status, $id);
            $stmt->execute();
            $stmt->close();
            $msg = "ອັບເດດຫ້ອງພັກແລ້ວ.";
        }
    } elseif ($act === 'delete') {
        $id = (int)($_POST['room_id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM rooms WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $msg = "ລົບຫ້ອງພັກແລ້ວ.";
        }
    }
}

$rooms = [];
$res = $conn->query("SELECT * FROM rooms ORDER BY id ASC");
while ($row = $res->fetch_assoc()) {
    $rooms[] = $row;
}
?>
<!DOCTYPE html>
<html lang="lo">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ຈັດການຫ້ອງພັກ - <?= e($settings['hotel_name']) ?></title>
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
.card { background: var(--card); border: 1px solid rgba(212,168,67,0.28); border-radius: 12px; padding: 16px; margin-bottom: 16px; }
.grid4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
label { display: block; font-size: 0.78rem; color: #9eb3a8; margin-bottom: 4px; }
input { width: 100%; padding: 8px 10px; border-radius: 7px; border: 1px solid rgba(212,168,67,0.35); background: #061810; color: #fff; font-family: inherit; }
.btn-gold { background: var(--accent); color: #061810; font-weight: 700; padding: 8px 14px; border-radius: 7px; border: none; cursor: pointer; }
table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
th, td { padding: 8px; border-bottom: 1px solid rgba(255,255,255,0.08); text-align: left; }
th { color: var(--accent); }
.ok { background: rgba(34,197,94,0.15); border: 1px solid #22c55e; color: #86efac; padding: 10px; border-radius: 8px; margin-bottom: 12px; }
@media (max-width: 900px) { .layout { flex-direction: column; } .grid4 { grid-template-columns: 1fr; } }
</style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <h2><?= e($settings['hotel_name']) ?></h2>
        <a href="index.php">Dashboard ສະຖິຕິ</a>
        <a href="bookings_manage.php">ຈັດການການຈອງ</a>
        <a href="rooms_manage.php" class="active">ຈັດການຫ້ອງພັກ</a>
        <a href="site_settings.php">ຕັ້ງຄ່າເວັບໄຊ</a>
        <a href="../index.php">ເບິ່ງໜ້າຫຼັກ</a>
        <a href="logout.php">ອອກຈາກລະບົບ</a>
    </aside>
    <main class="main">
        <h1 style="font-size:1.3rem;margin-bottom:14px;">ຈັດການຫ້ອງພັກ</h1>
        <?php if ($msg): ?><div class="ok"><?= e($msg) ?></div><?php endif; ?>

        <div class="card">
            <h3 style="margin-bottom:10px;">+ ເພີ່ມຫ້ອງພັກໃໝ່</h3>
            <form method="POST" class="grid4">
                <input type="hidden" name="action" value="add">
                <div><label>ຊື່ຫ້ອງ</label><input type="text" name="room_name" required></div>
                <div><label>ປະເພດ</label><input type="text" name="room_type" value="Deluxe"></div>
                <div><label>ລາຄາ/ຄືນ</label><input type="number" name="price_per_night" value="1200000"></div>
                <div><label>ຈຳນວນທັງໝົດ</label><input type="number" name="total_rooms" value="5"></div>
                <div><label>ຈຳນວນວ່າງ</label><input type="number" name="available_rooms" value="5"></div>
                <div><label>ແຂກສູງສຸດ</label><input type="number" name="max_guests" value="2"></div>
                <div style="grid-column:span 2;"><label>ລາຍລະອຽດ</label><input type="text" name="description"></div>
                <div style="grid-column:span 4;text-align:right;"><button type="submit" class="btn-gold">ເພີ່ມຫ້ອງພັກ</button></div>
            </form>
        </div>

        <div class="card" style="overflow-x:auto;">
            <table>
                <thead><tr><th>ຊື່ຫ້ອງ</th><th>ລາຄາ/ຄືນ</th><th>ວ່າງ/ທັງໝົດ</th><th>ສະຖານະ</th><th>ຈັດການ</th></tr></thead>
                <tbody>
                    <?php foreach ($rooms as $r): ?>
                        <tr>
                            <form method="POST">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="room_id" value="<?= (int)$r['id'] ?>">
                                <td><strong><?= e($r['room_name']) ?></strong></td>
                                <td><input type="number" name="price_per_night" value="<?= (float)$r['price_per_night'] ?>" style="width:110px;"></td>
                                <td>
                                    <input type="number" name="available_rooms" value="<?= (int)$r['available_rooms'] ?>" style="width:58px;"> /
                                    <input type="number" name="total_rooms" value="<?= (int)$r['total_rooms'] ?>" style="width:58px;">
                                </td>
                                <td><input type="text" name="status" value="<?= e($r['status']) ?>" style="width:115px;"></td>
                                <td style="display:flex;gap:6px;">
                                    <button type="submit" class="btn-gold">ບັນທຶກ</button>
                            </form>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="room_id" value="<?= (int)$r['id'] ?>">
                                        <button type="submit" style="padding:7px 10px;border-radius:7px;border:1px solid #ef4444;background:transparent;color:#fca5a5;cursor:pointer;">ລົບ</button>
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
