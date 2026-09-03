<?php
/**
 * SisFrota Municipal - Layout: Sidebar (menu lateral)
 * Renderiza itens de menu conforme o perfil RBAC do usuário logado.
 */
defined('SYS_ENTRY') or die('Acesso direto negado.');

require_once APP_PATH . '/helpers/AuthHelper.php';

$user     = AuthHelper::user();
$role     = $user['role'] ?? '';
$baseUrl  = defined('BASE_URL') ? BASE_URL : '';

// Identifica a rota atual para marcar o item de menu como "active"
$currentRoute = $_GET['route'] ?? 'dashboard';

/**
 * Retorna classe 'active' se a rota atual iniciar com o prefixo informado.
 */
function navClass(string $prefix, string $current): string
{
    return str_starts_with($current, $prefix) ? 'nav-item active' : 'nav-item';
}

// Rótulos de perfil em português
$roleLabels = [
    'ADMIN'    => 'Administrador',
    'OPERATOR' => 'Operador',
    'MANAGER'  => 'Gestor',
];
$roleCssClasses = [
    'ADMIN'    => 'user-role-admin',
    'OPERATOR' => 'user-role-operator',
    'MANAGER'  => 'user-role-manager',
];
$roleLabel    = $roleLabels[$role]         ?? $role;
$roleCssClass = $roleCssClasses[$role]     ?? '';
?>
<aside class="app-sidebar">
    <!-- Cabeçalho da Sidebar com logo e nome do sistema -->
    <div class="sidebar-header">
        <img src="<?= $baseUrl ?>/assets/img/logo.png"
             alt="Prefeitura Municipal"
             onerror="this.style.display='none'">
        <div>
            <div class="sidebar-brand-title">SisFrota</div>
            <div class="sidebar-brand-subtitle">Gestão de Frota Municipal</div>
        </div>
    </div>

    <!-- Navegação principal -->
    <nav class="sidebar-nav">

        <!-- Módulos visíveis para TODOS os perfis autenticados -->
        <div class="nav-section-title">Painel</div>
        <a href="<?= $baseUrl ?>/dashboard" class="<?= navClass('dashboard', $currentRoute) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>

        <!-- Veículos: todos podem ver a listagem -->
        <div class="nav-section-title">Frota</div>
        <a href="<?= $baseUrl ?>/vehicles" class="<?= navClass('vehicles', $currentRoute) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3m-4 12h8a2 2 0 0 0 2-2v-5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2Z"/><circle cx="7.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
            Veículos
        </a>

        <div class="nav-section-title">Operações</div>
        <?php if (in_array($role, ['ADMIN', 'OPERATOR'], true)): ?>
        <a href="<?= $baseUrl ?>/fuelings" class="<?= navClass('fuelings', $currentRoute) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 22V9l7-7 7 7v13"/><path d="M10 22v-5h4v5"/><path d="M14 8h2l2 2v3"/><circle cx="18" cy="16" r="2"/></svg>
            Abastecimentos
        </a>
        <?php endif; ?>
        <?php if (in_array($role, ['ADMIN', 'OPERATOR', 'MANAGER'], true)): ?>
        <a href="<?= $baseUrl ?>/maintenances" class="<?= navClass('maintenances', $currentRoute) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
            Manutenções
        </a>
        <?php endif; ?>

        <!-- Relatórios: ADMIN e MANAGER -->
        <?php if (in_array($role, ['ADMIN', 'MANAGER'], true)): ?>
        <div class="nav-section-title">Relatórios</div>
        <a href="<?= $baseUrl ?>/reports" class="<?= navClass('reports', $currentRoute) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Relatórios
        </a>
        <?php endif; ?>

        <!-- Configurações: somente ADMIN -->
        <?php if ($role === 'ADMIN'): ?>
        <div class="nav-section-title">Configurações</div>
        <a href="<?= $baseUrl ?>/settings/users" class="<?= navClass('settings/users', $currentRoute) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Usuários
        </a>
        <a href="<?= $baseUrl ?>/settings/categories" class="<?= navClass('settings/categories', $currentRoute) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            Categorias
        </a>
        <a href="<?= $baseUrl ?>/settings/secretariats" class="<?= navClass('settings/secretariats', $currentRoute) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Secretarias
        </a>
        <?php endif; ?>

    </nav>

    <!-- Rodapé da Sidebar: perfil do usuário e link de logout -->
    <div class="sidebar-footer">
        <div class="user-profile">
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($user['full_name'] ?? 'Usuário', ENT_QUOTES, 'UTF-8') ?></span>
                <span class="user-role-badge <?= htmlspecialchars($roleCssClass, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>
            <a href="<?= $baseUrl ?>/logout"
               title="Sair do sistema"
               style="color: #ef4444; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                Sair
            </a>
        </div>
    </div>
</aside>
