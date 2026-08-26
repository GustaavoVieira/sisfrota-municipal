<?php
/**
 * SisFrota Municipal - Layout: Header (topo do conteúdo principal)
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
    <title><?= htmlspecialchars($pageTitle ?? 'Painel', ENT_QUOTES, 'UTF-8') ?> - <?= $appName ?></title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/custom.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="app-container">
    <!-- Sidebar incluída via layouts/sidebar.php -->
    <?php require_once VIEWS_PATH . '/layouts/sidebar.php'; ?>

    <!-- Área Principal de Conteúdo -->
    <div class="app-main">
        <header class="app-header">
            <h1 class="page-title"><?= htmlspecialchars($pageTitle ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
            <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.9rem; color: #475569;">
                <span><?= htmlspecialchars($user['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                <a href="<?= $baseUrl ?>/logout"
                   style="color: #ef4444; text-decoration: none; font-weight: 600;"
                   title="Sair do sistema">Sair</a>
            </div>
        </header>

        <main class="app-content">
