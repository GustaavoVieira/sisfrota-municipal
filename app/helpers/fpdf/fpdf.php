<?php
/**
 * FPDF 1.86 (PHP 8.x compatible)
 * Library for generating PDF documents with pure PHP.
 */

if (!defined('SYS_ENTRY') && !defined('FPDF_ALLOW_STANDALONE')) {
    die('Acesso direto negado.');
}

define('FPDF_VERSION', '1.86');

class FPDF
{
    protected int $page = 0;
    protected int $n = 2;
    protected array $offsets = [];
    protected string $buffer = '';
    protected array $pages = [];
    protected int $state = 0;
    protected bool $compress = true;
    protected float $k;
    protected string $DefOrientation;
    protected string $CurOrientation;
    protected array $StdPageSizes = [];
    protected array $DefPageSize;
    protected array $CurPageSize;
    protected int $CurRotation = 0;
    protected array $PageInfo = [];
    protected float $wPt;
    protected float $hPt;
    protected float $w;
    protected float $h;
    protected float $lMargin;
    protected float $tMargin;
    protected float $rMargin;
    protected float $bMargin;
    protected float $cMargin;
    protected float $x;
    protected float $y;
    protected float $lasth = 0;
    protected float $LineWidth;
    protected string $fontpath = '';
    protected array $CoreFonts = [];
    protected array $fonts = [];
    protected array $FontFiles = [];
    protected array $encodings = [];
    protected array $cmaps = [];
    protected string $FontFamily = '';
    protected string $FontStyle = '';
    protected bool $underline = false;
    protected array $CurrentFont = [];
    protected float $FontSizePt = 12;
    protected float $FontSize = 12;
    protected string $DrawColor = '0 G';
    protected string $FillColor = '0 g';
    protected string $TextColor = '0 g';
    protected bool $ColorFlag = false;
    protected bool $WithAlpha = false;
    protected float $ws = 0;
    protected array $images = [];
    protected array $PageLinks = [];
    protected array $links = [];
    protected bool $AutoPageBreak = true;
    protected float $PageBreakTrigger;
    protected bool $InHeader = false;
    protected bool $InFooter = false;
    protected string $AliasNbPages = '';
    protected string $ZoomMode;
    protected string $LayoutMode;
    protected array $metadata = [];
    protected string $PDFVersion = '1.3';

    public function __construct(string $orientation = 'P', string $unit = 'mm', mixed $size = 'A4')
    {
        $this->CoreFonts = [
            'courier', 'courierB', 'courierI', 'courierBI',
            'helvetica', 'helveticaB', 'helveticaI', 'helveticaBI',
            'times', 'timesB', 'timesI', 'timesBI',
            'symbol', 'zapfdingbats'
        ];

        // Scale factor
        if ($unit === 'pt') {
            $this->k = 1;
        } elseif ($unit === 'mm') {
            $this->k = 72 / 25.4;
        } elseif ($unit === 'cm') {
            $this->k = 72 / 2.54;
        } elseif ($unit === 'in') {
            $this->k = 72;
        } else {
            $this->Error('Incorrect unit: ' . $unit);
        }

        // Page sizes
        $this->StdPageSizes = [
            'a3' => [841.89, 1190.55],
            'a4' => [595.28, 841.89],
            'a5' => [420.94, 595.28],
            'letter' => [612, 792],
            'legal' => [612, 1008]
        ];

        $size = $this->_getpagesize($size);
        $this->DefPageSize = $size;
        $this->CurPageSize = $size;

        // Orientation
        $orientation = strtolower($orientation);
        if ($orientation === 'p' || $orientation === 'portrait') {
            $this->DefOrientation = 'P';
            $this->w = $size[0];
            $this->h = $size[1];
        } elseif ($orientation === 'l' || $orientation === 'landscape') {
            $this->DefOrientation = 'L';
            $this->w = $size[1];
            $this->h = $size[0];
        } else {
            $this->Error('Incorrect orientation: ' . $orientation);
        }

        $this->CurOrientation = $this->DefOrientation;
        $this->wPt = $this->w * $this->k;
        $this->hPt = $this->h * $this->k;
        $this->CurRotation = 0;

        // Margins (1cm default)
        $margin = 28.35 / $this->k;
        $this->SetMargins($margin, $margin);
        $this->cMargin = $margin / 10;
        $this->LineWidth = .567 / $this->k;
        $this->SetAutoPageBreak(true, 2 * $margin);
        $this->SetDisplayMode('default');
        $this->SetCompression(true);
    }

    public function SetMargins(float $left, float $top, ?float $right = null): void
    {
        $this->lMargin = $left;
        $this->tMargin = $top;
        if ($right === null) {
            $right = $left;
        }
        $this->rMargin = $right;
    }

    public function SetLeftMargin(float $margin): void
    {
        $this->lMargin = $margin;
        if ($this->page > 0 && $this->x < $margin) {
            $this->x = $margin;
        }
    }

    public function SetTopMargin(float $margin): void
    {
        $this->tMargin = $margin;
    }

    public function SetRightMargin(float $margin): void
    {
        $this->rMargin = $margin;
    }

    public function SetAutoPageBreak(bool $auto, float $margin = 0): void
    {
        $this->AutoPageBreak = $auto;
        $this->bMargin = $margin;
        $this->PageBreakTrigger = $this->h - $margin;
    }

    public function SetDisplayMode(mixed $zoom, string $layout = 'continuous'): void
    {
        if ($zoom === 'fullpage' || $zoom === 'fullwidth' || $zoom === 'real' || $zoom === 'default' || !is_string($zoom)) {
            $this->ZoomMode = (string) $zoom;
        } else {
            $this->Error('Incorrect zoom display mode: ' . $zoom);
        }

        if ($layout === 'single' || $layout === 'continuous' || $layout === 'two' || $layout === 'default') {
            $this->LayoutMode = $layout;
        } else {
            $this->Error('Incorrect layout display mode: ' . $layout);
        }
    }

    public function SetCompression(bool $compress): void
    {
        $this->compress = function_exists('gzcompress') ? $compress : false;
    }

    public function SetTitle(string $title, bool $isUTF8 = false): void
    {
        $this->metadata['Title'] = $isUTF8 ? $title : $this->_UTF8toUTF16($title);
    }

    public function SetAuthor(string $author, bool $isUTF8 = false): void
    {
        $this->metadata['Author'] = $isUTF8 ? $author : $this->_UTF8toUTF16($author);
    }

    public function SetSubject(string $subject, bool $isUTF8 = false): void
    {
        $this->metadata['Subject'] = $isUTF8 ? $subject : $this->_UTF8toUTF16($subject);
    }

    public function SetKeywords(string $keywords, bool $isUTF8 = false): void
    {
        $this->metadata['Keywords'] = $isUTF8 ? $keywords : $this->_UTF8toUTF16($keywords);
    }

    public function SetCreator(string $creator, bool $isUTF8 = false): void
    {
        $this->metadata['Creator'] = $isUTF8 ? $creator : $this->_UTF8toUTF16($creator);
    }

    public function AliasNbPages(string $alias = '{nb}'): void
    {
        $this->AliasNbPages = $alias;
    }

    public function Error(string $msg): void
    {
        throw new Exception('FPDF error: ' . $msg);
    }

    public function Close(): void
    {
        if ($this->state === 3) {
            return;
        }
        if ($this->page === 0) {
            $this->AddPage();
        }
        $this->InFooter = true;
        $this->Footer();
        $this->InFooter = false;
        $this->_endpage();
        $this->_enddoc();
    }

    public function AddPage(string $orientation = '', mixed $size = '', int $rotation = 0): void
    {
        if ($this->state === 0) {
            $this->state = 1;
        }

        $family = $this->FontFamily;
        $style = $this->FontStyle . ($this->underline ? 'U' : '');
        $fontsize = $this->FontSizePt;
        $lw = $this->LineWidth;
        $dc = $this->DrawColor;
        $fc = $this->FillColor;
        $tc = $this->TextColor;
        $cf = $this->ColorFlag;

        if ($this->page > 0) {
            $this->InFooter = true;
            $this->Footer();
            $this->InFooter = false;
            $this->_endpage();
        }

        $this->_beginpage($orientation, $size, $rotation);
        $this->_out('2 J');
        $this->LineWidth = $lw;
        $this->_out(sprintf('%.2F w', $lw * $this->k));

        if ($family) {
            $this->SetFont($family, $style, $fontsize);
        }
        $this->DrawColor = $dc;
        if ($dc !== '0 G') {
            $this->_out($dc);
        }
        $this->FillColor = $fc;
        if ($fc !== '0 g') {
            $this->_out($fc);
        }
        $this->TextColor = $tc;
        $this->ColorFlag = $cf;

        $this->InHeader = true;
        $this->Header();
        $this->InHeader = false;

        if ($this->LineWidth !== $lw) {
            $this->LineWidth = $lw;
            $this->_out(sprintf('%.2F w', $lw * $this->k));
        }
        if ($family) {
            $this->SetFont($family, $style, $fontsize);
        }
        if ($this->DrawColor !== $dc) {
            $this->DrawColor = $dc;
            $this->_out($dc);
        }
        if ($this->FillColor !== $fc) {
            $this->FillColor = $fc;
            $this->_out($fc);
        }
        $this->TextColor = $tc;
        $this->ColorFlag = $cf;
    }

    public function Header(): void
    {
        // To be implemented in subclasses
    }

    public function Footer(): void
    {
        // To be implemented in subclasses
    }

    public function PageNo(): int
    {
        return $this->page;
    }

    public function SetDrawColor(int $r, ?int $g = null, ?int $b = null): void
    {
        if (($r === 0 && $g === 0 && $b === 0) || $g === null) {
            $this->DrawColor = sprintf('%.3F G', $r / 255);
        } else {
            $this->DrawColor = sprintf('%.3F %.3F %.3F RG', $r / 255, $g / 255, $b / 255);
        }
        if ($this->page > 0) {
            $this->_out($this->DrawColor);
        }
    }

    public function SetFillColor(int $r, ?int $g = null, ?int $b = null): void
    {
        if (($r === 0 && $g === 0 && $b === 0) || $g === null) {
            $this->FillColor = sprintf('%.3F g', $r / 255);
        } else {
            $this->FillColor = sprintf('%.3F %.3F %.3F rg', $r / 255, $g / 255, $b / 255);
        }
        $this->ColorFlag = ($this->FillColor !== $this->TextColor);
        if ($this->page > 0) {
            $this->_out($this->FillColor);
        }
    }

    public function SetTextColor(int $r, ?int $g = null, ?int $b = null): void
    {
        if (($r === 0 && $g === 0 && $b === 0) || $g === null) {
            $this->TextColor = sprintf('%.3F g', $r / 255);
        } else {
            $this->TextColor = sprintf('%.3F %.3F %.3F rg', $r / 255, $g / 255, $b / 255);
        }
        $this->ColorFlag = ($this->FillColor !== $this->TextColor);
    }

    public function GetStringWidth(string $s): float
    {
        $s = (string) $s;
        $cw = $this->CurrentFont['cw'] ?? [];
        $w = 0;
        $l = strlen($s);
        for ($i = 0; $i < $l; $i++) {
            $w += $cw[$s[$i]] ?? 0;
        }
        return $w * $this->FontSize / 1000;
    }

    public function SetLineWidth(float $width): void
    {
        $this->LineWidth = $width;
        if ($this->page > 0) {
            $this->_out(sprintf('%.2F w', $width * $this->k));
        }
    }

    public function Line(float $x1, float $y1, float $x2, float $y2): void
    {
        $this->_out(sprintf('%.2F %.2F m %.2F %.2F l S', $x1 * $this->k, ($this->h - $y1) * $this->k, $x2 * $this->k, ($this->h - $y2) * $this->k));
    }

    public function Rect(float $x, float $y, float $w, float $h, string $style = ''): void
    {
        if ($style === 'F') {
            $op = 'f';
        } elseif ($style === 'FD' || $style === 'DF') {
            $op = 'B';
        } else {
            $op = 'S';
        }
        $this->_out(sprintf('%.2F %.2F %.2F %.2F re %s', $x * $this->k, ($this->h - $y) * $this->k, $w * $this->k, -$h * $this->k, $op));
    }

    public function SetFont(string $family, string $style = '', float $size = 0): void
    {
        $family = strtolower($family);
        if ($family === '') {
            $family = $this->FontFamily;
        }
        if ($family === 'arial') {
            $family = 'helvetica';
        } elseif ($family === 'symbol' || $family === 'zapfdingbats') {
            $style = '';
        }
        $style = strtoupper($style);
        if (strpos($style, 'U') !== false) {
            $this->underline = true;
            $style = str_replace('U', '', $style);
        } else {
            $this->underline = false;
        }
        if ($style === 'IB') {
            $style = 'BI';
        }
        if ($size === 0.0) {
            $size = $this->FontSizePt;
        }

        if ($this->FontFamily === $family && $this->FontStyle === $style && $this->FontSizePt === $size) {
            return;
        }

        $fontkey = $family . $style;
        if (!isset($this->fonts[$fontkey])) {
            if (in_array($fontkey, $this->CoreFonts, true)) {
                $this->_loadCoreFont($fontkey);
            } else {
                $this->Error('Undefined font: ' . $family . ' ' . $style);
            }
        }

        $this->FontFamily = $family;
        $this->FontStyle = $style;
        $this->FontSizePt = $size;
        $this->FontSize = $size / $this->k;
        $this->CurrentFont = $this->fonts[$fontkey];
        if ($this->page > 0) {
            $this->_out(sprintf('BT /F%d %.2F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
        }
    }

    public function SetFontSize(float $size): void
    {
        if ($this->FontSizePt === $size) {
            return;
        }
        $this->FontSizePt = $size;
        $this->FontSize = $size / $this->k;
        if ($this->page > 0 && isset($this->CurrentFont['i'])) {
            $this->_out(sprintf('BT /F%d %.2F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
        }
    }

    public function Cell(float $w, float $h = 0, string $txt = '', mixed $border = 0, int $ln = 0, string $align = '', bool $fill = false, string $link = ''): void
    {
        $k = $this->k;
        if ($this->y + $h > $this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AutoPageBreak) {
            $x = $this->x;
            $ws = $this->ws;
            if ($ws > 0) {
                $this->ws = 0;
                $this->_out('0 Tw');
            }
            $this->AddPage($this->CurOrientation, $this->CurPageSize, $this->CurRotation);
            $this->x = $x;
            if ($ws > 0) {
                $this->ws = $ws;
                $this->_out(sprintf('%.3F Tw', $ws * $k));
            }
        }
        if ($w === 0.0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $s = '';
        if ($fill || $border === 1) {
            if ($fill) {
                $op = ($border === 1) ? 'B' : 'f';
            } else {
                $op = 'S';
            }
            $s = sprintf('%.2F %.2F %.2F %.2F re %s ', $this->x * $k, ($this->h - $this->y) * $k, $w * $k, -$h * $k, $op);
        }
        if (is_string($border)) {
            $x = $this->x;
            $y = $this->y;
            if (strpos($border, 'L') !== false) {
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x * $k, ($this->h - $y) * $k, $x * $k, ($this->h - ($y + $h)) * $k);
            }
            if (strpos($border, 'T') !== false) {
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x * $k, ($this->h - $y) * $k, ($x + $w) * $k, ($this->h - $y) * $k);
            }
            if (strpos($border, 'R') !== false) {
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', ($x + $w) * $k, ($this->h - $y) * $k, ($x + $w) * $k, ($this->h - ($y + $h)) * $k);
            }
            if (strpos($border, 'B') !== false) {
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x * $k, ($this->h - ($y + $h)) * $k, ($x + $w) * $k, ($this->h - ($y + $h)) * $k);
            }
        }
        if ($txt !== '') {
            if ($align === 'R') {
                $dx = $w - $this->cMargin - $this->GetStringWidth($txt);
            } elseif ($align === 'C') {
                $dx = ($w - $this->GetStringWidth($txt)) / 2;
            } else {
                $dx = $this->cMargin;
            }
            if ($this->ColorFlag) {
                $s .= 'q ' . $this->TextColor . ' ';
            }
            $txt2 = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $txt);
            $s .= sprintf('BT %.2F %.2F Td (%s) Tj ET', ($this->x + $dx) * $k, ($this->h - ($this->y + .5 * $h + .3 * $this->FontSize)) * $k, $txt2);
            if ($this->underline) {
                $s .= ' ' . $this->_dounderline($this->x + $dx, $this->y + .5 * $h + .3 * $this->FontSize, $txt);
            }
            if ($this->ColorFlag) {
                $s .= ' Q';
            }
            if ($link) {
                $this->Link($this->x + $dx, $this->y + .5 * $h - .5 * $this->FontSize, $this->GetStringWidth($txt), $this->FontSize, $link);
            }
        }
        if ($s) {
            $this->_out($s);
        }
        $this->lasth = $h;
        if ($ln > 0) {
            $this->y += $h;
            if ($ln === 1) {
                $this->x = $this->lMargin;
            }
        } else {
            $this->x += $w;
        }
    }

    public function MultiCell(float $w, float $h, string $txt, mixed $border = 0, string $align = 'J', bool $fill = false): void
    {
        $cw = $this->CurrentFont['cw'] ?? [];
        if ($w === 0.0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] === "\n") {
            $nb--;
        }
        $b = 0;
        if ($border) {
            if ($border === 1) {
                $border = 'LTRB';
                $b = 'LRT';
                $b2 = 'LR';
            } else {
                $b2 = '';
                if (strpos($border, 'L') !== false) {
                    $b2 .= 'L';
                }
                if (strpos($border, 'R') !== false) {
                    $b2 .= 'R';
                }
                $b = (strpos($border, 'T') !== false) ? $b2 . 'T' : $b2;
            }
        }
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $ns = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c === "\n") {
                if ($this->ws > 0) {
                    $this->ws = 0;
                    $this->_out('0 Tw');
                }
                $this->Cell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill);
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $ns = 0;
                $nl++;
                if ($border && $nl === 2) {
                    $b = $b2;
                }
                continue;
            }
            if ($c === ' ') {
                $sep = $i;
                $ls = $l;
                $ns++;
            }
            $l += $cw[$c] ?? 0;
            if ($l > $wmax) {
                if ($sep === -1) {
                    if ($i === $j) {
                        $i++;
                    }
                    if ($this->ws > 0) {
                        $this->ws = 0;
                        $this->_out('0 Tw');
                    }
                    $this->Cell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill);
                } else {
                    if ($align === 'J') {
                        $this->ws = ($ns > 1) ? ($wmax - $ls) / 1000 * $this->FontSize / ($ns - 1) : 0;
                        $this->_out(sprintf('%.3F Tw', $this->ws * $this->k));
                    }
                    $this->Cell($w, $h, substr($s, $j, $sep - $j), $b, 2, $align, $fill);
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $ns = 0;
                $nl++;
                if ($border && $nl === 2) {
                    $b = $b2;
                }
            } else {
                $i++;
            }
        }
        if ($this->ws > 0) {
            $this->ws = 0;
            $this->_out('0 Tw');
        }
        if ($border && strpos($border, 'B') !== false) {
            $b .= 'B';
        }
        $this->Cell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill);
        $this->x = $this->lMargin;
    }

    public function Ln(?float $h = null): void
    {
        $this->x = $this->lMargin;
        if ($h === null) {
            $this->y += $this->lasth;
        } else {
            $this->y += $h;
        }
    }

    public function GetX(): float
    {
        return $this->x;
    }

    public function SetX(float $x): void
    {
        if ($x >= 0) {
            $this->x = $x;
        } else {
            $this->x = $this->w + $x;
        }
    }

    public function GetY(): float
    {
        return $this->y;
    }

    public function SetY(float $y, bool $resetX = true): void
    {
        if ($resetX) {
            $this->x = $this->lMargin;
        }
        if ($y >= 0) {
            $this->y = $y;
        } else {
            $this->y = $this->h + $y;
        }
    }

    public function SetXY(float $x, float $y): void
    {
        $this->SetY($y, false);
        $this->SetX($x);
    }

    public function Image(string $file, ?float $x = null, ?float $y = null, float $w = 0, float $h = 0, string $type = '', string $link = ''): void
    {
        if (!isset($this->images[$file])) {
            if ($type === '') {
                $pos = strrpos($file, '.');
                if (!$pos) {
                    $this->Error('Image file has no extension and no type was specified: ' . $file);
                }
                $type = substr($file, $pos + 1);
            }
            $type = strtolower($type);
            if ($type === 'jpeg') {
                $type = 'jpg';
            }
            $mtd = '_parse' . $type;
            if (!method_exists($this, $mtd)) {
                $this->Error('Unsupported image type: ' . $type);
            }
            $info = $this->$mtd($file);
            $info['i'] = count($this->images) + 1;
            $this->images[$file] = $info;
        } else {
            $info = $this->images[$file];
        }

        if ($w === 0.0 && $h === 0.0) {
            $w = -96;
            $h = -96;
        }
        if ($w < 0) {
            $w = -$info['w'] * 72 / $w / $this->k;
        }
        if ($h < 0) {
            $h = -$info['h'] * 72 / $h / $this->k;
        }
        if ($w === 0.0) {
            $w = $h * $info['w'] / $info['h'];
        }
        if ($h === 0.0) {
            $h = $w * $info['h'] / $info['w'];
        }

        if ($y === null) {
            if ($this->y + $h > $this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AutoPageBreak) {
                $x2 = $this->x;
                $this->AddPage($this->CurOrientation, $this->CurPageSize, $this->CurRotation);
                $this->x = $x2;
            }
            $y = $this->y;
            $this->y += $h;
        }
        if ($x === null) {
            $x = $this->x;
        }
        $this->_out(sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q', $w * $this->k, $h * $this->k, $x * $this->k, ($this->h - ($y + $h)) * $this->k, $info['i']));
        if ($link) {
            $this->Link($x, $y, $w, $h, $link);
        }
    }

    public function Link(float $x, float $y, float $w, float $h, mixed $link): void
    {
        $this->PageLinks[$this->page][] = [$x * $this->k, $this->hPt - $y * $this->k, $w * $this->k, $h * $this->k, $link];
    }

    public function Output(string $dest = '', string $name = '', bool $isUTF8 = false): string
    {
        $this->Close();
        if (strlen($name) === 0) {
            $name = 'doc.pdf';
        }
        $dest = strtoupper($dest);
        if ($dest === '') {
            $dest = 'I';
        }
        switch ($dest) {
            case 'I':
                $this->_checkoutput();
                if (PHP_SAPI !== 'cli') {
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: inline; ' . $this->_httpencode('filename', $name, $isUTF8));
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                }
                echo $this->buffer;
                break;
            case 'D':
                $this->_checkoutput();
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; ' . $this->_httpencode('filename', $name, $isUTF8));
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
                echo $this->buffer;
                break;
            case 'F':
                $f = fopen($name, 'wb');
                if (!$f) {
                    $this->Error('Unable to create output file: ' . $name);
                }
                fwrite($f, $this->buffer, strlen($this->buffer));
                fclose($f);
                break;
            case 'S':
                return $this->buffer;
            default:
                $this->Error('Incorrect output destination: ' . $dest);
        }
        return '';
    }

    // ─── Protected / Internal Methods ──────────────────────────────────────────

    protected function _getpagesize(mixed $size): array
    {
        if (is_string($size)) {
            $size = strtolower($size);
            if (!isset($this->StdPageSizes[$size])) {
                $this->Error('Unknown page size: ' . $size);
            }
            $a = $this->StdPageSizes[$size];
            return [$a[0] / $this->k, $a[1] / $this->k];
        }
        if ($size[0] > $size[1]) {
            return [$size[1], $size[0]];
        }
        return $size;
    }

    protected function _beginpage(string $orientation, mixed $size, int $rotation): void
    {
        $this->page++;
        $this->pages[$this->page] = '';
        $this->PageInfo[$this->page] = [];
        $this->state = 2;
        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
        $this->FontFamily = '';

        if ($orientation === '') {
            $orientation = $this->DefOrientation;
        } else {
            $orientation = strtoupper($orientation[0]);
        }
        if ($size === '') {
            $size = $this->DefPageSize;
        } else {
            $size = $this->_getpagesize($size);
        }
        if ($orientation !== $this->CurOrientation || $size[0] !== $this->CurPageSize[0] || $size[1] !== $this->CurPageSize[1]) {
            if ($orientation === 'P') {
                $this->w = $size[0];
                $this->h = $size[1];
            } else {
                $this->w = $size[1];
                $this->h = $size[0];
            }
            $this->wPt = $this->w * $this->k;
            $this->hPt = $this->h * $this->k;
            $this->PageBreakTrigger = $this->h - $this->bMargin;
            $this->CurOrientation = $orientation;
            $this->CurPageSize = $size;
        }
        if ($orientation !== $this->DefOrientation || $size[0] !== $this->DefPageSize[0] || $size[1] !== $this->DefPageSize[1]) {
            $this->PageInfo[$this->page]['size'] = [$this->wPt, $this->hPt];
        }
        if ($rotation !== 0) {
            if ($rotation % 90 !== 0) {
                $this->Error('Incorrect rotation value: ' . $rotation);
            }
            $this->CurRotation = $rotation;
            $this->PageInfo[$this->page]['rotation'] = $rotation;
        }
    }

    protected function _endpage(): void
    {
        $this->state = 1;
    }

    protected function _loadCoreFont(string $fontkey): void
    {
        $i = count($this->fonts) + 1;
        $name = '';
        $cw = [];

        // Standard metrics for core fonts
        $standardCw = [
            ' ' => 250, '!' => 278, '"' => 355, '#' => 556, '$' => 556, '%' => 889, '&' => 667, '\'' => 191,
            '(' => 333, ')' => 333, '*' => 389, '+' => 584, ',' => 278, '-' => 333, '.' => 278, '/' => 278,
            '0' => 556, '1' => 556, '2' => 556, '3' => 556, '4' => 556, '5' => 556, '6' => 556, '7' => 556,
            '8' => 556, '9' => 556, ':' => 278, ';' => 278, '<' => 584, '=' => 584, '>' => 584, '?' => 556,
            '@' => 1015, 'A' => 667, 'B' => 667, 'C' => 722, 'D' => 722, 'E' => 667, 'F' => 611, 'G' => 778,
            'H' => 722, 'I' => 278, 'J' => 500, 'K' => 667, 'L' => 556, 'M' => 833, 'N' => 722, 'O' => 778,
            'P' => 667, 'Q' => 778, 'R' => 722, 'S' => 667, 'T' => 611, 'U' => 722, 'V' => 667, 'W' => 944,
            'X' => 667, 'Y' => 667, 'Z' => 611, '[' => 278, '\\' => 278, ']' => 278, '^' => 469, '_' => 556,
            '`' => 333, 'a' => 556, 'b' => 556, 'c' => 500, 'd' => 556, 'e' => 556, 'f' => 278, 'g' => 556,
            'h' => 556, 'i' => 222, 'j' => 222, 'k' => 500, 'l' => 222, 'm' => 833, 'n' => 556, 'o' => 556,
            'p' => 556, 'q' => 556, 'r' => 333, 's' => 500, 't' => 278, 'u' => 556, 'v' => 500, 'w' => 722,
            'x' => 500, 'y' => 500, 'z' => 500, '{' => 334, '|' => 260, '}' => 334, '~' => 584
        ];

        switch ($fontkey) {
            case 'courier':
                $name = 'Courier';
                $cw = array_fill(0, 256, 600);
                break;
            case 'courierB':
                $name = 'Courier-Bold';
                $cw = array_fill(0, 256, 600);
                break;
            case 'courierI':
                $name = 'Courier-Oblique';
                $cw = array_fill(0, 256, 600);
                break;
            case 'courierBI':
                $name = 'Courier-BoldOblique';
                $cw = array_fill(0, 256, 600);
                break;
            case 'helvetica':
                $name = 'Helvetica';
                $cw = $standardCw;
                break;
            case 'helveticaB':
                $name = 'Helvetica-Bold';
                $cw = $standardCw;
                break;
            case 'helveticaI':
                $name = 'Helvetica-Oblique';
                $cw = $standardCw;
                break;
            case 'helveticaBI':
                $name = 'Helvetica-BoldOblique';
                $cw = $standardCw;
                break;
            case 'times':
                $name = 'Times-Roman';
                $cw = $standardCw;
                break;
            case 'timesB':
                $name = 'Times-Bold';
                $cw = $standardCw;
                break;
            case 'timesI':
                $name = 'Times-Italic';
                $cw = $standardCw;
                break;
            case 'timesBI':
                $name = 'Times-BoldItalic';
                $cw = $standardCw;
                break;
            default:
                $name = 'Helvetica';
                $cw = $standardCw;
        }

        $this->fonts[$fontkey] = [
            'i' => $i,
            'type' => 'core',
            'name' => $name,
            'up' => -100,
            'ut' => 50,
            'cw' => $cw
        ];
    }

    protected function _dounderline(float $x, float $y, string $txt): string
    {
        $up = $this->CurrentFont['up'] ?? -100;
        $ut = $this->CurrentFont['ut'] ?? 50;
        $w = $this->GetStringWidth($txt) + $this->ws * substr_count($txt, ' ');
        return sprintf('%.2F %.2F %.2F %.2F re f', $x * $this->k, ($this->h - ($y - $up / 1000 * $this->FontSize)) * $this->k, $w * $this->k, -$ut / 1000 * $this->FontSizePt);
    }

    protected function _parsejpg(string $file): array
    {
        $a = getimagesize($file);
        if (!$a) {
            $this->Error('Missing or incorrect image file: ' . $file);
        }
        if ($a[2] !== 2) {
            $this->Error('Not a JPEG file: ' . $file);
        }
        if (!isset($a['channels']) || $a['channels'] === 3) {
            $colspace = 'DeviceRGB';
        } elseif ($a['channels'] === 4) {
            $colspace = 'DeviceCMYK';
        } else {
            $colspace = 'DeviceGray';
        }
        $bpc = $a['bits'] ?? 8;
        $data = file_get_contents($file);
        return ['w' => $a[0], 'h' => $a[1], 'cs' => $colspace, 'bpc' => $bpc, 'f' => 'DCTDecode', 'data' => $data];
    }

    protected function _parsepng(string $file): array
    {
        $f = fopen($file, 'rb');
        if (!$f) {
            $this->Error('Can\'t open image file: ' . $file);
        }
        $info = $this->_parsepngstream($f, $file);
        fclose($f);
        return $info;
    }

    protected function _parsepngstream($f, string $file): array
    {
        if ($this->_readstream($f, 8) !== chr(137) . 'PNG' . chr(13) . chr(10) . chr(26) . chr(10)) {
            $this->Error('Not a PNG file: ' . $file);
        }
        $this->_readstream($f, 4);
        if ($this->_readstream($f, 4) !== 'IHDR') {
            $this->Error('Incorrect PNG file: ' . $file);
        }
        $w = $this->_readint($f);
        $h = $this->_readint($f);
        $bpc = ord($this->_readstream($f, 1));
        if ($bpc > 8) {
            $this->Error('16-bit depth not supported: ' . $file);
        }
        $ct = ord($this->_readstream($f, 1));
        if ($ct === 0 || $ct === 4) {
            $colspace = 'DeviceGray';
        } elseif ($ct === 2 || $ct === 6) {
            $colspace = 'DeviceRGB';
        } elseif ($ct === 3) {
            $colspace = 'Indexed';
        } else {
            $this->Error('Unknown color type: ' . $file);
        }
        if (ord($this->_readstream($f, 1)) !== 0) {
            $this->Error('Unknown compression method: ' . $file);
        }
        if (ord($this->_readstream($f, 1)) !== 0) {
            $this->Error('Unknown filter method: ' . $file);
        }
        if (ord($this->_readstream($f, 1)) !== 0) {
            $this->Error('Interlacing not supported: ' . $file);
        }
        $this->_readstream($f, 4);
        $dp = sprintf('/Predictor 15 /Colors %d /BitsPerComponent %d /Columns %d', ($ct === 2 || $ct === 6) ? 3 : 1, $bpc, $w);

        $pal = '';
        $trns = '';
        $data = '';
        do {
            $n = $this->_readint($f);
            $type = $this->_readstream($f, 4);
            if ($type === 'PLTE') {
                $pal = $this->_readstream($f, $n);
                $this->_readstream($f, 4);
            } elseif ($type === 'tRNS') {
                $t = $this->_readstream($f, $n);
                if ($ct === 0) {
                    $trns = [ord(substr($t, 1, 1))];
                } elseif ($ct === 2) {
                    $trns = [ord(substr($t, 1, 1)), ord(substr($t, 3, 1)), ord(substr($t, 5, 1))];
                } else {
                    $pos = strpos($t, chr(0));
                    if ($pos !== false) {
                        $trns = [$pos];
                    }
                }
                $this->_readstream($f, 4);
            } elseif ($type === 'IDAT') {
                $data .= $this->_readstream($f, $n);
                $this->_readstream($f, 4);
            } elseif ($type === 'IEND') {
                break;
            } else {
                $this->_readstream($f, $n + 4);
            }
        } while ($n);

        if ($colspace === 'Indexed' && empty($pal)) {
            $this->Error('Missing palette in ' . $file);
        }
        $info = ['w' => $w, 'h' => $h, 'cs' => $colspace, 'bpc' => $bpc, 'f' => 'FlateDecode', 'dp' => $dp, 'pal' => $pal, 'trns' => $trns];
        if ($ct >= 4) {
            if (!function_exists('gzuncompress')) {
                $this->Error('Zlib not available, can\'t handle PNG alpha channel: ' . $file);
            }
            $data = gzuncompress($data);
            $color = '';
            $alpha = '';
            if ($ct === 4) {
                $len = 2 * $w;
                for ($i = 0; $i < $h; $i++) {
                    $pos = (1 + $len) * $i;
                    $color .= $data[$pos];
                    $alpha .= $data[$pos];
                    $line = substr($data, $pos + 1, $len);
                    $color .= preg_replace('/(.)./s', '$1', $line);
                    $alpha .= preg_replace('/.(.)/s', '$1', $line);
                }
            } else {
                $len = 4 * $w;
                for ($i = 0; $i < $h; $i++) {
                    $pos = (1 + $len) * $i;
                    $color .= $data[$pos];
                    $alpha .= $data[$pos];
                    $line = substr($data, $pos + 1, $len);
                    $color .= preg_replace('/(...)./s', '$1', $line);
                    $alpha .= preg_replace('/...(.)/s', '$1', $line);
                }
            }
            unset($data);
            $data = gzcompress($color);
            $info['smask'] = gzcompress($alpha);
            $this->WithAlpha = true;
            if ($this->PDFVersion < '1.4') {
                $this->PDFVersion = '1.4';
            }
        }
        $info['data'] = $data;
        return $info;
    }

    protected function _readstream($f, int $n): string
    {
        $res = '';
        while ($n > 0 && !feof($f)) {
            $s = fread($f, $n);
            if ($s === false) {
                $this->Error('Error while reading stream');
            }
            $n -= strlen($s);
            $res .= $s;
        }
        if ($n > 0) {
            $this->Error('Unexpected end of stream');
        }
        return $res;
    }

    protected function _readint($f): int
    {
        $a = unpack('Ni', $this->_readstream($f, 4));
        return $a['i'];
    }

    protected function _out(string $s): void
    {
        if ($this->state === 2) {
            $this->pages[$this->page] .= $s . "\n";
        } elseif ($this->state === 1) {
            $this->_put($s);
        } elseif ($this->state === 0) {
            $this->Error('No page has been added yet');
        } elseif ($this->state === 3) {
            $this->Error('The document is closed');
        }
    }

    protected function _put(string $s): void
    {
        $this->buffer .= $s . "\n";
    }

    protected function _checkoutput(): void
    {
        if (PHP_SAPI !== 'cli') {
            if (headers_sent($file, $line)) {
                $this->Error("Some data has already been output, can't send PDF file (output started at $file:$line)");
            }
        }
    }

    protected function _httpencode(string $param, string $value, bool $isUTF8): string
    {
        if ($isUTF8) {
            return $param . "*=UTF-8''" . rawurlencode($value);
        }
        return $param . '="' . str_replace('"', '\\"', $value) . '"';
    }

    protected function _UTF8toUTF16(string $s): string
    {
        $res = "\xFE\xFF";
        $nb = strlen($s);
        $i = 0;
        while ($i < $nb) {
            $c1 = ord($s[$i++]);
            if ($c1 >= 224) {
                $c2 = ord($s[$i++]);
                $c3 = ord($s[$i++]);
                $res .= chr((($c1 & 0x0F) << 4) | (($c2 & 0x3C) >> 2));
                $res .= chr((($c2 & 0x03) << 6) | ($c3 & 0x3F));
            } elseif ($c1 >= 192) {
                $c2 = ord($s[$i++]);
                $res .= chr(($c1 & 0x1C) >> 2);
                $res .= chr((($c1 & 0x03) << 6) | ($c2 & 0x3F));
            } else {
                $res .= "\0" . chr($c1);
            }
        }
        return $res;
    }

    protected function _enddoc(): void
    {
        $this->state = 3;
        $this->_putheader();
        $this->_putpages();
        $this->_putresources();
        $this->_putinfo();
        $this->_putcatalog();
        // Cross-ref
        $offset = strlen($this->buffer);
        $this->_put('xref');
        $this->_put('0 ' . ($this->n + 1));
        $this->_put('0000000000 65535 f ');
        for ($i = 1; $i <= $this->n; $i++) {
            $this->_put(sprintf('%010d 00000 n ', $this->offsets[$i]));
        }
        // Trailer
        $this->_put('trailer');
        $this->_put('<<');
        $this->_put('/Size ' . ($this->n + 1));
        $this->_put('/Root ' . $this->n . ' 0 R');
        $this->_put('/Info ' . ($this->n - 1) . ' 0 R');
        $this->_put('>>');
        $this->_put('startxref');
        $this->_put((string) $offset);
        $this->_put('%%EOF');
    }

    protected function _putheader(): void
    {
        $this->_put('%PDF-' . $this->PDFVersion);
    }

    protected function _putpages(): void
    {
        $nb = $this->page;
        if ($this->AliasNbPages !== '') {
            $alias = $this->AliasNbPages;
            for ($n = 1; $n <= $nb; $n++) {
                $this->pages[$n] = str_replace($alias, (string) $nb, $this->pages[$n]);
            }
        }
        for ($n = 1; $n <= $nb; $n++) {
            $this->_newobj();
            $this->_put('<</Type /Page');
            $this->_put('/Parent 1 0 R');
            if (isset($this->PageInfo[$n]['size'])) {
                $this->_put(sprintf('/MediaBox [0 0 %.2F %.2F]', $this->PageInfo[$n]['size'][0], $this->PageInfo[$n]['size'][1]));
            }
            if (isset($this->PageInfo[$n]['rotation'])) {
                $this->_put('/Rotate ' . $this->PageInfo[$n]['rotation']);
            }
            $this->_put('/Resources 2 0 R');
            if (isset($this->PageLinks[$n])) {
                $annots = '/Annots [';
                foreach ($this->PageLinks[$n] as $pl) {
                    $rect = sprintf('%.2F %.2F %.2F %.2F', $pl[0], $pl[1], $pl[0] + $pl[2], $pl[1] - $pl[3]);
                    $annots .= '<</Type /Annot /Subtype /Link /Rect [' . $rect . '] /Border [0 0 0] ';
                    if (is_string($pl[4])) {
                        $annots .= '/A <</S /URI /URI ' . $this->_textstring($pl[4]) . '>>>>';
                    } else {
                        $l = $this->links[$pl[4]];
                        $h = isset($this->PageInfo[$l[0]]['size']) ? $this->PageInfo[$l[0]]['size'][1] : $this->hPt;
                        $annots .= sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>', $this->PageInfo[$l[0]]['n'], $h - $l[1] * $this->k);
                    }
                }
                $this->_put($annots . ']');
            }
            if ($this->WithAlpha) {
                $this->_put('/Group <</Type /Group /S /Transparency /CS /DeviceRGB>>');
            }
            $this->_put('/Contents ' . ($this->n + 1) . ' 0 R>>');
            $this->_put('endobj');
            // Page content
            $p = $this->pages[$n];
            if ($this->compress) {
                $p = gzcompress($p);
                $this->_newobj();
                $this->_put('<</Filter /FlateDecode /Length ' . strlen($p) . '>>');
            } else {
                $this->_newobj();
                $this->_put('<</Length ' . strlen($p) . '>>');
            }
            $this->_putstream($p);
            $this->_put('endobj');
        }
        // Pages root
        $this->offsets[1] = strlen($this->buffer);
        $this->_put('1 0 obj');
        $this->_put('<</Type /Pages');
        $kids = '/Kids [';
        for ($n = 1; $n <= $nb; $n++) {
            $kids .= (3 + 2 * ($n - 1)) . ' 0 R ';
        }
        $this->_put($kids . ']');
        $this->_put('/Count ' . $nb);
        $this->_put(sprintf('/MediaBox [0 0 %.2F %.2F]', $this->DefPageSize[0] * $this->k, $this->DefPageSize[1] * $this->k));
        $this->_put('>>');
        $this->_put('endobj');
    }

    protected function _putresources(): void
    {
        $this->_putfonts();
        $this->_putimages();
        // Resource dictionary
        $this->offsets[2] = strlen($this->buffer);
        $this->_put('2 0 obj');
        $this->_put('<<');
        $this->_put('/ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
        $this->_put('/Font <<');
        foreach ($this->fonts as $font) {
            $this->_put('/F' . $font['i'] . ' ' . $font['n'] . ' 0 R');
        }
        $this->_put('>>');
        $this->_put('/XObject <<');
        foreach ($this->images as $image) {
            $this->_put('/I' . $image['i'] . ' ' . $image['n'] . ' 0 R');
        }
        $this->_put('>>');
        $this->_put('>>');
        $this->_put('endobj');
    }

    protected function _putfonts(): void
    {
        foreach ($this->fonts as $k => $font) {
            $this->fonts[$k]['n'] = $this->n + 1;
            $type = $font['type'];
            $name = $font['name'];
            if ($type === 'core') {
                $this->_newobj();
                $this->_put('<</Type /Font');
                $this->_put('/BaseFont /' . $name);
                $this->_put('/Subtype /Type1');
                if ($name !== 'Symbol' && $name !== 'ZapfDingbats') {
                    $this->_put('/Encoding /WinAnsiEncoding');
                }
                $this->_put('>>');
                $this->_put('endobj');
            }
        }
    }

    protected function _putimages(): void
    {
        foreach ($this->images as $file => &$info) {
            $this->_putimage($info);
            unset($this->images[$file]['data']);
            unset($this->images[$file]['smask']);
        }
        unset($info);
    }

    protected function _putimage(array &$info): void
    {
        $this->_newobj();
        $info['n'] = $this->n;
        $this->_put('<</Type /XObject');
        $this->_put('/Subtype /Image');
        $this->_put('/Width ' . $info['w']);
        $this->_put('/Height ' . $info['h']);
        if ($info['cs'] === 'Indexed') {
            $this->_put('/ColorSpace [/Indexed /DeviceRGB ' . (strlen($info['pal']) / 3 - 1) . ' ' . ($this->n + 1) . ' 0 R]');
        } else {
            $this->_put('/ColorSpace /' . $info['cs']);
            if ($info['cs'] === 'DeviceCMYK') {
                $this->_put('/Decode [1 0 1 0 1 0 1 0]');
            }
        }
        $this->_put('/BitsPerComponent ' . $info['bpc']);
        if (isset($info['f'])) {
            $this->_put('/Filter /' . $info['f']);
        }
        if (isset($info['dp'])) {
            $this->_put('/DecodeParms <<' . $info['dp'] . '>>');
        }
        if (isset($info['trns']) && is_array($info['trns'])) {
            $trns = '';
            for ($i = 0; $i < count($info['trns']); $i++) {
                $trns .= $info['trns'][$i] . ' ' . $info['trns'][$i] . ' ';
            }
            $this->_put('/Mask [' . $trns . ']');
        }
        if (isset($info['smask'])) {
            $this->_put('/SMask ' . ($this->n + 1) . ' 0 R');
        }
        $this->_put('/Length ' . strlen($info['data']) . '>>');
        $this->_putstream($info['data']);
        $this->_put('endobj');

        if (isset($info['smask'])) {
            $dp = '/Predictor 15 /Colors 1 /BitsPerComponent 8 /Columns ' . $info['w'];
            $smask = ['w' => $info['w'], 'h' => $info['h'], 'cs' => 'DeviceGray', 'bpc' => 8, 'f' => $info['f'], 'dp' => $dp, 'data' => $info['smask']];
            $this->_putimage($smask);
        }
        if ($info['cs'] === 'Indexed') {
            $this->_newobj();
            $pal = $this->compress ? gzcompress($info['pal']) : $info['pal'];
            $this->_put('<<' . ($this->compress ? '/Filter /FlateDecode ' : '') . '/Length ' . strlen($pal) . '>>');
            $this->_putstream($pal);
            $this->_put('endobj');
        }
    }

    protected function _putinfo(): void
    {
        $this->_newobj();
        $this->_put('<<');
        $this->_put('/Producer ' . $this->_textstring('FPDF ' . FPDF_VERSION . ' - SisFrota Municipal'));
        if (!empty($this->metadata['Title'])) {
            $this->_put('/Title ' . $this->_textstring($this->metadata['Title']));
        }
        if (!empty($this->metadata['Subject'])) {
            $this->_put('/Subject ' . $this->_textstring($this->metadata['Subject']));
        }
        if (!empty($this->metadata['Author'])) {
            $this->_put('/Author ' . $this->_textstring($this->metadata['Author']));
        }
        if (!empty($this->metadata['Keywords'])) {
            $this->_put('/Keywords ' . $this->_textstring($this->metadata['Keywords']));
        }
        if (!empty($this->metadata['Creator'])) {
            $this->_put('/Creator ' . $this->_textstring($this->metadata['Creator']));
        }
        $this->_put('/CreationDate ' . $this->_textstring('D:' . @date('YmdHis')));
        $this->_put('>>');
        $this->_put('endobj');
    }

    protected function _putcatalog(): void
    {
        $this->_newobj();
        $this->_put('<</Type /Catalog');
        $this->_put('/Pages 1 0 R');
        if ($this->ZoomMode === 'fullpage') {
            $this->_put('/OpenAction [3 0 R /Fit]');
        } elseif ($this->ZoomMode === 'fullwidth') {
            $this->_put('/OpenAction [3 0 R /FitH null]');
        } elseif ($this->ZoomMode === 'real') {
            $this->_put('/OpenAction [3 0 R /XYZ null null 1]');
        } elseif (!is_string($this->ZoomMode)) {
            $this->_put('/OpenAction [3 0 R /XYZ null null ' . sprintf('%.2F', $this->ZoomMode / 100) . ']');
        }
        if ($this->LayoutMode === 'single') {
            $this->_put('/PageLayout /SinglePage');
        } elseif ($this->LayoutMode === 'continuous') {
            $this->_put('/PageLayout /OneColumn');
        } elseif ($this->LayoutMode === 'two') {
            $this->_put('/PageLayout /TwoColumnLeft');
        }
        $this->_put('>>');
        $this->_put('endobj');
    }

    protected function _textstring(string $s): string
    {
        if ($this->_isutf8($s)) {
            $s = $this->_UTF8toUTF16($s);
        }
        return '(' . $this->_escape($s) . ')';
    }

    protected function _isutf8(string $s): bool
    {
        return (bool) preg_match('//u', $s);
    }

    protected function _escape(string $s): string
    {
        return str_replace(['\\', '(', ')', "\r"], ['\\\\', '\\(', '\\)', '\\r'], $s);
    }

    protected function _putstream(string $data): void
    {
        $this->_put('stream');
        $this->_put($data);
        $this->_put('endstream');
    }

    protected function _newobj(): void
    {
        $this->n++;
        $this->offsets[$this->n] = strlen($this->buffer);
        $this->_put($this->n . ' 0 obj');
    }
}
