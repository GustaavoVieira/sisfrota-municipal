<?php
/**
 * SisFrota Municipal - Controller de Categorias de Manutenção
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

require_once APP_PATH . '/helpers/AuthHelper.php';
require_once APP_PATH . '/models/MaintenanceCategory.php';

class MaintenanceCategoryController
{
    private MaintenanceCategory $categoryModel;

    public function __construct()
    {
        // Exige autenticação e perfil ADMIN
        AuthHelper::requireRole(['ADMIN']);
        $this->categoryModel = new MaintenanceCategory();
    }

    /**
     * Renderiza a listagem de categorias de manutenção.
     */
    public function index(): void
    {
        $categories = $this->categoryModel->getAll();
        $user = AuthHelper::user();
        $pageTitle = 'Categorias de Manutenção';
        $csrfToken = AuthHelper::generateCsrfToken();

        $error = $_SESSION['flash_error'] ?? null;
        $success = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        require_once VIEWS_PATH . '/layouts/header.php';
        require_once VIEWS_PATH . '/settings/categories.php';
        require_once VIEWS_PATH . '/layouts/footer.php';
    }

    /**
     * Cadastra uma nova categoria de manutenção.
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/settings/categories');
            exit;
        }

        if (!AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token de segurança inválido. Tente novamente.';
            header('Location: ' . BASE_URL . '/settings/categories');
            exit;
        }

        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status      = in_array($_POST['status'] ?? '', ['ACTIVE', 'INACTIVE'], true) ? $_POST['status'] : 'ACTIVE';
        $user        = AuthHelper::user();

        if (empty($name)) {
            $_SESSION['flash_error'] = 'O nome da categoria é obrigatório.';
            header('Location: ' . BASE_URL . '/settings/categories');
            exit;
        }

        if ($this->categoryModel->existsName($name)) {
            $_SESSION['flash_error'] = 'Já existe uma categoria cadastrada com este nome.';
            header('Location: ' . BASE_URL . '/settings/categories');
            exit;
        }

        $result = $this->categoryModel->create([
            'name'        => $name,
            'description' => $description,
            'status'      => $status,
            'created_by'  => $user['id'] ?? null
        ]);

        if ($result) {
            $_SESSION['flash_success'] = 'Categoria de manutenção cadastrada com sucesso!';
        } else {
            $_SESSION['flash_error'] = 'Erro ao cadastrar categoria.';
        }

        header('Location: ' . BASE_URL . '/settings/categories');
        exit;
    }

    /**
     * Atualiza os dados de uma categoria existente.
     */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/settings/categories');
            exit;
        }

        if (!AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token de segurança inválido. Tente novamente.';
            header('Location: ' . BASE_URL . '/settings/categories');
            exit;
        }

        $id          = (int) ($_POST['id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status      = in_array($_POST['status'] ?? '', ['ACTIVE', 'INACTIVE'], true) ? $_POST['status'] : 'ACTIVE';
        $user        = AuthHelper::user();

        if ($id <= 0 || empty($name)) {
            $_SESSION['flash_error'] = 'Dados inválidos para atualização da categoria.';
            header('Location: ' . BASE_URL . '/settings/categories');
            exit;
        }

        if ($this->categoryModel->existsName($name, $id)) {
            $_SESSION['flash_error'] = 'Já existe outra categoria cadastrada com este nome.';
            header('Location: ' . BASE_URL . '/settings/categories');
            exit;
        }

        $updated = $this->categoryModel->update($id, [
            'name'        => $name,
            'description' => $description,
            'status'      => $status,
            'updated_by'  => $user['id'] ?? null
        ]);

        if ($updated) {
            $_SESSION['flash_success'] = 'Categoria de manutenção atualizada com sucesso!';
        } else {
            $_SESSION['flash_error'] = 'Erro ao atualizar a categoria.';
        }

        header('Location: ' . BASE_URL . '/settings/categories');
        exit;
    }

    /**
     * Alterna o status (Ativo / Inativo) de uma categoria.
     */
    public function toggle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/settings/categories');
            exit;
        }

        if (!AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token de segurança inválido.';
            header('Location: ' . BASE_URL . '/settings/categories');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $user = AuthHelper::user();

        if ($id <= 0) {
            $_SESSION['flash_error'] = 'ID da categoria inválido.';
            header('Location: ' . BASE_URL . '/settings/categories');
            exit;
        }

        if ($this->categoryModel->toggleStatus($id, $user['id'] ?? null)) {
            $_SESSION['flash_success'] = 'Status da categoria alterado com sucesso!';
        } else {
            $_SESSION['flash_error'] = 'Erro ao alterar status da categoria.';
        }

        header('Location: ' . BASE_URL . '/settings/categories');
        exit;
    }
}
