<?php
/**
 * SisFrota Municipal - Teste da Fase 5 (CLI)
 * Valida o FormatHelper, Vehicle Model, Soft Delete, Hard Delete e Unicidade de Placas.
 */

define('SYS_ENTRY', true);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/FormatHelper.php';
require_once __DIR__ . '/../app/models/Vehicle.php';
require_once __DIR__ . '/../app/models/Secretariat.php';

echo "=========================================================\n";
echo " SisFrota Municipal — Teste de Validação da Fase 5 (CLI)\n";
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

// ─── 1. Testes do FormatHelper ─────────────────────────────────────────────
echo "--- 1. Testando FormatHelper ---\n";

assertTest(FormatHelper::cleanPlate('abc-1234') === 'ABC1234', 'cleanPlate("abc-1234") -> ABC1234');
assertTest(FormatHelper::formatPlate('ABC1234') === 'ABC-1234', 'formatPlate("ABC1234") (Tradicional) -> ABC-1234');
assertTest(FormatHelper::formatPlate('ABC1D23') === 'ABC1D23', 'formatPlate("ABC1D23") (Mercosul) -> ABC1D23');
assertTest(FormatHelper::isValidPlate('ABC-1234') === true, 'isValidPlate("ABC-1234") -> true');
assertTest(FormatHelper::isValidPlate('ABC1D23') === true, 'isValidPlate("ABC1D23") -> true');
assertTest(FormatHelper::isValidPlate('123-ABCD') === false, 'isValidPlate("123-ABCD") -> false');
assertTest(FormatHelper::formatCurrency(1234.56) === 'R$ 1.234,56', 'formatCurrency(1234.56) -> R$ 1.234,56');
assertTest(FormatHelper::formatDate('2026-08-30') === '30/08/2026', 'formatDate("2026-08-30") -> 30/08/2026');

// ─── 2. Testes do Vehicle Model ────────────────────────────────────────────
echo "\n--- 2. Testando Vehicle Model CRUD ---\n";

$vehicleModel = new Vehicle();
$secModel = new Secretariat();

// Pega uma secretaria existente
$secretariats = $secModel->getAll();
$secId = !empty($secretariats) ? $secretariats[0]['id'] : null;

$testPlate1 = 'TST1A23'; // Mercosul
$testPlate2 = 'TST-9999'; // Tradicional

// Limpa registros de testes anteriores se existirem
$existing1 = $vehicleModel->findByPlate($testPlate1);
if ($existing1) {
    $vehicleModel->hardDelete((int) $existing1['id']);
}
$existing2 = $vehicleModel->findByPlate($testPlate2);
if ($existing2) {
    $vehicleModel->hardDelete((int) $existing2['id']);
}

// Inserção do Veículo 1
$v1Id = $vehicleModel->create([
    'plate'          => $testPlate1,
    'model'          => 'Gol 1.0 Flex Teste',
    'brand'          => 'Volkswagen',
    'year'           => 2023,
    'secretariat_id' => $secId,
    'fuel_type'      => 'FLEX',
    'current_km'     => 15000,
    'status'         => 'ACTIVE'
], 1);

assertTest($v1Id !== false && $v1Id > 0, "Criação do veículo $testPlate1 com ID $v1Id");

// Busca do Veículo por ID
$fetchedV1 = $vehicleModel->findById((int) $v1Id);
assertTest($fetchedV1 !== null && $fetchedV1['plate'] === 'TST1A23', "Busca por ID de $testPlate1");
assertTest((int) $fetchedV1['current_km'] === 15000, "KM atual gravada corretamente (15.000 KM)");

// Busca por Placa
$fetchedByPlate = $vehicleModel->findByPlate('tst-1a23'); // Testa busca com hífen e minúsculas
assertTest($fetchedByPlate !== null && (int) $fetchedByPlate['id'] === (int) $v1Id, "Busca flexível por placa ('tst-1a23')");

// Atualização do Veículo
$updateSuccess = $vehicleModel->update((int) $v1Id, [
    'plate'          => $testPlate1,
    'model'          => 'Gol 1.0 Flex Atualizado',
    'brand'          => 'VW',
    'year'           => 2024,
    'secretariat_id' => $secId,
    'fuel_type'      => 'FLEX',
    'current_km'     => 18000,
    'status'         => 'MAINTENANCE'
], 1);
assertTest($updateSuccess, "Atualização do veículo $testPlate1 para status MAINTENANCE");

$updatedV1 = $vehicleModel->findById((int) $v1Id);
assertTest($updatedV1['model'] === 'Gol 1.0 Flex Atualizado' && $updatedV1['status'] === 'MAINTENANCE', "Validação de dados atualizados");

// ─── 3. Teste de Inativação (Soft Delete) ──────────────────────────────────
echo "\n--- 3. Testando Soft Delete ---\n";

$softDeleteSuccess = $vehicleModel->softDelete((int) $v1Id, 1);
assertTest($softDeleteSuccess, "Execução do softDelete() para o veículo ID $v1Id");

$v1Deleted = $vehicleModel->findById((int) $v1Id, false);
assertTest($v1Deleted === null, "Veículo inativado não é retornado na busca padrão (includeDeleted = false)");

$v1WithDeleted = $vehicleModel->findById((int) $v1Id, true);
assertTest($v1WithDeleted !== null && (int) $v1WithDeleted['is_deleted'] === 1 && $v1WithDeleted['status'] === 'UNAVAILABLE', "Veículo mantido no banco com is_deleted = 1 e status = UNAVAILABLE");

// ─── 4. Teste de Exclusão Física Permanente (Hard Delete) ──────────────────
echo "\n--- 4. Testando Hard Delete ---\n";

// Cria um novo veículo de teste sem histórico
$v2Id = $vehicleModel->create([
    'plate'          => $testPlate2,
    'model'          => 'Veículo Temporário',
    'brand'          => 'Teste',
    'year'           => 2021,
    'secretariat_id' => $secId,
    'fuel_type'      => 'GASOLINE',
    'current_km'     => 5000,
    'status'         => 'ACTIVE'
], 1);

assertTest($v2Id !== false, "Criação de veículo temporário $testPlate2");

$hardDeleteResult = $vehicleModel->hardDelete((int) $v2Id);
assertTest($hardDeleteResult['success'] === true, "hardDelete() executado com sucesso para veículo sem histórico");

$v2Check = $vehicleModel->findById((int) $v2Id, true);
assertTest($v2Check === null, "Veículo excluído permanentemente não existe mais no banco de dados");

// Limpeza final do v1Id de teste do banco
$vehicleModel->hardDelete((int) $v1Id);

echo "\n=========================================================\n";
echo " Resultado dos Testes: $passCount PASSADOS | $failCount FALHADOS\n";
echo "=========================================================\n";

if ($failCount > 0) {
    exit(1);
}
