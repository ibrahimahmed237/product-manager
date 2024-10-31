<?php

namespace App\Services;

use App\Models\Book;
use App\Models\DVD;
use App\Models\Furniture;
use App\Models\Product;
use InvalidArgumentException;

class ProductFactory
{
    private ValidationService $validationService;
    public function __construct(ValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    public function createProduct(array $data): Product
    {
        // Validate the basic product data
        $this->validationService->validateProductData($data);
        $product = $this->createProductInstance($data['type']);

        // Set common attributes
        $product->setSku($data['sku']);
        $product->setName($data['name']);
        $product->setPrice($data['price']);

        // Set type-specific attributes
        $this->setSpecificAttributes($product, $data);

        return $product;
    }

    private function createProductInstance(string $type): Product
    {
        return match ($type) {
            'DVD' => new DVD(),
            'Book' => new Book(),
            'Furniture' => new Furniture(),
            default => throw new InvalidArgumentException("Invalid product type: $type")
        };
    }

    private function setSpecificAttributes(Product $product, array $data): void
    {
        match ($product->getType()) {
            'DVD' => $this->setDVDSpecificAttributes($product, $data),
            'Book' => $this->setBookSpecificAttributes($product, $data),
            'Furniture' => $this->setFurnitureSpecificAttributes($product, $data),
            default => throw new InvalidArgumentException("Unsupported product type: {$product->getType()}")
        };
    }

    private function setDVDSpecificAttributes(DVD $product, array $data): void
    {
        if (!isset($data['size']) || !is_numeric($data['size']) || $data['size'] <= 0) {
            throw new InvalidArgumentException('DVD size must be a positive number');
        }
        $product->setSpecificAttribute((float)$data['size']);
    }

    private function setBookSpecificAttributes(Book $product, array $data): void
    {
        if (isset($data['weight']) && !is_numeric($data['weight']) || $data['weight'] <= 0) {
            throw new InvalidArgumentException('Book weight must be a positive number');
        }
        $product->setSpecificAttribute((float)$data['weight']);
    }

    private function setFurnitureSpecificAttributes(Furniture $product, array $data): void
    {
        $requiredDimensions = ['height', 'width', 'length'];
        foreach ($requiredDimensions as $dimension) {
            if (!isset($data[$dimension]) || !is_numeric($data[$dimension]) || $data[$dimension] <= 0) {
                throw new InvalidArgumentException("Furniture $dimension must be a positive number");
            }
        }
        $product->setSpecificAttribute([
            'height' => (float)$data['height'],
            'width' => (float)$data['width'],
            'length' => (float)$data['length']
        ]);
    }

    public function createFromDatabase(array $data): Product
    {
        $product = $this->createProductInstance($data['type']);

        // Set common attributes
        $product->setId((int)$data['id']);
        $product->setSku($data['sku']);
        $product->setName($data['name']);
        $product->setPrice((float)$data['price']);

        // Set specific attributes based on type
        match ($data['type']) {
            'DVD' => $product->setSpecificAttribute((float)$data['size']),
            'Book' => $product->setSpecificAttribute((float)$data['weight']),
            'Furniture' => $product->setSpecificAttribute([
                (float)$data['height'],
                (float)$data['width'],
                (float)$data['length']
            ]),
            default => throw new InvalidArgumentException("Invalid product type: {$data['type']}")
        };

        return $product;
    }
}
