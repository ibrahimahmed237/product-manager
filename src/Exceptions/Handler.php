<?php

namespace App\Exceptions;

use App\Utils\Logger;
use Throwable;

class Handler
{
    private const HTTP_CODES = [
        'ValidationException' => 422,
        'ProductException' => 422,
        'InvalidArgumentException' => 400,
        'PDOException' => 500,
        'RuntimeException' => 500
    ];

    private const ERROR_MESSAGES = [
        'PDOException' => [
            'production' => 'Database error occurred',
            'development' => null // will use actual message
        ],
        'RuntimeException' => [
            'production' => 'Internal server error',
            'development' => null
        ]
    ];

    /**
     * Handles all uncaught exceptions in the application
     */
    public static function handle(Throwable $exception): void
    {
        try {
            self::logException($exception);
            self::sendResponse($exception);
        } catch (Throwable $e) {
            // Fallback error handling if something goes wrong in the handler itself
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Critical error occurred'
            ]);
        }
    }

    /**
     * Registers all error handlers
     */
    public static function register(): void
    {
        error_reporting(E_ALL);
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handle']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Converts PHP errors to exceptions
     */
    public static function handleError(
        int $level,
        string $message,
        string $file = '',
        int $line = 0
    ): bool {
        if (error_reporting() & $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
        return true;
    }

    /**
     * Handles fatal errors
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            self::handle(new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            ));
        }
    }

    /**
     * Logs the exception
     */
    private static function logException(Throwable $exception): void
    {
        $logger = Logger::getLogger();

        $logMessage = sprintf(
            "[%s] %s: %s in %s:%d\nStack trace:\n%s\n",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
        $logger->error($logMessage);
    }

    /**
     * Sends JSON response for the exception
     */
    private static function sendResponse(Throwable $exception): void
    {
        $statusCode = self::getStatusCode($exception);
        $response = self::formatResponse($exception, $statusCode);

        self::clearOutput();
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
    }

    /**
     * Gets appropriate HTTP status code for the exception
     */
    private static function getStatusCode(Throwable $exception): int
    {
        $exceptionClass = get_class($exception);
        return self::HTTP_CODES[$exceptionClass] ?? 500;
    }

    /**
     * Formats the response based on environment
     */
    private static function formatResponse(Throwable $exception, int $statusCode): array
    {
        $isProduction = ($_ENV['APP_ENV'] ?? 'production') === 'production';
        $exceptionClass = get_class($exception);

        $response = [
            'status' => 'error',
            'message' => self::getErrorMessage($exception, $isProduction),
            'code' => $statusCode
        ];

        if (!$isProduction) {
            $response['debug'] = [
                'exception' => $exceptionClass,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ];
        }

        return $response;
    }

    /**
     * Gets appropriate error message based on environment
     */
    private static function getErrorMessage(Throwable $exception, bool $isProduction): string
    {
        $exceptionClass = get_class($exception);
        
        if (isset(self::ERROR_MESSAGES[$exceptionClass])) {
            return $isProduction 
                ? self::ERROR_MESSAGES[$exceptionClass]['production']
                : ($exception->getMessage() ?: self::ERROR_MESSAGES[$exceptionClass]['production']);
        }

        return $exception->getMessage();
    }

    /**
     * Clears any output buffers
     */
    private static function clearOutput(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }
}