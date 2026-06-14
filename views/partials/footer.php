    </div>
</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <p class="footer-brand">🏁 <?= APP_NAME ?></p>
        <p class="footer-tagline">La référence des événements automobiles en France.</p>
        <p class="footer-copy">&copy; <?= date('Y') ?> <?= APP_NAME ?>. Tous droits réservés.</p>
    </div>
</footer>

<?php if (!empty($extraJs)): ?>
    <?php foreach ($extraJs as $js): ?>
        <script src="<?= APP_URL ?>/public/js/<?= e($js) ?>" defer></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
