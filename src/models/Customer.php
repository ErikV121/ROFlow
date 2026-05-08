<?php

class Customer
{
    private int $id;
    private string $fullName;
    private ?string $phone;
    private ?string $email;
    private ?string $createdAt;

    public function __construct(
        int     $id,
        string  $fullName,
        ?string $phone = null,
        ?string $email = null,
        ?string $createdAt = null
    )
    {
        $this->id = $id;
        $this->fullName = $fullName;
        $this->phone = $phone;
        $this->email = $email;
        $this->createdAt = $createdAt;
    }

    public static function fromRow(array $row): self
    {
        return new self(
            (int)$row['id'],
            $row['full_name'],
            $row['phone'] ?? null,
            $row['email'] ?? null,
            $row['created_at'] ?? null
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setFullName(string $fullName): void
    {
        $this->fullName = $fullName;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }
}