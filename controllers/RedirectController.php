<?php
class RedirectController {
    public static function handle(string $code): void {
        $db = getDB();
        $stmt = $db->prepare('SELECT id, target_url, is_active FROM qr_codes WHERE short_code = ?');
        $stmt->execute([$code]);
        $qr = $stmt->fetch();

        if (!$qr || !$qr['is_active']) {
            http_response_code(404);
            require __DIR__ . '/../views/404.php';
            return;
        }

        // Increment scan count
        $db->prepare('UPDATE qr_codes SET scan_count = scan_count + 1 WHERE id = ?')
           ->execute([$qr['id']]);

        header('Location: ' . $qr['target_url'], true, 302);
        exit;
    }
}
