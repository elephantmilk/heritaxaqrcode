<?php
session_start();

define('APP_NAME', 'HeritaxaQR');
define('DB_PATH', __DIR__ . '/data/heritaxaqr.db');
define('UPLOAD_DIR', __DIR__ . '/assets/uploads/');
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2MB

// Helper: escape output
function e(string $val): string {
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}

// Helper: CSRF token
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf(): bool {
    return isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token']);
}

// Helper: redirect
function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
}

// Helper: flash messages
function flash(string $key, ?string $message = null) {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
    } else {
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
}
