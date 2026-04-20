<?php
require_once __DIR__ . '/includes/auth.php';

$stmt = db()->query(
    "SELECT p.id, p.filename, p.thumbname, p.title, p.uploaded_at, u.username
     FROM photos p
     JOIN users u ON u.id = p.user_id
     ORDER BY p.uploaded_at DESC"
);
$photos = $stmt->fetchAll();

$PAGETITLE = 'Mediathek';
$ACTIVE    = 'mediathek';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
  <span class="eyebrow">Galerie</span>
  <h2 style="margin: 10px 0 4px;">Mediathek</h2>
  <p style="color: var(--muted); margin: 0 0 30px;">
    Eindrücke und Momente der Ragebaiters.
  </p>

  <?php if (empty($photos)): ?>
    <div class="card" style="text-align:center; color: var(--muted);">
      Es wurden noch keine Fotos hochgeladen.
    </div>
  <?php else: ?>
    <div class="photo-grid lightbox-grid">
      <?php foreach ($photos as $p): ?>
        <figure class="photo-item"
                data-full="uploads/<?= e($p['filename']) ?>"
                data-title="<?= e($p['title'] ?? '') ?>"
                data-author="<?= e($p['username']) ?>">
          <img src="uploads/thumbs/<?= e($p['thumbname']) ?>"
               onerror="this.onerror=null;this.src='uploads/<?= e($p['filename']) ?>';"
               alt="<?= e($p['title'] ?? '') ?>" loading="lazy">
          <figcaption>
            <span><?= e($p['title'] ?? '') ?></span>
            <small>von <?= e($p['username']) ?></small>
          </figcaption>
        </figure>
      <?php endforeach; ?>
    </div>

    <!-- Lightbox -->
    <div id="lightbox" class="lightbox" role="dialog" aria-hidden="true">
      <button class="lightbox-close" type="button" aria-label="Schließen">&times;</button>
      <button class="lightbox-nav prev" type="button" aria-label="Vorheriges">‹</button>
      <button class="lightbox-nav next" type="button" aria-label="Nächstes">›</button>
      <img class="lightbox-img" src="" alt="">
      <div class="lightbox-caption"></div>
    </div>
  <?php endif; ?>
</section>

<script src="app.js"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
