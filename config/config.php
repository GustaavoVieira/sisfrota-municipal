<?php
/**
 * SisFrota Municipal - Arquivo de Configuração Principal
 * Importante: Este arquivo é compilado diretamente no PHP (sem uso de .env).
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

return [
    'db' => [
        'host'     => 'localhost',
        'dbname'   => 'sisfrota_db',
        'username' => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
        'port'     => 3306
    ],
    'app' => [
        'name'             => 'SisFrota Municipal',
        'url'              => 'http://localhost/Projeto%20Prefeitura',
        'timezone'         => 'America/Sao_Paulo',
        'session_lifetime' => 1800,
        'debug'            => false
    ],
    'logs' => [
        'enabled' => true,
        'path'    => __DIR__ . '/../logs/system_error.log'
    ]
];
