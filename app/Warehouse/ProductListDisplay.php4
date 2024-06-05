<?php

namespace App\Warehouse;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class DisplayProducts
{
    private OutputInterface $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param Product[] $products
     */
    public function display(array $products): void
    {
        $table = new Table($this->output);
        $table->setHeaderTitle("Warehouse");
        $table->setHeaders(["Name", "Stock", "Created", "Last updated"]);
        foreach ($products as $product) {
            $table->addRow(
                [
                    $product->name(),
                    $product->quantity(),
                    $product->createdAt()->timezone("Europe/Riga")->format("Y-m-d H:i:s"),
                    $product->updatedAt()->timezone("Europe/Riga")->format("Y-m-d H:i:s"),
                ]);
        }
        $table->setStyle("box");
        $table->getStyle()->setPadType(STR_PAD_BOTH);
        $table->render();
    }
}
