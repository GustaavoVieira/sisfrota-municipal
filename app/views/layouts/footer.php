<?php
/**
 * SisFrota Municipal - Layout: Footer (encerra o bloco de conteúdo principal)
 */
defined('SYS_ENTRY') or die('Acesso direto negado.');

$appName = htmlspecialchars($config['app']['name'] ?? 'SisFrota Municipal', ENT_QUOTES, 'UTF-8');
?>
        </main><!-- /app-content -->

        <footer class="app-footer">
            &copy; <?= date('Y') ?> <?= $appName ?> &mdash; Prefeitura Municipal &mdash; Versão 1.0
        </footer>
    </div><!-- /app-main -->
</div><!-- /app-container -->

<script src="<?= defined('BASE_URL') ? BASE_URL : '' ?>/assets/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($extraScripts)): ?>
    <?php foreach ($extraScripts as $script): ?>
        <script src="<?= htmlspecialchars($script, ENT_QUOTES, 'UTF-8') ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
<?php if (!empty($inlineScript)): ?>
<script>
<?= $inlineScript ?>
</script>
<?php endif; ?>
</body>
</html>
