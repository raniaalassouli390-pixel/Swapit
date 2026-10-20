<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../jwt.php';
require_once __DIR__ . '/../../helpers/api.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method Not Allowed. Use POST.', 405);
}

$user       = requireAuth();
$reporterId = $user->id;

$body = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    jsonError('Invalid JSON body.', 400);
}

$targetType  = trim($body['target_type']  ?? '');
$targetId    = $body['target_id']          ?? null;
$type        = trim($body['type']          ?? '');
$description = trim($body['description']  ?? '');

$validTypes = ['fraud', 'fake_item', 'spam', 'harassment', 'other'];
$errors     = [];

if (!in_array($targetType, ['user', 'listing'], true)) {
    $errors[] = 'target_type must be "user" or "listing".';
}

if (empty($targetId) || !is_numeric($targetId)) {
    $errors[] = 'target_id must be a valid numeric ID.';
}

if (!in_array($type, $validTypes, true)) {
    $errors[] = 'type must be one of: ' . implode(', ', $validTypes) . '.';
}

if (strlen($description) > 1000) {
    $errors[] = 'description must not exceed 1000 characters.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'errors' => $errors]);
    exit;
}

if ($targetType === 'user' && (int) $targetId === (int) $reporterId) {
    jsonError('You cannot report yourself.', 400);
}

try {
    $pdo = db();

    $reportedId = null;
    $listingId  = null;

    if ($targetType === 'user') {
        $reportedId = (int) $targetId;
    } else {
        $listingId = (int) $targetId;
    }

    $stmt = $pdo->prepare("
        INSERT INTO reports (reporter_id, reported_id, listing_id, type, description, status)
        VALUES (:reporter_id, :reported_id, :listing_id, :type, :description, 'open')
    ");

    $stmt->execute([
        ':reporter_id' => $reporterId,
        ':reported_id' => $reportedId,
        ':listing_id'  => $listingId,
        ':type'        => $type,
        ':description' => $description,
    ]);

    jsonResponse(['ok' => true, 'message' => 'Report submitted successfully.', 'report_id' => (int) $pdo->lastInsertId()], 201);
} catch (PDOException $e) {
    jsonError('Service temporarily unavailable', 500);
}
