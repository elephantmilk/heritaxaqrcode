<?php
$pageTitle = ($qr ? 'Bearbeiten' : 'Neuer QR-Code') . ' — ' . APP_NAME;
$loadQRLib = true;
require __DIR__ . '/layout.php';

$defaults = [
    'id' => 0,
    'target_url' => '',
    'title' => '',
    'short_code' => '',
    'dot_style' => 'rounded',
    'dot_color' => '#ffffff',
    'bg_color' => '#0a0a0f',
    'corner_square_style' => 'extra-rounded',
    'corner_square_color' => '#6366f1',
    'corner_dot_style' => 'dot',
    'corner_dot_color' => '#6366f1',
    'logo_data' => null,
    'logo_size' => 0.4,
];
$v = $qr ? array_merge($defaults, $qr) : $defaults;
?>

<div class="page-header">
    <h1><?= $qr ? 'QR-Code bearbeiten' : 'Neuer QR-Code' ?></h1>
</div>

<div class="qr-editor">
    <form method="POST" action="/qrcode/save" enctype="multipart/form-data" id="qr-form" class="qr-editor-form">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $v['id'] ?>">

        <div class="form-section">
            <h2>Ziel</h2>
            <div class="form-group">
                <label for="target_url">Weiterleitungs-URL</label>
                <input type="url" id="target_url" name="target_url" required placeholder="https://example.com" value="<?= e($v['target_url']) ?>">
            </div>
            <div class="form-group">
                <label for="title">Titel <span class="text-muted">(optional)</span></label>
                <input type="text" id="title" name="title" placeholder="Mein QR-Code" value="<?= e($v['title']) ?>">
            </div>
            <div class="form-group">
                <label for="short_code">Code <span class="text-muted">(leer = automatisch 6-stellig)</span></label>
                <div class="input-with-status">
                    <input type="text" id="short_code" name="short_code" placeholder="Z.B. SHOP23" value="<?= e($v['short_code']) ?>" pattern="[A-Z0-9]{3,20}" maxlength="20" style="text-transform: uppercase;">
                    <span id="code-status"></span>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h2>Dot-Style</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="dot_style">Form</label>
                    <select id="dot_style" name="dot_style">
                        <option value="square" <?= $v['dot_style'] === 'square' ? 'selected' : '' ?>>Square</option>
                        <option value="rounded" <?= $v['dot_style'] === 'rounded' ? 'selected' : '' ?>>Rounded</option>
                        <option value="dots" <?= $v['dot_style'] === 'dots' ? 'selected' : '' ?>>Dots</option>
                        <option value="classy" <?= $v['dot_style'] === 'classy' ? 'selected' : '' ?>>Classy</option>
                        <option value="classy-rounded" <?= $v['dot_style'] === 'classy-rounded' ? 'selected' : '' ?>>Classy Rounded</option>
                        <option value="extra-rounded" <?= $v['dot_style'] === 'extra-rounded' ? 'selected' : '' ?>>Extra Rounded</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="dot_color">Farbe</label>
                    <div class="color-input">
                        <input type="color" id="dot_color" name="dot_color" value="<?= e($v['dot_color']) ?>">
                        <input type="text" class="color-hex" value="<?= e($v['dot_color']) ?>" data-target="dot_color">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h2>Hintergrund</h2>
            <div class="form-group">
                <label for="bg_color">Farbe</label>
                <div class="color-input">
                    <input type="color" id="bg_color" name="bg_color" value="<?= e($v['bg_color']) ?>">
                    <input type="text" class="color-hex" value="<?= e($v['bg_color']) ?>" data-target="bg_color">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h2>Ecken</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="corner_square_style">Quadrat-Form</label>
                    <select id="corner_square_style" name="corner_square_style">
                        <option value="square" <?= $v['corner_square_style'] === 'square' ? 'selected' : '' ?>>Square</option>
                        <option value="extra-rounded" <?= $v['corner_square_style'] === 'extra-rounded' ? 'selected' : '' ?>>Extra Rounded</option>
                        <option value="dot" <?= $v['corner_square_style'] === 'dot' ? 'selected' : '' ?>>Dot</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="corner_square_color">Quadrat-Farbe</label>
                    <div class="color-input">
                        <input type="color" id="corner_square_color" name="corner_square_color" value="<?= e($v['corner_square_color']) ?>">
                        <input type="text" class="color-hex" value="<?= e($v['corner_square_color']) ?>" data-target="corner_square_color">
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="corner_dot_style">Punkt-Form</label>
                    <select id="corner_dot_style" name="corner_dot_style">
                        <option value="square" <?= $v['corner_dot_style'] === 'square' ? 'selected' : '' ?>>Square</option>
                        <option value="dot" <?= $v['corner_dot_style'] === 'dot' ? 'selected' : '' ?>>Dot</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="corner_dot_color">Punkt-Farbe</label>
                    <div class="color-input">
                        <input type="color" id="corner_dot_color" name="corner_dot_color" value="<?= e($v['corner_dot_color']) ?>">
                        <input type="text" class="color-hex" value="<?= e($v['corner_dot_color']) ?>" data-target="corner_dot_color">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h2>Logo</h2>
            <div class="form-group">
                <label for="logo_file">Bild hochladen <span class="text-muted">(max 2MB)</span></label>
                <input type="file" id="logo_file" name="logo_file" accept="image/*">
                <?php if ($v['logo_data']): ?>
                    <input type="hidden" name="existing_logo_data" value="<?= e($v['logo_data']) ?>">
                    <div class="logo-current">
                        <img src="<?= e($v['logo_data']) ?>" alt="Aktuelles Logo" width="40" height="40">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remove_logo" value="1"> Logo entfernen
                        </label>
                    </div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="logo_size">Logo-Größe</label>
                <div class="range-input">
                    <input type="range" id="logo_size" name="logo_size" min="0.1" max="0.5" step="0.05" value="<?= e($v['logo_size']) ?>">
                    <span id="logo_size_val"><?= round($v['logo_size'] * 100) ?>%</span>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-full">
                <?= $qr ? 'Speichern' : 'QR-Code erstellen' ?>
            </button>
        </div>
    </form>

    <div class="qr-editor-preview">
        <div class="preview-sticky">
            <h2>Vorschau</h2>
            <div id="qr-preview" class="qr-preview-container"></div>
            <div class="preview-actions">
                <button type="button" id="download-svg" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    SVG herunterladen
                </button>
                <button type="button" id="download-pdf" class="btn btn-ghost">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    PDF herunterladen
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    window.QR_EDIT_DATA = <?= json_encode([
        'id' => $v['id'],
        'logo' => $v['logo_data'] ?: null,
    ]) ?>;
</script>

<?php require __DIR__ . '/layout_end.php'; ?>
