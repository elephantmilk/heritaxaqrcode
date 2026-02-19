<?php $pageTitle = e($qr['title'] ?: $qr['short_code']) . ' — ' . APP_NAME; ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://unpkg.com/qr-code-styling@1.6.0-rc.1/lib/qr-code-styling.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        .view-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
            gap: 1.5rem;
        }
        .view-qr { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); padding: 2rem; }
        .view-qr svg { max-width: 400px; max-height: 400px; }
        .view-title { font-size: 1.25rem; font-weight: 600; text-align: center; }
        .view-url { color: var(--text-muted); font-size: 0.85rem; text-align: center; word-break: break-all; }
        .view-actions { display: flex; gap: 0.75rem; flex-wrap: wrap; justify-content: center; }
    </style>
</head>
<body>
    <div class="view-wrapper">
        <div class="view-title"><?= e($qr['title'] ?: $qr['short_code']) ?></div>
        <div class="view-qr" id="qr-container"></div>
        <div class="view-url"><?= e($qr['target_url']) ?></div>
        <div class="view-actions">
            <button id="download-svg" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                SVG herunterladen
            </button>
            <button id="download-pdf" class="btn btn-ghost">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                PDF herunterladen
            </button>
            <a href="/<?= e($qr['short_code']) ?>" class="btn btn-ghost" target="_blank">Link testen</a>
        </div>
    </div>

    <script>
    (function() {
        var baseUrl = window.location.origin;
        var data = <?= json_encode([
            'short_code' => $qr['short_code'],
            'dot_style' => $qr['dot_style'],
            'dot_color' => $qr['dot_color'],
            'bg_color' => $qr['bg_color'],
            'corner_square_style' => $qr['corner_square_style'],
            'corner_square_color' => $qr['corner_square_color'],
            'corner_dot_style' => $qr['corner_dot_style'],
            'corner_dot_color' => $qr['corner_dot_color'],
            'logo_data' => $qr['logo_data'],
            'logo_size' => (float)$qr['logo_size'],
        ]) ?>;

        function dlConfig() {
            return {
                width: 1024, height: 1024, type: 'svg',
                data: baseUrl + '/' + data.short_code,
                dotsOptions: { color: data.dot_color, type: data.dot_style },
                backgroundOptions: { color: data.bg_color },
                cornersSquareOptions: { type: data.corner_square_style, color: data.corner_square_color },
                cornersDotOptions: { type: data.corner_dot_style, color: data.corner_dot_color },
                image: data.logo_data || undefined,
                imageOptions: { crossOrigin: 'anonymous', margin: 20, imageSize: data.logo_size, hideBackgroundDots: true },
                qrOptions: { errorCorrectionLevel: 'H' }
            };
        }

        var qr = new QRCodeStyling({
            width: 400, height: 400, type: 'svg',
            data: baseUrl + '/' + data.short_code,
            dotsOptions: { color: data.dot_color, type: data.dot_style },
            backgroundOptions: { color: data.bg_color },
            cornersSquareOptions: { type: data.corner_square_style, color: data.corner_square_color },
            cornersDotOptions: { type: data.corner_dot_style, color: data.corner_dot_color },
            image: data.logo_data || undefined,
            imageOptions: { crossOrigin: 'anonymous', margin: 8, imageSize: data.logo_size, hideBackgroundDots: true },
            qrOptions: { errorCorrectionLevel: 'H' }
        });

        qr.append(document.getElementById('qr-container'));

        document.getElementById('download-svg').addEventListener('click', function() {
            var qrDl = new QRCodeStyling(dlConfig());
            qrDl.download({ name: 'qrcode-' + data.short_code, extension: 'svg' });
        });

        document.getElementById('download-pdf').addEventListener('click', function() {
            var btn = this;
            btn.disabled = true;
            btn.textContent = 'Erstelle PDF...';

            var config = dlConfig();
            config.type = 'canvas';
            config.width = 2048;
            config.height = 2048;

            var qrPdf = new QRCodeStyling(config);
            qrPdf.getRawData('png').then(function (blob) {
                var reader = new FileReader();
                reader.onload = function () {
                    var size = 100;
                    var pdf = new jspdf.jsPDF({ orientation: 'portrait', unit: 'mm', format: [size, size] });
                    pdf.addImage(reader.result, 'PNG', 0, 0, size, size);
                    pdf.save('qrcode-' + data.short_code + '.pdf');
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
    })();
    </script>
</body>
</html>
