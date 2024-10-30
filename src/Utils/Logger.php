<?php

namespace App\Utils;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

class Logger
{
    private static ?MonologLogger $logger = null;

    public static function getLogger(): MonologLogger
    {
        if (self::$logger === null) {
            self::$logger = new MonologLogger($_ENV['APP_NAME']);

            $logFile = dirname(__DIR__, 2) . "/logs/app.log";
            // Ensure the log directory exists
            if (!is_dir(dirname($logFile))) {
                mkdir(dirname($logFile), 0777, true);
            }
            
            if ($_ENV['APP_ENV'] === 'production') {
                self::$logger->pushHandler(new RotatingFileHandler($logFile, 30, MonologLogger::WARNING));
            } else {
                self::$logger->pushHandler(new StreamHandler($logFile, MonologLogger::DEBUG));    
            }
        }

        return self::$logger;
    }
}
