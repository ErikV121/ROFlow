<?php

require_once __DIR__ . '/../models/Customer.php';


class CustomerRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?Customer
    {
        $stmt = $this->pdo->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? Customer::fromRow($row) : null;
    }

    public function findByPhone(string $phone): ?Customer
    {
        $stmt = $this->pdo->prepare("SELECT * FROM customers WHERE phone = ? LIMIT 1");
        $stmt->execute([$phone]);
        $row = $stmt->fetch();
        return $row ? Customer::fromRow($row) : null;
    }


    public function create(string $fullName, ?string $phone, ?string $email): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO customers (full_name, phone, email) VALUES (?, ?, ?) RETURNING id"
        );
        $stmt->execute([$fullName, $phone, $email]);
        return (int) $stmt->fetchColumn();
    }

}
