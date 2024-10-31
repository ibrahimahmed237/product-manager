<?php

namespace App\Controllers;

use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\ProductFactory;
use App\Services\ValidationService;
use App\Exceptions\ProductException;
use App\Exceptions\Handler;

class ProductController
{
    private productRepositoryInterface $productRepository;
    private ValidationService $validationService;
    private ProductFactory $productFactory;

    public function __construct(ProductRepositoryInterface $productRepository, ValidationService $validationService, ProductFactory $productFactory)
    {
        $this->productRepository = $productRepository;
        $this->validationService = $validationService;
        $this->productFactory = $productFactory;
    }

    public function index()
    {
        try {
            $products = $this->productRepository->getAll();
            echo json_encode([
                'status' => 'success',
                'data' => $products
            ]);
        } catch (\Throwable $e) {
            Handler::handle($e);
        }
    }

    public function create()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data) {
                throw new ProductException('Invalid input data');
            }
            
            // validate the input data
            $this->validationService->validateProductData($data);

            // Check if SKU exists
            if ($this->productRepository->exists($data['sku'])) {
                throw new ProductException('SKU already exists');
            }

            $product = $this->productFactory->createProduct($data);
            $id = $this->productRepository->save($product);

            echo json_encode([
                'status' => 'success',
                'id' => $id
            ]);
        } catch (\Throwable $e) {
            Handler::handle($e);
        }
    }

    public function massDelete(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['ids']) || !is_array($data['ids'])) {
                throw new ProductException('Invalid input: ids array required');
            }

            $deletedCount = $this->productRepository->massDelete($data['ids']);

            echo json_encode([
                'status' => 'success',
                'message' => "$deletedCount products deleted successfully"
            ]);
        } catch (\Throwable $e) {
            Handler::handle($e);
        }
    }
}
