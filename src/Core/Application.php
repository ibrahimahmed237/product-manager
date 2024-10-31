<?php

namespace App\Core;

use App\Config\Environment;
use App\Controllers\ProductController;
use App\Exceptions\Handler;
use App\Repositories\ProductRepository;
use App\Services\ProductFactory;
use App\Services\ValidationService;

class Application
{
    private Router $router;
    private ProductController $productController;

    public function __construct()
    {
        $this->initializeApplication();
        $this->setupController();
        $this->setupRouter();
    }

    public function run(): void
    {
        try {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $this->router->dispatch($_SERVER['REQUEST_METHOD'], $uri);
        } catch (\Throwable $e) {
            Handler::handle($e);
        }
    }

    private function initializeApplication(): void
    {
        // Load environment variables
        Environment::load();

        // Set error handling
        error_reporting(E_ALL);
        ini_set('display_errors', $_ENV['APP_DEBUG'] ? '1' : '0');
        set_exception_handler([Handler::class, 'handle']);

        // Initialize router
        $this->router = new Router();
    }

    private function setupController(): void
    {
        // Set up dependencies
        $validationService = new ValidationService();
        $productFactory = new ProductFactory($validationService);
        $productRepository = new ProductRepository($productFactory);

        // Initialize controller
        $this->productController = new ProductController(
            $productRepository,
            $validationService,
            $productFactory
        );
    }

    private function setupRouter(): void
    {
        // Add CORS middleware
        $this->router->addMiddleware(function() {
            header('Access-Control-Allow-Origin: ' . $_ENV['CORS_ALLOWED_ORIGINS']);
            header('Content-Type: application/json; charset=utf-8');
            header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
        });

        // Define routes
        $this->router
            ->get('products', [$this->productController, 'index'])
            ->post('products', [$this->productController, 'create'])
            ->delete('mass-delete', [$this->productController, 'massDelete']);
    }
}
