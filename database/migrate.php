<?php
/**
 * SisFrota Municipal - Executor CLI de Migrations de Banco de Dados
 * Uso: php database/migrate.php
 */

define('SYS_ENTRY', true);

require_once __DIR__ . '/../app/models/Database.php';

try {
    echo "=== SisFrota Municipal - Execução de Migrations ===\n";

    $pdo = Database::getPdo();

    // 1. Garantir que a tabela de controle de migrations exista
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `migrations` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `migration` VARCHAR(255) NOT NULL,
            `executed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 2. Buscar migrations já executadas
    $stmt = $pdo->query("SELECT `migration` FROM `migrations`");
    $executedMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

    // 3. Buscar arquivos de migration disponíveis na pasta migrations/
    $migrationsDir = __DIR__ . '/migrations';
    if (!is_dir($migrationsDir)) {
        throw new Exception("Diretório de migrations não encontrado: {$migrationsDir}");
    }

    $files = glob($migrationsDir . '/*.sql');
    sort($files); // Ordena em ordem numérica/alfabética

    $pendingCount = 0;

    foreach ($files as $filePath) {
        $filename = basename($filePath);

        if (in_array($filename, $executedMigrations, true)) {
            continue;
        }

        $pendingCount++;
        echo "Executando migration: {$filename}... ";

        $sqlContent = file_get_contents($filePath);
        if ($sqlContent === false || trim($sqlContent) === '') {
            echo "[ALERTA] Arquivo vazio ou ilegível.\n";
            continue;
        }

        try {
            // Executa o script SQL da migration
            $pdo->exec($sqlContent);

            // Registra a migration como executada na tabela de controle
            $insertStmt = $pdo->prepare("INSERT INTO `migrations` (`migration`) VALUES (:migration)");
            $insertStmt->execute([':migration' => $filename]);

            echo "[SUCESSO]\n";
        } catch (Throwable $e) {
            echo "[ERRO]\n";
            throw new Exception("Erro ao aplicar a migration {$filename}: " . $e->getMessage(), 0, $e);
        }
    }

    if ($pendingCount === 0) {
        echo "[INFO] Nenhuma migration pendente. O banco de dados está atualizado.\n";
    } else {
        echo "=== [CONCLUÍDO] Total de {$pendingCount} migration(s) aplicada(s) com sucesso. ===\n";
    }

    exit(0);

} catch (Throwable $e) {
    echo "\n[FALHA CRÍTICA] " . $e->getMessage() . "\n";
    exit(1);
}
