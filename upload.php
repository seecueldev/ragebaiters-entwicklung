<?php
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

$user = current_user();
if (!$user) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Nicht angemeldet.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Methode nicht erlaubt.']);
    exit;
}

if (!csrf_check($_POST['csrf'] ?? '')) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'CSRF-Token ungültig.']);
    exit;
}

if (empty($_FILES['photo']) || !isset($_FILES['photo']['error'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Keine Datei empfangen.']);
    exit;
}

$file = $_FILES['photo'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Upload-Fehler (Code ' . (int)$file['error'] . ').']);
    exit;
}

if ($file['size'] > MAX_FILE_BYTES) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Datei zu groß (max. ' . (int)(MAX_FILE_BYTES/1024/1024) . ' MB).']);
    exit;
}

$allowed = unserialize(ALLOWED_MIMES);
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($file['tmp_name']) ?: '';
if (!in_array($mime, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Dateityp nicht erlaubt (' . $mime . ').']);
    exit;
}

$info = @getimagesize($file['tmp_name']);
if (!$info) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Bild konnte nicht gelesen werden.']);
    exit;
}
[$width, $height] = $info;

// Dateiname erzeugen
$extByMime = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];
$ext = $extByMime[$mime];
$base = bin2hex(random_bytes(12));
$filename  = $base . '.' . $ext;
$thumbname = $base . '_t.jpg';

if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0755, true)) { }
if (!is_dir(THUMB_DIR)  && !mkdir(THUMB_DIR, 0755, true))  { }

$targetPath = UPLOAD_DIR . $filename;
$thumbPath  = THUMB_DIR . $thumbname;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Speichern fehlgeschlagen.']);
    exit;
}

// Thumbnail erzeugen
try {
    makeThumbnail($targetPath, $thumbPath, THUMB_SIZE, $mime);
} catch (Throwable $e) {
    // Thumb ist nice-to-have – Fehler nicht fatal
    $thumbname = $filename;
}

$title = trim((string)($_POST['title'] ?? ''));
if ($title === '') {
    $title = pathinfo($file['name'], PATHINFO_FILENAME);
}
$title = mb_substr($title, 0, 160);

$stmt = db()->prepare(
    'INSERT INTO photos (user_id, filename, thumbname, title, mime, size_bytes, width, height)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([
    (int)$user['id'], $filename, $thumbname, $title, $mime,
    (int)$file['size'], (int)$width, (int)$height
]);
$id = (int)db()->lastInsertId();

echo json_encode([
    'ok'    => true,
    'photo' => [
        'id'    => $id,
        'title' => $title,
        'url'   => UPLOAD_URL . $filename,
        'thumb' => (strpos($thumbname, '_t') !== false ? THUMB_URL : UPLOAD_URL) . $thumbname,
    ],
]);

// ----------------------------------------
function makeThumbnail(string $src, string $dst, int $maxSide, string $mime): void {
    switch ($mime) {
        case 'image/jpeg': $img = imagecreatefromjpeg($src); break;
        case 'image/png':  $img = imagecreatefrompng($src);  break;
        case 'image/webp': $img = imagecreatefromwebp($src); break;
        case 'image/gif':  $img = imagecreatefromgif($src);  break;
        default: throw new RuntimeException('Unsupported');
    }
    if (!$img) throw new RuntimeException('decode failed');

    $w = imagesx($img);
    $h = imagesy($img);
    if (max($w, $h) <= $maxSide) {
        $nw = $w; $nh = $h;
    } elseif ($w >= $h) {
        $nw = $maxSide;
        $nh = (int)round($h * ($maxSide / $w));
    } else {
        $nh = $maxSide;
        $nw = (int)round($w * ($maxSide / $h));
    }
    $thumb = imagecreatetruecolor($nw, $nh);
    imagecopyresampled($thumb, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
    imagejpeg($thumb, $dst, 82);
    imagedestroy($img);
    imagedestroy($thumb);
}
