<?php
/**
 * notices.php — public notice board: every published notice, newest first.
 * (The storage layer lives in includes/notices.php.)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/content.php';
require_once __DIR__ . '/includes/notices.php';

$c = content();
$all = notices_all(true);

/* Optional category filter, e.g. notices.php?category=Examination */
$filter = (string) ($_GET['category'] ?? '');
if ($filter !== '' && in_array($filter, NOTICE_CATEGORIES, true)) {
    $all = array_values(array_filter($all, static fn (array $n): bool => $n['category'] === $filter));
} else {
    $filter = '';
}

/* Only offer filter chips for categories that actually have notices. */
$used_categories = [];
foreach (notices_all(true) as $n) {
    $used_categories[$n['category']] = ($used_categories[$n['category']] ?? 0) + 1;
}

$is_home = false;
$page_title = 'Notices — ' . $c['identity']['school_name'];
$page_description = 'School notices, holiday announcements, exam routines and admission dates from '
    . $c['identity']['school_name'] . ', Sirutar, Bhaktapur.';

require __DIR__ . '/includes/public-header.php';
?>

    <section class="page-head">
      <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
          <a href="<?= e(url('index.php')) ?>">Home</a>
          <span aria-hidden="true">/</span>
          <span aria-current="page">Notices</span>
        </nav>
        <h1>Notice board</h1>
        <p class="page-lede">Holidays, exam routines, admission dates and school announcements — newest first.</p>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <?php if ($used_categories !== []): ?>
          <div class="filter-chips reveal">
            <a class="chip<?= $filter === '' ? ' is-active' : '' ?>" href="<?= e(url('notices.php')) ?>">
              All <span>(<?= count(notices_all(true)) ?>)</span>
            </a>
            <?php foreach (NOTICE_CATEGORIES as $cat): ?>
              <?php if (!isset($used_categories[$cat])) { continue; } ?>
              <a class="chip<?= $filter === $cat ? ' is-active' : '' ?>"
                 href="<?= e(url('notices.php?category=' . rawurlencode($cat))) ?>">
                <?= e($cat) ?> <span>(<?= (int) $used_categories[$cat] ?>)</span>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if ($all === []): ?>
          <div class="empty-state reveal">
            <h2>Nothing here yet</h2>
            <p><?= $filter !== ''
                ? 'There are no notices in this category right now.'
                : 'No notices have been published yet. Announcements will appear here as the school posts them.' ?></p>
            <p><a class="btn btn-outline" href="<?= e(url('index.php')) ?>">Back to the homepage</a></p>
          </div>
        <?php else: ?>
          <div class="notice-list wide">
            <?php foreach ($all as $n): ?>
              <article class="notice-card reveal<?= $n['pinned'] ? ' is-pinned' : '' ?>">
                <div class="notice-meta">
                  <time datetime="<?= e($n['date']) ?>"><?= e(format_date($n['date'])) ?></time>
                  <span class="notice-tag"><?= e($n['category']) ?></span>
                  <?php if ($n['pinned']): ?><span class="notice-tag pinned">Pinned</span><?php endif; ?>
                  <?php if (notice_is_recent($n)): ?><span class="notice-tag new">New</span><?php endif; ?>
                </div>
                <h2><a href="<?= e(notice_url($n)) ?>"><?= e($n['title']) ?></a></h2>
                <?php if (trim($n['body']) !== ''): ?>
                  <p class="notice-excerpt"><?= e(excerpt($n['body'], 260)) ?></p>
                <?php endif; ?>
                <p class="notice-more">
                  <a href="<?= e(notice_url($n)) ?>">Read notice <span aria-hidden="true">→</span></a>
                  <?php if ($n['attachment'] !== ''): ?>
                    <a class="notice-file" href="<?= e(url('files/' . $n['attachment'])) ?>" target="_blank" rel="noopener">
                      <span aria-hidden="true">📎</span> Attachment
                    </a>
                  <?php endif; ?>
                </p>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>

<?php require __DIR__ . '/includes/public-footer.php'; ?>
