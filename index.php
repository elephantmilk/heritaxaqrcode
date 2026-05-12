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
require_once __DIR__ . '/controllers/BatchController.php';
require_once __DIR__ . '/controllers/PresetController.php';

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

// Batch Import
if ($uri === '/batch-import') {
    requireAuth();
    BatchController::importForm();
    exit;
}

if ($uri === '/api/batch/create' && $method === 'POST') {
    requireAuth();
    BatchController::createEntry();
    exit;
}

if ($uri === '/api/batch/save-pdf' && $method === 'POST') {
    requireAuth();
    BatchController::savePdf();
    exit;
}

// Presets
if ($uri === '/api/presets' && $method === 'GET') {
    requireAuth();
    PresetController::list();
    exit;
}

if ($uri === '/api/presets/save' && $method === 'POST') {
    requireAuth();
    PresetController::save();
    exit;
}

if (preg_match('#^/api/presets/delete/(\d+)$#', $uri, $m) && $method === 'POST') {
    requireAuth();
    PresetController::delete((int)$m[1]);
    exit;
}

// Public QR code view — standalone page with rendered QR code
// Mit Bindestrich (z. B. Title-01) oder nur A-Z0-9
if (preg_match('#^/qrcode/view/([a-zA-Z0-9\-]{2,60})$#', $uri, $m)) {
    QRCodeController::publicView($m[1]);
    exit;
}

// Short-code redirect (catch-all): Basis-URL/Title-01 oder /ABC123
if (preg_match('#^/([a-zA-Z0-9\-]{2,60})$#', $uri, $m)) {
    $code = $m[1];
    // Ohne Bindestrich: weiterhin case-insensitive (uppercase für Lookup)
    if (strpos($code, '-') === false && preg_match('/^[a-zA-Z0-9]{3,20}$/', $code)) {
        $code = strtoupper($code);
    }
    RedirectController::handle($code);
    exit;
}

// 404
http_response_code(404);
require __DIR__ . '/views/404.php';
