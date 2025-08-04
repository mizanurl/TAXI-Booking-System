<?php

namespace App\Repositories\MySQL;

use App\Models\User;
use App\Repositories\Contracts\UserInterface;
use PDO;
use PDOException;

class UserDatabase implements UserInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find a user by their ID.
     * @param int $id
     * @return User|null
     * @throws PDOException
     */
    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        return $userData ? User::fromArray($userData) : null;
    }

    /**
     * Find a user by their email address.
     * @param string $email
     * @return User|null
     * @throws PDOException
     */
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        return $userData ? User::fromArray($userData) : null;
    }

    /**
     * Create a new user.
     * @param User $user
     * @return int The ID of the newly created user.
     * @throws PDOException
     */
    public function create(User $user): int
    {
        $data = $user->toDatabaseArray();
        unset($data['id']); // ID is auto-incremented

        $sql = "INSERT INTO users (name, email, password, status, created_at, updated_at)
                VALUES (:name, :email, :password, :status, :created_at, :updated_at)";
        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', $data['password']); // Hashed password
        $stmt->bindParam(':status', $data['status'], PDO::PARAM_INT);
        $stmt->bindParam(':created_at', $data['created_at']);
        $stmt->bindParam(':updated_at', $data['updated_at']);

        $stmt->execute();
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing user.
     * @param User $user
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function update(User $user): bool
    {
        $data = $user->toDatabaseArray();
        if (!isset($data['id'])) {
            throw new PDOException("User ID is required for update.");
        }

        $sql = "UPDATE users SET
                    name = :name,
                    email = :email,
                    password = :password,
                    status = :status,
                    updated_at = :updated_at
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', $data['password']);
        $stmt->bindParam(':status', $data['status'], PDO::PARAM_INT);
        $stmt->bindParam(':updated_at', $data['updated_at']);

        return $stmt->execute();
    }

    /**
     * Delete a user by their ID.
     * @param int $id
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}