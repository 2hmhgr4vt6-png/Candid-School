<?php
/**
 * notices.php — notice board storage (data/notices.json).
 *
 * Shape on disk:
 *   { "next_id": 4, "items": [ { id, title, slug, date, category, body,
 *                                pinned, published, attachment,
 *                                created_at, updated_at }, ... ] }
 *
 * Sort order for display: pinned first, then newest date, then newest id.
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

const NOTICE_CATEGORIES = ['General', 'Admissions', 'Examination', 'Holiday', 'Event', 'Result', 'Urgent'];

function notices_store(): array
{
    $store = read_json('notices.json', ['next_id' => 1, 'items' => []]);

    if (!isset($store['items']) || !is_array($store['items'])) {
        $store['items'] = [];
    }
    if (!isset($store['next_id']) || !is_int($store['next_id'])) {
        $store['next_id'] = 1;
    }

    return $store;
}

/** Normalise one record, filling anything a older/hand-edited file is missing. */
function notice_normalise(array $n): array
{
    return [
        'id'         => (int) ($n['id'] ?? 0),
        'title'      => trim((string) ($n['title'] ?? '')),
        'slug'       => (string) ($n['slug'] ?? ''),
        'date'       => (string) ($n['date'] ?? ''),
        'category'   => (string) ($n['category'] ?? 'General'),
        'body'       => (string) ($n['body'] ?? ''),
        'pinned'     => (bool) ($n['pinned'] ?? false),
        'published'  => (bool) ($n['published'] ?? true),
        'attachment' => (string) ($n['attachment'] ?? ''),
        'created_at' => (string) ($n['created_at'] ?? ''),
        'updated_at' => (string) ($n['updated_at'] ?? ''),
    ];
}

function notices_sort(array &$items): void
{
    usort($items, static function (array $a, array $b): int {
        // Pinned notices float to the top.
        $pin = ($b['pinned'] ? 1 : 0) <=> ($a['pinned'] ? 1 : 0);
        if ($pin !== 0) {
            return $pin;
        }
        $date = strcmp((string) $b['date'], (string) $a['date']);
        if ($date !== 0) {
            return $date;
        }

        return $b['id'] <=> $a['id'];
    });
}

/**
 * All notices, sorted for display.
 *
 * @param bool     $publishedOnly Public pages pass true; the admin list passes false.
 * @param int|null $limit         Cap the number returned.
 */
function notices_all(bool $publishedOnly = true, ?int $limit = null): array
{
    $items = array_map('notice_normalise', notices_store()['items']);

    if ($publishedOnly) {
        $items = array_values(array_filter($items, static fn (array $n): bool => $n['published'] && $n['title'] !== ''));
    }

    notices_sort($items);

    return $limit !== null ? array_slice($items, 0, max(0, $limit)) : $items;
}

function notice_find(int $id, bool $publishedOnly = true): ?array
{
    foreach (notices_all($publishedOnly) as $notice) {
        if ($notice['id'] === $id) {
            return $notice;
        }
    }

    return null;
}

/** URL-safe slug used only to make notice links readable. */
function notice_slug(string $title): string
{
    $slug = strtolower(trim($title));
    // Keep Devanagari and other UTF-8 letters; drop punctuation.
    $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', $slug) ?? '';
    $slug = trim($slug, '-');

    return $slug === '' ? 'notice' : mb_substr($slug, 0, 60);
}

function notice_url(array $notice): string
{
    return url('notice.php?id=' . $notice['id'] . '&slug=' . rawurlencode($notice['slug'] ?: notice_slug($notice['title'])));
}

/**
 * Create or update a notice.
 *
 * @param array $input Raw (already validated) field values.
 * @param int   $id    0 to create, otherwise the record to update.
 * @return int|null    The saved id, or null if the write failed.
 */
function notice_save(array $input, int $id = 0): ?int
{
    $store = notices_store();
    $now = date('c');

    $record = [
        'id'         => $id,
        'title'      => trim((string) ($input['title'] ?? '')),
        'date'       => (string) ($input['date'] ?? date('Y-m-d')),
        'category'   => in_array($input['category'] ?? '', NOTICE_CATEGORIES, true) ? $input['category'] : 'General',
        'body'       => trim((string) ($input['body'] ?? '')),
        'pinned'     => !empty($input['pinned']),
        'published'  => !empty($input['published']),
        'attachment' => trim((string) ($input['attachment'] ?? '')),
    ];
    $record['slug'] = notice_slug($record['title']);

    if ($id > 0) {
        $found = false;
        foreach ($store['items'] as $i => $existing) {
            if ((int) ($existing['id'] ?? 0) === $id) {
                $record['created_at'] = (string) ($existing['created_at'] ?? $now);
                $record['updated_at'] = $now;
                $store['items'][$i] = $record;
                $found = true;
                break;
            }
        }
        if (!$found) {
            return null;
        }
    } else {
        $record['id'] = (int) $store['next_id'];
        $record['created_at'] = $now;
        $record['updated_at'] = $now;
        $store['items'][] = $record;
        $store['next_id'] = $record['id'] + 1;
    }

    return write_json('notices.json', $store) ? $record['id'] : null;
}

function notice_delete(int $id): bool
{
    $store = notices_store();
    $before = count($store['items']);

    $store['items'] = array_values(array_filter(
        $store['items'],
        static fn (array $n): bool => (int) ($n['id'] ?? 0) !== $id
    ));

    if (count($store['items']) === $before) {
        return false;
    }

    return write_json('notices.json', $store);
}

/** Toggle published/pinned without opening the full edit form. */
function notice_toggle(int $id, string $field): bool
{
    if (!in_array($field, ['published', 'pinned'], true)) {
        return false;
    }

    $store = notices_store();
    foreach ($store['items'] as $i => $notice) {
        if ((int) ($notice['id'] ?? 0) === $id) {
            $store['items'][$i][$field] = empty($notice[$field]);
            $store['items'][$i]['updated_at'] = date('c');

            return write_json('notices.json', $store);
        }
    }

    return false;
}

/** A notice is "new" for two weeks — used for the NEW badge on the public list. */
function notice_is_recent(array $notice, int $days = 14): bool
{
    $date = trim((string) $notice['date']);
    if ($date === '') {
        return false;
    }

    $dt = DateTimeImmutable::createFromFormat('Y-m-d', $date);
    if (!$dt) {
        return false;
    }

    return $dt >= new DateTimeImmutable("-{$days} days");
}
