<?php

namespace App\Warehouse;

use Carbon\Carbon;
use DateTimeInterface;
use JsonSerializable;
use Ramsey\Uuid\Uuid;

class Product implements JsonSerializable
{
    private string $name;
    private int $price;
    private int $quantity;
    private string $id;
    private Carbon $createdAt;
    private Carbon $updatedAt;
    private ?Carbon $expiresAt;

    public function __construct(
        string $name,
        int $price,
        int $quantity,
        ?string $id = null,
        ?string $createdAt = null,
        ?string $updatedAt = null,
        ?string $expiresAt = null
    ) {
        $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->id = $id ?: Uuid::uuid4()->toString();
        $this->createdAt = $createdAt ? Carbon::parse($createdAt) : Carbon::now("UTC");
        $this->updatedAt = $updatedAt ? Carbon::parse($updatedAt) : Carbon::now("UTC");
        $this->expiresAt = $expiresAt ? Carbon::parse($expiresAt) : null;
    }

    public function price(): int
    {
        return $this->price;
    }

    private function setPrice(int $price): void
    {
        $this->price = $price;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function expiresAt(): ?Carbon
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?Carbon $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    public function createdAt(): Carbon
    {
        return $this->createdAt;
    }

    public function updatedAt(): Carbon
    {
        return $this->updatedAt;
    }

    public function update(array $data): void
    {
        $this->setPrice($data["price"] ?? $this->price);
        $this->setQuantity($data["quantity"] ?? $this->quantity);
        $this->setExpiresAt($data["expiresAt"] ? Carbon::parse($data["expiresAt"]) : $this->expiresAt);

        $this->updatedAt = Carbon::now("UTC");
    }

    public function jsonSerialize(): array
    {
        return [
            "name" => $this->name,
            "price" => $this->price,
            "quantity" => $this->quantity,
            "id" => $this->id,
            "expiresAt" => $this->expiresAt ?
                $this->expiresAt->timezone("Europe/Riga")->format(DateTimeInterface::ATOM) :
                null,
            "createdAt" => $this->createdAt->timezone("Europe/Riga")->format(DateTimeInterface::ATOM),
            "updatedAt" => $this->updatedAt->timezone("Europe/Riga")->format(DateTimeInterface::ATOM),
        ];
    }

}