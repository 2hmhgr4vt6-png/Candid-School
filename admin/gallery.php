<?php
/**
 * admin/gallery.php — upload, caption, reorder and delete gallery photos.
 *
 * Files land in images/gallery/ with a generated name; the caption, alt text
 * and display order live in data/content.json under "gallery".
 */

declare(strict_types=1);

require_once __DIR__ . '/layout.php';
require_once dirname(__DIR__) . '/includes/uploads.php';

require_admin();

$c = content();
$gallery = is_array($c['gallery']) ? $c['gallery'] : [];
$upload_errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_csrf();

    $action = (string) ($_POST['action'] ?? '');

    /* ---- upload one or more photos ---- */
    if ($action === 'upload') {
        $files = $_FILES['photos'] ?? null;
        $added = 0;

        if (is_array($files) && is_array($files['name'] ?? null)) {
            $count = count($files['name']);

            for ($i = 0; $i < $count; $i++) {
                if ((int) $files['error'][$i] === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                $one = [
                    'name'     => $files['name'][$i],
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i],
                ];

                $result = gallery_upload($one);

                if ($result['ok']) {
                    $gallery[] = ['file' => $result['file'], 'caption' => '', 'alt' => ''];
                    $added++;
                } else {
                    $upload_errors[] = basename((string) $files['name'][$i]) . ': ' . $result['error'];
                }
            }
        }

        if ($added > 0) {
            if (content_save_section('gallery', $gallery)) {
                flash($added === 1 ? 'Photo uploaded. Add a caption below and save.' : $added . ' photos uploaded. Add captions below and save.', 'success');
            } else {
                flash('The files uploaded but the gallery list could not be saved. Check that data/ is writable.', 'error');
            }
        } elseif ($upload_errors === []) {
            flash('No files were selected.', 'info');
        }

        foreach ($upload_errors as $message) {
            flash($message, 'error');
        }
        redirect(url('admin/gallery.php'));
    }

    /* ---- delete one photo ---- */
    if ($action === 'delete') {
        $file = basename((string) ($_POST['file'] ?? ''));
        $before = count($gallery);

        $gallery = array_values(array_filter(
            $gallery,
            static fn (array $p): bool => basename((string) ($p['file'] ?? '')) !== $file
        ));

        if (count($gallery) < $before) {
            upload_delete(GALLERY_DIR, $file);
            content_save_section('gallery', $gallery);
            flash('Photo removed.', 'success');
        } else {
            flash('That photo is no longer in the gallery.', 'error');
        }
        redirect(url('admin/gallery.php'));
    }

    /* ---- save captions and order ---- */
    if ($action === 'save') {
        $rows = $_POST['photo'] ?? [];
        $updated = [];

        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $file = basename(trim((string) ($row['file'] ?? '')));

                // Only keep rows whose file genuinely exists on disk.
                if ($file === '' || !is_file(GALLERY_DIR . '/' . $file)) {
                    continue;
                }

                $updated[] = [
                    'file'    => $file,
                    'caption' => mb_substr(trim((string) ($row['caption'] ?? '')), 0, 200),
                    'alt'     => mb_substr(trim((string) ($row['alt'] ?? '')), 0, 200),
                    'order'   => (int) ($row['order'] ?? 0),
                ];
            }
        }

        // Sort by the order boxes, then drop the helper key.
        usort($updated, static fn (array $a, array $b): int => $a['order'] <=> $b['order']);
        $updated = array_map(
            static fn (array $p): array => ['file' => $p['file'], 'caption' => $p['caption'], 'alt' => $p['alt']],
            $updated
        );

        if (content_save_section('gallery', $updated)) {
            flash('Gallery saved.', 'success');
        } else {
            flash('Could not save the gallery. Check that data/ is writable.', 'error');
        }
        redirect(url('admin/gallery.php'));
    }
}

/* Photos sitting in images/gallery/ that the gallery list does not know about
   (e.g. uploaded over FTP) — offer to adopt them rather than ignoring them. */
$known = array_map(static fn (array $p): string => basename((string) ($p['file'] ?? '')), $gallery);
$orphans = [];
if (is_dir(GALLERY_DIR)) {
    foreach (scandir(GALLERY_DIR) ?: [] as $entry) {
        if (str_starts_with($entry, '.') || !is_file(GALLERY_DIR . '/' . $entry)) {
            continue;
        }
        if (!preg_match('/\.(jpe?g|png|webp|gif)$/i', $entry)) {
            continue;
        }
        if (!in_array($entry, $known, true)) {
            $orphans[] = $entry;
        }
    }
}

admin_head('Gallery', 'gallery.php');
?>

<p class="admin-lede">Photos appear in the Gallery section of the homepage, in the order below. Compress large
  photos first (<a href="https://squoosh.app" target="_blank" rel="noopener">squoosh.app</a> is free) — aim for
  under 300&nbsp;KB each so the page stays fast on mobile data.</p>

<?php if (!is_dir(GALLERY_DIR) || !is_writable(GALLERY_DIR)): ?>
  <div class="alert alert-error" role="alert">
    <strong>images/gallery/ is not writable</strong>, so uploads will fail. Set that folder's permissions to
    <code>755</code> in your hosting file manager.
  </div>
<?php endif; ?>

<section class="panel">
  <div class="panel-head"><h2>Upload photos</h2></div>

  <form method="POST" action="<?= e(url('admin/gallery.php')) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="upload" />

    <div class="field">
      <label for="photos">Choose photos</label>
      <input type="file" id="photos" name="photos[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple required />
      <p class="hint">JPG, PNG, WebP or GIF · up to 6 MB each · you can select several at once.</p>
    </div>

    <button class="btn btn-primary" type="submit">Upload</button>
  </form>
</section>

<?php if ($orphans !== []): ?>
  <section class="panel">
    <div class="panel-head"><h2>Found on the server</h2></div>
    <p class="panel-lede">These image files are in <code>images/gallery/</code> but are not in the gallery list —
      probably uploaded by FTP. Tick any you want to show on the website, then save.</p>

    <form method="POST" action="<?= e(url('admin/gallery.php')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save" />

      <?php foreach ($gallery as $i => $photo): ?>
        <input type="hidden" name="photo[<?= (int) $i ?>][file]" value="<?= e((string) $photo['file']) ?>" />
        <input type="hidden" name="photo[<?= (int) $i ?>][caption]" value="<?= e((string) ($photo['caption'] ?? '')) ?>" />
        <input type="hidden" name="photo[<?= (int) $i ?>][alt]" value="<?= e((string) ($photo['alt'] ?? '')) ?>" />
        <input type="hidden" name="photo[<?= (int) $i ?>][order]" value="<?= (int) $i ?>" />
      <?php endforeach; ?>

      <div class="orphan-grid">
        <?php foreach ($orphans as $j => $file): ?>
          <label class="orphan">
            <img src="<?= e(url('images/gallery/' . $file)) ?>" alt="" loading="lazy" />
            <span>
              <input type="checkbox" name="photo[<?= 500 + $j ?>][file]" value="<?= e($file) ?>" />
              <?= e($file) ?>
            </span>
            <input type="hidden" name="photo[<?= 500 + $j ?>][order]" value="<?= 500 + $j ?>" />
          </label>
        <?php endforeach; ?>
      </div>

      <div class="form-actions">
        <button class="btn btn-primary" type="submit">Add the ticked photos</button>
      </div>
    </form>
  </section>
<?php endif; ?>

<section class="panel">
  <div class="panel-head">
    <h2>Gallery <span class="count"><?= count($gallery) ?></span></h2>
    <?php if ($gallery !== []): ?>
      <a class="btn btn-sm btn-ghost" href="<?= e(url('index.php#gallery')) ?>" target="_blank" rel="noopener">View on the site ↗</a>
    <?php endif; ?>
  </div>

  <?php if ($gallery === []): ?>
    <p class="panel-empty">No photos yet. Until you add some, the website shows numbered grey placeholders in
      the gallery grid.</p>
  <?php else: ?>
    <form method="POST" action="<?= e(url('admin/gallery.php')) ?>" data-warn-unsaved>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save" />

      <div class="photo-rows">
        <?php foreach ($gallery as $i => $photo): ?>
          <?php $file = basename((string) ($photo['file'] ?? '')); ?>
          <?php $exists = $file !== '' && is_file(GALLERY_DIR . '/' . $file); ?>
          <div class="photo-row">
            <div class="photo-thumb">
              <?php if ($exists): ?>
                <img src="<?= e(url('images/gallery/' . $file)) ?>" alt="" loading="lazy" />
              <?php else: ?>
                <span class="thumb-missing">File missing</span>
              <?php endif; ?>
            </div>

            <div class="photo-fields">
              <input type="hidden" name="photo[<?= (int) $i ?>][file]" value="<?= e($file) ?>" />

              <div class="field-row">
                <div class="field narrow">
                  <label>Order
                    <input type="number" name="photo[<?= (int) $i ?>][order]" value="<?= (int) $i ?>" min="0" step="1" />
                  </label>
                </div>
                <div class="field grow">
                  <label>Caption <span class="optional">shown under the photo</span>
                    <input type="text" name="photo[<?= (int) $i ?>][caption]"
                           value="<?= e((string) ($photo['caption'] ?? '')) ?>"
                           placeholder="Science exhibition, Grade 5" />
                  </label>
                </div>
              </div>

              <div class="field">
                <label>Description for screen readers
                  <input type="text" name="photo[<?= (int) $i ?>][alt]"
                         value="<?= e((string) ($photo['alt'] ?? '')) ?>"
                         placeholder="Grade 5 students presenting a science project" />
                </label>
                <p class="hint">Describe what is happening in the photo. Used by visitors who cannot see it,
                  and by Google. Falls back to the caption if left blank.</p>
              </div>

              <p class="muted-line"><code><?= e($file) ?></code></p>
            </div>

          </div>
        <?php endforeach; ?>
      </div>

      <div class="form-actions sticky">
        <button class="btn btn-primary" type="submit">Save captions and order</button>
      </div>
    </form>

    <?php /* Deletes are separate one-per-photo forms so they cannot be confused
             with a caption save, and each asks for confirmation. */ ?>
    <div class="delete-strip">
      <h3>Remove a photo</h3>
      <div class="delete-buttons">
        <?php foreach ($gallery as $photo): ?>
          <?php $file = basename((string) ($photo['file'] ?? '')); ?>
          <form method="POST" action="<?= e(url('admin/gallery.php')) ?>"
                data-confirm="Delete this photo from the server? This cannot be undone.">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete" />
            <input type="hidden" name="file" value="<?= e($file) ?>" />
            <button class="btn btn-sm btn-danger" type="submit">
              Delete <?= e(excerpt((string) ($photo['caption'] ?? '') ?: $file, 28)) ?>
            </button>
          </form>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</section>

<?php admin_foot(); ?>
