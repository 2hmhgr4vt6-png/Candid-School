<?php
/**
 * public-header.php — <head> plus the sticky navigation, shared by every
 * public page (index.php, notices.php, notice.php).
 *
 * Set these before including it:
 *   $page_title       string  full <title> text
 *   $page_description string  meta description
 *   $is_home          bool    true on index.php (anchor links stay in-page)
 */

declare(strict_types=1);

require_once __DIR__ . '/content.php';

$c = content();
$identity = $c['identity'];
$contact  = $c['contact'];

$is_home = $is_home ?? false;
$school_name = (string) ($identity['school_name'] ?: 'Candid Career Secondary School');
$page_title = $page_title ?? ($school_name . ' — Sirutar, Bhaktapur, Nepal');
$page_description = $page_description ?? (
    $school_name . ' is a private, co-educational day school in Sirutar, Suryabinayak Municipality-1, '
    . 'Bhaktapur, Nepal, offering Nursery to Grade 10 under the national curriculum of the Government of Nepal.'
);

/**
 * Link to an in-page section: a bare anchor on the homepage, a full path
 * elsewhere, so the nav works identically on every page.
 */
function anchor(string $id): string
{
    global $is_home;

    return $is_home ? '#' . $id : url('index.php#' . $id);
}

$fb_url = (string) ($contact['facebook_url'] ?? '');
?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($page_title) ?></title>
  <meta name="description" content="<?= e($page_description) ?>" />
  <meta name="theme-color" content="#123f36" />

  <meta property="og:title" content="<?= e($school_name) ?>" />
  <meta property="og:description" content="<?= e($page_description) ?>" />
  <meta property="og:type" content="website" />
  <meta property="og:image" content="<?= e(url('images/og-share.jpg')) ?>" />

  <link rel="icon" href="<?= e(url('images/favicon.png')) ?>" type="image/png" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />

  <link rel="stylesheet" href="<?= e(url('css/style.css')) ?>" />
</head>
<body>
  <a class="skip-link" href="#main">Skip to main content</a>

  <header class="site-header" id="siteHeader">
    <div class="container header-inner">
      <a class="brand" href="<?= e($is_home ? '#home' : url('index.php')) ?>" aria-label="<?= e($school_name) ?> — home">
        <!-- To use a real logo, replace this <span> with:
             <img src="images/logo.png" alt="<?= e($school_name) ?> logo" class="brand-logo" /> -->
        <span class="brand-mark" aria-hidden="true">
          <svg viewBox="0 0 48 48" role="presentation" focusable="false">
            <path d="M24 6 4 15l20 9 20-9-20-9Z" fill="currentColor" />
            <path d="M12 22v9c0 3.5 5.4 6 12 6s12-2.5 12-6v-9l-12 5.4L12 22Z" fill="currentColor" opacity=".55" />
            <path d="M40 17v12" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" fill="none" />
          </svg>
        </span>
        <span class="brand-text">
          <strong><?= e($identity['short_name'] ?: 'Candid Career') ?></strong>
          <small>Secondary School</small>
        </span>
      </a>

      <button class="nav-toggle" id="navToggle" aria-expanded="false" aria-controls="primaryNav" aria-label="Open navigation menu">
        <span class="nav-toggle-bars" aria-hidden="true"><span></span><span></span><span></span></span>
      </button>

      <nav class="primary-nav" id="primaryNav" aria-label="Primary">
        <ul>
          <li><a href="<?= e($is_home ? '#home' : url('index.php')) ?>">Home</a></li>
          <li><a href="<?= e(anchor('about')) ?>">About Us</a></li>
          <li><a href="<?= e(anchor('academics')) ?>">Academics</a></li>
          <li><a href="<?= e(url('notices.php')) ?>">Notices</a></li>
          <li><a href="<?= e(anchor('parent-app')) ?>">Parent App</a></li>
          <li><a href="<?= e(anchor('gallery')) ?>">Gallery</a></li>
          <li><a href="<?= e(anchor('admissions')) ?>">Admissions</a></li>
          <li><a href="<?= e(anchor('contact')) ?>">Contact</a></li>
        </ul>
        <div class="nav-extras">
          <?php if ($fb_url !== ''): ?>
          <a class="social-icon" href="<?= e($fb_url) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($school_name) ?> on Facebook">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M13.5 22v-8h2.8l.42-3.25H13.5V8.67c0-.94.26-1.58 1.6-1.58h1.8V4.18A24 24 0 0 0 14.35 4C11.75 4 10 5.58 10 8.36v2.39H7.2V14H10v8h3.5Z"/></svg>
          </a>
          <?php endif; ?>
          <a class="btn btn-primary btn-sm nav-cta" href="<?= e(anchor('admissions')) ?>">Admissions</a>
        </div>
      </nav>
    </div>
  </header>

  <main id="main">
