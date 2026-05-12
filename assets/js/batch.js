document.addEventListener('DOMContentLoaded', function () {
    var previewEl = document.getElementById('qr-preview');
    var csvFileInput = document.getElementById('csv_file');
    var csvPreview = document.getElementById('csv-preview');
    var csvTbody = document.getElementById('csv-tbody');
    var csvCount = document.getElementById('csv-count');
    var csvClear = document.getElementById('csv-clear');
    var batchControls = document.getElementById('batch-controls');
    var btnStart = document.getElementById('btn-start-batch');
    var batchProgress = document.getElementById('batch-progress');
    var progressBar = document.getElementById('progress-bar');
    var progressCurrent = document.getElementById('progress-current');
    var progressTotal = document.getElementById('progress-total');
    var progressStatus = document.getElementById('progress-status');
    var batchLog = document.getElementById('batch-log');
    var batchDone = document.getElementById('batch-done');

    var logoDataUrl = null;
    var csvEntries = [];
    var isProcessing = false;

    // --- QR Preview ---

    var qrCode = new QRCodeStyling({
        width: 300,
        height: 300,
        type: 'svg',
        data: 'https://example.com/DEMO',
        dotsOptions: getDotsOptionsBatch(),
        backgroundOptions: {
            color: document.getElementById('bg_color').value
        },
        cornersSquareOptions: {
            type: document.getElementById('corner_square_style').value,
            color: document.getElementById('corner_square_color').value
        },
        cornersDotOptions: {
            type: document.getElementById('corner_dot_style').value,
            color: document.getElementById('corner_dot_color').value
        },
        imageOptions: {
            crossOrigin: 'anonymous',
            margin: 8,
            imageSize: parseFloat(document.getElementById('logo_size').value),
            hideBackgroundDots: true
        },
        qrOptions: { errorCorrectionLevel: 'H' }
    });
        qrCode.append(previewEl);

    function getPreviewData() {
        return 'https://www.qrcodeman.to/DEMO';
    }

    function getDotsOptionsBatch() {
        var type = document.getElementById('dot_style').value;
        var gradientEl = document.getElementById('dot_gradient_enabled');
        var useGradient = gradientEl && gradientEl.checked;
        if (useGradient && document.getElementById('dot_gradient_type')) {
            var rotationDeg = parseFloat(document.getElementById('dot_gradient_rotation').value) || 0;
            return {
                type: type,
                gradient: {
                    type: document.getElementById('dot_gradient_type').value,
                    rotation: rotationDeg * Math.PI / 180,
                    colorStops: [
                        { offset: 0, color: document.getElementById('dot_gradient_color1').value },
                        { offset: 1, color: document.getElementById('dot_gradient_color2').value }
                    ]
                }
            };
        }
        return {
            color: document.getElementById('dot_color').value,
            type: type
        };
    }

    function updatePreview() {
        qrCode.update({
            data: getPreviewData(),
            dotsOptions: getDotsOptionsBatch(),
            backgroundOptions: {
                color: document.getElementById('bg_color').value
            },
            cornersSquareOptions: {
                type: document.getElementById('corner_square_style').value,
                color: document.getElementById('corner_square_color').value
            },
            cornersDotOptions: {
                type: document.getElementById('corner_dot_style').value,
                color: document.getElementById('corner_dot_color').value
            },
            image: logoDataUrl || undefined,
            imageOptions: {
                imageSize: parseFloat(document.getElementById('logo_size').value),
                hideBackgroundDots: true,
                margin: 8
            }
        });
    }

    // Bind all styling inputs
    document.querySelectorAll('#batch-settings-form input, #batch-settings-form select').forEach(function (el) {
        if (el.type === 'file' || el.type === 'hidden' || el.type === 'submit') return;
        el.addEventListener('input', updatePreview);
        el.addEventListener('change', updatePreview);
    });

    // Color hex sync
    document.querySelectorAll('.color-hex').forEach(function (hexInput) {
        var colorId = hexInput.dataset.target;
        var colorInput = document.getElementById(colorId);

        colorInput.addEventListener('input', function () {
            hexInput.value = this.value;
        });

        hexInput.addEventListener('input', function () {
            if (/^#[0-9a-fA-F]{6}$/.test(this.value)) {
                colorInput.value = this.value;
                updatePreview();
            }
        });
    });

    // Logo handling
    document.getElementById('logo_file').addEventListener('change', function (e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (ev) {
                logoDataUrl = ev.target.result;
                updatePreview();
            };
            reader.readAsDataURL(file);
        }
    });

    // Logo size display
    var logoSizeInput = document.getElementById('logo_size');
    var logoSizeVal = document.getElementById('logo_size_val');
    logoSizeInput.addEventListener('input', function () {
        logoSizeVal.textContent = Math.round(this.value * 100) + '%';
    });

    // Gradient toggle
    var gradientEnabled = document.getElementById('dot_gradient_enabled');
    var gradientOptions = document.getElementById('gradient_options');
    var dotColorWrap = document.getElementById('dot_color_wrap');
    if (gradientEnabled && gradientOptions && dotColorWrap) {
        function toggleGradientUIBatch() {
            var on = gradientEnabled.checked;
            gradientOptions.style.display = on ? 'block' : 'none';
            dotColorWrap.style.display = on ? 'none' : 'block';
            updatePreview();
        }
        gradientEnabled.addEventListener('change', toggleGradientUIBatch);
        toggleGradientUIBatch();
    }

    // Gradient rotation value display
    var rotationInput = document.getElementById('dot_gradient_rotation');
    var rotationVal = document.getElementById('dot_gradient_rotation_val');
    if (rotationInput && rotationVal) {
        rotationInput.addEventListener('input', function () {
            rotationVal.textContent = this.value + '°';
            updatePreview();
        });
    }

    // --- Presets (batch) ---
    var presetSelect = document.getElementById('preset_select');
    var presetNameInput = document.getElementById('preset_name');
    var presetSaveBtn = document.getElementById('preset_save_btn');
    var batchForm = document.getElementById('batch-settings-form');

    function getFormSettingsBatch() {
        var g = document.getElementById('dot_gradient_enabled');
        var o = {
            dot_style: document.getElementById('dot_style').value,
            dot_color: document.getElementById('dot_color').value,
            bg_color: document.getElementById('bg_color').value,
            corner_square_style: document.getElementById('corner_square_style').value,
            corner_square_color: document.getElementById('corner_square_color').value,
            corner_dot_style: document.getElementById('corner_dot_style').value,
            corner_dot_color: document.getElementById('corner_dot_color').value,
            logo_size: document.getElementById('logo_size').value,
            dot_gradient_enabled: g ? (g.checked ? 1 : 0) : 0,
            dot_gradient_type: document.getElementById('dot_gradient_type') ? document.getElementById('dot_gradient_type').value : 'linear',
            dot_gradient_rotation: document.getElementById('dot_gradient_rotation') ? parseFloat(document.getElementById('dot_gradient_rotation').value) : 135,
            dot_gradient_color1: document.getElementById('dot_gradient_color1') ? document.getElementById('dot_gradient_color1').value : '#000000',
            dot_gradient_color2: document.getElementById('dot_gradient_color2') ? document.getElementById('dot_gradient_color2').value : '#888888'
        };
        if (logoDataUrl) o.logo_data = logoDataUrl;
        return o;
    }

    function applySettingsBatch(s) {
        if (!s) return;
        function set(id, val) {
            var el = document.getElementById(id);
            if (el && val !== undefined) el.value = val;
        }
        function setCheck(id, val) {
            var el = document.getElementById(id);
            if (el) el.checked = !!val;
        }
        set('dot_style', s.dot_style);
        set('dot_color', s.dot_color);
        set('bg_color', s.bg_color);
        set('corner_square_style', s.corner_square_style);
        set('corner_square_color', s.corner_square_color);
        set('corner_dot_style', s.corner_dot_style);
        set('corner_dot_color', s.corner_dot_color);
        set('logo_size', s.logo_size);
        setCheck('dot_gradient_enabled', s.dot_gradient_enabled);
        set('dot_gradient_type', s.dot_gradient_type);
        set('dot_gradient_rotation', s.dot_gradient_rotation);
        set('dot_gradient_color1', s.dot_gradient_color1);
        set('dot_gradient_color2', s.dot_gradient_color2);
        document.querySelectorAll('.color-hex').forEach(function (hexInput) {
            var colorId = hexInput.dataset.target;
            var colorInput = document.getElementById(colorId);
            if (colorInput && colorInput.value) hexInput.value = colorInput.value;
        });
        if (s.dot_gradient_rotation !== undefined && rotationVal) rotationVal.textContent = (s.dot_gradient_rotation || 0) + '°';
        if (s.logo_data !== undefined) {
            logoDataUrl = s.logo_data || null;
        }
        if (gradientEnabled && gradientOptions && dotColorWrap) {
            gradientOptions.style.display = gradientEnabled.checked ? 'block' : 'none';
            dotColorWrap.style.display = gradientEnabled.checked ? 'none' : 'block';
        }
        updatePreview();
    }

    if (presetSelect) {
        fetch('/api/presets')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                (data.presets || []).forEach(function (p) {
                    var opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = p.name;
                    opt.dataset.settings = JSON.stringify(p.settings || {});
                    presetSelect.appendChild(opt);
                });
            });
        presetSelect.addEventListener('change', function () {
            var opt = this.options[this.selectedIndex];
            if (opt && opt.value) {
                try {
                    applySettingsBatch(JSON.parse(opt.dataset.settings || '{}'));
                } catch (e) {}
            }
        });
    }

    if (presetSaveBtn && presetNameInput && batchForm && presetSelect) {
        presetSaveBtn.addEventListener('click', function () {
            var name = presetNameInput.value.trim();
            if (!name) {
                alert('Bitte einen Namen für das Design eingeben.');
                return;
            }
            var formData = new FormData();
            formData.append('csrf_token', batchForm.querySelector('input[name="csrf_token"]').value);
            formData.append('name', name);
            formData.append('settings', JSON.stringify(getFormSettingsBatch()));
            fetch('/api/presets/save', { method: 'POST', body: formData })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    var opt = document.createElement('option');
                    opt.value = data.id;
                    opt.textContent = name;
                    opt.dataset.settings = JSON.stringify(getFormSettingsBatch());
                    presetSelect.appendChild(opt);
                    presetNameInput.value = '';
                });
        });
    }

    // --- CSV Parsing ---

    csvFileInput.addEventListener('change', function (e) {
        var file = e.target.files[0];
        if (!file) return;

        var reader = new FileReader();
        reader.onload = function (ev) {
            var text = ev.target.result;
            parseCSV(text);
        };
        reader.readAsText(file, 'UTF-8');
    });

    function parseCSV(text) {
        csvEntries = [];
        var lines = text.split(/\r?\n/);

        for (var i = 0; i < lines.length; i++) {
            var line = lines[i].trim();
            if (line === '') continue;

            var parts = parseCSVLine(line);
            if (parts.length < 2) continue;

            var title = parts[0].trim();
            var description = parts[1].trim();
            if (title === '') continue;

            csvEntries.push({ title: title, description: description });
        }

        renderCSVPreview();
    }

    function parseCSVLine(line) {
        var result = [];
        var current = '';
        var inQuotes = false;

        for (var i = 0; i < line.length; i++) {
            var ch = line[i];
            if (inQuotes) {
                if (ch === '"') {
                    if (i + 1 < line.length && line[i + 1] === '"') {
                        current += '"';
                        i++;
                    } else {
                        inQuotes = false;
                    }
                } else {
                    current += ch;
                }
            } else {
                if (ch === '"') {
                    inQuotes = true;
                } else if (ch === ',') {
                    result.push(current);
                    current = '';
                } else {
                    current += ch;
                }
            }
        }
        result.push(current);
        return result;
    }

    function renderCSVPreview() {
        csvTbody.innerHTML = '';

        if (csvEntries.length === 0) {
            csvPreview.style.display = 'none';
            batchControls.style.display = 'none';
            return;
        }

        csvEntries.forEach(function (entry, idx) {
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td>' + (idx + 1) + '</td>' +
                '<td>' + escapeHtml(entry.title) + '</td>' +
                '<td>' + escapeHtml(entry.description) + '</td>';
            csvTbody.appendChild(tr);
        });

        csvCount.textContent = csvEntries.length + ' Einträge';
        csvPreview.style.display = 'block';
        batchControls.style.display = 'block';
    }

    csvClear.addEventListener('click', function () {
        csvEntries = [];
        csvFileInput.value = '';
        csvPreview.style.display = 'none';
        batchControls.style.display = 'none';
        batchProgress.style.display = 'none';
        batchDone.style.display = 'none';
        batchLog.innerHTML = '';
    });

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // --- Batch Processing ---

    btnStart.addEventListener('click', function () {
        if (isProcessing) return;

        var targetUrl = document.getElementById('target_url').value.trim();
        if (!targetUrl) {
            alert('Bitte die Ziel-URL (Weiterleitung) eingeben.');
            return;
        }

        if (csvEntries.length === 0) {
            alert('Bitte zuerst eine CSV-Datei laden.');
            return;
        }

        startBatch();
    });

    function getStyleSettings() {
        var g = document.getElementById('dot_gradient_enabled');
        var o = {
            dot_style: document.getElementById('dot_style').value,
            dot_color: document.getElementById('dot_color').value,
            bg_color: document.getElementById('bg_color').value,
            corner_square_style: document.getElementById('corner_square_style').value,
            corner_square_color: document.getElementById('corner_square_color').value,
            corner_dot_style: document.getElementById('corner_dot_style').value,
            corner_dot_color: document.getElementById('corner_dot_color').value,
            logo_size: document.getElementById('logo_size').value,
            dot_gradient_enabled: g ? (g.checked ? 1 : 0) : 0,
            dot_gradient_type: document.getElementById('dot_gradient_type') ? document.getElementById('dot_gradient_type').value : 'linear',
            dot_gradient_rotation: document.getElementById('dot_gradient_rotation') ? parseFloat(document.getElementById('dot_gradient_rotation').value) : 0,
            dot_gradient_color1: document.getElementById('dot_gradient_color1') ? document.getElementById('dot_gradient_color1').value : '#000000',
            dot_gradient_color2: document.getElementById('dot_gradient_color2') ? document.getElementById('dot_gradient_color2').value : '#888888'
        };
        return o;
    }

    function startBatch() {
        isProcessing = true;
        btnStart.disabled = true;
        btnStart.textContent = 'Verarbeite...';
        batchProgress.style.display = 'block';
        batchDone.style.display = 'none';
        batchLog.innerHTML = '';
        progressTotal.textContent = csvEntries.length;
        progressCurrent.textContent = '0';
        progressBar.style.width = '0%';

        var settings = getStyleSettings();
        var targetUrl = document.getElementById('target_url').value.trim();
        var suffix = document.getElementById('suffix').value.trim();
        var shortLinkBase = 'https://www.qrcodeman.to';

        processNext(0, settings, shortLinkBase, targetUrl, suffix);
    }

    function processNext(index, settings, shortLinkBase, targetUrl, suffix) {
        if (index >= csvEntries.length) {
            finishBatch();
            return;
        }

        var entry = csvEntries[index];
        progressCurrent.textContent = index + 1;
        progressBar.style.width = Math.round(((index + 1) / csvEntries.length) * 100) + '%';
        progressStatus.textContent = '— ' + entry.title;

        // Step 1: Create QR code entry in DB
        var formData = new FormData();
        formData.append('csrf_token', window.CSRF_TOKEN);
        formData.append('title', entry.title);
        formData.append('suffix', suffix);
        formData.append('description', entry.description);
        formData.append('target_url', targetUrl);
        formData.append('dot_style', settings.dot_style);
        formData.append('dot_color', settings.dot_color);
        formData.append('bg_color', settings.bg_color);
        formData.append('corner_square_style', settings.corner_square_style);
        formData.append('corner_square_color', settings.corner_square_color);
        formData.append('corner_dot_style', settings.corner_dot_style);
        formData.append('corner_dot_color', settings.corner_dot_color);
        formData.append('logo_size', settings.logo_size);
        formData.append('dot_gradient_enabled', settings.dot_gradient_enabled ? '1' : '');
        formData.append('dot_gradient_type', settings.dot_gradient_type || 'linear');
        formData.append('dot_gradient_rotation', String(settings.dot_gradient_rotation || 0));
        formData.append('dot_gradient_color1', settings.dot_gradient_color1 || '#000000');
        formData.append('dot_gradient_color2', settings.dot_gradient_color2 || '#888888');
        if (logoDataUrl) {
            formData.append('logo_data', logoDataUrl);
        }

        fetch('/api/batch/create', { method: 'POST', body: formData })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.error) {
                    logEntry(index, entry.title, 'error', data.error);
                    processNext(index + 1, settings, shortLinkBase, targetUrl, suffix);
                    return;
                }

                // Step 2: Render QR code (qrcodeman.to/CODE) and generate PDF
                generateAndUploadPDF(data, entry, suffix, shortLinkBase, function (success) {
                    logEntry(index, entry.title, success ? 'ok' : 'warn',
                        success ? data.short_code : 'PDF-Upload fehlgeschlagen');
                    processNext(index + 1, settings, shortLinkBase, targetUrl, suffix);
                });
            })
            .catch(function (err) {
                logEntry(index, entry.title, 'error', err.message);
                processNext(index + 1, settings, shortLinkBase, targetUrl, suffix);
            });
    }

    function generateAndUploadPDF(dbResult, entry, suffix, shortLinkBase, callback) {
        var qrData = shortLinkBase.replace(/\/+$/, '') + '/' + dbResult.short_code;

        var config = {
            width: 256,
            height: 256,
            type: 'svg',
            data: qrData,
            dotsOptions: getDotsOptionsBatch(),
            backgroundOptions: {
                color: document.getElementById('bg_color').value
            },
            cornersSquareOptions: {
                type: document.getElementById('corner_square_style').value,
                color: document.getElementById('corner_square_color').value
            },
            cornersDotOptions: {
                type: document.getElementById('corner_dot_style').value,
                color: document.getElementById('corner_dot_color').value
            },
            image: logoDataUrl || undefined,
            imageOptions: {
                crossOrigin: 'anonymous',
                margin: 20,
                imageSize: parseFloat(document.getElementById('logo_size').value),
                hideBackgroundDots: true
            },
            qrOptions: { errorCorrectionLevel: 'H' }
        };

        var qrPdf = new QRCodeStyling(config);

        qrPdf.getRawData('svg').then(function (blob) {
            var sizeMm = 45; // PDF-Seite und QR-Größe in mm (kleines Format)
            var pdf = new jspdf.jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: [sizeMm, sizeMm]
            });

            blob.text().then(function (svgString) {
                var wrap = document.createElement('div');
                wrap.innerHTML = svgString.trim();
                var svgEl = wrap.querySelector('svg');
                if (!svgEl) {
                    console.error('QR SVG not found');
                    callback(false);
                    return;
                }
                var w = parseFloat(svgEl.getAttribute('width')) || 256;
                var h = parseFloat(svgEl.getAttribute('height')) || 256;
                svgEl.setAttribute('viewBox', '0 0 ' + w + ' ' + h);
                svgEl.setAttribute('width', w);
                svgEl.setAttribute('height', h);
                pdf.svg(svgEl, { x: 0, y: 0, width: sizeMm, height: sizeMm })
                    .then(function () {
                        var pdfBlob = pdf.output('blob');
                        var uploadData = new FormData();
                        uploadData.append('csrf_token', window.CSRF_TOKEN);
                        uploadData.append('title', entry.title);
                        uploadData.append('suffix', suffix);
                        uploadData.append('pdf_file', pdfBlob, entry.title + '.pdf');
                        return fetch('/api/batch/save-pdf', { method: 'POST', body: uploadData });
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (result) {
                        callback(!result.error);
                    })
                    .catch(function (err) {
                        console.error('svg2pdf/upload error:', err);
                        callback(false);
                    });
            }).catch(function (err) {
                console.error('QR SVG read error:', err);
                callback(false);
            });
        }).catch(function (err) {
            console.error('QR render error:', err);
            callback(false);
        });
    }

    function logEntry(index, title, status, message) {
        var div = document.createElement('div');
        div.className = 'log-entry log-' + status;

        var icon = status === 'ok' ? '&#10003;' : status === 'error' ? '&#10007;' : '&#9888;';
        div.innerHTML = '<span class="log-icon">' + icon + '</span> ' +
            '<strong>' + escapeHtml(title) + '</strong> ' +
            '<span class="log-msg">' + escapeHtml(message) + '</span>';

        batchLog.appendChild(div);
        batchLog.scrollTop = batchLog.scrollHeight;
    }

    function finishBatch() {
        isProcessing = false;
        btnStart.disabled = false;
        btnStart.textContent = 'QR-Codes erstellen & PDFs speichern';
        progressStatus.textContent = '— Fertig!';
        batchDone.style.display = 'block';
    }
});
