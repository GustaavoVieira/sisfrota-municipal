<?php
/**
 * SisFrota Municipal - Helper de Autenticação, Sessão e RBAC
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

class AuthHelper
{
    private static int $sessionLifetime = 1800; // 30 minutos em segundos

    /**
     * Inicializa a sessão com parâmetros de segurança se ainda não estiver ativa.
     */
    public static function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $lifetime = self::$sessionLifetime;

            // Configurações de segurança dos cookies de sessão
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.gc_maxlifetime', (string)$lifetime);

            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path'     => '/',
                'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'), // true somente em HTTPS
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            session_start();
        }

        // Verifica expiração de sessão por inatividade
        if (self::isLoggedIn()) {
            if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > self::$sessionLifetime)) {
                self::logout();
            } else {
                $_SESSION['last_activity'] = time();
            }
        }
    }

    /**
     * Efetua o login do usuário salvando seus dados na sessão e regenerando a ID.
     */
    public static function login(array $user): void
    {
        self::initSession();
        session_regenerate_id(true);

        $_SESSION['user_id']       = $user['id'];
        $_SESSION['username']      = $user['username'];
        $_SESSION['full_name']     = $user['full_name'];
        $_SESSION['role']          = $user['role'];
        $_SESSION['last_activity'] = time();

        self::generateCsrfToken(true);
    }

    /**
     * Encerra a sessão do usuário.
     */
    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
    }

    /**
     * Verifica se há um usuário autenticado.
     */
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Retorna os dados do usuário conectado.
     */
    public static function user(): ?array
    {
        if (!self::isLoggedIn()) {
            return null;
        }

        return [
            'id'        => $_SESSION['user_id'] ?? null,
            'username'  => $_SESSION['username'] ?? '',
            'full_name' => $_SESSION['full_name'] ?? '',
            'role'      => $_SESSION['role'] ?? ''
        ];
    }

    /**
     * Verifica se o usuário logado possui um dos perfis informados.
     */
    public static function hasRole(array|string $roles): bool
    {
        if (!self::isLoggedIn()) {
            return false;
        }

        $userRole = $_SESSION['role'] ?? '';
        $allowedRoles = is_array($roles) ? $roles : [$roles];

        return in_array($userRole, $allowedRoles, true);
    }

    /**
     * Exige que o usuário esteja autenticado. Caso contrário, redireciona para a tela de login.
     */
    public static function requireLogin(): void
    {
        self::initSession();

        if (!self::isLoggedIn()) {
            $loginUrl = defined('BASE_URL') ? BASE_URL . '/login' : '/login';
            header("Location: " . $loginUrl);
            exit;
        }
    }

    /**
     * Exige que o usuário esteja autenticado e possua um perfil específico.
     * Caso contrário, dispara resposta HTTP 403 Forbidden.
     */
    public static function requireRole(array|string $roles): void
    {
        self::requireLogin();

        if (!self::hasRole($roles)) {
            http_response_code(403);
            $viewsPath = defined('VIEWS_PATH') ? VIEWS_PATH : __DIR__ . '/../views';
            $errorFile = $viewsPath . '/errors/403.php';

            if (file_exists($errorFile)) {
                require_once $errorFile;
            } else {
                echo "<h1>403 Forbidden</h1><p>Acesso negado: seu perfil não possui permissão para acessar esta página.</p>";
            }
            exit;
        }
    }

    /**
     * Gera e armazena token CSRF.
     */
    public static function generateCsrfToken(bool $forceNew = false): string
    {
        self::initSession();

        if ($forceNew || empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Valida um token CSRF enviado via POST.
     */
    public static function verifyCsrfToken(?string $token): bool
    {
        self::initSession();

        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
