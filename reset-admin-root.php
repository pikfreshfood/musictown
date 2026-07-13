<?php
// Standalone script – no Laravel needed
$env = file_get_contents(__DIR__.'/.env');
preg_match('/DB_HOST=(.+)/', $env, $h);
preg_match('/DB_PORT=(.+)/', $env, $po);
preg_match('/DB_DATABASE=(.+)/', $env, $d);
preg_match('/DB_USERNAME=(.+)/', $env, $u);
preg_match('/DB_PASSWORD=(.+)/', $env, $p);

$host = trim($h[1] ?? '127.0.0.1');
$port = trim($po[1] ?? '3306');
$db   = trim($d[1] ?? '');
$user = trim($u[1] ?? 'root');
$pass = trim($p[1] ?? '');

if (!$db) die("Could not read DB_DATABASE from .env");

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $hash = password_hash('admin', PASSWORD_BCRYPT);
    $now = date('Y-m-d H:i:s');

    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['admin@musictown.test']);
    $existing = $stmt->fetch();

    if ($existing) {
        $sql = "UPDATE users SET name = 'admin', password = ?, is_admin = 1, is_premium = 1, role = 'super_admin' WHERE email = 'admin@musictown.test'";
        $pdo->prepare($sql)->execute([$hash]);
    } else {
        $sql = "INSERT INTO users (name, email, phone, password, balance, is_admin, is_premium, role, created_at, updated_at) VALUES (?, ?, ?, ?, 0, 1, 1, ?, ?, ?)";
        $pdo->prepare($sql)->execute(['admin', 'admin@musictown.test', '0000000000', $hash, 'super_admin', $now, $now]);
    }

    echo "Done! Admin credentials: admin / admin. <a href='/admin/login'>Login here</a>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
