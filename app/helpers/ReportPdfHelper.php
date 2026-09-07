<?php
/**
 * SisFrota Municipal - Helper de Geração de Relatórios Oficiais em PDF
 * Utiliza a biblioteca local FPDF com cabeçalho da Prefeitura de Crateús,
 * estilização conforme DESIGN.md e codificação correta para o padrão WinAnsi.
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

require_once __DIR__ . '/fpdf/fpdf.php';
require_once __DIR__ . '/FormatHelper.php';

class ReportPdfHelper extends FPDF
{
    private string $reportTitle = 'Relatório Oficial';
    private string $reportSubtitle = '';
    private string $emitterName = 'Usuário';
    private string $emitterRole = 'ADMIN';
    private string $logoPath = '';

    public function __construct(string $orientation = 'P', string $reportTitle = '', string $reportSubtitle = '', ?array $user = null)
    {
        parent::__construct($orientation, 'mm', 'A4');

        $this->reportTitle = $reportTitle ?: 'Relatório Oficial';
        $this->reportSubtitle = $reportSubtitle ?: '';

        if ($user) {
            $this->emitterName = $user['full_name'] ?? ($user['username'] ?? 'Usuário');
            $this->emitterRole = $user['role'] ?? 'ADMIN';
        }

        $this->logoPath = ROOT_PATH . '/assets/img/logo.png';

        $this->SetAutoPageBreak(true, 18);
        $this->AliasNbPages('{nb}');
        $this->SetTitle($this->reportTitle, true);
        $this->SetAuthor('SisFrota Municipal - Prefeitura de Crateús', true);
        $this->SetCreator('SisFrota Municipal', true);
    }

    /**
     * Converte strings UTF-8 para ISO-8859-1 (WinAnsi) para compatibilidade com o FPDF.
     */
    public function utf8(string $text): string
    {
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
        }
        if (function_exists('iconv')) {
            return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text) ?: $text;
        }
        return utf8_decode($text);
    }

    /**
     * Cabeçalho institucional oficial de todas as páginas.
     */
    public function Header(): void
    {
        // Posição inicial
        $this->SetY(8);
        $this->SetX($this->lMargin);

        // Largura útil
        $pageWidth = $this->w - $this->lMargin - $this->rMargin;

        // Logo oficial da Prefeitura
        if (file_exists($this->logoPath)) {
            $this->Image($this->logoPath, $this->lMargin, 8, 14, 14);
            $textStartX = $this->lMargin + 18;
            $textWidth = $pageWidth - 18;
        } else {
            $textStartX = $this->lMargin;
            $textWidth = $pageWidth;
        }

        // Títulos institucionais
        $this->SetXY($textStartX, 8);
        $this->SetFont('Helvetica', 'B', 10);
        $this->SetTextColor(15, 23, 42); // #0f172a Dark Charcoal
        $this->Cell($textWidth, 4.5, $this->utf8('PREFEITURA MUNICIPAL DE CRATEÚS'), 0, 1, 'L');

        $this->SetX($textStartX);
        $this->SetFont('Helvetica', '', 8);
        $this->SetTextColor(71, 85, 105); // #475569 Slate Muted
        $this->Cell($textWidth, 4, $this->utf8('SisFrota Municipal • Sistema de Gestão de Frota'), 0, 1, 'L');

        $this->SetX($textStartX);
        $this->SetFont('Helvetica', 'B', 9);
        $this->SetTextColor(0, 107, 44); // #006b2c Primary Dark
        $this->Cell($textWidth, 4.5, $this->utf8(strtoupper($this->reportTitle)), 0, 1, 'L');

        // Subtítulo de filtros aplicados
        if (!empty($this->reportSubtitle)) {
            $this->SetX($textStartX);
            $this->SetFont('Helvetica', 'I', 7.5);
            $this->SetTextColor(100, 116, 139);
            $this->Cell($textWidth, 3.5, $this->utf8($this->reportSubtitle), 0, 1, 'L');
        }

        $this->Ln(2);

        // Barra horizontal institucional verde
        $this->SetDrawColor(22, 163, 74); // #16a34a Municipal Green
        $this->SetLineWidth(0.6);
        $this->Line($this->lMargin, $this->GetY(), $this->w - $this->rMargin, $this->GetY());
        $this->Ln(4);
    }

    /**
     * Rodapé institucional com data, emissor e paginação.
     */
    public function Footer(): void
    {
        $this->SetY(-14);

        // Linha divisória sutil
        $this->SetDrawColor(226, 232, 240); // #e2e8f0
        $this->SetLineWidth(0.3);
        $this->Line($this->lMargin, $this->GetY(), $this->w - $this->rMargin, $this->GetY());

        $this->SetY(-11);
        $this->SetFont('Helvetica', '', 7.5);
        $this->SetTextColor(100, 116, 139);

        // Rótulo do perfil
        $roles = [
            'ADMIN' => 'Administrador',
            'MANAGER' => 'Gestor',
            'OPERATOR' => 'Operador'
        ];
        $roleLabel = $roles[$this->emitterRole] ?? $this->emitterRole;

        $pageWidth = $this->w - $this->lMargin - $this->rMargin;
        $leftText = sprintf(
            'SisFrota Municipal • Emitido em %s às %s por %s (%s)',
            date('d/m/Y'),
            date('H:i:s'),
            $this->emitterName,
            $roleLabel
        );

        $this->Cell($pageWidth * 0.75, 4, $this->utf8($leftText), 0, 0, 'L');
        $this->Cell($pageWidth * 0.25, 4, $this->utf8('Página ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
    }

    /**
     * Renderiza o cabeçalho de uma tabela de relatório.
     */
    public function renderTableHeader(array $columns, array $widths, array $aligns = []): void
    {
        $this->SetFont('Helvetica', 'B', 8);
        $this->SetFillColor(22, 163, 74); // #16a34a Municipal Green
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(200, 210, 200);
        $this->SetLineWidth(0.2);

        foreach ($columns as $idx => $colName) {
            $w = $widths[$idx] ?? 25;
            $align = $aligns[$idx] ?? 'L';
            $this->Cell($w, 6.5, $this->utf8($colName), 1, 0, $align, true);
        }
        $this->Ln();
    }

    /**
     * Renderiza uma linha de dados com zebra striping.
     */
    public function renderTableRow(array $values, array $widths, array $aligns = [], bool $isEven = false): void
    {
        $this->SetFont('Helvetica', '', 7.5);

        if ($isEven) {
            $this->SetFillColor(248, 250, 252); // #f8fafc
        } else {
            $this->SetFillColor(255, 255, 255);
        }

        $this->SetTextColor(15, 23, 42); // #0f172a
        $this->SetDrawColor(226, 232, 240); // #e2e8f0
        $this->SetLineWidth(0.15);

        foreach ($values as $idx => $val) {
            $w = $widths[$idx] ?? 25;
            $align = $aligns[$idx] ?? 'L';
            $this->Cell($w, 5.5, $this->utf8((string) $val), 1, 0, $align, true);
        }
        $this->Ln();
    }

    /**
     * Renderiza uma linha de totais ou sumário no final da tabela.
     */
    public function renderTableSummary(array $values, array $widths, array $aligns = []): void
    {
        $this->SetFont('Helvetica', 'B', 8);
        $this->SetFillColor(241, 245, 249); // #f1f5f9
        $this->SetTextColor(15, 23, 42);
        $this->SetDrawColor(203, 213, 225);
        $this->SetLineWidth(0.25);

        foreach ($values as $idx => $val) {
            $w = $widths[$idx] ?? 25;
            $align = $aligns[$idx] ?? 'L';
            $this->Cell($w, 6.5, $this->utf8((string) $val), 1, 0, $align, true);
        }
        $this->Ln();
    }
}
