<?php
/**
 * SisFrota Municipal - Dashboard View (stub para Fase 3)
 * Implementação completa na Fase 8.
 */
defined('SYS_ENTRY') or die('Acesso direto negado.');

$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="card" style="text-align: center; padding: 3rem;">
    <div style="font-size: 3rem; margin-bottom: 1rem;">🚗</div>
    <h2 style="color: #16a34a; font-size: 1.75rem; font-weight: 700;">Bem-vindo ao SisFrota Municipal</h2>
    <p style="color: #475569; margin-top: 0.75rem; font-size: 1rem;">
        Olá, <strong><?= htmlspecialchars($user['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>!
        O painel de indicadores será implementado na Fase 8.
    </p>
    <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <?php if (in_array($user['role'] ?? '', ['ADMIN', 'OPERATOR'], true)): ?>
            <a href="<?= $baseUrl ?>/vehicles" class="btn-primary">Gerenciar Veículos</a>
        <?php endif; ?>
        <?php if (in_array($user['role'] ?? '', ['ADMIN', 'MANAGER'], true)): ?>
            <a href="<?= $baseUrl ?>/reports" class="btn-secondary">Relatórios</a>
        <?php endif; ?>
    </div>
</div>
