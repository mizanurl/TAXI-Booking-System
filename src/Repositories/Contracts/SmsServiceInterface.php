<?php

namespace App\Repositories\Contracts;

use App\Models\SmsService;
use PDOException;

interface SmsServiceInterface
{
    /**
     * Find an SMS service entry by its ID.
     * @param int $id
     * @return SmsService|null
     * @throws PDOException
     */
    public function findById(int $id): ?SmsService;

    /**
     * Get all SMS service entries.
     * @return SmsService[]
     * @throws PDOException
     */
    public function all(): array;

    /**
     * Create a new SMS service entry.
     * @param SmsService $smsService
     * @return int The ID of the newly created SMS service entry.
     * @throws PDOException
     */
    public function create(SmsService $smsService): int;

    /**
     * Update an existing SMS service entry.
     * @param SmsService $smsService
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function update(SmsService $smsService): bool;

    /**
     * Delete an SMS service entry by its ID.
     * @param int $id
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function delete(int $id): bool;
}