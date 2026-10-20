<?php


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
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


$input = [];
if (in_array($method, ['POST', 'PUT'])) {
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

        $stmt = $db->prepare('
            SELECT
                s.id, s.status, s.message, s.cash_addition,
                s.created_at, s.updated_at,
                s.sender_id, s.receiver_id,

                offered.id    AS offered_id,
                offered.title AS offered_title,
                offered.price AS offered_price,
                offered.city  AS offered_city,

                wanted.id     AS wanted_id,
                wanted.title  AS wanted_title,
                wanted.price  AS wanted_price,
                wanted.city   AS wanted_city,

                sender.username   AS sender_username,
                sender.avatar     AS sender_avatar,
                receiver.username AS receiver_username,
                receiver.avatar   AS receiver_avatar,

                (SELECT path FROM listing_images li
                 WHERE li.listing_id = offered.id AND li.is_primary = 1 LIMIT 1) AS offered_image,
                (SELECT path FROM listing_images li
                 WHERE li.listing_id = wanted.id  AND li.is_primary = 1 LIMIT 1) AS wanted_image

            FROM swap_offers s
            JOIN listings AS offered  ON offered.id  = s.offered_listing
            JOIN listings AS wanted   ON wanted.id   = s.wanted_listing
            JOIN users    AS sender   ON sender.id   = s.sender_id
            JOIN users    AS receiver ON receiver.id = s.receiver_id

            WHERE s.sender_id = ? OR s.receiver_id = ?
            ORDER BY s.created_at DESC
        ');
        $stmt->execute([$authUser->id, $authUser->id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $received = [];
        $sent     = [];

        foreach ($rows as $row) {
            $shaped = [
                'id'            => (int)   $row['id'],
                'status'        => $row['status'],
                'message'       => $row['message'],
                'cash_addition' => (float) $row['cash_addition'],
                'created_at'    => $row['created_at'],
                'updated_at'    => $row['updated_at'],
                'offered_listing' => [
                    'id'    => (int) $row['offered_id'],
                    'title' => $row['offered_title'],
                    'price' => $row['offered_price'] !== null ? (float) $row['offered_price'] : null,
                    'city'  => $row['offered_city'],
                    'image' => asset_url($row['offered_image']),
                ],
                'wanted_listing' => [
                    'id'    => (int) $row['wanted_id'],
                    'title' => $row['wanted_title'],
                    'price' => $row['wanted_price'] !== null ? (float) $row['wanted_price'] : null,
                    'city'  => $row['wanted_city'],
                    'image' => asset_url($row['wanted_image']),
                ],
                'sender'   => ['username' => $row['sender_username'],   'avatar' => $row['sender_avatar']],
                'receiver' => ['username' => $row['receiver_username'], 'avatar' => $row['receiver_avatar']],
            ];

            if ((int) $row['sender_id'] === (int) $authUser->id) {
                $sent[] = $shaped;
            } else {
                $received[] = $shaped;
            }
        }

        jsonResponse(['ok' => true, 'received' => $received, 'sent' => $sent]);
        break;

    
    case 'POST':
        $authUser = requireAuth();

        $body               = json_decode(file_get_contents('php://input'), true) ?? [];
        $offered_listing_id = isset($body['offered_listing_id']) ? (int) $body['offered_listing_id'] : 0;
        $wanted_listing_id  = isset($body['wanted_listing_id'])  ? (int) $body['wanted_listing_id']  : 0;
        $message            = trim($body['message']       ?? '');
        $cash_addition      = isset($body['cash_addition']) ? $body['cash_addition'] : 0;

        if (!$offered_listing_id) jsonError('offered_listing_id is required', 422);
        if (!$wanted_listing_id)  jsonError('wanted_listing_id is required', 422);
        if ($offered_listing_id === $wanted_listing_id) jsonError('offered and wanted listings must be different', 422);
        if (!is_numeric($cash_addition) || (float) $cash_addition < 0) jsonError('cash_addition must be a non-negative number', 422);
        $cash_addition = (float) $cash_addition;

        $db = db();

        $lstStmt = $db->prepare("
            SELECT id, user_id, status, type FROM listings WHERE id IN (:offered_id, :wanted_id)
        ");
        $lstStmt->execute([':offered_id' => $offered_listing_id, ':wanted_id' => $wanted_listing_id]);
        $rows = $lstStmt->fetchAll(PDO::FETCH_OBJ);

        $listingMap = [];
        foreach ($rows as $row) { $listingMap[$row->id] = $row; }

        $offeredListing = $listingMap[$offered_listing_id] ?? null;
        $wantedListing  = $listingMap[$wanted_listing_id]  ?? null;

        if (!$offeredListing) jsonError('Offered listing not found', 404);
        if (!$wantedListing)  jsonError('Wanted listing not found', 404);

        if ((int) $offeredListing->user_id !== (int) $authUser->id) jsonError('You can only offer your own listings', 403);
        if ((int) $wantedListing->user_id  === (int) $authUser->id) jsonError('You cannot swap with your own listing', 403);
        if ($offeredListing->status !== 'active') jsonError('Your offered listing is not active', 422);
        if ($wantedListing->status  !== 'active') jsonError('The wanted listing is not active', 422);

        $dupStmt = $db->prepare("
            SELECT id FROM swap_offers
            WHERE offered_listing = :offered_id AND wanted_listing = :wanted_id AND status = 'pending' LIMIT 1
        ");
        $dupStmt->execute([':offered_id' => $offered_listing_id, ':wanted_id' => $wanted_listing_id]);
        if ($dupStmt->fetch()) jsonError('A pending offer already exists for these listings', 409);

        $receiver_id = (int) $wantedListing->user_id;

        try {
            $db->beginTransaction();

            $insStmt = $db->prepare("
                INSERT INTO swap_offers
                    (sender_id, receiver_id, offered_listing, wanted_listing, message, cash_addition, status, created_at)
                VALUES
                    (:sender_id, :receiver_id, :offered_listing, :wanted_listing, :message, :cash_addition, 'pending', NOW())
            ");
            $insStmt->execute([
                ':sender_id'       => $authUser->id,
                ':receiver_id'     => $receiver_id,
                ':offered_listing' => $offered_listing_id,
                ':wanted_listing'  => $wanted_listing_id,
                ':message'         => $message !== '' ? $message : null,
                ':cash_addition'   => $cash_addition,
            ]);
            $offer_id = (int) $db->lastInsertId();

            $db->prepare("
                INSERT INTO notifications (user_id, type, title, body, link, is_read, created_at)
                VALUES (:uid, 'swap', 'New Swap Offer!', :body, :link, 0, NOW())
            ")->execute([
                ':uid'  => $receiver_id,
                ':body' => "You have a new swap offer for your listing #{$wanted_listing_id}.",
                ':link' => "/pages/swap-offers.php?id={$offer_id}",
            ]);

            $db->commit();
            jsonResponse(['ok' => true, 'offer_id' => $offer_id], 201);

        } catch (PDOException $e) {
            $db->rollBack();
            error_log('[swaps POST] PDOException: ' . $e->getMessage());
            jsonError('Database error while creating swap offer', 500);
        }
        break;

    
    case 'PUT':
        $authUser = requireAuth();

        if (!$id) jsonError('Missing offer id (?id=X)');

        $newStatus = trim($input['action'] ?? $input['status'] ?? '');
        $allowed   = ['accepted', 'rejected', 'cancelled', 'countered'];

        if (!in_array($newStatus, $allowed, true)) {
            jsonError('Invalid action. Must be: accepted, rejected, cancelled, or countered.', 422);
        }

        $db = db();

        $stmt = $db->prepare('
            SELECT s.id, s.sender_id, s.receiver_id,
                   s.offered_listing, s.wanted_listing, s.status,
                   offered.title       AS offered_title,
                   wanted.title        AS wanted_title,
                   u_sender.first_name AS sender_first_name,
                   u_recv.first_name   AS receiver_first_name
            FROM swap_offers s
            JOIN listings AS offered ON offered.id = s.offered_listing
            JOIN listings AS wanted  ON wanted.id  = s.wanted_listing
            JOIN users AS u_sender   ON u_sender.id = s.sender_id
            JOIN users AS u_recv     ON u_recv.id   = s.receiver_id
            WHERE s.id = ? LIMIT 1
        ');
        $stmt->execute([$id]);
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$offer) jsonError('Offer not found', 404);

        if ($offer['status'] !== 'pending') {
            jsonError("This offer is already '{$offer['status']}' and cannot be updated.", 409);
        }

        $senderId   = (int) $offer['sender_id'];
        $receiverId = (int) $offer['receiver_id'];
        $viewerId   = (int) $authUser->id;

        if (in_array($newStatus, ['accepted', 'rejected', 'countered'], true) && $viewerId !== $receiverId) {
            jsonError('Only the offer recipient can accept, reject, or counter this swap.', 403);
        }
        if ($newStatus === 'cancelled' && $viewerId !== $senderId) {
            jsonError('Only the offer sender can cancel this swap.', 403);
        }

        $newCash = null;
        if ($newStatus === 'countered' && isset($input['cash_addition']) && is_numeric($input['cash_addition'])) {
            $newCash = (float) $input['cash_addition'];
        }

        try {
            $db->beginTransaction();

            if ($newCash !== null) {
                $db->prepare('UPDATE swap_offers SET status = ?, cash_addition = ?, updated_at = NOW() WHERE id = ?')
                   ->execute([$newStatus, $newCash, $id]);
            } else {
                $db->prepare('UPDATE swap_offers SET status = ?, updated_at = NOW() WHERE id = ?')
                   ->execute([$newStatus, $id]);
            }

            if ($newStatus === 'accepted') {
                $db->prepare("UPDATE listings SET status = 'sold', updated_at = NOW() WHERE id IN (?, ?)")
                   ->execute([$offer['offered_listing'], $offer['wanted_listing']]);

                $db->prepare("
                    UPDATE swap_offers SET status = 'rejected', updated_at = NOW()
                    WHERE id != ? AND status = 'pending'
                      AND (offered_listing IN (?,?) OR wanted_listing IN (?,?))
                ")->execute([$id,
                    $offer['offered_listing'], $offer['wanted_listing'],
                    $offer['offered_listing'], $offer['wanted_listing'],
                ]);
            }

            $notifInfo = [
                'accepted'  => ['user_id' => $senderId,   'title' => 'Swap Offer Accepted!',
                                'body' => "{$offer['receiver_first_name']} accepted your swap: \"{$offer['offered_title']}\" for \"{$offer['wanted_title']}\"."],
                'rejected'  => ['user_id' => $senderId,   'title' => 'Swap Offer Declined',
                                'body' => "{$offer['receiver_first_name']} declined your swap offer for \"{$offer['wanted_title']}\"."],
                'cancelled' => ['user_id' => $receiverId, 'title' => 'Swap Offer Cancelled',
                                'body' => "{$offer['sender_first_name']} cancelled their swap offer for \"{$offer['wanted_title']}\"."],
                'countered' => ['user_id' => $senderId,   'title' => 'Swap Offer Countered',
                                'body' => "{$offer['receiver_first_name']} countered your swap offer for \"{$offer['wanted_title']}\"."],
            ];
            $n = $notifInfo[$newStatus];

            $db->prepare("
                INSERT INTO notifications (user_id, type, title, body, link, is_read, created_at)
                VALUES (?, 'swap', ?, ?, ?, 0, NOW())
            ")->execute([$n['user_id'], $n['title'], $n['body'], "/pages/swap-offers.php?id={$id}"]);

            $db->commit();
            jsonResponse(['ok' => true]);

        } catch (Exception $e) {
            $db->rollBack();
            error_log('[swaps PUT] Exception: ' . $e->getMessage());
            jsonError('Something went wrong. Please try again.', 500);
        }
        break;

    default:
        jsonError('Method not allowed', 405);
}
