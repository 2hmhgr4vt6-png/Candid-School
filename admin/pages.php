<?php
/**
 * admin/pages.php — the long-form text blocks on the homepage:
 * welcome message, our story, vision, mission, achievements, curriculum,
 * activities, admission steps and the facilities cards.
 *
 * All fields are plain text. They are escaped when rendered, so pasting from
 * Word cannot break the page and no HTML or script can be injected.
 */

declare(strict_types=1);

require_once __DIR__ . '/layout.php';

require_admin();

$c = content();
$pages = $c['pages'];
$facilities = is_array($c['facilities']) ? $c['facilities'] : [];
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_csrf();

    $block = static fn (string $k, int $max = 8000): string => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);

    $pages = [
        'welcome_title'     => mb_substr(trim((string) ($_POST['welcome_title'] ?? '')), 0, 160),
        'welcome_body'      => $block('welcome_body'),
        'about_body'        => $block('about_body'),
        'vision'            => $block('vision', 2000),
        'mission'           => lines_to_array($_POST['mission'] ?? ''),
        'achievements'      => lines_to_array($_POST['achievements'] ?? ''),
        'curriculum_body'   => $block('curriculum_body'),
        'optional_subjects' => mb_substr(trim((string) ($_POST['optional_subjects'] ?? '')), 0, 400),
        'activities'        => lines_to_array($_POST['activities'] ?? ''),
        'admission_steps'   => lines_to_array($_POST['admission_steps'] ?? ''),
    ];

    if ($pages['welcome_title'] === '') {
        $errors['welcome_title'] = 'Please give the welcome section a heading.';
    }

    /* Facilities cards: title + description, empty rows dropped. */
    $rows = $_POST['facility'] ?? [];
    $facilities = [];
    if (is_array($rows)) {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $title = mb_substr(trim((string) ($row['title'] ?? '')), 0, 80);
            $body = mb_substr(trim((string) ($row['body'] ?? '')), 0, 600);

            if ($title === '' && $body === '') {
                continue;
            }
            $facilities[] = ['title' => $title, 'body' => $body];
        }
    }
    $facilities = array_slice($facilities, 0, 24);

    if ($errors === []) {
        $ok = content_save_section('pages', $pages) && content_save_section('facilities', $facilities);

        if ($ok) {
            flash('Page text saved.', 'success');
            redirect(url('admin/pages.php'));
        }
        $errors['save'] = 'Could not save. Check that the data/ folder is writable (permissions 755).';
    }
}

/** Join a stored list back into one-per-line textarea content. */
$list = static fn (string $key): string => e(implode("\n", array_map('strval', is_array($pages[$key] ?? null) ? $pages[$key] : [])));

admin_head('Page text', 'pages.php');
?>

<?php if (isset($errors['save'])): ?>
  <div class="alert alert-error" role="alert"><?= e($errors['save']) ?></div>
<?php endif; ?>

<p class="admin-lede">Plain text only — no formatting codes needed. Leave a <strong>blank line</strong> between
  paragraphs in the big boxes; in the list boxes, put <strong>one item per line</strong>.</p>

<form method="POST" action="<?= e(url('admin/pages.php')) ?>" data-warn-unsaved>
  <?= csrf_field() ?>

  <section class="panel">
    <div class="panel-head"><h2>Welcome message</h2></div>
    <p class="panel-lede">The first block of text below the statistics bar.</p>

    <div class="field<?= isset($errors['welcome_title']) ? ' has-error' : '' ?>">
      <label for="welcome_title">Heading</label>
      <input type="text" id="welcome_title" name="welcome_title" required
             value="<?= e((string) $pages['welcome_title']) ?>" />
      <?php if (isset($errors['welcome_title'])): ?><p class="error"><?= e($errors['welcome_title']) ?></p><?php endif; ?>
    </div>

    <div class="field">
      <label for="welcome_body">Message</label>
      <textarea id="welcome_body" name="welcome_body" rows="9"><?= e((string) $pages['welcome_body']) ?></textarea>
      <p class="hint">Signed off with the principal's name from <a href="<?= e(url('admin/content.php')) ?>">School details</a>.</p>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Our story</h2></div>

    <div class="field">
      <label for="about_body">School history</label>
      <textarea id="about_body" name="about_body" rows="9"><?= e((string) $pages['about_body']) ?></textarea>
    </div>

    <div class="field">
      <label for="achievements">Achievements <span class="optional">one per line</span></label>
      <textarea id="achievements" name="achievements" rows="5"
                placeholder="100% SEE pass rate in 2081 BS&#10;Runners-up, municipality inter-school football 2081"><?= $list('achievements') ?></textarea>
      <p class="hint">Leave empty to hide the Achievements list from the website.</p>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Vision &amp; mission</h2></div>

    <div class="field">
      <label for="vision">Vision statement</label>
      <textarea id="vision" name="vision" rows="4"><?= e((string) $pages['vision']) ?></textarea>
    </div>

    <div class="field">
      <label for="mission">Mission points <span class="optional">one per line</span></label>
      <textarea id="mission" name="mission" rows="6"><?= $list('mission') ?></textarea>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Academics</h2></div>

    <div class="field">
      <label for="curriculum_body">Curriculum description</label>
      <textarea id="curriculum_body" name="curriculum_body" rows="8"><?= e((string) $pages['curriculum_body']) ?></textarea>
    </div>

    <div class="field">
      <label for="optional_subjects">Optional subjects in Grades 9–10</label>
      <input type="text" id="optional_subjects" name="optional_subjects"
             value="<?= e((string) $pages['optional_subjects']) ?>"
             placeholder="Computer Science, Optional Mathematics, Accountancy" />
      <p class="hint">Shown as a note under the curriculum text. Leave blank to hide it.</p>
    </div>

    <div class="field">
      <label for="activities">Co-curricular activities <span class="optional">one per line</span></label>
      <textarea id="activities" name="activities" rows="6"><?= $list('activities') ?></textarea>
      <p class="hint">Start a line with a short lead-in and a dash — e.g.
        <em>Sports competitions — inter-house athletics and games.</em></p>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Admission steps</h2></div>

    <div class="field">
      <label for="admission_steps">Steps <span class="optional">one per line, in order</span></label>
      <textarea id="admission_steps" name="admission_steps" rows="7"><?= $list('admission_steps') ?></textarea>
      <p class="hint">Each line becomes a numbered step. End the first sentence with a full stop and it is shown
        in bold as the step's heading.</p>
    </div>
  </section>

  <section class="panel" data-repeat>
    <div class="panel-head">
      <h2>Facilities</h2>
      <button class="btn btn-sm btn-outline" type="button" data-repeat-add>+ Add a facility</button>
    </div>
    <p class="panel-lede">The cards at the bottom of the About section. Remove any the school does not have —
      an accurate short list beats a long aspirational one.</p>

    <div class="repeat-list stacked" data-repeat-list>
      <?php foreach ($facilities as $i => $f): ?>
        <div class="repeat-row stacked" data-repeat-row>
          <div class="field">
            <label>Name
              <input type="text" name="facility[<?= (int) $i ?>][title]"
                     value="<?= e((string) ($f['title'] ?? '')) ?>" placeholder="Library" />
            </label>
          </div>
          <div class="field grow">
            <label>Description
              <textarea name="facility[<?= (int) $i ?>][body]" rows="2"><?= e((string) ($f['body'] ?? '')) ?></textarea>
            </label>
          </div>
          <button class="btn btn-sm btn-danger" type="button" data-repeat-remove>Remove</button>
        </div>
      <?php endforeach; ?>
    </div>

    <template>
      <div class="repeat-row stacked" data-repeat-row>
        <div class="field">
          <label>Name <input type="text" name="facility[__i__][title]" placeholder="Science lab" /></label>
        </div>
        <div class="field grow">
          <label>Description <textarea name="facility[__i__][body]" rows="2"></textarea></label>
        </div>
        <button class="btn btn-sm btn-danger" type="button" data-repeat-remove>Remove</button>
      </div>
    </template>
  </section>

  <div class="form-actions sticky">
    <button class="btn btn-primary" type="submit">Save page text</button>
    <a class="btn btn-ghost" href="<?= e(url('index.php')) ?>" target="_blank" rel="noopener">View on the site ↗</a>
  </div>
</form>

<?php admin_foot(); ?>
