<?php
require_once __DIR__ . '/config/db.php';

$rooms = [];
$rRes = $conn->query("SELECT * FROM rooms ORDER BY id ASC");
while ($r = $rRes->fetch_assoc()) {
    $rooms[] = $r;
}

$checkIn = $_GET['check_in'] ?? date('Y-m-d');
$checkOut = $_GET['check_out'] ?? date('Y-m-d', strtotime('+2 days'));
$guests = $_GET['guests'] ?? '2 Adults, 1 Child';

$heroBg = "linear-gradient(125deg, rgba(6,24,16,0.88), rgba(14,45,29,0.65)), url('https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1400&q=80') center/cover no-repeat";
if (!empty($settings['banner_image']) && file_exists(__DIR__ . '/' . $settings['banner_image'])) {
    $heroBg = "linear-gradient(125deg, rgba(6,24,16,0.86), rgba(14,45,29,0.62)), url('" . e($settings['banner_image']) . "') center/cover no-repeat";
}
?>
<!DOCTYPE html>
<html lang="lo">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($settings['hotel_name']) ?> - ໜ້າຫຼັກ</title>
<style>
:root {
    --bg-main: #061810;
    --panel: <?= e($settings['primary_color']) ?>;
    --card: <?= e($settings['card_color']) ?>;
    --accent: <?= e($settings['accent_color']) ?>;
    --text: #f4f7f5;
    --muted: #9eb3a8;
    --border: rgba(212, 168, 67, 0.28);
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Noto Sans Lao', sans-serif; background: var(--bg-main); color: var(--text); }
a { color: inherit; text-decoration: none; }
.layout { display: flex; min-height: 100vh; }
.sidebar {
    width: 240px; background: var(--panel); border-right: 1px solid var(--border);
    padding: 18px 14px; display: flex; flex-direction: column; justify-content: space-between;
    position: sticky; top: 0; height: 100vh;
}
.brand { text-align: center; padding-bottom: 14px; border-bottom: 1px solid var(--border); margin-bottom: 12px; }
.brand img { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 2px solid var(--accent); margin-bottom: 8px; }
.brand h2 { color: var(--accent); font-size: 1.15rem; }
.nav a {
    display: block; padding: 9px 12px; border-radius: 8px; margin-bottom: 4px;
    font-size: 0.88rem; color: #d8e4de;
}
.nav a:hover, .nav a.active { background: rgba(212,168,67,0.16); color: var(--accent); font-weight: 600; }
.sos { background: #991b1b; border: 1px solid #ef4444; color: #fff; padding: 9px; border-radius: 8px; text-align: center; font-weight: 700; font-size: 0.82rem; cursor: pointer; }
.main { flex: 1; display: flex; flex-direction: column; }
.topbar {
    background: var(--panel); border-bottom: 1px solid var(--border);
    padding: 10px 20px; display: flex; justify-content: space-between; align-items: center;
}
.btn-gold {
    background: var(--accent); color: #061810; font-weight: 700; padding: 9px 16px;
    border-radius: 8px; border: none; cursor: pointer; display: inline-block; font-size: 0.88rem;
}
.btn-outline {
    border: 1px solid var(--accent); color: var(--accent); padding: 7px 12px;
    border-radius: 8px; display: inline-block; font-size: 0.82rem; font-weight: 600;
}
.container { padding: 20px; display: grid; grid-template-columns: 2.1fr 1fr; gap: 18px; max-width: 1440px; width: 100%; margin: 0 auto; }
.hero {
    border-radius: 14px; border: 1px solid var(--border); padding: 24px;
    min-height: 280px; display: flex; flex-direction: column; justify-content: space-between;
    background: <?= $heroBg ?>;
}
.hero h1 { font-size: 2.2rem; color: #fff; text-shadow: 0 2px 12px rgba(0,0,0,0.65); }
.search-box {
    background: rgba(7, 27, 17, 0.94); border: 1px solid var(--border);
    border-radius: 10px; padding: 12px; margin-top: 16px;
    display: grid; grid-template-columns: repeat(3, 1fr) auto; gap: 10px; align-items: end;
}
label.lbl { display: block; font-size: 0.76rem; color: var(--muted); margin-bottom: 4px; }
input.inp {
    width: 100%; padding: 8px 10px; border-radius: 7px;
    border: 1px solid rgba(212,168,67,0.35); background: #05140d; color: #fff; font-family: inherit;
}
.card {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 14px; padding: 16px; margin-top: 18px;
}
.card:first-child { margin-top: 0; }
.card-title { font-size: 1rem; font-weight: 700; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; }
.rooms-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
.room-item {
    background: #081e13; border: 1px solid rgba(255,255,255,0.08);
    border-radius: 10px; padding: 12px; display: flex; flex-direction: column; justify-content: space-between; gap: 6px;
}
.room-price { color: var(--accent); font-weight: 700; font-size: 0.86rem; }
.room-avail { color: #4ade80; font-size: 0.76rem; }
.pay-grid { display: grid; grid-template-columns: 1fr 1fr 0.8fr; gap: 12px; }
.m-box { padding: 12px 6px; border-radius: 8px; text-align: center; font-weight: 800; font-size: 0.82rem; }
.contact-row { padding: 7px 0; border-bottom: 1px solid rgba(255,255,255,0.06); font-size: 0.84rem; }
.contact-row:last-child { border-bottom: none; }
footer { text-align: center; padding: 16px; color: var(--muted); font-size: 0.82rem; border-top: 1px solid rgba(255,255,255,0.07); margin-top: 20px; }
@media (max-width: 1050px) {
    .container { grid-template-columns: 1fr; }
    .rooms-grid { grid-template-columns: repeat(2, 1fr); }
    .pay-grid { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .layout { flex-direction: column; }
    .sidebar { width: 100%; height: auto; position: static; }
    .search-box { grid-template-columns: 1fr; }
}
</style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div>
            <div class="brand">
                <?php if (!empty($settings['logo_image']) && file_exists(__DIR__ . '/' . $settings['logo_image'])): ?>
                    <img src="<?= e($settings['logo_image']) ?>" alt="Logo"><br>
                <?php endif; ?>
                <h2><?= e($settings['hotel_name']) ?></h2>
                <div style="font-size:0.72rem;color:var(--muted);letter-spacing:2px;">LUXURY LODGE</div>
            </div>
            <nav class="nav">
                <a href="index.php" class="active">ໜ້າຫຼັກ (Overview)</a>
                <a href="booking_form.php">ຈອງຫ້ອງພັກ (Booking)</a>
                <a href="#rooms">ປະເພດຫ້ອງພັກ</a>
                <a href="#payments">ຊ່ອງທາງຊຳລະເງິນ</a>
                <a href="admin/index.php">Admin Dashboard</a>
                <a href="admin/bookings_manage.php">ຈັດການການຈອງ</a>
                <a href="admin/rooms_manage.php">ຈັດການຫ້ອງພັກ</a>
                <a href="admin/site_settings.php">ຕັ້ງຄ່າເວັບໄຊ</a>
                <?php if (!empty($_SESSION['admin_logged_in'])): ?>
                    <a href="admin/logout.php">ອອກຈາກລະບົບ</a>
                <?php else: ?>
                    <a href="login.php">ເຂົ້າສູ່ລະບົບ Admin</a>
                <?php endif; ?>
            </nav>
        </div>
        <div style="margin-top:14px;display:grid;gap:8px;">
            <button type="button" class="sos" onclick="alert('ສົ່ງສັນຍານ EMERGENCY SOS ແລ້ວ!')">EMERGENCY SOS</button>
            <div style="font-size:0.76rem;color:#4ade80;">● Online &amp; Connected</div>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            <div style="font-size:0.86rem;color:#4ade80;font-weight:600;">● Online — <?= e($settings['hotel_name']) ?></div>
            <div style="display:flex;gap:10px;align-items:center;">
                <a href="booking_form.php" class="btn-gold">ຈອງຫ້ອງພັກ</a>
                <a href="admin/index.php" class="btn-outline">Admin</a>
            </div>
        </header>

        <div class="container">
            <div>
                <section class="hero">
                    <div>
                        <div style="font-size:0.9rem;color:#e2ece7;">ຍິນດີຕ້ອນຮັບສູ່</div>
                        <h1><?= e($settings['hotel_name']) ?></h1>
                        <p style="color:#dce6e0;margin-top:4px;"><?= e($settings['tagline']) ?></p>
                        <div style="margin-top:12px;">
                            <a href="booking_form.php" class="btn-gold">Book Your Stay</a>
                        </div>
                    </div>

                    <form method="GET" action="#rooms" class="search-box">
                        <div>
                            <label class="lbl">Check In</label>
                            <input type="date" name="check_in" value="<?= e($checkIn) ?>" class="inp">
                        </div>
                        <div>
                            <label class="lbl">Check Out</label>
                            <input type="date" name="check_out" value="<?= e($checkOut) ?>" class="inp">
                        </div>
                        <div>
                            <label class="lbl">Guests</label>
                            <input type="text" name="guests" value="<?= e($guests) ?>" class="inp">
                        </div>
                        <button type="submit" class="btn-gold">ກວດສອບຫ້ອງ</button>
                    </form>
                </section>

                <section class="card" id="rooms">
                    <div class="card-title">
                        <span>Room Types &amp; Availability</span>
                        <a href="booking_form.php" style="font-size:0.82rem;color:var(--accent);">ຈອງເລີຍ →</a>
                    </div>
                    <div class="rooms-grid">
                        <?php foreach ($rooms as $room): ?>
                            <div class="room-item">
                                <div>
                                    <div style="font-weight:700;font-size:0.9rem;"><?= e($room['room_name']) ?></div>
                                    <div style="font-size:0.75rem;color:var(--muted);"><?= e($room['room_type']) ?></div>
                                </div>
                                <div class="room-price"><?= money_fmt((float)$room['price_per_night'], $settings['currency']) ?> / night</div>
                                <div class="room-avail">● <?= (int)$room['available_rooms'] ?>/<?= (int)$room['total_rooms'] ?> (<?= e($room['status']) ?>)</div>
                                <a href="booking_form.php?room_id=<?= (int)$room['id'] ?>&check_in=<?= urlencode($checkIn) ?>&check_out=<?= urlencode($checkOut) ?>" class="btn-outline" style="text-align:center;margin-top:4px;">ເລືອກຫ້ອງ</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="card" id="payments">
                    <div class="card-title"><span>Secure Payments</span></div>
                    <div class="pay-grid">
                        <div>
                            <div style="font-size:0.8rem;color:var(--muted);margin-bottom:6px;">Mobile Payment</div>
                            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;">
                                <div class="m-box" style="background:#eab308;color:#111;">BCEL One</div>
                                <div class="m-box" style="background:#dc2626;color:#fff;">LDB Trust</div>
                                <div class="m-box" style="background:#16a34a;color:#fff;">MMoney</div>
                            </div>
                        </div>
                        <div style="background:#081e13;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,0.07);font-size:0.8rem;">
                            <div style="color:var(--accent);font-weight:700;margin-bottom:4px;">Bank Transfer</div>
                            <div>Bank: <?= e($settings['bank_name']) ?></div>
                            <div>Account: <strong><?= e($settings['bank_account']) ?></strong></div>
                            <div>Branch: <?= e($settings['bank_branch']) ?></div>
                        </div>
                        <div style="background:#fff;color:#111;border-radius:10px;padding:10px;text-align:center;font-size:0.76rem;display:grid;place-items:center;">
                            <div>
                                <div style="font-weight:800;font-size:1rem;border:2px solid #111;padding:8px;margin-bottom:4px;">QR PAY</div>
                                Scan to Pay
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <aside>
                <div class="card">
                    <div class="card-title"><span>Contact Us</span></div>
                    <div class="contact-row"><strong>Phone / WhatsApp:</strong> <?= e($settings['phone']) ?></div>
                    <div class="contact-row"><strong>Facebook:</strong> <?= e($settings['facebook']) ?></div>
                    <div class="contact-row"><strong>Instagram:</strong> <?= e($settings['instagram']) ?></div>
                    <div class="contact-row"><strong>LinkedIn:</strong> <?= e($settings['linkedin']) ?></div>
                    <div class="contact-row"><strong>X Account:</strong> <?= e($settings['x_account']) ?></div>
                    <div class="contact-row"><strong>Email:</strong> <?= e($settings['email']) ?></div>
                </div>
            </aside>
        </div>

        <footer><?= e($settings['footer_text']) ?></footer>
    </div>
</div>
</body>
</html>
