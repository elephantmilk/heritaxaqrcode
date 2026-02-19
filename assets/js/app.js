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

    // Initialize QR code
    const qrCode = new QRCodeStyling({
        width: 300,
        height: 300,
        type: 'svg',
        data: getData(),
        dotsOptions: {
            color: document.getElementById('dot_color').value,
            type: document.getElementById('dot_style').value
        },
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
            dotsOptions: {
                color: document.getElementById('dot_color').value,
                type: document.getElementById('dot_style').value
            },
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
            dotsOptions: {
                color: document.getElementById('dot_color').value,
                type: document.getElementById('dot_style').value
            },
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
});
