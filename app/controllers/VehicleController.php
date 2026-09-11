<?php
/**
 * SisFrota Municipal - Controller de Veículos
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

require_once __DIR__ . '/../models/Vehicle.php';
require_once __DIR__ . '/../models/Secretariat.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../helpers/FormatHelper.php';
require_once __DIR__ . '/../models/SystemLog.php';

class VehicleController
{
    private Vehicle $vehicleModel;
    private Secretariat $secretariatModel;

    public function __construct()
    {
        $this->vehicleModel     = new Vehicle();
        $this->secretariatModel = new Secretariat();
    }

    /**
     * Exibe a listagem de veículos da frota com opções de busca e filtros.
     */
    public function index(): void
    {
        AuthHelper::requireLogin();

        $user = AuthHelper::user();
        $filters = [
            'search'         => trim($_GET['search'] ?? ''),
            'secretariat_id' => !empty($_GET['secretariat_id']) ? (int) $_GET['secretariat_id'] : null,
            'status'         => trim($_GET['status'] ?? '')
        ];

        $vehicles     = $this->vehicleModel->findAll($filters);
        $secretariats = $this->secretariatModel->getAll();

        $pageTitle = 'Gestão da Frota de Veículos';
        require_once VIEWS_PATH . '/layouts/header.php';
        require_once VIEWS_PATH . '/vehicles/index.php';
        require_once VIEWS_PATH . '/layouts/footer.php';
    }

    /**
     * Exibe a Ficha Individual do Veículo.
     */
    public function show(int $id): void
    {
        AuthHelper::requireLogin();

        $user    = AuthHelper::user();
        $vehicle = $this->vehicleModel->findById($id, true);

        if (!$vehicle) {
            $_SESSION['flash_error'] = 'Veículo não encontrado.';
            header('Location: ' . BASE_URL . '/vehicles');
            exit;
        }

        $fuelings     = $this->vehicleModel->getFuelingHistory($id);
        $maintenances = $this->vehicleModel->getMaintenanceHistory($id);
        $overallAvgKml = $this->vehicleModel->calculateOverallAvgKml($id);

        $pageTitle = 'Ficha do Veículo — ' . FormatHelper::formatPlate($vehicle['plate']);
        require_once VIEWS_PATH . '/layouts/header.php';
        require_once VIEWS_PATH . '/vehicles/show.php';
        require_once VIEWS_PATH . '/layouts/footer.php';
    }

    /**
     * Exibe o formulário de cadastro de novo veículo.
     */
    public function create(): void
    {
        AuthHelper::requireRole(['ADMIN', 'OPERATOR']);

        $user         = AuthHelper::user();
        $secretariats = $this->secretariatModel->getAll();
        $vehicle      = null; // modo criação

        $pageTitle = 'Novo Veículo';
        require_once VIEWS_PATH . '/layouts/header.php';
        require_once VIEWS_PATH . '/vehicles/form.php';
        require_once VIEWS_PATH . '/layouts/footer.php';
    }

    /**
     * Processa a inserção de um novo veículo.
     */
    public function store(): void
    {
        AuthHelper::requireRole(['ADMIN', 'OPERATOR']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/vehicles/new');
            exit;
        }

        if (!AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token de segurança inválido. Tente novamente.';
            header('Location: ' . BASE_URL . '/vehicles/new');
            exit;
        }

        $user  = AuthHelper::user();
        $plate = FormatHelper::cleanPlate($_POST['plate'] ?? '');
        $model = trim($_POST['model'] ?? '');

        // Validações
        if (!FormatHelper::isValidPlate($plate)) {
            $_SESSION['flash_error'] = 'Placa inválida. Utilize o formato tradicional (ABC-1234) ou Mercosul (ABC1D23).';
            $_SESSION['form_data']   = $_POST;
            header('Location: ' . BASE_URL . '/vehicles/new');
            exit;
        }

        if (empty($model)) {
            $_SESSION['flash_error'] = 'O modelo do veículo é obrigatório.';
            $_SESSION['form_data']   = $_POST;
            header('Location: ' . BASE_URL . '/vehicles/new');
            exit;
        }

        if ($this->vehicleModel->findByPlate($plate)) {
            $_SESSION['flash_error'] = 'Já existe um veículo cadastrado com a placa ' . FormatHelper::formatPlate($plate) . '.';
            $_SESSION['form_data']   = $_POST;
            header('Location: ' . BASE_URL . '/vehicles/new');
            exit;
        }

        $vehicleId = $this->vehicleModel->create($_POST, (int) $user['id']);

        if ($vehicleId) {
            unset($_SESSION['form_data']);
            $_SESSION['flash_success'] = 'Veículo ' . FormatHelper::formatPlate($plate) . ' cadastrado com sucesso!';
            SystemLog::log(SystemLog::LEVEL_INFO, "Veículo " . FormatHelper::formatPlate($plate) . " cadastrado", (int) $user['id']);
header('Location: ' . BASE_URL . '/vehicles');
        } else {
            $_SESSION['flash_error'] = 'Erro ao cadastrar o veículo. Tente novamente.';
            $_SESSION['form_data']   = $_POST;
            header('Location: ' . BASE_URL . '/vehicles/new');
        }
        exit;
    }

    /**
     * Exibe o formulário de edição de veículo existente.
     */
    public function edit(int $id): void
    {
        AuthHelper::requireRole(['ADMIN']);

        $user    = AuthHelper::user();
        $vehicle = $this->vehicleModel->findById($id);

        if (!$vehicle) {
            $_SESSION['flash_error'] = 'Veículo não encontrado ou inativado.';
            header('Location: ' . BASE_URL . '/vehicles');
            exit;
        }

        $secretariats = $this->secretariatModel->getAll();

        $pageTitle = 'Editar Veículo — ' . FormatHelper::formatPlate($vehicle['plate']);
        require_once VIEWS_PATH . '/layouts/header.php';
        require_once VIEWS_PATH . '/vehicles/form.php';
        require_once VIEWS_PATH . '/layouts/footer.php';
    }

    /**
     * Processa a atualização dos dados do veículo.
     */
    public function update(int $id): void
    {
        AuthHelper::requireRole(['ADMIN']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/vehicles');
            exit;
        }

        if (!AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token de segurança inválido. Tente novamente.';
            header('Location: ' . BASE_URL . '/vehicles/edit/' . $id);
            exit;
        }

        $user    = AuthHelper::user();
        $vehicle = $this->vehicleModel->findById($id);

        if (!$vehicle) {
            $_SESSION['flash_error'] = 'Veículo não encontrado.';
            header('Location: ' . BASE_URL . '/vehicles');
            exit;
        }

        $plate = FormatHelper::cleanPlate($_POST['plate'] ?? '');
        $model = trim($_POST['model'] ?? '');

        if (!FormatHelper::isValidPlate($plate)) {
            $_SESSION['flash_error'] = 'Placa inválida. Utilize o formato tradicional (ABC-1234) ou Mercosul (ABC1D23).';
            header('Location: ' . BASE_URL . '/vehicles/edit/' . $id);
            exit;
        }

        if (empty($model)) {
            $_SESSION['flash_error'] = 'O modelo do veículo é obrigatório.';
            header('Location: ' . BASE_URL . '/vehicles/edit/' . $id);
            exit;
        }

        if ($this->vehicleModel->findByPlate($plate, $id)) {
            $_SESSION['flash_error'] = 'Já existe outro veículo cadastrado com a placa ' . FormatHelper::formatPlate($plate) . '.';
            header('Location: ' . BASE_URL . '/vehicles/edit/' . $id);
            exit;
        }

        if ($this->vehicleModel->update($id, $_POST, (int) $user['id'])) {
            $_SESSION['flash_success'] = 'Dados do veículo ' . FormatHelper::formatPlate($plate) . ' atualizados com sucesso!';
            SystemLog::log(SystemLog::LEVEL_INFO, "Veículo " . FormatHelper::formatPlate($plate) . " atualizado", (int) $user['id']);
header('Location: ' . BASE_URL . '/vehicles');
        } else {
            $_SESSION['flash_error'] = 'Erro ao atualizar o veículo. Tente novamente.';
            header('Location: ' . BASE_URL . '/vehicles/edit/' . $id);
        }
        exit;
    }

    /**
     * Processa a inativação do veículo (Soft Delete).
     */
    public function softDelete(int $id): void
    {
        AuthHelper::requireRole(['ADMIN']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/vehicles');
            exit;
        }

        if (!AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token de segurança inválido.';
            header('Location: ' . BASE_URL . '/vehicles');
            exit;
        }

        $user    = AuthHelper::user();
        $vehicle = $this->vehicleModel->findById($id);

        if (!$vehicle) {
            $_SESSION['flash_error'] = 'Veículo não encontrado ou já inativado.';
            header('Location: ' . BASE_URL . '/vehicles');
            exit;
        }

        if ($this->vehicleModel->softDelete($id, (int) $user['id'])) {
            $_SESSION['flash_success'] = 'Veículo ' . FormatHelper::formatPlate($vehicle['plate']) . ' inativado com sucesso.';
        } else {
            $_SESSION['flash_error'] = 'Erro ao inativar o veículo.';
        }

        SystemLog::log(SystemLog::LEVEL_INFO, "Veículo " . FormatHelper::formatPlate($vehicle['plate']) . " inativado", (int) $user['id']);
header('Location: ' . BASE_URL . '/vehicles');
        exit;
    }

    /**
     * Processa a exclusão física permanente do veículo (Hard Delete).
     */
    public function hardDelete(int $id): void
    {
        AuthHelper::requireRole(['ADMIN']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/vehicles');
            exit;
        }

        if (!AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token de segurança inválido.';
            header('Location: ' . BASE_URL . '/vehicles');
            exit;
        }

        $result = $this->vehicleModel->hardDelete($id);

        if ($result['success']) {
            $_SESSION['flash_success'] = $result['message'];
        } else {
            $_SESSION['flash_error'] = $result['message'];
        }

        SystemLog::log(SystemLog::LEVEL_INFO, "Veículo ID $id excluído permanentemente", (int) $user['id']);
header('Location: ' . BASE_URL . '/vehicles');
        exit;
    }
}
