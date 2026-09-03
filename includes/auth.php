<?php
/**
 * auth.php — admin authentication.
 *
 * One shared admin password, stored only as a bcrypt hash in data/admin.php.
 * There is no password recovery by design: if it is lost, delete
 * data/admin.php and the first-run setup page reappears.
 *
 * Also provides the CSRF token helpers and the flash-message helpers that every
 * admin form uses.
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

/** Failed logins allowed from one IP before it is locked out. */
const LOGIN_MAX_ATTEMPTS = 5;
/** Lockout window, seconds. */
const LOGIN_LOCKOUT_SECONDS = 900;      // 15 minutes
/** Idle time before a session is dropped, seconds. */
const SESSION_IDLE_TIMEOUT = 7200;      // 2 hours

/* ---------- Session ------------------------------------------------------ */

function session_boot(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => base_url() . '/',
        'httponly' => true,          // not readable from JavaScript
        'samesite' => 'Lax',         // not sent on cross-site POSTs
        'secure'   => $https,        // HTTPS-only once the site has a certificate
    ]);

    session_name('ccss_admin');
    session_start();
}

/* ---------- Credentials store ------------------------------------------- */

function admin_config(): array
{
    return read_store('admin.php', []);
}

/** False on a fresh install — the setup page then asks for a new password. */
function admin_password_is_set(): bool
{
    $hash = (string) (admin_config()['password_hash'] ?? '');

    return $hash !== '';
}

function admin_set_password(string $password): bool
{
    $config = admin_config();
    $config['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    $config['password_set_at'] = date('c');

    return write_store('admin.php', $config);
}

/**
 * Minimum password rules. Returns an error string, or null when acceptable.
 */
function admin_password_problem(string $password, string $confirm): ?string
{
    if (mb_strlen($password) < 10) {
        return 'Please use at least 10 characters — a short phrase is easier to remember and harder to guess.';
    }
    if ($password !== $confirm) {
        return 'The two passwords do not match.';
    }
    if (preg_match('/^[0-9]+$/', $password)) {
        return 'Please do not use only numbers.';
    }
    $weak = ['password', 'admin', '1234567890', 'candidcareer', 'candidschool'];
    foreach ($weak as $bad) {
        if (stripos($password, $bad) === 0) {
            return 'That password is too easy to guess. Please choose something else.';
        }
    }

    return null;
}

/* ---------- Login rate limiting ----------------------------------------- */

function client_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
}

/**
 * Seconds remaining on a lockout, or 0 when the client may try again.
 */
function login_lockout_remaining(): int
{
    $log = read_store('login-attempts.php', []);
    $attempts = $log[client_ip()] ?? [];
    if (!is_array($attempts)) {
        return 0;
    }

    $cutoff = time() - LOGIN_LOCKOUT_SECONDS;
    $recent = array_values(array_filter($attempts, static fn ($t): bool => (int) $t > $cutoff));

    if (count($recent) < LOGIN_MAX_ATTEMPTS) {
        return 0;
    }

    return max(0, (int) min($recent) + LOGIN_LOCKOUT_SECONDS - time());
}

function login_record_failure(): void
{
    $log = read_store('login-attempts.php', []);
    $ip = client_ip();
    $cutoff = time() - LOGIN_LOCKOUT_SECONDS;

    // Prune old entries for every IP so this file cannot grow without bound.
    foreach ($log as $key => $times) {
        if (!is_array($times)) {
            unset($log[$key]);
            continue;
        }
        $kept = array_values(array_filter($times, static fn ($t): bool => (int) $t > $cutoff));
        if ($kept === []) {
            unset($log[$key]);
        } else {
            $log[$key] = $kept;
        }
    }

    $log[$ip][] = time();
    write_store('login-attempts.php', $log);
}

function login_clear_failures(): void
{
    $log = read_store('login-attempts.php', []);
    unset($log[client_ip()]);
    write_store('login-attempts.php', $log);
}

/* ---------- Login / logout ---------------------------------------------- */

/**
 * Verify a password and start an admin session.
 *
 * @return string|null Error message for the visitor, or null on success.
 */
function admin_login(string $password): ?string
{
    session_boot();

    $wait = login_lockout_remaining();
    if ($wait > 0) {
        return 'Too many failed attempts. Please try again in ' . (int) ceil($wait / 60) . ' minute(s).';
    }

    $hash = (string) (admin_config()['password_hash'] ?? '');
    if ($hash === '') {
        return 'No admin password has been set yet.';
    }

    if (!password_verify($password, $hash)) {
        login_record_failure();
        $left = LOGIN_MAX_ATTEMPTS - count(read_store('login-attempts.php', [])[client_ip()] ?? []);

        return 'Incorrect password.' . ($left > 0 && $left <= 2 ? ' ' . $left . ' attempt(s) left before a temporary lockout.' : '');
    }

    login_clear_failures();

    // New session id on privilege change, to shut down session fixation.
    session_regenerate_id(true);
    $_SESSION['admin'] = true;
    $_SESSION['login_at'] = time();
    $_SESSION['last_seen'] = time();
    $_SESSION['ua_hash'] = hash('sha256', (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));

    // Re-hash if PHP's default cost or algorithm has moved on.
    if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
        admin_set_password($password);
    }

    return null;
}

function admin_logout(): void
{
    session_boot();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $p['path'],
            'domain'   => $p['domain'] ?? '',
            'secure'   => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => $p['samesite'] ?? 'Lax',
        ]);
    }

    session_destroy();
}

function admin_is_logged_in(): bool
{
    session_boot();

    if (empty($_SESSION['admin'])) {
        return false;
    }

    // Idle timeout.
    if (time() - (int) ($_SESSION['last_seen'] ?? 0) > SESSION_IDLE_TIMEOUT) {
        admin_logout();

        return false;
    }

    // A stolen cookie replayed from another browser is rejected.
    $ua = hash('sha256', (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    if (!hash_equals((string) ($_SESSION['ua_hash'] ?? ''), $ua)) {
        admin_logout();

        return false;
    }

    $_SESSION['last_seen'] = time();

    return true;
}

/** Gate for every admin page. Redirects instead of returning. */
function require_admin(): void
{
    session_boot();

    if (!admin_password_is_set()) {
        redirect(url('admin/setup.php'));
    }
    if (!admin_is_logged_in()) {
        $target = $_SERVER['REQUEST_URI'] ?? url('admin/');
        redirect(url('admin/login.php?next=' . urlencode($target)));
    }
}

function redirect(string $to): never
{
    header('Location: ' . $to, true, 302);
    exit;
}

/* ---------- CSRF -------------------------------------------------------- */

function csrf_token(): string
{
    session_boot();

    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '" />';
}

function csrf_valid(?string $token): bool
{
    session_boot();

    return is_string($token)
        && !empty($_SESSION['csrf'])
        && hash_equals((string) $_SESSION['csrf'], $token);
}

/**
 * Reject any POST without a valid token.
 *
 * Called at the top of every admin form handler, so a malicious page cannot
 * make a logged-in admin's browser submit changes.
 */
function require_csrf(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    if (!csrf_valid($_POST['_token'] ?? null)) {
        http_response_code(419);
        exit('Your session expired while this page was open. Please go back, reload the page and try again.');
    }
}

/* ---------- Flash messages ---------------------------------------------- */

function flash(string $message, string $kind = 'success'): void
{
    session_boot();
    $_SESSION['flash'][] = ['message' => $message, 'kind' => $kind];
}

/** Read and clear queued messages. */
function flash_take(): array
{
    session_boot();
    $queue = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);

    return is_array($queue) ? $queue : [];
}
