<?php
/**
 * SisFrota Municipal - View: Erro 403 Forbidden
 */
defined('SYS_ENTRY') or die('Acesso direto negado.');

http_response_code(403);
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Negado - SisFrota Municipal</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/custom.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #f8fafc; }
        .error-card { text-align: center; max-width: 500px; background: #fff; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 3rem 2rem; }
        .error-code { font-size: 5rem; font-weight: 800; color: #ef4444; line-height: 1; }
        .error-title { font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 1rem 0 0.5rem; }
        .error-msg { color: #475569; margin-bottom: 2rem; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code">403</div>
        <div class="error-title">Acesso Negado</div>
        <p class="error-msg">Seu perfil de usuário não possui permissão para executar esta ação.</p>
        <a href="<?= $baseUrl ?>/dashboard" class="btn-primary">Voltar ao Dashboard</a>
    </div>
</body>
</html>
