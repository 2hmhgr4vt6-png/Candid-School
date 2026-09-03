<?php
/**
 * public-footer.php — CTA strip, footer and scripts. Closes </main> from
 * public-header.php.
 *
 * Set $show_cta = false before including to hide the "Ready to visit" strip.
 */

declare(strict_types=1);

require_once __DIR__ . '/content.php';

$c = content();
$identity = $c['identity'];
$contact  = $c['contact'];

$show_cta = $show_cta ?? true;
$is_home  = $is_home ?? false;
$fb_url   = (string) ($contact['facebook_url'] ?? '');
$phone    = (string) ($contact['phone'] ?? '');
$email    = (string) ($contact['email'] ?? '');
?>
  <?php if ($show_cta): ?>
    <section class="cta-strip" aria-labelledby="cta-title">
      <div class="container cta-inner reveal">
        <div>
          <h2 id="cta-title">Ready to visit our campus?</h2>
          <p>Admissions for Nursery to Grade 10 are open. Talk to our office and we will arrange a tour.</p>
        </div>
        <div class="cta-actions">
          <a class="btn btn-accent" href="<?= e(anchor('admissions')) ?>">Enquire now</a>
          <?php if ($phone !== ''): ?>
            <a class="btn btn-ghost-light" href="<?= e(tel_href($phone)) ?>">Call the school</a>
          <?php endif; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>
  </main>

  <footer class="site-footer">
    <div class="container footer-grid">
      <div class="footer-brand">
        <a class="brand light" href="<?= e($is_home ? '#home' : url('index.php')) ?>">
          <span class="brand-mark" aria-hidden="true">
            <svg viewBox="0 0 48 48" role="presentation" focusable="false">
              <path d="M24 6 4 15l20 9 20-9-20-9Z" fill="currentColor" />
              <path d="M12 22v9c0 3.5 5.4 6 12 6s12-2.5 12-6v-9l-12 5.4L12 22Z" fill="currentColor" opacity=".55" />
            </svg>
          </span>
          <span class="brand-text">
            <strong><?= e($identity['short_name'] ?: 'Candid Career') ?></strong>
            <small>Secondary School</small>
          </span>
        </a>
        <p>Nursery to Grade 10 · Co-educational day school · <?= e($contact['locality'] ?: 'Sirutar, Suryabinayak–1') ?>,
          <?= e($contact['district'] ?: 'Bhaktapur') ?>. Following the national curriculum of the Government of Nepal.</p>
        <?php if ($fb_url !== ''): ?>
        <div class="social-row">
          <a class="social-icon light" href="<?= e($fb_url) ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook page">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M13.5 22v-8h2.8l.42-3.25H13.5V8.67c0-.94.26-1.58 1.6-1.58h1.8V4.18A24 24 0 0 0 14.35 4C11.75 4 10 5.58 10 8.36v2.39H7.2V14H10v8h3.5Z"/></svg>
          </a>
        </div>
        <?php endif; ?>
      </div>

      <nav class="footer-nav" aria-label="Footer">
        <h3>Explore</h3>
        <ul>
          <li><a href="<?= e(anchor('about')) ?>">About Us</a></li>
          <li><a href="<?= e(anchor('academics')) ?>">Academics</a></li>
          <li><a href="<?= e(url('notices.php')) ?>">Notices</a></li>
          <li><a href="<?= e(anchor('parent-app')) ?>">Parent App</a></li>
          <li><a href="<?= e(anchor('gallery')) ?>">Gallery</a></li>
          <li><a href="<?= e(anchor('admissions')) ?>">Admissions</a></li>
          <li><a href="<?= e(anchor('contact')) ?>">Contact</a></li>
        </ul>
      </nav>

      <div class="footer-contact">
        <h3>Get in touch</h3>
        <address>
          <?php if (trim((string) $contact['street']) !== ''): ?>
            <?= e($contact['street']) ?><br />
          <?php endif; ?>
          <?= e($contact['locality']) ?><br />
          <?= e($contact['district']) ?><?= trim((string) $contact['country']) !== '' ? '<br />' . e($contact['country']) : '' ?>
        </address>
        <p>
          <?php if ($phone !== ''): ?>
            <a href="<?= e(tel_href($phone)) ?>"><?= e($phone) ?></a><br />
          <?php endif; ?>
          <?php if ($email !== ''): ?>
            <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
          <?php endif; ?>
        </p>
      </div>
    </div>

    <div class="container footer-bottom">
      <p>&copy; <?= date('Y') ?> <?= e($identity['school_name']) ?>. All rights reserved.</p>
      <p>
        <a href="<?= e(url('admin/')) ?>">Staff login</a>
        <span aria-hidden="true">·</span>
        <a href="<?= e($is_home ? '#home' : url('index.php')) ?>">Back to top ↑</a>
      </p>
    </div>
  </footer>

  <?php if ($is_home && !empty($contact['show_fb_feed']) && $fb_url !== ''): ?>
  <!-- Facebook SDK — powers the .fb-page plugin in the News section. -->
  <div id="fb-root"></div>
  <script async defer crossorigin="anonymous"
          src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v20.0"></script>
  <?php endif; ?>

  <script src="<?= e(url('js/main.js')) ?>"></script>
</body>
</html>
