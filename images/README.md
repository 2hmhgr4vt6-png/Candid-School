# Images

Gallery photos are managed from the admin panel (*Gallery*). The images listed
below are design elements rather than content, so they are still referenced
directly in the markup: drop a real file here using the exact filename and the
grey placeholder disappears. `index.php` and `includes/public-header.php` have an
HTML comment at each spot showing the tag to paste in.

## What goes where

| File | Used in | Recommended size | Notes |
| --- | --- | --- | --- |
| `hero.jpg` | Home hero background | 2000 × 1200 px, landscape | Wide campus or assembly shot. Keep the important subject slightly left of centre — text sits over the left half. Also uncomment the `background-image` line in `.hero-media` (`css/style.css`, section 6). |
| `welcome.jpg` | Welcome section | 900 × 1200 px, portrait | Students with a teacher, or a classroom in use. |
| `about.jpg` | About Us section | 900 × 1200 px, portrait | The school building or main gate. |
| `app-screen.png` | Parent App section | 600 × 1200 px, portrait | A real screenshot of the parent app home screen. |
| `gallery/` | Gallery grid | 800 × 600 px, 4:3 landscape | **Upload these through the admin panel** (*Gallery*) rather than by hand — it stores the caption, alt text and order for you. Files dropped in here over FTP are detected and offered for adoption on that screen. |
| `logo.png` | Header + footer brand mark | 200 px tall, transparent PNG | The school crest. Replacing it also means swapping the inline SVG monogram — see the comment in the `.brand` block of `includes/public-header.php`. |
| `favicon.png` | Browser tab | 512 × 512 px | Square crop of the crest. |
| `og-share.jpg` | Facebook / WhatsApp link previews | 1200 × 630 px | Campus photo with the school name legible. |

## Before you upload

- **Compress everything.** Aim for under 300 KB per photo (under 500 KB for
  `hero.jpg`). [Squoosh](https://squoosh.app) or TinyPNG do this in the browser.
- **Write real alt text.** Each placeholder is replaced by an `<img>` tag with an
  `alt` attribute — describe what is happening in the photo ("Grade 5 students
  presenting a science project"), not the file ("photo", "image1").
- **Get permission for photos of children.** Confirm the school has consent from
  parents before publishing any recognisable student photograph.
- Prefer `.jpg` for photographs and `.png` for the logo, favicon and screenshots.
