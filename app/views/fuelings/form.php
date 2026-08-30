<?php
/**
 * SisFrota Municipal - View: Formulário de Abastecimento (Novo / Editar)
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

$isEdit = !empty($fueling);
$formTitle = $isEdit ? 'Editar Abastecimento #' . $fueling['id'] : 'Novo Abastecimento';
$formAction = $isEdit ? BASE_URL . '/fuelings/update/' . $fueling['id'] : BASE_URL . '/fuelings/store';

// Restauração de dados submetidos em caso de erro de validação
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

$vehicleIdVal   = $formData['vehicle_id'] ?? ($fueling['vehicle_id'] ?? ($selectedVehicleId ?? ''));
$fuelingDateVal = $formData['fueling_date'] ?? ($fueling['fueling_date'] ?? date('Y-m-d\TH:i'));
if (str_contains($fuelingDateVal, ' ') && !str_contains($fuelingDateVal, 'T')) {
    $fuelingDateVal = date('Y-m-d\TH:i', strtotime($fuelingDateVal));
}

$kmVal          = $formData['km'] ?? ($fueling['km'] ?? '');
$litersVal      = $formData['liters'] ?? ($fueling['liters'] ?? '');
$totalPriceVal  = $formData['total_price'] ?? ($fueling['total_price'] ?? '');
$fuelTypeVal    = $formData['fuel_type'] ?? ($fueling['fuel_type'] ?? 'FLEX');
$stationNameVal = $formData['station_name'] ?? ($fueling['station_name'] ?? '');
?>

<div class="mb-4">
    <a href="<?= BASE_URL ?>/fuelings" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1">
        ‹ Voltar para Abastecimentos
    </a>
    <h1 class="h3 mb-0 text-slate-800 fw-bold mt-2"><?= htmlspecialchars($formTitle, ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="text-muted small mb-0">Informe os dados operacionais do abastecimento de combustível</p>
</div>

<!-- Mensagens de Erro -->
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show border border-danger-subtle shadow-sm" role="alert">
        <div class="d-flex align-items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="text-danger" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
            </svg>
            <div class="fw-bold">
                <?= htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-subtle shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="<?= $formAction ?>" id="fuelingForm">
                    <input type="hidden" name="csrf_token" value="<?= AuthHelper::generateCsrfToken() ?>">

                    <!-- Veículo -->
                    <div class="mb-4">
                        <label for="vehicle_id" class="form-label fw-bold text-dark">Veículo <span class="text-danger">*</span></label>
                        <select class="form-select form-select-lg" id="vehicle_id" name="vehicle_id" required <?= $isEdit ? 'disabled' : '' ?>>
                            <option value="">-- Selecione o Veículo --</option>
                            <?php foreach ($vehicles as $v): ?>
                                <option value="<?= $v['id'] ?>" 
                                        data-km="<?= $v['current_km'] ?>"
                                        data-fuel="<?= $v['fuel_type'] ?>"
                                        <?= ((string)$vehicleIdVal === (string)$v['id']) ? 'selected' : '' ?>>
                                    [<?= htmlspecialchars(FormatHelper::formatPlate($v['plate']), ENT_QUOTES, 'UTF-8') ?>] 
                                    <?= htmlspecialchars($v['model'], ENT_QUOTES, 'UTF-8') ?> 
                                    — KM Atual: <?= FormatHelper::formatNumber($v['current_km'], 0) ?> KM
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="vehicle_id" value="<?= $fueling['vehicle_id'] ?>">
                        <?php endif; ?>
                        <div class="form-text text-muted" id="vehicleKmNotice">
                            Selecione um veículo para visualizar sua última quilometragem registrada.
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <!-- Data e Hora -->
                        <div class="col-md-6">
                            <label for="fueling_date" class="form-label fw-semibold text-muted">Data e Hora do Abastecimento <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="fueling_date" name="fueling_date" 
                                   value="<?= htmlspecialchars($fuelingDateVal, ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>

                        <!-- Quilometragem no Momento -->
                        <div class="col-md-6">
                            <label for="km" class="form-label fw-semibold text-muted">KM Atual no Abastecimento <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control data-mono fw-bold" id="km" name="km" 
                                       value="<?= htmlspecialchars($kmVal, ENT_QUOTES, 'UTF-8') ?>" 
                                       min="1" placeholder="Ex: 45200" required>
                                <span class="input-group-text bg-light text-muted">KM</span>
                            </div>
                            <div class="form-text text-danger fw-semibold" id="kmWarning" style="display:none;">
                                ⚠️ A KM digitada deve ser maior que a KM atual do veículo!
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <!-- Quantidade de Litros -->
                        <div class="col-md-4">
                            <label for="liters" class="form-label fw-semibold text-muted">Litros <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0.01" class="form-control data-mono" id="liters" name="liters" 
                                       value="<?= htmlspecialchars($litersVal, ENT_QUOTES, 'UTF-8') ?>" 
                                       placeholder="0.00" required>
                                <span class="input-group-text bg-light text-muted">L</span>
                            </div>
                        </div>

                        <!-- Valor Total em R$ -->
                        <div class="col-md-4">
                            <label for="total_price" class="form-label fw-semibold text-muted">Valor Total (R$) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">R$</span>
                                <input type="number" step="0.01" min="0.01" class="form-control data-mono fw-bold text-success" id="total_price" name="total_price" 
                                       value="<?= htmlspecialchars($totalPriceVal, ENT_QUOTES, 'UTF-8') ?>" 
                                       placeholder="0.00" required>
                            </div>
                        </div>

                        <!-- Tipo de Combustível -->
                        <div class="col-md-4">
                            <label for="fuel_type" class="form-label fw-semibold text-muted">Tipo de Combustível <span class="text-danger">*</span></label>
                            <select class="form-select" id="fuel_type" name="fuel_type" required>
                                <option value="GASOLINE" <?= ($fuelTypeVal === 'GASOLINE') ? 'selected' : '' ?>>Gasolina</option>
                                <option value="ETHANOL"  <?= ($fuelTypeVal === 'ETHANOL') ? 'selected' : '' ?>>Etanol</option>
                                <option value="DIESEL"   <?= ($fuelTypeVal === 'DIESEL') ? 'selected' : '' ?>>Diesel</option>
                                <option value="FLEX"     <?= ($fuelTypeVal === 'FLEX') ? 'selected' : '' ?>>Flex</option>
                            </select>
                        </div>
                    </div>

                    <!-- Posto Fornecedor -->
                    <div class="mb-4">
                        <label for="station_name" class="form-label fw-semibold text-muted">Posto Fornecedor / Conveniado <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="station_name" name="station_name" 
                               value="<?= htmlspecialchars($stationNameVal, ENT_QUOTES, 'UTF-8') ?>" 
                               placeholder="Ex: Posto Central Crateús, Posto Petromax..." required>
                    </div>

                    <!-- Botões -->
                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="<?= BASE_URL ?>/fuelings" class="btn btn-outline-secondary px-4">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <?= $isEdit ? 'Atualizar Abastecimento' : 'Salvar Abastecimento' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Card Lateral Orientativo de Segurança -->
    <div class="col-lg-4">
        <div class="card border-subtle shadow-sm mb-4 bg-light">
            <div class="card-body">
                <h5 class="card-title h6 fw-bold text-dark d-flex align-items-center gap-2">
                    <span>🛡️</span> Regras de Validação Bloqueante
                </h5>
                <hr class="my-2 border-subtle">
                <ul class="small text-muted ps-3 mb-0">
                    <li class="mb-2"><strong>Trava Rígida de Quilometragem:</strong> A KM informada precisa ser <u>estritamente maior</u> que a última quilometragem gravada para o veículo.</li>
                    <li class="mb-2"><strong>Cálculo de Consumo Médio ($\text{KM/L}$):** É realizado automaticamente pelo sistema a partir do 2º abastecimento do veículo.</li>
                    <li class="mb-2"><strong>Atualização Automática:</strong> A quilometragem do veículo no cadastro geral será atualizada para a nova KM informada após o salvamento.</li>
                    <li><strong>Auditoria:</strong> Todos os lançamentos registram a data/hora e a identificação do servidor responsável.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const vehicleSelect = document.getElementById('vehicle_id');
    const kmInput = document.getElementById('km');
    const kmNotice = document.getElementById('vehicleKmNotice');
    const kmWarning = document.getElementById('kmWarning');
    const fuelSelect = document.getElementById('fuel_type');

    function updateVehicleInfo() {
        const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const currentKm = parseInt(selectedOption.getAttribute('data-km')) || 0;
            const defaultFuel = selectedOption.getAttribute('data-fuel');

            kmNotice.innerHTML = '📌 <strong>Quilometragem Mínima Obrigatória:</strong> Deve ser maior que <strong>' + currentKm.toLocaleString('pt-BR') + ' KM</strong>.';
            
            if (defaultFuel && !'<?= $isEdit ? "1" : "" ?>') {
                fuelSelect.value = defaultFuel;
            }

            if (kmInput.value) {
                const enteredKm = parseInt(kmInput.value) || 0;
                if (enteredKm <= currentKm) {
                    kmWarning.style.display = 'block';
                } else {
                    kmWarning.style.display = 'none';
                }
            }
        } else {
            kmNotice.innerHTML = 'Selecione um veículo para visualizar sua última quilometragem registrada.';
            kmWarning.style.display = 'none';
        }
    }

    vehicleSelect.addEventListener('change', updateVehicleInfo);
    kmInput.addEventListener('input', updateVehicleInfo);
    
    if (vehicleSelect.value) {
        updateVehicleInfo();
    }
});
</script>
