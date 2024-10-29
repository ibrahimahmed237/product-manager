<?php

namespace App\Models;

use App\Models\Product;

class Furniture extends Product
{
    public function getAttribute()
    {
        // Implement the method as needed
    }

    public function setAttribute($value)
    {
        // Implement the method as needed
    }
    private $height;
    private $width;
    private $length;

    public function __construct()
    {
        $this->type = 'Furniture';
    }

    public function setSpecificAttribute($dimensions): void
    {
        if (is_array($dimensions)) {
            list($this->height, $this->width, $this->length) = $dimensions;
        } else {
            // If dimensions come as string "HxWxL"
            list($this->height, $this->width, $this->length) = explode('x', $dimensions);
        }
    }

    public function getSpecificAttribute(): string
    {
        return "Dimensions: {$this->height}x{$this->width}x{$this->length}";
    }

    public function getSpecificValue()
    {
        return "{$this->height}x{$this->width}x{$this->length}";
    }
}
