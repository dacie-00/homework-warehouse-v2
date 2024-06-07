<?php
declare(strict_types=1);

use App\Ask;
use App\Warehouse\ProductListDisplay;
use App\Warehouse\Product;
use App\Warehouse\ProductList;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

require_once "vendor/autoload.php";

function load(string $fileName): ?array
{
    if (file_exists(__DIR__ . "$fileName.json")) {
        return json_decode(
            file_get_contents(__DIR__ . "$fileName.json"),
            false,
            512,
            JSON_THROW_ON_ERROR);
    }
    return null;
}

function save(JsonSerializable $serializable, string $fileName): void
{
    $serializable = json_encode($serializable, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    file_put_contents(__DIR__ . "$fileName.json", $serializable);
}

function validateLogin(string $username, string $password, array $users): bool
{
    foreach ($users as $user) {
        if ($user->username === $username && password_verify($password, $user->password)) {
            return true;
        }
    }
    return false;
}

$input = new ArgvInput();
$output = new ConsoleOutput();

$ask = new Ask($input, $output);
$warehouse = new ProductList(load("/storage/products"));
$warehouseDisplay = new ProductListDisplay($output);

$logger = new Logger("logger");
$logger->pushHandler(new StreamHandler(__DIR__ . "/storage/products.log"));

$users = load("/storage/users");
while (true) {
    [$username, $password] = $ask->login();
    if (validateLogin($username, $password, $users)) {
        break;
    }
    echo "Incorrect username or password!\n";
}

echo "Welcome, $username!\n";
$logger->info("$username logged into the database");
while (true) {
    $isWarehouseEmpty = count($warehouse->products()) === 0;
    if ($isWarehouseEmpty) {
        echo "The warehouse is empty!\n";
    } else {
        $warehouseDisplay->display($warehouse->products());
    }

    $mainAction = $ask->mainAction();
    if ($isWarehouseEmpty && in_array($mainAction, [
            Ask::DELETE_PRODUCT,
            Ask::WITHDRAW_FROM_PRODUCT,
            Ask::ADD_TO_PRODUCT,
        ], true)) {
        echo "You cannot do this as there are no products in the warehouse!\n";
        continue;
    }
    switch ($mainAction) {
        case Ask::ADD_NEW_PRODUCT:
            [$name, $quantity, $price] = $ask->productInfo();
            $warehouse->add(new Product($name, $quantity, $price));

            $logger->info("$username added the product $name to warehouse");
            save($warehouse, "/storage/products");
            break;
        case Ask::DELETE_PRODUCT:
            $product = $warehouse->getProductById($ask->product($warehouse->products()));
            $warehouse->delete($product);

            $logger->info("$username deleted the product {$product->name()} from warehouse");
            save($warehouse, "/storage/products");
            break;
        case ASK::ADD_TO_PRODUCT:
            $product = $warehouse->getProductById($ask->product($warehouse->products()));
            $quantity = $ask->quantity(1);
            $product->update(["quantity" => $product->quantity() + $quantity]);

            $logger->info("$username added $quantity to the {$product->name()} stock");
            save($warehouse, "/storage/products");
            break;
        case ASK::WITHDRAW_FROM_PRODUCT:
            $product = $warehouse->getProductById($ask->product($warehouse->products()));
            if ($product->quantity() === 0) {
                echo "You cannot withdraw any of this product, as there is 0 of it in stock!\n";
                continue 2;
            }
            $quantity = $ask->quantity(1, $product->quantity());
            $product->update(["quantity" => $product->quantity() + $quantity]);

            $logger->info("$username removed $quantity from the {$product->name()} stock");
            save($warehouse, "/storage/products");
            break;
        case ASK::UPDATE:
            $product = $warehouse->getProductById($ask->product($warehouse->products()));
            $property = $ask->property();
            $product->update($property);
            $propertyName = array_keys($property)[0];
            $logger->info(
                "$username changed the property '$propertyName' value " .
                "to {$property[$propertyName]} for {$product->name()}");
            save($warehouse, "/storage/products");
            break;
        case ASK::GET_REPORT:
            $productCount = 0;
            $productValue = 0;
            foreach($warehouse->products() as $product) {
                $productCount += $product->quantity();
                $productValue += $product->quantity() * $product->price();
            }
            echo "The total amount of products is $productCount and the total sum is $productValue$\n";
            break;
        case Ask::EXIT:
            exit;
    }
}
