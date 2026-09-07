<?php
/**
 * SisFrota Municipal - Controller de Relatórios e Exportações (Fase 9)
 * Controla a geração de relatórios de Abastecimentos, Manutenções e Consolidado
 * com exportação em planilha CSV (UTF-8 BOM) e documento oficial PDF (FPDF).
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

require_once APP_PATH . '/helpers/AuthHelper.php';
require_once APP_PATH . '/helpers/FormatHelper.php';
require_once APP_PATH . '/helpers/ReportPdfHelper.php';
require_once APP_PATH . '/models/Fueling.php';
require_once APP_PATH . '/models/Maintenance.php';
require_once APP_PATH . '/models/Vehicle.php';
require_once APP_PATH . '/models/Secretariat.php';
require_once APP_PATH . '/models/MaintenanceCategory.php';
require_once APP_PATH . '/models/Database.php';

class ReportController
{
    private Fueling $fuelingModel;
    private Maintenance $maintenanceModel;
    private Vehicle $vehicleModel;
    private Secretariat $secretariatModel;
    private MaintenanceCategory $categoryModel;

    public function __construct()
    {
        // Controle de Acesso: Apenas ADMIN e MANAGER podem acessar relatórios
        AuthHelper::requireRole(['ADMIN', 'MANAGER']);

        $this->fuelingModel     = new Fueling();
        $this->maintenanceModel = new Maintenance();
        $this->vehicleModel     = new Vehicle();
        $this->secretariatModel = new Secretariat();
        $this->categoryModel    = new MaintenanceCategory();
    }

    /**
     * Tela principal de filtros e visualização prévia dos relatórios.
     */
    public function index(): void
    {
        $user = AuthHelper::user();
        $filters = $this->extractFilters();

        $reportData = $this->getReportData($filters);
        $summary    = $this->calculateSummary($filters['report_type'], $reportData);

        // Carrega dados para os selects de filtros
        $secretariats = $this->secretariatModel->findAll();
        $vehicles     = $this->vehicleModel->findAll();
        $categories   = $this->categoryModel->findAllActive();

        $pageTitle = 'Relatórios Oficiais';
        require_once VIEWS_PATH . '/layouts/header.php';
        require_once VIEWS_PATH . '/reports/index.php';
        require_once VIEWS_PATH . '/layouts/footer.php';
    }

    /**
     * Exportação dos dados filtrados para planilha CSV com UTF-8 BOM.
     */
    public function exportCsv(): void
    {
        $filters    = $this->extractFilters();
        $reportData = $this->getReportData($filters);
        $type       = $filters['report_type'];
        $dateStr    = date('Ymd_His');
        $filename   = "relatorio_{$type}_{$dateStr}.csv";

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate');
        header('Pragma: public');

        $out = fopen('php://output', 'w');

        // Escreve o BOM UTF-8 (\xEF\xBB\xBF) para compatibilidade nativa com Excel
        fwrite($out, "\xEF\xBB\xBF");

        $delimiter = ';';

        if ($type === 'fuelings') {
            // Cabeçalho CSV
            fputcsv($out, [
                'Data/Hora',
                'Placa',
                'Modelo',
                'Secretaria',
                'KM no Momento',
                'Litros',
                'Valor Total (R$)',
                'Valor por Litro (R$)',
                'Combustível',
                'Posto Fornecedor',
                'Consumo Médio (KM/L)'
            ], $delimiter);

            $fuelTypes = [
                'GASOLINE' => 'Gasolina',
                'ETHANOL'  => 'Etanol',
                'DIESEL'   => 'Diesel',
                'FLEX'     => 'Flex'
            ];

            foreach ($reportData as $row) {
                $unitPrice = ($row['liters'] > 0) ? round($row['total_price'] / $row['liters'], 2) : 0;
                $fuelLabel = $fuelTypes[$row['fuel_type']] ?? $row['fuel_type'];
                $avgKml = ($row['avg_kml'] !== null && $row['avg_kml'] !== '') ? number_format((float)$row['avg_kml'], 2, ',', '.') : '-';

                fputcsv($out, [
                    FormatHelper::formatDateTime($row['fueling_date']),
                    FormatHelper::formatPlate($row['vehicle_plate'] ?? ''),
                    $row['vehicle_model'] ?? '',
                    $row['secretariat_name'] ?? 'Não vinculada',
                    number_format((int)$row['km'], 0, '', '.'),
                    number_format((float)$row['liters'], 2, ',', '.'),
                    number_format((float)$row['total_price'], 2, ',', '.'),
                    number_format((float)$unitPrice, 2, ',', '.'),
                    $fuelLabel,
                    $row['station_name'] ?? '',
                    $avgKml
                ], $delimiter);
            }

        } elseif ($type === 'maintenances') {
            // Cabeçalho CSV
            fputcsv($out, [
                'Data do Serviço',
                'Placa',
                'Modelo',
                'Secretaria',
                'Categoria',
                'Oficina Contratada',
                'Número da OS',
                'Valor Total (R$)',
                'Status'
            ], $delimiter);

            $statusLabels = [
                'SCHEDULED'   => 'Agendada',
                'IN_PROGRESS' => 'Em Andamento',
                'PENDING'     => 'Pendente',
                'COMPLETED'   => 'Concluída'
            ];

            foreach ($reportData as $row) {
                fputcsv($out, [
                    FormatHelper::formatDate($row['service_date']),
                    FormatHelper::formatPlate($row['vehicle_plate'] ?? ''),
                    $row['vehicle_model'] ?? '',
                    $row['secretariat_name'] ?? 'Não vinculada',
                    $row['category_name'] ?? 'Geral',
                    $row['workshop_name'] ?? '',
                    $row['os_number'] ?? '',
                    number_format((float)$row['cost'], 2, ',', '.'),
                    $statusLabels[$row['status']] ?? $row['status']
                ], $delimiter);
            }

        } elseif ($type === 'consolidated') {
            // Cabeçalho CSV
            fputcsv($out, [
                'Placa',
                'Modelo',
                'Secretaria',
                'KM Rodada no Período',
                'Litros Consumidos',
                'Total Combustível (R$)',
                'Total Manutenção (R$)',
                'Custo Total Geral (R$)'
            ], $delimiter);

            foreach ($reportData as $row) {
                fputcsv($out, [
                    FormatHelper::formatPlate($row['plate'] ?? ''),
                    $row['model'] ?? '',
                    $row['secretariat_name'] ?? 'Não vinculada',
                    number_format((int)$row['km_traveled'], 0, '', '.'),
                    number_format((float)$row['total_liters'], 2, ',', '.'),
                    number_format((float)$row['fuel_cost'], 2, ',', '.'),
                    number_format((float)$row['maintenance_cost'], 2, ',', '.'),
                    number_format((float)$row['total_cost'], 2, ',', '.')
                ], $delimiter);
            }
        }

        fclose($out);
        exit;
    }

    /**
     * Exportação dos dados filtrados para documento oficial em PDF.
     */
    public function exportPdf(): void
    {
        $filters    = $this->extractFilters();
        $reportData = $this->getReportData($filters);
        $user       = AuthHelper::user();
        $type       = $filters['report_type'];

        // Monta o subtítulo descritivo dos filtros
        $subtitles = [];
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $subtitles[] = 'Período: ' . FormatHelper::formatDate($filters['start_date']) . ' a ' . FormatHelper::formatDate($filters['end_date']);
        } elseif (!empty($filters['start_date'])) {
            $subtitles[] = 'A partir de: ' . FormatHelper::formatDate($filters['start_date']);
        } elseif (!empty($filters['end_date'])) {
            $subtitles[] = 'Até: ' . FormatHelper::formatDate($filters['end_date']);
        } else {
            $subtitles[] = 'Período: Histórico Geral Completo';
        }

        if (!empty($filters['secretariat_id'])) {
            $sec = $this->secretariatModel->findById((int)$filters['secretariat_id']);
            if ($sec) {
                $subtitles[] = 'Secretaria: ' . $sec['name'];
            }
        } else {
            $subtitles[] = 'Secretaria: Todas';
        }

        $subtitleText = implode('  |  ', $subtitles);

        if ($type === 'fuelings') {
            $pdf = new ReportPdfHelper('L', 'Relatório Oficial de Abastecimentos de Combustível', $subtitleText, $user);
            $pdf->AddPage();

            $columns = ['Data/Hora', 'Placa', 'Modelo', 'Secretaria', 'KM', 'Litros', 'Total R$', 'R$/L', 'Combustível', 'Posto', 'KM/L'];
            $widths  = [26, 20, 32, 42, 18, 16, 22, 16, 22, 44, 19];
            $aligns  = ['C', 'C', 'L', 'L', 'R', 'R', 'R', 'R', 'C', 'L', 'R'];

            $pdf->renderTableHeader($columns, $widths, $aligns);

            $fuelTypes = [
                'GASOLINE' => 'Gasolina',
                'ETHANOL'  => 'Etanol',
                'DIESEL'   => 'Diesel',
                'FLEX'     => 'Flex'
            ];

            $totLiters = 0.0;
            $totCost   = 0.0;

            foreach ($reportData as $idx => $row) {
                $unitPrice = ($row['liters'] > 0) ? round($row['total_price'] / $row['liters'], 2) : 0;
                $fuelLabel = $fuelTypes[$row['fuel_type']] ?? $row['fuel_type'];
                $avgKml = ($row['avg_kml'] !== null && $row['avg_kml'] !== '') ? number_format((float)$row['avg_kml'], 2, ',', '.') : '-';

                $totLiters += (float)$row['liters'];
                $totCost   += (float)$row['total_price'];

                $pdf->renderTableRow([
                    FormatHelper::formatDateTime($row['fueling_date']),
                    FormatHelper::formatPlate($row['vehicle_plate'] ?? ''),
                    $row['vehicle_model'] ?? '',
                    $row['secretariat_name'] ?? 'Não vinculada',
                    number_format((int)$row['km'], 0, '', '.'),
                    number_format((float)$row['liters'], 2, ',', '.'),
                    FormatHelper::formatCurrency($row['total_price']),
                    FormatHelper::formatCurrency($unitPrice),
                    $fuelLabel,
                    $row['station_name'] ?? '',
                    $avgKml
                ], $widths, $aligns, $idx % 2 === 1);
            }

            // Sumário
            $pdf->renderTableSummary([
                'TOTAL CONSOLIDADO',
                '',
                '',
                count($reportData) . ' abastecimentos',
                '',
                number_format($totLiters, 2, ',', '.') . ' L',
                FormatHelper::formatCurrency($totCost),
                '',
                '',
                '',
                ''
            ], $widths, $aligns);

        } elseif ($type === 'maintenances') {
            $pdf = new ReportPdfHelper('L', 'Relatório Oficial de Manutenções e Serviços Mecânicos', $subtitleText, $user);
            $pdf->AddPage();

            $columns = ['Data', 'Placa', 'Modelo', 'Secretaria', 'Categoria', 'Oficina', 'Nº OS', 'Valor Total', 'Status'];
            $widths  = [22, 22, 38, 45, 38, 45, 24, 25, 20];
            $aligns  = ['C', 'C', 'L', 'L', 'L', 'L', 'C', 'R', 'C'];

            $pdf->renderTableHeader($columns, $widths, $aligns);

            $statusLabels = [
                'SCHEDULED'   => 'Agendada',
                'IN_PROGRESS' => 'Em Andamento',
                'PENDING'     => 'Pendente',
                'COMPLETED'   => 'Concluída'
            ];

            $totCost = 0.0;

            foreach ($reportData as $idx => $row) {
                $totCost += (float)$row['cost'];
                $pdf->renderTableRow([
                    FormatHelper::formatDate($row['service_date']),
                    FormatHelper::formatPlate($row['vehicle_plate'] ?? ''),
                    $row['vehicle_model'] ?? '',
                    $row['secretariat_name'] ?? 'Não vinculada',
                    $row['category_name'] ?? 'Geral',
                    $row['workshop_name'] ?? '',
                    $row['os_number'] ?? '',
                    FormatHelper::formatCurrency($row['cost']),
                    $statusLabels[$row['status']] ?? $row['status']
                ], $widths, $aligns, $idx % 2 === 1);
            }

            // Sumário
            $pdf->renderTableSummary([
                'TOTAL CONSOLIDADO',
                '',
                '',
                count($reportData) . ' ordens de serviço',
                '',
                '',
                '',
                FormatHelper::formatCurrency($totCost),
                ''
            ], $widths, $aligns);

        } elseif ($type === 'consolidated') {
            $pdf = new ReportPdfHelper('L', 'Relatório Consolidado de Gastos e Rodagem por Veículo', $subtitleText, $user);
            $pdf->AddPage();

            $columns = ['Placa', 'Modelo', 'Secretaria', 'KM Rodada no Período', 'Litros Consumidos', 'Total Combustível', 'Total Manutenção', 'Custo Total Geral'];
            $widths  = [24, 45, 55, 34, 32, 33, 30, 26];
            $aligns  = ['C', 'L', 'L', 'R', 'R', 'R', 'R', 'R'];

            $pdf->renderTableHeader($columns, $widths, $aligns);

            $totKm     = 0;
            $totLiters = 0.0;
            $totFuel   = 0.0;
            $totMaint  = 0.0;
            $totGlobal = 0.0;

            foreach ($reportData as $idx => $row) {
                $totKm     += (int)$row['km_traveled'];
                $totLiters += (float)$row['total_liters'];
                $totFuel   += (float)$row['fuel_cost'];
                $totMaint  += (float)$row['maintenance_cost'];
                $totGlobal += (float)$row['total_cost'];

                $pdf->renderTableRow([
                    FormatHelper::formatPlate($row['plate'] ?? ''),
                    $row['model'] ?? '',
                    $row['secretariat_name'] ?? 'Não vinculada',
                    number_format((int)$row['km_traveled'], 0, '', '.') . ' KM',
                    number_format((float)$row['total_liters'], 2, ',', '.') . ' L',
                    FormatHelper::formatCurrency($row['fuel_cost']),
                    FormatHelper::formatCurrency($row['maintenance_cost']),
                    FormatHelper::formatCurrency($row['total_cost'])
                ], $widths, $aligns, $idx % 2 === 1);
            }

            // Sumário
            $pdf->renderTableSummary([
                'TOTAL GERAL',
                '',
                count($reportData) . ' veículos avaliados',
                number_format($totKm, 0, '', '.') . ' KM',
                number_format($totLiters, 2, ',', '.') . ' L',
                FormatHelper::formatCurrency($totFuel),
                FormatHelper::formatCurrency($totMaint),
                FormatHelper::formatCurrency($totGlobal)
            ], $widths, $aligns);
        }

        $dateStr = date('Ymd_His');
        $pdf->Output('I', "relatorio_{$type}_{$dateStr}.pdf");
        exit;
    }

    /**
     * Extrai e sanitiza os parâmetros de filtro recebidos via GET.
     */
    private function extractFilters(): array
    {
        $validTypes = ['fuelings', 'maintenances', 'consolidated'];
        $type = $_GET['report_type'] ?? 'fuelings';
        if (!in_array($type, $validTypes, true)) {
            $type = 'fuelings';
        }

        // Padrão de datas: se não informadas, assume o mês corrente
        $startDate = !empty($_GET['start_date']) ? trim($_GET['start_date']) : date('Y-m-01');
        $endDate   = !empty($_GET['end_date'])   ? trim($_GET['end_date'])   : date('Y-m-t');

        return [
            'report_type'    => $type,
            'start_date'     => $startDate,
            'end_date'       => $endDate,
            'secretariat_id' => !empty($_GET['secretariat_id']) ? (int)$_GET['secretariat_id'] : null,
            'vehicle_id'     => !empty($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : null,
            'category_id'    => !empty($_GET['category_id']) ? (int)$_GET['category_id'] : null,
            'status'         => !empty($_GET['status']) ? trim($_GET['status']) : null,
        ];
    }

    /**
     * Executa a consulta de dados de acordo com o tipo de relatório selecionado.
     */
    private function getReportData(array $filters): array
    {
        $type = $filters['report_type'];

        if ($type === 'fuelings') {
            return $this->fuelingModel->findAll([
                'start_date'     => $filters['start_date'],
                'end_date'       => $filters['end_date'],
                'secretariat_id' => $filters['secretariat_id'],
                'vehicle_id'     => $filters['vehicle_id'],
            ]);
        }

        if ($type === 'maintenances') {
            return $this->maintenanceModel->findAll([
                'start_date'     => $filters['start_date'],
                'end_date'       => $filters['end_date'],
                'secretariat_id' => $filters['secretariat_id'],
                'vehicle_id'     => $filters['vehicle_id'],
                'category_id'    => $filters['category_id'],
                'status'         => $filters['status'],
            ]);
        }

        if ($type === 'consolidated') {
            return $this->getConsolidatedReportData($filters);
        }

        return [];
    }

    /**
     * Compila os dados consolidados de gastos e rodagem por veículo no período.
     */
    private function getConsolidatedReportData(array $filters): array
    {
        $db = Database::getPdo();

        // 1. Busca os veículos cadastrados (aplicando filtro de secretaria se presente)
        $sqlVehicles = "SELECT v.id, v.plate, v.model, v.brand, v.current_km, s.name AS secretariat_name
                        FROM vehicles v
                        LEFT JOIN secretariats s ON v.secretariat_id = s.id
                        WHERE 1=1";
        $paramsV = [];

        if (!empty($filters['secretariat_id'])) {
            $sqlVehicles .= " AND v.secretariat_id = :sec_id";
            $paramsV[':sec_id'] = (int)$filters['secretariat_id'];
        }

        if (!empty($filters['vehicle_id'])) {
            $sqlVehicles .= " AND v.id = :vehicle_id";
            $paramsV[':vehicle_id'] = (int)$filters['vehicle_id'];
        }

        $sqlVehicles .= " ORDER BY s.name ASC, v.model ASC, v.plate ASC";

        $stmtV = $db->prepare($sqlVehicles);
        foreach ($paramsV as $k => $v) {
            $stmtV->bindValue($k, $v, PDO::PARAM_INT);
        }
        $stmtV->execute();
        $vehicles = $stmtV->fetchAll() ?: [];

        $results = [];

        // 2. Para cada veículo, compila os totais de combustível, manutenção e KM rodada no período
        foreach ($vehicles as $v) {
            $vId = (int)$v['id'];

            // Gastos e litros de combustível
            $sqlFuel = "SELECT COUNT(*) AS fueling_count,
                               COALESCE(SUM(liters), 0) AS total_liters,
                               COALESCE(SUM(total_price), 0) AS total_fuel_cost,
                               MIN(km) AS min_km,
                               MAX(km) AS max_km
                        FROM fuelings
                        WHERE vehicle_id = :v_id";
            $paramsF = [':v_id' => $vId];

            if (!empty($filters['start_date'])) {
                $sqlFuel .= " AND fueling_date >= :start_date";
                $paramsF[':start_date'] = $filters['start_date'] . ' 00:00:00';
            }
            if (!empty($filters['end_date'])) {
                $sqlFuel .= " AND fueling_date <= :end_date";
                $paramsF[':end_date'] = $filters['end_date'] . ' 23:59:59';
            }

            $stmtF = $db->prepare($sqlFuel);
            foreach ($paramsF as $k => $val) {
                if (is_int($val)) {
                    $stmtF->bindValue($k, $val, PDO::PARAM_INT);
                } else {
                    $stmtF->bindValue($k, $val, PDO::PARAM_STR);
                }
            }
            $stmtF->execute();
            $fuelData = $stmtF->fetch() ?: [];

            $fuelingCount = (int)($fuelData['fueling_count'] ?? 0);
            $totalLiters  = (float)($fuelData['total_liters'] ?? 0);
            $fuelCost     = (float)($fuelData['total_fuel_cost'] ?? 0);
            $kmTraveled   = 0;

            if ($fuelingCount > 1) {
                $kmTraveled = (int)$fuelData['max_km'] - (int)$fuelData['min_km'];
            } elseif ($fuelingCount === 1) {
                // Se teve 1 abastecimento no período, busca o abastecimento anterior ao período para calcular delta
                $stmtPrev = $db->prepare("SELECT km FROM fuelings WHERE vehicle_id = :v_id AND fueling_date < :start_date ORDER BY fueling_date DESC LIMIT 1");
                $stmtPrev->bindValue(':v_id', $vId, PDO::PARAM_INT);
                $stmtPrev->bindValue(':start_date', $filters['start_date'] . ' 00:00:00', PDO::PARAM_STR);
                $stmtPrev->execute();
                $prevKm = $stmtPrev->fetchColumn();

                if ($prevKm !== false && $prevKm !== null) {
                    $kmTraveled = max(0, (int)$fuelData['max_km'] - (int)$prevKm);
                }
            }

            // Gastos com manutenções no período
            $sqlMaint = "SELECT COALESCE(SUM(cost), 0) AS total_maintenance_cost,
                                COUNT(*) AS maintenance_count
                         FROM maintenances
                         WHERE vehicle_id = :v_id";
            $paramsM = [':v_id' => $vId];

            if (!empty($filters['start_date'])) {
                $sqlMaint .= " AND service_date >= :start_date";
                $paramsM[':start_date'] = $filters['start_date'];
            }
            if (!empty($filters['end_date'])) {
                $sqlMaint .= " AND service_date <= :end_date";
                $paramsM[':end_date'] = $filters['end_date'];
            }

            $stmtM = $db->prepare($sqlMaint);
            foreach ($paramsM as $k => $val) {
                if (is_int($val)) {
                    $stmtM->bindValue($k, $val, PDO::PARAM_INT);
                } else {
                    $stmtM->bindValue($k, $val, PDO::PARAM_STR);
                }
            }
            $stmtM->execute();
            $maintData = $stmtM->fetch() ?: [];

            $maintCost  = (float)($maintData['total_maintenance_cost'] ?? 0);
            $maintCount = (int)($maintData['maintenance_count'] ?? 0);
            $totalCost  = $fuelCost + $maintCost;

            // Inclui o veículo no consolidado
            $results[] = [
                'vehicle_id'        => $vId,
                'plate'             => $v['plate'],
                'model'             => $v['model'],
                'brand'             => $v['brand'],
                'secretariat_name'  => $v['secretariat_name'],
                'fueling_count'     => $fuelingCount,
                'total_liters'      => $totalLiters,
                'fuel_cost'         => $fuelCost,
                'km_traveled'       => $kmTraveled,
                'maintenance_count' => $maintCount,
                'maintenance_cost'  => $maintCost,
                'total_cost'        => $totalCost
            ];
        }

        return $results;
    }

    /**
     * Calcula métricas agregadas do resumo para exibição nos cards do topo da tela.
     */
    private function calculateSummary(string $reportType, array $data): array
    {
        $count = count($data);

        if ($reportType === 'fuelings') {
            $totalCost   = 0.0;
            $totalLiters = 0.0;
            foreach ($data as $row) {
                $totalCost   += (float)$row['total_price'];
                $totalLiters += (float)$row['liters'];
            }
            $avgPricePerLiter = ($totalLiters > 0) ? ($totalCost / $totalLiters) : 0.0;

            return [
                'count'             => $count,
                'total_cost'        => $totalCost,
                'total_liters'      => $totalLiters,
                'avg_price_liter'   => $avgPricePerLiter
            ];
        }

        if ($reportType === 'maintenances') {
            $totalCost = 0.0;
            $completed = 0;
            $pending   = 0;
            foreach ($data as $row) {
                $totalCost += (float)$row['cost'];
                if ($row['status'] === 'COMPLETED') {
                    $completed++;
                } else {
                    $pending++;
                }
            }
            return [
                'count'      => $count,
                'total_cost' => $totalCost,
                'completed'  => $completed,
                'pending'    => $pending
            ];
        }

        if ($reportType === 'consolidated') {
            $totFuel   = 0.0;
            $totMaint  = 0.0;
            $totGlobal = 0.0;
            $totKm     = 0;
            $totLiters = 0.0;

            foreach ($data as $row) {
                $totFuel   += (float)$row['fuel_cost'];
                $totMaint  += (float)$row['maintenance_cost'];
                $totGlobal += (float)$row['total_cost'];
                $totKm     += (int)$row['km_traveled'];
                $totLiters += (float)$row['total_liters'];
            }

            return [
                'count'            => $count,
                'total_fuel_cost'  => $totFuel,
                'total_maint_cost' => $totMaint,
                'total_global'     => $totGlobal,
                'total_km'         => $totKm,
                'total_liters'     => $totLiters
            ];
        }

        return ['count' => 0];
    }
}
