<?php

namespace App\Config;

use Dotenv\Dotenv;

class Environment
{
    public static function load(): void
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();

        $dotenv->required([
            'APP_NAME',
            'APP_ENV',
            'APP_DEBUG',
            'DB_CONNECTION',
            'DB_HOST',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',
            'CORS_ALLOWED_ORIGINS'
        ]);
    }
}