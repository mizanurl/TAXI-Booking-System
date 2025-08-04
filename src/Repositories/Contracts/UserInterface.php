<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use PDOException;

interface UserInterface
{
    /**
     * Find a user by their ID.
     * @param int $id
     * @return User|null
     * @throws PDOException
     */
    public function findById(int $id): ?User;

    /**
     * Find a user by their email address.
     * @param string $email
     * @return User|null
     * @throws PDOException
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create a new user.
     * @param User $user
     * @return int The ID of the newly created user.
     * @throws PDOException
     */
    public function create(User $user): int;

    /**
     * Update an existing user.
     * @param User $user
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function update(User $user): bool;

    /**
     * Delete a user by their ID.
     * @param int $id
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function delete(int $id): bool;
}