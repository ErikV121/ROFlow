<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../repository/UserRepository.php';

use Enum\Role;

class UserService
{
    private UserRepository $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function authenticate(string $email, string $plainPassword): ?User
    {
        $user = $this->userRepo->findByEmail($email);
        if ($user === null) {
            return null;
        }
        if (!password_verify($plainPassword, $user->getPasswordHash())) {
            return null;
        }
        return $user;
    }

    /**
     * Create a new staff user.
     * Validates email uniqueness and password strength BEFORE hitting the DB.
     * Hashes the password — repo never sees plaintext.
     */
    public function createUser(string $email, string $plainPassword, string $fullName, Role $role): User
    {
        // Validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format.');
        }
        if (strlen($plainPassword) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters.');
        }

        // Business rule: email must be unique
        if ($this->userRepo->findByEmail($email) !== null) {
            throw new RuntimeException('A user with that email already exists.');
        }

        // Hash before persistence — repo never gets plaintext
        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);

        $newId = $this->userRepo->create($email, $hash, $fullName, $role);
        return $this->userRepo->findById($newId);
    }

}