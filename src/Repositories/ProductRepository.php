<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\ProductFactory;
use PDO;
use RuntimeException;

class ProductRepository implements ProductRepositoryInterface
{

    private PDO $db;
    private ProductFactory $productFactory;

    public function __construct(ProductFactory $productFactory)
    {
        $this->db = Database::getConnection();
        $this->productFactory = $productFactory;
    }

    public function getAll(): array
    {
        $stm = $this->db->query("SELECT * FROM products ORDER BY id ASC;");
        $results = $stm->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(function($row) {
            return array_filter($row, function($value) {
                return $value !== null;
            });
        }, $results);
    }

    public function findById(int $id): ?Product
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->createProductFromData($data) : null;
    }
    public function findBySku(string $sku): ?Product
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE sku = :sku");
        $stmt->execute(['sku' => $sku]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->createProductFromData($data) : null;
    }

    public function massDelete(array $ids): int
    {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $sql = "DELETE FROM products WHERE id IN ($placeholders)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($ids);

        return $stmt->rowCount();
    }

    public function exists(string $sku): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM products WHERE sku = :sku");
        $stmt->execute(['sku' => $sku]);
        return (bool)$stmt->fetch(PDO::FETCH_COLUMN);
    }

    public function save(Product $product): int
    {
        $sql = "INSERT INTO products (
            sku, name, price, type, 
            size, weight, height, width, length
        ) VALUES (
            :sku, :name, :price, :type,
            :size, :weight, :height, :width, :length
        )";

        $stmt = $this->db->prepare($sql);

        $params = [
            'sku' => $product->getSku(),
            'name' => $product->getName(),
            'price' => $product->getPrice(),
            'type' => $product->getType(),
            'size' => null,
            'weight' => null,
            'height' => null,
            'width' => null,
            'length' => null
        ];

        match ($product->getType()) {
            'DVD' => $params['size'] = $product->getSpecificValue(),
            'Book' => $params['weight'] = $product->getSpecificValue(),
            'Furniture' => [
                $params['height'] = $product->getSpecificValue()['height'],
                $params['width'] = $product->getSpecificValue()['width'],
                $params['length'] = $product->getSpecificValue()['length']
            ],
            default => throw new RuntimeException("Unsupported product type: {$product->getType()}")
        };

        $stmt->execute($params);
        return (int)$this->db->lastInsertId();
    }
    private function createProductFromData(array $data): ?Product
    {
        return $this->productFactory->createFromDatabase($data);
    }
}
