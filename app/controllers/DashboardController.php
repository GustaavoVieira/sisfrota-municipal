<?php
/**
 * SisFrota Municipal - Controller do Dashboard (stub para Fase 3)
 * A implementação completa ocorrerá na Fase 8.
 */
defined('SYS_ENTRY') or die('Acesso direto negado.');

require_once __DIR__ . '/../helpers/AuthHelper.php';

class DashboardController
{
    /**
     * Exibe a tela principal do dashboard.
     * Qualquer usuário autenticado pode acessar.
     */
    public function index(): void
    {
        AuthHelper::requireLogin();

        $user      = AuthHelper::user();
        $pageTitle = 'Dashboard';

        require_once VIEWS_PATH . '/layouts/header.php';
        require_once VIEWS_PATH . '/dashboard/index.php';
        require_once VIEWS_PATH . '/layouts/footer.php';
    }
}
