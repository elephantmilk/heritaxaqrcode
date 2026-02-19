<?php
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function requireAuth(): void {
    if (!isLoggedIn()) {
        redirect('/login');
    }
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare('SELECT id, username FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}
