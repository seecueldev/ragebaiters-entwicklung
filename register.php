<?php
require_once __DIR__ . '/includes/auth.php';

$error = '';
$success = false;

if (current_user()) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) {
        $error = 'Sicherheits-Token ungültig. Bitte Seite neu laden.';
    } else {
        $invite = trim($_POST['invite'] ?? '');
        $user   = trim($_POST['username'] ?? '');
        $mail   = trim($_POST['email'] ?? '');
        $pass   = $_POST['password'] ?? '';
        $pass2  = $_POST['password2'] ?? '';

        if ($invite === '' || $user === '' || $mail === '' || $pass === '') {
            $error = 'Bitte alle Felder ausfüllen.';
        } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $error = 'Bitte eine gültige E-Mail-Adresse eingeben.';
        } elseif (!preg_match('/^[A-Za-z0-9_\-\.]{3,32}$/', $user)) {
            $error = 'Benutzername: 3–32 Zeichen, nur Buchstaben, Ziffern, _ - .';
        } elseif (strlen($pass) < 8) {
            $error = 'Passwort muss mindestens 8 Zeichen haben.';
        } elseif ($pass !== $pass2) {
            $error = 'Die Passwörter stimmen nicht überein.';
        } else {
            // Einladungscode prüfen
            $stmt = db()->prepare('SELECT id FROM invites WHERE code = ? AND used_by IS NULL');
            $stmt->execute([$invite]);
            $inviteRow = $stmt->fetch();

            if (!$inviteRow) {
                $error = 'Einladungscode ungültig oder bereits verwendet.';
            } else {
                // Doppelt vorhanden?
                $stmt = db()->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
                $stmt->execute([$user, $mail]);
                if ($stmt->fetch()) {
                    $error = 'Benutzername oder E-Mail bereits vergeben.';
                } else {
                    try {
                        db()->beginTransaction();
                        $hash = password_hash($pass, PASSWORD_DEFAULT);
                        $ins = db()->prepare(
                            'INSERT INTO users (username, email, password_hash, role)
                             VALUES (?, ?, ?, "member")'
                        );
                        $ins->execute([$user, $mail, $hash]);
                        $newId = (int)db()->lastInsertId();

                        $upd = db()->prepare(
                            'UPDATE invites SET used_by = ?, used_at = NOW() WHERE id = ?'
                        );
                        $upd->execute([$newId, $inviteRow['id']]);

                        db()->commit();
                        login_user($newId);
                        header('Location: dashboard.php');
                        exit;
                    } catch (Throwable $e) {
                        db()->rollBack();
                        $error = 'Registrierung fehlgeschlagen. Bitte später erneut versuchen.';
                    }
                }
            }
        }
    }
}

$PAGETITLE = 'Registrieren';
$ACTIVE    = 'login';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="auth-wrap">
    <div class="auth-card card">
      <span class="eyebrow">Neuer Account</span>
      <h2 style="margin: 10px 0 4px;">Registrieren</h2>
      <p style="color: var(--muted); margin: 0 0 24px;">
        Du brauchst einen Einladungscode vom Teamführer.
      </p>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post" class="auth-form" autocomplete="on">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <label class="field">
          <span>Einladungscode</span>
          <input type="text" name="invite" required autofocus>
        </label>

        <label class="field">
          <span>Benutzername</span>
          <input type="text" name="username" required minlength="3" maxlength="32"
                 pattern="[A-Za-z0-9_\-\.]+">
        </label>

        <label class="field">
          <span>E-Mail</span>
          <input type="email" name="email" required>
        </label>

        <label class="field">
          <span>Passwort (min. 8 Zeichen)</span>
          <input type="password" name="password" required minlength="8">
        </label>

        <label class="field">
          <span>Passwort wiederholen</span>
          <input type="password" name="password2" required minlength="8">
        </label>

        <button type="submit" class="btn-primary">Registrieren</button>
      </form>

      <p class="auth-hint">
        Schon einen Account? <a class="inline-link" href="login.php">Zum Login</a>
      </p>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
