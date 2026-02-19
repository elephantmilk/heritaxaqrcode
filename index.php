<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// Initialize DB
getDB();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Serve static files for built-in PHP server
if (php_sapi_name() === 'cli-server') {
    $filePath = __DIR__ . $uri;
    if ($uri !== '/' && is_file($filePath)) {
        return false;
    }
}

// Load controllers
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/QRCodeController.php';
require_once __DIR__ . '/controllers/RedirectController.php';

// --- Routes ---

// Auth
if ($uri === '/' || $uri === '/login') {
    if ($method === 'POST') {
        AuthController::login();
    } else {
        if (isLoggedIn()) redirect('/dashboard');
        AuthController::loginForm();
    }
    exit;
}

if ($uri === '/logout') {
    AuthController::logout();
    exit;
}

// Dashboard
if ($uri === '/dashboard') {
    requireAuth();
    DashboardController::index();
    exit;
}

// QR Code CRUD
if ($uri === '/qrcode/new') {
    requireAuth();
    QRCodeController::createForm();
    exit;
}

if (preg_match('#^/qrcode/edit/(\d+)$#', $uri, $m)) {
    requireAuth();
    QRCodeController::editForm((int)$m[1]);
    exit;
}

if ($uri === '/qrcode/save' && $method === 'POST') {
    requireAuth();
    QRCodeController::save();
    exit;
}

if (preg_match('#^/qrcode/delete/(\d+)$#', $uri, $m) && $method === 'POST') {
    requireAuth();
    QRCodeController::delete((int)$m[1]);
    exit;
}

if ($uri === '/api/qrcode/check-code' && $method === 'POST') {
    requireAuth();
    QRCodeController::checkCode();
    exit;
}

// Public QR code view — standalone page with rendered QR code
if (preg_match('#^/qrcode/view/([A-Z0-9]{3,20})$#', $uri, $m)) {
    QRCodeController::publicView($m[1]);
    exit;
}
if (preg_match('#^/qrcode/view/([a-zA-Z0-9]{3,20})$#', $uri, $m)) {
    QRCodeController::publicView(strtoupper($m[1]));
    exit;
}

// Short-code redirect (catch-all)
if (preg_match('#^/([A-Z0-9]{3,20})$#', $uri, $m)) {
    RedirectController::handle($m[1]);
    exit;
}

// Also allow lowercase access (redirect lookup is case-insensitive)
if (preg_match('#^/([a-zA-Z0-9]{3,20})$#', $uri, $m)) {
    RedirectController::handle(strtoupper($m[1]));
    exit;
}

// 404
http_response_code(404);
require __DIR__ . '/views/404.php';
