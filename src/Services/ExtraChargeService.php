<?php

namespace App\Services;

use App\Models\ExtraCharge;
use App\Repositories\Contracts\ExtraChargeInterface;
use App\Exceptions\NotFoundException;
use PDOException;

class ExtraChargeService
{
    private ExtraChargeInterface $extraChargeRepository;

    public function __construct(ExtraChargeInterface $extraChargeRepository)
    {
        $this->extraChargeRepository = $extraChargeRepository;
    }

    /**
     * Get all extra charges.
     * @return ExtraCharge[]
     * @throws \Exception On database errors.
     */
    public function getAllExtraCharges(): array
    {
        try {
            return $this->extraChargeRepository->all();
        } catch (PDOException $e) {
            error_log("Database error in getAllExtraCharges: " . $e->getMessage());
            throw new \Exception("Failed to retrieve extra charges due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in getAllExtraCharges: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get an extra charge by ID.
     * @param int $id
     * @return ExtraCharge
     * @throws NotFoundException If the extra charge is not found.
     * @throws \Exception On database errors.
     */
    public function getExtraChargeById(int $id): ExtraCharge
    {
        try {
            $extraCharge = $this->extraChargeRepository->findById($id);
            if (!$extraCharge) {
                throw new NotFoundException("Extra Charge with ID {$id} not found.");
            }
            return $extraCharge;
        } catch (PDOException $e) {
            error_log("Database error in getExtraChargeById: " . $e->getMessage());
            throw new \Exception("Failed to retrieve extra charge due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in getExtraChargeById: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new extra charge.
     * @param array $data Validated data for the new extra charge.
     * @return ExtraCharge
     * @throws \Exception On creation failure or database errors.
     */
    public function createExtraCharge(array $data): ExtraCharge
    {
        try {
            $extraCharge = new ExtraCharge(
                id: null,
                areaName: $data['area_name'],
                zipCodes: $data['zip_codes'],
                extraCharge: (float) $data['extra_charge'],
                extraTollCharge: (float) $data['extra_toll_charge'],
                status: (int) $data['status']
            );

            $newId = $this->extraChargeRepository->create($extraCharge);
            if (!$newId) {
                throw new \Exception("Failed to create extra charge in the database.");
            }
            $extraCharge->id = $newId;
            return $extraCharge;
        } catch (PDOException $e) {
            error_log("Database error in createExtraCharge: " . $e->getMessage());
            throw new \Exception("Failed to create extra charge due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in createExtraCharge: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing extra charge.
     * @param int $id The ID of the extra charge to update.
     * @param array $data Validated data for updating the extra charge.
     * @return ExtraCharge
     * @throws NotFoundException If the extra charge is not found.
     * @throws \Exception On update failure or database errors.
     */
    public function updateExtraCharge(int $id, array $data): ExtraCharge
    {
        try {
            $extraCharge = $this->extraChargeRepository->findById($id);
            if (!$extraCharge) {
                throw new NotFoundException("Extra Charge with ID {$id} not found.");
            }

            // Apply updates
            if (isset($data['area_name'])) $extraCharge->areaName = $data['area_name'];
            if (isset($data['zip_codes'])) $extraCharge->zipCodes = $data['zip_codes'];
            if (isset($data['extra_charge'])) $extraCharge->extraCharge = (float) $data['extra_charge'];
            if (isset($data['extra_toll_charge'])) $extraCharge->extraTollCharge = (float) $data['extra_toll_charge'];
            if (isset($data['status'])) $extraCharge->status = (int) $data['status'];

            $extraCharge->updatedAt = date('Y-m-d H:i:s');

            if (!$this->extraChargeRepository->update($extraCharge)) {
                throw new \Exception("Failed to update extra charge in the database.");
            }

            return $extraCharge;
        } catch (PDOException $e) {
            error_log("Database error in updateExtraCharge: " . $e->getMessage());
            throw new \Exception("Failed to update extra charge due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in updateExtraCharge: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete an extra charge.
     * @param int $id The ID of the extra charge to delete.
     * @return bool True on successful deletion.
     * @throws NotFoundException If the extra charge is not found.
     * @throws \Exception On deletion failure or database errors.
     */
    public function deleteExtraCharge(int $id): bool
    {
        try {
            $extraCharge = $this->extraChargeRepository->findById($id);
            if (!$extraCharge) {
                throw new NotFoundException("Extra Charge with ID {$id} not found.");
            }

            if (!$this->extraChargeRepository->delete($id)) {
                throw new \Exception("Failed to delete extra charge from the database.");
            }

            return true;
        } catch (PDOException $e) {
            error_log("Database error in deleteExtraCharge: " . $e->getMessage());
            throw new \Exception("Failed to delete extra charge due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in deleteExtraCharge: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get applicable extra charges based on provided zip codes.
     * @param string[] $zipCodes An array of zip codes to check.
     * @return ExtraCharge[]
     */
    public function getApplicableExtraCharges(array $zipCodes): array
    {
        if (empty($zipCodes)) {
            return [];
        }

        $allExtraCharges = $this->extraChargeRepository->all(); // Fetch all extra charges
        $applicableCharges = [];

        foreach ($allExtraCharges as $extraCharge) {
            // Split the stored zip_codes string into an array and trim whitespace
            $configuredZipCodes = array_map('trim', explode(',', $extraCharge->zipCodes));

            // Check if any of the provided zip codes match any configured zip codes
            foreach ($zipCodes as $providedZipCode) {
                if (in_array($providedZipCode, $configuredZipCodes)) {
                    $applicableCharges[] = $extraCharge;
                    break; // Found a match, move to the next extra charge entry
                }
            }
        }
        return $applicableCharges;
    }

    /**
     * Get an extra charge by area name.
     * @param string $areaName
     * @return ExtraCharge|null
     */
    public function getExtraChargeByAreaName(string $areaName): ?ExtraCharge
    {
        try {
            return $this->extraChargeRepository->findByAreaName($areaName);
        } catch (PDOException $e) {
            error_log("Database error in getExtraChargeByAreaName: " . $e->getMessage());
            throw new \Exception("Failed to retrieve extra charge due to a database error.");
        }
    }
}