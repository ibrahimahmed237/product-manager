<?php

namespace App\Models;

use App\Models;

class DVD extends Product
{
    public function getAttribute(): string
    {
        return $this->getSpecificAttribute();
    }

    public function setAttribute($value): void
    {
        $this->setSpecificAttribute($value);
    }

    private float $size;

    public function __construct()
    {
        $this->type = 'DVD';
    }

    public function setSpecificAttribute($size): void
    {
        $this->size = (float)$size;
    }

    public function getSpecificAttribute(): string
    {
        return "Size: {$this->size} MB";
    }

    public function getSpecificValue()
    {
        return $this->size;
    }
}
