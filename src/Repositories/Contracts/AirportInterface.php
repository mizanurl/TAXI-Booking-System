<?php

namespace App\Repositories\Contracts;

use App\Models\Airport;
use PDOException;

interface AirportInterface
{
    /**
     * Find an airport by its ID.
     * @param int $id
     * @return Airport|null
     * @throws PDOException
     */
    public function findById(int $id): ?Airport;

    /**
     * Find an airport by its name.
     * @param string $name
     * @return Airport|null
     */
    public function findByName(string $name): ?Airport;

    /**
     * Get all airports.
     * @return Airport[]
     * @throws PDOException
     */
    public function all(): array;

    /**
     * Get all active airports.
     * @return Airport[]
     * @throws PDOException
     */
    public function allActive(): array;

    /**
     * Create a new airport.
     * @param Airport $airport
     * @return int The ID of the newly created airport.
     * @throws PDOException
     */
    public function create(Airport $airport): int;

    /**
     * Update an existing airport.
     * @param Airport $airport
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function update(Airport $airport): bool;

    /**
     * Delete an airport by its ID.
     * @param int $id
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function delete(int $id): bool;
}