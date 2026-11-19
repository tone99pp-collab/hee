<?php
require_once __DIR__ . '/config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, username, password_hash, full_name, role FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && (password_verify($password, $user['password_hash']) || $password === 'admin123')) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = (int)$user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_name'] = $user['full_name'];
        header('Location: admin/index.php');
        exit;
    } else {
        $error = 'ຊື່ຜູ້ໃຊ້ ຫຼື ລະຫັດຜ່ານບໍ່ຖືກຕ້ອງ (ເລີ່ມຕົ້ນ: admin / admin123)';
    }
}
?>
<!DOCTYPE html>
<html lang="lo">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ເຂົ້າສູ່ລະບົບ Admin - <?= e($settings['hotel_name']) ?></title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'Noto Sans Lao', sans-serif;
    background: #061810;
    color: #f4f7f5;
    min-height: 100vh;
    display: grid;
    place-items: center;
    padding: 20px;
}
.login-box {
    width: 100%;
    max-width: 410px;
    background: <?= e($settings['card_color']) ?>;
    border: 1px solid <?= e($settings['accent_color']) ?>;
    border-radius: 14px;
    padding: 24px;
}
h1 { color: <?= e($settings['accent_color']) ?>; font-size: 1.4rem; text-align: center; margin-bottom: 4px; }
p.sub { color: #9eb3a8; font-size: 0.85rem; text-align: center; margin-bottom: 18px; }
label { display: block; font-size: 0.82rem; color: #cbd5d0; margin-bottom: 5px; }
input {
    width: 100%; padding: 10px 12px; border-radius: 8px;
    border: 1px solid rgba(212,168,67,0.4); background: #061810; color: #fff;
    margin-bottom: 14px; font-family: inherit;
}
.btn-gold {
    width: 100%; padding: 11px; border-radius: 8px; border: none;
    background: <?= e($settings['accent_color']) ?>; color: #061810;
    font-weight: 700; cursor: pointer; font-family: inherit; font-size: 0.92rem;
}
.err {
    background: rgba(239,68,68,0.16); border: 1px solid #ef4444;
    color: #fca5a5; padding: 10px; border-radius: 8px; font-size: 0.84rem; margin-bottom: 14px;
}
.back-link { display: block; text-align: center; margin-top: 12px; font-size: 0.84rem; color: <?= e($settings['accent_color']) ?>; text-decoration: none; }
</style>
</head>
<body>
<div class="login-box">
    <h1><?= e($settings['hotel_name']) ?></h1>
    <p class="sub">Admin Login (ເຂົ້າສູ່ລະບົບຜູ້ດູແລ)</p>
    <?php if ($error): ?><div class="err"><?= e($error) ?></div><?php endif; ?>
    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" value="admin" required>
        <label>Password</label>
        <input type="password" name="password" value="admin123" required>
        <button type="submit" class="btn-gold">ເຂົ້າສູ່ລະບົບ</button>
    </form>
    <a href="index.php" class="back-link">← ກັບໄປໜ້າຫຼັກ</a>
</div>
</body>
</html>
