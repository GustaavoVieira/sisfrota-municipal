<?php
/**
 * SisFrota Municipal - View: Ficha Individual do Veículo
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

$formattedPlate = FormatHelper::formatPlate($vehicle['plate']);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-3">
            <h1 class="h3 mb-0 text-slate-800 fw-bold data-mono bg-light px-3 py-1 rounded border border-subtle">
                <?= htmlspecialchars($formattedPlate, ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <?php if (!empty($vehicle['is_deleted'])): ?>
                <span class="badge bg-secondary fs-6">Inativado (Soft Delete)</span>
            <?php elseif ($vehicle['status'] === 'ACTIVE'): ?>
                <span class="badge bg-success fs-6">Ativo</span>
            <?php elseif ($vehicle['status'] === 'MAINTENANCE'): ?>
                <span class="badge bg-warning text-dark fs-6">Em Manutenção</span>
            <?php else: ?>
                <span class="badge bg-danger fs-6">Indisponível</span>
            <?php endif; ?>
        </div>
        <p class="text-muted small mb-0 mt-1">
            <?= htmlspecialchars($vehicle['model'], ENT_QUOTES, 'UTF-8') ?> 
            <?= !empty($vehicle['brand']) ? '• ' . htmlspecialchars($vehicle['brand'], ENT_QUOTES, 'UTF-8') : '' ?>
            <?= !empty($vehicle['year']) ? '(' . $vehicle['year'] . ')' : '' ?>
        </p>
    </div>

    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/vehicles" class="btn btn-outline-secondary">
            ← Voltar para a Frota
        </a>
        <?php if (AuthHelper::hasRole(['ADMIN']) && empty($vehicle['is_deleted'])): ?>
            <a href="<?= BASE_URL ?>/vehicles/edit/<?= $vehicle['id'] ?>" class="btn btn-primary">
                Editar Veículo
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Cards de Destaque / Métricas do Veículo -->
<div class="row g-3 mb-4">
    <!-- KM Atual -->
    <div class="col-md-3">
        <div class="card border-subtle shadow-sm h-100">
            <div class="card-body">
                <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Hodômetro / KM Atual</span>
                <span class="fs-4 fw-bold text-slate-800 data-mono">
                    <?= FormatHelper::formatNumber($vehicle['current_km'], 0) ?> KM
                </span>
            </div>
        </div>
    </div>

    <!-- Consumo Médio Acumulado -->
    <div class="col-md-3">
        <div class="card border-subtle shadow-sm h-100">
            <div class="card-body">
                <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Consumo Médio Acumulado</span>
                <span class="fs-4 fw-bold text-success data-mono">
                    <?= $overallAvgKml !== null ? FormatHelper::formatNumber($overallAvgKml, 2) . ' KM/L' : 'Sem dados suficientes' ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Secretaria -->
    <div class="col-md-3">
        <div class="card border-subtle shadow-sm h-100">
            <div class="card-body">
                <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Secretaria / Setor</span>
                <span class="fs-6 fw-bold text-slate-800 d-block">
                    <?= htmlspecialchars($vehicle['secretariat_name'] ?? 'Não associada', ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Combustível Padrão -->
    <div class="col-md-3">
        <div class="card border-subtle shadow-sm h-100">
            <div class="card-body">
                <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Combustível Padrão</span>
                <span class="fs-6 fw-bold text-slate-800 d-block">
                    <?php
                    $fuelMap = ['GASOLINE' => 'Gasolina', 'ETHANOL' => 'Etanol', 'DIESEL' => 'Diesel', 'FLEX' => 'Flex'];
                    echo htmlspecialchars($fuelMap[$vehicle['fuel_type']] ?? $vehicle['fuel_type'], ENT_QUOTES, 'UTF-8');
                    ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Abas de Histórico -->
<div class="card border-subtle shadow-sm">
    <div class="card-header bg-light border-bottom-0 pb-0">
        <ul class="nav nav-tabs card-header-tabs" id="vehicleTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold" id="fuelings-tab" data-bs-toggle="tab" data-bs-target="#fuelings-tab-pane" type="button" role="tab" aria-controls="fuelings-tab-pane" aria-selected="true">
                    Histórico de Abastecimentos (<?= count($fuelings) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold" id="maintenances-tab" data-bs-toggle="tab" data-bs-target="#maintenances-tab-pane" type="button" role="tab" aria-controls="maintenances-tab-pane" aria-selected="false">
                    Histórico de Manutenções (<?= count($maintenances) ?>)
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body p-0">
        <div class="tab-content" id="vehicleTabsContent">
            
            <!-- Aba 1: Abastecimentos -->
            <div class="tab-pane fade show active" id="fuelings-tab-pane" role="tabpanel" aria-labelledby="fuelings-tab" tabindex="0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-3">Data / Hora</th>
                                <th scope="col" class="text-end">KM no Momento</th>
                                <th scope="col" class="text-end">Litros</th>
                                <th scope="col" class="text-end">Valor Total (R$)</th>
                                <th scope="col" class="text-end">Consumo (KM/L)</th>
                                <th scope="col">Posto Fornecedor</th>
                                <th scope="col">Registrado Por</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($fuelings)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        Nenhum abastecimento registrado para este veículo até o momento.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($fuelings as $f): ?>
                                    <tr>
                                        <td class="ps-3 data-mono">
                                            <?= FormatHelper::formatDateTime($f['fueling_date']) ?>
                                        </td>
                                        <td class="text-end data-mono fw-semibold">
                                            <?= FormatHelper::formatNumber($f['km'], 0) ?> KM
                                        </td>
                                        <td class="text-end data-mono">
                                            <?= FormatHelper::formatNumber($f['liters'], 2) ?> L
                                        </td>
                                        <td class="text-end data-mono fw-semibold text-slate-800">
                                            <?= FormatHelper::formatCurrency($f['total_price']) ?>
                                        </td>
                                        <td class="text-end data-mono">
                                            <?php if ($f['avg_kml'] !== null): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2">
                                                    <?= FormatHelper::formatNumber($f['avg_kml'], 2) ?> KM/L
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small">1º Lançamento</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($f['station_name'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td class="small text-muted">
                                            <?= htmlspecialchars($f['creator_name'] ?? 'Sistema', ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Aba 2: Manutenções -->
            <div class="tab-pane fade" id="maintenances-tab-pane" role="tabpanel" aria-labelledby="maintenances-tab" tabindex="0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-3">Data Serviço</th>
                                <th scope="col">Categoria</th>
                                <th scope="col">Oficina Contratada</th>
                                <th scope="col">Nº da OS</th>
                                <th scope="col" class="text-end">Custo Total (R$)</th>
                                <th scope="col" class="text-center">Status OS</th>
                                <th scope="col">Registrado Por</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($maintenances)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        Nenhuma manutenção registrada para este veículo até o momento.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($maintenances as $m): ?>
                                    <tr>
                                        <td class="ps-3 data-mono">
                                            <?= FormatHelper::formatDate($m['service_date']) ?>
                                        </td>
                                        <td class="fw-semibold">
                                            <?= htmlspecialchars($m['category_name'] ?? 'Mecânica Geral', ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($m['workshop_name'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td class="data-mono">
                                            <?= htmlspecialchars($m['os_number'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td class="text-end data-mono fw-semibold text-slate-800">
                                            <?= FormatHelper::formatCurrency($m['cost']) ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $statusBadge = [
                                                'SCHEDULED'   => '<span class="badge bg-info text-dark">Agendada</span>',
                                                'IN_PROGRESS' => '<span class="badge bg-warning text-dark">Em Andamento</span>',
                                                'PENDING'     => '<span class="badge bg-secondary">Pendente</span>',
                                                'COMPLETED'   => '<span class="badge bg-success">Concluída</span>'
                                            ];
                                            echo $statusBadge[$m['status']] ?? '<span class="badge bg-light text-dark">' . $m['status'] . '</span>';
                                            ?>
                                        </td>
                                        <td class="small text-muted">
                                            <?= htmlspecialchars($m['creator_name'] ?? 'Sistema', ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
