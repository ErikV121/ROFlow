<?php

require_once __DIR__ . '/enum/Role.php';
use Enum\Role;


class User
{
    private int $id;
    private string $email;
    private string $passwordHash;
    private string $fullName;
    private Role $role;
    private ?string $createdAt;

    public function __construct(
        int     $id,
        string  $email,
        string  $passwordHash,
        string  $fullName,
        Role    $role,
        ?string $createdAt = null
    )
    {
        $this->id = $id;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->fullName = $fullName;
        $this->role = $role;
        $this->createdAt = $createdAt;
    }




//    reminder , our built in ORM
    public static function fromRow(array $row): self
    {
        return new self(
            (int)$row['id'],
            $row['email'],
            $row['password_hash'],
            $row['full_name'],
            Role::from($row['role']),
            $row['created_at'] ?? null
        );
    }


    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function isAdvisor(): bool
    {
        return $this->role === Role::Advisor;
    }

    public function isTechnician(): bool
    {
        return $this->role === Role::Technician;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function setFullName(string $fullName): void
    {
        $this->fullName = $fullName;
    }

    public function setRole(Role $role): void
    {
        $this->role = $role;
    }
}