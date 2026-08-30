<?php
/**
 * SisFrota Municipal - Controller de Usuários
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

require_once APP_PATH . '/helpers/AuthHelper.php';
require_once APP_PATH . '/models/User.php';

class UserController
{
    private User $userModel;

    public function __construct()
    {
        // Exige autenticação e perfil ADMIN
        AuthHelper::requireRole(['ADMIN']);
        $this->userModel = new User();
    }

    /**
     * Renderiza a listagem de usuários.
     */
    public function index(): void
    {
        $users = $this->userModel->getAll();
        $currentUser = AuthHelper::user();
        $pageTitle = 'Gestão de Usuários';
        $csrfToken = AuthHelper::generateCsrfToken();

        $error = $_SESSION['flash_error'] ?? null;
        $success = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        require_once VIEWS_PATH . '/layouts/header.php';
        require_once VIEWS_PATH . '/settings/users.php';
        require_once VIEWS_PATH . '/layouts/footer.php';
    }

    /**
     * Processa a criação de um novo usuário.
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/settings/users');
            exit;
        }

        if (!AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token de segurança inválido. Tente novamente.';
            header('Location: ' . BASE_URL . '/settings/users');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = in_array($_POST['role'] ?? '', ['ADMIN', 'OPERATOR', 'MANAGER'], true) ? $_POST['role'] : 'OPERATOR';
        $status   = in_array($_POST['status'] ?? '', ['ACTIVE', 'INACTIVE'], true) ? $_POST['status'] : 'ACTIVE';
        $user     = AuthHelper::user();

        if (empty($username) || empty($fullName) || empty($password)) {
            $_SESSION['flash_error'] = 'Preencha todos os campos obrigatórios (nome de usuário, nome completo e senha).';
            header('Location: ' . BASE_URL . '/settings/users');
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['flash_error'] = 'A senha deve conter no mínimo 6 caracteres.';
            header('Location: ' . BASE_URL . '/settings/users');
            exit;
        }

        if ($this->userModel->existsUsername($username)) {
            $_SESSION['flash_error'] = 'Este nome de usuário já está em uso.';
            header('Location: ' . BASE_URL . '/settings/users');
            exit;
        }

        $created = $this->userModel->create([
            'username'   => $username,
            'full_name'   => $fullName,
            'password'   => $password,
            'role'       => $role,
            'status'     => $status,
            'created_by' => $user['id'] ?? null
        ]);

        if ($created) {
            $_SESSION['flash_success'] = 'Usuário cadastrado com sucesso!';
        } else {
            $_SESSION['flash_error'] = 'Erro ao cadastrar o usuário.';
        }

        header('Location: ' . BASE_URL . '/settings/users');
        exit;
    }

    /**
     * Processa a atualização de dados cadastrais do usuário (sem alterar a senha).
     */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/settings/users');
            exit;
        }

        if (!AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token de segurança inválido.';
            header('Location: ' . BASE_URL . '/settings/users');
            exit;
        }

        $id       = (int) ($_POST['id'] ?? 0);
        $fullName = trim($_POST['full_name'] ?? '');
        $role     = in_array($_POST['role'] ?? '', ['ADMIN', 'OPERATOR', 'MANAGER'], true) ? $_POST['role'] : 'OPERATOR';
        $status   = in_array($_POST['status'] ?? '', ['ACTIVE', 'INACTIVE'], true) ? $_POST['status'] : 'ACTIVE';
        $user     = AuthHelper::user();

        if ($id <= 0 || empty($fullName)) {
            $_SESSION['flash_error'] = 'Dados inválidos para atualização do usuário.';
            header('Location: ' . BASE_URL . '/settings/users');
            exit;
        }

        // Impede que o admin inative ou altere seu próprio perfil se for ele mesmo
        if ($user['id'] == $id && $status === 'INACTIVE') {
            $_SESSION['flash_error'] = 'Você não pode inativar sua própria conta de usuário.';
            header('Location: ' . BASE_URL . '/settings/users');
            exit;
        }

        $updated = $this->userModel->update($id, [
            'full_name'  => $fullName,
            'role'       => $role,
            'status'     => $status,
            'updated_by' => $user['id'] ?? null
        ]);

        if ($updated) {
            $_SESSION['flash_success'] = 'Dados do usuário atualizados com sucesso!';
        } else {
            $_SESSION['flash_error'] = 'Erro ao atualizar o usuário.';
        }

        header('Location: ' . BASE_URL . '/settings/users');
        exit;
    }

    /**
     * Atualiza a senha de um usuário.
     */
    public function updatePassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/settings/users');
            exit;
        }

        if (!AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token de segurança inválido.';
            header('Location: ' . BASE_URL . '/settings/users');
            exit;
        }

        $id          = (int) ($_POST['id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';
        $user        = AuthHelper::user();

        if ($id <= 0 || empty($newPassword)) {
            $_SESSION['flash_error'] = 'Identificador ou nova senha não informados.';
            header('Location: ' . BASE_URL . '/settings/users');
            exit;
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['flash_error'] = 'A nova senha deve conter no mínimo 6 caracteres.';
            header('Location: ' . BASE_URL . '/settings/users');
            exit;
        }

        if ($this->userModel->updatePassword($id, $newPassword, $user['id'] ?? null)) {
            $_SESSION['flash_success'] = 'Senha do usuário redefinida com sucesso!';
        } else {
            $_SESSION['flash_error'] = 'Erro ao redefinir a senha do usuário.';
        }

        header('Location: ' . BASE_URL . '/settings/users');
        exit;
    }

    /**
     * Exclui uma conta de usuário se não houver vínculos e não for o usuário logado.
     */
    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/settings/users');
            exit;
        }

        if (!AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token de segurança inválido.';
            header('Location: ' . BASE_URL . '/settings/users');
            exit;
        }

        $id   = (int) ($_POST['id'] ?? 0);
        $user = AuthHelper::user();

        if ($id <= 0) {
            $_SESSION['flash_error'] = 'ID de usuário inválido.';
            header('Location: ' . BASE_URL . '/settings/users');
            exit;
        }

        if ($user['id'] == $id) {
            $_SESSION['flash_error'] = 'Você não pode excluir sua própria conta de usuário logada.';
            header('Location: ' . BASE_URL . '/settings/users');
            exit;
        }

        $targetUser = $this->userModel->findById($id);
        if (!$targetUser) {
            $_SESSION['flash_error'] = 'Usuário não encontrado.';
            header('Location: ' . BASE_URL . '/settings/users');
            exit;
        }

        if ($this->userModel->hasAssociatedRecords($id)) {
            $_SESSION['flash_error'] = 'Não é possível excluir este usuário pois ele possui registros vinculados no sistema. Em vez de excluir, inative a conta.';
            header('Location: ' . BASE_URL . '/settings/users');
            exit;
        }

        if ($this->userModel->delete($id)) {
            $_SESSION['flash_success'] = 'Usuário excluído com sucesso!';
        } else {
            $_SESSION['flash_error'] = 'Erro ao excluir o usuário.';
        }

        header('Location: ' . BASE_URL . '/settings/users');
        exit;
    }
}
