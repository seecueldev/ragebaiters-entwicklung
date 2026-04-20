<?php
/**
 * Zentrale Konfiguration für die Ragebaiters-Seite auf bplaced.
 * Bei bplaced findest du die Zugangsdaten in deinem Admin-Panel
 * unter "MySQL Datenbanken".
 */

// === DATENBANK (bei bplaced anpassen!) ===
define('DB_HOST', 'myadmin.rgbait-dbtest.bplaced.net');          // bei bplaced in der Regel "localhost"
define('DB_NAME', 'rgbait-dbtest_webseite');       // z.B. "ragebaiters"
define('DB_USER', 'rgbait-dbtest_tobi');       // dein MySQL-Benutzername
define('DB_PASS', 'jason12345');   // dein MySQL-Passwort

// === UPLOAD-EINSTELLUNGEN ===
define('UPLOAD_DIR',      __DIR__ . '/../uploads/');
define('THUMB_DIR',       __DIR__ . '/../uploads/thumbs/');
define('UPLOAD_URL',      'uploads/');
define('THUMB_URL',       'uploads/thumbs/');
define('MAX_FILE_BYTES',  8 * 1024 * 1024); // 8 MB pro Bild
define('ALLOWED_MIMES',   serialize(['image/jpeg', 'image/png', 'image/webp', 'image/gif']));
define('THUMB_SIZE',      400); // Thumbnail-Größe in Pixel (längere Seite)

// === SICHERHEIT ===
define('SITE_NAME', 'die Ragebaiters');

// Sessions härten
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (!empty($_SERVER['HTTPS'])) {
    ini_set('session.cookie_secure', '1');
}
session_name('RAGEBAITERS_SID');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fehlerausgabe im Livebetrieb ausschalten
ini_set('display_errors', '0');
error_reporting(E_ALL);
