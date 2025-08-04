<?php

namespace App\Repositories\Contracts;

use App\Models\TunnelCharge;
use PDOException;

interface TunnelChargeInterface
{
    /**
     * Find a tunnel charge by its ID.
     * @param int $id
     * @return TunnelCharge|null
     * @throws PDOException
     */
    public function findById(int $id): ?TunnelCharge;

    /**
     * Get all tunnel charges.
     * @return TunnelCharge[]
     * @throws PDOException
     */
    public function all(): array;

    /**
     * Create a new tunnel charge.
     * @param TunnelCharge $tunnelCharge
     * @return int The ID of the newly created tunnel charge.
     * @throws PDOException
     */
    public function create(TunnelCharge $tunnelCharge): int;

    /**
     * Update an existing tunnel charge.
     * @param TunnelCharge $tunnelCharge
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function update(TunnelCharge $tunnelCharge): bool;

    /**
     * Delete a tunnel charge by its ID.
     * @param int $id
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function delete(int $id): bool;
}