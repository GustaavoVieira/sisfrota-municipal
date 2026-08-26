<?php
/**
 * SisFrota Municipal - Ponto de Entrada Único (Bootstrap & Router)
 * Fase 3: Roteador central com suporte a autenticação e RBAC.
 */

// ─── 1. Constante de segurança ────────────────────────────────────────────────
define('SYS_ENTRY', true);

// ─── 2. Carregamento das configurações ───────────────────────────────────────
$config = require_once __DIR__ . '/config/config.php';

// ─── 3. Fuso horário obrigatório ─────────────────────────────────────────────
date_default_timezone_set($config['app']['timezone'] ?? 'America/Sao_Paulo');

// ─── 4. Constantes de caminhos ───────────────────────────────────────────────
define('ROOT_PATH',  __DIR__);
define('APP_PATH',   ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEWS_PATH', APP_PATH  . '/views');

// ─── 5. URL base do sistema ──────────────────────────────────────────────────
// Detecta automaticamente a URL base (ex: http://localhost/ProjetoPrefeitura)
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
define('BASE_URL', rtrim($config['app']['url'] ?? ('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $scriptDir), '/'));

// ─── 6. Autoloader simples para models, controllers e helpers ────────────────
spl_autoload_register(function (string $className): void {
    $paths = [
        APP_PATH . '/models/'      . $className . '.php',
        APP_PATH . '/controllers/' . $className . '.php',
        APP_PATH . '/helpers/'     . $className . '.php',
    ];
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ─── 7. Inicialização da sessão ───────────────────────────────────────────────
require_once APP_PATH . '/helpers/AuthHelper.php';
AuthHelper::initSession();

// ─── 8. Roteador ─────────────────────────────────────────────────────────────
// Extrai a rota da URL após o diretório base (sem query string)
$requestUri  = $_SERVER['REQUEST_URI'] ?? '/';
$basePath    = $scriptDir;                          // ex: /ProjetoPrefeitura
$path        = parse_url($requestUri, PHP_URL_PATH);
$route       = ltrim(substr($path, strlen($basePath)), '/'); // ex: "login" ou "vehicles/new"
$route       = $route === '' ? 'dashboard' : $route;

// Armazena a rota para uso na Sidebar (highlight do menu ativo)
$_GET['route'] = $route;

// Quebra a rota em segmentos
$segments = explode('/', $route);
$resource = $segments[0] ?? '';                    // ex: "vehicles"
$action   = $segments[1] ?? 'index';              // ex: "new", "edit", "show"
$param    = $segments[2] ?? null;                 // ex: "42" (ID)

// ─── Definição das Rotas ─────────────────────────────────────────────────────
try {

    switch ($resource) {

        // ── Autenticação ──────────────────────────────────────────────────────
        case '':
        case 'login':
            require_once APP_PATH . '/controllers/AuthController.php';
            $controller = new AuthController();
            $controller->login();
            break;

        case 'logout':
            require_once APP_PATH . '/controllers/AuthController.php';
            $controller = new AuthController();
            $controller->logout();
            break;

        // ── Dashboard ─────────────────────────────────────────────────────────
        case 'dashboard':
            require_once APP_PATH . '/controllers/DashboardController.php';
            $controller = new DashboardController();
            $controller->index();
            break;

        // ── Veículos (stub - Fase 5) ──────────────────────────────────────────
        case 'vehicles':
            AuthHelper::requireLogin();
            // Stub temporário até a Fase 5
            $pageTitle = 'Veículos';
            $user = AuthHelper::user();
            require_once VIEWS_PATH . '/layouts/header.php';
            echo '<div class="card"><p>Módulo de Veículos — implementação na Fase 5.</p></div>';
            require_once VIEWS_PATH . '/layouts/footer.php';
            break;

        // ── Abastecimentos (stub - Fase 6) ────────────────────────────────────
        case 'fuelings':
            AuthHelper::requireRole(['ADMIN', 'OPERATOR']);
            $pageTitle = 'Abastecimentos';
            $user = AuthHelper::user();
            require_once VIEWS_PATH . '/layouts/header.php';
            echo '<div class="card"><p>Módulo de Abastecimentos — implementação na Fase 6.</p></div>';
            require_once VIEWS_PATH . '/layouts/footer.php';
            break;

        // ── Manutenções (stub - Fase 7) ───────────────────────────────────────
        case 'maintenances':
            AuthHelper::requireRole(['ADMIN', 'OPERATOR']);
            $pageTitle = 'Manutenções';
            $user = AuthHelper::user();
            require_once VIEWS_PATH . '/layouts/header.php';
            echo '<div class="card"><p>Módulo de Manutenções — implementação na Fase 7.</p></div>';
            require_once VIEWS_PATH . '/layouts/footer.php';
            break;

        // ── Relatórios (stub - Fase 9) ────────────────────────────────────────
        case 'reports':
            AuthHelper::requireRole(['ADMIN', 'MANAGER']);
            $pageTitle = 'Relatórios';
            $user = AuthHelper::user();
            require_once VIEWS_PATH . '/layouts/header.php';
            echo '<div class="card"><p>Módulo de Relatórios — implementação na Fase 9.</p></div>';
            require_once VIEWS_PATH . '/layouts/footer.php';
            break;

        // ── Configurações (stub - Fase 4) ─────────────────────────────────────
        case 'settings':
            AuthHelper::requireRole(['ADMIN']);
            $pageTitle  = 'Configurações';
            $user = AuthHelper::user();
            require_once VIEWS_PATH . '/layouts/header.php';
            echo '<div class="card"><p>Módulo de Configurações — implementação na Fase 4.</p></div>';
            require_once VIEWS_PATH . '/layouts/footer.php';
            break;

        // ── Rota não encontrada (404) ─────────────────────────────────────────
        default:
            http_response_code(404);
            if (AuthHelper::isLoggedIn()) {
                $pageTitle = 'Página não encontrada';
                $user = AuthHelper::user();
                require_once VIEWS_PATH . '/layouts/header.php';
                echo '<div class="card" style="text-align:center;padding:3rem;">
                        <div style="font-size:3rem;">🔍</div>
                        <h2 style="margin-top:1rem;">Página não encontrada</h2>
                        <p style="color:#475569;margin:1rem 0;">A rota solicitada não existe no sistema.</p>
                        <a href="' . BASE_URL . '/dashboard" class="btn-primary">Voltar ao Dashboard</a>
                      </div>';
                require_once VIEWS_PATH . '/layouts/footer.php';
            } else {
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
            break;
    }

} catch (Throwable $e) {
    // Fallback de erro: mensagem amigável + log em arquivo
    $logFile = ROOT_PATH . '/logs/system_error.log';
    $logMessage = sprintf(
        "[%s] CRITICAL | IP: %s | URI: %s | Erro: %s | Arquivo: %s | Linha: %d\n",
        date('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR'] ?? 'CLI',
        $_SERVER['REQUEST_URI'] ?? '-',
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );
    if ($config['logs']['enabled'] ?? true) {
        @file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    // Exibe mensagem genérica sem expor detalhes ao usuário
    http_response_code(500);
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Erro Interno</title>
    <style>body{font-family:sans-serif;background:#f8fafc;display:flex;justify-content:center;align-items:center;min-height:100vh;}
    .err{background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:3rem;text-align:center;max-width:480px;}
    h2{color:#ef4444;}</style></head><body>
    <div class="err"><h2>Falha Técnica</h2>
    <p>Ocorreu uma falha técnica ao processar a solicitação. O erro foi registrado para análise da equipe de TI.</p>
    <a href="' . (defined('BASE_URL') ? BASE_URL : '') . '/dashboard" style="color:#16a34a;font-weight:600;">Voltar ao Painel</a>
    </div></body></html>';
}
