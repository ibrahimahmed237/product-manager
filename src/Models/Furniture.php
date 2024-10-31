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
        [$this->height, $this->width, $this->length] = [$dimensions['height'], $dimensions['width'], $dimensions['length']];
    }

    public function getSpecificAttribute(): string
    {
        return "Dimensions: {$this->height}x{$this->width}x{$this->length}";
    }

    public function getSpecificValue()
    {
        return [
            'height' => $this->height,
            'width' => $this->width,
            'length' => $this->length
        ];
    }
}
