<?php
/**
 * SisFrota Municipal - View do Dashboard Gerencial
 * Fase 8: Painel de Indicadores, Gráficos Chart.js e Atividades Recentes
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>

<!-- Cabeçalho do Dashboard com Seletor de Mês -->
<div class="dashboard-header">
    <div class="dashboard-title-box">
        <h2>Visão Geral da Frota</h2>
        <p>Indicadores operacionais e financeiros consolidados de <strong><?= htmlspecialchars(FormatHelper::formatMonthName($selectedMonth), ENT_QUOTES, 'UTF-8') ?></strong>.</p>
    </div>

    <form method="GET" action="<?= $baseUrl ?>/dashboard" class="month-selector-form">
        <label for="monthSelect" class="form-label" style="margin-bottom: 0; font-size: 0.85rem; color: var(--text-muted);">Mês de Referência:</label>
        <select name="month" id="monthSelect" class="month-selector-select" onchange="this.form.submit()">
            <?php foreach ($availableMonths as $mValue => $mLabel): ?>
                <option value="<?= htmlspecialchars($mValue, ENT_QUOTES, 'UTF-8') ?>" <?= $mValue === $selectedMonth ? 'selected' : '' ?>>
                    <?= htmlspecialchars($mLabel, ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-secondary" style="padding: 0.45rem 0.85rem; font-size: 0.85rem;">
            Filtrar
        </button>
    </form>
</div>

<!-- 1. Grid de 4 Cards de Destaque (KPIs) -->
<div class="kpi-grid">
    <!-- Card 1: Gasto Total -->
    <div class="kpi-card" style="border-top: 4px solid var(--primary-color);">
        <div class="kpi-card-header">
            <span class="kpi-label">Gasto Total no Mês</span>
            <div class="kpi-icon-box" style="background-color: #dcfce7; color: var(--primary-color);">
                💰
            </div>
        </div>
        <div class="kpi-value font-mono">
            <?= htmlspecialchars(FormatHelper::formatCurrency($totalCost), ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="kpi-subtext">
            Combustível: <?= htmlspecialchars(FormatHelper::formatCurrency($totalFuelCost), ENT_QUOTES, 'UTF-8') ?><br>
            Oficina: <?= htmlspecialchars(FormatHelper::formatCurrency($totalMaintCost), ENT_QUOTES, 'UTF-8') ?>
        </div>
    </div>

    <!-- Card 2: Gastos com Combustível -->
    <div class="kpi-card" style="border-top: 4px solid #0284c7;">
        <div class="kpi-card-header">
            <span class="kpi-label">Gastos com Combustível</span>
            <div class="kpi-icon-box" style="background-color: #e0f2fe; color: #0284c7;">
                ⛽
            </div>
        </div>
        <div class="kpi-value font-mono">
            <?= htmlspecialchars(FormatHelper::formatCurrency($totalFuelCost), ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="kpi-subtext">
            <strong><?= htmlspecialchars(FormatHelper::formatNumber($totalFuelLiters, 1), ENT_QUOTES, 'UTF-8') ?> L</strong> abastecidos (<?= $totalFuelCount ?> registros)
        </div>
    </div>

    <!-- Card 3: KM Rodado no Mês -->
    <div class="kpi-card" style="border-top: 4px solid #8b5cf6;">
        <div class="kpi-card-header">
            <span class="kpi-label">KM Rodado no Mês</span>
            <div class="kpi-icon-box" style="background-color: #f3e8ff; color: #8b5cf6;">
                🛣️
            </div>
        </div>
        <div class="kpi-value font-mono">
            <?= number_format($totalKmMonth, 0, ',', '.') ?> <span style="font-size: 1rem; font-weight: 600;">KM</span>
        </div>
        <div class="kpi-subtext">
            Distância estimada percorrida pela frota no mês
        </div>
    </div>

    <!-- Card 4: Veículos Indisponíveis -->
    <div class="kpi-card" style="border-top: 4px solid <?= $unavailableVehicles > 0 ? '#ef4444' : 'var(--primary-color)' ?>;">
        <div class="kpi-card-header">
            <span class="kpi-label">Veículos Indisponíveis</span>
            <div class="kpi-icon-box" style="background-color: <?= $unavailableVehicles > 0 ? '#fee2e2' : '#dcfce7' ?>; color: <?= $unavailableVehicles > 0 ? '#ef4444' : 'var(--primary-color)' ?>;">
                <?= $unavailableVehicles > 0 ? '⚠️' : '✅' ?>
            </div>
        </div>
        <div class="kpi-value font-mono" style="color: <?= $unavailableVehicles > 0 ? '#dc2626' : 'var(--primary-color)' ?>;">
            <?= $unavailableVehicles ?> <span style="font-size: 1rem; font-weight: 500; color: var(--text-muted);">/ <?= $totalVehicles ?></span>
        </div>
        <div class="kpi-subtext">
            <?php if ($unavailableVehicles > 0): ?>
                <?= $maintVehicles ?> em oficina &bull; <?= $unavailVehicles ?> indisponíveis
            <?php else: ?>
                100% da frota ativa em operação normal
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 2. Gráficos Gerenciais -->
<div class="charts-grid">
    <!-- Gráfico 1: Evolução de Custos Mensais (Últimos 6 meses) -->
    <div class="chart-card">
        <div class="chart-card-header">
            <div class="chart-card-title">Evolução de Custos Mensais (R$)</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">
                Últimos 6 meses até <?= htmlspecialchars(FormatHelper::getMonthShortName(substr($selectedMonth, 5, 2)) . '/' . substr($selectedMonth, 2, 2), ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
        <div class="chart-container-box">
            <canvas id="costEvolutionChart"></canvas>
        </div>
    </div>

    <!-- Gráfico 2: Proporção de Manutenções por Status -->
    <div class="chart-card">
        <div class="chart-card-header">
            <div class="chart-card-title">Situação das Manutenções</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">
                Total geral: <?= array_sum($statusCounts) ?> OS
            </div>
        </div>
        <div class="chart-container-box" style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <?php if (array_sum($statusCounts) > 0): ?>
                <div style="position: relative; width: 100%; height: 210px;">
                    <canvas id="maintenanceStatusChart"></canvas>
                </div>
                <div style="margin-top: 1rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; width: 100%; font-size: 0.78rem;">
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #16a34a;"></span>
                        <span>Concluídas: <strong><?= $statusCounts['COMPLETED'] ?></strong></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #0284c7;"></span>
                        <span>Em Andamento: <strong><?= $statusCounts['IN_PROGRESS'] ?></strong></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #facc15;"></span>
                        <span>Pendentes: <strong><?= $statusCounts['PENDING'] ?></strong></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #94a3b8;"></span>
                        <span>Agendadas: <strong><?= $statusCounts['SCHEDULED'] ?></strong></span>
                    </div>
                </div>
            <?php else: ?>
                <div style="text-align: center; color: var(--text-muted); padding: 2rem;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">🔧</div>
                    <p>Nenhuma ordem de manutenção registrada.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 3. Tabelas de Atividades Recentes -->
<div class="tables-grid">
    <!-- Tabela: Últimos Abastecimentos -->
    <div class="table-card">
        <div class="table-card-header">
            <span class="table-card-title">Últimos Abastecimentos</span>
            <?php if (in_array($user['role'] ?? '', ['ADMIN', 'OPERATOR'], true)): ?>
                <a href="<?= $baseUrl ?>/fuelings" class="btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.78rem;">
                    Ver Todos
                </a>
            <?php endif; ?>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Veículo</th>
                        <th style="text-align: right;">Litros</th>
                        <th style="text-align: right;">Valor</th>
                        <th>KM/L</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentFuelings)): ?>
                        <?php foreach ($recentFuelings as $f): ?>
                            <tr>
                                <td style="white-space: nowrap;"><?= htmlspecialchars(FormatHelper::formatDateTime($f['fueling_date']), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($f['vehicle_model'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                                    <span class="font-mono" style="font-size: 0.75rem; color: var(--text-muted);">
                                        <?= htmlspecialchars(FormatHelper::formatPlate($f['vehicle_plate']), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td style="text-align: right;" class="font-mono"><?= htmlspecialchars(FormatHelper::formatNumber($f['liters'], 2), ENT_QUOTES, 'UTF-8') ?> L</td>
                                <td style="text-align: right; font-weight: 600;" class="font-mono"><?= htmlspecialchars(FormatHelper::formatCurrency($f['total_price']), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="font-mono">
                                    <?php if (!empty($f['avg_kml'])): ?>
                                        <span class="status-pill status-pill-success" style="font-size: 0.7rem;">
                                            <?= htmlspecialchars(FormatHelper::formatNumber($f['avg_kml'], 2), ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 1.5rem;">
                                Nenhum abastecimento registrado até o momento.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tabela: Últimas Manutenções -->
    <div class="table-card">
        <div class="table-card-header">
            <span class="table-card-title">Últimas Manutenções</span>
            <a href="<?= $baseUrl ?>/maintenances" class="btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.78rem;">
                Ver Todas
            </a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Veículo</th>
                        <th>Serviço</th>
                        <th style="text-align: right;">Custo</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentMaintenances)): ?>
                        <?php 
                        $statusLabels = [
                            'SCHEDULED'   => ['Agendada', 'status-pill-secondary'],
                            'IN_PROGRESS' => ['Em Andamento', 'status-pill-info'],
                            'PENDING'     => ['Pendente', 'status-pill-warning'],
                            'COMPLETED'   => ['Concluída', 'status-pill-success'],
                        ];
                        ?>
                        <?php foreach ($recentMaintenances as $m): ?>
                            <?php 
                            $stInfo = $statusLabels[$m['status']] ?? [$m['status'], 'status-pill-secondary'];
                            ?>
                            <tr>
                                <td style="white-space: nowrap;"><?= htmlspecialchars(FormatHelper::formatDate($m['service_date']), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($m['vehicle_model'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                                    <span class="font-mono" style="font-size: 0.75rem; color: var(--text-muted);">
                                        <?= htmlspecialchars(FormatHelper::formatPlate($m['vehicle_plate']), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($m['category_name'] ?? 'Manutenção', ENT_QUOTES, 'UTF-8') ?><br>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($m['workshop_name'], ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td style="text-align: right; font-weight: 600;" class="font-mono"><?= htmlspecialchars(FormatHelper::formatCurrency($m['cost']), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <span class="status-pill <?= $stInfo[1] ?>">
                                        <?= htmlspecialchars($stInfo[0], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 1.5rem;">
                                Nenhuma manutenção registrada até o momento.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 4. Inicialização do Chart.js -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js não carregado.');
        return;
    }

    // ─── Gráfico 1: Evolução de Custos Mensais (Bar Chart) ────────────────
    const costCtx = document.getElementById('costEvolutionChart');
    if (costCtx) {
        new Chart(costCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartCostLabels, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [
                    {
                        label: 'Combustível',
                        data: <?= json_encode($chartCostFuel) ?>,
                        backgroundColor: '#16a34a',
                        borderRadius: 4,
                        barPercentage: 0.6
                    },
                    {
                        label: 'Manutenção',
                        data: <?= json_encode($chartCostMaint) ?>,
                        backgroundColor: '#facc15',
                        borderRadius: 4,
                        barPercentage: 0.6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            font: { family: 'Inter', size: 12 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e2e8f0', drawBorder: false },
                        ticks: {
                            font: { family: 'Inter', size: 11 },
                            callback: function (val) {
                                if (val >= 1000) return 'R$ ' + (val / 1000) + 'k';
                                return 'R$ ' + val;
                            }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter', size: 12 } }
                    }
                }
            }
        });
    }

    // ─── Gráfico 2: Situação das Manutenções (Doughnut Chart) ──────────────
    const maintCtx = document.getElementById('maintenanceStatusChart');
    if (maintCtx) {
        new Chart(maintCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Concluídas', 'Em Andamento', 'Pendentes', 'Agendadas'],
                datasets: [{
                    data: [
                        <?= (int) $statusCounts['COMPLETED'] ?>,
                        <?= (int) $statusCounts['IN_PROGRESS'] ?>,
                        <?= (int) $statusCounts['PENDING'] ?>,
                        <?= (int) $statusCounts['SCHEDULED'] ?>
                    ],
                    backgroundColor: [
                        '#16a34a',
                        '#0284c7',
                        '#facc15',
                        '#94a3b8'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return ' ' + context.label + ': ' + context.parsed + ' OS';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>

