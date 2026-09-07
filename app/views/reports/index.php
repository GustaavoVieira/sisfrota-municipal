<?php
/**
 * SisFrota Municipal - View: Módulo de Relatórios Oficiais (Fase 9)
 * Filtros dinâmicos, cards de resumo e visualização tabular de dados com exportação CSV e PDF.
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

$baseUrl = defined('BASE_URL') ? BASE_URL : '';

// Labels de tipos de relatório
$reportTypes = [
    'fuelings'     => 'Abastecimentos de Combustível',
    'maintenances' => 'Manutenções e Ordens de Serviço',
    'consolidated' => 'Consolidado de Gastos e Rodagem por Veículo'
];

$curType = $filters['report_type'];

// Query string para exportações mantendo os filtros atuais
$queryParams = http_build_query($filters);
$csvUrl = $baseUrl . '/reports/csv?' . $queryParams;
$pdfUrl = $baseUrl . '/reports/pdf?' . $queryParams;
?>

<div class="content-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0 0 0.25rem 0;">Relatórios Oficiais</h1>
        <p style="color: #475569; margin: 0; font-size: 0.875rem;">
            Emissão e auditoria de registros da frota com exportação para planilha Excel (CSV) e documento formatado para impressão (PDF).
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="<?= $csvUrl ?>" class="btn-secondary" style="display: inline-flex; align-items: center; gap: 0.5rem;" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Exportar CSV
        </a>
        <a href="<?= $pdfUrl ?>" class="btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem;" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Gerar Relatório PDF
        </a>
    </div>
</div>

<!-- Card de Filtros -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form method="GET" action="<?= $baseUrl ?>/reports" id="reportFilterForm">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: flex-end;">
            
            <!-- Tipo de Relatório -->
            <div>
                <label for="report_type" style="display: block; font-size: 0.8125rem; font-weight: 600; color: #0f172a; margin-bottom: 0.35rem;">
                    Tipo de Relatório <span style="color:#ef4444;">*</span>
                </label>
                <select name="report_type" id="report_type" class="form-control" style="width: 100%;" onchange="document.getElementById('reportFilterForm').submit();">
                    <?php foreach ($reportTypes as $key => $label): ?>
                        <option value="<?= $key ?>" <?= ($curType === $key) ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Data Inicial -->
            <div>
                <label for="start_date" style="display: block; font-size: 0.8125rem; font-weight: 600; color: #0f172a; margin-bottom: 0.35rem;">
                    Data Inicial
                </label>
                <input type="date" name="start_date" id="start_date" class="form-control" style="width: 100%;" value="<?= htmlspecialchars($filters['start_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <!-- Data Final -->
            <div>
                <label for="end_date" style="display: block; font-size: 0.8125rem; font-weight: 600; color: #0f172a; margin-bottom: 0.35rem;">
                    Data Final
                </label>
                <input type="date" name="end_date" id="end_date" class="form-control" style="width: 100%;" value="<?= htmlspecialchars($filters['end_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <!-- Secretaria -->
            <div>
                <label for="secretariat_id" style="display: block; font-size: 0.8125rem; font-weight: 600; color: #0f172a; margin-bottom: 0.35rem;">
                    Secretaria / Setor
                </label>
                <select name="secretariat_id" id="secretariat_id" class="form-control" style="width: 100%;">
                    <option value="">Todas as Secretarias</option>
                    <?php foreach ($secretariats as $sec): ?>
                        <option value="<?= $sec['id'] ?>" <?= (!empty($filters['secretariat_id']) && (int)$filters['secretariat_id'] === (int)$sec['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sec['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filtros Específicos para Abastecimento / Manutenção -->
            <?php if ($curType === 'fuelings' || $curType === 'maintenances'): ?>
            <div>
                <label for="vehicle_id" style="display: block; font-size: 0.8125rem; font-weight: 600; color: #0f172a; margin-bottom: 0.35rem;">
                    Veículo Específico
                </label>
                <select name="vehicle_id" id="vehicle_id" class="form-control" style="width: 100%;">
                    <option value="">Todos os Veículos</option>
                    <?php foreach ($vehicles as $v): ?>
                        <option value="<?= $v['id'] ?>" <?= (!empty($filters['vehicle_id']) && (int)$filters['vehicle_id'] === (int)$v['id']) ? 'selected' : '' ?>>
                            <?= FormatHelper::formatPlate($v['plate']) ?> - <?= htmlspecialchars($v['model'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if ($curType === 'maintenances'): ?>
            <div>
                <label for="category_id" style="display: block; font-size: 0.8125rem; font-weight: 600; color: #0f172a; margin-bottom: 0.35rem;">
                    Categoria
                </label>
                <select name="category_id" id="category_id" class="form-control" style="width: 100%;">
                    <option value="">Todas as Categorias</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (!empty($filters['category_id']) && (int)$filters['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="status" style="display: block; font-size: 0.8125rem; font-weight: 600; color: #0f172a; margin-bottom: 0.35rem;">
                    Status
                </label>
                <select name="status" id="status" class="form-control" style="width: 100%;">
                    <option value="">Todos os Status</option>
                    <option value="SCHEDULED" <?= ($filters['status'] === 'SCHEDULED') ? 'selected' : '' ?>>Agendada</option>
                    <option value="IN_PROGRESS" <?= ($filters['status'] === 'IN_PROGRESS') ? 'selected' : '' ?>>Em Andamento</option>
                    <option value="PENDING" <?= ($filters['status'] === 'PENDING') ? 'selected' : '' ?>>Pendente</option>
                    <option value="COMPLETED" <?= ($filters['status'] === 'COMPLETED') ? 'selected' : '' ?>>Concluída</option>
                </select>
            </div>
            <?php endif; ?>

            <!-- Botões de Filtro -->
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn-primary" style="flex: 1;">
                    Filtrar
                </button>
                <a href="<?= $baseUrl ?>/reports?report_type=<?= $curType ?>" class="btn-secondary" title="Limpar Filtros">
                    Limpar
                </a>
            </div>

        </div>
    </form>
</div>

<!-- Resumo dos Indicadores do Filtro -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">

    <div class="kpi-card" style="background:#fff; border:1px solid #e2e8f0; border-radius:0.5rem; padding:1.25rem;">
        <div style="font-size:0.8125rem; color:#475569; font-weight:600; margin-bottom:0.25rem;">TOTAL DE REGISTROS</div>
        <div class="data-mono" style="font-size:1.5rem; font-weight:700; color:#0f172a;">
            <?= number_format($summary['count'] ?? 0, 0, '', '.') ?>
        </div>
        <div style="font-size:0.75rem; color:#64748b; margin-top:0.25rem;">No período selecionado</div>
    </div>

    <?php if ($curType === 'fuelings'): ?>
        <div class="kpi-card" style="background:#fff; border:1px solid #e2e8f0; border-radius:0.5rem; padding:1.25rem;">
            <div style="font-size:0.8125rem; color:#475569; font-weight:600; margin-bottom:0.25rem;">LITROS ABASTECIDOS</div>
            <div class="data-mono" style="font-size:1.5rem; font-weight:700; color:#16a34a;">
                <?= number_format($summary['total_liters'] ?? 0, 2, ',', '.') ?> L
            </div>
            <div style="font-size:0.75rem; color:#64748b; margin-top:0.25rem;">Volume total adquirido</div>
        </div>

        <div class="kpi-card" style="background:#fff; border:1px solid #e2e8f0; border-radius:0.5rem; padding:1.25rem;">
            <div style="font-size:0.8125rem; color:#475569; font-weight:600; margin-bottom:0.25rem;">TOTAL GASTO EM COMBUSTÍVEL</div>
            <div class="data-mono" style="font-size:1.5rem; font-weight:700; color:#0f172a;">
                <?= FormatHelper::formatCurrency($summary['total_cost'] ?? 0) ?>
            </div>
            <div style="font-size:0.75rem; color:#64748b; margin-top:0.25rem;">Preço médio: <?= FormatHelper::formatCurrency($summary['avg_price_liter'] ?? 0) ?>/L</div>
        </div>

    <?php elseif ($curType === 'maintenances'): ?>
        <div class="kpi-card" style="background:#fff; border:1px solid #e2e8f0; border-radius:0.5rem; padding:1.25rem;">
            <div style="font-size:0.8125rem; color:#475569; font-weight:600; margin-bottom:0.25rem;">SERVIÇOS CONCLUÍDOS</div>
            <div class="data-mono" style="font-size:1.5rem; font-weight:700; color:#16a34a;">
                <?= number_format($summary['completed'] ?? 0, 0, '', '.') ?>
            </div>
            <div style="font-size:0.75rem; color:#64748b; margin-top:0.25rem;"><?= number_format($summary['pending'] ?? 0, 0, '', '.') ?> pendentes/em andamento</div>
        </div>

        <div class="kpi-card" style="background:#fff; border:1px solid #e2e8f0; border-radius:0.5rem; padding:1.25rem;">
            <div style="font-size:0.8125rem; color:#475569; font-weight:600; margin-bottom:0.25rem;">TOTAL GASTO EM OFICINAS</div>
            <div class="data-mono" style="font-size:1.5rem; font-weight:700; color:#0f172a;">
                <?= FormatHelper::formatCurrency($summary['total_cost'] ?? 0) ?>
            </div>
            <div style="font-size:0.75rem; color:#64748b; margin-top:0.25rem;">Despesas de manutenção</div>
        </div>

    <?php elseif ($curType === 'consolidated'): ?>
        <div class="kpi-card" style="background:#fff; border:1px solid #e2e8f0; border-radius:0.5rem; padding:1.25rem;">
            <div style="font-size:0.8125rem; color:#475569; font-weight:600; margin-bottom:0.25rem;">KM TOTAL RODADO</div>
            <div class="data-mono" style="font-size:1.5rem; font-weight:700; color:#0284c7;">
                <?= number_format($summary['total_km'] ?? 0, 0, '', '.') ?> KM
            </div>
            <div style="font-size:0.75rem; color:#64748b; margin-top:0.25rem;"><?= number_format($summary['total_liters'] ?? 0, 2, ',', '.') ?> L consumidos</div>
        </div>

        <div class="kpi-card" style="background:#fff; border:1px solid #e2e8f0; border-radius:0.5rem; padding:1.25rem;">
            <div style="font-size:0.8125rem; color:#475569; font-weight:600; margin-bottom:0.25rem;">CUSTO TOTAL GERAL</div>
            <div class="data-mono" style="font-size:1.5rem; font-weight:700; color:#0f172a;">
                <?= FormatHelper::formatCurrency($summary['total_global'] ?? 0) ?>
            </div>
            <div style="font-size:0.75rem; color:#64748b; margin-top:0.25rem;">Combustível + Manutenções</div>
        </div>
    <?php endif; ?>

</div>

<!-- Tabela de Dados -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-size: 1rem; font-weight: 600; color: #0f172a;">
            <?= htmlspecialchars($reportTypes[$curType], ENT_QUOTES, 'UTF-8') ?>
        </h3>
        <span style="font-size: 0.8125rem; color: #64748b;">
            Exibindo <strong><?= count($reportData) ?></strong> registros
        </span>
    </div>

    <div class="table-responsive">
        <table class="data-table" style="width: 100%; border-collapse: collapse;">

            <?php if ($curType === 'fuelings'): ?>
                <thead>
                    <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; color: #475569; text-transform: uppercase;">Data/Hora</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; color: #475569; text-transform: uppercase;">Veículo / Placa</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; color: #475569; text-transform: uppercase;">Secretaria</th>
                        <th style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; color: #475569; text-transform: uppercase;">KM</th>
                        <th style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; color: #475569; text-transform: uppercase;">Litros</th>
                        <th style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; color: #475569; text-transform: uppercase;">Valor Total</th>
                        <th style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; color: #475569; text-transform: uppercase;">R$/L</th>
                        <th style="padding: 0.75rem 1rem; text-align: center; font-size: 0.75rem; color: #475569; text-transform: uppercase;">Combustível</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; color: #475569; text-transform: uppercase;">Posto</th>
                        <th style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; color: #475569; text-transform: uppercase;">Consumo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reportData)): ?>
                        <tr>
                            <td colspan="10" style="padding: 2.5rem; text-align: center; color: #64748b;">
                                Nenhum registro de abastecimento encontrado com os filtros informados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reportData as $row): ?>
                            <?php $unitPrice = ($row['liters'] > 0) ? ($row['total_price'] / $row['liters']) : 0; ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.75rem 1rem;" class="data-mono"><?= FormatHelper::formatDateTime($row['fueling_date']) ?></td>
                                <td style="padding: 0.75rem 1rem;">
                                    <div style="font-weight: 600; color: #0f172a;" class="data-mono"><?= FormatHelper::formatPlate($row['vehicle_plate']) ?></div>
                                    <div style="font-size: 0.75rem; color: #64748b;"><?= htmlspecialchars($row['vehicle_model'], ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td style="padding: 0.75rem 1rem; color: #334155; font-size: 0.8125rem;">
                                    <?= htmlspecialchars($row['secretariat_name'] ?? 'Não vinculada', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td style="padding: 0.75rem 1rem; text-align: right;" class="data-mono"><?= number_format((int)$row['km'], 0, '', '.') ?> KM</td>
                                <td style="padding: 0.75rem 1rem; text-align: right;" class="data-mono"><?= number_format((float)$row['liters'], 2, ',', '.') ?> L</td>
                                <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #0f172a;" class="data-mono"><?= FormatHelper::formatCurrency($row['total_price']) ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: right; color: #64748b;" class="data-mono"><?= FormatHelper::formatCurrency($unitPrice) ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: center;">
                                    <span class="status-chip" style="background:#e0f2fe; color:#0369a1; font-size:0.75rem; padding:0.2rem 0.5rem; border-radius:9999px;">
                                        <?= htmlspecialchars($row['fuel_type'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td style="padding: 0.75rem 1rem; color: #475569; font-size: 0.8125rem;"><?= htmlspecialchars($row['station_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: right;" class="data-mono">
                                    <?php if ($row['avg_kml'] !== null && $row['avg_kml'] !== ''): ?>
                                        <span style="color: #16a34a; font-weight: 600;"><?= number_format((float)$row['avg_kml'], 2, ',', '.') ?> KM/L</span>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

            <?php elseif ($curType === 'maintenances'): ?>
                <thead>
                    <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; color: #475569; text-transform: uppercase;">Data</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; color: #475569; text-transform: uppercase;">Veículo / Placa</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; color: #475569; text-transform: uppercase;">Secretaria</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; color: #475569; text-transform: uppercase;">Categoria</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; color: #475569; text-transform: uppercase;">Oficina Contratada</th>
                        <th style="padding: 0.75rem 1rem; text-align: center; font-size: 0.75rem; color: #475569; text-transform: uppercase;">Nº OS</th>
                        <th style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; color: #475569; text-transform: uppercase;">Valor Total</th>
                        <th style="padding: 0.75rem 1rem; text-align: center; font-size: 0.75rem; color: #475569; text-transform: uppercase;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reportData)): ?>
                        <tr>
                            <td colspan="8" style="padding: 2.5rem; text-align: center; color: #64748b;">
                                Nenhum registro de manutenção encontrado com os filtros informados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reportData as $row): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.75rem 1rem;" class="data-mono"><?= FormatHelper::formatDate($row['service_date']) ?></td>
                                <td style="padding: 0.75rem 1rem;">
                                    <div style="font-weight: 600; color: #0f172a;" class="data-mono"><?= FormatHelper::formatPlate($row['vehicle_plate']) ?></div>
                                    <div style="font-size: 0.75rem; color: #64748b;"><?= htmlspecialchars($row['vehicle_model'], ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td style="padding: 0.75rem 1rem; color: #334155; font-size: 0.8125rem;">
                                    <?= htmlspecialchars($row['secretariat_name'] ?? 'Não vinculada', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td style="padding: 0.75rem 1rem; font-weight: 500; color: #0f172a; font-size: 0.8125rem;">
                                    <?= htmlspecialchars($row['category_name'] ?? 'Geral', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td style="padding: 0.75rem 1rem; color: #475569; font-size: 0.8125rem;">
                                    <?= htmlspecialchars($row['workshop_name'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td style="padding: 0.75rem 1rem; text-align: center;" class="data-mono">
                                    <?= htmlspecialchars($row['os_number'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #0f172a;" class="data-mono">
                                    <?= FormatHelper::formatCurrency($row['cost']) ?>
                                </td>
                                <td style="padding: 0.75rem 1rem; text-align: center;">
                                    <?php
                                    $statusStyles = [
                                        'SCHEDULED'   => 'background:#e0e7ff; color:#3730a3;',
                                        'IN_PROGRESS' => 'background:#fef3c7; color:#92400e;',
                                        'PENDING'     => 'background:#fee2e2; color:#991b1b;',
                                        'COMPLETED'   => 'background:#dcfce7; color:#166534;',
                                    ];
                                    $statusNames = [
                                        'SCHEDULED'   => 'Agendada',
                                        'IN_PROGRESS' => 'Em Andamento',
                                        'PENDING'     => 'Pendente',
                                        'COMPLETED'   => 'Concluída',
                                    ];
                                    $chipStyle = $statusStyles[$row['status']] ?? 'background:#f1f5f9; color:#475569;';
                                    $chipName  = $statusNames[$row['status']]  ?? $row['status'];
                                    ?>
                                    <span class="status-chip" style="<?= $chipStyle ?> font-size:0.75rem; padding:0.2rem 0.6rem; border-radius:9999px;">
                                        <?= $chipName ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

            <?php elseif ($curType === 'consolidated'): ?>
                <thead>
                    <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; color: #475569; text-transform: uppercase;">Veículo / Placa</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; color: #475569; text-transform: uppercase;">Secretaria</th>
                        <th style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; color: #475569; text-transform: uppercase;">KM Rodada no Período</th>
                        <th style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; color: #475569; text-transform: uppercase;">Litros Consumidos</th>
                        <th style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; color: #475569; text-transform: uppercase;">Total Combustível</th>
                        <th style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; color: #475569; text-transform: uppercase;">Total Manutenção</th>
                        <th style="padding: 0.75rem 1rem; text-align: right; font-size: 0.75rem; color: #475569; text-transform: uppercase;">Custo Total Geral</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reportData)): ?>
                        <tr>
                            <td colspan="7" style="padding: 2.5rem; text-align: center; color: #64748b;">
                                Nenhum veículo encontrado com os filtros informados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reportData as $row): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.75rem 1rem;">
                                    <div style="font-weight: 600; color: #0f172a;" class="data-mono"><?= FormatHelper::formatPlate($row['plate']) ?></div>
                                    <div style="font-size: 0.75rem; color: #64748b;"><?= htmlspecialchars($row['model'], ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td style="padding: 0.75rem 1rem; color: #334155; font-size: 0.8125rem;">
                                    <?= htmlspecialchars($row['secretariat_name'] ?? 'Não vinculada', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td style="padding: 0.75rem 1rem; text-align: right;" class="data-mono">
                                    <?= number_format((int)$row['km_traveled'], 0, '', '.') ?> KM
                                </td>
                                <td style="padding: 0.75rem 1rem; text-align: right;" class="data-mono">
                                    <?= number_format((float)$row['total_liters'], 2, ',', '.') ?> L
                                </td>
                                <td style="padding: 0.75rem 1rem; text-align: right; color: #0f172a;" class="data-mono">
                                    <?= FormatHelper::formatCurrency($row['fuel_cost']) ?>
                                </td>
                                <td style="padding: 0.75rem 1rem; text-align: right; color: #0f172a;" class="data-mono">
                                    <?= FormatHelper::formatCurrency($row['maintenance_cost']) ?>
                                </td>
                                <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 700; color: #006b2c;" class="data-mono">
                                    <?= FormatHelper::formatCurrency($row['total_cost']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            <?php endif; ?>

        </table>
    </div>
</div>
