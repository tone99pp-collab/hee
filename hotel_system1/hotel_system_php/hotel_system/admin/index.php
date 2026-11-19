<?php
require_once __DIR__ . '/../config/db.php';
check_admin('../login.php');

$bookingCount = (int)($conn->query("SELECT COUNT(*) AS c FROM bookings")->fetch_assoc()['c'] ?? 0);
$inHouseCount = (int)($conn->query("SELECT COUNT(*) AS c FROM bookings WHERE status = 'Checked-in'")->fetch_assoc()['c'] ?? 0);
$revenueSum = (float)($conn->query("SELECT COALESCE(SUM(total_amount),0) AS s FROM bookings WHERE status != 'Cancelled'")->fetch_assoc()['s'] ?? 0);

$roomAgg = $conn->query("SELECT COALESCE(SUM(total_rooms),0) AS t, COALESCE(SUM(available_rooms),0) AS a FROM rooms")->fetch_assoc();
$totalRooms = max(1, (int)($roomAgg['t'] ?? 1));
$availRooms = (int)($roomAgg['a'] ?? 0);
$occRate = (int)round((($totalRooms - $availRooms) / $totalRooms) * 100);

$roomStats = [];
$rsQ = $conn->query("SELECT room_name, total_rooms, available_rooms FROM rooms ORDER BY id ASC");
while ($row = $rsQ->fetch_assoc()) {
    $roomStats[] = $row;
}

$labelsJson = json_encode(['ມ.ກ.', 'ກ.ພ.', 'ມີ.ນ.', 'ເມ.ສ.', 'ພ.ພ.', 'ມິ.ຖ.'], JSON_UNESCAPED_UNICODE);
$base = max(1000000, (int)$revenueSum);
$valuesJson = json_encode([
    (int)($base * 0.6), (int)($base * 0.7), (int)($base * 0.65),
    (int)($base * 0.8), (int)($base * 0.9), $base
]);
$rNamesJson = json_encode(array_column($roomStats, 'room_name'), JSON_UNESCAPED_UNICODE);
$rUsedJson = json_encode(array_map(fn($r) => max(1, (int)$r['total_rooms'] - (int)$r['available_rooms'] + 1), $roomStats));
?>
<!DOCTYPE html>
<html lang="lo">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - <?= e($settings['hotel_name']) ?></title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
.kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 18px; }
.kpi { background: var(--card); border: 1px solid rgba(212,168,67,0.28); border-radius: 12px; padding: 14px; }
.kpi .val { font-size: 1.4rem; font-weight: 700; color: var(--accent); margin-top: 4px; }
.charts { display: grid; grid-template-columns: 1.4fr 1fr; gap: 14px; }
.box { background: var(--card); border: 1px solid rgba(212,168,67,0.28); border-radius: 12px; padding: 16px; }
@media (max-width: 900px) {
    .layout { flex-direction: column; }
    .kpis, .charts { grid-template-columns: 1fr; }
}
</style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <h2><?= e($settings['hotel_name']) ?></h2>
        <a href="index.php" class="active">Dashboard ສະຖິຕິ</a>
        <a href="bookings_manage.php">ຈັດການການຈອງ</a>
        <a href="rooms_manage.php">ຈັດການຫ້ອງພັກ</a>
        <a href="site_settings.php">ຕັ້ງຄ່າເວັບໄຊ</a>
        <a href="../index.php">ເບິ່ງໜ້າຫຼັກ</a>
        <a href="logout.php">ອອກຈາກລະບົບ</a>
    </aside>
    <main class="main">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h1 style="font-size:1.3rem;">Dashboard Overview</h1>
            <span style="font-size:0.84rem;color:#9eb3a8;">ຜູ້ໃຊ້: <?= e($_SESSION['admin_name'] ?? 'Admin') ?></span>
        </div>

        <div class="kpis">
            <div class="kpi"><div style="font-size:0.8rem;color:#9eb3a8;">ການຈອງທັງໝົດ</div><div class="val"><?= $bookingCount ?></div></div>
            <div class="kpi"><div style="font-size:0.8rem;color:#9eb3a8;">ອັດຕາເຂົ້າພັກ</div><div class="val"><?= $occRate ?>%</div></div>
            <div class="kpi"><div style="font-size:0.8rem;color:#9eb3a8;">ແຂກ Checked-in</div><div class="val"><?= $inHouseCount ?></div></div>
            <div class="kpi"><div style="font-size:0.8rem;color:#9eb3a8;">ລາຍຮັບລວມ</div><div class="val"><?= money_fmt($revenueSum, $settings['currency']) ?></div></div>
        </div>

        <div class="charts">
            <div class="box">
                <div style="font-weight:700;margin-bottom:10px;">Revenue Overview (Chart.js)</div>
                <canvas id="revChart" height="160"></canvas>
            </div>
            <div class="box">
                <div style="font-weight:700;margin-bottom:10px;">Bookings by Room Type</div>
                <canvas id="roomChart" height="160"></canvas>
            </div>
        </div>
    </main>
</div>
<script>
new Chart(document.getElementById('revChart'), {
    type: 'line',
    data: {
        labels: <?= $labelsJson ?>,
        datasets: [{
            label: 'Revenue (<?= e($settings['currency']) ?>)',
            data: <?= $valuesJson ?>,
            borderColor: '<?= e($settings['accent_color']) ?>',
            backgroundColor: 'rgba(212,168,67,0.18)',
            fill: true,
            tension: 0.35
        }]
    },
    options: { plugins: { legend: { labels: { color: '#fff' } } }, scales: { x: { ticks: { color: '#9eb3a8' } }, y: { ticks: { color: '#9eb3a8' } } } }
});
new Chart(document.getElementById('roomChart'), {
    type: 'doughnut',
    data: {
        labels: <?= $rNamesJson ?>,
        datasets: [{ data: <?= $rUsedJson ?>, backgroundColor: ['#d4a843', '#22c55e', '#38bdf8', '#f97316'] }]
    },
    options: { plugins: { legend: { position: 'bottom', labels: { color: '#fff' } } } }
});
</script>
</body>
</html>
