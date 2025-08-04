<?php

namespace App\Repositories\Contracts;

use App\Models\CarSlabFare;

interface CarSlabFareInterface
{
    /**
     * Get all car slab fares for a specific car.
     * @param int $carId
     * @return CarSlabFare[]
     */
    public function getByCarId(int $carId): array;

    /**
     * Create a new car slab fare entry.
     * @param CarSlabFare $carSlabFare
     * @return int The ID of the newly created entry.
     */
    public function create(CarSlabFare $carSlabFare): int;

    /**
     * Find a car slab fare by its ID.
     * @param int $id
     * @return CarSlabFare|null
     */
    public function findById(int $id): ?CarSlabFare;

    /**
     * Update an existing car slab fare entry.
     * @param CarSlabFare $carSlabFare
     * @return bool True on success, false otherwise.
     */
    public function update(CarSlabFare $carSlabFare): bool;

    /**
     * Delete a car slab fare by its ID.
     * @param int $id
     * @return bool True on success, false otherwise.
     */
    public function delete(int $id): bool;

    /**
     * Delete all car slab fares associated with a specific car ID.
     * @param int $carId
     * @return bool True on success, false otherwise.
     */
    public function deleteByCarId(int $carId): bool;
}