<?php
/**
 * SisFrota Municipal - Ponto de Entrada Único (Bootstrap & Router)
 */

// 1. Constante de segurança para verificação nos arquivos incluídos
define('SYS_ENTRY', true);

// 2. Carregamento das configurações globais do projeto
$config = require_once __DIR__ . '/config/config.php';

// 3. Configuração obrigatória do Fuso Horário oficial
date_default_timezone_set($config['app']['timezone'] ?? 'America/Sao_Paulo');

// 4. Definição de constantes de caminhos do sistema
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEWS_PATH', APP_PATH . '/views');

// Bootstrap básico da Fase 1 - Infraestrutura Inicial Pronta
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($config['app']['name']); ?> - Base de Infraestrutura</title>
    <style>
        body { font-family: sans-serif; background: #f8fafc; color: #0f172a; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: #ffffff; padding: 2rem; border-radius: 0.5rem; border: 1px solid #e2e8f0; max-width: 500px; text-align: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .badge { background: #16a34a; color: white; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <img src="assets/img/logo.png" alt="Prefeitura Municipal" style="max-height: 80px; margin-bottom: 1rem;" onerror="this.style.display='none'">
        <h2><?php echo htmlspecialchars($config['app']['name']); ?></h2>
        <p><span class="badge">Fase 1 Concluída</span></p>
        <p>Infraestrutura inicial, segurança de diretórios e fuso horário <code><?php echo date_default_timezone_get(); ?></code> configurados com sucesso.</p>
    </div>
</body>
</html>
