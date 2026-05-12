<?php
class DashboardController {
    private const PER_PAGE = 36;

    public static function index(): void {
        $db = getDB();
        $userId = $_SESSION['user_id'];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $stmt = $db->prepare('SELECT COUNT(*) FROM qr_codes WHERE user_id = ?');
        $stmt->execute([$userId]);
        $totalCount = (int) $stmt->fetchColumn();
        $totalPages = $totalCount > 0 ? (int) ceil($totalCount / self::PER_PAGE) : 1;
        $page = min($page, max(1, $totalPages));

        $stmt = $db->prepare('SELECT * FROM qr_codes WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?');
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, self::PER_PAGE, PDO::PARAM_INT);
        $stmt->bindValue(3, ($page - 1) * self::PER_PAGE, PDO::PARAM_INT);
        $stmt->execute();
        $qrcodes = $stmt->fetchAll();
        $perPage = self::PER_PAGE;

        $success = flash('success');
        require __DIR__ . '/../views/dashboard.php';
    }
}
