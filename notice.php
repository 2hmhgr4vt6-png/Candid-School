<?php
/**
 * notice.php — a single notice, at a shareable URL.
 * Unpublished or unknown ids return a real 404 rather than an empty page.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/content.php';
require_once __DIR__ . '/includes/notices.php';
require_once __DIR__ . '/includes/uploads.php';

$c = content();
$id = (int) ($_GET['id'] ?? 0);
$notice = $id > 0 ? notice_find($id, true) : null;

if ($notice === null) {
    http_response_code(404);

    $is_home = false;
    $page_title = 'Notice not found — ' . $c['identity']['school_name'];
    require __DIR__ . '/includes/public-header.php';
    ?>
    <section class="section">
      <div class="container empty-state">
        <h1>We could not find that notice</h1>
        <p>It may have been removed, or the link may be incomplete.</p>
        <p><a class="btn btn-primary" href="<?= e(url('notices.php')) ?>">See all notices</a></p>
      </div>
    </section>
    <?php
    require __DIR__ . '/includes/public-footer.php';
    exit;
}

/* Neighbouring notices for the prev/next links. */
$all = notices_all(true);
$index = null;
foreach ($all as $i => $n) {
    if ($n['id'] === $notice['id']) {
        $index = $i;
        break;
    }
}
$newer = ($index !== null && $index > 0) ? $all[$index - 1] : null;
$older = ($index !== null && $index < count($all) - 1) ? $all[$index + 1] : null;

$is_home = false;
$page_title = $notice['title'] . ' — ' . $c['identity']['school_name'];
$page_description = excerpt($notice['body'], 155) ?: ('Notice from ' . $c['identity']['school_name']);

require __DIR__ . '/includes/public-header.php';
?>

    <section class="page-head">
      <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
          <a href="<?= e(url('index.php')) ?>">Home</a>
          <span aria-hidden="true">/</span>
          <a href="<?= e(url('notices.php')) ?>">Notices</a>
          <span aria-hidden="true">/</span>
          <span aria-current="page"><?= e(excerpt($notice['title'], 48)) ?></span>
        </nav>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <article class="notice-full">
          <div class="notice-meta">
            <time datetime="<?= e($notice['date']) ?>"><?= e(format_date($notice['date'])) ?></time>
            <span class="notice-tag"><?= e($notice['category']) ?></span>
            <?php if ($notice['pinned']): ?><span class="notice-tag pinned">Pinned</span><?php endif; ?>
          </div>

          <h1><?= e($notice['title']) ?></h1>

          <div class="notice-body">
            <?= paragraphs($notice['body']) ?: '<p class="note dim">This notice has no further details.</p>' ?>
          </div>

          <?php if ($notice['attachment'] !== '' && is_file(files_dir() . '/' . basename($notice['attachment']))): ?>
            <p class="notice-attachment">
              <a class="btn btn-outline" href="<?= e(url('files/' . $notice['attachment'])) ?>" target="_blank" rel="noopener">
                <span aria-hidden="true">📎</span> Download attachment
              </a>
            </p>
          <?php endif; ?>

          <?php if (trim((string) $notice['updated_at']) !== ''): ?>
            <p class="note dim notice-stamp">Last updated <?= e(date('j F Y', strtotime($notice['updated_at']) ?: time())) ?>.</p>
          <?php endif; ?>
        </article>

        <nav class="notice-nav" aria-label="More notices">
          <?php if ($newer !== null): ?>
            <a class="notice-nav-link prev" href="<?= e(notice_url($newer)) ?>">
              <small>Newer notice</small><span><?= e(excerpt($newer['title'], 60)) ?></span>
            </a>
          <?php else: ?><span></span><?php endif; ?>

          <?php if ($older !== null): ?>
            <a class="notice-nav-link next" href="<?= e(notice_url($older)) ?>">
              <small>Older notice</small><span><?= e(excerpt($older['title'], 60)) ?></span>
            </a>
          <?php endif; ?>
        </nav>

        <p class="center-note"><a class="btn btn-outline" href="<?= e(url('notices.php')) ?>">All notices</a></p>
      </div>
    </section>

<?php require __DIR__ . '/includes/public-footer.php'; ?>
