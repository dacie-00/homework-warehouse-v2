<?php

use Ramsey\Uuid\Uuid;

require_once __DIR__ . "/../vendor/autoload.php";

$products = null;
if (file_exists(__DIR__ . "/products.json")) {
    $products = json_decode(file_get_contents(__DIR__ . "/products.json"), false, JSON_THROW_ON_ERROR);
}

foreach ($products as $product) {
    if (!isset($product->price)) {
        $product->price = 1;
    }
    if (!isset($product->id) || is_int($product->id)) {
        $product->id = Uuid::uuid4()->toString();
    }
    if (!isset($product->expiresAt)) {
        $product->expiresAt = null;
    }
}

file_put_contents(__DIR__ . "/products.json", json_encode($products, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
