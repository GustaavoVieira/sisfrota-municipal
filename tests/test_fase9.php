<?php
/**
 * SisFrota Municipal - Testes Automatizados da Fase 9 (CLI)
 * Módulo de Relatórios e Exportações (CSV e PDF)
 */

define('SYS_ENTRY', true);
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEWS_PATH', APP_PATH . '/views');
define('BASE_URL', 'http://localhost/ProjetoPrefeitura');

$config = require_once CONFIG_PATH . '/config.php';
date_default_timezone_set($config['app']['timezone'] ?? 'America/Sao_Paulo');

require_once APP_PATH . '/helpers/AuthHelper.php';
AuthHelper::initSession();

// Simula login de administrador para os testes
$_SESSION['user_id']       = 1;
$_SESSION['role']          = 'ADMIN';
$_SESSION['username']      = 'admin';
$_SESSION['full_name']     = 'Administrador do Sistema';
$_SESSION['last_activity'] = time();

require_once APP_PATH . '/models/Database.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Secretariat.php';
require_once APP_PATH . '/models/MaintenanceCategory.php';
require_once APP_PATH . '/models/Vehicle.php';
require_once APP_PATH . '/models/Fueling.php';
require_once APP_PATH . '/models/Maintenance.php';
require_once APP_PATH . '/helpers/FormatHelper.php';
require_once APP_PATH . '/helpers/fpdf/fpdf.php';
require_once APP_PATH . '/helpers/ReportPdfHelper.php';
require_once APP_PATH . '/controllers/ReportController.php';

$pdo = Database::getPdo();

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

function runTest(string $title, callable $fn): void {
    global $totalTests, $passedTests, $failedTests;
    $totalTests++;
    echo "\n[TESTE $totalTests] $title... ";
    try {
        $result = $fn();
        if ($result === true || $result === null) {
            echo "OK \033[32m[PASSOU]\033[0m";
            $passedTests++;
        } else {
            echo "FALHOU \033[31m[ERRO: $result]\033[0m";
            $failedTests++;
        }
    } catch (Throwable $e) {
        echo "EXCEÇÃO \033[31m[" . $e->getMessage() . " em " . $e->getFile() . ":" . $e->getLine() . "]\033[0m";
        $failedTests++;
    }
}

echo "========================================================================\n";
echo " SisFrota Municipal - Execução de Testes da Fase 9: Relatórios & Export\n";
echo "========================================================================\n";

// ── Setup de dados para testes de relatórios ──────────────────────────────────
$userModel = new User();
$adminUser = $userModel->findByUsername('admin');
$adminId   = (int)($adminUser['id'] ?? 1);

$secModel = new Secretariat();
$allSecs  = $secModel->findAll();
$secId1   = !empty($allSecs[0]['id']) ? (int)$allSecs[0]['id'] : 1;
$secId2   = !empty($allSecs[1]['id']) ? (int)$allSecs[1]['id'] : $secId1;

$catModel = new MaintenanceCategory();
$allCats  = $catModel->findAll();
$catId1   = !empty($allCats[0]['id']) ? (int)$allCats[0]['id'] : 1;

$vehModel = new Vehicle();
$testPlate1 = 'REP' . rand(1000, 9999);
$testPlate2 = 'REP' . rand(1000, 9999);

$vehId1 = (int)$vehModel->create([
    'plate' => $testPlate1,
    'model' => 'Gol 1.0 Relatório Teste',
    'brand' => 'Volkswagen',
    'year' => 2023,
    'secretariat_id' => $secId1,
    'fuel_type' => 'FLEX',
    'current_km' => 10000,
    'status' => 'ACTIVE'
], $adminId);

$vehId2 = (int)$vehModel->create([
    'plate' => $testPlate2,
    'model' => 'Spin 1.8 Saúde Relatório',
    'brand' => 'Chevrolet',
    'year' => 2024,
    'secretariat_id' => $secId2,
    'fuel_type' => 'GASOLINE',
    'current_km' => 5000,
    'status' => 'ACTIVE'
], $adminId);

$fuelModel = new Fueling();
$maintModel = new Maintenance();

// Inserir abastecimentos de teste com datas controladas
$fuelRes1 = $fuelModel->create([
    'vehicle_id' => $vehId1,
    'fueling_date' => date('Y-m-d', strtotime('-10 days')) . ' 10:00:00',
    'km' => 10400,
    'liters' => 40.0,
    'total_price' => 240.00,
    'fuel_type' => 'GASOLINE',
    'station_name' => 'Posto Central Test'
], $adminId);

$fuelRes2 = $fuelModel->create([
    'vehicle_id' => $vehId1,
    'fueling_date' => date('Y-m-d', strtotime('-2 days')) . ' 14:30:00',
    'km' => 10850,
    'liters' => 45.0,
    'total_price' => 270.00,
    'fuel_type' => 'GASOLINE',
    'station_name' => 'Posto Central Test'
], $adminId);

// Inserir manutenção de teste
$maintRes1 = $maintModel->create([
    'vehicle_id' => $vehId1,
    'category_id' => $catId1,
    'service_date' => date('Y-m-d', strtotime('-5 days')),
    'workshop_name' => 'Oficina Mecânica Crateús Test',
    'os_number' => 'OS-REP-901',
    'cost' => 550.00,
    'status' => 'COMPLETED',
    'notes' => 'Troca de pastilhas e óleo do motor'
], $adminId);

// ── TESTE 1: Consulta do Relatório de Abastecimentos com filtros de data e secretaria ──
runTest('Consulta do Relatório de Abastecimentos com filtros de data e secretaria', function() use ($fuelModel, $vehId1, $secId1) {
    $results = $fuelModel->findAll([
        'start_date' => date('Y-m-d', strtotime('-15 days')),
        'end_date' => date('Y-m-d'),
        'secretariat_id' => $secId1,
        'vehicle_id' => $vehId1
    ]);

    if (count($results) < 2) {
        return 'Esperava pelo menos 2 abastecimentos retornados no filtro, obteve ' . count($results);
    }

    $first = $results[0];
    if (empty($first['vehicle_plate']) || empty($first['secretariat_name'])) {
        return 'Joins de veículo ou secretaria não foram retornados na consulta';
    }

    return true;
});

// ── TESTE 2: Consulta do Relatório de Manutenções com filtros de data e secretaria ──
runTest('Consulta do Relatório de Manutenções com filtros de data e secretaria', function() use ($maintModel, $vehId1, $secId1, $catId1) {
    $results = $maintModel->findAll([
        'start_date' => date('Y-m-d', strtotime('-15 days')),
        'end_date' => date('Y-m-d'),
        'secretariat_id' => $secId1,
        'vehicle_id' => $vehId1,
        'category_id' => $catId1,
        'status' => 'COMPLETED'
    ]);

    if (count($results) < 1) {
        return 'Esperava pelo menos 1 manutenção retornada no filtro, obteve ' . count($results);
    }

    $first = $results[0];
    if ($first['os_number'] !== 'OS-REP-901' || (float)$first['cost'] !== 550.0) {
        return 'Dados de custo ou OS incorretos na consulta de manutenções';
    }

    return true;
});

// ── TESTE 3: Cálculo do Relatório Consolidado de Gastos por Veículo ──
runTest('Cálculo do Relatório Consolidado de Gastos por Veículo', function() use ($vehId1, $secId1) {
    $controller = new ReportController();
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getConsolidatedReportData');
    $method->setAccessible(true);

    $data = $method->invoke($controller, [
        'start_date' => date('Y-m-d', strtotime('-15 days')),
        'end_date' => date('Y-m-d'),
        'secretariat_id' => $secId1,
        'vehicle_id' => $vehId1
    ]);

    if (empty($data)) {
        return 'Nenhum dado consolidado retornado para o veículo de teste';
    }

    $item = $data[0];
    if ((int)$item['fueling_count'] !== 2) {
        return 'Esperava 2 abastecimentos computados, obteve ' . $item['fueling_count'];
    }
    if ((float)$item['total_liters'] !== 85.0) {
        return 'Esperava 85L computados, obteve ' . $item['total_liters'];
    }
    if ((float)$item['fuel_cost'] !== 510.0) { // 240 + 270
        return 'Esperava R$ 510,00 de combustível, obteve ' . $item['fuel_cost'];
    }
    if ((float)$item['maintenance_cost'] !== 550.0) {
        return 'Esperava R$ 550,00 de manutenção, obteve ' . $item['maintenance_cost'];
    }
    if ((float)$item['total_cost'] !== 1060.0) { // 510 + 550
        return 'Esperava R$ 1060,00 de total geral, obteve ' . $item['total_cost'];
    }
    if ((int)$item['km_traveled'] !== 450) { // 10850 - 10400
        return 'Esperava 450 KM rodados no período, obteve ' . $item['km_traveled'];
    }

    return true;
});

// ── TESTE 4: Instanciação e Geração da Biblioteca Local FPDF ──
runTest('Instanciação e compilação do FPDF 1.86 local', function() {
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetFont('Helvetica', 'B', 16);
    $pdf->Cell(40, 10, 'SisFrota Test PDF');
    $output = $pdf->Output('S');

    if (empty($output)) {
        return 'Output binário do PDF retornou vazio';
    }
    if (!str_starts_with($output, '%PDF-1.')) {
        return 'Cabeçalho binário do PDF inválido: ' . substr($output, 0, 10);
    }

    return true;
});

// ── TESTE 5: Geração de Relatório Oficial com ReportPdfHelper (Logo, Cabeçalho e Tabela) ──
runTest('Geração de Relatório Oficial com ReportPdfHelper (Logo, Cabeçalho e Tabela)', function() {
    $user = [
        'full_name' => 'Diretor de Gestão',
        'role' => 'MANAGER'
    ];

    $pdf = new ReportPdfHelper('L', 'Relatório Oficial de Testes', 'Período: 01/08/2026 a 31/08/2026', $user);
    $pdf->AddPage();

    $columns = ['Data', 'Placa', 'Modelo', 'Secretaria', 'Valor Total'];
    $widths  = [30, 30, 70, 80, 40];
    $aligns  = ['C', 'C', 'L', 'L', 'R'];

    $pdf->renderTableHeader($columns, $widths, $aligns);
    $pdf->renderTableRow(['01/08/2026', 'ABC-1234', 'Gol 1.0 Flex', 'Secretaria de Saúde', 'R$ 250,00'], $widths, $aligns, false);
    $pdf->renderTableRow(['05/08/2026', 'XYZ-9876', 'Spin 1.8', 'Secretaria de Educação', 'R$ 320,00'], $widths, $aligns, true);
    $pdf->renderTableSummary(['TOTAL', '', '', '2 registros', 'R$ 570,00'], $widths, $aligns);

    $pdfContent = $pdf->Output('S');

    if (empty($pdfContent) || strlen($pdfContent) < 500) {
        return 'PDF compilado muito curto ou vazio: ' . strlen($pdfContent) . ' bytes';
    }

    return true;
});

// ── TESTE 6: Validação de Codificação e Cabeçalhos do CSV (BOM UTF-8 e Delimitador ;) ──
runTest('Validação de Codificação do CSV (BOM UTF-8 e Delimitador ;)', function() {
    $bom = "\xEF\xBB\xBF";
    $testRow = ['Data/Hora', 'Placa', 'Secretaria de Ação Social', 'R$ 1.250,00'];
    
    $fp = fopen('php://memory', 'r+');
    fwrite($fp, $bom);
    fputcsv($fp, $testRow, ';');
    rewind($fp);
    $csvContent = stream_get_contents($fp);
    fclose($fp);

    if (!str_starts_with($csvContent, $bom)) {
        return 'BOM UTF-8 não está no início do conteúdo CSV';
    }
    if (!str_contains($csvContent, 'Secretaria de Ação Social')) {
        return 'Acentuação no CSV não preservada';
    }
    if (!str_contains($csvContent, ';')) {
        return 'Delimitador ponto e vírgula não foi utilizado no CSV';
    }

    return true;
});

// ── TESTE 7: Controle de Acesso RBAC para Relatórios (ADMIN e MANAGER permitidos, OPERATOR negado) ──
runTest('Controle de Acesso RBAC para Relatórios (ADMIN e MANAGER permitidos, OPERATOR negado)', function() {
    // 1. ADMIN deve ter permissão
    $_SESSION['role'] = 'ADMIN';
    if (!AuthHelper::hasRole(['ADMIN', 'MANAGER'])) {
        return 'ADMIN deveria ter acesso a relatórios';
    }

    // 2. MANAGER deve ter permissão
    $_SESSION['role'] = 'MANAGER';
    if (!AuthHelper::hasRole(['ADMIN', 'MANAGER'])) {
        return 'MANAGER deveria ter acesso a relatórios';
    }

    // 3. OPERATOR NÃO deve ter permissão
    $_SESSION['role'] = 'OPERATOR';
    if (AuthHelper::hasRole(['ADMIN', 'MANAGER'])) {
        return 'OPERATOR NÃO deveria ter acesso a relatórios';
    }

    return true;
});

// ── Limpeza dos registros de teste ──
$pdo->exec("DELETE FROM maintenances WHERE vehicle_id IN ($vehId1, $vehId2)");
$pdo->exec("DELETE FROM fuelings WHERE vehicle_id IN ($vehId1, $vehId2)");
$pdo->exec("DELETE FROM vehicles WHERE id IN ($vehId1, $vehId2)");

echo "\n\n========================================================================\n";
echo " RESUMO DOS TESTES DA FASE 9\n";
echo "========================================================================\n";
echo " Total de testes executados : $totalTests\n";
echo " Testes aprovados           : \033[32m$passedTests\033[0m\n";
echo " Testes com falha           : " . ($failedTests > 0 ? "\033[31m$failedTests\033[0m" : "\033[32m0\033[0m") . "\n";
echo "========================================================================\n";

if ($failedTests > 0) {
    exit(1);
}
exit(0);
