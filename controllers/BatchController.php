<?php
class BatchController {

    public static function importForm(): void {
        require __DIR__ . '/../views/batch_import.php';
    }

    public static function createEntry(): void {
        header('Content-Type: application/json');

        if (!verify_csrf()) {
            http_response_code(403);
            echo json_encode(['error' => 'Ungültige Anfrage.']);
            return;
        }

        $db = getDB();

        $titleRaw    = trim($_POST['title'] ?? '');
        $suffix      = trim($_POST['suffix'] ?? '');
        $title       = $suffix !== '' ? $titleRaw . '-' . $suffix : $titleRaw;
        $description = trim($_POST['description'] ?? '');
        $targetUrl   = trim($_POST['target_url'] ?? '');
        $baseUrl     = 'https://www.qrcodeman.to';
        $dotStyle    = $_POST['dot_style'] ?? 'square';
        $dotColor    = $_POST['dot_color'] ?? '#ffffff';
        $bgColor     = $_POST['bg_color'] ?? '#0a0a0f';
        $cornerSquareStyle = $_POST['corner_square_style'] ?? 'square';
        $cornerSquareColor = $_POST['corner_square_color'] ?? '#ffffff';
        $cornerDotStyle    = $_POST['corner_dot_style'] ?? 'square';
        $cornerDotColor    = $_POST['corner_dot_color'] ?? '#ffffff';
        $logoData    = $_POST['logo_data'] ?? null;
        $logoSize    = max(0.1, min(0.5, (float)($_POST['logo_size'] ?? 0.4)));
        $dotGradientEnabled = !empty($_POST['dot_gradient_enabled']) ? 1 : 0;
        $dotGradientType = ($_POST['dot_gradient_type'] ?? 'linear') === 'radial' ? 'radial' : 'linear';
        $dotGradientRotation = (float)($_POST['dot_gradient_rotation'] ?? 0);
        $dotGradientColor1 = $_POST['dot_gradient_color1'] ?? '#000000';
        $dotGradientColor2 = $_POST['dot_gradient_color2'] ?? '#888888';

        if ($targetUrl === '' || !filter_var($targetUrl, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Ungültige Ziel-URL.']);
            return;
        }

        $shortCode = self::generateCode();
        $shortLink = $baseUrl . '/' . $shortCode;

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

        echo json_encode([
            'id'          => $db->lastInsertId(),
            'short_code'  => $shortCode,
            'short_link'  => $shortLink,
            'target_url'  => $targetUrl,
        ]);
    }

    public static function savePdf(): void {
        header('Content-Type: application/json');

        if (!verify_csrf()) {
            http_response_code(403);
            echo json_encode(['error' => 'Ungültige Anfrage.']);
            return;
        }

        $title  = trim($_POST['title'] ?? '');
        $suffix = trim($_POST['suffix'] ?? '');

        if ($title === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Title fehlt.']);
            return;
        }

        if (!isset($_FILES['pdf_file'])) {
            http_response_code(400);
            echo json_encode(['error' => 'PDF-Datei fehlt (kein Upload).']);
            return;
        }
        $err = $_FILES['pdf_file']['error'];
        if ($err !== UPLOAD_ERR_OK) {
            $messages = [
                UPLOAD_ERR_INI_SIZE   => 'PDF zu groß (Server-Limit).',
                UPLOAD_ERR_FORM_SIZE => 'PDF zu groß (Formular-Limit).',
                UPLOAD_ERR_PARTIAL    => 'PDF nur teilweise hochgeladen.',
                UPLOAD_ERR_NO_FILE    => 'Keine Datei gewählt.',
                UPLOAD_ERR_NO_TMP_DIR => 'Server: temporärer Ordner fehlt.',
                UPLOAD_ERR_CANT_WRITE => 'Server: Speichern fehlgeschlagen.',
                UPLOAD_ERR_EXTENSION  => 'Upload durch Server-Erweiterung blockiert.',
            ];
            $msg = $messages[$err] ?? 'Upload-Fehler (Code ' . $err . ').';
            http_response_code(400);
            echo json_encode(['error' => $msg]);
            return;
        }

        $safeSuffix = $suffix !== '' ? self::sanitizeFilename($suffix) : 'default';
        $pdfDir = __DIR__ . '/../assets/auto/pdfs/' . $safeSuffix;
        if (!is_dir($pdfDir)) {
            mkdir($pdfDir, 0755, true);
        }

        $safeName = self::sanitizeFilename($title);
        $baseName = $suffix !== ''
            ? $safeName . '-' . $safeSuffix
            : $safeName;

        $filename = $baseName . '.pdf';
        $destPath = $pdfDir . '/' . $filename;

        $counter = 2;
        while (file_exists($destPath)) {
            $filename = $baseName . '-' . $counter . '.pdf';
            $destPath = $pdfDir . '/' . $filename;
            $counter++;
        }

        if (!move_uploaded_file($_FILES['pdf_file']['tmp_name'], $destPath)) {
            http_response_code(500);
            echo json_encode(['error' => 'PDF konnte nicht gespeichert werden.']);
            return;
        }

        echo json_encode([
            'filename' => $filename,
            'path'     => '/assets/auto/pdfs/' . $safeSuffix . '/' . $filename,
        ]);
    }

    private static function sanitizeFilename(string $name): string {
        $name = preg_replace('/[^\w\-. äöüÄÖÜß]/u', '_', $name);
        $name = preg_replace('/_+/', '_', $name);
        $name = trim($name, '_. ');
        return $name ?: 'unnamed';
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
