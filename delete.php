<?php
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

$user = current_user();
if (!$user) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Nicht angemeldet.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'] ?? '')) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Ungültige Anfrage.']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['ok' => false, 'error' => 'ID fehlt.']);
    exit;
}

$stmt = db()->prepare('SELECT * FROM photos WHERE id = ?');
$stmt->execute([$id]);
$photo = $stmt->fetch();
if (!$photo) {
    echo json_encode(['ok' => false, 'error' => 'Foto nicht gefunden.']);
    exit;
}

// Nur Besitzer oder Admin darf löschen
if ((int)$photo['user_id'] !== (int)$user['id'] && $user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Keine Berechtigung.']);
    exit;
}

@unlink(UPLOAD_DIR . $photo['filename']);
@unlink(THUMB_DIR  . $photo['thumbname']);

$stmt = db()->prepare('DELETE FROM photos WHERE id = ?');
$stmt->execute([$id]);

echo json_encode(['ok' => true]);
