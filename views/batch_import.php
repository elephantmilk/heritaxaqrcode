<?php
$pageTitle = 'Batch Import — ' . APP_NAME;
$loadQRLib = true;
$loadBatchJS = true;
require __DIR__ . '/layout.php';
?>

<div class="page-header">
    <h1>Batch Import</h1>
</div>

<div class="batch-layout">
    <!-- Left: Settings + CSV -->
    <div class="batch-settings">
        <form id="batch-settings-form" class="qr-editor-form">
            <?= csrf_field() ?>

            <div class="form-section">
                <h2>Basis-Einstellungen</h2>
                <div class="form-group">
                    <label for="target_url">Ziel-URL (Weiterleitung)</label>
                    <input type="url" id="target_url" name="target_url" required placeholder="https://example.com/landing" value="">
                    <small class="text-muted">Wohin der Nutzer beim Scan weitergeleitet wird (in der DB gespeichert)</small>
                </div>
                <div class="form-group">
                    <label for="suffix">PDF-Suffix</label>
                    <input type="text" id="suffix" name="suffix" placeholder="z.B. peptify" value="">
                    <small class="text-muted">Dateiname: {Title}-{Suffix}.pdf</small>
                </div>
            </div>

            <div class="form-section">
                <h2>Dot-Style</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label for="dot_style">Form</label>
                        <select id="dot_style" name="dot_style">
                            <option value="square">Square</option>
                            <option value="rounded" selected>Rounded</option>
                            <option value="dots">Dots</option>
                            <option value="classy">Classy</option>
                            <option value="classy-rounded">Classy Rounded</option>
                            <option value="extra-rounded">Extra Rounded</option>
                        </select>
                    </div>
                    <div class="form-group dot-color-single" id="dot_color_wrap">
                        <label for="dot_color">Farbe</label>
                        <div class="color-input">
                            <input type="color" id="dot_color" name="dot_color" value="#ffffff">
                            <input type="text" class="color-hex" value="#ffffff" data-target="dot_color">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="dot_gradient_enabled" name="dot_gradient_enabled" value="1">
                        Farbverlauf für Dots
                    </label>
                </div>
                <div class="gradient-options" id="gradient_options" style="display:none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="dot_gradient_type">Verlauf-Typ</label>
                            <select id="dot_gradient_type" name="dot_gradient_type">
                                <option value="linear" selected>Linear</option>
                                <option value="radial">Radial</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="dot_gradient_rotation">Winkel (Grad)</label>
                            <div class="range-input">
                                <input type="range" id="dot_gradient_rotation" name="dot_gradient_rotation" min="0" max="360" step="5" value="135">
                                <span id="dot_gradient_rotation_val">135°</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="dot_gradient_color1">Farbe 1</label>
                            <div class="color-input">
                                <input type="color" id="dot_gradient_color1" name="dot_gradient_color1" value="#000000">
                                <input type="text" class="color-hex" value="#000000" data-target="dot_gradient_color1">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="dot_gradient_color2">Farbe 2</label>
                            <div class="color-input">
                                <input type="color" id="dot_gradient_color2" name="dot_gradient_color2" value="#888888">
                                <input type="text" class="color-hex" value="#888888" data-target="dot_gradient_color2">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>Hintergrund</h2>
                <div class="form-group">
                    <label for="bg_color">Farbe</label>
                    <div class="color-input">
                        <input type="color" id="bg_color" name="bg_color" value="#0a0a0f">
                        <input type="text" class="color-hex" value="#0a0a0f" data-target="bg_color">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>Ecken</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label for="corner_square_style">Quadrat-Form</label>
                        <select id="corner_square_style" name="corner_square_style">
                            <option value="square">Square</option>
                            <option value="extra-rounded" selected>Extra Rounded</option>
                            <option value="dot">Dot</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="corner_square_color">Quadrat-Farbe</label>
                        <div class="color-input">
                            <input type="color" id="corner_square_color" name="corner_square_color" value="#6366f1">
                            <input type="text" class="color-hex" value="#6366f1" data-target="corner_square_color">
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="corner_dot_style">Punkt-Form</label>
                        <select id="corner_dot_style" name="corner_dot_style">
                            <option value="square">Square</option>
                            <option value="dot" selected>Dot</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="corner_dot_color">Punkt-Farbe</label>
                        <div class="color-input">
                            <input type="color" id="corner_dot_color" name="corner_dot_color" value="#6366f1">
                            <input type="text" class="color-hex" value="#6366f1" data-target="corner_dot_color">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>Logo</h2>
                <div class="form-group">
                    <label for="logo_file">Bild hochladen <span class="text-muted">(max 2MB)</span></label>
                    <input type="file" id="logo_file" name="logo_file" accept="image/*">
                </div>
                <div class="form-group">
                    <label for="logo_size">Logo-Größe</label>
                    <div class="range-input">
                        <input type="range" id="logo_size" name="logo_size" min="0.1" max="0.5" step="0.05" value="0.4">
                        <span id="logo_size_val">40%</span>
                    </div>
                </div>
            </div>
        </form>

        <!-- CSV: links -->
        <div class="batch-csv-section">
            <h2>CSV-Datei</h2>
            <p class="text-muted" style="margin-bottom: 0.75rem;">Format: Title,Description (eine Zeile pro QR-Code)</p>
            <div class="form-group">
                <input type="file" id="csv_file" accept=".csv,.txt">
            </div>

            <div id="csv-preview" class="csv-preview" style="display:none;">
                <div class="csv-preview-header">
                    <span id="csv-count">0 Einträge</span>
                    <button type="button" id="csv-clear" class="btn btn-sm btn-ghost">Zurücksetzen</button>
                </div>
                <div class="csv-table-wrap">
                    <table class="csv-table">
                        <thead>
                            <tr><th>#</th><th>Title</th><th>Description</th></tr>
                        </thead>
                        <tbody id="csv-tbody"></tbody>
                    </table>
                </div>
            </div>

            <div id="batch-controls" style="margin-top: 1rem;">
                <button type="button" id="btn-start-batch" class="btn btn-primary btn-full">
                    QR-Codes erstellen &amp; PDFs speichern
                </button>
                <p class="text-muted" style="font-size: 0.8rem; margin-top: 0.5rem;">CSV zuerst hochladen, dann hier starten.</p>
            </div>

            <div id="batch-progress" style="display:none; margin-top: 1rem;">
                <div class="progress-bar-wrap">
                    <div class="progress-bar" id="progress-bar"></div>
                </div>
                <div class="progress-text">
                    <span id="progress-current">0</span> / <span id="progress-total">0</span>
                    <span id="progress-status"></span>
                </div>
                <div id="batch-log" class="batch-log"></div>
            </div>

            <div id="batch-done" style="display:none; margin-top: 1rem;" class="alert alert-success">
                Fertig! Alle QR-Codes wurden erstellt und PDFs gespeichert.
            </div>
        </div>
    </div>

    <!-- Right: Design + Preview -->
    <div class="batch-right">
        <div class="form-section preset-section">
            <h2>Design</h2>
            <div class="preset-controls">
                <div class="form-group">
                    <label for="preset_select">Design laden</label>
                    <select id="preset_select" class="preset-select">
                        <option value="">— Gespeichertes Design wählen —</option>
                    </select>
                </div>
                <div class="form-group preset-save-row">
                    <label>&nbsp;</label>
                    <div class="preset-save-group">
                        <input type="text" id="preset_name" placeholder="Design-Name" class="preset-name-input">
                        <button type="button" id="preset_save_btn" class="btn btn-ghost btn-sm">Design speichern</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="preview-sticky">
            <h2>Vorschau</h2>
            <div id="qr-preview" class="qr-preview-container"></div>
        </div>
    </div>
</div>

<script>
    window.CSRF_TOKEN = '<?= csrf_token() ?>';
</script>

<?php require __DIR__ . '/layout_end.php'; ?>
