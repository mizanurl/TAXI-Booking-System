<?php

namespace App\Repositories\Contracts;

use App\Models\GoogleApiKey;

/**
 * Interface for Google API Key Repository.
 */
interface GoogleApiKeyInterface
{
    /**
     * Find a key by its ID.
     * @param int $id
     * @return GoogleApiKey|null
     * @throws PDOException
     */
    public function findById(int $id): ?GoogleApiKey;

    /**
     * Find a key by its value.
     * @param string $keyValue
     * @return GoogleApiKey|null
     */
    public function findByApiKey(string $keyValue): ?GoogleApiKey;

    /**
     * Get all keys.
     * @return GoogleApiKey[]
     * @throws PDOException
     */
    public function all(): array;

    /**
     * Get all active keys.
     * @return GoogleApiKey[]
     * @throws PDOException
     */
    public function allActive(): array;

    /**
     * Get one active key.
     * @return GoogleApiKey|null
     * @throws PDOException
     */
    public function getOneActive(): ?GoogleApiKey;

    /**
     * Create a new key.
     * @param GoogleApiKey $googleApiKey
     * @return int The ID of the newly created airport.
     * @throws PDOException
     */
    public function create(GoogleApiKey $googleApiKey): int;

    /**
     * Update an existing key.
     * @param GoogleApiKey $googleApiKey
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function update(GoogleApiKey $googleApiKey): bool;
}