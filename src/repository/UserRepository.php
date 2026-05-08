<?php

require_once __DIR__ . '/../models/User.php';

use Enum\Role;

class UserRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? User::fromRow($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ? User::fromRow($row) : null;
    }

    public function findAllByRole(Role $role): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM users WHERE role = ? ORDER BY full_name ASC"
        );
        $stmt->execute([$role->value]);

        $users = [];
        while ($row = $stmt->fetch()) {
            $users[] = User::fromRow($row);
        }
        return $users;
    }



    public function create(string $email, string $passwordHash, string $fullName, Role $role): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (email, password_hash, full_name, role) VALUES (?, ?, ?, ?) RETURNING id"
        );
        $stmt->execute([$email, $passwordHash, $fullName, $role->value]);
        return (int) $stmt->fetchColumn();
    }

}
