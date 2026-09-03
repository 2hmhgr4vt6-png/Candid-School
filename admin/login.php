<?php
/**
 * admin/login.php — password entry.
 */

declare(strict_types=1);

require_once __DIR__ . '/layout.php';

session_boot();

if (!admin_password_is_set()) {
    redirect(url('admin/setup.php'));
}
if (admin_is_logged_in()) {
    redirect(url('admin/index.php'));
}

$error = null;

/**
 * Only allow redirecting back to a path inside this site's admin area, so a
 * crafted ?next= cannot bounce the user to another domain.
 */
$next = (string) ($_GET['next'] ?? $_POST['next'] ?? '');
$safe_next = url('admin/index.php');
if ($next !== '' && !preg_match('#^(https?:)?//#', $next) && str_contains($next, '/admin/')) {
    $safe_next = $next;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_csrf();

    $error = admin_login((string) ($_POST['password'] ?? ''));

    if ($error === null) {
        redirect($safe_next);
    }
}

$locked = login_lockout_remaining();

admin_head('Sign in', '', false);
?>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-brand">
      <span class="admin-brand-mark" aria-hidden="true">CC</span>
      <h1>Website admin</h1>
      <p>Sign in to post notices and update the school's website.</p>
    </div>

    <?php if ($error !== null): ?>
      <div class="alert alert-error" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($locked > 0): ?>
      <div class="alert alert-info" role="status">
        Sign-in is paused for <?= (int) ceil($locked / 60) ?> more minute(s) after repeated failed attempts.
      </div>
    <?php endif; ?>

    <form method="POST" action="<?= e(url('admin/login.php')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="next" value="<?= e($safe_next) ?>" />
      <div class="field">
        <label for="password">Admin password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password"
               autofocus <?= $locked > 0 ? 'disabled' : '' ?> />
      </div>
      <button class="btn btn-primary btn-block" type="submit" <?= $locked > 0 ? 'disabled' : '' ?>>Sign in</button>
    </form>

    <p class="auth-note"><a href="<?= e(url('index.php')) ?>">← Back to the website</a></p>
  </div>
</div>
<?php admin_foot(false); ?>
