<?php
/**
 * SisFrota Municipal - View: Listagem de Abastecimentos
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-slate-800 fw-bold">Gestão de Abastecimentos</h1>
        <p class="text-muted small mb-0">Registro e histórico detalhado de consumo de combustível da frota municipal</p>
    </div>
    <?php if (AuthHelper::hasRole(['ADMIN', 'OPERATOR'])): ?>
        <a href="<?= BASE_URL ?>/fuelings/new" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Novo Abastecimento</span>
        </a>
    <?php endif; ?>
</div>

<!-- Flash Messages -->
<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Toolbar de Filtros -->
<div class="card border-subtle shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/fuelings" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label small fw-semibold text-muted">Buscar (Placa, Modelo, Posto)</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?= htmlspecialchars($filters['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                       placeholder="Ex: ABC-1234, Posto Ipiranga...">
            </div>

            <div class="col-md-3">
                <label for="vehicle_id" class="form-label small fw-semibold text-muted">Veículo</label>
                <select class="form-select" id="vehicle_id" name="vehicle_id">
                    <option value="">Todos os Veículos</option>
                    <?php foreach ($vehicles as $v): ?>
                        <option value="<?= $v['id'] ?>" <?= ($filters['vehicle_id'] == $v['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(FormatHelper::formatPlate($v['plate']), ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($v['model'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label for="start_date" class="form-label small fw-semibold text-muted">Data Inicial</label>
                <input type="date" class="form-control" id="start_date" name="start_date" 
                       value="<?= htmlspecialchars($filters['start_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="col-md-2">
                <label for="end_date" class="form-label small fw-semibold text-muted">Data Final</label>
                <input type="date" class="form-control" id="end_date" name="end_date" 
                       value="<?= htmlspecialchars($filters['end_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-secondary w-100">Filtrar</button>
                <?php if (!empty($filters['search']) || !empty($filters['vehicle_id']) || !empty($filters['start_date']) || !empty($filters['end_date']) || !empty($filters['fuel_type'])): ?>
                    <a href="<?= BASE_URL ?>/fuelings" class="btn btn-outline-secondary" title="Limpar Filtros">✕</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Tabela de Abastecimentos -->
<div class="card border-subtle shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-3">Data / Hora</th>
                        <th scope="col">Veículo</th>
                        <th scope="col">Posto Fornecedor</th>
                        <th scope="col">Combustível</th>
                        <th scope="col" class="text-end">KM Momento</th>
                        <th scope="col" class="text-end">Litros</th>
                        <th scope="col" class="text-end">Valor Total (R$)</th>
                        <th scope="col" class="text-end">Média (KM/L)</th>
                        <th scope="col" class="text-end pe-3">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($fuelings)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                Nenhum lançamento de abastecimento encontrado.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($fuelings as $f): ?>
                            <tr>
                                <td class="ps-3">
                                    <span class="data-mono text-dark fw-semibold">
                                        <?= FormatHelper::formatDateTime($f['fueling_date']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="data-mono fw-bold bg-light px-2 py-1 rounded border border-subtle">
                                            <?= htmlspecialchars(FormatHelper::formatPlate($f['vehicle_plate']), ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <span class="fw-semibold text-slate-800">
                                            <?= htmlspecialchars($f['vehicle_model'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($f['secretariat_name'])): ?>
                                        <div class="text-muted small">
                                            <?= htmlspecialchars($f['secretariat_name'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">
                                        <?= htmlspecialchars($f['station_name'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $fuelMap = [
                                        'GASOLINE' => ['label' => 'Gasolina', 'class' => 'bg-danger-subtle text-danger border-danger-subtle'],
                                        'ETHANOL'  => ['label' => 'Etanol',   'class' => 'bg-success-subtle text-success border-success-subtle'],
                                        'DIESEL'   => ['label' => 'Diesel',   'class' => 'bg-warning-subtle text-dark border-warning-subtle'],
                                        'FLEX'     => ['label' => 'Flex',     'class' => 'bg-info-subtle text-dark border-info-subtle']
                                    ];
                                    $typeInfo = $fuelMap[$f['fuel_type']] ?? ['label' => $f['fuel_type'], 'class' => 'bg-secondary-subtle text-dark'];
                                    ?>
                                    <span class="badge <?= $typeInfo['class'] ?> px-2 py-1 border">
                                        <?= htmlspecialchars($typeInfo['label'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="data-mono fw-semibold">
                                        <?= FormatHelper::formatNumber($f['km'], 0) ?> KM
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="data-mono">
                                        <?= FormatHelper::formatNumber($f['liters'], 2) ?> L
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="data-mono fw-bold text-success">
                                        <?= FormatHelper::formatMoney($f['total_price']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <?php if ($f['avg_kml'] !== null): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle data-mono fs-6">
                                            <?= FormatHelper::formatNumber($f['avg_kml'], 2) ?> KM/L
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small italic" title="Primeiro abastecimento do veículo">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1">
                                        <a href="<?= BASE_URL ?>/vehicles/show/<?= $f['vehicle_id'] ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Ver Ficha do Veículo">
                                            Ficha
                                        </a>

                                        <?php if (AuthHelper::hasRole(['ADMIN'])): ?>
                                            <a href="<?= BASE_URL ?>/fuelings/edit/<?= $f['id'] ?>" 
                                               class="btn btn-sm btn-outline-secondary" title="Editar Abastecimento">
                                                Editar
                                            </a>
                                            <form method="POST" action="<?= BASE_URL ?>/fuelings/delete/<?= $f['id'] ?>" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Deseja realmente excluir este abastecimento?');">
                                                <input type="hidden" name="csrf_token" value="<?= AuthHelper::generateCsrfToken() ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                                    Excluir
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
