<?php

use App\Config\Environment;
use App\Controllers\ProductController;
use App\Exceptions\Handler;
use PHP_CodeSniffer\Tokenizers\PHP;

require_once dirname(__DIR__) . './vendor/autoload.php';


// Load environment variables
Environment::load();

// Enable error handling
error_reporting(E_ALL);
ini_set('display_errors', $_ENV['APP_DEBUG'] ? '1' : '0');

// Set up Cors
header('Access-Control-Allow-Origin: ' . $_ENV['CORS_ALLOWED_ORIGINS']);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Error handling
set_exception_handler([Handler::class, 'handle']);

// Route Handling
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));

$controller = new ProductController();

try {
    match ($_SERVER['REQUEST_METHOD']) {
        'GET' => $controller->index(),

        'POST' => match (end($uri)) {
            'products' => $controller->store(),
            default => throw new RuntimeException('Not Found', 404),
        },

        'DELETE' => match (end($uri)) {
            'mass-delete' => $controller->massDelete(),
            default => throw new RuntimeException('Not Found', 404),
        },

        default => throw new RuntimeException('Not Found', 404),
    };
} catch (Throwable $e) {
    Handler::handle($e);
}
