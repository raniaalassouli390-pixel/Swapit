<?php


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../jwt.php';
require_once __DIR__ . '/../../helpers/api.php';

$adminUser = requireAdmin(); 

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;


$input = [];
if ($method === 'PUT') {
    $raw = file_get_contents('php://input');
    if ($raw && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
        $input = json_decode($raw, true) ?? [];
    } else {
        $input = $_POST;
    }
}

switch ("$method:$action") {

    
    case 'GET:stats':
        $row = db()->query('
            SELECT
                (SELECT COUNT(*)   FROM users)                                  AS total_users,
                (SELECT COUNT(*)   FROM users    WHERE status = "active")       AS active_users,
                (SELECT COUNT(*)   FROM listings WHERE status != "removed")     AS total_listings,
                (SELECT COUNT(*)   FROM auctions WHERE status = "live")         AS live_auctions,
                (SELECT COUNT(*)   FROM reports  WHERE status = "open")         AS open_reports,
                (SELECT COUNT(*)   FROM bids)                                   AS total_bids,
                (SELECT COUNT(*)   FROM swap_offers WHERE status = "accepted")  AS total_swaps
        ')->fetch(PDO::FETCH_ASSOC);

        jsonResponse([
            'ok'    => true,
            'stats' => [
                'total_users'    => (int) $row['total_users'],
                'active_users'   => (int) $row['active_users'],
                'total_listings' => (int) $row['total_listings'],
                'live_auctions'  => (int) $row['live_auctions'],
                'open_reports'   => (int) $row['open_reports'],
                'total_bids'     => (int) $row['total_bids'],
                'total_swaps'    => (int) $row['total_swaps'],
            ],
        ]);
        break;

    
    case 'GET:users':
        $pdo     = db();
        $page    = max(1, (int) ($_GET['page']   ?? 1));
        $search  = trim($_GET['search']  ?? '');
        $role    = trim($_GET['role']    ?? '');
        $status  = trim($_GET['status']  ?? '');
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;

        $where  = [];
        $params = [];

        if ($search !== '') {
            $where[]           = '(username LIKE :search OR email LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        if ($role !== '' && in_array($role, ['user', 'admin'], true)) {
            $where[]         = 'role = :role';
            $params[':role'] = $role;
        }

        if ($status !== '' && in_array($status, ['active', 'suspended', 'unverified'], true)) {
            $where[]           = 'status = :status';
            $params[':status'] = $status;
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users $whereSQL");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();
        $pages = (int) ceil($total / $perPage);

        $stmt = $pdo->prepare("
            SELECT id, username, first_name, last_name, email,
                   role, status, is_verified, created_at,
                   rating, review_count,
                   (SELECT COUNT(*) FROM listings l
                    WHERE l.user_id = users.id AND l.status != 'removed') AS listing_count
            FROM users
            $whereSQL
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($users as &$u) {
            $u['id']            = (int)   $u['id'];
            $u['is_verified']   = (bool)  $u['is_verified'];
            $u['rating']        = $u['rating'] !== null ? (float) $u['rating'] : null;
            $u['review_count']  = (int)   $u['review_count'];
            $u['listing_count'] = (int)   $u['listing_count'];
        }
        unset($u);

        jsonResponse([
            'ok'    => true,
            'users' => $users,
            'total' => $total,
            'page'  => $page,
            'pages' => $pages,
        ]);
        break;

    
    case 'PUT:user':
        if (!$id) {
            jsonError('Missing user id (?id=X)');
        }

        $pdo = db();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            jsonError('User not found', 404);
        }

        $isSelf = ($id === (int) $adminUser->id);

        $set    = [];
        $params = [];

        
        if (array_key_exists('status', $input)) {
            if ($isSelf) jsonError('Cannot change your own account status', 403);
            $status = trim((string) $input['status']);
            if (!in_array($status, ['active', 'suspended', 'unverified'], true)) {
                jsonError('status must be one of: active, suspended, unverified', 422);
            }
            $set[]    = 'status = ?';
            $params[] = $status;
        }

        
        if (array_key_exists('role', $input)) {
            if ($isSelf) jsonError('Cannot change your own role', 403);
            $role = trim((string) $input['role']);
            if (!in_array($role, ['user', 'admin'], true)) {
                jsonError('role must be either "user" or "admin"', 422);
            }
            $set[]    = 'role = ?';
            $params[] = $role;
        }

        
        if (array_key_exists('username', $input)) {
            $username = trim((string) $input['username']);
            if ($username === '' || strlen($username) > 50) {
                jsonError('username is required and must be 1–50 chars', 422);
            }
            $dup = $pdo->prepare('SELECT id FROM users WHERE username = ? AND id != ? LIMIT 1');
            $dup->execute([$username, $id]);
            if ($dup->fetch()) jsonError('Username already taken', 409);
            $set[]    = 'username = ?';
            $params[] = $username;
        }

        
        if (array_key_exists('email', $input)) {
            $email = trim((string) $input['email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                jsonError('Invalid email address', 422);
            }
            $dup = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
            $dup->execute([$email, $id]);
            if ($dup->fetch()) jsonError('Email already in use', 409);
            $set[]    = 'email = ?';
            $params[] = $email;
        }

        
        foreach (['first_name', 'last_name'] as $f) {
            if (array_key_exists($f, $input)) {
                $val = trim((string) $input[$f]);
                if (strlen($val) > 50) jsonError("$f must be ≤ 50 chars", 422);
                $set[]    = "$f = ?";
                $params[] = $val;
            }
        }

        if (array_key_exists('password', $input)) {
            $password = (string) $input['password'];
            if ($password !== '') {
                if (strlen($password) < 8) {
                    jsonError('password must be at least 8 characters', 422);
                }
                $set[]    = 'password = ?';
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }
        }

        if (!$set) {
            jsonError('Nothing to update. Provide at least one of: status, role, username, email, first_name, last_name, password.', 422);
        }

        $params[] = $id;
        $pdo->prepare('UPDATE users SET ' . implode(', ', $set) . ' WHERE id = ?')
            ->execute($params);

        jsonResponse(['ok' => true]);
        break;

    
    case 'DELETE:user':
        if (!$id) {
            jsonError('Missing user id (?id=X)');
        }
        if ($id === (int) $adminUser->id) {
            jsonError('Cannot delete your own admin account', 403);
        }

        $pdo  = db();
        $stmt = $pdo->prepare('SELECT id, role FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $target = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$target) jsonError('User not found', 404);

        
        
        if ($target['role'] === 'admin') {
            $remaining = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
            if ($remaining <= 1) jsonError('Cannot delete the last admin account', 403);
        }

        
        
        
        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);

        jsonResponse(['ok' => true]);
        break;

    
    case 'GET:listings':
        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $limit  = 20;
        $offset = ($page - 1) * $limit;

        $where  = [];
        $params = [];

        if (!empty($_GET['status'])) {
            $where[]  = 'listings.status = ?';
            $params[] = $_GET['status'];
        }

        if (!empty($_GET['search'])) {
            $where[]  = 'listings.title LIKE ?';
            $params[] = '%' . $_GET['search'] . '%';
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = db()->prepare("
            SELECT COUNT(*)
            FROM listings
            JOIN users ON users.id = listings.user_id
            $whereSQL
        ");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = db()->prepare("
            SELECT listings.*, users.username AS seller_username
            FROM listings
            JOIN users ON users.id = listings.user_id
            $whereSQL
            ORDER BY listings.created_at DESC
            LIMIT $limit OFFSET $offset
        ");
        $stmt->execute($params);
        $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        jsonResponse([
            'ok'       => true,
            'listings' => $listings,
            'total'    => $total,
            'page'     => $page,
            'pages'    => (int) ceil($total / $limit),
        ]);
        break;

    
    case 'DELETE:listing':
        if (!$id) {
            jsonError('Missing listing id (?id=X)');
        }

        $stmt = db()->prepare('SELECT id FROM listings WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            jsonError('Listing not found', 404);
        }

        db()->prepare("UPDATE listings SET status = 'removed' WHERE id = ?")
           ->execute([$id]);

        jsonResponse(['ok' => true]);
        break;

    
    case 'GET:reports':
        $pdo        = db();
        $page       = max(1, (int) ($_GET['page'] ?? 1));
        $status     = trim($_GET['status'] ?? 'open');
        $reportType = trim($_GET['report_type'] ?? '');
        $perPage    = max(1, min(50, (int) ($_GET['per_page'] ?? 20)));
        $offset     = ($page - 1) * $perPage;

        $where  = [];
        $params = [];

        if ($status !== '' && in_array($status, ['open', 'reviewing', 'resolved'], true)) {
            $where[]  = 'r.status = ?';
            $params[] = $status;
        }

        if ($reportType === 'listing') {
            $where[] = 'r.listing_id IS NOT NULL';
        } elseif ($reportType === 'user') {
            $where[] = 'r.reported_id IS NOT NULL';
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM reports r $whereSQL");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();
        $pages = (int) ceil($total / $perPage);

        $stmt = $pdo->prepare("
            SELECT r.*,
                   u1.username AS reporter_username,
                   u2.username AS reported_username,
                   l.title     AS listing_title
            FROM reports r
            LEFT JOIN users u1    ON u1.id = r.reporter_id
            LEFT JOIN users u2    ON u2.id = r.reported_id
            LEFT JOIN listings l  ON l.id  = r.listing_id
            $whereSQL
            ORDER BY r.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $bindParams = array_merge($params, [$perPage, $offset]);
        $stmt->execute($bindParams);
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($reports as &$r) {
            $r['id']             = (int) $r['id'];
            $r['reporter_id']    = (int) $r['reporter_id'];
            $r['reported_id']    = $r['reported_id'] ? (int) $r['reported_id'] : null;
            $r['listing_id']     = $r['listing_id']  ? (int) $r['listing_id']  : null;
            $r['report_type']    = $r['listing_id'] ? 'listing' : 'user';
            $r['target_id']      = $r['listing_id'] ?: $r['reported_id'];
            $r['reason']         = $r['type'];
            $r['reporter_name']  = $r['reporter_username'];
            $r['reported_name']  = $r['reported_username'];
            $r['listing_title']  = $r['listing_title'];
        }
        unset($r);

        jsonResponse([
            'ok'           => true,
            'reports'      => $reports,
            'total'        => $total,
            'page'         => $page,
            'pages'        => $pages,
            'total_pages'  => $pages,
        ]);
        break;

    
    case 'PUT:report':
        if (!$id) {
            jsonError('Missing report id (?id=X)');
        }

        $status = trim($input['status'] ?? '');
        if (!in_array($status, ['open', 'reviewing', 'resolved'], true)) {
            jsonError('status must be one of: open, reviewing, resolved', 422);
        }

        $stmt = db()->prepare('SELECT id FROM reports WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            jsonError('Report not found', 404);
        }

        db()->prepare('UPDATE reports SET status = ? WHERE id = ?')->execute([$status, $id]);

        jsonResponse(['ok' => true]);
        break;

    default:
        if (!$action) {
            jsonError('Missing action query param. E.g. ?action=stats');
        }
        jsonError("Unknown action: $action", 400);
}
