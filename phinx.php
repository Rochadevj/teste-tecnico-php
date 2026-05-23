<?php

$envFile = __DIR__ . '/.env';
$env     = file_exists($envFile) ? parse_ini_file($envFile) : [];
$value   = static function (string $key, string $default) use ($env): string {
    $value = $env[$key] ?? getenv($key);

    if ($value === false || $value === '') {
        return $default;
    }

    return (string) $value;
};

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
            'host'    => $value('DB_HOST', 'localhost'),
            'name'    => $value('DB_NAME', 'brudam_test'),
            'user'    => $value('DB_USER', 'root'),
            'pass'    => $value('DB_PASS', ''),
            'port'    => (int) $value('DB_PORT', '3306'),
            'charset' => 'utf8mb4',
        ],
    ],
    'version_order' => 'creation',
];
