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
     * Formata data e hora (AAAA-MM-DD HH:MM:SS) para o padrão brasileiro (DD/MM/AAAA HH:MM).
     */
    public static function formatDateTime(?string $datetime): string
    {
        if (empty($datetime)) {
            return '-';
        }
        $timestamp = strtotime($datetime);
        if ($timestamp === false) {
            return $datetime;
        }
        return date('d/m/Y H:i', $timestamp);
    }
}
