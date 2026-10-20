<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../jwt.php';
require_once __DIR__ . '/../../helpers/api.php';

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

$input = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    if ($raw && (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false)) {
        $input = json_decode($raw, true) ?? [];
    } else {
        $input = $_POST;
    }
}

function jsonResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function jsonError(string $message, int $code = 400): void
{
    jsonResponse(['ok' => false, 'error' => $message], $code);
}

function generate_verify_code(): string
{
    return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function sendVerificationEmail(string $toEmail, string $toName, string $code): bool
{
    $verifyPage = rtrim(APP_URL ?? 'https://swapit.com', '/') . '/src/config/pages/verify.php';
    $subject = 'Verify your SwapIt account';

    $plainText = implode("\n", [
        "Hi {$toName},",
        '',
        'Thanks for joining SwapIt! Your verification code is:',
        $code,
        '',
        "Enter this code on: {$verifyPage}",
        '',
        'This code expires in 24 hours.',
        '',
        'If you did not create this account, you can safely ignore this email.',
        '',
        '— The SwapIt Team',
    ]);

    $safeCode = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
    $safePage = htmlspecialchars($verifyPage, ENT_QUOTES, 'UTF-8');
    $safeName  = htmlspecialchars($toName,    ENT_QUOTES, 'UTF-8');
    $htmlBody  = "<!DOCTYPE html><html lang='en'><body style='font-family:Arial,sans-serif;background:#f4f4f5;padding:40px;'><div style='background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);'><h2>Hi {$safeName},</h2><p>Your verification code is:</p><p style='font-size:28px;font-weight:700;letter-spacing:4px'>{$safeCode}</p><p>Enter it at <a href='{$safePage}'>{$safePage}</a></p></div></body></html>";

    $boundary = '----=_Part_' . bin2hex(random_bytes(8));
    $headers  = implode("\r\n", [
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        'From: SwapIt <no-reply@swapit.com>',
        'Reply-To: no-reply@swapit.com',
        'X-Mailer: PHP/' . PHP_VERSION,
    ]);

    $body = implode("\r\n", [
        '--' . $boundary,
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 7bit',
        '',
        $plainText,
        '',
        '--' . $boundary,
        'Content-Type: text/html; charset=UTF-8',
        'Content-Transfer-Encoding: 7bit',
        '',
        $htmlBody,
        '',
        '--' . $boundary . '--',
    ]);

    $sent = @mail($toEmail, $subject, $body, $headers);

    if (!$sent) {
        error_log(sprintf('[SwapIt][auth] Verification email failed for %s', $toEmail));
    }

    return $sent;
}



function normalize_login_email(string $email): string
{
    return strtolower(trim($email));
}

function ensure_login_attempts_table(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `login_attempts` (
            `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `email`        VARCHAR(150) NOT NULL,
            `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_email_time` (`email`, `attempted_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $hasEmail = (bool) $pdo->query("SHOW COLUMNS FROM `login_attempts` LIKE 'email'")->fetch();
    if (!$hasEmail) {
        $pdo->exec("ALTER TABLE `login_attempts` ADD COLUMN `email` VARCHAR(150) NULL AFTER `id`");
        $pdo->exec("ALTER TABLE `login_attempts` ADD INDEX `idx_email_time` (`email`, `attempted_at`)");
    }
}

function check_rate_limit(PDO $pdo, string $email): void
{
    $email      = normalize_login_email($email);
    $max        = 5;
    $window_sec = 900;

    try {
        ensure_login_attempts_table($pdo);

        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM `login_attempts`
            WHERE `email` = ? AND `attempted_at` > NOW() - INTERVAL ? SECOND
        ");
        $stmt->execute([$email, $window_sec]);

        if ((int) $stmt->fetchColumn() >= $max) {
            jsonResponse([
                'ok'          => false,
                'error'       => 'Too many failed login attempts for this account. Please try again in 15 minutes.',
                'retry_after' => $window_sec,
            ], 429);
        }
    } catch (PDOException $e) {
        error_log('[SwapIt][auth] Rate limit check failed: ' . $e->getMessage());
    }
}

function record_failed_attempt(PDO $pdo, string $email): void
{
    try {
        ensure_login_attempts_table($pdo);
        $pdo->prepare("INSERT INTO `login_attempts` (`email`) VALUES (?)")
            ->execute([normalize_login_email($email)]);
    } catch (PDOException $e) {
        error_log('[SwapIt][auth] Could not record failed attempt: ' . $e->getMessage());
    }
}

function clear_attempts(PDO $pdo, string $email): void
{
    try {
        ensure_login_attempts_table($pdo);
        $pdo->prepare("DELETE FROM `login_attempts` WHERE `email` = ?")
            ->execute([normalize_login_email($email)]);
    } catch (PDOException $e) {
        error_log('[SwapIt][auth] Could not clear attempts: ' . $e->getMessage());
    }
}



switch ($action) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonError('Method not allowed', 405);
        }

        $email    = trim($input['email']    ?? '');
        $password = $input['password']       ?? '';

        if (!$email || !$password) {
            jsonError('Email and password are required');
        }

        try {
            $pdo = db();

            
            check_rate_limit($pdo, $email);

            
            $stmt = $pdo->prepare('
                SELECT id, username, first_name, last_name, email,
                       password, role, status, is_verified
                FROM users WHERE email = ? LIMIT 1
            ');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            
            if (!$user || !password_verify($password, $user['password'])) {
                record_failed_attempt($pdo, $email);
                jsonError('Invalid email or password', 401);
            }

            
            if ($user['status'] === 'suspended') {
                jsonError('Your account is deactivated. Please try again later or contact the admin.', 403);
            }

            if ($user['status'] === 'unverified' || (int) $user['is_verified'] === 0) {
                jsonError('Please verify your email before logging in.', 403);
            }

            
            clear_attempts($pdo, $email);

            unset($user['password'], $user['is_verified']);
            $token = createJWT($user);

            jsonResponse([
                'ok'    => true,
                'token' => $token,
                'user'  => [
                    'id'       => (int) $user['id'],
                    'username' => $user['username'],
                    'name'     => trim($user['first_name'] . ' ' . ($user['last_name'] ?? '')),
                    'email'    => $user['email'],
                    'role'     => $user['role'],
                ],
            ]);
        } catch (PDOException $e) {
            jsonError('Service temporarily unavailable', 500);
        }
        break;

    case 'register':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonError('Method not allowed', 405);
        }

        $email      = trim($input['email']      ?? '');
        $password   = $input['password']         ?? '';
        $username   = trim($input['username']   ?? '');
        $first_name = trim($input['first_name'] ?? $input['firstName'] ?? '');
        $last_name  = trim($input['last_name']  ?? $input['lastName']  ?? '');

        if (!$email || !$password || !$username || !$first_name) {
            jsonError('Email, password, username and first name are required');
        }

        if (strlen($password) < 8) {
            jsonError('Password must be at least 8 characters');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonError('Invalid email format');
        }

        try {
            $pdo = db();
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
            $stmt->execute([$email, $username]);
            if ($stmt->fetch()) {
                jsonError('Email or username already registered');
            }

            $hash       = password_hash($password, PASSWORD_DEFAULT);
            $verifyCode = generate_verify_code();

            $stmt = $pdo->prepare('INSERT INTO users (username, first_name, last_name, email, password, role, status, verify_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$username, $first_name, $last_name, $email, $hash, 'user', 'unverified', $verifyCode]);

            $id       = (int) $pdo->lastInsertId();
            $userData = [
                'id'         => $id,
                'username'   => $username,
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'email'      => $email,
                'role'       => 'user',
            ];
            $token = createJWT($userData);

            $displayName = trim($first_name . ' ' . $last_name);
            $emailSent   = sendVerificationEmail($email, $displayName, $verifyCode);

            $message = $emailSent
                ? 'Account created! Please check your email to verify your account.'
                : 'Account created, but we could not send the verification email. Please contact support.';

            $payload = [
                'ok'         => true,
                'message'    => $message,
                'email_sent' => $emailSent,
                'token'      => $token,
                'user'       => [
                    'id'       => $id,
                    'username' => $username,
                    'name'     => $displayName,
                    'email'    => $email,
                    'role'     => 'user',
                ],
            ];
            if (DEBUG && !$emailSent) {
                $payload['dev_verify_code'] = $verifyCode;
            }
            jsonResponse($payload, 201);
        } catch (PDOException $e) {
            error_log('[SwapIt][auth][register] ' . $e->getMessage());
            jsonError(DEBUG ? 'Database error: ' . $e->getMessage() : 'Database error: Service temporarily unavailable', 500);
        }
        break;

    case 'logout':
        jsonResponse(['ok' => true, 'message' => 'Logged out. Client should discard token.']);
        break;

    
    
    

    case 'verify':
        $email = trim($input['email'] ?? '');
        $code  = trim($input['code'] ?? $input['token'] ?? $_GET['token'] ?? '');

        if (!$code) {
            jsonError('Verification code is required.', 422);
        }

        try {
            $pdo = db();

            if ($email) {
                $stmt = $pdo->prepare('
                    SELECT id, first_name, email, status, is_verified, verify_token
                    FROM users
                    WHERE email = ? AND verify_token = ?
                    LIMIT 1
                ');
                $stmt->execute([$email, $code]);
            } else {
                $stmt = $pdo->prepare('
                    SELECT id, first_name, email, status, is_verified, verify_token
                    FROM users
                    WHERE verify_token = ?
                    LIMIT 1
                ');
                $stmt->execute([$code]);
            }
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                jsonError('Invalid or expired verification code.', 400);
            }

            if ((int) $user['is_verified'] === 1) {
                jsonResponse([
                    'ok'               => true,
                    'message'          => 'Your email is already verified. Please log in.',
                    'already_verified' => true,
                ]);
            }

            $pdo->prepare('
                UPDATE users
                SET is_verified  = 1,
                    status       = "active",
                    verify_token = NULL,
                    updated_at   = NOW()
                WHERE id = ?
            ')->execute([$user['id']]);

            jsonResponse([
                'ok'      => true,
                'message' => 'Email verified successfully! You can now log in.',
            ]);

        } catch (PDOException $e) {
            jsonError('Service temporarily unavailable', 500);
        }
        break;

    
    

    case 'resend':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonError('Method not allowed', 405);
        }

        $email = trim($input['email'] ?? '');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonError('A valid email address is required.', 422);
        }

        try {
            $pdo = db();

            $stmt = $pdo->prepare('
                SELECT id, first_name, last_name, is_verified, status
                FROM users WHERE email = ? LIMIT 1
            ');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            
            if (!$user || (int) $user['is_verified'] === 1) {
                jsonResponse([
                    'ok'      => true,
                    'message' => 'If this email exists and is unverified, a new link has been sent.',
                ]);
            }

            $newCode = generate_verify_code();

            $pdo->prepare('
                UPDATE users
                SET verify_token = ?, updated_at = NOW()
                WHERE id = ?
            ')->execute([$newCode, $user['id']]);

            $displayName = trim($user['first_name'] . ' ' . ($user['last_name'] ?? ''));
            $emailSent   = sendVerificationEmail($email, $displayName, $newCode);

            $payload = [
                'ok'      => true,
                'message' => 'If this email exists and is unverified, a new link has been sent.',
            ];
            if (DEBUG && !$emailSent) {
                $payload['dev_verify_code'] = $newCode;
            }
            jsonResponse($payload);

        } catch (PDOException $e) {
            jsonError('Service temporarily unavailable', 500);
        }
        break;

    
    

    case 'forgot':
    case 'forgot_password':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonError('Method not allowed', 405);
        }
        $email = trim($input['email'] ?? '');
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonError('A valid email is required');
        }
        try {
            $pdo  = db();
            $stmt = $pdo->prepare('SELECT id, first_name FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $resetLink = null;
            $mailSent  = false;
            if ($user) {
                $resetToken = bin2hex(random_bytes(32));
                $pdo->prepare('UPDATE users SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?')
                    ->execute([$resetToken, $user['id']]);
                $resetLink = rtrim(APP_URL ?? 'https://swapit.com', '/')
                    . '/src/config/pages/reset-password.php?token=' . urlencode($resetToken);
                $subject   = 'Reset your SwapIt password';
                $body      = "Hi {$user['first_name']},\n\nClick this link to reset your password:\n{$resetLink}\n\nThis link expires in 1 hour.\n\nIf you did not request this, ignore this email.\n\n— The SwapIt Team";
                $mailSent  = @mail($email, $subject, $body, 'From: no-reply@swapit.com');
            }

            $payload = ['ok' => true, 'message' => 'If this email is registered, a reset link has been sent.'];
            if (DEBUG && $resetLink && !$mailSent) {
                $payload['dev_reset_link'] = $resetLink;
            }
            jsonResponse($payload);
        } catch (PDOException $e) {
            jsonError('Service temporarily unavailable', 500);
        }
        break;

    
    

    case 'reset':
    case 'reset_password':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonError('Method not allowed', 405);
        }
        $resetToken  = $input['token']    ?? '';
        $newPassword = $input['password'] ?? '';
        if (!$resetToken || strlen($newPassword) < 8) {
            jsonError('Invalid token or password too short (min 8 characters)');
        }
        try {
            $pdo  = db();
            $stmt = $pdo->prepare('SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1');
            $stmt->execute([$resetToken]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                jsonError('Invalid or expired reset token', 400);
            }
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL, updated_at = NOW() WHERE id = ?')
                ->execute([$hash, $user['id']]);
            jsonResponse(['ok' => true, 'message' => 'Password updated successfully. You can now log in.']);
        } catch (PDOException $e) {
            jsonError('Service temporarily unavailable', 500);
        }
        break;

    default:
        jsonError('Invalid action', 400);
}
