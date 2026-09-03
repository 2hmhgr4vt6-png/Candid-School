# Candid Career Secondary School — website + admin panel

The website for **Candid Career Secondary School**, Sirutar, Suryabinayak
Municipality–1, Bhaktapur, Nepal — plus a simple admin panel so the school
office can post notices and keep the site's details current without touching
any code.

Plain PHP, CSS and vanilla JavaScript. No database, no build step, no
dependencies to install: it runs on ordinary cPanel shared hosting as soon as
the files are uploaded.

---

## Contents

- [What the school can edit](#what-the-school-can-edit)
- [Requirements](#requirements)
- [Preview it locally](#preview-it-locally)
- [Deploying to cPanel or shared hosting](#deploying-to-cpanel-or-shared-hosting)
- [First run: setting the admin password](#first-run-setting-the-admin-password)
- [Using the admin panel](#using-the-admin-panel)
- [File structure](#file-structure)
- [Adding real photos](#adding-real-photos)
- [The enquiry form](#the-enquiry-form)
- [Backups](#backups)
- [If the password is lost](#if-the-password-is-lost)
- [Security notes](#security-notes)
- [Hosting on nginx](#hosting-on-nginx)
- [Changing colours or fonts](#changing-colours-or-fonts)
- [Still to fill in](#still-to-fill-in)
- [Accessibility and browser support](#accessibility-and-browser-support)

---

## What the school can edit

Everything below is editable at **`/admin`** — no code, no re-uploading files.
Saves take effect on the public site immediately.

| Admin screen | Controls |
| --- | --- |
| **Notices** | Post, edit, pin, unpublish and delete notices. Categories, dates, and PDF/image attachments. |
| **Contact details** | Phone (two numbers), email, street/landmark, locality, district, office and school hours, map position, Facebook link, Facebook feed on/off. |
| **School details** | Full and short school name, tagline, established year, principal's name and title, and the four statistics in the green bar. |
| **Page text** | Welcome message, "Our story", achievements, vision, mission points, curriculum text, optional subjects, co-curricular activities, admission steps, and the facilities cards. |
| **Gallery** | Upload photos, write captions and alt text, reorder, delete. |
| **Settings** | Play Store / App Store links, enquiry-form destination, change the admin password. |

The **Dashboard** leads with a "Still to do" list of details that have not been
filled in yet, so it is obvious what is outstanding.

Things that stay in code (rarely change): the grade-levels table, the "Why
choose us" cards, and the parent-app feature list.

---

## Requirements

- **PHP 8.1 or newer** with the `mbstring` and `fileinfo` extensions
  (both standard on cPanel — under *Select PHP Version → Extensions*).
- Write permission on `data/`, `images/gallery/` and `files/`.
- No database, no Composer, no Node.

If any requirement is missing, the site shows a short page explaining exactly
what to change rather than a blank screen.

---

## Preview it locally

From the project root:

```bash
php -S localhost:8000
```

Then open <http://localhost:8000>. The admin panel is at
<http://localhost:8000/admin>.

Opening `index.php` by double-clicking will **not** work — PHP needs to run
through a server. Any of these are fine too: MAMP, XAMPP, Laragon, or
`php artisan serve`-style built-in servers.

### Checking the responsive breakpoints

Open the browser device toolbar (`Ctrl/Cmd + Shift + M`) and test at **375 px**,
**768 px** (the nav collapses to a hamburger at and below this), **1024 px** and
**1440 px**.

---

## Deploying to cPanel or shared hosting

1. **Upload** everything to `public_html` (or a subfolder), keeping the folder
   structure. Over FTP or cPanel's File Manager — a zip upload plus *Extract* is
   quickest.
2. **Set folder permissions to 755** on these three, so the admin panel can
   write to them:
   - `data/`
   - `images/gallery/`
   - `files/`

   In File Manager: right-click the folder → *Change Permissions* → tick the
   three "read" and the owner "write" boxes (755). If saving in the admin panel
   later reports a permissions error, this is what it means.
3. **Turn on HTTPS.** In cPanel, *SSL/TLS Status → Run AutoSSL* issues a free
   certificate. Then uncomment the "Force HTTPS" block at the bottom of
   `.htaccess`. This matters — the admin panel sends a password.
4. **Visit `https://yourdomain/admin`** and set your password (next section).

The included `.htaccess` handles the default document, blocks access to `data/`,
sets security headers, enables compression and caches static assets. Apache
picks it up automatically; nginx users see [below](#hosting-on-nginx).

---

## First run: setting the admin password

The first time anyone visits `/admin`, it redirects to a setup page that asks
you to choose the admin password. That page then closes itself permanently —
once a password exists it can only be changed from inside the panel
(*Settings → Change the admin password*).

- Minimum 10 characters. A short phrase (`sirutar-school-2026`) is both easier
  to remember and harder to guess than a short complicated word.
- It is stored as a bcrypt hash, never as readable text.
- There is one shared password for the whole office, by design.

---

## Using the admin panel

### Posting a notice

*Notices → Add a notice.* Give it a title, pick the date and a category, and
write the details. Then:

- **Publish on the website** — untick to save it as a draft that only you can
  see. Drafts are invisible to visitors even at their direct URL.
- **Pin to the top** — keeps an important notice above newer ones, on both the
  homepage and the notice board page.
- **Attachment** — a PDF or image up to 6 MB (exam routines, fee notices, result
  sheets). Visitors get a "Download attachment" button.

Notice text is plain text. Leave a **blank line between paragraphs** and they
appear as separate paragraphs on the site. Notices show in three places: the
latest four on the homepage, all of them on `/notices.php`, and each one at its
own shareable link.

### Editing the site's text

*Page text* holds the longer written blocks. Two conventions:

- **Big boxes** (welcome, our story, curriculum): blank line = new paragraph.
- **List boxes** (mission, achievements, activities, admission steps): **one
  item per line**. Leave a list empty and that section disappears from the site
  rather than showing an empty heading.

For admission steps, ending the first sentence with a full stop renders that
sentence in bold as the step's heading.

### Unfilled details

Any contact detail you have not entered yet shows on the site as a small
italic **"To be updated"** marker rather than an empty gap or a dead link — so
a half-finished site still looks deliberate. The Dashboard lists everything
outstanding.

---

## File structure

```
.
├── index.php               Public homepage (all sections)
├── notices.php             Public notice board — every published notice
├── notice.php              A single notice, at a shareable URL
│
├── admin/
│   ├── index.php           Dashboard: what is still missing, recent notices
│   ├── login.php           Password entry
│   ├── setup.php           First-run password creation (self-closing)
│   ├── logout.php
│   ├── notices.php         Add / edit / pin / unpublish / delete notices
│   ├── contact.php         Phone, email, address, hours, map, Facebook
│   ├── content.php         Name, tagline, established, principal, statistics
│   ├── pages.php           Long-form text blocks and facilities
│   ├── gallery.php         Photo upload, captions, order, delete
│   ├── settings.php        App links, form endpoint, change password
│   └── layout.php          Shared admin chrome
│
├── includes/
│   ├── bootstrap.php       Paths, JSON storage, escaping helpers
│   ├── content.php         Editable content + the defaults behind it
│   ├── notices.php         Notice storage and sorting
│   ├── auth.php            Login, sessions, CSRF, rate limiting
│   ├── uploads.php         Validated file uploads
│   ├── public-header.php   <head> and the site navigation
│   └── public-footer.php   CTA strip, footer, scripts
│
├── data/                   Content, written by the admin panel (keep writable)
│   ├── content.json        All site content
│   ├── notices.json        The notices
│   ├── admin.php           Hashed password (PHP-wrapped — see Security notes)
│   └── .htaccess           Denies web access to this folder
│
├── css/
│   ├── style.css           Public site
│   └── admin.css           Admin panel
├── js/main.js              Nav, smooth scroll, scroll reveal, form validation
├── images/
│   ├── README.md           Which image goes where, and at what size
│   └── gallery/            Uploaded photos (keep writable)
└── files/                  Notice attachments (keep writable)
```

`data/`, `files/` and `images/gallery/` are in `.gitignore`: they are the live
site's content, and committing them would overwrite the school's real content on
every deploy.

---

## Adding real photos

Gallery photos are uploaded through the admin panel — no file editing.

Five images are still referenced directly in the markup, because they are
design elements rather than content. See
[`images/README.md`](images/README.md) for the filename and size of each:
`hero.jpg`, `welcome.jpg`, `about.jpg`, `app-screen.png`, plus `logo.png`,
`favicon.png` and `og-share.jpg`. Each has an HTML comment at its spot showing
the `<img>` tag to paste in. Compress everything first —
[squoosh.app](https://squoosh.app) is free and runs in the browser.

Please make sure the school has parents' consent before publishing any
recognisable photograph of a student.

---

## The enquiry form

The admissions form validates in the browser and, by default, opens the
visitor's own email app addressed to the school email from *Contact details*.
That works everywhere but depends on the visitor having email set up.

To collect enquiries properly, create a free form at
[formspree.io](https://formspree.io) and paste the address it gives you into
*Settings → Form endpoint*. The form posts the fields `name`, `phone`, `email`,
`grade` and `message`.

---

## Backups

The entire content of the website is three things:

```
data/content.json     all site text and settings
data/notices.json     every notice
images/gallery/       the photos
files/                notice attachments
```

Download those and you have a complete backup. To restore, upload them back.
cPanel's *Backup* tool covers them automatically as part of a home-directory
backup.

---

## If the password is lost

There is no email recovery — the site has no way to send email reliably on
shared hosting, and a recovery link would be a weak point.

Instead: delete **`data/admin.php`** on the server (File Manager or FTP). The
next visit to `/admin` shows the first-run setup page again, and you can set a
new password. No content is lost.

---

## Security notes

What is built in:

- **Passwords** are stored as bcrypt hashes (`password_hash`), never in the
  clear, and re-hashed automatically if PHP's default cost changes.
- **The credentials file is PHP, not JSON.** `data/admin.php` returns a PHP
  array, so a direct web request to it *executes* the file and outputs nothing —
  on any web server, with or without the `.htaccess`. A `.json` file would be
  served as readable text by a server that ignored the `.htaccess` rules.
- **CSRF tokens** on every form. A POST without a valid token is rejected, so
  another site cannot make a logged-in admin's browser change your content.
- **Login rate limiting** — 5 failed attempts from one IP triggers a 15-minute
  lockout, which makes password guessing impractical.
- **Session hardening** — `HttpOnly` and `SameSite=Lax` cookies, marked
  `Secure` automatically over HTTPS, a fresh session id on login, a 2-hour idle
  timeout, and a check that binds the session to the browser that created it.
- **Output escaping everywhere.** All content is stored as plain text and
  escaped on the way out, so no editor (or attacker who got in) can inject
  markup or script into a page. This is verified by an automated test that
  posts a `<script>` payload and asserts it renders as visible text.
- **Upload validation** — extension *and* real content type must both be on the
  whitelist, images must genuinely decode as images, filenames are generated
  rather than accepted, a 6 MB cap, and an `.htaccess` in every upload folder
  that disables script execution.
- **Path traversal** is blocked on every delete: the resolved path must sit
  inside the intended folder.
- **`noindex`** on all admin pages.

What is your responsibility:

- **Turn on HTTPS.** Until you do, the admin password crosses the network in
  the clear. The Dashboard shows a reminder while the panel is served over
  plain `http://`. This is the single most important step.
- Keep the password to the people who need it, and change it in *Settings* when
  someone leaves.

---

## Hosting on nginx

`.htaccess` files are ignored by nginx, so add the equivalent to your server
block. (`data/admin.php` is safe either way — see Security notes — but the
content files should not be browsable.)

```nginx
server {
    index index.php index.html;

    # Content store: not directly readable
    location ^~ /data/  { deny all; return 404; }

    # Uploads: serve as files, never execute
    location ^~ /files/          { location ~ \.php$ { deny all; } }
    location ^~ /images/gallery/ { location ~ \.php$ { deny all; } }

    # Hide dotfiles
    location ~ /\. { deny all; return 404; }

    location / { try_files $uri $uri/ =404; }

    location ~ \.php$ {
        include        fastcgi_params;
        fastcgi_pass   unix:/run/php/php8.1-fpm.sock;
        fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options        "SAMEORIGIN" always;
    add_header Referrer-Policy        "strict-origin-when-cross-origin" always;
}
```

---

## Changing colours or fonts

Every colour, font, radius and shadow is a CSS custom property at the top of
`css/style.css` (section 1). Change it once and the whole site follows:

```css
:root {
  --green-800: #123f36;   /* primary — headers, buttons, stats bar */
  --gold-500:  #d9901a;   /* warm accent — eyebrows, CTAs, highlights */
  --font-display: "Fraunces", Georgia, serif;                  /* headings */
  --font-sans:    "Plus Jakarta Sans", system-ui, sans-serif;  /* body */
}
```

The admin panel has its own equivalents at the top of `css/admin.css`. Fonts
load from Google Fonts via a `<link>` in `includes/public-header.php` — update
that too if you change the font families.

---

## Still to fill in

These came from the school's own details and are already correct: the name,
location, Nursery–Grade 10 range, private co-educational day-school status, the
~554 student count, the Government of Nepal national curriculum, the Facebook
page, and the map coordinates (27.6509446, 85.3820644).

Everything the school still needs to supply is now a field in the admin panel,
and the Dashboard lists what is outstanding:

- Phone number, email address, exact street/landmark, office and school hours
- Established year, principal's name
- Achievements, optional subjects for Grades 9–10
- Parent app store links
- Gallery photos, and the five design images listed above

**No tagline** was confirmed from the Facebook page, so the site currently shows
*"Learning today, leading tomorrow."* Two alternatives are offered in the admin
panel next to the field: *"Character first. Career always."* and *"Where values
shape careers."* Replace it with the school's own line if they have one.

The body text in the Welcome, About, Vision & Mission, Facilities, Academics and
Admissions sections is written to match the facts above, but it is **editorial
placeholder prose** — particularly the admission steps and the age bands in the
grade table, which follow common Nepali school practice rather than this
school's stated policy. Read it through and correct anything that does not match
how the school actually runs.

---

## Accessibility and browser support

Semantic HTML5 landmarks, a skip link, visible focus rings, labelled form
fields with `role="alert"` errors, `aria-expanded` on the mobile nav (closes on
Escape and on outside click), `aria-current` on the active nav link, an
`aria-live` form status, alt text or `role="img"` labels on every image and
placeholder, and colour contrast meeting WCAG AA on text. The gallery asks for
a screen-reader description separately from the visible caption.

Scroll-reveal animations, the counter and smooth scrolling switch off
automatically when the visitor has *reduce motion* enabled. If JavaScript fails
to load, every section still renders. A print stylesheet drops the nav, map,
form and Facebook feed.

Works in current Chrome, Edge, Firefox and Safari, on desktop and mobile.
