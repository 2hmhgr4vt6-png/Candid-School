<?php
/**
 * admin/setup.php — first-run password creation.
 *
 * Reachable only while no password hash exists. Once data/admin.php holds a
 * hash this page redirects to the login screen, so it cannot be used to
 * overwrite the password later (that is admin/settings.php, behind the login).
 */

declare(strict_types=1);

require_once __DIR__ . '/layout.php';

session_boot();

if (admin_password_is_set()) {
    redirect(url('admin/login.php'));
}

$error = null;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_csrf();

    $password = (string) ($_POST['password'] ?? '');
    $confirm  = (string) ($_POST['confirm'] ?? '');

    $error = admin_password_problem($password, $confirm);

    if ($error === null) {
        if (admin_set_password($password)) {
            admin_login($password);
            flash('Admin password set. Welcome — start by filling in your contact details.', 'success');
            redirect(url('admin/contact.php'));
        }
        $error = 'The password could not be saved. Check that the data/ folder is writable (permissions 755).';
    }
}

admin_head('Set up your admin password', '', false);
?>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-brand">
      <span class="admin-brand-mark" aria-hidden="true">CC</span>
      <h1>Welcome — let's secure your admin panel</h1>
      <p>Choose the password the school office will use to sign in and update the website. It is the only
        password for this panel, so store it somewhere safe.</p>
    </div>

    <?php if ($error !== null): ?>
      <div class="alert alert-error" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= e(url('admin/setup.php')) ?>" autocomplete="off">
      <?= csrf_field() ?>
      <div class="field">
        <label for="password">New password</label>
        <input type="password" id="password" name="password" required minlength="10"
               autocomplete="new-password" autofocus />
        <p class="hint">At least 10 characters. A short phrase you will remember — like
          <em>sirutar-school-2026</em> — is stronger than a short complicated word.</p>
      </div>
      <div class="field">
        <label for="confirm">Repeat the password</label>
        <input type="password" id="confirm" name="confirm" required minlength="10" autocomplete="new-password" />
      </div>
      <button class="btn btn-primary btn-block" type="submit">Save password and sign in</button>
    </form>

    <p class="auth-note">Forgot it later? Delete <code>data/admin.php</code> on the server and this page
      returns so you can set a new one.</p>
  </div>
</div>
<?php admin_foot(false); ?>
