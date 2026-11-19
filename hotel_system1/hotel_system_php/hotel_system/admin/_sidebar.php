<?php
$active_page = $active_page ?? 'dashboard';
$pending_count = 0;
$p_res = $conn->query("SELECT COUNT(*) AS cnt FROM bookings WHERE status = 'Pending'");
if ($p_res && $row = $p_res->fetch_assoc()) {
    $pending_count = (int)$row['cnt'];
}
?>
<aside class="sidebar" id="sidebarNav">
    <div>
        <div class="brand-box">
            <?php if (!empty($settings['logo_image']) && file_exists(__DIR__ . '/../' . $settings['logo_image'])): ?>
                <img src="../<?= h($settings['logo_image']) ?>" alt="Logo" class="brand-logo-img">
            <?php else: ?>
                <div class="brand-logo-img" style="display:grid;place-items:center;background:#133825;color:var(--accent);font-weight:700;">LH</div>
            <?php endif; ?>
            <div class="brand-title"><?= h($settings['hotel_name']) ?></div>
            <div class="brand-sub">ADMIN CONSOLE</div>
        </div>

        <div class="nav-group">
            <div class="nav-label">ຈັດການລະບົບ</div>
            <a class="nav-link <?= $active_page === 'dashboard' ? 'active' : '' ?>" href="index.php">
                <span>Dashboard ສະຖິຕິ</span>
            </a>
            <a class="nav-link <?= $active_page === 'bookings' ? 'active' : '' ?>" href="bookings_manage.php">
                <span>ຈັດການການຈອງ</span>
                <?php if ($pending_count > 0): ?>
                    <span class="nav-badge"><?= $pending_count ?></span>
                <?php endif; ?>
            </a>
            <a class="nav-link <?= $active_page === 'rooms' ? 'active' : '' ?>" href="rooms_manage.php">
                <span>ຈັດການຫ້ອງພັກ</span>
            </a>
            <a class="nav-link <?= $active_page === 'settings' ? 'active' : '' ?>" href="site_settings.php">
                <span>ຕັ້ງຄ່າເວັບໄຊ (Settings)</span>
            </a>

            <div class="nav-label">ໜ້າເວັບໄຊຫຼັກ</div>
            <a class="nav-link" href="../index.php"><span>ເບິ່ງໜ້າຫຼັກ (Luxury)</span></a>
            <a class="nav-link" href="../booking_form.php"><span>ຟອມຈອງຫ້ອງພັກ</span></a>
            <a class="nav-link" href="logout.php"><span>ອອກຈາກລະບົບ (Logout)</span></a>
        </div>
    </div>

    <div class="sidebar-footer">
        <button type="button" class="sos-btn" onclick="alert('ສົ່ງສັນຍານ EMERGENCY SOS ແລ້ວ!')">
            EMERGENCY SOS
        </button>
        <div class="status-pill"><span class="dot-green"></span> MySQL Connected</div>
    </div>
</aside>
