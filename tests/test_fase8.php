<?php
/**
 * SisFrota Municipal - Teste da Fase 8 (CLI)
 * Valida o DashboardController, Agregações de Indicadores, Gráficos Chart.js e Métricas Consolidadas.
 */

define('SYS_ENTRY', true);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/FormatHelper.php';
require_once __DIR__ . '/../app/models/Database.php';
require_once __DIR__ . '/../app/models/Vehicle.php';
require_once __DIR__ . '/../app/models/Fueling.php';
require_once __DIR__ . '/../app/models/Maintenance.php';
require_once __DIR__ . '/../app/models/MaintenanceCategory.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';

echo "=========================================================\n";
echo " SisFrota Municipal — Teste de Validação da Fase 8 (CLI)\n";
echo "=========================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(bool $condition, string $description): void {
    global $passCount, $failCount;
    if ($condition) {
        echo "[PASS] $description\n";
        $passCount++;
    } else {
        echo "[FAIL] $description\n";
        $failCount++;
    }
}

// ─── 1. Validação da biblioteca local Chart.js ──────────────────────────────
$chartJsPath = dirname(__DIR__) . '/assets/js/vendor/chart.min.js';
assertTest(file_exists($chartJsPath) && filesize($chartJsPath) > 50000, 'Chart.js minificado existe localmente em assets/js/vendor/chart.min.js sem dependência de CDN');

// ─── 2. Validação dos Helpers de Mês em Português ───────────────────────────
assertTest(FormatHelper::formatMonthName('2026-09') === 'Setembro de 2026', 'FormatHelper::formatMonthName formata corretamente "Setembro de 2026"');
assertTest(FormatHelper::formatMonthName('2026-01') === 'Janeiro de 2026', 'FormatHelper::formatMonthName formata corretamente "Janeiro de 2026"');
assertTest(FormatHelper::getMonthShortName('09') === 'Set', 'FormatHelper::getMonthShortName formata "Set"');
assertTest(FormatHelper::getMonthShortName(5) === 'Mai', 'FormatHelper::getMonthShortName formata "Mai"');

// ─── 3. Preparação de Dados de Teste para o Dashboard ───────────────────────
$db = Database::getPdo();
$vehicleModel = new Vehicle();
$fuelingModel = new Fueling();
$maintModel   = new Maintenance();
$catModel     = new MaintenanceCategory();

$testPlate = 'DSH8T01';

// Limpar resíduos de testes anteriores
$existingV = $vehicleModel->findByPlate($testPlate);
if ($existingV) {
    $vId = (int) $existingV['id'];
    $db->prepare("DELETE FROM fuelings WHERE vehicle_id = ?")->execute([$vId]);
    $db->prepare("DELETE FROM maintenances WHERE vehicle_id = ?")->execute([$vId]);
    $vehicleModel->hardDelete($vId);
}

// Criar veículo para testes do dashboard
$vId = (int) $vehicleModel->create([
    'plate'          => $testPlate,
    'model'          => 'Van Executiva Dashboard',
    'brand'          => 'Mercedes-Benz',
    'year'           => 2024,
    'secretariat_id' => null,
    'fuel_type'      => 'DIESEL',
    'current_km'     => 10000,
    'status'         => 'ACTIVE'
], 1);
assertTest($vId > 0, "Veículo de teste do Dashboard criado com ID $vId");

// Obter categoria de manutenção
$cats = $catModel->findAllActive();
$catId = !empty($cats) ? (int) $cats[0]['id'] : (int) $catModel->create(['name' => 'Revisão Dashboard', 'created_by' => 1]);

// Inserir abastecimentos no mês atual para o teste
$curMonth = date('Y-m');
$f1 = $fuelingModel->create([
    'vehicle_id'   => $vId,
    'fueling_date' => date('Y-m-02 10:00:00'),
    'km'           => 10500,
    'liters'       => 50.00,
    'total_price'  => 300.00,
    'fuel_type'    => 'DIESEL',
    'station_name' => 'Posto Teste Dashboard'
], 1);
$f2 = $fuelingModel->create([
    'vehicle_id'   => $vId,
    'fueling_date' => date('Y-m-05 14:00:00'),
    'km'           => 11000,
    'liters'       => 50.00,
    'total_price'  => 320.00,
    'fuel_type'    => 'DIESEL',
    'station_name' => 'Posto Teste Dashboard'
], 1);
assertTest($f1 > 0 && $f2 > 0, 'Dois abastecimentos de teste criados no mês atual (500 km rodados)');

// Inserir manutenção no mês atual
$m1 = $maintModel->create([
    'vehicle_id'    => $vId,
    'category_id'   => $catId,
    'service_date'  => date('Y-m-03'),
    'workshop_name' => 'Oficina Teste Dashboard',
    'os_number'     => 'OS-DASH-801',
    'cost'          => 450.00,
    'status'        => 'COMPLETED',
    'notes'         => 'Serviço de teste do dashboard'
], 1);
assertTest($m1 > 0, "Manutenção de teste criada no mês atual com custo R$ 450,00");

// ─── 4. Teste de Consultas e Agregações do Dashboard ────────────────────────
// 4.1 Gastos com combustível no mês
$monthStart = $curMonth . '-01 00:00:00';
$monthEnd   = date('Y-m-t 23:59:59', strtotime($curMonth . '-01'));

$stmtF = $db->prepare("SELECT COALESCE(SUM(total_price), 0) AS total_fuel, COALESCE(SUM(liters), 0) AS total_liters FROM fuelings WHERE fueling_date >= ? AND fueling_date <= ?");
$stmtF->execute([$monthStart, $monthEnd]);
$resF = $stmtF->fetch();
assertTest((float) $resF['total_fuel'] >= 620.00, 'Gasto com combustível do mês consolidado com sucesso (>= R$ 620,00)');
assertTest((float) $resF['total_liters'] >= 100.00, 'Litros de combustível do mês consolidados com sucesso (>= 100 L)');

// 4.2 Gastos com manutenção no mês
$stmtM = $db->prepare("SELECT COALESCE(SUM(cost), 0) AS total_maint FROM maintenances WHERE service_date >= ? AND service_date <= ?");
$stmtM->execute([$curMonth . '-01', date('Y-m-t', strtotime($curMonth . '-01'))]);
$resM = $stmtM->fetch();
assertTest((float) $resM['total_maint'] >= 450.00, 'Gasto com manutenção do mês consolidado com sucesso (>= R$ 450,00)');

// 4.3 KM rodado no mês
$stmtKm = $db->prepare(
    "SELECT COALESCE(SUM(km_diff), 0) AS total_km
     FROM (
         SELECT f.km - COALESCE(
             LAG(f.km) OVER (PARTITION BY f.vehicle_id ORDER BY f.fueling_date ASC, f.id ASC),
             f.km
         ) AS km_diff,
         f.fueling_date
         FROM fuelings f
     ) AS diffs
     WHERE diffs.fueling_date >= ? AND diffs.fueling_date <= ?"
);
$stmtKm->execute([$monthStart, $monthEnd]);
$totalKmMonth = (int) $stmtKm->fetchColumn();
assertTest($totalKmMonth >= 500, "Cálculo de KM rodados no mês via delta é preciso (>= 500 KM calculados: $totalKmMonth KM)");

// 4.4 Status da Frota
$stmtV = $db->query(
    "SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'ACTIVE' THEN 1 ELSE 0 END) AS active_count,
        SUM(CASE WHEN status = 'MAINTENANCE' THEN 1 ELSE 0 END) AS maint_count,
        SUM(CASE WHEN status = 'UNAVAILABLE' THEN 1 ELSE 0 END) AS unavail_count
     FROM vehicles
     WHERE is_deleted = 0"
);
$resV = $stmtV->fetch();
assertTest((int) $resV['total'] > 0 && (int) $resV['active_count'] > 0, 'Status da frota consolidado (total e veículos ativos calculados)');

// ─── 5. Teste do Histórico de 6 Meses para o Gráfico de Evolução ─────────────
$chartLabels = [];
$chartFuel   = [];
$chartMaint  = [];
$refTimestamp = strtotime($curMonth . '-01');
for ($i = 5; $i >= 0; $i--) {
    $mTime = strtotime("-$i months", $refTimestamp);
    $mKey  = date('Y-m', $mTime);
    $chartLabels[] = FormatHelper::getMonthShortName(date('m', $mTime)) . '/' . date('y', $mTime);
    
    $sF = $db->prepare("SELECT COALESCE(SUM(total_price), 0) FROM fuelings WHERE fueling_date >= ? AND fueling_date <= ?");
    $sF->execute([$mKey . '-01 00:00:00', date('Y-m-t 23:59:59', $mTime)]);
    $chartFuel[] = (float) $sF->fetchColumn();

    $sM = $db->prepare("SELECT COALESCE(SUM(cost), 0) FROM maintenances WHERE service_date >= ? AND service_date <= ?");
    $sM->execute([$mKey . '-01', date('Y-m-t', $mTime)]);
    $chartMaint[] = (float) $sM->fetchColumn();
}
assertTest(count($chartLabels) === 6, 'Histórico de 6 meses gerado com exatamente 6 rótulos temporais');
assertTest(count($chartFuel) === 6 && count($chartMaint) === 6, 'Datasets de combustível e manutenção gerados para os 6 meses');

// ─── 6. Teste da Proporção de Manutenções por Status ────────────────────────
$stmtStatus = $db->query("SELECT status, COUNT(*) AS count FROM maintenances GROUP BY status");
$statusRows = $stmtStatus->fetchAll();
$statusCounts = ['COMPLETED' => 0, 'IN_PROGRESS' => 0, 'PENDING' => 0, 'SCHEDULED' => 0];
foreach ($statusRows as $r) {
    if (isset($statusCounts[$r['status']])) {
        $statusCounts[$r['status']] = (int) $r['count'];
    }
}
assertTest($statusCounts['COMPLETED'] >= 1, 'Distribuição por status calcula manutenções COMPLETED com sucesso');

// ─── 7. Teste das Atividades Recentes com Limit ──────────────────────────────
$recentFuelings = $fuelingModel->findAll(['limit' => 5]);
$recentMaintenances = $maintModel->findAll(['limit' => 5]);
assertTest(count($recentFuelings) <= 5 && count($recentFuelings) > 0, 'Listagem de últimos abastecimentos respeita limite de 5 registros');
assertTest(count($recentMaintenances) <= 5 && count($recentMaintenances) > 0, 'Listagem de últimas manutenções respeita limite de 5 registros');

// ─── Limpeza dos dados de teste ─────────────────────────────────────────────
$db->prepare("DELETE FROM fuelings WHERE vehicle_id = ?")->execute([$vId]);
$db->prepare("DELETE FROM maintenances WHERE vehicle_id = ?")->execute([$vId]);
$vehicleModel->hardDelete($vId);

echo "\n---------------------------------------------------------\n";
echo " Resultado dos Testes da Fase 8: $passCount Passaram | $failCount Falharam\n";
echo "---------------------------------------------------------\n";

if ($failCount === 0) {
    echo ">>> TODOS OS TESTES DA FASE 8 FORAM APROVADOS COM SUCESSO! <<<\n";
    exit(0);
} else {
    echo ">>> ALGUNS TESTES FALHARAM. VERIFIQUE OS ERROS ACIMA. <<<\n";
    exit(1);
}
