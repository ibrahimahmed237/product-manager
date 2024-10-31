<?php
namespace App\Repositories\Interfaces;

use App\Models\Product;

interface ProductRepositoryInterface
{
    public function getAll(): array;
    public function findById(int $id): ?Product;
    public function findBySku(string $sku): ?Product;
    public function save(Product $product): int;
    public function massDelete(array $ids): int;
    public function exists(string $sku): bool;
}
