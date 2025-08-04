<?php

namespace App\Repositories\Contracts;

use App\Models\ExtraCharge;
use PDOException;

interface ExtraChargeInterface
{
    /**
     * Find an extra charge by its ID.
     * @param int $id
     * @return ExtraCharge|null
     * @throws PDOException
     */
    public function findById(int $id): ?ExtraCharge;

    /**
     * Find an extra charge by area name.
     * @param string $areaName
     * @return ExtraCharge|null
     * @throws PDOException
     */
    public function findByAreaName(string $areaName): ?ExtraCharge;

    /**
     * Get all extra charges.
     * @return ExtraCharge[]
     * @throws PDOException
     */
    public function all(): array;

    /**
     * Create a new extra charge.
     * @param ExtraCharge $extraCharge
     * @return int The ID of the newly created extra charge.
     * @throws PDOException
     */
    public function create(ExtraCharge $extraCharge): int;

    /**
     * Update an existing extra charge.
     * @param ExtraCharge $extraCharge
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function update(ExtraCharge $extraCharge): bool;

    /**
     * Delete an extra charge by its ID.
     * @param int $id
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function delete(int $id): bool;
}