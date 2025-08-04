<?php

namespace App\Services;

use App\Models\SmsService;
use App\Repositories\Contracts\SmsServiceInterface;
use App\Exceptions\NotFoundException;
use PDOException;

class SmsServiceService
{
    private SmsServiceInterface $smsServiceRepository;

    public function __construct(SmsServiceInterface $smsServiceRepository)
    {
        $this->smsServiceRepository = $smsServiceRepository;
    }

    /**
     * Get all SMS service entries.
     * @return SmsService[]
     * @throws \Exception On database errors.
     */
    public function getAllSmsServices(): array
    {
        try {
            return $this->smsServiceRepository->all();
        } catch (PDOException $e) {
            error_log("Database error in getAllSmsServices: " . $e->getMessage());
            throw new \Exception("Failed to retrieve SMS services due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in getAllSmsServices: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get an SMS service entry by ID.
     * @param int $id
     * @return SmsService
     * @throws NotFoundException If the SMS service entry is not found.
     * @throws \Exception On database errors.
     */
    public function getSmsServiceById(int $id): SmsService
    {
        try {
            $smsService = $this->smsServiceRepository->findById($id);
            if (!$smsService) {
                throw new NotFoundException("SMS Service with ID {$id} not found.");
            }
            return $smsService;
        } catch (PDOException $e) {
            error_log("Database error in getSmsServiceById: " . $e->getMessage());
            throw new \Exception("Failed to retrieve SMS service due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in getSmsServiceById: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new SMS service entry.
     * @param array $data Validated data for the new SMS service entry.
     * @return SmsService
     * @throws \Exception On creation failure or database errors.
     */
    public function createSmsService(array $data): SmsService
    {
        try {
            $smsService = new SmsService(
                id: null,
                phoneNumber: $data['phone_number'],
                status: (int) $data['status']
            );

            $newId = $this->smsServiceRepository->create($smsService);
            if (!$newId) {
                throw new \Exception("Failed to create SMS service in the database.");
            }
            $smsService->id = $newId;
            return $smsService;
        } catch (PDOException $e) {
            error_log("Database error in createSmsService: " . $e->getMessage());
            throw new \Exception("Failed to create SMS service due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in createSmsService: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing SMS service entry.
     * @param int $id The ID of the SMS service entry to update.
     * @param array $data Validated data for updating the SMS service entry.
     * @return SmsService
     * @throws NotFoundException If the SMS service entry is not found.
     * @throws \Exception On update failure or database errors.
     */
    public function updateSmsService(int $id, array $data): SmsService
    {
        try {
            $smsService = $this->smsServiceRepository->findById($id);
            if (!$smsService) {
                throw new NotFoundException("SMS Service with ID {$id} not found.");
            }

            // Apply updates
            if (isset($data['phone_number'])) $smsService->phoneNumber = $data['phone_number'];
            if (isset($data['status'])) $smsService->status = (int) $data['status'];

            $smsService->updatedAt = date('Y-m-d H:i:s');

            if (!$this->smsServiceRepository->update($smsService)) {
                throw new \Exception("Failed to update SMS service in the database.");
            }

            return $smsService;
        } catch (PDOException $e) {
            error_log("Database error in updateSmsService: " . $e->getMessage());
            throw new \Exception("Failed to update SMS service due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in updateSmsService: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete an SMS service entry.
     * @param int $id The ID of the SMS service entry to delete.
     * @return bool True on successful deletion.
     * @throws NotFoundException If the SMS service entry is not found.
     * @throws \Exception On deletion failure or database errors.
     */
    public function deleteSmsService(int $id): bool
    {
        try {
            $smsService = $this->smsServiceRepository->findById($id);
            if (!$smsService) {
                throw new NotFoundException("SMS Service with ID {$id} not found.");
            }

            if (!$this->smsServiceRepository->delete($id)) {
                throw new \Exception("Failed to delete SMS service from the database.");
            }

            return true;
        } catch (PDOException $e) {
            error_log("Database error in deleteSmsService: " . $e->getMessage());
            throw new \Exception("Failed to delete SMS service due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in deleteSmsService: " . $e->getMessage());
            throw $e;
        }
    }
}