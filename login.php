<?php
require_once __DIR__ . '/includes/auth.php';

$error = '';

// Wenn schon eingeloggt -> Dashboard
if (current_user()) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) {
        $error = 'Sicherheits-Token ungültig. Bitte Seite neu laden.';
    } else {
        $login = trim($_POST['login'] ?? '');
        $pass  = $_POST['password'] ?? '';

        if ($login === '' || $pass === '') {
            $error = 'Bitte Benutzername/E-Mail und Passwort eingeben.';
        } else {
            $stmt = db()->prepare(
                'SELECT id, password_hash FROM users WHERE username = ? OR email = ? LIMIT 1'
            );
            $stmt->execute([$login, $login]);
            $row = $stmt->fetch();

            if ($row && password_verify($pass, $row['password_hash'])) {
                login_user((int)$row['id']);
                header('Location: dashboard.php');
                exit;
            }
            $error = 'Login fehlgeschlagen. Überprüfe deine Eingaben.';
            usleep(400000); // kleiner Delay gegen Brute-Force
        }
    }
}

$PAGETITLE = 'Login';
$ACTIVE    = 'login';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="auth-wrap">
    <div class="auth-card card">
      <span class="eyebrow">Interner Bereich</span>
      <h2 style="margin: 10px 0 4px;">Anmelden</h2>
      <p style="color: var(--muted); margin: 0 0 24px;">Melde dich an, um Fotos hochzuladen.</p>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post" class="auth-form" autocomplete="on">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <label class="field">
          <span>Benutzername oder E-Mail</span>
          <input type="text" name="login" required autofocus>
        </label>

        <label class="field">
          <span>Passwort</span>
          <input type="password" name="password" required>
        </label>

        <button type="submit" class="btn-primary">Anmelden</button>
      </form>

      <p class="auth-hint">
        Noch kein Account? <a class="inline-link" href="register.php">Jetzt registrieren</a>
      </p>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
