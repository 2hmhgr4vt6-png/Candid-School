# Images

Every image on the site is currently a gray placeholder drawn in CSS. Drop real
files here using the exact filenames below and the placeholders disappear —
`index.html` has an HTML comment at each spot telling you what to swap.

## What goes where

| File | Used in | Recommended size | Notes |
| --- | --- | --- | --- |
| `hero.jpg` | Home hero background | 2000 × 1200 px, landscape | Wide campus or assembly shot. Keep the important subject slightly left of centre — text sits over the left half. Also uncomment the `background-image` line in `.hero-media` (`css/style.css`, section 6). |
| `welcome.jpg` | Welcome section | 900 × 1200 px, portrait | Students with a teacher, or a classroom in use. |
| `about.jpg` | About Us section | 900 × 1200 px, portrait | The school building or main gate. |
| `app-screen.png` | Parent App section | 600 × 1200 px, portrait | A real screenshot of the parent app home screen. |
| `gallery/gallery-01.jpg` … `gallery-08.jpg` | Gallery grid | 800 × 600 px, 4:3 landscape | Classrooms, cultural programme, sports day, prize distribution, field trip, library, assembly, campus. |
| `logo.png` | Header + footer brand mark | 200 px tall, transparent PNG | The school crest. Replacing it also means swapping the inline SVG monogram — see the comment in the `.brand` block of `index.html`. |
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
