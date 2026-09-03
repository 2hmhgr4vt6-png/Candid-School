<?php
/**
 * admin/settings.php — parent app links, enquiry form destination, password.
 */

declare(strict_types=1);

require_once __DIR__ . '/layout.php';

require_admin();

$c = content();
$app = $c['app'];
$form_cfg = $c['form'];
$errors = [];
$pw_errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_csrf();

    $action = (string) ($_POST['action'] ?? '');

    /* ---- app links + form endpoint ---- */
    if ($action === 'save') {
        $app = [
            'play_store_url' => mb_substr(trim((string) ($_POST['play_store_url'] ?? '')), 0, 400),
            'app_store_url'  => mb_substr(trim((string) ($_POST['app_store_url'] ?? '')), 0, 400),
        ];
        $form_cfg = [
            'action' => mb_substr(trim((string) ($_POST['form_action'] ?? '')), 0, 400),
        ];

        foreach (['play_store_url' => 'Google Play link', 'app_store_url' => 'App Store link'] as $key => $label) {
            if ($app[$key] !== '' && !filter_var($app[$key], FILTER_VALIDATE_URL)) {
                $errors[$key] = 'Please paste the full ' . $label . ', starting with https://';
            }
        }
        if ($form_cfg['action'] !== '' && !filter_var($form_cfg['action'], FILTER_VALIDATE_URL)) {
            $errors['form_action'] = 'Please paste the full endpoint address, starting with https:// — or leave it blank.';
        }

        if ($errors === []) {
            if (content_save_section('app', $app) && content_save_section('form', $form_cfg)) {
                flash('Settings saved.', 'success');
                redirect(url('admin/settings.php'));
            }
            $errors['save'] = 'Could not save. Check that the data/ folder is writable (permissions 755).';
        }
    }

    /* ---- change password ---- */
    if ($action === 'password') {
        $current = (string) ($_POST['current'] ?? '');
        $new     = (string) ($_POST['new'] ?? '');
        $confirm = (string) ($_POST['confirm'] ?? '');

        $hash = (string) (admin_config()['password_hash'] ?? '');

        if (!password_verify($current, $hash)) {
            $pw_errors['current'] = 'That is not the current password.';
        }

        $problem = admin_password_problem($new, $confirm);
        if ($problem !== null) {
            $pw_errors['new'] = $problem;
        }

        if ($pw_errors === []) {
            if (admin_set_password($new)) {
                flash('Password changed. Use the new one next time you sign in.', 'success');
                redirect(url('admin/settings.php'));
            }
            $pw_errors['new'] = 'The new password could not be saved. Check that data/ is writable.';
        }
    }
}

admin_head('Settings', 'settings.php');
?>

<?php if (isset($errors['save'])): ?>
  <div class="alert alert-error" role="alert"><?= e($errors['save']) ?></div>
<?php endif; ?>

<form method="POST" action="<?= e(url('admin/settings.php')) ?>" data-warn-unsaved>
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="save" />

  <section class="panel">
    <div class="panel-head"><h2>Parent app download links</h2></div>
    <p class="panel-lede">The two store buttons in the Parent App section. Until a link is filled in, its button
      is shown greyed out and does nothing.</p>

    <div class="field<?= isset($errors['play_store_url']) ? ' has-error' : '' ?>">
      <label for="play_store_url">Google Play link</label>
      <input type="url" id="play_store_url" name="play_store_url" value="<?= e((string) $app['play_store_url']) ?>"
             placeholder="https://play.google.com/store/apps/details?id=..." />
      <?php if (isset($errors['play_store_url'])): ?><p class="error"><?= e($errors['play_store_url']) ?></p><?php endif; ?>
    </div>

    <div class="field<?= isset($errors['app_store_url']) ? ' has-error' : '' ?>">
      <label for="app_store_url">Apple App Store link</label>
      <input type="url" id="app_store_url" name="app_store_url" value="<?= e((string) $app['app_store_url']) ?>"
             placeholder="https://apps.apple.com/app/id..." />
      <?php if (isset($errors['app_store_url'])): ?><p class="error"><?= e($errors['app_store_url']) ?></p><?php endif; ?>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Admissions enquiry form</h2></div>
    <p class="panel-lede">By default the enquiry form opens the visitor's own email app, addressed to the school
      email in <a href="<?= e(url('admin/contact.php')) ?>">Contact details</a>. That works everywhere but relies
      on the visitor having email set up. To collect enquiries properly, paste a form endpoint below.</p>

    <div class="field<?= isset($errors['form_action']) ? ' has-error' : '' ?>">
      <label for="form_action">Form endpoint <span class="optional">optional</span></label>
      <input type="url" id="form_action" name="form_action" value="<?= e((string) get_in($c, 'form.action', '')) ?>"
             placeholder="https://formspree.io/f/YOUR_FORM_ID" />
      <p class="hint">Create a free form at <a href="https://formspree.io" target="_blank" rel="noopener">formspree.io</a>
        and paste the address it gives you. The form posts the fields
        <code>name</code>, <code>phone</code>, <code>email</code>, <code>grade</code> and <code>message</code>.</p>
      <?php if (isset($errors['form_action'])): ?><p class="error"><?= e($errors['form_action']) ?></p><?php endif; ?>
    </div>
  </section>

  <div class="form-actions">
    <button class="btn btn-primary" type="submit">Save settings</button>
  </div>
</form>

<section class="panel">
  <div class="panel-head"><h2>Change the admin password</h2></div>

  <?php if ($pw_errors !== []): ?>
    <div class="alert alert-error" role="alert">Please check the fields below.</div>
  <?php endif; ?>

  <form method="POST" action="<?= e(url('admin/settings.php')) ?>" autocomplete="off">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="password" />

    <div class="field third<?= isset($pw_errors['current']) ? ' has-error' : '' ?>">
      <label for="current">Current password</label>
      <input type="password" id="current" name="current" required autocomplete="current-password" />
      <?php if (isset($pw_errors['current'])): ?><p class="error"><?= e($pw_errors['current']) ?></p><?php endif; ?>
    </div>

    <div class="field-row">
      <div class="field<?= isset($pw_errors['new']) ? ' has-error' : '' ?>">
        <label for="new">New password</label>
        <input type="password" id="new" name="new" required minlength="10" autocomplete="new-password" />
        <p class="hint">At least 10 characters.</p>
        <?php if (isset($pw_errors['new'])): ?><p class="error"><?= e($pw_errors['new']) ?></p><?php endif; ?>
      </div>
      <div class="field">
        <label for="confirm">Repeat the new password</label>
        <input type="password" id="confirm" name="confirm" required minlength="10" autocomplete="new-password" />
      </div>
    </div>

    <div class="form-actions">
      <button class="btn btn-primary" type="submit">Change password</button>
    </div>
  </form>
</section>

<section class="panel">
  <div class="panel-head"><h2>About this panel</h2></div>
  <ul class="fact-list">
    <li><strong>Where content is stored</strong><span><code>data/content.json</code> and <code>data/notices.json</code>.
      Back these two files up and you have backed up the whole website's content.</span></li>
    <li><strong>Uploads</strong><span>Photos go to <code>images/gallery/</code>, notice attachments to
      <code>files/</code>.</span></li>
    <li><strong>Password recovery</strong><span>There is none by design. If the password is lost, delete
      <code>data/admin.php</code> on the server and the first-run setup page returns.</span></li>
    <li><strong>Sessions</strong><span>You are signed out automatically after 2 hours of inactivity.</span></li>
  </ul>
</section>

<?php admin_foot(); ?>
