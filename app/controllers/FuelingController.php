<?php
/**
 * SisFrota Municipal - Controller de Abastecimentos
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

require_once __DIR__ . '/../models/Fueling.php';
require_once __DIR__ . '/../models/Vehicle.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../helpers/FormatHelper.php';
require_once __DIR__ . '/../models/SystemLog.php';

class FuelingController
{
    private Fueling $fuelingModel;
    private Vehicle $vehicleModel;

    public function __construct()
    {
        $this->fuelingModel = new Fueling();
        $this->vehicleModel = new Vehicle();
    }

    /**
     * Exibe a listagem geral de abastecimentos da frota.
     */
    public function index(): void
    {
        AuthHelper::requireLogin();

        $user = AuthHelper::user();
        $filters = [
            'search'     => trim($_GET['search'] ?? ''),
            'vehicle_id' => !empty($_GET['vehicle_id']) ? (int) $_GET['vehicle_id'] : null,
            'start_date' => trim($_GET['start_date'] ?? ''),
            'end_date'   => trim($_GET['end_date'] ?? ''),
            'fuel_type'  => trim($_GET['fuel_type'] ?? '')
        ];

        $fuelings = $this->fuelingModel->findAll($filters);
        $vehicles = $this->vehicleModel->findAll(); // lista de veículos para o select de filtro

        $pageTitle = 'Lançamentos de Abastecimentos';
        require_once VIEWS_PATH . '/layouts/header.php';
        require_once VIEWS_PATH . '/fuelings/index.php';
        require_once VIEWS_PATH . '/layouts/footer.php';
    }

    /**
     * Exibe o formulário de lançamento de novo abastecimento.
     */
    public function create(): void
    {
        AuthHelper::requireRole(['ADMIN', 'OPERATOR']);

        $user     = AuthHelper::user();
        $vehicles = $this->vehicleModel->findAll(['status' => 'ACTIVE']);
        $fueling  = null; // modo criação

        $selectedVehicleId = !empty($_GET['vehicle_id']) ? (int) $_GET['vehicle_id'] : null;

        $pageTitle = 'Novo Abastecimento';
        require_once VIEWS_PATH . '/layouts/header.php';
        require_once VIEWS_PATH . '/fuelings/form.php';
        require_once VIEWS_PATH . '/layouts/footer.php';
    }

    /**
     * Processa a gravação de um novo abastecimento com validação bloqueante de KM.
     */
    public function store(): void
    {
        AuthHelper::requireRole(['ADMIN', 'OPERATOR']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/fuelings/new');
            exit;
        }

        if (!AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token de segurança inválido. Tente novamente.';
            $_SESSION['form_data']   = $_POST;
            header('Location: ' . BASE_URL . '/fuelings/new');
            exit;
        }

        $user = AuthHelper::user();
        $result = $this->fuelingModel->create($_POST, (int) $user['id']);

        if ($result['success']) {
            unset($_SESSION['form_data']);
            $_SESSION['flash_success'] = $result['message'];
            SystemLog::log(SystemLog::LEVEL_INFO, "Abastecimento cadastrado", (int) $user['id']);
header('Location: ' . BASE_URL . '/fuelings');
        } else {
            $_SESSION['flash_error'] = $result['message'];
SystemLog::log(SystemLog::LEVEL_ERROR, "Falha ao cadastrar abastecimento: {$result['message']}", (int) $user['id']);
            $_SESSION['form_data']   = $_POST;
            
            $redirectUrl = BASE_URL . '/fuelings/new';
            if (!empty($_POST['vehicle_id'])) {
                $redirectUrl .= '?vehicle_id=' . (int) $_POST['vehicle_id'];
            }
            header('Location: ' . $redirectUrl);
        }
        exit;
    }

    /**
     * Exibe a tela de edição/correção administrativa de abastecimento (Exclusivo ADMIN).
     */
    public function edit(int $id): void
    {
        AuthHelper::requireRole(['ADMIN']);

        $user    = AuthHelper::user();
        $fueling = $this->fuelingModel->findById($id);

        if (!$fueling) {
            $_SESSION['flash_error'] = 'Abastecimento não encontrado.';
            header('Location: ' . BASE_URL . '/fuelings');
            exit;
        }

        $vehicles = $this->vehicleModel->findAll();

        $pageTitle = 'Editar Abastecimento #' . $fueling['id'];
        require_once VIEWS_PATH . '/layouts/header.php';
        require_once VIEWS_PATH . '/fuelings/form.php';
        require_once VIEWS_PATH . '/layouts/footer.php';
    }

    /**
     * Processa a atualização de um abastecimento (Exclusivo ADMIN).
     */
    public function update(int $id): void
    {
        AuthHelper::requireRole(['ADMIN']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/fuelings');
            exit;
        }

        if (!AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token de segurança inválido. Tente novamente.';
            header('Location: ' . BASE_URL . '/fuelings/edit/' . $id);
            exit;
        }

        $user = AuthHelper::user();
        $result = $this->fuelingModel->update($id, $_POST, (int) $user['id']);

        if ($result['success']) {
            $_SESSION['flash_success'] = $result['message'];
                SystemLog::log(SystemLog::LEVEL_INFO, "Abastecimento atualizado", (int) $user['id']);
                header('Location: ' . BASE_URL . '/fuelings');
        } else {
            $_SESSION['flash_error'] = $result['message'];
            header('Location: ' . BASE_URL . '/fuelings/edit/' . $id);
        }
        exit;
    }

    /**
     * Processa a exclusão de um abastecimento (Exclusivo ADMIN).
     */
    public function delete(int $id): void
    {
        AuthHelper::requireRole(['ADMIN']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/fuelings');
            exit;
        }

        if (!AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token de segurança inválido.';
            header('Location: ' . BASE_URL . '/fuelings');
            exit;
        }

        $user = AuthHelper::user();
        $result = $this->fuelingModel->delete($id, (int) $user['id']);

        if ($result['success']) {
            $_SESSION['flash_success'] = $result['message'];
        } else {
            $_SESSION['flash_error'] = $result['message'];
        }

        header('Location: ' . BASE_URL . '/fuelings');
        exit;
    }
}
