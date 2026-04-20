<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_login();

$stmt = db()->prepare(
    'SELECT id, filename, thumbname, title, uploaded_at
     FROM photos WHERE user_id = ?
     ORDER BY uploaded_at DESC'
);
$stmt->execute([$user['id']]);
$myPhotos = $stmt->fetchAll();

$PAGETITLE = 'Dashboard';
$ACTIVE    = 'dashboard';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
  <span class="eyebrow">Interner Bereich</span>
  <h2 style="margin: 10px 0 4px;">Willkommen, <?= e($user['username']) ?></h2>
  <p style="color: var(--muted); margin: 0 0 30px;">
    Lade hier Fotos hoch. Sie erscheinen automatisch in der Mediathek.
  </p>

  <div class="card upload-card" id="uploadCard">
    <input type="file" id="fileInput" accept="image/jpeg,image/png,image/webp,image/gif" multiple hidden>
    <input type="hidden" id="csrfToken" value="<?= e(csrf_token()) ?>">

    <div class="upload-dropzone" id="dropzone">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
           stroke-linecap="round" stroke-linejoin="round" class="upload-icon">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
        <polyline points="17 8 12 3 7 8"/>
        <line x1="12" y1="3" x2="12" y2="15"/>
      </svg>
      <h3 style="margin: 10px 0 6px;">Bilder hier ablegen</h3>
      <p style="color: var(--muted); margin: 0;">oder klicken zum Auswählen</p>
      <p style="color: var(--muted); font-size: 0.8rem; margin-top: 10px;">
        JPG, PNG, WebP, GIF · max. <?= (int)(MAX_FILE_BYTES/1024/1024) ?> MB pro Datei
      </p>
    </div>

    <div id="uploadList" class="upload-list"></div>
  </div>

  <h3 style="margin: 50px 0 20px;">Meine Fotos (<?= count($myPhotos) ?>)</h3>

  <?php if (empty($myPhotos)): ?>
    <div class="card" style="text-align:center; color: var(--muted);">
      Noch keine Bilder hochgeladen.
    </div>
  <?php else: ?>
    <div class="photo-grid">
      <?php foreach ($myPhotos as $p): ?>
        <figure class="photo-item" data-id="<?= (int)$p['id'] ?>">
          <a href="uploads/<?= e($p['filename']) ?>" target="_blank" rel="noopener">
            <img src="uploads/thumbs/<?= e($p['thumbname']) ?>"
                 onerror="this.onerror=null;this.src='uploads/<?= e($p['filename']) ?>';"
                 alt="<?= e($p['title'] ?? '') ?>" loading="lazy">
          </a>
          <figcaption>
            <span><?= e($p['title'] ?? '') ?></span>
            <button class="btn-delete" type="button" data-id="<?= (int)$p['id'] ?>" title="Löschen">✕</button>
          </figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<script src="app.js"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
