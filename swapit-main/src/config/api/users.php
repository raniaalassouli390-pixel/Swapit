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
require_once __DIR__ . '/../../helpers/api.php';

$authUser = requireAuth();
$method   = $_SERVER['REQUEST_METHOD'];

$input = [];
if (in_array($method, ['POST', 'PUT'], true)) {
    $raw = file_get_contents('php://input');
    if ($raw && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
        $input = json_decode($raw, true) ?? [];
    } else {
        $input = $_POST;
    }
}

switch ($method) {

    
    case 'GET':

        
        if (isset($_GET['id'])) {
            $targetId = (int) $_GET['id'];
            if ($targetId <= 0) {
                jsonError('Invalid user id', 400);
            }

            $pdo = db();

            $stmt = $pdo->prepare('
                SELECT id, username, first_name, last_name,
                       bio, city, avatar, role, rating, review_count, created_at
                FROM   users
                WHERE  id = ?
                LIMIT  1
            ');
            $stmt->execute([$targetId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                jsonError('User not found', 404);
            }

            $user['id']           = (int)   $user['id'];
            $user['rating']       = (float) $user['rating'];
            $user['review_count'] = (int)   $user['review_count'];

            
            $stmt = $pdo->prepare('
                SELECT id, title, price, type, status, created_at,
                       (SELECT path FROM listing_images
                        WHERE listing_id = l.id AND is_primary = 1 LIMIT 1) AS primary_image
                FROM   listings l
                WHERE  user_id = ? AND status = "active"
                ORDER  BY created_at DESC
            ');
            $stmt->execute([$targetId]);
            $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($listings as &$l) {
                $l['id']            = (int)   $l['id'];
                $l['price']         = $l['price'] !== null ? (float) $l['price'] : null;
                $l['primary_image'] = asset_url($l['primary_image']);
            }
            unset($l);

            
            $stmt = $pdo->prepare('
                SELECT r.id, r.rating, r.comment, r.created_at,
                       r.reviewer_id,
                       u.username AS reviewer_username,
                       u.avatar   AS reviewer_avatar
                FROM   reviews r
                JOIN   users   u ON u.id = r.reviewer_id
                WHERE  r.reviewed_id = ?
                ORDER  BY r.created_at DESC
            ');
            $stmt->execute([$targetId]);
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($reviews as &$r) {
                $r['id']          = (int)   $r['id'];
                $r['reviewer_id'] = (int)   $r['reviewer_id'];
                $r['rating']      = (float) $r['rating'];
            }
            unset($r);

            jsonResponse([
                'ok'       => true,
                'user'     => $user,
                'listings' => $listings,
                'reviews'  => $reviews,
            ]);
        }

        
        if (($_GET['action'] ?? null) === 'dashboard') {
            $pdo    = db();
            $userId = (int) $authUser->id;

            
            $listingsStmt = $pdo->prepare('
                SELECT
                    l.id, l.title, l.description, l.price, l.type,
                    l.status, l.created_at, l.updated_at,
                    COUNT(b.id) AS bid_count
                FROM listings l
                LEFT JOIN auctions a ON a.listing_id = l.id
                LEFT JOIN bids b     ON b.auction_id  = a.id
                WHERE l.user_id = :user_id
                GROUP BY l.id
                ORDER BY l.created_at DESC
            ');
            $listingsStmt->execute([':user_id' => $userId]);
            $listings = $listingsStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($listings as &$l) {
                $l['id']        = (int)   $l['id'];
                $l['price']     = $l['price'] !== null ? (float) $l['price'] : null;
                $l['bid_count'] = (int)   $l['bid_count'];
            }
            unset($l);

            
            $bidsStmt = $pdo->prepare('
                SELECT
                    b.id,
                    b.auction_id,
                    l.title AS listing_title,
                    b.amount,
                    b.created_at
                FROM bids b
                INNER JOIN auctions a ON a.id = b.auction_id
                INNER JOIN listings l ON l.id = a.listing_id
                WHERE b.user_id = :user_id
                ORDER BY b.created_at DESC
            ');
            $bidsStmt->execute([':user_id' => $userId]);
            $bids = $bidsStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($bids as &$b) {
                $b['id']         = (int)   $b['id'];
                $b['auction_id'] = (int)   $b['auction_id'];
                $b['amount']     = (float) $b['amount'];
            }
            unset($b);

            
            $swapsStmt = $pdo->prepare('
                SELECT
                    so.id,
                    so.offered_listing,
                    ol.title AS offered_listing_title,
                    so.wanted_listing,
                    wl.title AS wanted_listing_title,
                    so.sender_id,
                    so.receiver_id,
                    so.status,
                    so.message,
                    so.created_at,
                    so.updated_at,
                    CASE
                        WHEN so.sender_id = :user_id THEN "sent"
                        ELSE "received"
                    END AS direction
                FROM swap_offers so
                INNER JOIN listings ol ON ol.id = so.offered_listing
                INNER JOIN listings wl ON wl.id = so.wanted_listing
                WHERE so.sender_id   = :user_id
                   OR so.receiver_id = :user_id
                ORDER BY so.created_at DESC
            ');
            $swapsStmt->execute([':user_id' => $userId]);
            $swapOffers = $swapsStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($swapOffers as &$s) {
                $s['id']              = (int) $s['id'];
                $s['offered_listing'] = (int) $s['offered_listing'];
                $s['wanted_listing']  = (int) $s['wanted_listing'];
                $s['sender_id']       = (int) $s['sender_id'];
                $s['receiver_id']     = (int) $s['receiver_id'];
            }
            unset($s);

            jsonResponse([
                'ok'   => true,
                'data' => [
                    'listings'    => $listings,
                    'bids'        => $bids,
                    'swap_offers' => $swapOffers,
                ],
                'meta' => [
                    'listings_count'    => count($listings),
                    'bids_count'        => count($bids),
                    'swap_offers_count' => count($swapOffers),
                ],
            ]);
        }

        
        $pdo  = db();
        $stmt = $pdo->prepare('
            SELECT id, username, first_name, last_name, email, phone,
                   bio, city, avatar, role, rating, review_count, created_at
            FROM users WHERE id = ? LIMIT 1
        ');
        $stmt->execute([$authUser->id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            jsonError('User not found', 404);
        }

        $user['id']           = (int)   $user['id'];
        $user['rating']       = (float) $user['rating'];
        $user['review_count'] = (int)   $user['review_count'];

        jsonResponse(['ok' => true, 'user' => $user]);
        break;

    
    
    
    
    case 'PUT':
        if (empty($input)) {
            jsonError('Request body is empty or invalid JSON', 422);
        }

        $pdo  = db();
        $stmt = $pdo->prepare('
            SELECT id, first_name, last_name, bio, city, phone, avatar, password
            FROM users WHERE id = ? LIMIT 1
        ');
        $stmt->execute([$authUser->id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            jsonError('User not found', 404);
        }

        $fields = [];
        $params = [];
        $errors = [];

        if (array_key_exists('first_name', $input)) {
            $val = trim($input['first_name']);
            if ($val === '') {
                $errors[] = 'first_name cannot be empty';
            } elseif (mb_strlen($val) > 50) {
                $errors[] = 'first_name must be 50 characters or fewer';
            } else {
                $fields[] = 'first_name = :first_name';
                $params[':first_name'] = $val;
            }
        }

        if (array_key_exists('last_name', $input)) {
            $val = trim($input['last_name']);
            if (mb_strlen($val) > 50) {
                $errors[] = 'last_name must be 50 characters or fewer';
            } else {
                $fields[] = 'last_name = :last_name';
                $params[':last_name'] = $val ?: null;
            }
        }

        if (array_key_exists('bio', $input)) {
            $val = trim($input['bio']);
            $fields[] = 'bio = :bio';
            $params[':bio'] = $val ?: null;
        }

        if (array_key_exists('city', $input)) {
            $val = trim($input['city']);
            if (mb_strlen($val) > 100) {
                $errors[] = 'city must be 100 characters or fewer';
            } else {
                $fields[] = 'city = :city';
                $params[':city'] = $val ?: null;
            }
        }

        if (array_key_exists('phone', $input)) {
            $val = trim($input['phone']);
            if ($val !== '' && !preg_match('/^\+?[\d\s\-]{7,20}$/', $val)) {
                $errors[] = 'Invalid phone number format';
            } else {
                if ($val !== '') {
                    $phoneStmt = $pdo->prepare('SELECT id FROM users WHERE phone = ? AND id != ? LIMIT 1');
                    $phoneStmt->execute([$val, $authUser->id]);
                    if ($phoneStmt->fetch()) {
                        $errors[] = 'Phone number already in use by another account';
                    }
                }
                $fields[] = 'phone = :phone';
                $params[':phone'] = $val ?: null;
            }
        }

        if (array_key_exists('avatar', $input)) {
            $val = trim($input['avatar']);
            if ($val !== '' && mb_strlen($val) > 255) {
                $errors[] = 'Avatar path is too long (max 255 characters)';
            } else {
                $fields[] = 'avatar = :avatar';
                $params[':avatar'] = $val ?: null;
            }
        }

        
        $newPass = trim($input['new_password']     ?? '');
        $curPass = trim($input['current_password'] ?? '');

        if ($newPass !== '') {
            if ($curPass === '') {
                $errors[] = 'current_password is required to set a new password';
            } elseif (!password_verify($curPass, $user['password'])) {
                $errors[] = 'Current password is incorrect';
            } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $newPass)) {
                $errors[] = 'New password must be at least 8 characters and include uppercase, lowercase, a number, and a special character';
            } elseif (password_verify($newPass, $user['password'])) {
                $errors[] = 'New password must be different from the current password';
            } else {
                $fields[] = 'password = :password';
                $params[':password'] = password_hash($newPass, PASSWORD_DEFAULT);
            }
        }

        if (!empty($errors)) {
            jsonError(implode('; ', $errors), 422);
        }

        if (empty($fields)) {
            jsonResponse(['ok' => true, 'message' => 'Nothing to update']);
        }

        $params[':id'] = $authUser->id;
        $pdo->prepare(
            'UPDATE users SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id'
        )->execute($params);

        $updStmt = $pdo->prepare('
            SELECT id, username, first_name, last_name, email, phone,
                   bio, city, avatar, rating, review_count
            FROM users WHERE id = ? LIMIT 1
        ');
        $updStmt->execute([$authUser->id]);
        $updated = $updStmt->fetch(PDO::FETCH_ASSOC);

        $updated['id']           = (int)   $updated['id'];
        $updated['rating']       = (float) $updated['rating'];
        $updated['review_count'] = (int)   $updated['review_count'];

        jsonResponse([
            'ok'               => true,
            'message'          => 'Profile updated successfully',
            'password_changed' => $newPass !== '',
            'user'             => $updated,
        ]);
        break;

    default:
        jsonError('Method not allowed', 405);
}
