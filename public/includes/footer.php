    </main>
    <footer class="site-footer">
        <div class="container footer-inner">
            <div class="footer-brand">
                <a href="/" class="logo">
                    <img class="logo-mark" src="/assets/img/logo.svg" alt="" width="32" height="32">
                    <span class="logo-text"><?= e($site['name']) ?></span>
                </a>
                <p><?= e(t('site.tagline')) ?></p>
            </div>
            <nav class="footer-links" aria-label="<?= e(t('footer.content')) ?>">
                <h2><?= e(t('footer.content')) ?></h2>
                <a href="/tools"><?= e(t('nav.tools')) ?></a>
                <a href="/guides"><?= e(t('nav.guides')) ?></a>
                <a href="/projects"><?= e(t('nav.projects')) ?></a>
            </nav>
            <nav class="footer-links" aria-label="<?= e(t('footer.site')) ?>">
                <h2><?= e(t('footer.site')) ?></h2>
                <a href="/about"><?= e(t('nav.about')) ?></a>
                <a href="/contact"><?= e(t('nav.contact')) ?></a>
            </nav>
        </div>
        <div class="container footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= e($site['name']) ?></p>
        </div>
    </footer>
</body>
</html>
