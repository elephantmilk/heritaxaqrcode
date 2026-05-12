<?php $pageTitle = 'Dashboard — ' . APP_NAME; $loadQRLib = true; require __DIR__ . '/layout.php'; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<div class="page-header">
    <h1>Deine QR-Codes</h1>
    <a href="/qrcode/new" class="btn btn-primary">+ Neuer QR-Code</a>
</div>

<?php if (!empty($qrcodes)): ?>
<div class="dashboard-search">
    <label for="qr-search" class="sr-only">QR-Codes durchsuchen</label>
    <input type="search" id="qr-search" class="search-input" placeholder="Suchen nach Titel, Code oder URL…" autocomplete="off">
    <span id="qr-search-count" class="search-count" aria-live="polite"></span>
</div>
<?php endif; ?>

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
    <div class="qr-grid" id="qr-grid">
        <?php foreach ($qrcodes as $qr): ?>
        <div class="qr-card" data-search-title="<?= e(mb_strtolower($qr['title'] ?? '')) ?>" data-search-code="<?= e(mb_strtolower($qr['short_code'] ?? '')) ?>" data-search-url="<?= e(mb_strtolower($qr['target_url'] ?? '')) ?>">
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
                 data-short-code="<?= e($qr['short_code']) ?>"
                 data-dot-gradient-enabled="<?= (int)($qr['dot_gradient_enabled'] ?? 0) ?>"
                 data-dot-gradient-type="<?= e($qr['dot_gradient_type'] ?? 'linear') ?>"
                 data-dot-gradient-rotation="<?= e($qr['dot_gradient_rotation'] ?? 0) ?>"
                 data-dot-gradient-color1="<?= e($qr['dot_gradient_color1'] ?? '#000000') ?>"
                 data-dot-gradient-color2="<?= e($qr['dot_gradient_color2'] ?? '#888888') ?>">
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

    <?php if (!empty($qrcodes) && isset($totalCount)): ?>
    <nav class="pagination" aria-label="Seitennavigation">
        <div class="pagination-info">
            <?php
            $from = $totalCount === 0 ? 0 : ($page - 1) * $perPage + 1;
            $to = min($page * $perPage, $totalCount);
            ?>
            <?= $from ?>–<?= $to ?> von <?= number_format($totalCount) ?>
        </div>
        <?php if ($totalPages > 1): ?>
        <div class="pagination-links">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="btn btn-sm btn-ghost" rel="prev">← Zurück</a>
            <?php endif; ?>
            <?php
            $range = 2;
            $start = max(1, $page - $range);
            $end = min($totalPages, $page + $range);
            if ($start > 1): ?>
                <a href="?page=1" class="pagination-num">1</a>
                <?php if ($start > 2): ?><span class="pagination-ellipsis">…</span><?php endif;
            endif;
            for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="pagination-num pagination-current" aria-current="page"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>" class="pagination-num"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor;
            if ($end < $totalPages): ?>
                <?php if ($end < $totalPages - 1): ?><span class="pagination-ellipsis">…</span><?php endif; ?>
                <a href="?page=<?= $totalPages ?>" class="pagination-num"><?= $totalPages ?></a>
            <?php endif; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="btn btn-sm btn-ghost" rel="next">Weiter →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
<?php endif; ?>

<script>
// Render QR previews on dashboard
function getDotsOptionsFromData(d) {
    var useGradient = d.dotGradientEnabled === '1' || d.dotGradientEnabled === 1;
    var type = d.dotStyle;
    if (useGradient && d.dotGradientType) {
        var rotationDeg = parseFloat(d.dotGradientRotation) || 0;
        return {
            type: type,
            gradient: {
                type: d.dotGradientType,
                rotation: rotationDeg * Math.PI / 180,
                colorStops: [
                    { offset: 0, color: d.dotGradientColor1 || '#000000' },
                    { offset: 1, color: d.dotGradientColor2 || '#888888' }
                ]
            }
        };
    }
    return { color: d.dotColor, type: type };
}

document.addEventListener('DOMContentLoaded', function() {
    var searchEl = document.getElementById('qr-search');
    var gridEl = document.getElementById('qr-grid');
    var countEl = document.getElementById('qr-search-count');
    if (searchEl && gridEl) {
        function updateSearch() {
            var q = (searchEl.value || '').trim().toLowerCase();
            var cards = gridEl.querySelectorAll('.qr-card');
            var visible = 0;
            cards.forEach(function(card) {
                var title = (card.getAttribute('data-search-title') || '');
                var code = (card.getAttribute('data-search-code') || '');
                var url = (card.getAttribute('data-search-url') || '');
                var match = !q || title.indexOf(q) !== -1 || code.indexOf(q) !== -1 || url.indexOf(q) !== -1;
                card.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            if (countEl) {
                countEl.textContent = q ? visible + ' von ' + cards.length : '';
            }
        }
        searchEl.addEventListener('input', updateSearch);
        searchEl.addEventListener('search', updateSearch);
    }

    document.querySelectorAll('[id^="qr-preview-"]').forEach(function(el) {
        const d = el.dataset;
        const baseUrl = window.location.origin;
        const qr = new QRCodeStyling({
            width: 160,
            height: 160,
            type: 'svg',
            data: baseUrl + '/' + d.shortCode,
            dotsOptions: getDotsOptionsFromData(d),
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
        dotsOptions: getDotsOptionsFromData(d),
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
