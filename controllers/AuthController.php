<?php
class AuthController {
    public static function loginForm(): void {
        $error = flash('error');
        require __DIR__ . '/../views/login.php';
    }

    public static function login(): void {
        if (!verify_csrf()) {
            flash('error', 'Ungültige Anfrage.');
            redirect('/login');
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            flash('error', 'Bitte alle Felder ausfüllen.');
            redirect('/login');
        }

        $db = getDB();
        $stmt = $db->prepare('SELECT id, password_hash FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            flash('error', 'Ungültige Anmeldedaten.');
            redirect('/login');
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        redirect('/dashboard');
    }

    public static function logout(): void {
        $_SESSION = [];
        session_destroy();
        redirect('/login');
    }
}
