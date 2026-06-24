<?php
declare(strict_types=1);

const UPLOAD_DIR = __DIR__ . '/../img/uploads/';
const UPLOAD_URL = 'img/uploads/';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function categories(): array
{
    return [
        'europa' => 'Europa',
        'teknautas' => 'Teknautas',
    ];
}

function category_label(string $slug): string
{
    return categories()[$slug] ?? ucfirst($slug);
}

function safe_category(string $slug): ?string
{
    return array_key_exists($slug, categories()) ? $slug : null;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        exit('Neispravan sigurnosni token. Osvježite stranicu i pokušajte ponovno.');
    }
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function is_admin(): bool
{
    return is_logged_in() && (int)($_SESSION['level'] ?? 0) === 1;
}

function image_url(string $filename): string
{
    if ($filename === '') {
        return 'img/antarktika.jpg';
    }

    if (str_starts_with($filename, 'seed:')) {
        return 'img/' . substr($filename, 5);
    }

    return UPLOAD_URL . rawurlencode($filename);
}

function upload_image(array $file, ?string $existing = null): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        if ($existing !== null && $existing !== '') {
            return $existing;
        }
        throw new RuntimeException('Odaberite sliku.');
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Slika nije uspješno prenesena.');
    }

    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('Slika smije imati najviše 5 MB.');
    }

    $tmp = (string)($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        throw new RuntimeException('Neispravna prenesena datoteka.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed[$mime]) || getimagesize($tmp) === false) {
        throw new RuntimeException('Dozvoljene su samo JPG, PNG, GIF i WEBP slike.');
    }

    if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0755, true) && !is_dir(UPLOAD_DIR)) {
        throw new RuntimeException('Nije moguće pripremiti mapu za slike.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($tmp, UPLOAD_DIR . $filename)) {
        throw new RuntimeException('Slika se nije mogla spremiti.');
    }

    return $filename;
}

function delete_uploaded_image(string $filename): void
{
    if ($filename === '' || str_starts_with($filename, 'seed:')) {
        return;
    }

    $path = UPLOAD_DIR . basename($filename);
    if (is_file($path)) {
        @unlink($path);
    }
}

function article_excerpt(string $text, int $limit = 135): string
{
    $plain = trim(strip_tags($text));
    if (mb_strlen($plain) <= $limit) {
        return $plain;
    }
    return rtrim(mb_substr($plain, 0, $limit - 1)) . '…';
}

function format_date(string $date): string
{
    $timestamp = strtotime($date);
    return $timestamp ? date('d/m/Y', $timestamp) : $date;
}

function redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}
