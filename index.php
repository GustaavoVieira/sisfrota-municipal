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
define('ROOT_PATH',   __DIR__);
define('APP_PATH',    ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEWS_PATH',  APP_PATH  . '/views');

// ─── 5. URL base — sempre detectada dinamicamente ────────────────────────────
// Garante funcionamento independente da subpasta instalada no servidor.
// Ex.: http://localhost/ProjetoPrefeitura  ou  https://dominio.com
$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host    = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Pega o diretório onde index.php está instalado (relativo à raiz do servidor)
// Ex.: SCRIPT_NAME = /ProjetoPrefeitura/index.php → subdir = /ProjetoPrefeitura
$subdir  = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'), '/\\');

define('BASE_URL', $scheme . '://' . $host . $subdir);

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

// ─── 8. Extração da rota ──────────────────────────────────────────────────────
// REQUEST_URI pode conter query string: /ProjetoPrefeitura/vehicles?page=2
// Removemos o prefixo da subpasta e a query string para obter apenas a rota limpa.
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$urlPath    = parse_url($requestUri, PHP_URL_PATH); // sem query string
// Remove o prefixo da subpasta (ex: /ProjetoPrefeitura) e a barra inicial
$route = ltrim(substr($urlPath, strlen($subdir)), '/');
$route = ($route === '' || $route === 'index.php') ? 'dashboard' : $route;

// Armazena a rota atual para que a sidebar possa marcar o item ativo
$_GET['route'] = $route;

// Quebra em segmentos: "vehicles/edit/42" → ['vehicles', 'edit', '42']
$segments = explode('/', $route);
$resource = $segments[0] ?? '';
$action   = $segments[1] ?? 'index';
$param    = $segments[2] ?? null;

// ─── 9. Roteamento ───────────────────────────────────────────────────────────
try {

    switch ($resource) {

        // ── Autenticação ──────────────────────────────────────────────────────
        case 'login':
            require_once APP_PATH . '/controllers/AuthController.php';
            (new AuthController())->login();
            break;

        case 'logout':
            require_once APP_PATH . '/controllers/AuthController.php';
            (new AuthController())->logout();
            break;

        // ── Dashboard (raiz redireciona para dashboard quando logado) ─────────
        case 'dashboard':
            require_once APP_PATH . '/controllers/DashboardController.php';
            (new DashboardController())->index();
            break;

        // ── Veículos (stub — Fase 5) ──────────────────────────────────────────
        case 'vehicles':
            AuthHelper::requireLogin();
            $user = AuthHelper::user();
            $pageTitle = 'Veículos';
            require_once VIEWS_PATH . '/layouts/header.php';
            echo '<div class="card"><p>Módulo de Veículos — implementação na Fase 5.</p></div>';
            require_once VIEWS_PATH . '/layouts/footer.php';
            break;

        // ── Abastecimentos (stub — Fase 6) ────────────────────────────────────
        case 'fuelings':
            AuthHelper::requireRole(['ADMIN', 'OPERATOR']);
            $user = AuthHelper::user();
            $pageTitle = 'Abastecimentos';
            require_once VIEWS_PATH . '/layouts/header.php';
            echo '<div class="card"><p>Módulo de Abastecimentos — implementação na Fase 6.</p></div>';
            require_once VIEWS_PATH . '/layouts/footer.php';
            break;

        // ── Manutenções (stub — Fase 7) ───────────────────────────────────────
        case 'maintenances':
            AuthHelper::requireRole(['ADMIN', 'OPERATOR']);
            $user = AuthHelper::user();
            $pageTitle = 'Manutenções';
            require_once VIEWS_PATH . '/layouts/header.php';
            echo '<div class="card"><p>Módulo de Manutenções — implementação na Fase 7.</p></div>';
            require_once VIEWS_PATH . '/layouts/footer.php';
            break;

        // ── Relatórios (stub — Fase 9) ────────────────────────────────────────
        case 'reports':
            AuthHelper::requireRole(['ADMIN', 'MANAGER']);
            $user = AuthHelper::user();
            $pageTitle = 'Relatórios';
            require_once VIEWS_PATH . '/layouts/header.php';
            echo '<div class="card"><p>Módulo de Relatórios — implementação na Fase 9.</p></div>';
            require_once VIEWS_PATH . '/layouts/footer.php';
            break;

        // ── Configurações (Fase 4: Secretarias, Categorias e Usuários) ────────
        case 'settings':
            AuthHelper::requireRole(['ADMIN']);
            $subResource = $segments[1] ?? 'users';
            $subAction   = $segments[2] ?? 'index';

            switch ($subResource) {
                case 'secretariats':
                    require_once APP_PATH . '/controllers/SecretariatController.php';
                    $controller = new SecretariatController();
                    if ($subAction === 'store') {
                        $controller->store();
                    } elseif ($subAction === 'update') {
                        $controller->update();
                    } elseif ($subAction === 'delete') {
                        $controller->delete();
                    } else {
                        $controller->index();
                    }
                    break;

                case 'categories':
                    require_once APP_PATH . '/controllers/MaintenanceCategoryController.php';
                    $controller = new MaintenanceCategoryController();
                    if ($subAction === 'store') {
                        $controller->store();
                    } elseif ($subAction === 'update') {
                        $controller->update();
                    } elseif ($subAction === 'toggle') {
                        $controller->toggle();
                    } else {
                        $controller->index();
                    }
                    break;

                case 'users':
                default:
                    require_once APP_PATH . '/controllers/UserController.php';
                    $controller = new UserController();
                    if ($subAction === 'store') {
                        $controller->store();
                    } elseif ($subAction === 'update') {
                        $controller->update();
                    } elseif ($subAction === 'password') {
                        $controller->updatePassword();
                    } else {
                        $controller->index();
                    }
                    break;
            }
            break;

        // ── Rota raiz: redireciona para dashboard (se logado) ou login ────────
        case '':
            if (AuthHelper::isLoggedIn()) {
                header('Location: ' . BASE_URL . '/dashboard');
            } else {
                header('Location: ' . BASE_URL . '/login');
            }
            exit;

        // ── Rota não encontrada (404) ─────────────────────────────────────────
        default:
            http_response_code(404);
            if (AuthHelper::isLoggedIn()) {
                $user = AuthHelper::user();
                $pageTitle = 'Página não encontrada';
                require_once VIEWS_PATH . '/layouts/header.php';
                echo '<div class="card" style="text-align:center;padding:3rem;">
                        <div style="font-size:3rem;">🔍</div>
                        <h2 style="margin-top:1rem;">Página não encontrada</h2>
                        <p style="color:#475569;margin:1rem 0 2rem;">A rota <code>' . htmlspecialchars($route, ENT_QUOTES, 'UTF-8') . '</code> não existe no sistema.</p>
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
    // Fallback de erro: mensagem amigável ao usuário + log em arquivo (sem expor detalhes)
    $logFile = ROOT_PATH . '/logs/system_error.log';
    $logLine = sprintf(
        "[%s] CRITICAL | IP: %s | URI: %s | Erro: %s em %s:%d\n",
        date('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR'] ?? 'CLI',
        $_SERVER['REQUEST_URI'] ?? '-',
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );
    if ($config['logs']['enabled'] ?? true) {
        @file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }

    http_response_code(500);
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }

    $baseUrlSafe = defined('BASE_URL') ? BASE_URL : '';
    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Erro Interno — SisFrota</title>
    <style>body{font-family:sans-serif;background:#f8fafc;display:flex;justify-content:center;align-items:center;min-height:100vh;}
    .err{background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:3rem 2rem;text-align:center;max-width:480px;}
    h2{color:#ef4444;margin-bottom:1rem;}</style></head><body>
    <div class="err">
        <h2>Falha Técnica</h2>
        <p>Ocorreu uma falha técnica ao processar a solicitação. O erro foi registrado para análise da equipe de TI.</p>
        <p style="margin-top:1.5rem;"><a href="' . $baseUrlSafe . '/login" style="color:#16a34a;font-weight:600;">Voltar ao Login</a></p>
    </div></body></html>';
}
