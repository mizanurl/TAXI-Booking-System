<?php

namespace App\Repositories\Contracts;

use App\Models\Car;

interface CarInterface
{
    /**
     * Find a car by its ID.
     * @param int $id
     * @return Car|null
     */
    public function findById(int $id): ?Car;

    /**
     * Get all cars.
     * @return Car[]
     */
    public function all(): array;

    /**
     * Create a new car.
     * @param Car $car
     * @return int The ID of the newly created car.
     */
    public function create(Car $car): int;

    /**
     * Update an existing car.
     * @param Car $car
     * @return bool True on success, false otherwise.
     */
    public function update(Car $car): bool;

    /**
     * Delete a car by its ID.
     * @param int $id
     * @return bool True on success, false otherwise.
     */
    public function delete(int $id): bool;
}