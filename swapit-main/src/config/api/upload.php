<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../jwt.php';
require_once __DIR__ . '/../../helpers/api.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$authUser = requireAuth();

define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('UPLOAD_DIR',    __DIR__ . '/../../../public/images/uploads/');

$listingId = (int) ($_POST['listing_id'] ?? 0);

if ($listingId <= 0) {
    jsonError('listing_id is required', 422);
}

$stmt = db()->prepare('SELECT id FROM listings WHERE id = ? AND user_id = ? AND status != "removed"');
$stmt->execute([$listingId, $authUser->id]);

if (!$stmt->fetch()) {
    jsonError('Listing not found or access denied', 403);
}

if (empty($_FILES['images'])) {
    jsonError('No files uploaded', 422);
}

$isPrimary  = (int) ($_POST['is_primary'] ?? 0);
$finfo      = new finfo(FILEINFO_MIME_TYPE);
$uploaded   = [];
$sortOrder  = 0;

$files = [];
foreach ($_FILES['images'] as $key => $values) {
    foreach ($values as $index => $value) {
        $files[$index][$key] = $value;
    }
}

if ($isPrimary === 1) {
    $stmt = db()->prepare('UPDATE listing_images SET is_primary = 0 WHERE listing_id = ?');
    $stmt->execute([$listingId]);
}

foreach ($files as $index => $file) {

    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        jsonError('Upload error on file ' . ($index + 1), 422);
    }

    
    if ($file['size'] > MAX_FILE_SIZE) {
        jsonError('File ' . ($index + 1) . ' is too large. Max 5MB.', 422);
    }

    
    $mimeType = $finfo->file($file['tmp_name']);
    if (!in_array($mimeType, ALLOWED_TYPES)) {
        jsonError('File ' . ($index + 1) . ' is not a valid image type.', 422);
    }

    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename  = uniqid() . '_' . time() . '.' . $extension;

    
    $folder = UPLOAD_DIR . $listingId . '/';
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }

    
    if (!move_uploaded_file($file['tmp_name'], $folder . $filename)) {
        jsonError('Could not save file ' . ($index + 1), 500);
    }

    
    $thisIsPrimary = ($isPrimary === 1 && $index === 0) ? 1 : 0;

    
    $path = '/public/images/uploads/' . $listingId . '/' . $filename;
    $stmt = db()->prepare(
        'INSERT INTO listing_images (listing_id, path, is_primary, sort_order)
         VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$listingId, $path, $thisIsPrimary, $sortOrder]);

    
    $imageId = db()->lastInsertId();

    
    $uploaded[] = [
        'id'         => (int) $imageId,
        'path'       => $path,
        'is_primary' => $thisIsPrimary,
    ];

    $sortOrder++;
}

echo json_encode([
    'ok'       => true,
    'uploaded' => $uploaded,
]);
