<?php
/**
 * SisFrota Municipal - View: Formulário de Veículo (Cadastro e Edição)
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

$isEdit = !empty($vehicle['id']);
$formAction = $isEdit ? BASE_URL . '/vehicles/update/' . $vehicle['id'] : BASE_URL . '/vehicles/store';
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']); // limpa após usar

// Valores preenchidos (prioriza form_data em caso de erro, depois $vehicle, depois valor default)
$valPlate         = $formData['plate'] ?? ($vehicle['plate'] ?? '');
$valModel         = $formData['model'] ?? ($vehicle['model'] ?? '');
$valBrand         = $formData['brand'] ?? ($vehicle['brand'] ?? '');
$valYear          = $formData['year'] ?? ($vehicle['year'] ?? '');
$valSecretariatId = $formData['secretariat_id'] ?? ($vehicle['secretariat_id'] ?? '');
$valFuelType      = $formData['fuel_type'] ?? ($vehicle['fuel_type'] ?? 'FLEX');
$valCurrentKm     = $formData['current_km'] ?? ($vehicle['current_km'] ?? 0);
$valStatus        = $formData['status'] ?? ($vehicle['status'] ?? 'ACTIVE');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-slate-800 fw-bold">
            <?= $isEdit ? 'Editar Veículo' : 'Novo Veículo' ?>
        </h1>
        <p class="text-muted small mb-0">
            <?= $isEdit ? 'Atualize as informações do veículo da frota' : 'Cadastre um novo veículo na frota municipal' ?>
        </p>
    </div>
    <a href="<?= BASE_URL ?>/vehicles" class="btn btn-outline-secondary">
        ← Voltar para a Frota
    </a>
</div>

<!-- Flash Error -->
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="card border-subtle shadow-sm max-w-800">
    <div class="card-body p-4">
        <form method="POST" action="<?= $formAction ?>">
            <input type="hidden" name="csrf_token" value="<?= AuthHelper::generateCsrfToken() ?>">

            <div class="row g-3 mb-3">
                <!-- Placa -->
                <div class="col-md-6">
                    <label for="plate" class="form-label fw-semibold">Placa <span class="text-danger">*</span></label>
                    <input type="text" class="form-control data-mono uppercase" id="plate" name="plate" 
                           value="<?= htmlspecialchars(FormatHelper::formatPlate($valPlate), ENT_QUOTES, 'UTF-8') ?>" 
                           placeholder="ABC-1234 ou ABC1D23" maxlength="8" required autofocus>
                    <div class="form-text">Aceita padrão antigo (ABC-1234) ou Mercosul (ABC1D23).</div>
                </div>

                <!-- Modelo -->
                <div class="col-md-6">
                    <label for="model" class="form-label fw-semibold">Modelo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="model" name="model" 
                           value="<?= htmlspecialchars($valModel, ENT_QUOTES, 'UTF-8') ?>" 
                           placeholder="Ex: Gol 1.0, L200 Triton, Ambulance" required>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <!-- Marca -->
                <div class="col-md-6">
                    <label for="brand" class="form-label fw-semibold">Marca / Fabricante</label>
                    <input type="text" class="form-control" id="brand" name="brand" 
                           value="<?= htmlspecialchars($valBrand, ENT_QUOTES, 'UTF-8') ?>" 
                           placeholder="Ex: Volkswagen, Fiat, Mitsubishi">
                </div>

                <!-- Ano -->
                <div class="col-md-6">
                    <label for="year" class="form-label fw-semibold">Ano de Fabricação</label>
                    <input type="number" class="form-control data-mono" id="year" name="year" 
                           value="<?= htmlspecialchars($valYear, ENT_QUOTES, 'UTF-8') ?>" 
                           placeholder="Ex: 2022" min="1970" max="<?= date('Y') + 1 ?>">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <!-- Secretaria -->
                <div class="col-md-6">
                    <label for="secretariat_id" class="form-label fw-semibold">Secretaria / Setor Responsável</label>
                    <select class="form-select" id="secretariat_id" name="secretariat_id">
                        <option value="">Selecione a Secretaria...</option>
                        <?php foreach ($secretariats as $sec): ?>
                            <option value="<?= $sec['id'] ?>" <?= ($valSecretariatId == $sec['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sec['name'], ENT_QUOTES, 'UTF-8') ?> 
                                <?= !empty($sec['acronym']) ? '(' . htmlspecialchars($sec['acronym'], ENT_QUOTES, 'UTF-8') . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tipo de Combustível Padrão -->
                <div class="col-md-6">
                    <label for="fuel_type" class="form-label fw-semibold">Combustível Padrão <span class="text-danger">*</span></label>
                    <select class="form-select" id="fuel_type" name="fuel_type" required>
                        <option value="FLEX" <?= ($valFuelType === 'FLEX') ? 'selected' : '' ?>>Flex (Gasolina / Etanol)</option>
                        <option value="GASOLINE" <?= ($valFuelType === 'GASOLINE') ? 'selected' : '' ?>>Gasolina</option>
                        <option value="ETHANOL" <?= ($valFuelType === 'ETHANOL') ? 'selected' : '' ?>>Etanol</option>
                        <option value="DIESEL" <?= ($valFuelType === 'DIESEL') ? 'selected' : '' ?>>Diesel</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <!-- KM Atual -->
                <div class="col-md-6">
                    <label for="current_km" class="form-label fw-semibold">KM Atual Hodômetro <span class="text-danger">*</span></label>
                    <input type="number" class="form-control data-mono" id="current_km" name="current_km" 
                           value="<?= (int) $valCurrentKm ?>" min="0" required>
                    <div class="form-text">Será a referência mínima para os lançamentos de abastecimento.</div>
                </div>

                <!-- Status -->
                <div class="col-md-6">
                    <label for="status" class="form-label fw-semibold">Status Operacional <span class="text-danger">*</span></label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="ACTIVE" <?= ($valStatus === 'ACTIVE') ? 'selected' : '' ?>>Ativo (Disponível)</option>
                        <option value="MAINTENANCE" <?= ($valStatus === 'MAINTENANCE') ? 'selected' : '' ?>>Em Manutenção</option>
                        <option value="UNAVAILABLE" <?= ($valStatus === 'UNAVAILABLE') ? 'selected' : '' ?>>Indisponível</option>
                    </select>
                </div>
            </div>

            <hr class="my-4 border-subtle">

            <div class="d-flex justify-content-end gap-2">
                <a href="<?= BASE_URL ?>/vehicles" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                    <?= $isEdit ? 'Salvar Alterações' : 'Cadastrar Veículo' ?>
                </button>
            </div>
        </form>
    </div>
</div>
