<?php

namespace App\Warehouse;

use JsonSerializable;
use OutOfBoundsException;

class ProductList implements JsonSerializable
{
    /**
     * @var Product[]
     */
    private ?array $products;

    public function __construct(?array $products)
    {
        $this->products = $products ?: [];
    }

    public function getProducts(): array
    {
        return $this->products;
    }

    public function add(Product $product): void
    {
        $this->products[$product->id()] = $product;
    }

    public function remove(Product $product): void
    {
        unset($this->products[$product->id()]);
    }

    public function getProductById(int $id): Product
    {
        if (!isset($this->products[$id])) {
            return $this->products[$id];
        }
        throw new OutOfBoundsException("Product with id $id does not exist");
    }

    public function jsonSerialize()
    {
        return array_values($this->products);
    }
}