<?php

namespace App\Repositories\Contracts;

use App\Models\Slab;

interface SlabInterface
{
    /**
     * Find a slab by its ID.
     * @param int $id
     * @return Slab|null
     */
    public function findById(int $id): ?Slab;

    /**
     * Get all slabs.
     * @return Slab[]
     */
    public function all(): array;

    /**
     * Create a new slab.
     * @param Slab $slab
     * @return int The ID of the newly created slab.
     */
    public function create(Slab $slab): int;

    /**
     * Update an existing slab.
     * @param Slab $slab
     * @return bool True on success, false otherwise.
     */
    public function update(Slab $slab): bool;

    /**
     * Delete a slab by its ID.
     * @param int $id
     * @return bool True on success, false otherwise.
     */
    public function delete(int $id): bool;
}