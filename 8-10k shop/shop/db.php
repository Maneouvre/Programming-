<?php
require __DIR__ . '/config.php';
session_start();

define('DATA_FILE', __DIR__ . '/data/products.json');
define('SLIDES_FILE', __DIR__ . '/data/slides.json');

function db_read() {
    $empty = ['next' => 1, 'items' => []];
    if (!file_exists(DATA_FILE)) return $empty;
    $d = json_decode((string)file_get_contents(DATA_FILE), true);
    return (is_array($d) && isset($d['items'])) ? $d : $empty;
}
function db_write($d) {
    $fp = @fopen(DATA_FILE, 'c');
    if (!$fp) return false;
    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    fwrite($fp, json_encode($d, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return true;
}

function slideshow_read() {
    $empty = ['next' => 1, 'items' => []];
    if (!file_exists(SLIDES_FILE)) return $empty;
    $data = json_decode((string)file_get_contents(SLIDES_FILE), true);
    return (is_array($data) && isset($data['items'])) ? $data : $empty;
}
function slideshow_write($data) {
    $fp = @fopen(SLIDES_FILE, 'c');
    if (!$fp) return false;
    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    fwrite($fp, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return true;
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function cut($s, $n) { return function_exists('mb_substr') ? mb_substr($s, 0, $n) : substr($s, 0, $n); }

function csrf() {
    if (empty($_SESSION['t'])) $_SESSION['t'] = bin2hex(random_bytes(16));
    return $_SESSION['t'];
}
function csrf_field() { return '<input type="hidden" name="t" value="' . csrf() . '">'; }
function csrf_check() {
    if (!hash_equals($_SESSION['t'] ?? '', $_POST['t'] ?? '')) {
        http_response_code(400);
        exit('Session expired. Go back, refresh and try again.');
    }
}
function require_admin() {
    if (empty($_SESSION['admin'])) { header('Location: login.php'); exit; }
}

function delete_image($name) {
    if ($name && preg_match('/^[a-f0-9]+\.(jpg|png|webp)$/', $name)) @unlink(__DIR__ . '/admin/uploads/' . $name);
}

function delete_slideshow_image($name) {
    if ($name && preg_match('/^[a-f0-9]+\.(jpg|png|webp)$/', $name)) {
        @unlink(__DIR__ . '/admin/slideshow_uploads/' . $name);
    }
}

function save_slideshow_image($file) {
    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) return [null, 'Choose an image for the announcement.'];
    if ($file['error'] !== UPLOAD_ERR_OK) return [null, 'Upload failed. Try a smaller photo.'];
    if ($file['size'] > 8 * 1024 * 1024) return [null, 'Photo is too big (max 8MB).'];

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($types[$mime])) return [null, 'Only JPG, PNG or WEBP photos are allowed.'];

    $dir = __DIR__ . '/admin/slideshow_uploads/';
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) return [null, 'Could not create the slideshow image folder.'];

    $name = bin2hex(random_bytes(8)) . '.' . $types[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . $name)) return [null, 'Could not save the slideshow photo.'];
    return [$name, null];
}

// Returns [filename|null, error|null]. Resizes big phone photos so the site stays fast.
function save_image($f) {
    if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) return [null, null];
    if ($f['error'] !== UPLOAD_ERR_OK) return [null, 'Upload failed. Try a smaller photo.'];
    if ($f['size'] > 8 * 1024 * 1024) return [null, 'Photo is too big (max 8MB).'];

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($types[$mime])) return [null, 'Only JPG, PNG or WEBP photos are allowed.'];

    $dir = __DIR__ . '/admin/uploads/';
    $base = bin2hex(random_bytes(8));

    if (function_exists('imagecreatefromjpeg') && function_exists('imagejpeg')) {
        $src = false;
        if ($mime === 'image/jpeg') $src = @imagecreatefromjpeg($f['tmp_name']);
        elseif ($mime === 'image/png') $src = @imagecreatefrompng($f['tmp_name']);
        elseif (function_exists('imagecreatefromwebp')) $src = @imagecreatefromwebp($f['tmp_name']);

        if ($src) {
            if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
                $ex = @exif_read_data($f['tmp_name']);
                $o = $ex['Orientation'] ?? 1;
                if ($o == 3) $src = imagerotate($src, 180, 0);
                elseif ($o == 6) $src = imagerotate($src, -90, 0);
                elseif ($o == 8) $src = imagerotate($src, 90, 0);
            }
            $w = imagesx($src); $h = imagesy($src); $max = 900;
            $r = min(1, $max / $w, $max / $h);
            $nw = max(1, (int)($w * $r)); $nh = max(1, (int)($h * $r));
            $dst = imagecreatetruecolor($nw, $nh);
            imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagejpeg($dst, $dir . $base . '.jpg', 82);
            return [$base . '.jpg', null];
        }
    }
    $name = $base . '.' . $types[$mime];
    if (!move_uploaded_file($f['tmp_name'], $dir . $name)) return [null, 'Could not save the photo.'];
    return [$name, null];
}
