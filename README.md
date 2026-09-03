# Candid Career Secondary School — website

A static, single-page marketing site for **Candid Career Secondary School**,
Sirutar, Suryabinayak Municipality–1, Bhaktapur, Nepal.

Plain HTML, CSS and vanilla JavaScript — no build step, no framework, no
dependencies to install. It can be dropped onto GitHub Pages, Netlify, or a
shared-hosting `public_html` folder as-is.

---

## Contents

- [Preview it locally](#preview-it-locally)
- [File structure](#file-structure)
- [Replace the placeholder content](#replace-the-placeholder-content)
- [Add real photos](#add-real-photos)
- [Wire up the enquiry form](#wire-up-the-enquiry-form)
- [The Facebook feed](#the-facebook-feed)
- [The Google Map](#the-google-map)
- [Change the colours or fonts](#change-the-colours-or-fonts)
- [Deploy](#deploy)
- [Accessibility and browser support](#accessibility-and-browser-support)

---

## Preview it locally

The quickest way — just open the file:

```
open index.html          # macOS
xdg-open index.html      # Linux
start index.html         # Windows
```

That works for everything except the Facebook feed, which needs a real
`http://` origin. To serve the folder properly, use any one of these from the
project root:

```bash
python3 -m http.server 8000     # Python 3 (pre-installed on macOS/Linux)
npx serve .                     # Node.js
php -S localhost:8000           # PHP
```

Then visit <http://localhost:8000>.

If you use VS Code, the **Live Server** extension does the same thing with a
right-click → *Open with Live Server*.

### Check the responsive breakpoints

Open your browser's device toolbar (`Ctrl/Cmd + Shift + M`) and test at
**375 px** (phone), **768 px** (tablet portrait — the nav collapses to a
hamburger here), **1024 px** (tablet landscape) and **1440 px** (desktop).

---

## File structure

```
.
├── index.html          Every section of the site (single page, anchor nav)
├── css/
│   └── style.css       All styles, organised into 17 numbered sections
├── js/
│   └── main.js         Nav, smooth scroll, scroll reveal, form validation
├── images/
│   ├── README.md       Which image goes where, and at what size
│   └── gallery/        Gallery photos (gallery-01.jpg … gallery-08.jpg)
└── README.md           This file
```

The site is a **single page with anchor sections** — `#home`, `#about`,
`#academics`, `#parent-app`, `#gallery`, `#admissions`, `#contact`. The nav
links, footer links and CTA buttons all point at those IDs.

Want real separate pages later? Split each `<section>` out of `index.html` into
its own file (`about.html`, `academics.html`, …), copy the `<header>` and
`<footer>` into each, and change the nav `href`s from `#about` to `about.html`.

---

## Replace the placeholder content

Anything still needing real information is marked **`[PLACEHOLDER]`** in
`index.html`, always with an HTML comment above it explaining what to put there.
Find them all at once:

```bash
grep -n "PLACEHOLDER" index.html
```

### Must be replaced before publishing

| What | Where to find it | Sections it appears in |
| --- | --- | --- |
| **Phone number** | School office | Admissions callout, Contact, CTA strip, Footer |
| **Email address** | School office / Facebook page | Admissions callout, Contact, Footer, form `data-mailto` |
| **Exact street address** | Google Maps listing / school office | Contact, Footer |
| **Office & school hours** | School office | Admissions callout, Contact |
| **Established year** | Facebook *About* section or school records | About Us |
| **Principal's name** | School office | Welcome section signature |
| **Achievements** | Facebook posts, SEE results | About Us |
| **Optional subjects (Grades 9–10)** | Academics coordinator | Academics |
| **App store links** | Play Store / App Store listings | Parent App |
| **Gallery captions** | You | Gallery |
| **Site URL + share image** | Your host | `<meta property="og:*">` in `<head>` |

Phone and email each appear in **more than one place** — search-and-replace
rather than editing one by one:

```bash
# example: set the phone number everywhere at once
sed -i 's|\[PLACEHOLDER — phone number\]|01-XXXXXXX|g' index.html
sed -i 's|href="tel:+977"|href="tel:+977XXXXXXXXX"|g' index.html
```

### Tagline

No tagline was confirmed from the school's Facebook page, so the hero currently
shows the first of three options. All three are listed in a comment right above
it in `index.html` — keep one, or paste the school's own line:

1. **"Learning today, leading tomorrow."** ← currently in use
2. **"Character first. Career always."**
3. **"Where values shape careers."**

### Facts already filled in as real content

These came from the school's own details and need no editing: the name,
location, Nursery–Grade 10 range, private co-educational day-school status, the
~554 student count, the Government of Nepal national curriculum, the Facebook
page URL, and the map coordinates (27.6509446, 85.3820644).

Body copy in the Welcome, About, Vision & Mission, Facilities, Academics and
Admissions sections is written to be accurate to those facts but is **editorial
placeholder text** — read it through and adjust anything that does not match how
the school actually runs.

---

## Add real photos

See [`images/README.md`](images/README.md) for the full table of filenames and
sizes. In short:

1. Save your compressed photo into `images/` (or `images/gallery/`) using the
   filename listed there.
2. In `index.html`, find the matching `<div class="img-placeholder">` and replace
   the whole block with an `<img>` tag. Every gallery tile has a copy-paste
   example in the comment above the gallery grid:

   ```html
   <figure class="gallery-item reveal">
     <img src="images/gallery/gallery-01.jpg"
          alt="Grade 5 students presenting a science project"
          loading="lazy" width="800" height="600" />
     <figcaption>Science exhibition, Grade 5</figcaption>
   </figure>
   ```

3. For the **hero background**, also uncomment the `background-image` line in the
   `.hero-media` rule (`css/style.css`, section 6) so the photo sits behind the
   gradient overlay.

Always write a descriptive `alt` — it is what screen-reader users and search
engines read.

---

## Wire up the enquiry form

The Admissions form validates in the browser but has **no backend**. Until you
connect one, submitting it opens the visitor's email app (using the
`data-mailto` address on the `<form>` — set that to the school's real email).

Pick whichever suits your host. The full instructions are also in a comment
above the `<form>` in `index.html`.

**Formspree** (works on any static host, free tier available):

```html
<form class="enquiry-form" id="enquiryForm" novalidate
      action="https://formspree.io/f/YOUR_FORM_ID" method="POST">
```

**Netlify Forms** (if you deploy to Netlify):

```html
<form class="enquiry-form" id="enquiryForm" novalidate
      name="admissions" method="POST" data-netlify="true">
```

**Your own PHP script** (typical on cPanel shared hosting):

```html
<form class="enquiry-form" id="enquiryForm" novalidate
      action="send.php" method="POST">
```

`js/main.js` detects a real `action` and lets the browser submit normally; it
only falls back to `mailto:` when the form has no action. Field names sent are
`name`, `phone`, `email`, `grade` and `message`.

---

## The Facebook feed

The News & Updates section embeds the official Facebook Page plugin for
[facebook.com/candid.intl.5](https://www.facebook.com/candid.intl.5) via the
Facebook SDK (loaded at the bottom of `index.html` — no app or API key needed).

Things worth knowing:

- It **will not render from `file://`** — serve the site over `http://` to see it
  locally.
- Browsers or extensions that block third-party scripts will hide it. A plain
  text link to the page is always shown underneath as a fallback.
- The plugin needs a pixel width, so `js/main.js` measures the container and
  sets `data-width` (Facebook caps it at 500 px), re-rendering on resize.
- To remove the feed entirely, delete the `<section id="news">` block and the
  `<div id="fb-root">` plus the `connect.facebook.net` `<script>` tag.

---

## The Google Map

The Contact section embeds an iframe centred on the school's coordinates —
**27.6509446, 85.3820644** — using Google's keyless `output=embed` URL. Nothing
to configure and no billing account required.

To recentre it, edit the `q=` values in the iframe `src` (and in the
"Open in Google Maps" button below it). Change `z=17` to zoom in or out.

Once the school has a verified Google Business Profile, you can swap the URL for
the *Share → Embed a map* snippet from that listing to get the school's name and
reviews in the map pin.

---

## Change the colours or fonts

Every colour, font, radius and shadow is a CSS custom property at the top of
`css/style.css` (section 1). Change it in one place and the whole site follows:

```css
:root {
  --green-800: #123f36;   /* primary — headers, buttons, stats bar */
  --gold-500:  #d9901a;   /* warm accent — eyebrows, CTAs, highlights */
  --font-display: "Fraunces", Georgia, serif;   /* headings */
  --font-sans:    "Plus Jakarta Sans", system-ui, sans-serif;  /* body */
}
```

Fonts are loaded from Google Fonts via a `<link>` in `<head>`. If you change
`--font-display` or `--font-sans`, update that link too.

---

## Deploy

**GitHub Pages** — push this repo, then *Settings → Pages → Deploy from a
branch*, pick your branch and the `/ (root)` folder.

**Netlify** — drag the project folder onto the Netlify dashboard, or connect the
repo. No build command; publish directory `.`.

**cPanel / shared hosting** — upload `index.html`, `css/`, `js/` and `images/`
into `public_html` over FTP or the File Manager, keeping the folder structure.

There is nothing to build and no server-side code, so any static host works.

---

## Accessibility and browser support

Built in: semantic HTML5 landmarks, a skip link, visible focus rings, labelled
form fields with `role="alert"` error messages, `aria-expanded` on the mobile
nav toggle (closes on Escape and on outside click), `aria-current` on the active
nav link, an `aria-live` form status, alt text or `role="img"` labels on every
image and placeholder, and colour contrast meeting WCAG AA on text.

Scroll-reveal animations, the counter and smooth scrolling are all disabled
automatically when the visitor has *reduce motion* enabled in their operating
system. If JavaScript fails to load, every section still renders — the reveal
classes are only hidden once JS confirms it is running.

Works in all current versions of Chrome, Edge, Firefox and Safari, on desktop
and mobile. A print stylesheet is included, which drops the nav, map, form and
Facebook feed.
