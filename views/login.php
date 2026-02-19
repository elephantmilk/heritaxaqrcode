<?php $pageTitle = 'Login — ' . APP_NAME; require __DIR__ . '/layout.php'; ?>

<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <svg class="login-logo" viewBox="0 0 32 32" width="48" height="48" fill="none">
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
            <h1>QRCode<strong>Man</strong></h1>
            <p class="text-muted">Melde dich an, um deine QR-Codes zu verwalten.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/login">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="username">Benutzername</label>
                <input type="text" id="username" name="username" required autofocus autocomplete="username" placeholder="admin">
            </div>
            <div class="form-group">
                <label for="password">Passwort</label>
                <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••">
            </div>
            <button type="submit" class="btn btn-primary btn-full">Anmelden</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/layout_end.php'; ?>
