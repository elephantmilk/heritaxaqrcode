<?php if (!defined('APP_NAME')) exit; ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php $cssPath = __DIR__ . '/../assets/css/style.css'; $cssVer = file_exists($cssPath) ? filemtime($cssPath) : 0; ?>
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= $cssVer ?>">
    <?php if (!empty($loadQRLib)): ?>
    <script src="https://unpkg.com/qr-code-styling@1.6.0-rc.1/lib/qr-code-styling.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/svg2pdf.js@2/dist/svg2pdf.umd.min.js"></script>
    <?php endif; ?>
</head>
<body>
    <header class="header">
        <div class="container header-inner">
            <a href="<?= isLoggedIn() ? '/dashboard' : '/' ?>" class="logo">
                <svg class="logo-icon" viewBox="0 0 32 32" width="32" height="32" fill="none">
                    <rect x="1" y="1" width="12" height="12" rx="2" stroke="currentColor" stroke-width="2"/>
                    <rect x="19" y="1" width="12" height="12" rx="2" stroke="currentColor" stroke-width="2"/>
                    <rect x="1" y="19" width="12" height="12" rx="2" stroke="currentColor" stroke-width="2"/>
                    <rect x="4" y="4" width="6" height="6" rx="1" fill="currentColor"/>
                    <rect x="22" y="4" width="6" height="6" rx="1" fill="currentColor"/>
                    <rect x="4" y="22" width="6" height="6" rx="1" fill="currentColor"/>
                    <rect x="19" y="19" width="4" height="4" rx="1" fill="currentColor"/>
                    <rect x="25" y="19" width="6" height="4" rx="1" fill="currentColor"/>
                    <rect x="19" y="25" width="4" height="6" rx="1" fill="currentColor"/>
                    <rect x="27" y="27" width="4" height="4" rx="1" fill="currentColor"/>
                </svg>
                <span class="logo-text">HeritaxaQR</span>
            </a>
            <?php if (isLoggedIn()): ?>
            <nav class="nav">
                <a href="/dashboard" class="nav-link">Dashboard</a>
                <a href="/batch-import" class="nav-link">Batch Import</a>
                <a href="/qrcode/new" class="nav-link btn btn-sm btn-primary">+ Neuer QR-Code</a>
                <a href="/logout" class="nav-link nav-logout">Logout</a>
            </nav>
            <?php endif; ?>
        </div>
    </header>
    <main class="main">
        <div class="container">
