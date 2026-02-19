<?php
if (!defined('APP_NAME')) { require_once __DIR__ . '/../config.php'; require_once __DIR__ . '/../db.php'; require_once __DIR__ . '/../auth.php'; }
$pageTitle = '404 — ' . APP_NAME;
require __DIR__ . '/layout.php';
?>

<div class="error-page">
    <h1>404</h1>
    <p>Dieser QR-Code existiert nicht oder wurde deaktiviert.</p>
    <a href="/" class="btn btn-primary">Zur Startseite</a>
</div>

<?php require __DIR__ . '/layout_end.php'; ?>
