<?php
/**
 * admin/content.php — school identity and the four homepage statistics.
 */

declare(strict_types=1);

require_once __DIR__ . '/layout.php';

require_admin();

$c = content();
$identity = $c['identity'];
$stats = is_array($c['stats']) ? $c['stats'] : [];
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_csrf();

    $text = static fn (string $k, int $max = 200): string => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);

    $identity = [
        'school_name'     => $text('school_name', 120),
        'short_name'      => $text('short_name', 60),
        'tagline'         => $text('tagline', 160),
        'established'     => $text('established', 40),
        'principal_name'  => $text('principal_name', 120),
        'principal_title' => $text('principal_title', 60) ?: 'Principal',
    ];

    if ($identity['school_name'] === '') {
        $errors['school_name'] = 'The school name cannot be blank — it is used in the page title and headings.';
    }
    if ($identity['short_name'] === '') {
        $identity['short_name'] = $identity['school_name'];
    }

    /* Rebuild the stats list from the submitted rows, dropping empty ones. */
    $rows = $_POST['stat'] ?? [];
    $stats = [];
    if (is_array($rows)) {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $value = mb_substr(trim((string) ($row['value'] ?? '')), 0, 40);
            $label = mb_substr(trim((string) ($row['label'] ?? '')), 0, 80);
            $count = preg_replace('/\D+/', '', (string) ($row['count'] ?? '')) ?? '';

            if ($value === '' && $label === '') {
                continue;
            }
            $stats[] = ['value' => $value, 'label' => $label, 'count' => $count];
        }
    }
    if (count($stats) > 8) {
        $stats = array_slice($stats, 0, 8);
    }

    if ($errors === []) {
        $ok = content_save_section('identity', $identity) && content_save_section('stats', $stats);

        if ($ok) {
            flash('School details saved.', 'success');
            redirect(url('admin/content.php'));
        }
        $errors['save'] = 'Could not save. Check that the data/ folder is writable (permissions 755).';
    }
}

$v = static fn (string $k): string => e((string) ($identity[$k] ?? ''));

admin_head('School details', 'content.php');
?>

<?php if (isset($errors['save'])): ?>
  <div class="alert alert-error" role="alert"><?= e($errors['save']) ?></div>
<?php endif; ?>

<form method="POST" action="<?= e(url('admin/content.php')) ?>" data-warn-unsaved>
  <?= csrf_field() ?>

  <section class="panel">
    <div class="panel-head"><h2>Name &amp; tagline</h2></div>

    <div class="field<?= isset($errors['school_name']) ? ' has-error' : '' ?>">
      <label for="school_name">Full school name</label>
      <input type="text" id="school_name" name="school_name" required value="<?= $v('school_name') ?>" />
      <?php if (isset($errors['school_name'])): ?><p class="error"><?= e($errors['school_name']) ?></p><?php endif; ?>
    </div>

    <div class="field-row">
      <div class="field">
        <label for="short_name">Short name</label>
        <input type="text" id="short_name" name="short_name" value="<?= $v('short_name') ?>" />
        <p class="hint">Shown beside the logo in the header and footer.</p>
      </div>
      <div class="field">
        <label for="established">Established year</label>
        <input type="text" id="established" name="established" value="<?= $v('established') ?>"
               placeholder="e.g. 2063 BS (2006 AD)" />
        <p class="hint">Appears above "Our story". Leave blank to hide that line.</p>
      </div>
    </div>

    <div class="field">
      <label for="tagline">Tagline</label>
      <input type="text" id="tagline" name="tagline" value="<?= $v('tagline') ?>" maxlength="160" />
      <p class="hint">The line under the school name in the hero. Suggestions if you want to change it:
        <em>Character first. Career always.</em> · <em>Where values shape careers.</em></p>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Principal</h2></div>
    <p class="panel-lede">Signs off the welcome message on the homepage. Leave the name blank to hide the
      signature entirely.</p>

    <div class="field-row">
      <div class="field">
        <label for="principal_name">Principal's name</label>
        <input type="text" id="principal_name" name="principal_name" value="<?= $v('principal_name') ?>" />
      </div>
      <div class="field">
        <label for="principal_title">Title</label>
        <input type="text" id="principal_title" name="principal_title" value="<?= $v('principal_title') ?>" />
      </div>
    </div>
  </section>

  <section class="panel" data-repeat>
    <div class="panel-head">
      <h2>Homepage statistics</h2>
      <button class="btn btn-sm btn-outline" type="button" data-repeat-add>+ Add a statistic</button>
    </div>
    <p class="panel-lede">The green bar under the hero. Four reads best on desktop. Put a number in the last
      column to make it count up as visitors scroll to it.</p>

    <div class="repeat-list" data-repeat-list>
      <?php foreach ($stats as $i => $stat): ?>
        <div class="repeat-row" data-repeat-row>
          <div class="field">
            <label>Value
              <input type="text" name="stat[<?= (int) $i ?>][value]" value="<?= e((string) ($stat['value'] ?? '')) ?>"
                     placeholder="Nursery–10" />
            </label>
          </div>
          <div class="field grow">
            <label>Label
              <input type="text" name="stat[<?= (int) $i ?>][label]" value="<?= e((string) ($stat['label'] ?? '')) ?>"
                     placeholder="Grade levels offered" />
            </label>
          </div>
          <div class="field narrow">
            <label>Count up to
              <input type="text" name="stat[<?= (int) $i ?>][count]" value="<?= e((string) ($stat['count'] ?? '')) ?>"
                     inputmode="numeric" placeholder="554" />
            </label>
          </div>
          <button class="btn btn-sm btn-danger" type="button" data-repeat-remove title="Remove this statistic">Remove</button>
        </div>
      <?php endforeach; ?>
    </div>

    <?php /* Template for rows added in the browser; __i__ becomes a unique index in JS. */ ?>
    <template>
      <div class="repeat-row" data-repeat-row>
        <div class="field">
          <label>Value <input type="text" name="stat[__i__][value]" placeholder="Co-ed" /></label>
        </div>
        <div class="field grow">
          <label>Label <input type="text" name="stat[__i__][label]" placeholder="Girls and boys together" /></label>
        </div>
        <div class="field narrow">
          <label>Count up to <input type="text" name="stat[__i__][count]" inputmode="numeric" /></label>
        </div>
        <button class="btn btn-sm btn-danger" type="button" data-repeat-remove>Remove</button>
      </div>
    </template>
  </section>

  <div class="form-actions sticky">
    <button class="btn btn-primary" type="submit">Save school details</button>
    <a class="btn btn-ghost" href="<?= e(url('index.php')) ?>" target="_blank" rel="noopener">View on the site ↗</a>
  </div>
</form>

<?php admin_foot(); ?>
