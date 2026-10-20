<?php

$step = $_POST['step'] ?? '';
$msg = '';
$error = '';

if ($step === 'config') {
    $host   = $_POST['db_host'] ?? '127.0.0.1';
    $pass   = $_POST['db_pass'] ?? 'root';
    $user   = $_POST['db_user'] ?? 'root';
    $dbname = $_POST['db_name'] ?? 'swapit';
    $appurl = rtrim($_POST['app_url'] ?? 'http://localhost:8888', '/');

    $content = "<?php\ndefine('DB_HOST',    '$host');\ndefine('DB_NAME',    '$dbname');\ndefine('DB_CHARSET', 'utf8mb4');\ndefine('DB_USER',    '$user');\ndefine('DB_PASS',    '$pass');\ndefine('APP_URL', '$appurl');\ndefine('DEBUG',   true);\ndefine('JWT_SECRET', '" . bin2hex(random_bytes(32)) . "');\n";
    $configPath = __DIR__ . '/../src/config/config.php';
    if (file_put_contents($configPath, $content)) {
        $msg = '✅ config.php created!';
    } else {
        $error = '❌ Could not write config.php — check folder permissions.';
    }
}

if ($step === 'db') {
    $configPath = __DIR__ . '/../src/config/config.php';
    if (!file_exists($configPath)) { $error = '❌ Create config.php first (Step 1).'; }
    else {
        require_once $configPath;
        try {
            $pdo = new PDO("mysql:host=".DB_HOST.";charset=utf8mb4", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `".DB_NAME."` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `".DB_NAME."`");
            $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
            $seed   = file_get_contents(__DIR__ . '/../database/seed.sql');
            foreach (array_filter(array_map('trim', explode(';', $schema))) as $q) $pdo->exec($q);
            foreach (array_filter(array_map('trim', explode(';', $seed)))   as $q) $pdo->exec($q);
            $msg = '✅ Database created and seeded!';
        } catch (Exception $e) {
            $error = '❌ ' . $e->getMessage();
        }
    }
}

$configExists = file_exists(__DIR__ . '/../src/config/config.php');
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>SwapIt Setup</title>
<style>body{font-family:system-ui,sans-serif;max-width:560px;margin:60px auto;padding:0 20px;background:#f5f5f5}
h1{color:#2C6E49}fieldset{border:1px solid #ddd;border-radius:10px;padding:20px;margin-bottom:20px;background:#fff}
legend{font-weight:700;color:#2C6E49;padding:0 8px}label{display:block;margin:10px 0 4px;font-size:14px;font-weight:500}
input{width:100%;padding:9px 12px;border:1px solid #ddd;border-radius:7px;font-size:14px;box-sizing:border-box}
button{width:100%;padding:11px;background:#2C6E49;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;margin-top:12px}
.msg{background:#d4edda;color:#155724;padding:12px;border-radius:8px;margin-bottom:16px}
.err{background:#f8d7da;color:#721c24;padding:12px;border-radius:8px;margin-bottom:16px}
.done{opacity:.5;pointer-events:none}.go{display:block;text-align:center;margin-top:20px;padding:14px;background:#2C6E49;color:#fff;border-radius:10px;text-decoration:none;font-weight:700;font-size:16px}
</style></head><body>
<h1>🔄 SwapIt Setup</h1>
<?php if($msg): ?><div class="msg"><?= $msg ?></div><?php endif; ?>
<?php if($error): ?><div class="err"><?= $error ?></div><?php endif; ?>

<fieldset <?= $configExists ? 'class="done"' : '' ?>>
  <legend>Step 1 — Database Config <?= $configExists ? '✅' : '' ?></legend>
  <form method="post">
  <input type="hidden" name="step" value="config">
  <label>DB Host</label><input name="db_host" value="127.0.0.1">
  <label>DB User</label><input name="db_user" value="root">
  <label>DB Password <small>(MAMP default: root)</small></label><input name="db_pass" value="root">
  <label>DB Name</label><input name="db_name" value="swapit">
  <label>App URL</label><input name="app_url" value="http://localhost:8888">
  <button>Create config.php</button>
  </form>
</fieldset>

<fieldset>
  <legend>Step 2 — Create &amp; Seed Database</legend>
  <form method="post">
  <input type="hidden" name="step" value="db">
  <button <?= !$configExists ? 'disabled' : '' ?>>Create database + import schema + seed data</button>
  </form>
</fieldset>

<?php if($configExists): ?>
<a href="/src/config/pages/home.php" class="go">🚀 Open SwapIt →</a>
<?php endif; ?>
</body></html>
