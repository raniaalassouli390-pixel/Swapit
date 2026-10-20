<?php

header('Content-Type: text/html; charset=utf-8');

$password = 'Demo@1234';
$email = 'admin@swapit.jo';
$hash = password_hash($password, PASSWORD_DEFAULT);

$configPath = __DIR__ . '/src/config/config.php';
if (!is_file($configPath)) {
    echo "<h2>config.php not found</h2>";
    echo "<p>Copy <code>src/config/config.example.php</code> to <code>src/config/config.php</code> and set your DB credentials first.</p>";
    echo "<h3>Or set password manually in phpMyAdmin</h3>";
    echo "<p>Run this SQL (password will be <strong>Demo@1234</strong>):</p>";
    echo "<pre>UPDATE users SET password = " . var_export($hash, true) . " WHERE email = " . var_export($email, true) . ";</pre>";
    exit;
}

require_once $configPath;
require_once __DIR__ . '/src/config/database.php';

echo "<h2>Set admin password</h2>";

try {
    $pdo = db();
    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
    $stmt->execute([$hash, $email]);
    $updated = $stmt->rowCount();

    if ($updated === 0) {
        $stmt = $pdo->query("SELECT email FROM users LIMIT 5");
        $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p><strong>No user found with email <code>" . htmlspecialchars($email) . "</code>.</strong></p>";
        echo "<p>Make sure you ran <code>database/schema.sql</code> and <code>database/seed.sql</code>. Existing emails: " . implode(', ', array_map('htmlspecialchars', $users ?: ['none'])) . "</p>";
        echo "<p>Or run this SQL in phpMyAdmin to set the password:</p>";
        echo "<pre>UPDATE users SET password = " . var_export($hash, true) . " WHERE email = " . var_export($email, true) . ";</pre>";
        exit;
    }

    
    $stmt = $pdo->prepare('SELECT password FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $verifyOk = password_verify($password, $row['password']);

    echo "<p><strong>Updated 1 user.</strong> Password for <code>" . htmlspecialchars($email) . "</code> is now: <code>" . htmlspecialchars($password) . "</code></p>";
    echo "<p>Verification test: " . ($verifyOk ? '<strong style="color:green">OK – login should work</strong>' : '<strong style="color:red">FAIL – hash mismatch</strong>') . "</p>";
    echo "<p>Try logging in again in Postman with this email and password, then delete this file.</p>";
} catch (Exception $e) {
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>You can set the password manually. Run this SQL in phpMyAdmin (password will be <strong>Demo@1234</strong>):</p>";
    echo "<pre>UPDATE users SET password = " . var_export($hash, true) . " WHERE email = " . var_export($email, true) . ";</pre>";
}
