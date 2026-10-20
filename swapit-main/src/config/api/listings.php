<?php


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
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
        $pdo = db();

        if ($id) {
            

            $stmt = $pdo->prepare('
                SELECT l.*,
                       u.username   AS seller_username,
                       u.avatar     AS seller_avatar,
                       u.rating     AS seller_rating,
                       u.city       AS seller_city,
                       c.name       AS category_name,
                       c.slug       AS category_slug
                FROM listings l
                JOIN users      u ON u.id = l.user_id
                LEFT JOIN categories c ON c.id = l.category_id
                WHERE l.id = ? AND l.status != "removed"
                LIMIT 1
            ');
            $stmt->execute([$id]);
            $listing = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$listing) jsonError('Listing not found', 404);

            $imgStmt = $pdo->prepare('
                SELECT path, is_primary FROM listing_images
                WHERE listing_id = ? ORDER BY is_primary DESC, sort_order ASC
            ');
            $imgStmt->execute([$id]);
            $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($images as &$img) { $img['path'] = asset_url($img['path']); } unset($img);

            $auction = null;
            if ($listing['type'] === 'auction') {
                $auctStmt = $pdo->prepare('
                    SELECT a.*, COUNT(b.id) AS bid_count
                    FROM auctions a
                    LEFT JOIN bids b ON b.auction_id = a.id
                    WHERE a.listing_id = ?
                    GROUP BY a.id LIMIT 1
                ');
                $auctStmt->execute([$id]);
                $auction = $auctStmt->fetch(PDO::FETCH_ASSOC);
            }

            $pdo->prepare('UPDATE listings SET views = views + 1 WHERE id = ?')->execute([$id]);

            jsonResponse([
                'ok'      => true,
                'listing' => [
                    'id'          => (int)  $listing['id'],
                    'user_id'     => (int)  $listing['user_id'],
                    'title'       => $listing['title'],
                    'description' => $listing['description'],
                    'type'        => $listing['type'],
                    'price'       => $listing['price'] !== null ? (float) $listing['price'] : null,
                    'condition'   => $listing['condition'],
                    'city'        => $listing['city'],
                    'swap_notes'  => $listing['swap_notes'],
                    'status'      => $listing['status'],
                    'views'       => (int) $listing['views'] + 1,
                    'is_featured' => (bool) $listing['is_featured'],
                    'created_at'  => $listing['created_at'],
                    'images'      => $images,
                    'category'    => $listing['category_id'] ? [
                        'id'   => (int) $listing['category_id'],
                        'name' => $listing['category_name'],
                        'slug' => $listing['category_slug'],
                    ] : null,
                    'seller' => [
                        'id'       => (int)  $listing['user_id'],
                        'username' => $listing['seller_username'],
                        'avatar'   => asset_url($listing['seller_avatar']),
                        'rating'   => (float) $listing['seller_rating'],
                        'city'     => $listing['seller_city'],
                    ],
                    'auction' => $auction ? [
                        'id'          => (int)   $auction['id'],
                        'start_price' => (float) $auction['start_price'],
                        'current_bid' => $auction['current_bid'] !== null ? (float) $auction['current_bid'] : null,
                        'starts_at'   => $auction['starts_at'],
                        'ends_at'     => $auction['ends_at'],
                        'status'      => $auction['status'],
                        'bid_count'   => (int)   $auction['bid_count'],
                    ] : null,
                ],
            ]);
        }

        

        $allowed_types      = ['sale', 'swap', 'auction'];
        $allowed_conditions = ['new', 'like_new', 'good', 'fair', 'for_parts'];
        $allowed_sorts      = [
            'newest'     => 'l.created_at DESC',
            'oldest'     => 'l.created_at ASC',
            'price_asc'  => 'l.price ASC',
            'price_desc' => 'l.price DESC',
            'popular'    => 'l.views DESC',
        ];

        $type      = in_array($_GET['type']      ?? '', $allowed_types, true)      ? $_GET['type']      : null;
        $condition = in_array($_GET['condition'] ?? '', $allowed_conditions, true) ? $_GET['condition'] : null;
        $city      = trim($_GET['city']   ?? '') ?: null;
        $search    = trim($_GET['search'] ?? '') ?: null;
        $min_price = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float) $_GET['min_price'] : null;
        $max_price = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float) $_GET['max_price'] : null;
        $sort      = $allowed_sorts[$_GET['sort'] ?? ''] ?? $allowed_sorts['newest'];
        $page      = max(1, (int) ($_GET['page'] ?? 1));
        $per_page  = 20;
        $offset    = ($page - 1) * $per_page;

        $category_id = null;
        if (!empty($_GET['category'])) {
            $cat_raw = trim($_GET['category']);
            if (ctype_digit($cat_raw)) {
                $category_id = (int) $cat_raw;
            } else {
                $catStmt = $pdo->prepare('SELECT id FROM categories WHERE slug = ? LIMIT 1');
                $catStmt->execute([$cat_raw]);
                $cat = $catStmt->fetch(PDO::FETCH_ASSOC);
                $category_id = $cat ? (int) $cat['id'] : -1;
            }
        }

        $where  = ["l.status = 'active'"];
        $params = [];

        if ($type)        { $where[] = 'l.type = ?';        $params[] = $type; }
        if ($condition)   { $where[] = 'l.condition = ?';   $params[] = $condition; }
        if ($category_id) { $where[] = 'l.category_id = ?'; $params[] = $category_id; }
        if ($city)        { $where[] = 'l.city LIKE ?';     $params[] = "%$city%"; }
        if ($min_price !== null) { $where[] = 'l.price >= ?'; $params[] = $min_price; }
        if ($max_price !== null) { $where[] = 'l.price <= ?'; $params[] = $max_price; }
        if ($search) {
            $where[]  = '(l.title LIKE ? OR l.description LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $where_sql = 'WHERE ' . implode(' AND ', $where);

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM listings l $where_sql");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $fetchParams   = $params;
        $fetchParams[] = $per_page;
        $fetchParams[] = $offset;

        $stmt = $pdo->prepare("
            SELECT
                l.id, l.title, l.type, l.price, l.condition,
                l.city, l.status, l.views, l.is_featured, l.created_at,
                u.username AS seller_username,
                u.avatar   AS seller_avatar,
                u.rating   AS seller_rating,
                c.name     AS category_name,
                c.slug     AS category_slug,
                (SELECT path FROM listing_images li WHERE li.listing_id = l.id AND li.is_primary = 1 LIMIT 1) AS primary_image
            FROM listings l
            JOIN users u ON u.id = l.user_id
            LEFT JOIN categories c ON c.id = l.category_id
            $where_sql
            ORDER BY l.is_featured DESC, $sort
            LIMIT ? OFFSET ?
        ");
        $stmt->execute($fetchParams);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $listings = array_map(fn($row) => [
            'id'            => (int)   $row['id'],
            'title'         => $row['title'],
            'type'          => $row['type'],
            'price'         => $row['price'] !== null ? (float) $row['price'] : null,
            'condition'     => $row['condition'],
            'city'          => $row['city'],
            'views'         => (int)   $row['views'],
            'is_featured'   => (bool)  $row['is_featured'],
            'created_at'    => $row['created_at'],
            'primary_image' => asset_url($row['primary_image']),
            'category'      => $row['category_name'] ? [
                'name' => $row['category_name'],
                'slug' => $row['category_slug'],
            ] : null,
            'seller' => [
                'username' => $row['seller_username'],
                'avatar'   => $row['seller_avatar'],
                'rating'   => (float) $row['seller_rating'],
            ],
        ], $rows);

        jsonResponse([
            'ok'       => true,
            'listings' => $listings,
            'total'    => $total,
            'page'     => $page,
            'pages'    => (int) ceil($total / $per_page),
        ]);
        break;

    
    case 'POST':
        $authUser = requireAuth();
        $pdo      = db();

        $title     = trim($input['title']     ?? '');
        $type      = trim($input['type']      ?? '');
        $condition = trim($input['condition'] ?? '');

        if (!$title)                                             jsonError('Title is required.');
        if (mb_strlen($title) > 255)                            jsonError('Title must be 255 characters or fewer.');
        if (!in_array($type, ['sale','swap','auction'], true))  jsonError('Type must be: sale, swap, or auction.');
        if (!in_array($condition, ['new','like_new','good','fair','for_parts'], true))
                                                                 jsonError('Invalid condition value.');

        $price       = isset($input['price'])       && is_numeric($input['price'])       ? (float) $input['price']       : null;
        $start_price = isset($input['start_price']) && is_numeric($input['start_price']) ? (float) $input['start_price'] : null;
        $starts_at   = trim($input['starts_at'] ?? '');
        $ends_at     = trim($input['ends_at']   ?? '');

        if ($type === 'sale'    && ($price === null || $price <= 0))               jsonError('Price is required for sale listings.');
        if ($type === 'auction' && ($start_price === null || $start_price <= 0))   jsonError('Start price is required for auctions.');
        if ($type === 'auction' && (!$starts_at || !$ends_at))                     jsonError('starts_at and ends_at are required for auctions.');
        if ($type === 'auction' && strtotime($ends_at) <= strtotime($starts_at))   jsonError('ends_at must be after starts_at.');

        $description = trim($input['description'] ?? '') ?: null;
        $category_id = isset($input['category_id']) ? (int) $input['category_id'] : null;
        $city        = trim($input['city']       ?? '') ?: null;
        $swap_notes  = trim($input['swap_notes'] ?? '') ?: null;

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('
                INSERT INTO listings
                    (user_id, title, description, type, price, category_id, city, `condition`, swap_notes, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "active")
            ');
            $stmt->execute([
                $authUser->id, $title, $description, $type,
                $type === 'sale' ? $price : null,
                $category_id, $city, $condition, $swap_notes,
            ]);
            $listing_id = (int) $pdo->lastInsertId();

            if ($type === 'auction') {
                $pdo->prepare('
                    INSERT INTO auctions (listing_id, start_price, starts_at, ends_at, status)
                    VALUES (?, ?, ?, ?, "upcoming")
                ')->execute([$listing_id, $start_price, $starts_at, $ends_at]);
            }

            $pdo->commit();
            jsonResponse(['ok' => true, 'id' => $listing_id], 201);

        } catch (PDOException $e) {
            $pdo->rollBack();
            jsonError('Service temporarily unavailable', 500);
        }
        break;

    
    case 'PUT':
        $authUser = requireAuth();
        $pdo      = db();

        if (!$id) jsonError('Missing listing id (?id=X)');

        $stmt = $pdo->prepare("SELECT * FROM listings WHERE id = ? AND status != 'removed' LIMIT 1");
        $stmt->execute([$id]);
        $listing = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$listing) jsonError('Listing not found', 404);

        $isOwner = (int) $listing['user_id'] === (int) $authUser->id;
        $isAdmin = $authUser->role === 'admin';

        if (!$isOwner && !$isAdmin) {
            jsonError('Forbidden', 403);
        }

        $fields = [];
        $params = [];
        $errors = [];

        if (array_key_exists('title', $input)) {
            $title = trim($input['title']);
            if ($title === '')               $errors[] = 'title cannot be empty';
            elseif (mb_strlen($title) > 255) $errors[] = 'title must be 255 characters or fewer';
            else { $fields[] = 'title = ?'; $params[] = $title; }
        }

        if (array_key_exists('description', $input)) {
            $fields[] = 'description = ?';
            $params[] = trim($input['description']) ?: null;
        }

        if (array_key_exists('price', $input)) {
            $price = $input['price'];
            if ($price !== null && $price !== '' && (!is_numeric($price) || (float) $price < 0)) {
                $errors[] = 'price must be a positive number or null';
            } else {
                $fields[] = 'price = ?';
                $params[] = ($price !== null && $price !== '') ? (float) $price : null;
            }
        }

        if (array_key_exists('category_id', $input)) {
            $cat      = $input['category_id'];
            $fields[] = 'category_id = ?';
            $params[] = ($cat !== null && (int) $cat > 0) ? (int) $cat : null;
        }

        if (array_key_exists('city', $input)) {
            $fields[] = 'city = ?';
            $params[] = trim($input['city']) ?: null;
        }

        if (array_key_exists('condition', $input)) {
            $condition         = trim($input['condition']);
            $allowedConditions = ['new', 'like_new', 'good', 'fair', 'for_parts'];
            if (!in_array($condition, $allowedConditions, true)) {
                $errors[] = 'condition must be one of: ' . implode(', ', $allowedConditions);
            } else {
                $fields[] = '`condition` = ?';
                $params[] = $condition;
            }
        }

        if (array_key_exists('swap_notes', $input)) {
            $fields[] = 'swap_notes = ?';
            $params[] = trim($input['swap_notes']) ?: null;
        }

        if (array_key_exists('status', $input)) {
            $status          = trim($input['status']);
            $allowedStatuses = ['active', 'draft'];
            if (!in_array($status, $allowedStatuses, true)) {
                $errors[] = 'status must be one of: active, draft';
            } else {
                $fields[] = '`status` = ?';
                $params[] = $status;
            }
        }

        if (!empty($errors)) jsonError(implode('; ', $errors), 422);
        if (empty($fields))  jsonResponse(['ok' => true, 'message' => 'No fields to update']);

        $params[] = $id;
        $pdo->prepare('UPDATE listings SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = ?')
            ->execute($params);

        jsonResponse(['ok' => true]);
        break;

    
    case 'DELETE':
        $authUser = requireAuth();
        $pdo      = db();

        if (!$id) jsonError('Missing listing id (?id=X)');

        $stmt = $pdo->prepare('SELECT * FROM listings WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $listing = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$listing) jsonError('Listing not found', 404);

        if ((int) $listing['user_id'] !== (int) $authUser->id && $authUser->role !== 'admin') {
            jsonError('Forbidden', 403);
        }

        if ($listing['status'] === 'removed') jsonError('Listing already removed', 409);

        if ($listing['type'] === 'auction') {
            $auctStmt = $pdo->prepare('
                SELECT a.status, COUNT(b.id) AS bid_count
                FROM auctions a
                LEFT JOIN bids b ON b.auction_id = a.id
                WHERE a.listing_id = ?
                GROUP BY a.id LIMIT 1
            ');
            $auctStmt->execute([$id]);
            $auction = $auctStmt->fetch(PDO::FETCH_ASSOC);

            if ($auction && $auction['status'] === 'live' && (int) $auction['bid_count'] > 0) {
                jsonError('Cannot delete a listing with an active auction that has bids.', 409);
            }
        }

        $swapStmt = $pdo->prepare("
            SELECT COUNT(*) FROM swap_offers
            WHERE (offered_listing = ? OR wanted_listing = ?) AND status = 'pending'
        ");
        $swapStmt->execute([$id, $id]);
        if ((int) $swapStmt->fetchColumn() > 0) {
            jsonError('Cannot delete a listing with pending swap offers.', 409);
        }

        $pdo->prepare("UPDATE listings SET status = 'removed', updated_at = NOW() WHERE id = ?")
            ->execute([$id]);

        $pdo->prepare("
            UPDATE swap_offers SET status = 'cancelled', updated_at = NOW()
            WHERE (offered_listing = ? OR wanted_listing = ?)
              AND status NOT IN ('accepted','rejected','cancelled')
        ")->execute([$id, $id]);

        jsonResponse(['ok' => true]);
        break;

    default:
        jsonError('Method not allowed', 405);
}
