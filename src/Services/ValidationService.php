<?php
namespace App\Services;

use App\Exceptions\ProductException;

class ValidationService
{
    private const REQUIRED_FIELDS = ['sku', 'name', 'price', 'type'];
    private const VALID_PRODUCT_TYPES = ['DVD', 'Book', 'Furniture'];

    public function validateProductData(array $data): void
    {
        $this->validateRequired($data);
        $this->validateTypes($data);
        $this->validateSpecificAttributes($data);
    }

    private function validateRequired(array $data): void
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (empty($data[$field])) {
                throw new ProductException("Please submit required data: {$field} is missing");
            }
        }
    }

    private function validateTypes(array $data): void
    {
        if (!preg_match('/^[A-Za-z0-9-]+$/', $data['sku'])) {
            throw ProductException::invalidSku();
        }

        if (!is_numeric($data['price']) || $data['price'] <= 0) {
            throw ProductException::invalidPrice();
        }

        if (!in_array($data['type'], self::VALID_PRODUCT_TYPES)) {
            throw new ProductException(message: "Invalid product type");
        }
    }

    private function validateSpecificAttributes(array $data): void
    {
        match ($data['type']) {
            'DVD' => $this->validateSize($data),
            'Book' => $this->validateWeight($data),
            'Furniture' => $this->validateDimensions($data),
            default => throw new ProductException("Unsupported product type: {$data['type']}")
        };
    }

    private function validateSize(array $data): void
    {
        if (!isset($data['size']) || !is_numeric($data['size']) || $data['size'] <= 0) {
            throw new ProductException("Please provide size in MB (positive number)");
        }
    }

    private function validateWeight(array $data): void
    {
        if (!isset($data['weight']) || !is_numeric($data['weight']) || $data['weight'] <= 0) {
            throw new ProductException("Please provide weight in KG (positive number)");
        }
    }

    private function validateDimensions(array $data): void
    {
        $dimensions = ['height', 'width', 'length'];
        foreach ($dimensions as $dim) {
            if (!isset($data[$dim]) || !is_numeric($data[$dim]) || $data[$dim] <= 0) {
                throw new ProductException("Please provide dimensions (HxWxL) (positive numbers)");
            }
        }
    }
}
