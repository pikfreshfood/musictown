<?php
// 🔽 EDIT THESE with your cPanel database details
$host = 'localhost';
$dbname = 'YOUR_DATABASE_NAME';
$username = 'YOUR_DB_USER';
$password = 'YOUR_DB_PASSWORD';
// 🔽 EDIT ABOVE

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $hash = password_hash('admin', PASSWORD_BCRYPT);
    $now = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['admin@musictown.test']);
    $existing = $stmt->fetch();

    if ($existing) {
        $pdo->prepare("UPDATE users SET name='admin', password=?, is_admin=1, is_premium=1, role='super_admin' WHERE email='admin@musictown.test'")->execute([$hash]);
        echo "Done! Admin password reset to: admin / admin. <a href='/admin/login'>Login here</a>";
    } else {
        $pdo->prepare("INSERT INTO users (name, email, phone, password, balance, is_admin, is_premium, role, created_at, updated_at) VALUES (?,?,?,?,0,1,1,?,?,?)")->execute(['admin', 'admin@musictown.test', '0000000000', $hash, 'super_admin', $now, $now]);
        echo "Done! Admin created with: admin / admin. <a href='/admin/login'>Login here</a>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
