<?php
/**
 * index.php — public homepage.
 *
 * Every visible string comes from data/content.json (with the defaults in
 * includes/content.php as a fallback), so the school edits this page from
 * /admin rather than by touching markup.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/content.php';
require_once __DIR__ . '/includes/notices.php';

$c          = content();
$identity   = $c['identity'];
$stats      = is_array($c['stats']) ? $c['stats'] : [];
$contact    = $c['contact'];
$pages      = $c['pages'];
$facilities = is_array($c['facilities']) ? $c['facilities'] : [];
$gallery    = is_array($c['gallery']) ? $c['gallery'] : [];
$app        = $c['app'];

$latest_notices = notices_all(true, 4);

$is_home = true;
$page_title = $identity['school_name'] . ' — Sirutar, Bhaktapur, Nepal';

require __DIR__ . '/includes/public-header.php';
?>

    <!-- ---------- HERO ---------- -->
    <section class="hero" id="home">
      <!-- To use a real photo, drop images/hero.jpg in place and uncomment the
           background-image line in .hero-media (css/style.css, section 6). -->
      <div class="hero-media" aria-hidden="true"></div>
      <div class="container hero-inner">
        <p class="eyebrow reveal"><?= e($contact['locality']) ?> · <?= e($contact['district']) ?></p>
        <h1 class="reveal"><?= e($identity['school_name']) ?></h1>
        <?php if (trim((string) $identity['tagline']) !== ''): ?>
          <p class="hero-tagline reveal"><?= e($identity['tagline']) ?></p>
        <?php endif; ?>
        <p class="hero-lede reveal">A private, co-educational day school serving families across Sirutar and greater
          Bhaktapur — from Nursery through Grade 10, following the national curriculum of the Government of Nepal.</p>
        <div class="hero-actions reveal">
          <a class="btn btn-primary" href="#admissions">Admissions</a>
          <a class="btn btn-ghost" href="#contact">Contact Us</a>
        </div>
      </div>
    </section>

    <!-- ---------- QUICK STATS ---------- -->
    <?php if ($stats !== []): ?>
    <section class="stats" aria-label="School at a glance">
      <div class="container stats-grid">
        <?php foreach ($stats as $stat): ?>
          <div class="stat reveal">
            <span class="stat-value"<?= trim((string) ($stat['count'] ?? '')) !== ''
                ? ' data-count-to="' . e((string) (int) $stat['count']) . '" data-count-suffix="+"'
                : '' ?>><?= e((string) ($stat['value'] ?? '')) ?></span>
            <span class="stat-label"><?= e((string) ($stat['label'] ?? '')) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <!-- ---------- WELCOME ---------- -->
    <section class="section welcome" aria-labelledby="welcome-title">
      <div class="container split">
        <div class="split-text reveal">
          <p class="eyebrow">Welcome</p>
          <h2 id="welcome-title"><?= e($pages['welcome_title']) ?></h2>
          <?= paragraphs($pages['welcome_body']) ?>
          <?php if (trim((string) $identity['principal_name']) !== ''): ?>
            <p class="signature">
              <strong><?= e($identity['principal_name']) ?></strong><br />
              <span><?= e($identity['principal_title']) ?>, <?= e($identity['school_name']) ?></span>
            </p>
          <?php endif; ?>
        </div>
        <div class="split-media reveal">
          <!-- Real photo: replace this block with
               <img src="images/welcome.jpg" alt="Students and teachers on campus" /> -->
          <div class="img-placeholder tall" role="img" aria-label="Placeholder: campus or classroom photograph">
            <span>Campus photo<br /><small>images/welcome.jpg</small></span>
          </div>
        </div>
      </div>
    </section>

    <!-- ---------- WHY CHOOSE US ---------- -->
    <section class="section section-tint" aria-labelledby="why-title">
      <div class="container">
        <div class="section-head reveal">
          <p class="eyebrow">Why choose us</p>
          <h2 id="why-title">Four things families notice first</h2>
          <p class="section-lede">Strong teaching, a full co-curricular calendar, clear values, and communication
            that does not stop at the school gate.</p>
        </div>

        <div class="card-grid four">
          <article class="card reveal">
            <span class="card-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" focusable="false"><path fill="currentColor" d="M12 3 2 7.5l10 4.5 10-4.5L12 3Zm-6 7.9v4.2c0 1.9 2.7 3.4 6 3.4s6-1.5 6-3.4v-4.2l-6 2.7-6-2.7ZM20 9v5.5a1 1 0 0 0 2 0V9h-2Z"/></svg>
            </span>
            <h3>Academics</h3>
            <p>The full national curriculum from Nursery to Grade 10, taught in small, attentive classes with
              regular assessment and targeted support for students who need it.</p>
          </article>
          <article class="card reveal">
            <span class="card-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" focusable="false"><path fill="currentColor" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 2.2c1.3 0 2.6.3 3.7.9l-1.9 2.6-3.6 0L8.3 5.1A8 8 0 0 1 12 4.2ZM4.3 10.6l2.5 1.9-1.1 3.5H3.1a7.9 7.9 0 0 1 1.2-5.4Zm2.1 8.1 1-.9 3.4 0 .9 2.9a7.9 7.9 0 0 1-5.3-2Zm6.9 2 .9-2.9 3.4 0 1 .9a7.9 7.9 0 0 1-5.3 2Zm5.9-4.7-1.1-3.5 2.5-1.9a7.9 7.9 0 0 1 1.2 5.4h-2.6Z"/></svg>
            </span>
            <h3>Extracurriculars</h3>
            <p>Annual cultural programmes, inter-house sports competitions, music, dance, art and speech —
              because confidence is built on a stage and a field, not only at a desk.</p>
          </article>
          <article class="card reveal">
            <span class="card-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" focusable="false"><path fill="currentColor" d="M12 2 4 5v6.5c0 5 3.4 9.2 8 10.5 4.6-1.3 8-5.5 8-10.5V5l-8-3Zm-1 13.4-3.2-3.2 1.5-1.5 1.7 1.7 4-4 1.5 1.5-5.5 5.5Z"/></svg>
            </span>
            <h3>Discipline &amp; Values</h3>
            <p>Clear, consistent expectations applied with warmth. Honesty, punctuality, respect and
              responsibility are taught daily and modelled by staff.</p>
          </article>
          <article class="card reveal">
            <span class="card-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" focusable="false"><path fill="currentColor" d="M7 1.5h10A2.5 2.5 0 0 1 19.5 4v16A2.5 2.5 0 0 1 17 22.5H7A2.5 2.5 0 0 1 4.5 20V4A2.5 2.5 0 0 1 7 1.5Zm0 2A.5.5 0 0 0 6.5 4v16c0 .3.2.5.5.5h10a.5.5 0 0 0 .5-.5V4a.5.5 0 0 0-.5-.5H7Zm3 15h4v1.2h-4v-1.2Z"/></svg>
            </span>
            <h3>Parent App &amp; Communication</h3>
            <p>Attendance, routine, assignments, progress reports, notices and live bus GPS tracking — in your
              pocket, updated by the school as the day happens.</p>
          </article>
        </div>
      </div>
    </section>

    <!-- ---------- NOTICE BOARD (managed from /admin) ---------- -->
    <section class="section" id="notices" aria-labelledby="notices-title">
      <div class="container">
        <div class="section-head reveal">
          <p class="eyebrow">Notice board</p>
          <h2 id="notices-title">Latest notices</h2>
          <p class="section-lede">Holidays, exam routines, admission dates and school announcements.</p>
        </div>

        <?php if ($latest_notices === []): ?>
          <div class="empty-state reveal">
            <p>No notices have been published yet. Announcements will appear here as the school posts them.</p>
          </div>
        <?php else: ?>
          <div class="notice-list">
            <?php foreach ($latest_notices as $n): ?>
              <article class="notice-card reveal<?= $n['pinned'] ? ' is-pinned' : '' ?>">
                <div class="notice-meta">
                  <time datetime="<?= e($n['date']) ?>"><?= e(format_date($n['date'])) ?></time>
                  <span class="notice-tag"><?= e($n['category']) ?></span>
                  <?php if ($n['pinned']): ?><span class="notice-tag pinned">Pinned</span><?php endif; ?>
                  <?php if (notice_is_recent($n)): ?><span class="notice-tag new">New</span><?php endif; ?>
                </div>
                <h3><a href="<?= e(notice_url($n)) ?>"><?= e($n['title']) ?></a></h3>
                <?php if (trim($n['body']) !== ''): ?>
                  <p class="notice-excerpt"><?= e(excerpt($n['body'], 200)) ?></p>
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
          <p class="center-note"><a class="btn btn-outline" href="<?= e(url('notices.php')) ?>">See all notices</a></p>
        <?php endif; ?>
      </div>
    </section>

    <!-- ---------- FACEBOOK FEED (toggle in admin → Contact) ---------- -->
    <?php if (!empty($contact['show_fb_feed']) && trim((string) $contact['facebook_url']) !== ''): ?>
    <section class="section section-tint" id="news" aria-labelledby="news-title">
      <div class="container">
        <div class="section-head reveal">
          <p class="eyebrow">News &amp; updates</p>
          <h2 id="news-title">Straight from our Facebook page</h2>
          <p class="section-lede">Event photos and day-to-day updates are posted first on Facebook.</p>
        </div>
        <div class="fb-wrap reveal">
          <div class="fb-page" id="fbPage"
               data-href="<?= e($contact['facebook_url']) ?>"
               data-tabs="timeline" data-width="500" data-height="640"
               data-small-header="false" data-adapt-container-width="true"
               data-hide-cover="false" data-show-facepile="true">
            <blockquote cite="<?= e($contact['facebook_url']) ?>" class="fb-xfbml-parse-ignore">
              <a href="<?= e($contact['facebook_url']) ?>" target="_blank" rel="noopener noreferrer"><?= e($identity['school_name']) ?> on Facebook</a>
            </blockquote>
          </div>
          <p class="fb-fallback">Feed not loading? Visit us directly on
            <a href="<?= e($contact['facebook_url']) ?>" target="_blank" rel="noopener noreferrer">Facebook</a>.</p>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <!-- ---------- ABOUT ---------- -->
    <section class="section<?= empty($contact['show_fb_feed']) ? ' section-tint' : '' ?>" id="about" aria-labelledby="about-title">
      <div class="container">
        <div class="section-head reveal">
          <p class="eyebrow">About us</p>
          <h2 id="about-title">Our story</h2>
        </div>

        <div class="split">
          <div class="split-text reveal">
            <?php if (trim((string) $identity['established']) !== ''): ?>
              <p class="established-line">Established <strong><?= e($identity['established']) ?></strong></p>
            <?php endif; ?>
            <?= paragraphs($pages['about_body']) ?>

            <?php $achievements = is_array($pages['achievements']) ? array_filter($pages['achievements']) : []; ?>
            <?php if ($achievements !== []): ?>
              <h3>Achievements</h3>
              <ul class="ticks">
                <?php foreach ($achievements as $item): ?>
                  <li><?= e((string) $item) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>

          <div class="split-media reveal">
            <!-- Real photo: replace with <img src="images/about.jpg" alt="The school building" /> -->
            <div class="img-placeholder tall" role="img" aria-label="Placeholder: school building photograph">
              <span>School building<br /><small>images/about.jpg</small></span>
            </div>
          </div>
        </div>

        <div class="card-grid two vm-grid">
          <article class="card accent-card reveal">
            <h3>Our Vision</h3>
            <?= paragraphs($pages['vision']) ?>
          </article>
          <article class="card accent-card reveal">
            <h3>Our Mission</h3>
            <?php $mission = is_array($pages['mission']) ? array_filter($pages['mission']) : []; ?>
            <?php if ($mission !== []): ?>
              <ul class="ticks">
                <?php foreach ($mission as $item): ?>
                  <li><?= e((string) $item) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </article>
        </div>

        <?php if ($facilities !== []): ?>
          <h3 class="subhead reveal">Facilities</h3>
          <div class="card-grid three">
            <?php foreach ($facilities as $f): ?>
              <article class="card soft reveal">
                <h4><?= e((string) ($f['title'] ?? '')) ?></h4>
                <p><?= e((string) ($f['body'] ?? '')) ?></p>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- ---------- ACADEMICS ---------- -->
    <section class="section<?= empty($contact['show_fb_feed']) ? '' : ' section-tint' ?>" id="academics" aria-labelledby="academics-title">
      <div class="container">
        <div class="section-head reveal">
          <p class="eyebrow">Academics</p>
          <h2 id="academics-title">Nursery to Grade 10, one continuous path</h2>
          <p class="section-lede">Students can join us at four years old and stay through the Secondary Education
            Examination — no change of school, no lost momentum.</p>
        </div>

        <div class="table-wrap reveal">
          <table class="grades-table">
            <caption>Grade levels offered at <?= e($identity['school_name']) ?></caption>
            <thead>
              <tr>
                <th scope="col">Level</th>
                <th scope="col">Grades</th>
                <th scope="col">Typical age</th>
                <th scope="col">Focus</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th scope="row">Early Years</th>
                <td>Nursery, LKG, UKG</td>
                <td>4–6 years</td>
                <td>Play-based literacy and numeracy, language confidence, school routines.</td>
              </tr>
              <tr>
                <th scope="row">Primary</th>
                <td>Grades 1–5</td>
                <td>6–11 years</td>
                <td>Nepali, English, Mathematics, Science, Social Studies, Health &amp; Physical Education.</td>
              </tr>
              <tr>
                <th scope="row">Lower Secondary</th>
                <td>Grades 6–8</td>
                <td>11–14 years</td>
                <td>Subject specialisation begins; computer studies and project work introduced.</td>
              </tr>
              <tr>
                <th scope="row">Secondary</th>
                <td>Grades 9–10</td>
                <td>14–16 years</td>
                <td>Full SEE preparation with compulsory and optional subjects, practicals and mock exams.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="split top-gap">
          <div class="split-text reveal">
            <h3>Curriculum</h3>
            <?= paragraphs($pages['curriculum_body']) ?>
            <?php if (trim((string) $pages['optional_subjects']) !== ''): ?>
              <p class="note"><strong>Optional subjects in Grades 9–10:</strong> <?= e($pages['optional_subjects']) ?></p>
            <?php endif; ?>
          </div>
          <div class="split-text reveal">
            <h3>Co-curricular activities</h3>
            <?php $activities = is_array($pages['activities']) ? array_filter($pages['activities']) : []; ?>
            <?php if ($activities !== []): ?>
              <ul class="ticks">
                <?php foreach ($activities as $item): ?>
                  <li><?= e((string) $item) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>

    <!-- ---------- PARENT APP ---------- -->
    <section class="section section-dark" id="parent-app" aria-labelledby="app-title">
      <div class="container split app-split">
        <div class="split-text reveal">
          <p class="eyebrow">Parent App</p>
          <h2 id="app-title">Your child's school day, on your phone</h2>
          <p class="section-lede">Our companion app keeps parents and guardians connected to the classroom
            without a phone call or a trip to the office.</p>

          <ul class="feature-list">
            <li><span aria-hidden="true">📰</span><div><strong>News &amp; events</strong>Announcements, holidays and event dates as we publish them.</div></li>
            <li><span aria-hidden="true">🗓️</span><div><strong>Timeline</strong>A running record of your child's school activity.</div></li>
            <li><span aria-hidden="true">⏰</span><div><strong>Class routine</strong>The daily and weekly timetable, always current.</div></li>
            <li><span aria-hidden="true">📝</span><div><strong>Assignments</strong>Homework set by teachers, with due dates.</div></li>
            <li><span aria-hidden="true">📊</span><div><strong>Progress reports</strong>Exam results and term-wise performance.</div></li>
            <li><span aria-hidden="true">✅</span><div><strong>Attendance</strong>Daily present/absent records, visible the same day.</div></li>
            <li><span aria-hidden="true">🚌</span><div><strong>Bus GPS tracking</strong>See where the school bus is on its route in real time.</div></li>
            <li><span aria-hidden="true">💬</span><div><strong>Complaints &amp; feedback</strong>Raise a concern directly with the school.</div></li>
            <li><span aria-hidden="true">🖊️</span><div><strong>Leave notes</strong>Inform the class teacher of an absence in advance.</div></li>
            <li><span aria-hidden="true">📚</span><div><strong>Library system</strong>Books borrowed, due dates and availability.</div></li>
            <li><span aria-hidden="true">✉️</span><div><strong>SMS notifications</strong>Urgent notices reach you even without the app open.</div></li>
          </ul>

          <div class="store-buttons">
            <?php $play = trim((string) $app['play_store_url']); $ios = trim((string) $app['app_store_url']); ?>
            <a class="store-btn<?= $play === '' ? ' is-disabled' : '' ?>"
               <?= $play !== '' ? 'href="' . e($play) . '" target="_blank" rel="noopener"' : 'aria-disabled="true"' ?>
               aria-label="Get the Parent App on Google Play<?= $play === '' ? ' (link coming soon)' : '' ?>">
              <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M3.6 2.3 13.9 12 3.6 21.7A1.7 1.7 0 0 1 3 20.4V3.6c0-.5.2-1 .6-1.3Zm11.7 10.9 2.6 2.5-9.6 5.5 7-8Zm0-2.4-7-8 9.6 5.5-2.6 2.5Zm3.9-1.3 2.5 1.4c.9.5.9 1.7 0 2.2l-2.5 1.4-3-2.5 3-2.5Z"/></svg>
              <span><small>Get it on</small><strong>Google Play</strong></span>
            </a>
            <a class="store-btn<?= $ios === '' ? ' is-disabled' : '' ?>"
               <?= $ios !== '' ? 'href="' . e($ios) . '" target="_blank" rel="noopener"' : 'aria-disabled="true"' ?>
               aria-label="Download the Parent App on the App Store<?= $ios === '' ? ' (link coming soon)' : '' ?>">
              <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M16.4 12.7c0-2.2 1.8-3.3 1.9-3.4-1-1.5-2.6-1.7-3.2-1.7-1.3-.1-2.6.8-3.3.8-.7 0-1.7-.8-2.8-.8-1.5 0-2.8.9-3.6 2.2-1.5 2.7-.4 6.6 1.1 8.8.7 1 1.6 2.2 2.7 2.2 1.1 0 1.5-.7 2.8-.7s1.6.7 2.7.7c1.2 0 2-1.1 2.7-2.2.5-.7.7-1.1 1-2-2.5-.9-2.7-3.5-2-3.9ZM14.6 5.9c.6-.7 1-1.7.9-2.7-.9.1-2 .6-2.6 1.3-.6.6-1 1.6-.9 2.6 1 .1 2-.5 2.6-1.2Z"/></svg>
              <span><small>Download on the</small><strong>App Store</strong></span>
            </a>
          </div>
          <p class="note dim">Need help signing in? Contact the school office and we will issue your parent
            credentials.</p>
        </div>

        <div class="split-media reveal">
          <!-- Real screenshot: replace the inner div with
               <img src="images/app-screen.png" alt="The parent app home screen" /> -->
          <div class="phone-frame" role="img" aria-label="Placeholder: screenshot of the parent app">
            <div class="phone-screen">
              <span>App screenshot<br /><small>images/app-screen.png</small></span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ---------- GALLERY (managed from /admin → Gallery) ---------- -->
    <section class="section" id="gallery" aria-labelledby="gallery-title">
      <div class="container">
        <div class="section-head reveal">
          <p class="eyebrow">Gallery</p>
          <h2 id="gallery-title">Life at <?= e($identity['short_name'] ?: 'Candid Career') ?></h2>
          <p class="section-lede">Classrooms, cultural programmes, sports days and the everyday moments in
            between.</p>
        </div>

        <div class="gallery-grid">
          <?php if ($gallery !== []): ?>
            <?php foreach ($gallery as $photo): ?>
              <?php $file = basename((string) ($photo['file'] ?? '')); ?>
              <?php if ($file === '' || !is_file(GALLERY_DIR . '/' . $file)) { continue; } ?>
              <figure class="gallery-item reveal">
                <img src="<?= e(url('images/gallery/' . $file)) ?>"
                     alt="<?= e((string) ($photo['alt'] ?? $photo['caption'] ?? 'School photograph')) ?>"
                     loading="lazy" />
                <?php if (trim((string) ($photo['caption'] ?? '')) !== ''): ?>
                  <figcaption><?= e((string) $photo['caption']) ?></figcaption>
                <?php endif; ?>
              </figure>
            <?php endforeach; ?>
          <?php else: ?>
            <?php /* No photos uploaded yet — numbered placeholders keep the layout intact. */ ?>
            <?php for ($i = 1; $i <= 8; $i++): ?>
              <figure class="gallery-item reveal">
                <div class="img-placeholder" role="img" aria-label="Placeholder for gallery photo <?= $i ?>">
                  <span><?= $i ?><small>Upload in admin → Gallery</small></span>
                </div>
              </figure>
            <?php endfor; ?>
          <?php endif; ?>
        </div>

        <?php if (trim((string) $contact['facebook_url']) !== ''): ?>
          <p class="center-note">More photos are posted regularly on our
            <a href="<?= e($contact['facebook_url']) ?>" target="_blank" rel="noopener noreferrer">Facebook page</a>.</p>
        <?php endif; ?>
      </div>
    </section>

    <!-- ---------- ADMISSIONS ---------- -->
    <section class="section section-tint" id="admissions" aria-labelledby="admissions-title">
      <div class="container">
        <div class="section-head reveal">
          <p class="eyebrow">Admissions</p>
          <h2 id="admissions-title">Joining <?= e($identity['school_name']) ?></h2>
          <p class="section-lede">We admit students from Nursery through Grade 10. Admissions open ahead of each
            academic session, and mid-session transfers are considered where seats allow.</p>
        </div>

        <div class="split admissions-split">
          <div class="split-text reveal">
            <h3>How to apply</h3>
            <?php $steps = is_array($pages['admission_steps']) ? array_filter($pages['admission_steps']) : []; ?>
            <?php if ($steps !== []): ?>
              <ol class="steps">
                <?php foreach ($steps as $step): ?>
                  <?php
                  /* "Heading. Rest of the sentence." renders the lead-in in bold. */
                  $step = (string) $step;
                  $split = preg_split('/(?<=\.)\s+/', $step, 2);
                  ?>
                  <li>
                    <?php if (is_array($split) && count($split) === 2): ?>
                      <strong><?= e($split[0]) ?></strong> <?= e($split[1]) ?>
                    <?php else: ?>
                      <?= e($step) ?>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ol>
            <?php endif; ?>

            <div class="callout reveal">
              <h4>Admissions office</h4>
              <p>
                <strong>Phone:</strong>
                <?php if (trim((string) $contact['phone']) !== ''): ?>
                  <a href="<?= e(tel_href($contact['phone'])) ?>"><?= e($contact['phone']) ?></a>
                  <?= trim((string) $contact['phone_alt']) !== '' ? ', <a href="' . e(tel_href($contact['phone_alt'])) . '">' . e($contact['phone_alt']) . '</a>' : '' ?>
                <?php else: ?>
                  <?= pending() ?>
                <?php endif; ?>
                <br />
                <strong>Email:</strong>
                <?php if (trim((string) $contact['email']) !== ''): ?>
                  <a href="mailto:<?= e($contact['email']) ?>"><?= e($contact['email']) ?></a>
                <?php else: ?>
                  <?= pending() ?>
                <?php endif; ?>
                <br />
                <strong>Office hours:</strong>
                <?= trim((string) $contact['office_hours']) !== '' ? e($contact['office_hours']) : pending() ?>
              </p>
            </div>
          </div>

          <div class="split-text reveal">
            <h3>Enquiry form</h3>
            <?php
            /*
             * Where enquiries go is set in admin → Settings:
             *   - a Formspree / Netlify / custom endpoint URL, or
             *   - blank, in which case js/main.js falls back to opening the
             *     visitor's email client addressed to the contact email.
             */
            $form_action = trim((string) get_in($c, 'form.action', ''));
            ?>
            <form class="enquiry-form" id="enquiryForm" novalidate
                  <?= $form_action !== '' ? 'action="' . e($form_action) . '" method="POST"' : '' ?>
                  data-mailto="<?= e($contact['email']) ?>">
              <div class="field">
                <label for="f-name">Full name <span class="req" aria-hidden="true">*</span></label>
                <input type="text" id="f-name" name="name" autocomplete="name" required aria-describedby="e-name" />
                <p class="error" id="e-name" role="alert" hidden></p>
              </div>

              <div class="field-row">
                <div class="field">
                  <label for="f-phone">Phone <span class="req" aria-hidden="true">*</span></label>
                  <input type="tel" id="f-phone" name="phone" autocomplete="tel" inputmode="tel"
                         placeholder="98XXXXXXXX" required aria-describedby="e-phone" />
                  <p class="error" id="e-phone" role="alert" hidden></p>
                </div>
                <div class="field">
                  <label for="f-email">Email</label>
                  <input type="email" id="f-email" name="email" autocomplete="email" aria-describedby="e-email" />
                  <p class="error" id="e-email" role="alert" hidden></p>
                </div>
              </div>

              <div class="field">
                <label for="f-grade">Grade interested in <span class="req" aria-hidden="true">*</span></label>
                <select id="f-grade" name="grade" required aria-describedby="e-grade">
                  <option value="">Please select a grade</option>
                  <?php
                  $grades = ['Nursery', 'LKG', 'UKG'];
                  for ($g = 1; $g <= 10; $g++) {
                      $grades[] = 'Grade ' . $g;
                  }
                  foreach ($grades as $grade) {
                      echo '<option>' . e($grade) . "</option>\n";
                  }
                  ?>
                </select>
                <p class="error" id="e-grade" role="alert" hidden></p>
              </div>

              <div class="field">
                <label for="f-message">Message <span class="req" aria-hidden="true">*</span></label>
                <textarea id="f-message" name="message" rows="5" required aria-describedby="e-message"
                          placeholder="Tell us a little about your child and what you would like to know."></textarea>
                <p class="error" id="e-message" role="alert" hidden></p>
              </div>

              <button class="btn btn-primary btn-block" type="submit">Send enquiry</button>
              <p class="form-status" id="formStatus" role="status" aria-live="polite"></p>
              <p class="note dim">We reply to enquiries within two working days. Your details are used only to
                answer your enquiry.</p>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- ---------- CONTACT ---------- -->
    <section class="section" id="contact" aria-labelledby="contact-title">
      <div class="container">
        <div class="section-head reveal">
          <p class="eyebrow">Contact</p>
          <h2 id="contact-title">Come and see us</h2>
          <p class="section-lede">We are just off the Kaushaltar–Biruwa road in Sirutar. Visitors are welcome
            during school hours.</p>
        </div>

        <div class="contact-layout">
          <div class="contact-details reveal">
            <div class="contact-block">
              <h3>Address</h3>
              <address>
                <?= e($identity['school_name']) ?><br />
                <?php if (trim((string) $contact['street']) !== ''): ?><?= e($contact['street']) ?><br /><?php endif; ?>
                <?= e($contact['locality']) ?><br />
                <?= e($contact['district']) ?><?php if (trim((string) $contact['country']) !== ''): ?><br /><?= e($contact['country']) ?><?php endif; ?>
              </address>
            </div>

            <div class="contact-block">
              <h3>Phone</h3>
              <p>
                <?php if (trim((string) $contact['phone']) !== ''): ?>
                  <a href="<?= e(tel_href($contact['phone'])) ?>"><?= e($contact['phone']) ?></a>
                  <?php if (trim((string) $contact['phone_alt']) !== ''): ?>
                    <br /><a href="<?= e(tel_href($contact['phone_alt'])) ?>"><?= e($contact['phone_alt']) ?></a>
                  <?php endif; ?>
                <?php else: ?>
                  <?= pending() ?>
                <?php endif; ?>
              </p>
            </div>

            <div class="contact-block">
              <h3>Email</h3>
              <p>
                <?php if (trim((string) $contact['email']) !== ''): ?>
                  <a href="mailto:<?= e($contact['email']) ?>"><?= e($contact['email']) ?></a>
                <?php else: ?>
                  <?= pending() ?>
                <?php endif; ?>
              </p>
            </div>

            <div class="contact-block">
              <h3>School hours</h3>
              <p><?= trim((string) $contact['school_hours']) !== '' ? e($contact['school_hours']) : pending() ?></p>
            </div>

            <?php if (trim((string) $contact['facebook_url']) !== ''): ?>
            <div class="contact-block">
              <h3>Follow us</h3>
              <div class="social-row">
                <a class="social-icon dark" href="<?= e($contact['facebook_url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook page">
                  <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M13.5 22v-8h2.8l.42-3.25H13.5V8.67c0-.94.26-1.58 1.6-1.58h1.8V4.18A24 24 0 0 0 14.35 4C11.75 4 10 5.58 10 8.36v2.39H7.2V14H10v8h3.5Z"/></svg>
                </a>
                <a class="social-label" href="<?= e($contact['facebook_url']) ?>" target="_blank" rel="noopener noreferrer">
                  <?= e(preg_replace('#^https?://(www\.)?#', '', (string) $contact['facebook_url']) ?? '') ?>
                </a>
              </div>
            </div>
            <?php endif; ?>
          </div>

          <?php
          /* Keyless Google Maps embed, centred on the coordinates in admin → Contact. */
          $lat = (float) ($contact['map_lat'] ?: 27.6509446);
          $lng = (float) ($contact['map_lng'] ?: 85.3820644);
          $zoom = max(1, min(21, (int) ($contact['map_zoom'] ?: 17)));
          $coords = $lat . ',' . $lng;
          ?>
          <div class="map-wrap reveal">
            <iframe
              title="Map showing the location of <?= e($identity['school_name']) ?>"
              src="https://www.google.com/maps?q=<?= e($coords) ?>&amp;hl=en&amp;z=<?= $zoom ?>&amp;output=embed"
              width="100%" height="100%" style="border:0" loading="lazy"
              referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
          </div>
        </div>

        <p class="center-note">
          <a class="btn btn-outline" href="https://www.google.com/maps/search/?api=1&amp;query=<?= e($coords) ?>"
             target="_blank" rel="noopener noreferrer">Open in Google Maps</a>
        </p>
      </div>
    </section>

<?php require __DIR__ . '/includes/public-footer.php'; ?>
