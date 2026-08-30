<?php
/**
 * SisFrota Municipal - Controller de Secretarias
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

require_once APP_PATH . '/helpers/AuthHelper.php';
require_once APP_PATH . '/models/Secretariat.php';

class SecretariatController
{
    private Secretariat $secretariatModel;

    public function __construct()
    {
        // Exige autenticação e perfil ADMIN
        AuthHelper::requireRole(['ADMIN']);
        $this->secretariatModel = new Secretariat();
    }

    /**
     * Renderiza a listagem de secretarias.
     */
    public function index(): void
    {
        $secretariats = $this->secretariatModel->getAll();
        $user = AuthHelper::user();
        $pageTitle = 'Gestão de Secretarias';
        $csrfToken = AuthHelper::generateCsrfToken();

        $error = $_SESSION['flash_error'] ?? null;
        $success = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        require_once VIEWS_PATH . '/layouts/header.php';
        require_once VIEWS_PATH . '/settings/secretariats.php';
        require_once VIEWS_PATH . '/layouts/footer.php';
    }

    /**
     * Processa a inserção de uma nova secretaria.
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/settings/secretariats');
            exit;
        }

        if (!AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token de segurança inválido. Tente novamente.';
            header('Location: ' . BASE_URL . '/settings/secretariats');
            exit;
        }

        $name    = trim($_POST['name'] ?? '');
        $acronym = trim($_POST['acronym'] ?? '');
        $user    = AuthHelper::user();

        if (empty($name)) {
            $_SESSION['flash_error'] = 'O nome da secretaria é obrigatório.';
            header('Location: ' . BASE_URL . '/settings/secretariats');
            exit;
        }

        if ($this->secretariatModel->existsName($name)) {
            $_SESSION['flash_error'] = 'Já existe uma secretaria cadastrada com este nome.';
            header('Location: ' . BASE_URL . '/settings/secretariats');
            exit;
        }

        $result = $this->secretariatModel->create([
            'name'       => $name,
            'acronym'    => $acronym,
            'created_by' => $user['id'] ?? null
        ]);

        if ($result) {
            $_SESSION['flash_success'] = 'Secretaria cadastrada com sucesso!';
        } else {
            $_SESSION['flash_error'] = 'Erro ao cadastrar a secretaria. Tente novamente.';
        }

        header('Location: ' . BASE_URL . '/settings/secretariats');
        exit;
    }

    /**
     * Processa a atualização de uma secretaria existente.
     */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/settings/secretariats');
            exit;
        }

        if (!AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token de segurança inválido. Tente novamente.';
            header('Location: ' . BASE_URL . '/settings/secretariats');
            exit;
        }

        $id      = (int) ($_POST['id'] ?? 0);
        $name    = trim($_POST['name'] ?? '');
        $acronym = trim($_POST['acronym'] ?? '');
        $user    = AuthHelper::user();

        if ($id <= 0 || empty($name)) {
            $_SESSION['flash_error'] = 'Dados inválidos para atualização da secretaria.';
            header('Location: ' . BASE_URL . '/settings/secretariats');
            exit;
        }

        if ($this->secretariatModel->existsName($name, $id)) {
            $_SESSION['flash_error'] = 'Já existe outra secretaria cadastrada com este nome.';
            header('Location: ' . BASE_URL . '/settings/secretariats');
            exit;
        }

        $updated = $this->secretariatModel->update($id, [
            'name'       => $name,
            'acronym'    => $acronym,
            'updated_by' => $user['id'] ?? null
        ]);

        if ($updated) {
            $_SESSION['flash_success'] = 'Secretaria atualizada com sucesso!';
        } else {
            $_SESSION['flash_error'] = 'Erro ao atualizar a secretaria.';
        }

        header('Location: ' . BASE_URL . '/settings/secretariats');
        exit;
    }

    /**
     * Exclui uma secretaria se não tiver vínculos.
     */
    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/settings/secretariats');
            exit;
        }

        if (!AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token de segurança inválido.';
            header('Location: ' . BASE_URL . '/settings/secretariats');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Identificador de secretaria inválido.';
            header('Location: ' . BASE_URL . '/settings/secretariats');
            exit;
        }

        $secretariat = $this->secretariatModel->getById($id);
        if (!$secretariat) {
            $_SESSION['flash_error'] = 'Secretaria não encontrada.';
            header('Location: ' . BASE_URL . '/settings/secretariats');
            exit;
        }

        if (($secretariat['total_vehicles'] ?? 0) > 0) {
            $_SESSION['flash_error'] = 'Não é possível excluir a secretaria pois existem veículos vinculados a ela.';
            header('Location: ' . BASE_URL . '/settings/secretariats');
            exit;
        }

        if ($this->secretariatModel->delete($id)) {
            $_SESSION['flash_success'] = 'Secretaria excluída com sucesso!';
        } else {
            $_SESSION['flash_error'] = 'Erro ao excluir a secretaria.';
        }

        header('Location: ' . BASE_URL . '/settings/secretariats');
        exit;
    }
}
