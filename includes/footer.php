    </main>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> XO Arena. Built by Kai.</p>
    </footer>

    <script src="<?php echo BASE_URL; ?>assets/script.js"></script>
    <?php if (isset($extraScript)): ?>
        <script src="<?php echo BASE_URL . $extraScript; ?>"></script>
    <?php endif; ?>
</body>
</html>
