<?php
/**
 * admin/contact.php — phone, email, address, hours, map position, Facebook.
 *
 * These values appear in the Contact section, the admissions callout, the CTA
 * strip and the footer, so editing them here updates the whole site at once.
 */

declare(strict_types=1);

require_once __DIR__ . '/layout.php';

require_admin();

$c = content();
$values = $c['contact'];
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_csrf();

    $text = static fn (string $k, int $max = 200): string => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);

    $values = [
        'phone'        => $text('phone', 60),
        'phone_alt'    => $text('phone_alt', 60),
        'email'        => $text('email', 120),
        'street'       => $text('street', 160),
        'locality'     => $text('locality', 160),
        'district'     => $text('district', 160),
        'country'      => $text('country', 80),
        'office_hours' => $text('office_hours', 160),
        'school_hours' => $text('school_hours', 160),
        'facebook_url' => $text('facebook_url', 300),
        'map_lat'      => $text('map_lat', 32),
        'map_lng'      => $text('map_lng', 32),
        'map_zoom'     => $text('map_zoom', 4),
        'show_fb_feed' => !empty($_POST['show_fb_feed']),
    ];

    if ($values['email'] !== '' && !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'That does not look like a valid email address.';
    }

    if ($values['facebook_url'] !== '' && !filter_var($values['facebook_url'], FILTER_VALIDATE_URL)) {
        $errors['facebook_url'] = 'Please paste the full address, starting with https://';
    }

    if ($values['map_lat'] !== '' && (!is_numeric($values['map_lat']) || abs((float) $values['map_lat']) > 90)) {
        $errors['map_lat'] = 'Latitude must be a number between -90 and 90.';
    }
    if ($values['map_lng'] !== '' && (!is_numeric($values['map_lng']) || abs((float) $values['map_lng']) > 180)) {
        $errors['map_lng'] = 'Longitude must be a number between -180 and 180.';
    }

    $zoom = (int) $values['map_zoom'];
    $values['map_zoom'] = (string) ($zoom >= 1 && $zoom <= 21 ? $zoom : 17);

    if ($errors === []) {
        if (content_save_section('contact', $values)) {
            flash('Contact details saved — they are live on the website now.', 'success');
            redirect(url('admin/contact.php'));
        }
        $errors['save'] = 'Could not save. Check that the data/ folder is writable (permissions 755).';
    }
}

$v = static fn (string $k): string => e((string) ($values[$k] ?? ''));

admin_head('Contact details', 'contact.php');
?>

<?php if (isset($errors['save'])): ?>
  <div class="alert alert-error" role="alert"><?= e($errors['save']) ?></div>
<?php endif; ?>

<p class="admin-lede">Used in the Contact section, the admissions box, the "Call the school" button and the
  footer. Leave a field blank and the website quietly shows "To be updated" instead of a broken link.</p>

<form method="POST" action="<?= e(url('admin/contact.php')) ?>" data-warn-unsaved>
  <?= csrf_field() ?>

  <section class="panel">
    <div class="panel-head"><h2>Phone &amp; email</h2></div>

    <div class="field-row">
      <div class="field">
        <label for="phone">Main phone number</label>
        <input type="text" id="phone" name="phone" value="<?= $v('phone') ?>" placeholder="01-XXXXXXX" />
        <p class="hint">Write it as you want it displayed. The tap-to-call link is generated automatically.</p>
      </div>
      <div class="field">
        <label for="phone_alt">Second number <span class="optional">optional</span></label>
        <input type="text" id="phone_alt" name="phone_alt" value="<?= $v('phone_alt') ?>" placeholder="98XXXXXXXX" />
      </div>
    </div>

    <div class="field<?= isset($errors['email']) ? ' has-error' : '' ?>">
      <label for="email">Email address</label>
      <input type="email" id="email" name="email" value="<?= $v('email') ?>" placeholder="info@example.com" />
      <p class="hint">Also where the admissions enquiry form sends messages, unless you set a form endpoint in
        Settings.</p>
      <?php if (isset($errors['email'])): ?><p class="error"><?= e($errors['email']) ?></p><?php endif; ?>
    </div>

    <div class="field-row">
      <div class="field">
        <label for="office_hours">Admissions office hours</label>
        <input type="text" id="office_hours" name="office_hours" value="<?= $v('office_hours') ?>"
               placeholder="Sunday–Friday, 10:00 AM – 4:00 PM" />
      </div>
      <div class="field">
        <label for="school_hours">School hours</label>
        <input type="text" id="school_hours" name="school_hours" value="<?= $v('school_hours') ?>"
               placeholder="Sunday–Friday, 10:00 AM – 4:00 PM. Saturday closed." />
      </div>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Address</h2></div>

    <div class="field">
      <label for="street">Street or landmark</label>
      <input type="text" id="street" name="street" value="<?= $v('street') ?>"
             placeholder="Near Kaushaltar–Biruwa Road" />
      <p class="hint">The first line of the address — a building name, road or recognisable landmark.</p>
    </div>

    <div class="field-row">
      <div class="field">
        <label for="locality">Locality &amp; ward</label>
        <input type="text" id="locality" name="locality" value="<?= $v('locality') ?>" />
      </div>
      <div class="field">
        <label for="district">District &amp; province</label>
        <input type="text" id="district" name="district" value="<?= $v('district') ?>" />
      </div>
    </div>

    <div class="field third">
      <label for="country">Country</label>
      <input type="text" id="country" name="country" value="<?= $v('country') ?>" />
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Map position</h2></div>
    <p class="panel-lede">To move the pin: open Google Maps, right-click the school and click the
      latitude/longitude that appears — it copies both numbers. Paste them here.</p>

    <div class="field-row three">
      <div class="field<?= isset($errors['map_lat']) ? ' has-error' : '' ?>">
        <label for="map_lat">Latitude</label>
        <input type="text" id="map_lat" name="map_lat" value="<?= $v('map_lat') ?>" inputmode="decimal" />
        <?php if (isset($errors['map_lat'])): ?><p class="error"><?= e($errors['map_lat']) ?></p><?php endif; ?>
      </div>
      <div class="field<?= isset($errors['map_lng']) ? ' has-error' : '' ?>">
        <label for="map_lng">Longitude</label>
        <input type="text" id="map_lng" name="map_lng" value="<?= $v('map_lng') ?>" inputmode="decimal" />
        <?php if (isset($errors['map_lng'])): ?><p class="error"><?= e($errors['map_lng']) ?></p><?php endif; ?>
      </div>
      <div class="field">
        <label for="map_zoom">Zoom (1–21)</label>
        <input type="number" id="map_zoom" name="map_zoom" min="1" max="21" value="<?= $v('map_zoom') ?>" />
        <p class="hint">17 shows the neighbourhood.</p>
      </div>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Facebook</h2></div>

    <div class="field<?= isset($errors['facebook_url']) ? ' has-error' : '' ?>">
      <label for="facebook_url">Facebook page address</label>
      <input type="url" id="facebook_url" name="facebook_url" value="<?= $v('facebook_url') ?>"
             placeholder="https://www.facebook.com/yourpage" />
      <p class="hint">Clear this field to remove the Facebook icon from the site entirely.</p>
      <?php if (isset($errors['facebook_url'])): ?><p class="error"><?= e($errors['facebook_url']) ?></p><?php endif; ?>
    </div>

    <label class="inline-check">
      <input type="checkbox" name="show_fb_feed" value="1" <?= !empty($values['show_fb_feed']) ? 'checked' : '' ?> />
      <span><strong>Show the Facebook feed on the homepage</strong>
        <small>Embeds your latest posts. Turn it off if you would rather rely on the notice board alone.</small></span>
    </label>
  </section>

  <div class="form-actions sticky">
    <button class="btn btn-primary" type="submit">Save contact details</button>
    <a class="btn btn-ghost" href="<?= e(url('index.php#contact')) ?>" target="_blank" rel="noopener">View on the site ↗</a>
  </div>
</form>

<?php admin_foot(); ?>
