document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('qr-form');
    if (!form) return;

    const previewEl = document.getElementById('qr-preview');
    const editData = window.QR_EDIT_DATA || {};
    let logoDataUrl = editData.logo || null;

    const baseUrl = window.location.origin;
    const shortCodeInput = document.getElementById('short_code');
    const urlInput = document.getElementById('target_url');

    function getData() {
        const code = shortCodeInput.value.toUpperCase() || 'DEMO';
        return baseUrl + '/' + code;
    }

    function getDotsOptions() {
        const type = document.getElementById('dot_style').value;
        const gradientEl = document.getElementById('dot_gradient_enabled');
        const useGradient = gradientEl && gradientEl.checked;
        if (useGradient && document.getElementById('dot_gradient_type')) {
            const rotationDeg = parseFloat(document.getElementById('dot_gradient_rotation').value) || 0;
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

    // Initialize QR code
    const qrCode = new QRCodeStyling({
        width: 300,
        height: 300,
        type: 'svg',
        data: getData(),
        dotsOptions: getDotsOptions(),
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
            margin: 8,
            imageSize: parseFloat(document.getElementById('logo_size').value),
            hideBackgroundDots: true
        },
        qrOptions: {
            errorCorrectionLevel: 'H'
        }
    });

    qrCode.append(previewEl);

    // Update preview on any input change
    function updatePreview() {
        qrCode.update({
            data: getData(),
            dotsOptions: getDotsOptions(),
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

    // Gradient toggle: show/hide options and single color
    const gradientEnabled = document.getElementById('dot_gradient_enabled');
    const gradientOptions = document.getElementById('gradient_options');
    const dotColorWrap = document.getElementById('dot_color_wrap');
    if (gradientEnabled && gradientOptions && dotColorWrap) {
        function toggleGradientUI() {
            const on = gradientEnabled.checked;
            gradientOptions.style.display = on ? 'block' : 'none';
            dotColorWrap.style.display = on ? 'none' : 'block';
            updatePreview();
        }
        gradientEnabled.addEventListener('change', toggleGradientUI);
        toggleGradientUI();
    }

    // Gradient rotation value display
    const rotationInput = document.getElementById('dot_gradient_rotation');
    const rotationVal = document.getElementById('dot_gradient_rotation_val');
    if (rotationInput && rotationVal) {
        rotationInput.addEventListener('input', function () {
            rotationVal.textContent = this.value + '°';
            updatePreview();
        });
    }

    // Bind all inputs
    form.querySelectorAll('input, select').forEach(function (el) {
        if (el.type === 'file' || el.type === 'hidden' || el.type === 'submit') return;
        el.addEventListener('input', updatePreview);
        el.addEventListener('change', updatePreview);
    });

    // Logo file handling
    document.getElementById('logo_file').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (ev) {
                logoDataUrl = ev.target.result;
                updatePreview();
            };
            reader.readAsDataURL(file);
        }
    });

    // Remove logo checkbox
    const removeLogo = document.querySelector('input[name="remove_logo"]');
    if (removeLogo) {
        removeLogo.addEventListener('change', function () {
            if (this.checked) {
                logoDataUrl = null;
                updatePreview();
            } else {
                logoDataUrl = editData.logo || null;
                updatePreview();
            }
        });
    }

    // Logo size display
    const logoSizeInput = document.getElementById('logo_size');
    const logoSizeVal = document.getElementById('logo_size_val');
    logoSizeInput.addEventListener('input', function () {
        logoSizeVal.textContent = Math.round(this.value * 100) + '%';
    });

    // Color hex sync
    document.querySelectorAll('.color-hex').forEach(function (hexInput) {
        const colorId = hexInput.dataset.target;
        const colorInput = document.getElementById(colorId);

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

    // Short code uppercase enforcement
    shortCodeInput.addEventListener('input', function () {
        this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    });

    // Short code availability check
    let checkTimeout;
    shortCodeInput.addEventListener('input', function () {
        const status = document.getElementById('code-status');
        const code = this.value;
        clearTimeout(checkTimeout);

        if (code.length < 3) {
            status.textContent = '';
            status.className = '';
            return;
        }

        status.textContent = '...';
        status.className = 'status-checking';

        checkTimeout = setTimeout(function () {
            const formData = new FormData();
            formData.append('code', code);
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
            if (editData.id) formData.append('exclude_id', editData.id);

            fetch('/api/qrcode/check-code', { method: 'POST', body: formData })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.available) {
                        status.textContent = 'Verfügbar';
                        status.className = 'status-available';
                    } else {
                        status.textContent = 'Vergeben';
                        status.className = 'status-taken';
                    }
                });
        }, 300);
    });

    // Helper: build high-res QR config from current form state
    function getDownloadConfig() {
        return {
            width: 1024,
            height: 1024,
            type: 'svg',
            data: getData(),
            dotsOptions: getDotsOptions(),
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
    }

    // SVG Download
    document.getElementById('download-svg').addEventListener('click', function () {
        const code = shortCodeInput.value.toUpperCase() || 'QRCODE';
        var qrDownload = new QRCodeStyling(getDownloadConfig());
        qrDownload.download({ name: 'qrcode-' + code, extension: 'svg' });
    });

    // PDF Download — render as canvas, embed high-res image into PDF
    document.getElementById('download-pdf').addEventListener('click', function () {
        var btn = this;
        btn.disabled = true;
        btn.textContent = 'Erstelle PDF...';
        var code = shortCodeInput.value.toUpperCase() || 'QRCODE';

        var config = getDownloadConfig();
        config.type = 'canvas'; // render as canvas for reliable PDF embedding
        config.width = 2048;
        config.height = 2048;

        var qrPdf = new QRCodeStyling(config);
        qrPdf.getRawData('png').then(function (blob) {
            var reader = new FileReader();
            reader.onload = function () {
                var imgData = reader.result;
                var size = 100; // mm — square PDF
                var pdf = new jspdf.jsPDF({ orientation: 'portrait', unit: 'mm', format: [size, size] });
                pdf.addImage(imgData, 'PNG', 0, 0, size, size);
                pdf.save('qrcode-' + code + '.pdf');
                btn.disabled = false;
                btn.textContent = 'PDF herunterladen';
            };
            reader.readAsDataURL(blob);
        }).catch(function (err) {
            console.error('PDF error:', err);
            btn.disabled = false;
            btn.textContent = 'PDF herunterladen';
        });
    });

    // --- Presets ---
    const presetSelect = document.getElementById('preset_select');
    const presetNameInput = document.getElementById('preset_name');
    const presetSaveBtn = document.getElementById('preset_save_btn');

    function getFormSettings() {
        const g = document.getElementById('dot_gradient_enabled');
        const o = {
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

    function applySettings(s) {
        if (!s) return;
        const set = function (id, val) {
            const el = document.getElementById(id);
            if (el && val !== undefined) el.value = val;
        };
        const setCheck = function (id, val) {
            const el = document.getElementById(id);
            if (el) el.checked = !!val;
        };
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
            const colorId = hexInput.dataset.target;
            const colorInput = document.getElementById(colorId);
            if (colorInput && colorInput.value) hexInput.value = colorInput.value;
        });
        if (s.dot_gradient_rotation !== undefined && rotationVal) rotationVal.textContent = (s.dot_gradient_rotation || 0) + '°';
        if (s.logo_data !== undefined) {
            logoDataUrl = s.logo_data || null;
            const existingLogo = form.querySelector('input[name="existing_logo_data"]');
            if (existingLogo) existingLogo.value = s.logo_data || '';
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
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = p.name;
                    opt.dataset.settings = JSON.stringify(p.settings || {});
                    presetSelect.appendChild(opt);
                });
            });
        presetSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.value) {
                try {
                    applySettings(JSON.parse(opt.dataset.settings || '{}'));
                } catch (e) {}
            }
        });
    }

    if (presetSaveBtn && presetNameInput) {
        presetSaveBtn.addEventListener('click', function () {
            const name = presetNameInput.value.trim();
            if (!name) {
                alert('Bitte einen Namen für das Design eingeben.');
                return;
            }
            const formData = new FormData();
            formData.append('csrf_token', form.querySelector('input[name="csrf_token"]').value);
            formData.append('name', name);
            formData.append('settings', JSON.stringify(getFormSettings()));
            fetch('/api/presets/save', { method: 'POST', body: formData })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    const opt = document.createElement('option');
                    opt.value = data.id;
                    opt.textContent = name;
                    opt.dataset.settings = JSON.stringify(getFormSettings());
                    presetSelect.appendChild(opt);
                    presetNameInput.value = '';
                });
        });
    }
});
