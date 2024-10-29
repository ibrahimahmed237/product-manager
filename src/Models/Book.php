<?php

namespace App\Models;

class Book extends Product
{
    public function getAttribute()
    {
        return $this->weight;
    }

    public function setAttribute($value): void
    {
        $this->weight = $value;
    }
    private $weight;

    public function __construct()
    {
        $this->type = 'Book';
    }
    public function getSpecificAttribute(): string
    {
        return $this->weight;
    }
    public function setSpecificAttribute($weight): void
    {
        $this->weight = $weight;
    }

    public function getSpecificValue()
    {
        return $this->weight;
    }
}
