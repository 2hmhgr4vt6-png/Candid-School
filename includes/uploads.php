<?php
/**
 * uploads.php — validated file uploads for gallery photos and notice attachments.
 *
 * Everything an admin uploads is treated as hostile:
 *   - extension AND real content type must both be on the whitelist
 *   - images must actually decode as images (getimagesize)
 *   - the stored filename is generated here, never taken from the upload
 *   - the destination folders ship with an .htaccess that disables script
 *     execution, so even a crafted file cannot be run
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

const UPLOAD_MAX_BYTES = 6 * 1024 * 1024; // 6 MB

const UPLOAD_IMAGE_TYPES = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];

const UPLOAD_DOC_TYPES = [
    'application/pdf' => 'pdf',
];

/**
 * Human-readable message for a PHP upload error code.
 */
function upload_error_message(int $code): string
{
    return match ($code) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'That file is larger than the server allows. Please compress it and try again.',
        UPLOAD_ERR_PARTIAL    => 'The upload was interrupted. Please try again.',
        UPLOAD_ERR_NO_FILE    => 'No file was selected.',
        UPLOAD_ERR_NO_TMP_DIR,
        UPLOAD_ERR_CANT_WRITE => 'The server could not save the file. Please contact your host.',
        UPLOAD_ERR_EXTENSION  => 'The upload was blocked by the server.',
        default               => 'The file could not be uploaded.',
    };
}

/**
 * Validate and move one uploaded file.
 *
 * @param array  $file    One entry from $_FILES.
 * @param string $destDir Absolute destination directory.
 * @param array  $allowed MIME type => extension map.
 * @param string $prefix  Filename prefix, e.g. 'gallery'.
 * @return array{ok:bool,file?:string,error?:string}
 */
function upload_store(array $file, string $destDir, array $allowed, string $prefix): array
{
    $code = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($code !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => upload_error_message($code)];
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return ['ok' => false, 'error' => 'That upload could not be verified. Please try again.'];
    }

    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0) {
        return ['ok' => false, 'error' => 'That file is empty.'];
    }
    if ($size > UPLOAD_MAX_BYTES) {
        return ['ok' => false, 'error' => 'Please keep files under ' . (int) (UPLOAD_MAX_BYTES / 1048576) . ' MB. Compress the photo at squoosh.app and try again.'];
    }

    // Trust the file's own bytes, not the browser-supplied type.
    $mime = mime_content_type($tmp) ?: '';
    if (!isset($allowed[$mime])) {
        return ['ok' => false, 'error' => 'That file type is not allowed here. Accepted: ' . implode(', ', array_unique(array_values($allowed))) . '.'];
    }
    $ext = $allowed[$mime];

    // The declared extension must agree with the real content type.
    $claimed = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
    $sameFamily = [$ext, $ext === 'jpg' ? 'jpeg' : $ext];
    if ($claimed !== '' && !in_array($claimed, $sameFamily, true)) {
        return ['ok' => false, 'error' => 'That file\'s name and its actual contents do not match. Please re-save it and try again.'];
    }

    // Images must genuinely decode as images.
    if (str_starts_with($mime, 'image/')) {
        $info = @getimagesize($tmp);
        if ($info === false || empty($info[0]) || empty($info[1])) {
            return ['ok' => false, 'error' => 'That file is not a readable image.'];
        }
    }

    if (!is_dir($destDir) && !@mkdir($destDir, 0755, true)) {
        return ['ok' => false, 'error' => 'The upload folder does not exist and could not be created.'];
    }
    if (!is_writable($destDir)) {
        return ['ok' => false, 'error' => 'The upload folder is not writable. Set its permissions to 755 in your hosting file manager.'];
    }

    upload_protect_dir($destDir);

    // Generated name: never any part of the user-supplied filename.
    $name = sprintf('%s-%s-%s.%s', $prefix, date('Ymd'), bin2hex(random_bytes(4)), $ext);
    $target = $destDir . '/' . $name;

    if (!move_uploaded_file($tmp, $target)) {
        return ['ok' => false, 'error' => 'The file could not be saved to the server.'];
    }

    @chmod($target, 0644);

    return ['ok' => true, 'file' => $name];
}

/** Drop an .htaccess that stops anything in an upload folder being executed. */
function upload_protect_dir(string $dir): void
{
    $path = $dir . '/.htaccess';
    if (file_exists($path)) {
        return;
    }

    $rules = <<<'HTACCESS'
# Uploaded files are served as downloads only — never executed.
php_flag engine off
<IfModule mod_rewrite.c>
    RewriteEngine Off
</IfModule>
<FilesMatch "\.(php|phtml|php[0-9]|phar|pl|py|cgi|sh|htaccess)$">
    Require all denied
</FilesMatch>
HTACCESS;

    @file_put_contents($path, $rules . "\n");
}

/** Delete a previously uploaded file, guarding against path traversal. */
function upload_delete(string $dir, string $name): bool
{
    $name = basename(trim($name));
    if ($name === '' || $name === '.' || $name === '..' || str_starts_with($name, '.')) {
        return false;
    }

    $path = $dir . '/' . $name;
    $real = realpath($path);
    $realDir = realpath($dir);

    // Must resolve to a plain file genuinely inside $dir.
    if ($real === false || $realDir === false || !str_starts_with($real, $realDir . DIRECTORY_SEPARATOR) || !is_file($real)) {
        return false;
    }

    return @unlink($real);
}

/** Storage location for notice attachments. */
function files_dir(): string
{
    return ROOT_DIR . '/files';
}

function gallery_upload(array $file): array
{
    return upload_store($file, GALLERY_DIR, UPLOAD_IMAGE_TYPES, 'gallery');
}

function attachment_upload(array $file): array
{
    return upload_store($file, files_dir(), UPLOAD_DOC_TYPES + UPLOAD_IMAGE_TYPES, 'notice');
}
