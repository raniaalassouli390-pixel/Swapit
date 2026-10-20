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
        $listingId = isset($_GET['listing_id']) ? (int) $_GET['listing_id'] : null;

        if (!$listingId) {
            jsonError('Missing listing_id query param (?listing_id=X)');
        }

        $pdo = db();

        
        $listingStmt = $pdo->prepare('
            SELECT id, title, type, user_id
            FROM listings
            WHERE id = ? AND status != "removed"
            LIMIT 1
        ');
        $listingStmt->execute([$listingId]);
        $listing = $listingStmt->fetch(PDO::FETCH_ASSOC);

        if (!$listing || $listing['type'] !== 'auction') {
            jsonError('Listing not found or not an auction', 404);
        }

        
        $auctStmt = $pdo->prepare('
            SELECT id, status, current_bid, start_price, starts_at, ends_at
            FROM auctions
            WHERE listing_id = ?
            LIMIT 1
        ');
        $auctStmt->execute([$listingId]);
        $auction = $auctStmt->fetch(PDO::FETCH_ASSOC);

        if (!$auction) jsonError('Auction data not found', 404);

        
        $viewerId   = null;
        $viewerRole = null;
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if ($authHeader) {
            try {
                $decoded    = verifyJWT(str_replace('Bearer ', '', $authHeader));
                $viewerId   = (int) $decoded->id;
                $viewerRole = $decoded->role ?? 'user';
            } catch (Exception $e) {
                
            }
        }

        
        $bidsStmt = $pdo->prepare('
            SELECT b.id, b.amount, b.created_at, b.user_id,
                   u.username, u.avatar
            FROM bids b
            JOIN auctions a ON a.id = b.auction_id
            JOIN users    u ON u.id = b.user_id
            WHERE a.listing_id = ?
            ORDER BY b.amount DESC, b.created_at ASC
        ');
        $bidsStmt->execute([$listingId]);
        $bids = $bidsStmt->fetchAll(PDO::FETCH_ASSOC);

        
        $shaped = array_map(function ($bid) use ($viewerId, $viewerRole) {
            $isOwn    = $viewerId !== null && (int) $bid['user_id'] === $viewerId;
            $showFull = $isOwn || $viewerRole === 'admin';
            return [
                'id'         => (int)   $bid['id'],
                'amount'     => (float) $bid['amount'],
                'created_at' => $bid['created_at'],
                'is_you'     => $isOwn,
                'bidder'     => [
                    'username' => $showFull ? $bid['username'] : maskUsername($bid['username']),
                    'avatar'   => $showFull ? $bid['avatar']   : null,
                ],
            ];
        }, $bids);

        jsonResponse([
            'ok'      => true,
            'listing' => [
                'id'    => (int) $listing['id'],
                'title' => $listing['title'],
            ],
            'auction' => [
                'status'      => $auction['status'],
                'start_price' => (float) $auction['start_price'],
                'current_bid' => $auction['current_bid'] !== null ? (float) $auction['current_bid'] : null,
                'starts_at'   => $auction['starts_at'],
                'ends_at'     => $auction['ends_at'],
                'total_bids'  => count($bids),
            ],
            'bids' => $shaped,
        ]);
        break;

    
    case 'POST':
        $authUser = requireAuth();

        $body       = json_decode(file_get_contents('php://input'), true) ?? [];
        $listing_id = isset($body['listing_id']) ? (int) $body['listing_id'] : 0;
        $amount     = isset($body['amount'])     ? $body['amount']           : null;

        if (!$listing_id)                                    jsonError('listing_id is required', 422);
        if ($amount === null || $amount === '')               jsonError('amount is required', 422);
        if (!is_numeric($amount) || (float) $amount <= 0)    jsonError('amount must be a positive number', 422);

        $amount = (float) $amount;
        $db     = db();

        $lstStmt = $db->prepare('
            SELECT id, user_id, type, status FROM listings WHERE id = :id LIMIT 1
        ');
        $lstStmt->execute([':id' => $listing_id]);
        $listing = $lstStmt->fetch(PDO::FETCH_OBJ);

        if (!$listing || $listing->status === 'removed') jsonError('Listing not found', 404);
        if ($listing->type !== 'auction')                jsonError('This listing is not an auction', 422);
        if ((int) $authUser->id === (int) $listing->user_id) jsonError('You cannot bid on your own listing', 403);

        $aucStmt = $db->prepare('
            SELECT id, status, start_price, current_bid FROM auctions WHERE listing_id = :lid LIMIT 1
        ');
        $aucStmt->execute([':lid' => $listing_id]);
        $auction = $aucStmt->fetch(PDO::FETCH_OBJ);

        if (!$auction)                   jsonError('Auction record not found', 404);
        if ($auction->status !== 'live') jsonError('Auction is not live', 422);

        $floor = ($auction->current_bid !== null && (float) $auction->current_bid > 0)
            ? (float) $auction->current_bid
            : (float) $auction->start_price;

        if ($amount <= $floor) {
            jsonError(sprintf('Bid must be higher than current bid of %.2f', $floor), 422);
        }

        
        $prevStmt = $db->prepare('
            SELECT user_id FROM bids WHERE auction_id = :aid ORDER BY amount DESC LIMIT 1
        ');
        $prevStmt->execute([':aid' => $auction->id]);
        $previousTopBid = $prevStmt->fetch(PDO::FETCH_OBJ);

        try {
            $db->beginTransaction();

            $db->prepare('
                INSERT INTO bids (auction_id, user_id, amount, created_at)
                VALUES (:auction_id, :user_id, :amount, NOW())
            ')->execute([
                ':auction_id' => $auction->id,
                ':user_id'    => $authUser->id,
                ':amount'     => $amount,
            ]);
            $bidId = (int) $db->lastInsertId();

            $db->prepare('
                UPDATE auctions SET current_bid = :amount WHERE id = :id
            ')->execute([':amount' => $amount, ':id' => $auction->id]);

            if ($previousTopBid && (int) $previousTopBid->user_id !== (int) $authUser->id) {
                $db->prepare("
                    INSERT INTO notifications (user_id, type, title, body, link, is_read, created_at)
                    VALUES (:uid, 'bid', 'You have been outbid!', :body, :link, 0, NOW())
                ")->execute([
                    ':uid'  => $previousTopBid->user_id,
                    ':body' => sprintf('Someone placed a higher bid of %.2f JD.', $amount),
                    ':link' => "/pages/auction.php?id={$listing_id}",
                ]);
            }

            $db->commit();
            jsonResponse(['ok' => true, 'bid_id' => $bidId, 'current_bid' => $amount], 201);

        } catch (PDOException $e) {
            $db->rollBack();
            error_log('[bids POST] PDOException: ' . $e->getMessage());
            jsonError('Database error while placing bid', 500);
        }
        break;

    default:
        jsonError('Method not allowed', 405);
}

function maskUsername(string $username): string
{
    $len = mb_strlen($username);
    if ($len <= 2) return '**';
    if ($len === 3) return mb_substr($username, 0, 1) . '**';
    return mb_substr($username, 0, 2) . '***' . mb_substr($username, -1);
}
