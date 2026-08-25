<?php
/**
 * SisFrota Municipal - Conexão Singleton PDO com Banco de Dados
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

class Database
{
    private static ?Database $instance = null;
    private ?PDO $pdo = null;

    /**
     * Construtor privado para impedir instanciação direta.
     * Carrega as configurações de config/config.php e estabelece a conexão PDO.
     */
    private function __construct()
    {
        $configFile = __DIR__ . '/../../config/config.php';
        
        if (!file_exists($configFile)) {
            throw new Exception("Arquivo de configuração não encontrado em: {$configFile}");
        }

        $config = require $configFile;

        if (!isset($config['db'])) {
            throw new Exception("Configurações de banco de dados não encontradas no arquivo de configuração.");
        }

        $dbConfig = $config['db'];
        $host     = $dbConfig['host'] ?? 'localhost';
        $dbname   = $dbConfig['dbname'] ?? 'sisfrota_db';
        $username = $dbConfig['username'] ?? 'root';
        $password = $dbConfig['password'] ?? '';
        $charset  = $dbConfig['charset'] ?? 'utf8mb4';
        $port     = (int)($dbConfig['port'] ?? 3306);

        try {
            // Garante que o banco de dados exista antes de conectar diretamente a ele
            $dsnHostOnly = sprintf("mysql:host=%s;port=%d;charset=%s", $host, $port, $charset);
            $tempPdo = new PDO($dsnHostOnly, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

            // Estabelece a conexão final com o banco de dados do sistema
            $dsn = sprintf("mysql:host=%s;port=%d;dbname=%s;charset=%s", $host, $port, $dbname, $charset);
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $this->pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new Exception("Falha na conexão com o banco de dados MySQL: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Retorna a instância única da classe Database.
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retorna o objeto de conexão PDO ativo.
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Atalho estático para obter o objeto PDO diretamente.
     */
    public static function getPdo(): PDO
    {
        return self::getInstance()->getConnection();
    }

    /**
     * Impede a clonagem da instância Singleton.
     */
    private function __clone() {}

    /**
     * Impede a desserialização da instância Singleton.
     */
    public function __wakeup()
    {
        throw new Exception("Não é permitido desserializar uma instância Singleton.");
    }
}
