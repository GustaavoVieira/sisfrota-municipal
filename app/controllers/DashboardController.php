<?php
/**
 * SisFrota Municipal - Controller do Dashboard
 * Fase 8: Painel de Indicadores Gerenciais e Gráficos
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../helpers/FormatHelper.php';
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/Fueling.php';
require_once __DIR__ . '/../models/Maintenance.php';
require_once __DIR__ . '/../models/Vehicle.php';

class DashboardController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getPdo();
    }

    /**
     * Exibe a tela principal do dashboard com todos os indicadores consolidados.
     * Acessível para todos os perfis autenticados (ADMIN, OPERATOR, MANAGER).
     */
    public function index(): void
    {
        AuthHelper::requireLogin();

        $user      = AuthHelper::user();
        $pageTitle = 'Dashboard Gerencial';

        // 1. Mês de referência selecionado (padrão: mês atual YYYY-MM)
        $selectedMonth = $_GET['month'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
            $selectedMonth = date('Y-m');
        }

        $monthStart = $selectedMonth . '-01 00:00:00';
        $monthEnd   = date('Y-m-t 23:59:59', strtotime($selectedMonth . '-01'));

        $monthStartDate = $selectedMonth . '-01';
        $monthEndDate   = date('Y-m-t', strtotime($selectedMonth . '-01'));

        // 2. Métricas do Mês de Referência
        // 2.1 Gastos com Combustível
        $stmtFuel = $this->db->prepare(
            "SELECT COALESCE(SUM(total_price), 0) AS total_fuel,
                    COALESCE(SUM(liters), 0) AS total_liters,
                    COUNT(*) AS count_fuel
             FROM fuelings
             WHERE fueling_date >= :start_date AND fueling_date <= :end_date"
        );
        $stmtFuel->execute([
            ':start_date' => $monthStart,
            ':end_date'   => $monthEnd
        ]);
        $fuelMetrics = $stmtFuel->fetch() ?: ['total_fuel' => 0, 'total_liters' => 0, 'count_fuel' => 0];
        $totalFuelCost   = (float) $fuelMetrics['total_fuel'];
        $totalFuelLiters = (float) $fuelMetrics['total_liters'];
        $totalFuelCount  = (int)   $fuelMetrics['count_fuel'];

        // 2.2 Gastos com Manutenção
        $stmtMaint = $this->db->prepare(
            "SELECT COALESCE(SUM(cost), 0) AS total_maint,
                    COUNT(*) AS count_maint
             FROM maintenances
             WHERE service_date >= :start_date AND service_date <= :end_date"
        );
        $stmtMaint->execute([
            ':start_date' => $monthStartDate,
            ':end_date'   => $monthEndDate
        ]);
        $maintMetrics = $stmtMaint->fetch() ?: ['total_maint' => 0, 'count_maint' => 0];
        $totalMaintCost  = (float) $maintMetrics['total_maint'];
        $totalMaintCount = (int)   $maintMetrics['count_maint'];

        // 2.3 Gasto Total Consolidado
        $totalCost = $totalFuelCost + $totalMaintCost;

        // 2.4 KM Rodado no Mês (diferencial de KM entre abastecimentos do mês)
        $stmtKm = $this->db->prepare(
            "SELECT COALESCE(SUM(km_diff), 0) AS total_km
             FROM (
                 SELECT f.km - COALESCE(
                     LAG(f.km) OVER (PARTITION BY f.vehicle_id ORDER BY f.fueling_date ASC, f.id ASC),
                     f.km
                 ) AS km_diff,
                 f.fueling_date
                 FROM fuelings f
             ) AS diffs
             WHERE diffs.fueling_date >= :start_date AND diffs.fueling_date <= :end_date"
        );
        $stmtKm->execute([
            ':start_date' => $monthStart,
            ':end_date'   => $monthEnd
        ]);
        $kmMetric = $stmtKm->fetch();
        $totalKmMonth = (int) ($kmMetric['total_km'] ?? 0);

        // 2.5 Status da Frota (Veículos Ativos vs Indisponíveis)
        $stmtVehicles = $this->db->query(
            "SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'ACTIVE' THEN 1 ELSE 0 END) AS active_count,
                SUM(CASE WHEN status = 'MAINTENANCE' THEN 1 ELSE 0 END) AS maint_count,
                SUM(CASE WHEN status = 'UNAVAILABLE' THEN 1 ELSE 0 END) AS unavail_count
             FROM vehicles
             WHERE is_deleted = 0"
        );
        $vehMetrics = $stmtVehicles->fetch() ?: ['total' => 0, 'active_count' => 0, 'maint_count' => 0, 'unavail_count' => 0];
        $totalVehicles       = (int) ($vehMetrics['total'] ?? 0);
        $activeVehicles      = (int) ($vehMetrics['active_count'] ?? 0);
        $maintVehicles       = (int) ($vehMetrics['maint_count'] ?? 0);
        $unavailVehicles     = (int) ($vehMetrics['unavail_count'] ?? 0);
        $unavailableVehicles = $maintVehicles + $unavailVehicles;

        // 3. Gráfico 1: Evolução de Custos Mensais (Últimos 6 meses até o mês selecionado)
        $chartCostLabels = [];
        $chartCostFuel   = [];
        $chartCostMaint  = [];

        $refTimestamp = strtotime($selectedMonth . '-01');
        for ($i = 5; $i >= 0; $i--) {
            $mTime = strtotime("-$i months", $refTimestamp);
            $mKey  = date('Y-m', $mTime);
            $mStart = $mKey . '-01 00:00:00';
            $mEnd   = date('Y-m-t 23:59:59', $mTime);
            $mStartDate = $mKey . '-01';
            $mEndDate   = date('Y-m-t', $mTime);

            $label = FormatHelper::getMonthShortName(date('m', $mTime)) . '/' . date('y', $mTime);
            $chartCostLabels[] = $label;

            // Combustível no mês $mKey
            $sF = $this->db->prepare(
                "SELECT COALESCE(SUM(total_price), 0) AS val 
                 FROM fuelings 
                 WHERE fueling_date >= :s AND fueling_date <= :e"
            );
            $sF->execute([':s' => $mStart, ':e' => $mEnd]);
            $chartCostFuel[] = (float) $sF->fetchColumn();

            // Manutenção no mês $mKey
            $sM = $this->db->prepare(
                "SELECT COALESCE(SUM(cost), 0) AS val 
                 FROM maintenances 
                 WHERE service_date >= :s AND service_date <= :e"
            );
            $sM->execute([':s' => $mStartDate, ':e' => $mEndDate]);
            $chartCostMaint[] = (float) $sM->fetchColumn();
        }

        // 4. Gráfico 2: Proporção de Manutenções (por status)
        $stmtStatus = $this->db->query(
            "SELECT status, COUNT(*) AS count
             FROM maintenances
             GROUP BY status"
        );
        $statusRows = $stmtStatus->fetchAll() ?: [];
        $statusCounts = [
            'COMPLETED'   => 0,
            'IN_PROGRESS' => 0,
            'PENDING'     => 0,
            'SCHEDULED'   => 0,
        ];
        foreach ($statusRows as $row) {
            if (isset($statusCounts[$row['status']])) {
                $statusCounts[$row['status']] = (int) $row['count'];
            }
        }

        // 5. Listagens Recentes (Últimos 5 abastecimentos e últimas 5 manutenções)
        $fuelingModel     = new Fueling();
        $maintenanceModel = new Maintenance();

        $recentFuelings     = $fuelingModel->findAll(['limit' => 5]);
        $recentMaintenances = $maintenanceModel->findAll(['limit' => 5]);

        // 6. Lista de Meses Disponíveis para o Seletor (Últimos 12 meses + próximos 2)
        $availableMonths = [];
        $now = time();
        for ($i = -2; $i < 12; $i++) {
            $t = strtotime("-$i months", $now);
            $k = date('Y-m', $t);
            $availableMonths[$k] = FormatHelper::formatMonthName($k);
        }

        // Scripts adicionais necessários para o layout (Chart.js local)
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $extraScripts = [
            $baseUrl . '/assets/js/vendor/chart.min.js'
        ];

        require_once VIEWS_PATH . '/layouts/header.php';
        require_once VIEWS_PATH . '/dashboard/index.php';
        require_once VIEWS_PATH . '/layouts/footer.php';
    }
}
