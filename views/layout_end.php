        </div>
    </main>
    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> HeritaxaQR</p>
        </div>
    </footer>
    <?php
    $jsBase = __DIR__ . '/../assets/js/';
    $vApp = file_exists($jsBase . 'app.js') ? filemtime($jsBase . 'app.js') : 0;
    $vBatch = file_exists($jsBase . 'batch.js') ? filemtime($jsBase . 'batch.js') : 0;
    ?>
    <?php if (!empty($loadQRLib) && empty($loadBatchJS)): ?>
    <script src="/assets/js/app.js?v=<?= $vApp ?>"></script>
    <?php endif; ?>
    <?php if (!empty($loadBatchJS)): ?>
    <script src="/assets/js/batch.js?v=<?= $vBatch ?>"></script>
    <?php endif; ?>
</body>
</html>
