<?php
/**
 * SisFrota Municipal - Teste da Fase 6 (CLI)
 * Valida o Fueling Model, Trava Bloqueante de KM, Cálculo de KM/L e Atualização do Veículo.
 */

define('SYS_ENTRY', true);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/FormatHelper.php';
require_once __DIR__ . '/../app/models/Vehicle.php';
require_once __DIR__ . '/../app/models/Fueling.php';

echo "=========================================================\n";
echo " SisFrota Municipal — Teste de Validação da Fase 6 (CLI)\n";
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

$vehicleModel = new Vehicle();
$fuelingModel = new Fueling();

$testPlate = 'TST6F01';

// Limpar dados de testes prévios
$existingV = $vehicleModel->findByPlate($testPlate);
if ($existingV) {
    $vId = (int) $existingV['id'];
    $fuelings = $fuelingModel->findAll(['vehicle_id' => $vId]);
    foreach ($fuelings as $f) {
        $fuelingModel->delete((int) $f['id'], 1);
    }
    $vehicleModel->hardDelete($vId);
}

// ── 1. Criar Veículo para Teste ─────────────────────────────────────────────
echo "--- 1. Criando Veículo de Teste ---\n";

$vId = $vehicleModel->create([
    'plate'      => $testPlate,
    'model'      => 'Fiat Argo 1.3 Teste',
    'brand'      => 'Fiat',
    'year'       => 2024,
    'fuel_type'  => 'FLEX',
    'current_km' => 10000,
    'status'     => 'ACTIVE'
], 1);

assertTest($vId !== false && $vId > 0, "Veículo de teste $testPlate criado com ID $vId (KM inicial: 10.000)");

// ── 2. Testar 1º Abastecimento (avg_kml deve ser NULL) ──────────────────────
echo "\n--- 2. Testando 1º Abastecimento (avg_kml = NULL) ---\n";

$res1 = $fuelingModel->create([
    'vehicle_id'   => $vId,
    'fueling_date' => '2026-08-30 08:00:00',
    'km'           => 10500,
    'liters'       => 40.0,
    'total_price'  => 220.00,
    'fuel_type'    => 'GASOLINE',
    'station_name' => 'Posto Central Crateús'
], 1);

assertTest($res1['success'] === true, "1º Abastecimento (10.500 KM) gravado com sucesso");

$f1 = $fuelingModel->findById((int) ($res1['id'] ?? 0));
assertTest($f1 !== null && $f1['avg_kml'] === null, "Consumo médio (avg_kml) do 1º abastecimento é NULL");

$vCheck1 = $vehicleModel->findById((int) $vId);
assertTest((int) $vCheck1['current_km'] === 10500, "KM atual do veículo atualizada para 10.500 KM");

// ── 3. Testar 2º Abastecimento (avg_kml deve ser calculado) ─────────────────
echo "\n--- 3. Testando 2º Abastecimento (Cálculo KM/L) ---\n";

// (11.000 - 10.500) = 500 KM / 50 Litros = 10,00 KM/L
$res2 = $fuelingModel->create([
    'vehicle_id'   => $vId,
    'fueling_date' => '2026-08-30 12:00:00',
    'km'           => 11000,
    'liters'       => 50.0,
    'total_price'  => 275.00,
    'fuel_type'    => 'GASOLINE',
    'station_name' => 'Posto Petromax'
], 1);

assertTest($res2['success'] === true, "2º Abastecimento (11.000 KM) gravado com sucesso");

$f2 = $fuelingModel->findById((int) ($res2['id'] ?? 0));
assertTest($f2 !== null && (float) $f2['avg_kml'] === 10.00, "Cálculo correto de consumo médio: (11.000 - 10.500) / 50 = 10,00 KM/L");

$vCheck2 = $vehicleModel->findById((int) $vId);
assertTest((int) $vCheck2['current_km'] === 11000, "KM atual do veículo atualizada para 11.000 KM");

// ── 4. Testar Trava Bloqueante de Quilometragem ─────────────────────────────
echo "\n--- 4. Testando Trava Bloqueante Rígida de KM ---\n";

// Tentativa 1: KM menor (10.800 KM < 11.000 KM)
$resInvalid1 = $fuelingModel->create([
    'vehicle_id'   => $vId,
    'fueling_date' => '2026-08-30 15:00:00',
    'km'           => 10800,
    'liters'       => 30.0,
    'total_price'  => 165.00,
    'fuel_type'    => 'GASOLINE',
    'station_name' => 'Posto Central Crateús'
], 1);

assertTest($resInvalid1['success'] === false, "Rejeição bloqueante para KM menor (10.800 KM <= 11.000 KM)");
assertTest(str_contains($resInvalid1['message'], 'menor ou igual'), "Mensagem de erro clara informando trava de KM");

// Tentativa 2: KM igual (11.000 KM == 11.000 KM)
$resInvalid2 = $fuelingModel->create([
    'vehicle_id'   => $vId,
    'fueling_date' => '2026-08-30 15:30:00',
    'km'           => 11000,
    'liters'       => 30.0,
    'total_price'  => 165.00,
    'fuel_type'    => 'GASOLINE',
    'station_name' => 'Posto Central Crateús'
], 1);

assertTest($resInvalid2['success'] === false, "Rejeição bloqueante para KM igual (11.000 KM == 11.000 KM)");

// ── 5. Testar 3º Abastecimento Válido ───────────────────────────────────────
echo "\n--- 5. Testando 3º Abastecimento Válido ---\n";

// (11.600 - 11.000) = 600 KM / 50 Litros = 12,00 KM/L
$res3 = $fuelingModel->create([
    'vehicle_id'   => $vId,
    'fueling_date' => '2026-08-30 18:00:00',
    'km'           => 11600,
    'liters'       => 50.0,
    'total_price'  => 275.00,
    'fuel_type'    => 'GASOLINE',
    'station_name' => 'Posto Petromax'
], 1);

assertTest($res3['success'] === true, "3º Abastecimento (11.600 KM) gravado com sucesso");

$f3 = $fuelingModel->findById((int) ($res3['id'] ?? 0));
assertTest($f3 !== null && (float) $f3['avg_kml'] === 12.00, "Cálculo correto de consumo médio: (11.600 - 11.000) / 50 = 12,00 KM/L");

$vCheck3 = $vehicleModel->findById((int) $vId);
assertTest((int) $vCheck3['current_km'] === 11600, "KM atual do veículo atualizada para 11.600 KM");

// ── 6. Testar Consulta Filtrada de Abastecimentos ────────────────────────────
echo "\n--- 6. Testando Consulta Filtrada ---\n";

$fuelingList = $fuelingModel->findAll(['vehicle_id' => $vId]);
assertTest(count($fuelingList) === 3, "Listagem retornou exatamente os 3 abastecimentos do veículo de teste");

// ── 7. Limpeza dos Dados de Teste ───────────────────────────────────────────
echo "\n--- 7. Limpeza dos Dados de Teste ---\n";

foreach ($fuelingList as $f) {
    $fuelingModel->delete((int) $f['id'], 1);
}
$vehicleModel->hardDelete((int) $vId);

$vCheckClean = $vehicleModel->findById((int) $vId, true);
assertTest($vCheckClean === null, "Veículo de teste e seus abastecimentos foram limpos com sucesso");

echo "\n=========================================================\n";
echo " Resultado dos Testes: $passCount PASSADOS | $failCount FALHADOS\n";
echo "=========================================================\n";

if ($failCount > 0) {
    exit(1);
}
