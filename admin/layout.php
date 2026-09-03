<?php
/**
 * admin/layout.php — chrome shared by every admin screen.
 *
 * Usage:
 *   require_once __DIR__ . '/layout.php';
 *   admin_head('Notices', 'notices');
 *   ... page content ...
 *   admin_foot();
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/content.php';

/** Admin navigation: file => label. */
function admin_nav(): array
{
    return [
        'index.php'    => ['Dashboard', '⌂'],
        'notices.php'  => ['Notices', '📣'],
        'contact.php'  => ['Contact details', '☎'],
        'content.php'  => ['School details', '🏫'],
        'pages.php'    => ['Page text', '✎'],
        'gallery.php'  => ['Gallery', '🖼'],
        'settings.php' => ['Settings', '⚙'],
    ];
}

function admin_head(string $title, string $current = '', bool $chrome = true): void
{
    $school = (string) get_in(content(), 'identity.short_name', 'Candid Career');
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />
  <title><?= e($title) ?> — Admin · <?= e($school) ?></title>
  <link rel="icon" href="<?= e(url('images/favicon.png')) ?>" type="image/png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= e(url('css/admin.css')) ?>" />
</head>
<body class="<?= $chrome ? 'admin' : 'admin admin-bare' ?>">
<?php if ($chrome): ?>
  <a class="skip-link" href="#admin-main">Skip to content</a>

  <header class="admin-topbar">
    <div class="admin-topbar-inner">
      <a class="admin-brand" href="<?= e(url('admin/index.php')) ?>">
        <span class="admin-brand-mark" aria-hidden="true">CC</span>
        <span>
          <strong><?= e($school) ?></strong>
          <small>Website admin</small>
        </span>
      </a>

      <button class="admin-nav-toggle" id="adminNavToggle" aria-expanded="false" aria-controls="adminNav">
        <span aria-hidden="true">☰</span> Menu
      </button>

      <div class="admin-topbar-actions">
        <a class="admin-view-site" href="<?= e(url('index.php')) ?>" target="_blank" rel="noopener">View site ↗</a>
        <a class="admin-logout" href="<?= e(url('admin/logout.php')) ?>">Log out</a>
      </div>
    </div>
  </header>

  <div class="admin-shell">
    <nav class="admin-side" id="adminNav" aria-label="Admin sections">
      <ul>
        <?php foreach (admin_nav() as $file => [$label, $icon]): ?>
          <?php $active = ($current !== '' && str_starts_with($file, $current)) || basename($_SERVER['SCRIPT_NAME'] ?? '') === $file; ?>
          <li>
            <a href="<?= e(url('admin/' . $file)) ?>"<?= $active ? ' class="is-active" aria-current="page"' : '' ?>>
              <span class="ico" aria-hidden="true"><?= $icon ?></span><?= e($label) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
      <p class="admin-side-note">Changes go live on the website the moment you save.</p>
    </nav>

    <main class="admin-main" id="admin-main">
      <div class="admin-page">
        <h1 class="admin-title"><?= e($title) ?></h1>
        <?php admin_flashes(); ?>
<?php endif; ?>
<?php
}

/** Print and clear any queued flash messages. */
function admin_flashes(): void
{
    foreach (flash_take() as $msg) {
        $kind = in_array($msg['kind'], ['success', 'error', 'info'], true) ? $msg['kind'] : 'info';
        printf(
            '<div class="alert alert-%s" role="%s">%s</div>' . "\n",
            e($kind),
            $kind === 'error' ? 'alert' : 'status',
            e((string) $msg['message'])
        );
    }
}

function admin_foot(bool $chrome = true): void
{
    if ($chrome) {
        ?>
      </div>
    </main>
  </div>

  <script>
    /* Sidebar drawer on narrow screens. */
    (function () {
      var t = document.getElementById('adminNavToggle');
      var n = document.getElementById('adminNav');
      if (!t || !n) return;
      t.addEventListener('click', function () {
        var open = n.classList.toggle('is-open');
        t.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    })();

    /* Warn before leaving a form with unsaved edits. */
    (function () {
      var forms = document.querySelectorAll('form[data-warn-unsaved]');
      var dirty = false;
      forms.forEach(function (f) {
        f.addEventListener('input', function () { dirty = true; });
        f.addEventListener('submit', function () { dirty = false; });
      });
      window.addEventListener('beforeunload', function (e) {
        if (!dirty) return;
        e.preventDefault();
        e.returnValue = '';
      });
    })();

    /* Confirm destructive actions. */
    (function () {
      document.querySelectorAll('form[data-confirm]').forEach(function (f) {
        f.addEventListener('submit', function (e) {
          if (!window.confirm(f.getAttribute('data-confirm'))) e.preventDefault();
        });
      });
    })();

    /* Repeatable list rows: add / remove without a page reload.
       Each new row gets a unique index substituted for __i__ in the template,
       so the rows post as separate array entries instead of overwriting. */
    (function () {
      var seq = 1000;

      document.querySelectorAll('[data-repeat]').forEach(function (wrap) {
        var tpl = wrap.querySelector('template');
        var list = wrap.querySelector('[data-repeat-list]');
        var add = wrap.querySelector('[data-repeat-add]');
        if (!tpl || !list || !add) return;

        add.addEventListener('click', function () {
          list.insertAdjacentHTML('beforeend', tpl.innerHTML.replace(/__i__/g, String(seq++)));
          var added = list.lastElementChild;
          var first = added && added.querySelector('input, textarea');
          if (first) first.focus();
        });

        list.addEventListener('click', function (e) {
          var btn = e.target.closest('[data-repeat-remove]');
          if (!btn) return;
          var row = btn.closest('[data-repeat-row]');
          if (row) row.remove();
        });
      });
    })();
  </script>
</body>
</html>
        <?php
    } else {
        ?>
</body>
</html>
        <?php
    }
}
