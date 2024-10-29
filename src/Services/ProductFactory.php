<?php

namespace App\Services;

use App\Models\Book;
use App\Models\DVD;
use App\Models\Furniture;
use App\Models\Product;
use Exception;
class ProductFactory
{
    public static function createProduct(string $type): Product
    {
        return match ($type) {
            'DVD' => new DVD(),
            'Book' => new Book(),
            'Furniture' => new Furniture(),
            default => throw new Exception("Invalid product type: $type")
        };
    }
}
