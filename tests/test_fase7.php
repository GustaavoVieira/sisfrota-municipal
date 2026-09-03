<?php
/**
 * SisFrota Municipal - Teste da Fase 7 (CLI)
 * Valida o Maintenance Model, Transições de Status de Manutenção e Sincronização de Status do Veículo.
 */

define('SYS_ENTRY', true);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/FormatHelper.php';
require_once __DIR__ . '/../app/models/Vehicle.php';
require_once __DIR__ . '/../app/models/MaintenanceCategory.php';
require_once __DIR__ . '/../app/models/Maintenance.php';

echo "=========================================================\n";
echo " SisFrota Municipal — Teste de Validação da Fase 7 (CLI)\n";
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

$vehicleModel  = new Vehicle();
$categoryModel = new MaintenanceCategory();
$maintModel    = new Maintenance();

$testPlate = 'TST7M01';

// Limpar dados de testes prévios se existirem
$existingV = $vehicleModel->findByPlate($testPlate);
if ($existingV) {
    $vId = (int) $existingV['id'];
    $maintenances = $maintModel->findAll(['vehicle_id' => $vId]);
    foreach ($maintenances as $m) {
        $maintModel->delete((int) $m['id'], 1);
    }
    $vehicleModel->hardDelete($vId);
}

// Obter uma categoria ativa para teste
$categories = $categoryModel->findAllActive();
if (empty($categories)) {
    $catId = (int) $categoryModel->create([
        'name'        => 'Manutenção Preventiva Teste',
        'description' => 'Categoria de teste automatizado',
        'status'      => 'ACTIVE',
        'created_by'  => 1,
    ]);
} else {
    $catId = (int) $categories[0]['id'];
}

// ── 1. Criar Veículo de Teste ─────────────────────────────────────────────
echo "--- 1. Criando Veículo de Teste (Status Inicial: ACTIVE) ---\n";

$vId = $vehicleModel->create([
    'plate'      => $testPlate,
    'model'      => 'VW Gol 1.6 Teste Fase 7',
    'brand'      => 'Volkswagen',
    'year'       => 2023,
    'fuel_type'  => 'FLEX',
    'current_km' => 25000,
    'status'     => 'ACTIVE'
], 1);

assertTest($vId !== false && $vId > 0, "Veículo de teste $testPlate criado com ID $vId (Status: ACTIVE)");

// ── 2. Cadastrar Ordem de Serviço com Status IN_PROGRESS ──────────────────
echo "\n--- 2. Cadastrando Manutenção (Status: IN_PROGRESS) ---\n";

$res1 = $maintModel->create([
    'vehicle_id'   => $vId,
    'category_id'  => $catId,
    'service_date' => date('Y-m-d'),
    'workshop_name'=> 'Oficina Central Crateús',
    'os_number'    => 'OS-TEST-701',
    'cost'         => 1250.50,
    'status'       => 'IN_PROGRESS',
    'notes'        => 'Substituição de correia dentada e amortecedores.'
], 1);

assertTest($res1['success'] === true, "1ª Manutenção cadastrada com sucesso (OS-TEST-701, Status: IN_PROGRESS)");
$maintId1 = (int) ($res1['id'] ?? 0);

$vCheck1 = $vehicleModel->findById($vId);
assertTest($vCheck1 !== null && $vCheck1['status'] === 'MAINTENANCE', "Status do veículo atualizado automaticamente para MAINTENANCE");

// ── 3. Verificar Ocultação de Veículo em Manutenção ──────────────────────
echo "\n--- 3. Verificando Ocultação do Veículo em Seletores Ativos ---\n";

$activeVehicles = $vehicleModel->findAll(['status' => 'ACTIVE']);
$foundInActive  = false;
foreach ($activeVehicles as $av) {
    if ((int)$av['id'] === $vId) {
        $foundInActive = true;
        break;
    }
}
assertTest(!$foundInActive, "Veículo em estado MAINTENANCE foi omitido da lista de veículos ACTIVE para abastecimento");

// ── 4. Cadastrar 2ª Manutenção com Status PENDING ─────────────────────────
echo "\n--- 4. Cadastrando 2ª Manutenção (Status: PENDING) ---\n";

$res2 = $maintModel->create([
    'vehicle_id'   => $vId,
    'category_id'  => $catId,
    'service_date' => date('Y-m-d'),
    'workshop_name'=> 'Auto Elétrica Crateús',
    'os_number'    => 'OS-TEST-702',
    'cost'         => 350.00,
    'status'       => 'PENDING',
    'notes'        => 'Aguardando chegada de alternador original.'
], 1);

assertTest($res2['success'] === true, "2ª Manutenção cadastrada com sucesso (OS-TEST-702, Status: PENDING)");
$maintId2 = (int) ($res2['id'] ?? 0);

$vCheck2 = $vehicleModel->findById($vId);
assertTest($vCheck2 !== null && $vCheck2['status'] === 'MAINTENANCE', "Status do veículo permanece MAINTENANCE com 2 OS pendentes");

// ── 5. Concluir 1ª Manutenção (Ainda deve permanecer MAINTENANCE pela 2ª) ──
echo "\n--- 5. Concluindo 1ª Manutenção (Status -> COMPLETED) ---\n";

$resUp1 = $maintModel->update($maintId1, [
    'vehicle_id'   => $vId,
    'category_id'  => $catId,
    'service_date' => date('Y-m-d'),
    'workshop_name'=> 'Oficina Central Crateús',
    'os_number'    => 'OS-TEST-701',
    'cost'         => 1250.50,
    'status'       => 'COMPLETED',
    'notes'        => 'Serviço concluído com teste de rodagem aprovado.'
], 1);

assertTest($resUp1['success'] === true, "1ª Manutenção atualizada para COMPLETED");

$vCheck3 = $vehicleModel->findById($vId);
assertTest($vCheck3 !== null && $vCheck3['status'] === 'MAINTENANCE', "Veículo continua em MAINTENANCE pois a 2ª OS (OS-TEST-702) ainda está PENDING");

// ── 6. Concluir 2ª Manutenção (Status do veículo DEVE restaurar para ACTIVE)
echo "\n--- 6. Concluindo 2ª Manutenção (Restauração Automática para ACTIVE) ---\n";

$resUp2 = $maintModel->update($maintId2, [
    'vehicle_id'   => $vId,
    'category_id'  => $catId,
    'service_date' => date('Y-m-d'),
    'workshop_name'=> 'Auto Elétrica Crateús',
    'os_number'    => 'OS-TEST-702',
    'cost'         => 350.00,
    'status'       => 'COMPLETED',
    'notes'        => 'Peça instalada e sistema elétrico liberado.'
], 1);

assertTest($resUp2['success'] === true, "2ª Manutenção atualizada para COMPLETED");

$vCheck4 = $vehicleModel->findById($vId);
assertTest($vCheck4 !== null && $vCheck4['status'] === 'ACTIVE', "Status do veículo restaurado automaticamente para ACTIVE com todas as manutenções concluídas!");

// ── 7. Testar Consultas Filtradas ──────────────────────────────────────────
echo "\n--- 7. Testando Consultas Filtradas de Manutenções ---\n";

$maintList = $maintModel->findAll(['vehicle_id' => $vId]);
assertTest(count($maintList) === 2, "Listagem retornou exatamente as 2 ordens de manutenção do veículo");

$filterSearch = $maintModel->findAll(['search' => 'OS-TEST-701']);
assertTest(count($filterSearch) === 1 && $filterSearch[0]['os_number'] === 'OS-TEST-701', "Filtro por número de OS funcionou perfeitamente");

// ── 8. Limpeza dos Dados de Teste ───────────────────────────────────────────
echo "\n--- 8. Limpeza dos Dados de Teste ---\n";

$maintModel->delete($maintId1, 1);
$maintModel->delete($maintId2, 1);
$vehicleModel->hardDelete($vId);

$vCheckClean = $vehicleModel->findById($vId, true);
assertTest($vCheckClean === null, "Veículo de teste e suas ordens de manutenção foram limpos com sucesso");

echo "\n=========================================================\n";
echo " Resultado dos Testes: $passCount PASSADOS | $failCount FALHADOS\n";
echo "=========================================================\n";

if ($failCount > 0) {
    exit(1);
}
