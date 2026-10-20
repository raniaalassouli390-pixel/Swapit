<?php


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../jwt.php';
require_once __DIR__ . '/../../helpers/api.php';

$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;
$action = $_GET['action'] ?? '';

$authUser = requireAuth(); 

switch ($method) {

    
    case 'GET':
        $pdo        = db();
        $onlyUnread = !empty($_GET['unread']);

        $unreadStmt = $pdo->prepare(
            'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0'
        );
        $unreadStmt->execute([$authUser->id]);
        $unreadCount = (int) $unreadStmt->fetchColumn();

        $where  = 'WHERE user_id = ?';
        $params = [$authUser->id];
        if ($onlyUnread) {
            $where .= ' AND is_read = 0';
        }

        $stmt = $pdo->prepare("
            SELECT id, type, title, body, link, is_read, created_at
            FROM notifications
            $where
            ORDER BY created_at DESC
            LIMIT 50
        ");
        $stmt->execute($params);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($notifications as &$n) {
            $n['id']      = (int)  $n['id'];
            $n['is_read'] = (bool) $n['is_read'];
        }
        unset($n);

        jsonResponse([
            'ok'            => true,
            'unread_count'  => $unreadCount,
            'notifications' => $notifications,
        ]);
        break;

    
    case 'PUT':
        $pdo = db();

        if ($action === 'mark_all') {
            $pdo->prepare(
                'UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0'
            )->execute([$authUser->id]);
            jsonResponse(['ok' => true]);
        }

        if (!$id) {
            jsonError('Missing notification id (?id=X) or action (?action=mark_all)');
        }

        $stmt = $pdo->prepare('SELECT id, user_id FROM notifications WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $notif = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$notif || (int) $notif['user_id'] !== (int) $authUser->id) {
            jsonError('Notification not found', 404);
        }

        $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = ?')->execute([$id]);
        jsonResponse(['ok' => true]);
        break;

    default:
        jsonError('Method not allowed', 405);
}
