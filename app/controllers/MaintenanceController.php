<?php
/**
 * SisFrota Municipal - Controller de Manutenções
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

require_once __DIR__ . '/../models/Maintenance.php';
require_once __DIR__ . '/../models/MaintenanceCategory.php';
require_once __DIR__ . '/../models/Vehicle.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../helpers/FormatHelper.php';

class MaintenanceController
{
    private Maintenance $maintenanceModel;
    private MaintenanceCategory $categoryModel;
    private Vehicle $vehicleModel;

    public function __construct()
    {
        $this->maintenanceModel = new Maintenance();
        $this->categoryModel    = new MaintenanceCategory();
        $this->vehicleModel     = new Vehicle();
    }

    /**
     * Exibe a listagem geral de ordens de manutenção da frota.
     */
    public function index(): void
    {
        AuthHelper::requireLogin();

        $user = AuthHelper::user();
        $filters = [
            'search'      => trim($_GET['search'] ?? ''),
            'vehicle_id'  => !empty($_GET['vehicle_id']) ? (int) $_GET['vehicle_id'] : null,
            'category_id' => !empty($_GET['category_id']) ? (int) $_GET['category_id'] : null,
            'status'      => trim($_GET['status'] ?? ''),
            'start_date'  => trim($_GET['start_date'] ?? ''),
            'end_date'    => trim($_GET['end_date'] ?? '')
        ];

        $maintenances = $this->maintenanceModel->findAll($filters);
        $vehicles     = $this->vehicleModel->findAll();
        $categories   = $this->categoryModel->findAll();

        $pageTitle = 'Ordens de Manutenção';
        require_once VIEWS_PATH . '/layouts/header.php';
        require_once VIEWS_PATH . '/maintenances/index.php';
        require_once VIEWS_PATH . '/layouts/footer.php';
    }

    /**
     * Exibe o formulário de cadastramento de nova ordem de manutenção.
     */
    public function create(): void
    {
        AuthHelper::requireRole(['ADMIN', 'OPERATOR']);

        $user        = AuthHelper::user();
        $vehicles    = $this->vehicleModel->findAll();
        $categories  = $this->categoryModel->findAllActive();
        $maintenance = null; // Modo criação

        $selectedVehicleId = !empty($_GET['vehicle_id']) ? (int) $_GET['vehicle_id'] : null;

        $pageTitle = 'Nova Ordem de Manutenção';
        require_once VIEWS_PATH . '/layouts/header.php';
        require_once VIEWS_PATH . '/maintenances/form.php';
        require_once VIEWS_PATH . '/layouts/footer.php';
    }

    /**
     * Processa a gravação de uma nova ordem de manutenção.
     */
    public function store(): void
    {
        AuthHelper::requireRole(['ADMIN', 'OPERATOR']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/maintenances/new');
            exit;
        }

        if (!AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token de segurança inválido. Tente novamente.';
            $_SESSION['form_data']   = $_POST;
            header('Location: ' . BASE_URL . '/maintenances/new');
            exit;
        }

        $user   = AuthHelper::user();
        $result = $this->maintenanceModel->create($_POST, (int) $user['id']);

        if ($result['success']) {
            unset($_SESSION['form_data']);
            $_SESSION['flash_success'] = $result['message'];
            header('Location: ' . BASE_URL . '/maintenances');
        } else {
            $_SESSION['flash_error'] = $result['message'];
            $_SESSION['form_data']   = $_POST;

            $redirectUrl = BASE_URL . '/maintenances/new';
            if (!empty($_POST['vehicle_id'])) {
                $redirectUrl .= '?vehicle_id=' . (int) $_POST['vehicle_id'];
            }
            header('Location: ' . $redirectUrl);
        }
        exit;
    }

    /**
     * Exibe a tela de edição e alteração de status da Ordem de Serviço.
     */
    public function edit(int $id): void
    {
        AuthHelper::requireRole(['ADMIN', 'OPERATOR']);

        $user        = AuthHelper::user();
        $maintenance = $this->maintenanceModel->findById($id);

        if (!$maintenance) {
            $_SESSION['flash_error'] = 'Ordem de manutenção não encontrada.';
            header('Location: ' . BASE_URL . '/maintenances');
            exit;
        }

        $vehicles   = $this->vehicleModel->findAll();
        $categories = $this->categoryModel->findAll();

        $pageTitle = 'Editar Ordem de Serviço #' . htmlspecialchars($maintenance['os_number'], ENT_QUOTES, 'UTF-8');
        require_once VIEWS_PATH . '/layouts/header.php';
        require_once VIEWS_PATH . '/maintenances/form.php';
        require_once VIEWS_PATH . '/layouts/footer.php';
    }

    /**
     * Processa a atualização da Ordem de Serviço e status do atendimento.
     */
    public function update(int $id): void
    {
        AuthHelper::requireRole(['ADMIN', 'OPERATOR']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/maintenances');
            exit;
        }

        if (!AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token de segurança inválido. Tente novamente.';
            header('Location: ' . BASE_URL . '/maintenances/edit/' . $id);
            exit;
        }

        $user   = AuthHelper::user();
        $result = $this->maintenanceModel->update($id, $_POST, (int) $user['id']);

        if ($result['success']) {
            $_SESSION['flash_success'] = $result['message'];
            header('Location: ' . BASE_URL . '/maintenances');
        } else {
            $_SESSION['flash_error'] = $result['message'];
            header('Location: ' . BASE_URL . '/maintenances/edit/' . $id);
        }
        exit;
    }

    /**
     * Processa a exclusão de uma ordem de manutenção (Exclusivo ADMIN).
     */
    public function delete(int $id): void
    {
        AuthHelper::requireRole(['ADMIN']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/maintenances');
            exit;
        }

        if (!AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token de segurança inválido.';
            header('Location: ' . BASE_URL . '/maintenances');
            exit;
        }

        $user   = AuthHelper::user();
        $result = $this->maintenanceModel->delete($id, (int) $user['id']);

        if ($result['success']) {
            $_SESSION['flash_success'] = $result['message'];
        } else {
            $_SESSION['flash_error'] = $result['message'];
        }

        header('Location: ' . BASE_URL . '/maintenances');
        exit;
    }
}
