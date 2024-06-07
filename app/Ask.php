<?php
declare(strict_types=1);

namespace App;

use App\Warehouse\Product;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use RuntimeException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class Ask
{
    private InputInterface $input;
    private OutputInterface $output;
    private QuestionHelper $helper;

    public const ADD_NEW_PRODUCT = "add new product";
    public const DELETE_PRODUCT = "delete product";
    public const ADD_TO_PRODUCT = "add to product";
    public const WITHDRAW_FROM_PRODUCT = "withdraw from product";
    public const UPDATE = "update product";
    public const GET_REPORT = "get report";
    public const EXIT = "exit";

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->helper = new QuestionHelper();
    }

    public function mainAction(): string
    {
        $question = new ChoiceQuestion("What do you want to do?", [
            self::ADD_NEW_PRODUCT,
            self::DELETE_PRODUCT,
            self::ADD_TO_PRODUCT,
            self::WITHDRAW_FROM_PRODUCT,
            self::UPDATE,
            self::GET_REPORT,
            self::EXIT,
        ]);
        return $this->helper->ask($this->input, $this->output, $question);
    }

    /**
     * @param Product[] $products
     */
    public function product(array $products): string
    {
        $productChoices = [];
        foreach ($products as $product) {
            $productChoices[] = "{$product->name()} ({$product->id()})";
        }
        $question = new ChoiceQuestion("Select a product", $productChoices);
        $pickedProduct = $this->helper->ask($this->input, $this->output, $question);
        $id = substr($pickedProduct, strrpos($pickedProduct, "("));
        return trim($id, "()");
    }

    /**
     * @return array{string, int}
     */
    public function productInfo(): array
    {
        $nameQuestion = new Question("What is the product name? ");
        $name = $this->helper->ask($this->input, $this->output, $nameQuestion);
        $quantity = $this->quantity();
        $price = $this->price();
        return [$name, $quantity, $price];
    }

    public function quantity(int $min = 1, int $max = 9999999): int
    {
        $quantityQuestion = (new Question("Enter the quantity ($min-$max) "))
            ->setValidator(function ($input) use ($min, $max): string {
                return $this->quantityValidator($input, $min, $max);
            });
        return (int)$this->helper->ask($this->input, $this->output, $quantityQuestion);
    }

    public function price(int $max = 9999999): int
    {
        $quantityQuestion = (new Question("Enter the price (1-$max) "))
            ->setValidator(function ($input) use ($max): string {
                return $this->quantityValidator($input, 1, $max);
            });
        return (int)$this->helper->ask($this->input, $this->output, $quantityQuestion);
    }

    public function property()
    {
        $question = new ChoiceQuestion("What property do you want to update?", [
            "price",
            "expiration date",
        ]);
        $property = $this->helper->ask($this->input, $this->output, $question);
        switch ($property)
        {
            case "price":
                return ["price" => $this->price()];
                break;
            case "expiration date":
                return ["expiresAt" => $this->date()];
        }

    }

    /**
     * @return array{string, string}
     */
    public function login(): array
    {
        $usernameQuestion = new Question("Enter your username ");
        $username = $this->helper->ask($this->input, $this->output, $usernameQuestion);

        $passwordQuestion = new Question("Enter your password ");
        $passwordQuestion->setHidden(true);
        $password = $this->helper->ask($this->input, $this->output, $passwordQuestion);

        return [$username, $password];
    }

    private function quantityValidator(string $input, int $min, int $max): string
    {
        if (!is_numeric($input)) {
            throw new RuntimeException("Quantity must be a number");
        }
        if ($input < $min) {
            throw new RuntimeException("Quantity must be greater than or equal to $min");
        }
        if ($input > $max) {
            throw new RuntimeException("Quantity must be less than or equal to $max");
        }
        return $input;
    }

    public function date(): string
    {
        $question = (new Question("Enter the date - "))
            ->setValidator(function ($input): string {
                return $this->dateValidator($input);
            });
        return $this->helper->ask($this->input, $this->output, $question);
    }

    private function dateValidator($input): string
    {
        try {
            Carbon::parse($input);
        }
        catch (InvalidFormatException $e) {
            throw new RuntimeException("Date could not be parsed");
        }
        return $input;
    }
}
