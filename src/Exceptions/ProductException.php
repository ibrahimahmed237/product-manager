<?php
namespace App\Exceptions;

use Exception;

class ProductException extends Exception
{
    public static function invalidSku(): self
    {
        return new self("SKU must be unique");
    }

    public static function invalidPrice(): self
    {
        return new self("Price must be a positive number");
    }

    public static function invalidProduct(): self
    {
        return new self("Invalid product data provided");
    }

    public static function databaseError(string $message): self
    {
        return new self("Database error: {$message}");
    }
}