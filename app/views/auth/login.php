<?php
/**
 * SisFrota Municipal - View: Tela de Login
 */
defined('SYS_ENTRY') or die('Acesso direto negado.');

$appName = htmlspecialchars($config['app']['name'] ?? 'SisFrota Municipal', ENT_QUOTES, 'UTF-8');
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - <?= $appName ?></title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/custom.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
    </style>
<link rel="icon" type="image/png" href="<?= $baseUrl ?>/docs/logoSF.png">
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <img src="<?= $baseUrl ?>/assets/img/logo.png"
                 alt="Prefeitura Municipal"
                 onerror="this.style.display='none'">
            <h1 class="login-title"><?= $appName ?></h1>
            <p class="login-subtitle">Sistema de Gestão de Frota Municipal</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= $baseUrl ?>/login" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-group">
                <label class="form-label" for="username">Nome de Usuário</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    class="form-control"
                    autocomplete="username"
                    value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    required
                    autofocus
                    placeholder="Digite seu usuário"
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Senha</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    autocomplete="current-password"
                    required
                    placeholder="Digite sua senha"
                >
            </div>

            <div class="form-group" style="margin-top: 1.75rem;">
                <button
                    type="submit"
                    class="btn-primary"
                    style="width: 100%; padding: 0.75rem; font-size: 1rem;"
                    id="btn-login"
                >
                    Entrar no Sistema
                </button>
            </div>
        </form>

        <div style="text-align: center; margin-top: 1rem; font-size: 0.8rem; color: #475569;">
            &copy; <?= date('Y') ?> Prefeitura Municipal &mdash; SisFrota
        </div>
    </div>

    <script>
        // Desabilita botão e mostra indicador de carregamento no envio do formulário
        document.querySelector('form').addEventListener('submit', function(e) {
            const btn = document.getElementById('btn-login');
            btn.disabled = true;
            btn.textContent = 'Aguarde...';
        });
    </script>
</body>
</html>
