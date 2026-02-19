<?php
class DashboardController {
    public static function index(): void {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM qr_codes WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$_SESSION['user_id']]);
        $qrcodes = $stmt->fetchAll();
        $success = flash('success');
        require __DIR__ . '/../views/dashboard.php';
    }
}
