<?php

namespace App\Exceptions;

class ProductException extends \Exception
{
    public static function invalidSku(): self
    {
        return new self('Invalid SKU format');
    }

    public static function invalidPrice(): self
    {
        return new self('Price must be a positive number');
    }

    public static function duplicateSku(string $sku): self
    {
        return new self("Product with SKU '{$sku}' already exists");
    }

    public static function notFound(string $sku): self
    {
        return new self("Product with SKU '{$sku}' not found");
    }

    public static function invalidType(string $type): self
    {
        return new self("Invalid product type: {$type}");
    }
}
