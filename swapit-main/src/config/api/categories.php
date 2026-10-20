<?php


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../../helpers/api.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

$pdo = db();

if ($id) {
    
    $stmt = $pdo->prepare(
        'SELECT id, name, slug, icon, parent_id, sort_order FROM categories WHERE id = ? LIMIT 1'
    );
    $stmt->execute([$id]);
    $cat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cat) {
        jsonError('Category not found', 404);
    }

    $cat['id'] = (int) $cat['id'];
    if ($cat['parent_id'] !== null) {
        $cat['parent_id'] = (int) $cat['parent_id'];
    }

    $childStmt = $pdo->prepare(
        'SELECT id, name, slug, icon, sort_order FROM categories WHERE parent_id = ? ORDER BY sort_order'
    );
    $childStmt->execute([$id]);
    $children = $childStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($children as &$c) {
        $c['id'] = (int) $c['id'];
    }
    unset($c);

    $cat['children'] = $children;
    jsonResponse(['ok' => true, 'category' => $cat]);
}


$topStmt = $pdo->query(
    'SELECT id, name, slug, icon, sort_order FROM categories WHERE parent_id IS NULL ORDER BY sort_order'
);
$tops = $topStmt->fetchAll(PDO::FETCH_ASSOC);

$childStmt = $pdo->query(
    'SELECT id, name, slug, icon, parent_id, sort_order FROM categories WHERE parent_id IS NOT NULL ORDER BY sort_order'
);
$allChildren = $childStmt->fetchAll(PDO::FETCH_ASSOC);

$byParent = [];
foreach ($allChildren as $child) {
    $pid = (int) $child['parent_id'];
    $byParent[$pid][] = ['id' => (int) $child['id'], 'name' => $child['name'], 'slug' => $child['slug'], 'icon' => $child['icon'], 'sort_order' => (int) $child['sort_order']];
}

foreach ($tops as &$cat) {
    $cat['id']       = (int) $cat['id'];
    $cat['children'] = $byParent[$cat['id']] ?? [];
}
unset($cat);

jsonResponse(['ok' => true, 'categories' => $tops]);
