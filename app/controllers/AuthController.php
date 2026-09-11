<?php
/**
 * SisFrota Municipal - Controller de Autenticação (Login / Logout)
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../models/SystemLog.php';

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Exibe a tela de login (GET) ou processa a tentativa de autenticação (POST).
     */
    public function login(): void
    {
        AuthHelper::initSession();

        // Se o usuário já estiver logado, redireciona diretamente para o Dashboard
        if (AuthHelper::isLoggedIn()) {
            $dashboardUrl = defined('BASE_URL') ? BASE_URL . '/dashboard' : '/dashboard';
            header("Location: " . $dashboardUrl);
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username  = trim($_POST['username'] ?? '');
            $password  = $_POST['password'] ?? '';
            $csrfToken = $_POST['csrf_token'] ?? '';

            // Validação de segurança do token CSRF
            if (!AuthHelper::verifyCsrfToken($csrfToken)) {
                $error = "Sessão ou requisição inválida. Por favor, tente novamente.";
            } elseif (empty($username) || empty($password)) {
                $error = "Preencha o nome de usuário e a senha.";
            } else {
                // Tenta autenticar o usuário
                $user = $this->userModel->authenticate($username, $password);

                if ($user) {
                    AuthHelper::login($user);
SystemLog::log(SystemLog::LEVEL_INFO, 'Login bem-sucedido', $user['id']);
                    $dashboardUrl = defined('BASE_URL') ? BASE_URL . '/dashboard' : '/dashboard';
                    header("Location: " . $dashboardUrl);
                    exit;
                } else {
                    // Mensagem genérica para prevenção de enumeração de usuários (FSD Seção 15)
                    $error = "Nome de usuário ou senha incorretos.";
                }
            }
        }

        $csrfToken = AuthHelper::generateCsrfToken();
        $viewsPath = defined('VIEWS_PATH') ? VIEWS_PATH : __DIR__ . '/../views';
        
        require_once $viewsPath . '/auth/login.php';
    }

    /**
     * Encerra a sessão do usuário e redireciona para a tela de login.
     */
    public function logout(): void
    {
        $user = AuthHelper::user();
AuthHelper::logout();
SystemLog::log(SystemLog::LEVEL_INFO, 'Logout realizado', $user['id'] ?? null);
        $loginUrl = defined('BASE_URL') ? BASE_URL . '/login' : '/login';
        header("Location: " . $loginUrl);
        exit;
    }
}
