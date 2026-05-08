<?php

class Vehicle
{
    private int $id;
    private int $customerId;
    private string $vin;
    private int $year;
    private string $make;
    private string $model;
    private ?string $color;
    private ?string $createdAt;

    public function __construct(
        int     $id,
        int     $customerId,
        string  $vin,
        int     $year,
        string  $make,
        string  $model,
        ?string $color = null,
        ?string $createdAt = null
    )
    {
        $this->id         = $id;
        $this->customerId = $customerId;
        $this->vin        = $vin;
        $this->year       = $year;
        $this->make       = $make;
        $this->model      = $model;
        $this->color      = $color;
        $this->createdAt  = $createdAt;
    }

    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (int) $row['customer_id'],
            $row['vin'],
            (int) $row['year'],
            $row['make'],
            $row['model'],
            $row['color'] ?? null,
            $row['created_at'] ?? null
        );
    }

    public function getId(): int { return $this->id; }
    public function getCustomerId(): int { return $this->customerId; }
    public function getVin(): string { return $this->vin; }
    public function getYear(): int { return $this->year; }
    public function getMake(): string { return $this->make; }
    public function getModel(): string { return $this->model; }
    public function getColor(): ?string { return $this->color; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    public function getDisplayName(): string
    {
        return "{$this->year} {$this->make} {$this->model}";
    }

    public function setVin(string $vin): void { $this->vin = $vin; }
    public function setYear(int $year): void { $this->year = $year; }
    public function setMake(string $make): void { $this->make = $make; }
    public function setModel(string $model): void { $this->model = $model; }
    public function setColor(?string $color): void { $this->color = $color; }
    public function setCustomerId(int $customerId): void { $this->customerId = $customerId; }
}