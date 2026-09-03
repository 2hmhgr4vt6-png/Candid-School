<?php
/**
 * admin/notices.php — add, edit, pin, unpublish and delete notices.
 *
 * One screen: the add/edit form on top, the full list underneath. All writes go
 * through POST + CSRF, then redirect, so a refresh never re-submits.
 */

declare(strict_types=1);

require_once __DIR__ . '/layout.php';
require_once dirname(__DIR__) . '/includes/notices.php';
require_once dirname(__DIR__) . '/includes/uploads.php';

require_admin();

$errors = [];
/* Field values shown in the form — repopulated after a failed save. */
$form = [
    'id'         => 0,
    'title'      => '',
    'date'       => date('Y-m-d'),
    'category'   => 'General',
    'body'       => '',
    'pinned'     => false,
    'published'  => true,
    'attachment' => '',
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_csrf();

    $action = (string) ($_POST['action'] ?? '');

    /* ---- delete ---- */
    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $notice = notice_find($id, false);

        if ($notice && notice_delete($id)) {
            // Remove the orphaned attachment along with the notice.
            if ($notice['attachment'] !== '') {
                upload_delete(files_dir(), $notice['attachment']);
            }
            flash('Notice deleted.', 'success');
        } else {
            flash('That notice could not be deleted.', 'error');
        }
        redirect(url('admin/notices.php'));
    }

    /* ---- pin / publish toggles ---- */
    if ($action === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        $field = (string) ($_POST['field'] ?? '');

        if (notice_toggle($id, $field)) {
            $notice = notice_find($id, false);
            $state = $notice && !empty($notice[$field]);
            flash(match ($field) {
                'published' => $state ? 'Notice published — it is now on the website.' : 'Notice unpublished — it is hidden from visitors.',
                'pinned'    => $state ? 'Notice pinned to the top.' : 'Notice unpinned.',
                default     => 'Notice updated.',
            }, 'success');
        } else {
            flash('That notice could not be updated.', 'error');
        }
        redirect(url('admin/notices.php'));
    }

    /* ---- create / update ---- */
    if ($action === 'save') {
        $form['id']        = (int) ($_POST['id'] ?? 0);
        $form['title']     = trim((string) ($_POST['title'] ?? ''));
        $form['date']      = trim((string) ($_POST['date'] ?? ''));
        $form['category']  = (string) ($_POST['category'] ?? 'General');
        $form['body']      = trim((string) ($_POST['body'] ?? ''));
        $form['pinned']    = !empty($_POST['pinned']);
        $form['published'] = !empty($_POST['published']);

        $existing = $form['id'] > 0 ? notice_find($form['id'], false) : null;
        $form['attachment'] = $existing['attachment'] ?? '';

        if ($form['title'] === '') {
            $errors['title'] = 'Please give the notice a title.';
        } elseif (mb_strlen($form['title']) > 200) {
            $errors['title'] = 'Please keep the title under 200 characters.';
        }

        $d = DateTimeImmutable::createFromFormat('Y-m-d', $form['date']);
        if (!$d || $d->format('Y-m-d') !== $form['date']) {
            $errors['date'] = 'Please choose a valid date.';
        }

        if (!in_array($form['category'], NOTICE_CATEGORIES, true)) {
            $errors['category'] = 'Please choose one of the listed categories.';
        }

        if (mb_strlen($form['body']) > 20000) {
            $errors['body'] = 'This notice is very long. Please keep it under 20,000 characters.';
        }

        /* Remove the existing attachment if asked. */
        if (!empty($_POST['remove_attachment']) && $form['attachment'] !== '') {
            upload_delete(files_dir(), $form['attachment']);
            $form['attachment'] = '';
        }

        /* A new upload replaces whatever was there. */
        if (!empty($_FILES['attachment']['name'] ?? '')) {
            $result = attachment_upload($_FILES['attachment']);
            if ($result['ok']) {
                if ($form['attachment'] !== '') {
                    upload_delete(files_dir(), $form['attachment']);
                }
                $form['attachment'] = $result['file'];
            } else {
                $errors['attachment'] = $result['error'];
            }
        }

        if ($errors === []) {
            $saved = notice_save($form, $form['id']);

            if ($saved !== null) {
                flash(
                    $form['id'] > 0 ? 'Notice updated.' : ($form['published']
                        ? 'Notice published — it is live on the website now.'
                        : 'Notice saved as a draft. Publish it when you are ready.'),
                    'success'
                );
                redirect(url('admin/notices.php'));
            }

            $errors['save'] = 'The notice could not be saved. Check that the data/ folder is writable.';
        }
    }
}

/* Editing an existing notice (GET ?edit=ID), unless a failed POST is showing. */
$editing = false;
if ($errors === [] && isset($_GET['edit'])) {
    $notice = notice_find((int) $_GET['edit'], false);
    if ($notice) {
        $form = $notice;
        $editing = true;
    } else {
        flash('That notice no longer exists.', 'error');
    }
}
if ($errors !== [] && (int) $form['id'] > 0) {
    $editing = true;
}

$all = notices_all(false);

admin_head($editing ? 'Edit notice' : 'Notices', 'notices.php');
?>

<?php if (isset($errors['save'])): ?>
  <div class="alert alert-error" role="alert"><?= e($errors['save']) ?></div>
<?php endif; ?>

<section class="panel" id="new">
  <div class="panel-head">
    <h2><?= $editing ? 'Edit notice' : 'Add a notice' ?></h2>
    <?php if ($editing): ?>
      <a class="btn btn-sm btn-outline" href="<?= e(url('admin/notices.php')) ?>">Cancel — add a new one instead</a>
    <?php endif; ?>
  </div>

  <form method="POST" action="<?= e(url('admin/notices.php#new')) ?>" enctype="multipart/form-data" data-warn-unsaved>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save" />
    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>" />

    <div class="field<?= isset($errors['title']) ? ' has-error' : '' ?>">
      <label for="title">Title</label>
      <input type="text" id="title" name="title" maxlength="200" required
             value="<?= e((string) $form['title']) ?>"
             placeholder="e.g. Dashain holiday — school closed 10–20 October" />
      <?php if (isset($errors['title'])): ?><p class="error"><?= e($errors['title']) ?></p><?php endif; ?>
    </div>

    <div class="field-row three">
      <div class="field<?= isset($errors['date']) ? ' has-error' : '' ?>">
        <label for="date">Date</label>
        <input type="date" id="date" name="date" required value="<?= e((string) $form['date']) ?>" />
        <?php if (isset($errors['date'])): ?><p class="error"><?= e($errors['date']) ?></p><?php endif; ?>
      </div>

      <div class="field<?= isset($errors['category']) ? ' has-error' : '' ?>">
        <label for="category">Category</label>
        <select id="category" name="category">
          <?php foreach (NOTICE_CATEGORIES as $cat): ?>
            <option<?= $form['category'] === $cat ? ' selected' : '' ?>><?= e($cat) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['category'])): ?><p class="error"><?= e($errors['category']) ?></p><?php endif; ?>
      </div>

      <div class="field">
        <label for="attachment">Attachment <span class="optional">optional</span></label>
        <input type="file" id="attachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.webp" />
        <p class="hint">PDF or image, up to 6 MB — exam routines, fee notices, result sheets.</p>
        <?php if (isset($errors['attachment'])): ?><p class="error"><?= e($errors['attachment']) ?></p><?php endif; ?>
        <?php if (trim((string) $form['attachment']) !== ''): ?>
          <p class="current-file">
            Attached:
            <a href="<?= e(url('files/' . $form['attachment'])) ?>" target="_blank" rel="noopener"><?= e($form['attachment']) ?></a>
            <label class="inline-check">
              <input type="checkbox" name="remove_attachment" value="1" /> remove it
            </label>
          </p>
        <?php endif; ?>
      </div>
    </div>

    <div class="field<?= isset($errors['body']) ? ' has-error' : '' ?>">
      <label for="body">Notice details</label>
      <textarea id="body" name="body" rows="8"
                placeholder="Write the notice as you would post it on the board. Leave a blank line between paragraphs."><?= e((string) $form['body']) ?></textarea>
      <p class="hint">Plain text. Leave a blank line between paragraphs — they appear as separate paragraphs
        on the website.</p>
      <?php if (isset($errors['body'])): ?><p class="error"><?= e($errors['body']) ?></p><?php endif; ?>
    </div>

    <div class="check-row">
      <label class="inline-check">
        <input type="checkbox" name="published" value="1" <?= $form['published'] ? 'checked' : '' ?> />
        <span><strong>Publish on the website</strong><small>Uncheck to keep it as a draft only you can see.</small></span>
      </label>
      <label class="inline-check">
        <input type="checkbox" name="pinned" value="1" <?= $form['pinned'] ? 'checked' : '' ?> />
        <span><strong>Pin to the top</strong><small>Keeps an important notice above newer ones.</small></span>
      </label>
    </div>

    <div class="form-actions">
      <button class="btn btn-primary" type="submit"><?= $editing ? 'Save changes' : 'Save notice' ?></button>
      <a class="btn btn-ghost" href="<?= e(url('notices.php')) ?>" target="_blank" rel="noopener">Preview the notice board ↗</a>
    </div>
  </form>
</section>

<section class="panel">
  <div class="panel-head">
    <h2>All notices <span class="count"><?= count($all) ?></span></h2>
  </div>

  <?php if ($all === []): ?>
    <p class="panel-empty">No notices yet. Add your first one above.</p>
  <?php else: ?>
    <div class="table-scroll">
      <table class="admin-table">
        <thead>
          <tr>
            <th scope="col">Notice</th>
            <th scope="col">Date</th>
            <th scope="col">Status</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($all as $n): ?>
            <tr<?= $n['published'] ? '' : ' class="is-draft"' ?>>
              <td>
                <strong><?= e($n['title']) ?></strong>
                <?php if ($n['pinned']): ?><span class="pill pill-gold">Pinned</span><?php endif; ?>
                <span class="muted-line">
                  <?= e($n['category']) ?>
                  <?php if ($n['attachment'] !== ''): ?> · has an attachment<?php endif; ?>
                </span>
              </td>
              <td class="nowrap"><?= e(format_date($n['date'], 'j M Y')) ?></td>
              <td>
                <span class="pill <?= $n['published'] ? 'pill-green' : 'pill-grey' ?>">
                  <?= $n['published'] ? 'Published' : 'Draft' ?>
                </span>
              </td>
              <td>
                <div class="row-actions">
                  <a class="btn btn-sm btn-outline" href="<?= e(url('admin/notices.php?edit=' . $n['id'] . '#new')) ?>">Edit</a>

                  <form method="POST" action="<?= e(url('admin/notices.php')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle" />
                    <input type="hidden" name="id" value="<?= (int) $n['id'] ?>" />
                    <input type="hidden" name="field" value="published" />
                    <button class="btn btn-sm btn-outline" type="submit">
                      <?= $n['published'] ? 'Unpublish' : 'Publish' ?>
                    </button>
                  </form>

                  <form method="POST" action="<?= e(url('admin/notices.php')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle" />
                    <input type="hidden" name="id" value="<?= (int) $n['id'] ?>" />
                    <input type="hidden" name="field" value="pinned" />
                    <button class="btn btn-sm btn-outline" type="submit">
                      <?= $n['pinned'] ? 'Unpin' : 'Pin' ?>
                    </button>
                  </form>

                  <?php if ($n['published']): ?>
                    <a class="btn btn-sm btn-ghost" href="<?= e(notice_url($n)) ?>" target="_blank" rel="noopener">View</a>
                  <?php endif; ?>

                  <form method="POST" action="<?= e(url('admin/notices.php')) ?>"
                        data-confirm="Delete &quot;<?= e(addslashes($n['title'])) ?>&quot;? This cannot be undone.">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete" />
                    <input type="hidden" name="id" value="<?= (int) $n['id'] ?>" />
                    <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>

<?php admin_foot(); ?>
