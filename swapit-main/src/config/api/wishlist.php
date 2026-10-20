<?php


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../jwt.php';
require_once __DIR__ . '/../../helpers/api.php';

$method    = $_SERVER['REQUEST_METHOD'];
$listingId = isset($_GET['listing_id']) ? (int) $_GET['listing_id'] : null;


$input = [];
if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    if ($raw && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
        $input = json_decode($raw, true) ?? [];
    } else {
        $input = $_POST;
    }
}

$authUser = requireAuth(); 

switch ($method) {

    
    case 'GET':
        $db = db();

        $stmt = $db->prepare('
            SELECT
                w.id,
                w.created_at,
                l.id        AS listing_id,
                l.title,
                l.price,
                l.type,
                l.condition,
                l.city,
                l.status,
                (SELECT path FROM listing_images
                 WHERE listing_id = l.id AND is_primary = 1 LIMIT 1) AS primary_image
            FROM wishlist w
            JOIN listings l ON l.id = w.listing_id
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
        ');
        $stmt->execute([$authUser->id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        
        $wishlist = array_map(function (array $row): array {
            $row['available']     = ($row['status'] === 'active');
            $row['primary_image'] = asset_url($row['primary_image']);
            return $row;
        }, $rows);

        jsonResponse(['ok' => true, 'wishlist' => $wishlist]);
        break;

    
    case 'POST':
        $listingIdBody = isset($input['listing_id']) ? (int) $input['listing_id'] : null;

        if (!$listingIdBody) {
            jsonError('Missing listing_id in request body');
        }

        $db = db();

        $stmt = $db->prepare('SELECT id, user_id, status FROM listings WHERE id = ? LIMIT 1');
        $stmt->execute([$listingIdBody]);
        $listing = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$listing || $listing['status'] !== 'active') {
            jsonError('Listing not found or unavailable', 404);
        }

        if ((int) $listing['user_id'] === (int) $authUser->id) {
            jsonError('You cannot wishlist your own listing', 422);
        }

        $stmt = $db->prepare('
            INSERT IGNORE INTO wishlist (user_id, listing_id, created_at)
            VALUES (?, ?, NOW())
        ');
        $stmt->execute([$authUser->id, $listingIdBody]);

        jsonResponse(['ok' => true]);
        break;

    
    case 'DELETE':
        if (!$listingId) {
            jsonError('Missing listing_id query param (?listing_id=X)');
        }

        $db = db();

        $db->prepare('DELETE FROM wishlist WHERE user_id = ? AND listing_id = ?')
           ->execute([$authUser->id, $listingId]);

        jsonResponse(['ok' => true]);
        break;

    default:
        jsonError('Method not allowed', 405);
}
