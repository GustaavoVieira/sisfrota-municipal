<?php
/**
 * SisFrota Municipal - Helper de Formatação e Utilitários
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

class FormatHelper
{
    /**
     * Limpa a string de placa removendo hífens, espaços e caracteres especiais e convertendo para maiúsculas.
     */
    public static function cleanPlate(?string $plate): string
    {
        if ($plate === null) {
            return '';
        }
        return strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $plate));
    }

    /**
     * Formata uma placa de veículo no padrão tradicional (ABC-1234) ou Mercosul (ABC1D23).
     */
    public static function formatPlate(?string $plate): string
    {
        $clean = self::cleanPlate($plate);

        if (strlen($clean) !== 7) {
            return $clean;
        }

        // Padrão Tradicional: 3 Letras + 4 Números (Ex: ABC1234 -> ABC-1234)
        if (preg_match('/^[A-Z]{3}[0-9]{4}$/', $clean)) {
            return substr($clean, 0, 3) . '-' . substr($clean, 3);
        }

        // Padrão Mercosul: 3 Letras + 1 Número + 1 Letra + 2 Números (Ex: ABC1D23)
        // No padrão Mercosul a representação oficial não possui hífen.
        if (preg_match('/^[A-Z]{3}[0-9]{1}[A-Z]{1}[0-9]{2}$/', $clean)) {
            return $clean;
        }

        return $clean;
    }

    /**
     * Valida se a placa informada obedece ao padrão brasileiro (Tradicional ou Mercosul).
     */
    public static function isValidPlate(?string $plate): bool
    {
        $clean = self::cleanPlate($plate);

        if (strlen($clean) !== 7) {
            return false;
        }

        $tradicional = preg_match('/^[A-Z]{3}[0-9]{4}$/', $clean);
        $mercosul    = preg_match('/^[A-Z]{3}[0-9]{1}[A-Z]{1}[0-9]{2}$/', $clean);

        return (bool) ($tradicional || $mercosul);
    }

    /**
     * Formata um valor numérico para a moeda corrente brasileira (R$ 1.234,56).
     */
    public static function formatCurrency(float|int|string|null $value): string
    {
        $val = (float) ($value ?? 0);
        return 'R$ ' . number_format($val, 2, ',', '.');
    }

    /**
     * Formata um número com o número de casas decimais especificado no padrão brasileiro.
     */
    public static function formatNumber(float|int|string|null $value, int $decimals = 2): string
    {
        $val = (float) ($value ?? 0);
        return number_format($val, $decimals, ',', '.');
    }

    /**
     * Formata uma data no formato ISO (AAAA-MM-DD) para o padrão brasileiro (DD/MM/AAAA).
     */
    public static function formatDate(?string $date): string
    {
        if (empty($date)) {
            return '-';
        }
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return $date;
        }
        return date('d/m/Y', $timestamp);
    }

    /**
     * Retorna o nome do mês e ano em português (ex: "Setembro de 2026").
     */
    public static function formatMonthName(?string $yearMonth): string
    {
        if (empty($yearMonth) || !preg_match('/^(\d{4})-(\d{2})$/', $yearMonth, $m)) {
            return $yearMonth ?? '';
        }

        $meses = [
            '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
            '04' => 'Abril',   '05' => 'Maio',      '06' => 'Junho',
            '07' => 'Julho',   '08' => 'Agosto',    '09' => 'Setembro',
            '10' => 'Outubro', '11' => 'Novembro',  '12' => 'Dezembro'
        ];

        $ano = $m[1];
        $mes = $m[2];

        return ($meses[$mes] ?? $mes) . ' de ' . $ano;
    }

    /**
     * Retorna o nome abreviado do mês em português (ex: "Set").
     */
    public static function getMonthShortName(string|int $month): string
    {
        $m = str_pad((string) (int) $month, 2, '0', STR_PAD_LEFT);
        $short = [
            '01' => 'Jan', '02' => 'Fev', '03' => 'Mar',
            '04' => 'Abr', '05' => 'Mai', '06' => 'Jun',
            '07' => 'Jul', '08' => 'Ago', '09' => 'Set',
            '10' => 'Out', '11' => 'Nov', '12' => 'Dez'
        ];
        return $short[$m] ?? $m;
    }
}

