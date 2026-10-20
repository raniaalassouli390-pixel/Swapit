<?php


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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
$convId = isset($_GET['conv_id']) ? (int) $_GET['conv_id'] : null;


$input = [];
if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    if ($raw && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
        $input = json_decode($raw, true) ?? [];
    } else {
        $input = $_POST;
    }
}

switch ($method) {

    
    case 'GET':
        $authUser = requireAuth();
        $db       = db();

        if ($convId) {
            

            
            $page   = max(1, (int) ($_GET['page']  ?? 1));
            $limit  = min(100, max(1, (int) ($_GET['limit'] ?? 30)));
            $offset = ($page - 1) * $limit;

            
            $convStmt = $db->prepare('
                SELECT c.id, c.user1_id, c.user2_id, c.listing_id,
                       l.title AS listing_title,
                       other_u.id         AS other_id,
                       other_u.username   AS other_username,
                       other_u.avatar     AS other_avatar,
                       other_u.first_name AS other_first_name
                FROM conversations c
                LEFT JOIN listings l ON l.id = c.listing_id
                JOIN users other_u ON other_u.id = IF(c.user1_id = ?, c.user2_id, c.user1_id)
                WHERE c.id = ?
                LIMIT 1
            ');
            $convStmt->execute([$authUser->id, $convId]);
            $conv = $convStmt->fetch(PDO::FETCH_ASSOC);

            if (!$conv) jsonError('Not found or access denied', 403);

            if ((int) $conv['user1_id'] !== (int) $authUser->id &&
                (int) $conv['user2_id'] !== (int) $authUser->id) {
                jsonError('Not found or access denied', 403);
            }

            
            $countStmt = $db->prepare('SELECT COUNT(*) FROM messages WHERE conversation_id = ?');
            $countStmt->execute([$convId]);
            $totalMessages = (int) $countStmt->fetchColumn();
            $totalPages    = (int) ceil($totalMessages / $limit);

            
            $msgStmt = $db->prepare('
                SELECT m.id, m.body, m.is_read, m.created_at,
                       u.id       AS sender_id,
                       u.username AS sender_username,
                       u.avatar   AS sender_avatar
                FROM messages m
                JOIN users u ON u.id = m.sender_id
                WHERE m.conversation_id = ?
                ORDER BY m.created_at ASC
                LIMIT ? OFFSET ?
            ');
            $msgStmt->execute([$convId, $limit, $offset]);
            $messages = $msgStmt->fetchAll(PDO::FETCH_ASSOC);

            
            $db->prepare('
                UPDATE messages SET is_read = 1
                WHERE conversation_id = ? AND sender_id != ? AND is_read = 0
            ')->execute([$convId, $authUser->id]);

            $shaped = array_map(fn($msg) => [
                'id'         => (int)  $msg['id'],
                'body'       => $msg['body'],
                'is_read'    => (bool) $msg['is_read'],
                'created_at' => $msg['created_at'],
                'is_mine'    => (int) $msg['sender_id'] === (int) $authUser->id,
                'sender'     => [
                    'id'       => (int) $msg['sender_id'],
                    'username' => $msg['sender_username'],
                    'avatar'   => $msg['sender_avatar'],
                ],
            ], $messages);

            jsonResponse([
                'ok'           => true,
                'conversation' => [
                    'id'         => (int) $conv['id'],
                    'listing_id' => $conv['listing_id'] ? (int) $conv['listing_id'] : null,
                    'listing'    => $conv['listing_title'] ? [
                        'id'    => (int) $conv['listing_id'],
                        'title' => $conv['listing_title'],
                    ] : null,
                    'other_user' => [
                        'id'         => (int) $conv['other_id'],
                        'username'   => $conv['other_username'],
                        'avatar'     => $conv['other_avatar'],
                        'first_name' => $conv['other_first_name'],
                    ],
                ],
                'messages'   => $shaped,
                'pagination' => [
                    'page'        => $page,
                    'limit'       => $limit,
                    'total'       => $totalMessages,
                    'total_pages' => $totalPages,
                    'has_next'    => $page < $totalPages,
                    'has_prev'    => $page > 1,
                ],
            ]);
        }

        

        $stmt = $db->prepare('
            SELECT
                c.id, c.listing_id, c.updated_at,
                l.title  AS listing_title,
                l.status AS listing_status,
                other_u.id         AS other_id,
                other_u.username   AS other_username,
                other_u.avatar     AS other_avatar,
                other_u.first_name AS other_first_name,

                (SELECT COUNT(*) FROM messages m
                 WHERE m.conversation_id = c.id
                   AND m.sender_id != ?
                   AND m.is_read = 0) AS unread_count,

                (SELECT body FROM messages m2
                 WHERE m2.conversation_id = c.id
                 ORDER BY m2.created_at DESC LIMIT 1) AS last_message,

                (SELECT sender_id FROM messages m3
                 WHERE m3.conversation_id = c.id
                 ORDER BY m3.created_at DESC LIMIT 1) AS last_sender_id,

                (SELECT created_at FROM messages m4
                 WHERE m4.conversation_id = c.id
                 ORDER BY m4.created_at DESC LIMIT 1) AS last_message_at

            FROM conversations c
            LEFT JOIN listings l ON l.id = c.listing_id
            JOIN users other_u ON other_u.id = IF(c.user1_id = ?, c.user2_id, c.user1_id)
            WHERE c.user1_id = ? OR c.user2_id = ?
            ORDER BY c.updated_at DESC
        ');
        $stmt->execute([$authUser->id, $authUser->id, $authUser->id, $authUser->id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalUnreadStmt = $db->prepare('
            SELECT COUNT(*) FROM messages m
            JOIN conversations c ON c.id = m.conversation_id
            WHERE (c.user1_id = ? OR c.user2_id = ?)
              AND m.sender_id != ? AND m.is_read = 0
        ');
        $totalUnreadStmt->execute([$authUser->id, $authUser->id, $authUser->id]);
        $totalUnread = (int) $totalUnreadStmt->fetchColumn();

        $conversations = array_map(fn($row) => [
            'id'         => (int)  $row['id'],
            'updated_at' => $row['updated_at'],
            'other_user' => [
                'id'         => (int) $row['other_id'],
                'username'   => $row['other_username'],
                'avatar'     => $row['other_avatar'],
                'first_name' => $row['other_first_name'],
            ],
            'listing' => $row['listing_id'] ? [
                'id'     => (int) $row['listing_id'],
                'title'  => $row['listing_title'],
                'status' => $row['listing_status'],
            ] : null,
            'last_message' => $row['last_message'] ? [
                'preview' => mb_strlen($row['last_message']) > 60
                    ? mb_substr($row['last_message'], 0, 57) . '...'
                    : $row['last_message'],
                'sent_at' => $row['last_message_at'],
                'is_mine' => (int) $row['last_sender_id'] === (int) $authUser->id,
            ] : null,
            'unread_count' => (int) $row['unread_count'],
        ], $rows);

        jsonResponse([
            'ok'            => true,
            'total_unread'  => $totalUnread,
            'conversations' => $conversations,
        ]);
        break;

    
    case 'POST':
        $authUser = requireAuth();
        $db       = db();

        $body    = trim($input['body'] ?? '');
        $conv_id = isset($input['conv_id']) ? (int) $input['conv_id'] : null;

        if (!$body)                  jsonError('Message body is required.');
        if (mb_strlen($body) > 2000) jsonError('Message body must be 2000 characters or fewer.');

        
        if ($conv_id) {
            $convStmt = $db->prepare('
                SELECT id, user1_id, user2_id FROM conversations WHERE id = ? LIMIT 1
            ');
            $convStmt->execute([$conv_id]);
            $conv = $convStmt->fetch(PDO::FETCH_ASSOC);

            if (!$conv) jsonError('Conversation not found', 404);
            if ((int) $conv['user1_id'] !== (int) $authUser->id &&
                (int) $conv['user2_id'] !== (int) $authUser->id) {
                jsonError('Access denied', 403);
            }

            try {
                $db->beginTransaction();

                $db->prepare('INSERT INTO messages (conversation_id, sender_id, body) VALUES (?, ?, ?)')
                   ->execute([$conv_id, $authUser->id, $body]);
                $msgId = (int) $db->lastInsertId();

                $db->prepare('UPDATE conversations SET updated_at = NOW() WHERE id = ?')
                   ->execute([$conv_id]);

                $db->commit();
                jsonResponse(['ok' => true, 'message_id' => $msgId, 'conv_id' => $conv_id]);

            } catch (PDOException $e) {
                $db->rollBack();
                jsonError('Service temporarily unavailable', 500);
            }
        }

        
        $receiver_id = isset($input['receiver_id']) ? (int) $input['receiver_id']
            : (isset($input['recipient_id']) ? (int) $input['recipient_id'] : null);
        $listing_id  = isset($input['listing_id'])  ? (int) $input['listing_id']  : null;

        if (!$receiver_id) jsonError('Either conv_id or receiver_id is required.');
        if ($receiver_id === (int) $authUser->id) jsonError('You cannot message yourself.', 422);

        $recvStmt = $db->prepare('SELECT id FROM users WHERE id = ? AND status = "active" LIMIT 1');
        $recvStmt->execute([$receiver_id]);
        if (!$recvStmt->fetch()) jsonError('Receiver not found', 404);

        $existStmt = $db->prepare('
            SELECT id FROM conversations
            WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)
            LIMIT 1
        ');
        $existStmt->execute([$authUser->id, $receiver_id, $receiver_id, $authUser->id]);
        $existing = $existStmt->fetch(PDO::FETCH_ASSOC);

        try {
            $db->beginTransaction();

            if ($existing) {
                $conv_id = (int) $existing['id'];
            } else {
                $db->prepare('INSERT INTO conversations (user1_id, user2_id, listing_id) VALUES (?, ?, ?)')
                   ->execute([$authUser->id, $receiver_id, $listing_id]);
                $conv_id = (int) $db->lastInsertId();
            }

            $db->prepare('INSERT INTO messages (conversation_id, sender_id, body) VALUES (?, ?, ?)')
               ->execute([$conv_id, $authUser->id, $body]);
            $msgId = (int) $db->lastInsertId();

            $db->prepare('UPDATE conversations SET updated_at = NOW() WHERE id = ?')
               ->execute([$conv_id]);

            $db->prepare("
                INSERT INTO notifications (user_id, type, title, body, link, is_read, created_at)
                VALUES (?, 'message', 'New Message', ?, ?, 0, NOW())
            ")->execute([
                $receiver_id,
                mb_strlen($body) > 60 ? mb_substr($body, 0, 57) . '...' : $body,
                "/pages/chat.php?conv_id={$conv_id}",
            ]);

            $db->commit();
            jsonResponse(['ok' => true, 'message_id' => $msgId, 'conv_id' => $conv_id], 201);

        } catch (PDOException $e) {
            $db->rollBack();
            jsonError('Service temporarily unavailable', 500);
        }
        break;

    default:
        jsonError('Method not allowed', 405);
}
