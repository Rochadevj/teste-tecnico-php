<?php

namespace App;

use PDO;

class Database
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $envFile = __DIR__ . '/../.env';
        $env     = file_exists($envFile) ? parse_ini_file($envFile) : [];

        if (!file_exists($envFile) && getenv('DB_HOST') === false) {
            http_response_code(500);
            echo json_encode(['erro' => 'Arquivo .env nao encontrado. Copie .env.example para .env.']);
            exit;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            self::env($env, 'DB_HOST', 'localhost'),
            self::env($env, 'DB_PORT', '3306'),
            self::env($env, 'DB_NAME', 'brudam_test')
        );

        self::$instance = new PDO($dsn, self::env($env, 'DB_USER', 'root'), self::env($env, 'DB_PASS', ''), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        return self::$instance;
    }

    private static function env(array $env, string $key, string $default): string
    {
        $value = $env[$key] ?? getenv($key);

        if ($value === false || $value === '') {
            return $default;
        }

        return (string) $value;
    }
}
