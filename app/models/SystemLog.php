<?php
/**
 * SisFrota Municipal - Model de Log de Sistema
 *
 * Registra eventos de auditoria e erros. Se a conexão ao banco falhar,
 * grava em um arquivo de fallback (logs/system_error.log).
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

class SystemLog
{
    /**
     * Níveis de log suportados.
     */
    public const LEVEL_INFO    = 'INFO';
    public const LEVEL_WARNING = 'WARNING';
    public const LEVEL_ERROR   = 'ERROR';
    public const LEVEL_CRITICAL = 'CRITICAL';

    /**
     * Grava uma entrada de log.
     *
     * @param string $level   Nível do log (INFO, WARNING, ERROR, CRITICAL).
     * @param string $message Mensagem livre a ser registrada.
     * @param int|null $userId ID do usuário associado (opcional).
     */
    public static function log(string $level, string $message, ?int $userId = null): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $ip        = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $userId    = $userId ?? (AuthHelper::isLoggedIn() ? AuthHelper::user()['id'] : null);

        // Primeiro tenta inserir no banco de dados.
        try {
            $db = Database::getInstance(); // Singleton PDO configurado em app/models/Database.php
            $stmt = $db->prepare(
                "INSERT INTO system_logs (level, message, ip_address, user_id, created_at) VALUES (:level, :msg, :ip, :uid, :ts)"
            );
            $stmt->bindValue(':level', $level);
            $stmt->bindValue(':msg', $message);
            $stmt->bindValue(':ip', $ip);
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':ts', $timestamp);
            $stmt->execute();
            return;
        } catch (Throwable $e) {
            // Em caso de falha, grava no arquivo de fallback.
            $logLine = sprintf(
                "[%s] %s | IP: %s | UserID: %s | %s\n",
                $timestamp,
                strtoupper($level),
                $ip,
                $userId ?? 'NULL',
                $message
            );
            $fallbackPath = __DIR__ . '/../../logs/system_error.log';
            @file_put_contents($fallbackPath, $logLine, FILE_APPEND | LOCK_EX);
        }
    }
}
?>
