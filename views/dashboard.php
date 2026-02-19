<?php $pageTitle = 'Dashboard — ' . APP_NAME; $loadQRLib = true; require __DIR__ . '/layout.php'; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<div class="page-header">
    <h1>Deine QR-Codes</h1>
    <a href="/qrcode/new" class="btn btn-primary">+ Neuer QR-Code</a>
</div>

<?php if (empty($qrcodes)): ?>
    <div class="empty-state">
        <svg viewBox="0 0 32 32" width="64" height="64" fill="none" style="color: var(--text-secondary); margin-bottom: 1rem;">
            <rect x="1" y="1" width="12" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/>
            <rect x="19" y="1" width="12" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/>
            <rect x="1" y="19" width="12" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/>
            <rect x="4" y="4" width="6" height="6" rx="1" fill="currentColor" opacity="0.3"/>
            <rect x="22" y="4" width="6" height="6" rx="1" fill="currentColor" opacity="0.3"/>
            <rect x="4" y="22" width="6" height="6" rx="1" fill="currentColor" opacity="0.3"/>
        </svg>
        <h2>Noch keine QR-Codes</h2>
        <p class="text-muted">Erstelle deinen ersten dynamischen QR-Code.</p>
        <a href="/qrcode/new" class="btn btn-primary" style="margin-top: 1rem;">Jetzt erstellen</a>
    </div>
<?php else: ?>
    <div class="qr-grid">
        <?php foreach ($qrcodes as $qr): ?>
        <div class="qr-card">
            <div class="qr-card-preview" id="qr-preview-<?= $qr['id'] ?>"
                 data-url="<?= e($qr['target_url']) ?>"
                 data-dot-style="<?= e($qr['dot_style']) ?>"
                 data-dot-color="<?= e($qr['dot_color']) ?>"
                 data-bg-color="<?= e($qr['bg_color']) ?>"
                 data-corner-square-style="<?= e($qr['corner_square_style']) ?>"
                 data-corner-square-color="<?= e($qr['corner_square_color']) ?>"
                 data-corner-dot-style="<?= e($qr['corner_dot_style']) ?>"
                 data-corner-dot-color="<?= e($qr['corner_dot_color']) ?>"
                 data-logo="<?= e($qr['logo_data'] ?? '') ?>"
                 data-logo-size="<?= e($qr['logo_size']) ?>"
                 data-short-code="<?= e($qr['short_code']) ?>">
            </div>
            <div class="qr-card-body">
                <h3 class="qr-card-title"><?= e($qr['title'] ?: $qr['short_code']) ?></h3>
                <div class="qr-card-code">
                    <code>/<?= e($qr['short_code']) ?></code>
                </div>
                <a href="<?= e($qr['target_url']) ?>" class="qr-card-url" target="_blank" rel="noopener"><?= e(strlen($qr['target_url']) > 40 ? substr($qr['target_url'], 0, 40) . '...' : $qr['target_url']) ?></a>
                <div class="qr-card-stats">
                    <span class="stat">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <?= number_format($qr['scan_count']) ?> Scans
                    </span>
                    <span class="stat">
                        <?= date('d.m.Y', strtotime($qr['created_at'])) ?>
                    </span>
                </div>
            </div>
            <div class="qr-card-actions">
                <button class="btn btn-sm btn-ghost" onclick="downloadQR(<?= $qr['id'] ?>, 'svg')" title="SVG Download">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    SVG
                </button>
                <button class="btn btn-sm btn-ghost" onclick="downloadQR(<?= $qr['id'] ?>, 'pdf')" title="PDF Download">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    PDF
                </button>
                <a href="/qrcode/view/<?= e($qr['short_code']) ?>" class="btn btn-sm btn-ghost" title="Ansehen" target="_blank">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </a>
                <a href="/qrcode/edit/<?= $qr['id'] ?>" class="btn btn-sm btn-ghost" title="Bearbeiten">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </a>
                <form method="POST" action="/qrcode/delete/<?= $qr['id'] ?>" class="inline" onsubmit="return confirm('QR-Code wirklich löschen?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-ghost btn-danger" title="Löschen">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
// Render QR previews on dashboard
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[id^="qr-preview-"]').forEach(function(el) {
        const d = el.dataset;
        const baseUrl = window.location.origin;
        const qr = new QRCodeStyling({
            width: 160,
            height: 160,
            type: 'svg',
            data: baseUrl + '/' + d.shortCode,
            dotsOptions: { color: d.dotColor, type: d.dotStyle },
            backgroundOptions: { color: d.bgColor },
            cornersSquareOptions: { type: d.cornerSquareStyle, color: d.cornerSquareColor },
            cornersDotOptions: { type: d.cornerDotStyle, color: d.cornerDotColor },
            image: d.logo || undefined,
            imageOptions: { crossOrigin: 'anonymous', margin: 4, imageSize: parseFloat(d.logoSize) || 0.4, hideBackgroundDots: true },
            qrOptions: { errorCorrectionLevel: 'H' }
        });
        qr.append(el);
        el._qrInstance = qr;
    });
});

function buildQRConfig(d) {
    var baseUrl = window.location.origin;
    return {
        width: 1024, height: 1024, type: 'svg',
        data: baseUrl + '/' + d.shortCode,
        dotsOptions: { color: d.dotColor, type: d.dotStyle },
        backgroundOptions: { color: d.bgColor },
        cornersSquareOptions: { type: d.cornerSquareStyle, color: d.cornerSquareColor },
        cornersDotOptions: { type: d.cornerDotStyle, color: d.cornerDotColor },
        image: d.logo || undefined,
        imageOptions: { crossOrigin: 'anonymous', margin: 10, imageSize: parseFloat(d.logoSize) || 0.4, hideBackgroundDots: true },
        qrOptions: { errorCorrectionLevel: 'H' }
    };
}

function downloadQR(id, format) {
    var el = document.getElementById('qr-preview-' + id);
    if (!el) return;
    var d = el.dataset;
    var config = buildQRConfig(d);

    if (format === 'pdf') {
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
                pdf.save('qrcode-' + d.shortCode + '.pdf');
            };
            reader.readAsDataURL(blob);
        }).catch(function (err) { console.error('PDF error:', err); });
    } else {
        var qrDownload = new QRCodeStyling(config);
        qrDownload.download({ name: 'qrcode-' + d.shortCode, extension: 'svg' });
    }
}
</script>

<?php require __DIR__ . '/layout_end.php'; ?>
