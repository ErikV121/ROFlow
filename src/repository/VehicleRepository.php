<?php

require_once __DIR__ . '/../models/Vehicle.php';

class VehicleRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?Vehicle
    {
        $stmt = $this->pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? Vehicle::fromRow($row) : null;
    }

    public function findByVin(string $vin): ?Vehicle
    {
        $stmt = $this->pdo->prepare("SELECT * FROM vehicles WHERE vin = ? LIMIT 1");
        $stmt->execute([$vin]);
        $row = $stmt->fetch();
        return $row ? Vehicle::fromRow($row) : null;
    }

    public function create(
        int     $customerId,
        string  $vin,
        int     $year,
        string  $make,
        string  $model,
        ?string $color
    ): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO vehicles (customer_id, vin, year, make, model, color)
             VALUES (?, ?, ?, ?, ?, ?) RETURNING id"
        );
        $stmt->execute([$customerId, $vin, $year, $make, $model, $color]);
        return (int) $stmt->fetchColumn();
    }

}
