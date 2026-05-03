<?php

$envFile = __DIR__ . '/.env';
$env     = file_exists($envFile) ? parse_ini_file($envFile) : [];

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds'      => '%%PHINX_CONFIG_DIR%%/db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinx_log',
        'default_environment'     => 'development',
        'development' => [
            'adapter' => 'mysql',
            'host'    => $env['DB_HOST'] ?? 'localhost',
            'name'    => $env['DB_NAME'] ?? 'brudam_test',
            'user'    => $env['DB_USER'] ?? 'root',
            'pass'    => $env['DB_PASS'] ?? '',
            'port'    => (int) ($env['DB_PORT'] ?? 3306),
            'charset' => 'utf8mb4',
        ],
    ],
    'version_order' => 'creation',
];
