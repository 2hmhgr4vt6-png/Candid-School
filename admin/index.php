<?php
/**
 * admin/index.php — dashboard.
 *
 * Leads with the things still missing from the website, so whoever runs the
 * school office can see at a glance what needs attention.
 */

declare(strict_types=1);

require_once __DIR__ . '/layout.php';
require_once dirname(__DIR__) . '/includes/notices.php';

require_admin();

$c = content();
$notices = notices_all(false);
$published = array_filter($notices, static fn (array $n): bool => $n['published']);

/* Outstanding content, in the order a visitor would miss it. */
$todo = [];
$check = static function (bool $done, string $label, string $where) use (&$todo): void {
    if (!$done) {
        $todo[] = ['label' => $label, 'where' => $where];
    }
};

$check(trim((string) get_in($c, 'contact.phone')) !== '', 'Add the school phone number', 'contact.php');
$check(trim((string) get_in($c, 'contact.email')) !== '', 'Add the school email address', 'contact.php');
$check(trim((string) get_in($c, 'contact.street')) !== '', 'Add the street or landmark detail to the address', 'contact.php');
$check(trim((string) get_in($c, 'contact.school_hours')) !== '', 'Add the school hours', 'contact.php');
$check(trim((string) get_in($c, 'contact.office_hours')) !== '', 'Add the admissions office hours', 'contact.php');
$check(trim((string) get_in($c, 'identity.established')) !== '', 'Add the year the school was established', 'content.php');
$check(trim((string) get_in($c, 'identity.principal_name')) !== '', "Add the principal's name", 'content.php');
$check(is_array(get_in($c, 'pages.achievements', [])) && get_in($c, 'pages.achievements', []) !== [], 'List some school achievements', 'pages.php');
$check(is_array(get_in($c, 'gallery', [])) && get_in($c, 'gallery', []) !== [], 'Upload photos to the gallery', 'gallery.php');
$check(count($published) > 0, 'Publish your first notice', 'notices.php');
$check(trim((string) get_in($c, 'app.play_store_url')) !== '' || trim((string) get_in($c, 'app.app_store_url')) !== '', 'Add the parent app download links', 'settings.php');

$on_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
$is_local = in_array((string) ($_SERVER['SERVER_NAME'] ?? ''), ['localhost', '127.0.0.1', '::1'], true);

$data_writable = is_dir(DATA_DIR) && is_writable(DATA_DIR);
$gallery_writable = is_dir(GALLERY_DIR) ? is_writable(GALLERY_DIR) : is_writable(ROOT_DIR . '/images');

admin_head('Dashboard', 'index.php');
?>

<?php if (!$data_writable): ?>
  <div class="alert alert-error" role="alert">
    <strong>The <code>data/</code> folder is not writable.</strong> Nothing you change here can be saved until
    it is. In your hosting file manager, set the permissions of <code>data/</code> to <code>755</code> (or
    <code>775</code>) and make sure it is owned by your hosting account.
  </div>
<?php endif; ?>

<?php if (!$gallery_writable): ?>
  <div class="alert alert-info" role="status">
    The <code>images/gallery/</code> folder is not writable, so photo uploads will fail. Set its permissions to
    <code>755</code> to enable them.
  </div>
<?php endif; ?>

<p class="admin-lede">Everything on the public website is edited from here. Changes appear immediately —
  there is nothing to publish or rebuild.</p>

<div class="stat-cards">
  <a class="stat-card" href="<?= e(url('admin/notices.php')) ?>">
    <span class="stat-card-value"><?= count($published) ?></span>
    <span class="stat-card-label">Published notices</span>
    <?php if (count($notices) > count($published)): ?>
      <span class="stat-card-note"><?= count($notices) - count($published) ?> draft(s)</span>
    <?php endif; ?>
  </a>
  <a class="stat-card" href="<?= e(url('admin/gallery.php')) ?>">
    <span class="stat-card-value"><?= count(is_array($c['gallery']) ? $c['gallery'] : []) ?></span>
    <span class="stat-card-label">Gallery photos</span>
  </a>
  <a class="stat-card" href="<?= e(url('admin/pages.php')) ?>">
    <span class="stat-card-value"><?= count(is_array($c['facilities']) ? $c['facilities'] : []) ?></span>
    <span class="stat-card-label">Facilities listed</span>
  </a>
  <a class="stat-card <?= $todo === [] ? 'is-good' : 'is-warn' ?>" href="#todo">
    <span class="stat-card-value"><?= count($todo) ?></span>
    <span class="stat-card-label">Details still missing</span>
  </a>
</div>

<section class="panel" id="todo">
  <div class="panel-head">
    <h2>Still to do</h2>
  </div>
  <?php if ($todo === []): ?>
    <p class="panel-empty">Nothing outstanding — every detail on the website has been filled in. 🎉</p>
  <?php else: ?>
    <ul class="todo-list">
      <?php foreach ($todo as $item): ?>
        <li>
          <span><?= e($item['label']) ?></span>
          <a class="btn btn-sm btn-outline" href="<?= e(url('admin/' . $item['where'])) ?>">Do it</a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</section>

<section class="panel">
  <div class="panel-head">
    <h2>Recent notices</h2>
    <a class="btn btn-sm btn-primary" href="<?= e(url('admin/notices.php#new')) ?>">Add a notice</a>
  </div>

  <?php if ($notices === []): ?>
    <p class="panel-empty">No notices yet. Notices you add here show on the homepage and on the
      <a href="<?= e(url('notices.php')) ?>" target="_blank" rel="noopener">notice board page</a>.</p>
  <?php else: ?>
    <table class="admin-table">
      <thead>
        <tr>
          <th scope="col">Notice</th>
          <th scope="col">Date</th>
          <th scope="col">Status</th>
          <th scope="col"><span class="visually-hidden">Actions</span></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (array_slice($notices, 0, 6) as $n): ?>
          <tr>
            <td>
              <strong><?= e($n['title']) ?></strong>
              <?php if ($n['pinned']): ?><span class="pill pill-gold">Pinned</span><?php endif; ?>
              <span class="muted-line"><?= e($n['category']) ?></span>
            </td>
            <td class="nowrap"><?= e(format_date($n['date'], 'j M Y')) ?></td>
            <td>
              <span class="pill <?= $n['published'] ? 'pill-green' : 'pill-grey' ?>">
                <?= $n['published'] ? 'Published' : 'Draft' ?>
              </span>
            </td>
            <td class="nowrap">
              <a class="btn btn-sm btn-outline" href="<?= e(url('admin/notices.php?edit=' . $n['id'])) ?>">Edit</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>

<?php if (!$on_https && !$is_local): ?>
  <section class="panel">
    <div class="panel-head"><h2>Security</h2></div>
    <div class="alert alert-info" role="status">
      <strong>This panel is being served over plain <code>http://</code>.</strong>
      Your password travels unencrypted on that connection. Most hosts issue a free certificate in one click —
      in cPanel it is under <em>SSL/TLS Status → Run AutoSSL</em>. Once <code>https://</code> works, uncomment the
      "Force HTTPS" block at the bottom of <code>.htaccess</code> and the login cookie becomes HTTPS-only
      automatically.
    </div>
  </section>
<?php endif; ?>

<section class="panel">
  <div class="panel-head"><h2>Where things live</h2></div>
  <ul class="link-grid">
    <li><a href="<?= e(url('admin/notices.php')) ?>"><strong>Notices</strong><span>Post, edit, pin or unpublish announcements.</span></a></li>
    <li><a href="<?= e(url('admin/contact.php')) ?>"><strong>Contact details</strong><span>Phone, email, address, hours, map position, Facebook link.</span></a></li>
    <li><a href="<?= e(url('admin/content.php')) ?>"><strong>School details</strong><span>Tagline, established year, principal, the four homepage statistics.</span></a></li>
    <li><a href="<?= e(url('admin/pages.php')) ?>"><strong>Page text</strong><span>Welcome message, our story, vision, mission, achievements, facilities.</span></a></li>
    <li><a href="<?= e(url('admin/gallery.php')) ?>"><strong>Gallery</strong><span>Upload and caption photos, remove old ones.</span></a></li>
    <li><a href="<?= e(url('admin/settings.php')) ?>"><strong>Settings</strong><span>App store links, enquiry form destination, change your password.</span></a></li>
  </ul>
</section>

<?php admin_foot(); ?>
