<?php
/**
 * bootstrap.php — shared foundation for the public site and the admin panel.
 *
 * Loaded first by every entry point. Defines paths, JSON read/write helpers
 * (atomic + locked, so a half-written file can never be served), and the output
 * escaping helpers used everywhere.
 *
 * No database: all content lives in data/*.json.
 */

declare(strict_types=1);

if (defined('CCSS_BOOTSTRAPPED')) {
    return;
}
define('CCSS_BOOTSTRAPPED', true);

/* ---------- Requirements ------------------------------------------------- */

/*
 * Checked up front so a host with the wrong PHP version or a missing extension
 * shows an explanatory message instead of a blank white page. All three are
 * standard on cPanel hosting; PHP version is usually selectable in
 * "MultiPHP Manager" and extensions in "Select PHP Version → Extensions".
 */
(static function (): void {
    $problems = [];

    if (PHP_VERSION_ID < 80100) {
        $problems[] = 'This site needs PHP 8.1 or newer. This server is running PHP ' . PHP_VERSION
            . '. In cPanel, change it under "MultiPHP Manager".';
    }
    foreach (['mbstring' => 'mbstring', 'fileinfo' => 'fileinfo'] as $ext => $label) {
        if (!extension_loaded($ext)) {
            $problems[] = 'The PHP "' . $label . '" extension is not enabled. In cPanel, enable it under '
                . '"Select PHP Version → Extensions".';
        }
    }

    if ($problems === []) {
        return;
    }

    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><meta charset="utf-8"><title>Server setup needed</title>'
        . '<div style="font:16px/1.6 system-ui,sans-serif;max-width:44em;margin:12vh auto;padding:0 1.5em;color:#17211f">'
        . '<h1 style="font-size:1.4em">This website needs a small server change</h1><ul>';
    foreach ($problems as $problem) {
        echo '<li>' . htmlspecialchars($problem, ENT_QUOTES, 'UTF-8') . '</li>';
    }
    echo '</ul><p style="color:#5f6b68">Once that is done, reload this page.</p></div>';
    exit;
})();

/* ---------- Paths -------------------------------------------------------- */

define('ROOT_DIR', dirname(__DIR__));
define('DATA_DIR', ROOT_DIR . '/data');
define('INCLUDES_DIR', ROOT_DIR . '/includes');
define('GALLERY_DIR', ROOT_DIR . '/images/gallery');

/** Web path of the site root, so the same code works in a subfolder. */
function base_url(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }

    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = str_replace('\\', '/', dirname($script));

    // An admin page lives one level deeper than the site root.
    if (preg_match('#/admin$#', $dir)) {
        $dir = dirname($dir);
    }
    $base = ($dir === '/' || $dir === '.') ? '' : rtrim($dir, '/');

    return $base;
}

/** Build a URL relative to the site root. */
function url(string $path = ''): string
{
    return base_url() . '/' . ltrim($path, '/');
}

/* ---------- Error handling ---------------------------------------------- */

/*
 * Keep PHP notices out of the visitors' faces. Errors are logged instead.
 * While setting the site up, temporarily set display_errors to '1' here.
 */
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

/* ---------- Escaping ----------------------------------------------------- */

/** Escape for HTML text and attributes. Use on every dynamic value. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Render a plain-text block as paragraphs.
 *
 * Content is stored as plain text (blank line = new paragraph) and escaped on
 * the way out, so an editor can never inject markup or script into the page.
 */
function paragraphs(?string $text, string $class = ''): string
{
    $text = trim((string) $text);
    if ($text === '') {
        return '';
    }

    $attr = $class !== '' ? ' class="' . e($class) . '"' : '';
    $blocks = preg_split('/\R{2,}/', $text) ?: [];
    $html = '';

    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '') {
            continue;
        }
        // Single newlines inside a paragraph become <br>.
        $html .= '<p' . $attr . '>' . nl2br(e($block), false) . "</p>\n";
    }

    return $html;
}

/** Split a textarea of one-per-line values into a clean array. */
function lines_to_array(?string $text): array
{
    $lines = preg_split('/\R/', (string) $text) ?: [];
    $out = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line !== '') {
            $out[] = $line;
        }
    }

    return $out;
}

/* ---------- JSON storage ------------------------------------------------- */

/**
 * Read a JSON file from data/.
 *
 * Returns $fallback when the file is missing or unreadable, so a fresh install
 * with no data files still renders.
 */
function read_json(string $name, array $fallback = []): array
{
    $path = DATA_DIR . '/' . basename($name);
    if (!is_readable($path)) {
        return $fallback;
    }

    $raw = file_get_contents($path);
    if ($raw === false || trim($raw) === '') {
        return $fallback;
    }

    try {
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        error_log('Corrupt JSON in ' . $path . ': ' . $e->getMessage());
        return $fallback;
    }

    return is_array($data) ? $data : $fallback;
}

/**
 * Write a JSON file atomically.
 *
 * Writes to a temp file in the same directory then renames it over the target,
 * so a reader never sees a partial file even if two admins save at once.
 */
function write_json(string $name, array $data): bool
{
    if (!is_dir(DATA_DIR) && !@mkdir(DATA_DIR, 0755, true)) {
        error_log('Cannot create data directory: ' . DATA_DIR);
        return false;
    }

    $path = DATA_DIR . '/' . basename($name);

    try {
        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
    } catch (JsonException $e) {
        error_log('Cannot encode JSON for ' . $path . ': ' . $e->getMessage());
        return false;
    }

    $tmp = $path . '.' . bin2hex(random_bytes(6)) . '.tmp';
    if (file_put_contents($tmp, $json . "\n", LOCK_EX) === false) {
        error_log('Cannot write ' . $tmp . ' — check folder permissions (needs 755 and a writable owner).');
        @unlink($tmp);
        return false;
    }

    @chmod($tmp, 0644);

    if (!@rename($tmp, $path)) {
        error_log('Cannot replace ' . $path);
        @unlink($tmp);
        return false;
    }

    return true;
}

/* ---------- Sensitive storage (PHP-wrapped, not JSON) -------------------- */

/*
 * The admin password hash must never be readable over HTTP. data/.htaccess
 * denies it on Apache, but a misconfigured nginx or lighttpd server would
 * happily serve a .json file as plain text. So anything sensitive is stored as
 * a PHP file returning an array: request it directly and the server executes
 * it, printing nothing, on every host — no server configuration required.
 */

/** Read a PHP-wrapped data store. */
function read_store(string $name, array $fallback = []): array
{
    $path = DATA_DIR . '/' . basename($name);
    if (!is_readable($path)) {
        return $fallback;
    }

    $data = @include $path;

    return is_array($data) ? $data : $fallback;
}

/** Write a PHP-wrapped data store atomically. */
function write_store(string $name, array $data): bool
{
    if (!is_dir(DATA_DIR) && !@mkdir(DATA_DIR, 0755, true)) {
        error_log('Cannot create data directory: ' . DATA_DIR);
        return false;
    }

    $path = DATA_DIR . '/' . basename($name);

    $code = "<?php\n"
        . "// Generated by the admin panel — do not edit by hand.\n"
        . "// Stored as PHP rather than JSON so that a direct web request to this\n"
        . "// file executes it and reveals nothing, whatever the web server config.\n"
        . 'return ' . var_export($data, true) . ";\n";

    $tmp = $path . '.' . bin2hex(random_bytes(6)) . '.tmp';
    if (file_put_contents($tmp, $code, LOCK_EX) === false) {
        error_log('Cannot write ' . $tmp . ' — check folder permissions.');
        @unlink($tmp);
        return false;
    }

    @chmod($tmp, 0644);

    if (!@rename($tmp, $path)) {
        error_log('Cannot replace ' . $path);
        @unlink($tmp);
        return false;
    }

    // Without this, opcache can keep serving the previous contents of the file
    // after a password change.
    if (function_exists('opcache_invalidate')) {
        @opcache_invalidate($path, true);
    }

    return true;
}

/** Fetch a nested value with a dotted key, e.g. get_in($c, 'contact.phone'). */
function get_in(array $array, string $path, mixed $default = ''): mixed
{
    $node = $array;
    foreach (explode('.', $path) as $key) {
        if (!is_array($node) || !array_key_exists($key, $node)) {
            return $default;
        }
        $node = $node[$key];
    }

    return $node === null ? $default : $node;
}

/* ---------- Small formatting helpers ------------------------------------ */

/** Strip everything but digits and a leading + so tel: links always work. */
function tel_href(?string $phone): string
{
    $phone = (string) $phone;
    $plus = str_starts_with(trim($phone), '+') ? '+' : '';
    $digits = preg_replace('/\D+/', '', $phone) ?? '';

    return $digits === '' ? '' : 'tel:' . $plus . $digits;
}

/** Format a stored Y-m-d date for display. Falls back to the raw string. */
function format_date(?string $date, string $format = 'j F Y'): string
{
    $date = trim((string) $date);
    if ($date === '') {
        return '';
    }

    $dt = DateTimeImmutable::createFromFormat('Y-m-d', $date);

    return $dt ? $dt->format($format) : $date;
}

/** Shorten plain text to a rough character budget on a word boundary. */
function excerpt(?string $text, int $limit = 180): string
{
    $text = trim(preg_replace('/\s+/', ' ', (string) $text) ?? '');
    if ($text === '' || mb_strlen($text) <= $limit) {
        return $text;
    }

    $cut = mb_substr($text, 0, $limit);
    $space = mb_strrpos($cut, ' ');

    return rtrim($space ? mb_substr($cut, 0, $space) : $cut, " ,.;:—-") . '…';
}
