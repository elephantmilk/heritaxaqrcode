<?php
class PresetController {

    public static function list(): void {
        header('Content-Type: application/json');
        $db = getDB();
        $stmt = $db->prepare('SELECT id, name, settings, created_at FROM qr_presets WHERE user_id = ? ORDER BY name');
        $stmt->execute([$_SESSION['user_id']]);
        $presets = $stmt->fetchAll();
        foreach ($presets as &$p) {
            $p['settings'] = json_decode($p['settings'], true);
        }
        echo json_encode(['presets' => $presets]);
    }

    public static function save(): void {
        header('Content-Type: application/json');

        if (!verify_csrf()) {
            http_response_code(403);
            echo json_encode(['error' => 'Ungültige Anfrage.']);
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $settingsJson = $_POST['settings'] ?? '';

        if ($name === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Name fehlt.']);
            return;
        }

        $settings = json_decode($settingsJson, true);
        if (!is_array($settings)) {
            http_response_code(400);
            echo json_encode(['error' => 'Ungültige Einstellungen.']);
            return;
        }

        $db = getDB();
        $stmt = $db->prepare('INSERT INTO qr_presets (user_id, name, settings) VALUES (?, ?, ?)');
        $stmt->execute([$_SESSION['user_id'], $name, $settingsJson]);

        echo json_encode([
            'id'   => $db->lastInsertId(),
            'name' => $name,
        ]);
    }

    public static function delete(int $id): void {
        header('Content-Type: application/json');

        if (!verify_csrf()) {
            http_response_code(403);
            echo json_encode(['error' => 'Ungültige Anfrage.']);
            return;
        }

        $db = getDB();
        $stmt = $db->prepare('DELETE FROM qr_presets WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $_SESSION['user_id']]);

        echo json_encode(['ok' => true]);
    }
}
