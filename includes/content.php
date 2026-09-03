<?php
/**
 * content.php — the editable content store.
 *
 * content_defaults() is the single source of truth for the shape of
 * data/content.json. Anything the admin panel has never saved falls back to
 * these values, so the site always renders — even on a fresh checkout with no
 * data/ directory at all.
 *
 * Values left as an empty string are the ones the school still needs to supply
 * (phone, email, established year …); the public site shows a clearly marked
 * "not set yet" note in their place rather than a broken blank.
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function content_defaults(): array
{
    return [
        'identity' => [
            'school_name'     => 'Candid Career Secondary School',
            'short_name'      => 'Candid Career',
            'tagline'         => 'Learning today, leading tomorrow.',
            'established'     => '',
            'principal_name'  => '',
            'principal_title' => 'Principal',
        ],

        'stats' => [
            [
                'value' => 'Nursery–10',
                'label' => 'Grade levels offered',
                'count' => '',
            ],
            [
                'value' => '554',
                'label' => 'Students enrolled',
                'count' => '554',   // set = animate up to this number
            ],
            [
                'value' => 'Co-ed',
                'label' => 'Girls and boys together',
                'count' => '',
            ],
            [
                'value' => 'Day School',
                'label' => 'Day scholars only',
                'count' => '',
            ],
        ],

        'contact' => [
            'phone'         => '',
            'phone_alt'     => '',
            'email'         => '',
            'street'        => '',
            'locality'      => 'Sirutar, Suryabinayak Municipality–1',
            'district'      => 'Bhaktapur District, Bagmati Province',
            'country'       => 'Nepal',
            'office_hours'  => '',
            'school_hours'  => '',
            'facebook_url'  => 'https://www.facebook.com/candid.intl.5',
            'map_lat'       => '27.6509446',
            'map_lng'       => '85.3820644',
            'map_zoom'      => '17',
            'show_fb_feed'  => true,
        ],

        'pages' => [
            'welcome_title' => 'A warm welcome from our school family',
            'welcome_body'  => "For families in Sirutar and the surrounding wards of Suryabinayak Municipality, Candid Career Secondary School is a place where children are known by name. Our teachers work closely with every student — from a four-year-old's first day in Nursery to a Grade 10 student preparing for the Secondary Education Examination — so that academic progress and personal character grow together.\n\nWe believe a good school does three things well: it teaches carefully, it holds students to kind and clear expectations, and it keeps parents genuinely informed. That last point is why we built a companion mobile app for parents, and why our door is always open.",

            'about_body'    => "Candid Career Secondary School was founded by a group of educators who wanted a neighbourhood school in Sirutar that could take a child all the way from their very first classroom to the end of secondary education without ever feeling impersonal.\n\nWhat began as a small campus near the Kaushaltar–Biruwa road now serves roughly 554 students across Nursery to Grade 10. Growth has not changed the founding idea: every child is taught by name, every family is treated as a partner, and every year should leave a student more capable and more considerate than the last.",

            'vision'        => 'To be the school Sirutar families trust most — a place where academic strength, cultural pride and personal integrity are inseparable, and where every graduate leaves with a clear sense of the career and the character they are building toward.',

            'mission'       => [
                'Deliver the national curriculum thoroughly, in classes small enough for real attention.',
                'Give every student a stage, a field and a chance to lead.',
                'Hold clear standards of discipline with consistent kindness.',
                'Keep parents informed daily, not termly.',
                'Keep the school affordable for the community it serves.',
            ],

            'achievements'  => [],

            'curriculum_body' => "We teach the national curriculum prescribed by the Government of Nepal, using the Curriculum Development Centre's syllabus and textbooks across all grades. Grade 10 students sit the Secondary Education Examination (SEE) as regular candidates.\n\nWithin that framework we add what a curriculum cannot mandate: regular unit tests so nothing is discovered too late, written feedback parents can actually read, and remedial time for students who need a second pass at a topic.",

            'optional_subjects' => '',

            'activities' => [
                'Cultural programmes — annual function, national day celebrations, festival programmes with music, dance and drama.',
                'Sports competitions — inter-house athletics and games, plus inter-school fixtures through the year.',
                'Speech, quiz and art contests — held grade-wise so younger students compete too.',
                'Educational tours and field visits — grade-appropriate trips tied to the syllabus.',
                'Cleanliness and community days — students take responsibility for their own campus and neighbourhood.',
            ],

            'admission_steps' => [
                'Get in touch. Call the school office or send the enquiry form on this page. Tell us your child\'s age and the grade you have in mind.',
                'Visit the campus. We would rather you saw the classrooms and met a teacher before deciding. Walk-in visits during school hours are welcome.',
                'Interaction or entrance assessment. Early-years applicants have a short informal interaction; applicants for Grade 1 and above sit a brief placement assessment in English, Nepali and Mathematics.',
                'Submit documents. Birth certificate, the previous school\'s transfer certificate and marksheet (for transfers), passport-size photographs, and a parent or guardian\'s identification.',
                'Confirm the seat. Complete the admission form and fee formalities at the office, and collect your parent app credentials.',
            ],
        ],

        'facilities' => [
            ['title' => 'Classrooms',            'body' => 'Bright, well-ventilated classrooms sized for attentive teaching, with dedicated early-years rooms set up for play-based learning in Nursery and the pre-primary grades.'],
            ['title' => 'Sports',                'body' => 'An open ground for daily physical education and the annual inter-house sports competition, plus indoor games during monsoon weeks.'],
            ['title' => 'Cultural & Arts Space', 'body' => 'Room for music, dance and drama practice ahead of our cultural programmes, national day celebrations and annual function.'],
            ['title' => 'Library',               'body' => 'A growing collection in Nepali and English, with borrowing tracked through the same system parents can see in the app.'],
            ['title' => 'School Bus',            'body' => 'Transport along routes serving Sirutar, Kaushaltar and nearby settlements — with live GPS tracking for parents on pick-up and drop-off.'],
            ['title' => 'Safe Campus',           'body' => 'A gated, supervised day campus with clean drinking water, first-aid provision and staff on duty throughout school hours.'],
        ],

        'app' => [
            'play_store_url' => '',
            'app_store_url'  => '',
        ],

        'gallery' => [],

        'form' => [
            // Where the admissions enquiry form posts. Empty = mailto: fallback
            // to the contact email.
            'action' => '',
        ],
    ];
}

/**
 * Load site content, merged over the defaults.
 *
 * Cached per request — the public page calls this from several partials.
 */
function content(bool $refresh = false): array
{
    static $cache = null;

    if ($cache === null || $refresh) {
        $cache = content_merge(content_defaults(), read_json('content.json'));
    }

    return $cache;
}

/**
 * Merge saved values over defaults.
 *
 * Associative arrays merge key by key so a new default added in a later version
 * appears without the admin having to re-save. Lists (stats, mission, gallery,
 * facilities …) are replaced wholesale — an admin who deletes every item means
 * it, and merging would resurrect deleted rows.
 */
function content_merge(array $defaults, array $saved): array
{
    $out = $defaults;

    foreach ($saved as $key => $value) {
        if (is_array($value) && isset($defaults[$key]) && is_array($defaults[$key]) && !array_is_list($value)) {
            $out[$key] = content_merge($defaults[$key], $value);
        } else {
            $out[$key] = $value;
        }
    }

    return $out;
}

/** Persist one top-level section (identity, contact, pages …). */
function content_save_section(string $section, array $values): bool
{
    $stored = read_json('content.json');
    $stored[$section] = $values;

    $ok = write_json('content.json', $stored);
    if ($ok) {
        content(true); // drop the per-request cache so the page reflects the save
    }

    return $ok;
}

/**
 * A value the school still has to fill in.
 *
 * The public site calls this to decide between printing a real value and
 * printing a muted "to be updated" note, so an unset phone number never shows
 * as an empty gap or a dead link.
 */
function pending(string $what = ''): string
{
    return '<span class="pending" title="This detail has not been added in the admin panel yet.">'
        . ($what !== '' ? e($what) : 'To be updated')
        . '</span>';
}
