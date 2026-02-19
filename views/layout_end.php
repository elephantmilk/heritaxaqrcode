        </div>
    </main>
    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> QRCodeMan</p>
        </div>
    </footer>
    <?php if (!empty($loadQRLib)): ?>
    <script src="/assets/js/app.js"></script>
    <?php endif; ?>
</body>
</html>
