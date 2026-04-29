<?php
class Config
{
    public static function db(): array
    {
        return [
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'port' => getenv('DB_PORT') ?: '3306',
            'name' => getenv('DB_NAME') ?: 'taratib',
            'user' => getenv('DB_USER') ?: 'root',
            'pass' => getenv('DB_PASS') ?: '',
            'charset' => 'utf8mb4',
        ];
    }

    public static function appName(): string
    {
        return 'Taratib';
    }

    public static function roles(): array
    {
        return ['admin', 'haifa', 'khaoula', 'mohamed', 'viewer'];
    }
}
