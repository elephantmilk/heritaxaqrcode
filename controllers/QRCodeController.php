<?php
class QRCodeController {

    public static function createForm(): void {
        $qr = null;
        require __DIR__ . '/../views/qrcode_form.php';
    }

    public static function editForm(int $id): void {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM qr_codes WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $_SESSION['user_id']]);
        $qr = $stmt->fetch();
        if (!$qr) {
            flash('error', 'QR-Code nicht gefunden.');
            redirect('/dashboard');
        }
        require __DIR__ . '/../views/qrcode_form.php';
    }

    public static function save(): void {
        if (!verify_csrf()) {
            flash('error', 'Ungültige Anfrage.');
            redirect('/dashboard');
        }

        $db = getDB();
        $id = (int)($_POST['id'] ?? 0);
        $targetUrl = trim($_POST['target_url'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $shortCode = strtoupper(trim($_POST['short_code'] ?? ''));
        $dotStyle = $_POST['dot_style'] ?? 'square';
        $dotColor = $_POST['dot_color'] ?? '#ffffff';
        $bgColor = $_POST['bg_color'] ?? '#0a0a0f';
        $cornerSquareStyle = $_POST['corner_square_style'] ?? 'square';
        $cornerSquareColor = $_POST['corner_square_color'] ?? '#ffffff';
        $cornerDotStyle = $_POST['corner_dot_style'] ?? 'square';
        $cornerDotColor = $_POST['corner_dot_color'] ?? '#ffffff';
        $logoSize = max(0.1, min(0.5, (float)($_POST['logo_size'] ?? 0.4)));
        $dotGradientEnabled = !empty($_POST['dot_gradient_enabled']) ? 1 : 0;
        $dotGradientType = ($_POST['dot_gradient_type'] ?? 'linear') === 'radial' ? 'radial' : 'linear';
        $dotGradientRotation = (float)($_POST['dot_gradient_rotation'] ?? 0);
        $dotGradientColor1 = $_POST['dot_gradient_color1'] ?? '#000000';
        $dotGradientColor2 = $_POST['dot_gradient_color2'] ?? '#888888';

        // Validate URL
        if ($targetUrl === '' || !filter_var($targetUrl, FILTER_VALIDATE_URL)) {
            flash('error', 'Bitte eine gültige URL eingeben.');
            redirect($id ? "/qrcode/edit/$id" : '/qrcode/new');
        }

        // Generate or validate short code
        if ($shortCode === '') {
            $shortCode = self::generateCode();
        } else {
            if (!preg_match('/^[A-Z0-9]{3,20}$/', $shortCode)) {
                flash('error', 'Code darf nur Großbuchstaben und Ziffern enthalten (3-20 Zeichen).');
                redirect($id ? "/qrcode/edit/$id" : '/qrcode/new');
            }
            // Check uniqueness
            $stmt = $db->prepare('SELECT id FROM qr_codes WHERE short_code = ? AND id != ?');
            $stmt->execute([$shortCode, $id]);
            if ($stmt->fetch()) {
                flash('error', 'Dieser Code ist bereits vergeben.');
                redirect($id ? "/qrcode/edit/$id" : '/qrcode/new');
            }
        }

        // Handle logo upload — store as base64 data URL for self-contained SVG export
        $logoData = $_POST['existing_logo_data'] ?? null;
        if (!empty($_FILES['logo_file']['tmp_name'])) {
            $file = $_FILES['logo_file'];
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowed)) {
                flash('error', 'Ungültiges Bildformat. Erlaubt: JPG, PNG, GIF, SVG, WebP.');
                redirect($id ? "/qrcode/edit/$id" : '/qrcode/new');
            }
            if ($file['size'] > MAX_UPLOAD_SIZE) {
                flash('error', 'Logo darf maximal 2MB groß sein.');
                redirect($id ? "/qrcode/edit/$id" : '/qrcode/new');
            }

            $rawData = file_get_contents($file['tmp_name']);
            $logoData = 'data:' . $mime . ';base64,' . base64_encode($rawData);
        }

        // Remove logo if requested
        if (!empty($_POST['remove_logo'])) {
            $logoData = null;
        }

        if ($id > 0) {
            // Update
            $stmt = $db->prepare('UPDATE qr_codes SET
                target_url = ?, title = ?, description = ?, short_code = ?,
                dot_style = ?, dot_color = ?, bg_color = ?,
                corner_square_style = ?, corner_square_color = ?,
                corner_dot_style = ?, corner_dot_color = ?,
                logo_data = ?, logo_size = ?,
                dot_gradient_enabled = ?, dot_gradient_type = ?, dot_gradient_rotation = ?,
                dot_gradient_color1 = ?, dot_gradient_color2 = ?,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND user_id = ?');
            $stmt->execute([
                $targetUrl, $title, $description, $shortCode,
                $dotStyle, $dotColor, $bgColor,
                $cornerSquareStyle, $cornerSquareColor,
                $cornerDotStyle, $cornerDotColor,
                $logoData, $logoSize,
                $dotGradientEnabled, $dotGradientType, $dotGradientRotation,
                $dotGradientColor1, $dotGradientColor2,
                $id, $_SESSION['user_id']
            ]);
            flash('success', 'QR-Code aktualisiert.');
        } else {
            // Insert
            $stmt = $db->prepare('INSERT INTO qr_codes
                (user_id, short_code, target_url, title, description, dot_style, dot_color, bg_color,
                 corner_square_style, corner_square_color, corner_dot_style, corner_dot_color,
                 logo_data, logo_size,
                 dot_gradient_enabled, dot_gradient_type, dot_gradient_rotation, dot_gradient_color1, dot_gradient_color2)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $_SESSION['user_id'], $shortCode, $targetUrl, $title, $description,
                $dotStyle, $dotColor, $bgColor,
                $cornerSquareStyle, $cornerSquareColor,
                $cornerDotStyle, $cornerDotColor,
                $logoData, $logoSize,
                $dotGradientEnabled, $dotGradientType, $dotGradientRotation, $dotGradientColor1, $dotGradientColor2
            ]);
            flash('success', 'QR-Code erstellt.');
        }

        redirect('/dashboard');
    }

    public static function delete(int $id): void {
        if (!verify_csrf()) {
            flash('error', 'Ungültige Anfrage.');
            redirect('/dashboard');
        }
        $db = getDB();
        $db->prepare('DELETE FROM qr_codes WHERE id = ? AND user_id = ?')
           ->execute([$id, $_SESSION['user_id']]);
        flash('success', 'QR-Code gelöscht.');
        redirect('/dashboard');
    }

    public static function publicView(string $code): void {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM qr_codes WHERE short_code = ? AND is_active = 1');
        $stmt->execute([$code]);
        $qr = $stmt->fetch();
        if (!$qr) {
            http_response_code(404);
            require __DIR__ . '/../views/404.php';
            return;
        }
        require __DIR__ . '/../views/qrcode_view.php';
    }

    public static function checkCode(): void {
        header('Content-Type: application/json');
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $excludeId = (int)($_POST['exclude_id'] ?? 0);

        if ($code === '' || !preg_match('/^[A-Z0-9]{3,20}$/', $code)) {
            echo json_encode(['available' => false, 'error' => 'Ungültiges Format']);
            return;
        }

        $db = getDB();
        $stmt = $db->prepare('SELECT id FROM qr_codes WHERE short_code = ? AND id != ?');
        $stmt->execute([$code, $excludeId]);
        echo json_encode(['available' => !$stmt->fetch()]);
    }

    private static function generateCode(): string {
        $db = getDB();
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $max = strlen($chars) - 1;
        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $chars[random_int(0, $max)];
            }
            $stmt = $db->prepare('SELECT id FROM qr_codes WHERE short_code = ?');
            $stmt->execute([$code]);
        } while ($stmt->fetch());
        return $code;
    }
}
