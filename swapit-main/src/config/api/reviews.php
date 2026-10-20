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

$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id'])      ? (int) $_GET['id']      : null;
$userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : null;


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
        if (!$userId) jsonError('Missing user_id query param (?user_id=X)');

        $db = db();

        
        $userStmt = $db->prepare('
            SELECT id, username, first_name, last_name, avatar, city, rating, review_count, created_at
            FROM users WHERE id = ? AND status != "suspended" LIMIT 1
        ');
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) jsonError('User not found', 404);

        
        $stmt = $db->prepare('
            SELECT
                r.id, r.rating, r.comment, r.created_at,
                reviewer.id         AS reviewer_id,
                reviewer.username   AS reviewer_username,
                reviewer.avatar     AS reviewer_avatar,
                l.id                AS listing_id,
                l.title             AS listing_title
            FROM reviews r
            JOIN users AS reviewer ON reviewer.id = r.reviewer_id
            LEFT JOIN listings l   ON l.id = r.listing_id
            WHERE r.reviewed_id = ?
            ORDER BY r.created_at DESC
        ');
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        
        $avgStmt = $db->prepare('
            SELECT ROUND(AVG(rating), 2) AS average, COUNT(*) AS total
            FROM reviews WHERE reviewed_id = ?
        ');
        $avgStmt->execute([$userId]);
        $avg = $avgStmt->fetch(PDO::FETCH_ASSOC);

        
        $breakdownStmt = $db->prepare('
            SELECT rating, COUNT(*) AS count FROM reviews WHERE reviewed_id = ?
            GROUP BY rating ORDER BY rating DESC
        ');
        $breakdownStmt->execute([$userId]);
        $breakdownRows = $breakdownStmt->fetchAll(PDO::FETCH_ASSOC);

        $breakdownMap = array_fill(1, 5, 0);
        foreach ($breakdownRows as $row) {
            $breakdownMap[(int) $row['rating']] = (int) $row['count'];
        }
        $total      = (int) $avg['total'];
        $breakdown  = [];
        for ($stars = 5; $stars >= 1; $stars--) {
            $breakdown[] = [
                'stars'      => $stars,
                'count'      => $breakdownMap[$stars],
                'percentage' => $total > 0 ? round(($breakdownMap[$stars] / $total) * 100, 1) : 0,
            ];
        }

        
        $reviews = array_map(fn($row) => [
            'id'         => (int) $row['id'],
            'rating'     => (int) $row['rating'],
            'comment'    => $row['comment'],
            'created_at' => $row['created_at'],
            'reviewer'   => [
                'id'       => (int) $row['reviewer_id'],
                'username' => $row['reviewer_username'],
                'avatar'   => $row['reviewer_avatar'],
            ],
            'listing' => $row['listing_id'] ? [
                'id'    => (int) $row['listing_id'],
                'title' => $row['listing_title'],
            ] : null,
        ], $rows);

        jsonResponse([
            'ok'             => true,
            'user'           => [
                'id'           => (int)   $user['id'],
                'username'     => $user['username'],
                'first_name'   => $user['first_name'],
                'last_name'    => $user['last_name'],
                'avatar'       => $user['avatar'],
                'city'         => $user['city'],
                'member_since' => $user['created_at'],
            ],
            'average_rating' => $avg['average'] !== null ? (float) $avg['average'] : null,
            'review_count'   => $total,
            'breakdown'      => $breakdown,
            'reviews'        => $reviews,
        ]);
        break;

    
    case 'POST':
        $authUser   = requireAuth();
        $db         = db();

        $reviewedId = isset($input['reviewed_id']) ? (int) $input['reviewed_id'] : null;
        $rating     = isset($input['rating'])      ? (int) $input['rating']      : null;
        $listingId  = isset($input['listing_id'])  ? (int) $input['listing_id']  : null;
        $comment    = trim($input['comment'] ?? '') ?: null;

        if (!$reviewedId)                           jsonError('reviewed_id is required.');
        if ($reviewedId === (int) $authUser->id)    jsonError('You cannot review yourself.', 403);
        if ($rating === null || $rating < 1 || $rating > 5) jsonError('Rating must be an integer between 1 and 5.');

        
        $reviewedStmt = $db->prepare('SELECT id FROM users WHERE id = ? AND status = "active" LIMIT 1');
        $reviewedStmt->execute([$reviewedId]);
        if (!$reviewedStmt->fetch()) jsonError('Reviewed user not found.', 404);

        
        $dupStmt = $db->prepare('
            SELECT id FROM reviews
            WHERE reviewer_id = ? AND reviewed_id = ?
              AND (listing_id = ? OR (listing_id IS NULL AND ? IS NULL))
            LIMIT 1
        ');
        $dupStmt->execute([$authUser->id, $reviewedId, $listingId, $listingId]);
        if ($dupStmt->fetch()) jsonError('You have already reviewed this user for this listing.');

        try {
            $db->beginTransaction();

            $db->prepare('
                INSERT INTO reviews (reviewer_id, reviewed_id, listing_id, rating, comment)
                VALUES (?, ?, ?, ?, ?)
            ')->execute([$authUser->id, $reviewedId, $listingId, $rating, $comment]);
            $reviewId = (int) $db->lastInsertId();

            
            $db->prepare('
                UPDATE users SET
                    rating       = (SELECT ROUND(AVG(rating), 2) FROM reviews WHERE reviewed_id = ?),
                    review_count = (SELECT COUNT(*) FROM reviews WHERE reviewed_id = ?)
                WHERE id = ?
            ')->execute([$reviewedId, $reviewedId, $reviewedId]);

            $db->commit();
            jsonResponse(['ok' => true, 'review_id' => $reviewId], 201);

        } catch (PDOException $e) {
            $db->rollBack();
            jsonError('Service temporarily unavailable', 500);
        }
        break;

    
    case 'DELETE':
        $authUser = requireAuth();
        $db       = db();

        if (!$id) jsonError('Missing review id (?id=X)');

        $stmt = $db->prepare('SELECT id, reviewer_id, reviewed_id FROM reviews WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $review = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$review) jsonError('Review not found.', 404);

        if ((int) $review['reviewer_id'] !== (int) $authUser->id && $authUser->role !== 'admin') {
            jsonError('Forbidden', 403);
        }

        try {
            $db->beginTransaction();

            $db->prepare('DELETE FROM reviews WHERE id = ?')->execute([$id]);

            
            $db->prepare('
                UPDATE users SET
                    rating       = COALESCE((SELECT ROUND(AVG(rating), 2) FROM reviews WHERE reviewed_id = ?), 0),
                    review_count = (SELECT COUNT(*) FROM reviews WHERE reviewed_id = ?)
                WHERE id = ?
            ')->execute([$review['reviewed_id'], $review['reviewed_id'], $review['reviewed_id']]);

            $db->commit();
            jsonResponse(['ok' => true]);

        } catch (PDOException $e) {
            $db->rollBack();
            jsonError('Service temporarily unavailable', 500);
        }
        break;

    default:
        jsonError('Method not allowed', 405);
}
