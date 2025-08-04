<?php

namespace App\Services;

use App\Models\TunnelCharge;
use App\Repositories\Contracts\TunnelChargeInterface;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException; // Used for date validation logic
use PDOException;

class TunnelChargeService
{
    private TunnelChargeInterface $tunnelChargeRepository;

    public function __construct(TunnelChargeInterface $tunnelChargeRepository)
    {
        $this->tunnelChargeRepository = $tunnelChargeRepository;
    }

    /**
     * Get all tunnel charges.
     * @return TunnelCharge[]
     * @throws \Exception On database errors.
     */
    public function getAllTunnelCharges(): array
    {
        try {
            return $this->tunnelChargeRepository->all();
        } catch (PDOException $e) {
            error_log("Database error in getAllTunnelCharges: " . $e->getMessage());
            throw new \Exception("Failed to retrieve tunnel charges due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in getAllTunnelCharges: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get a tunnel charge by ID.
     * @param int $id
     * @return TunnelCharge
     * @throws NotFoundException If the tunnel charge is not found.
     * @throws \Exception On database errors.
     */
    public function getTunnelChargeById(int $id): TunnelCharge
    {
        try {
            $tunnelCharge = $this->tunnelChargeRepository->findById($id);
            if (!$tunnelCharge) {
                throw new NotFoundException("Tunnel Charge with ID {$id} not found.");
            }
            return $tunnelCharge;
        } catch (PDOException $e) {
            error_log("Database error in getTunnelChargeById: " . $e->getMessage());
            throw new \Exception("Failed to retrieve tunnel charge due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in getTunnelChargeById: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new tunnel charge.
     * @param array $data Validated data for the new tunnel charge.
     * @return TunnelCharge
     * @throws ValidationException If charge_end_date is before charge_start_date.
     * @throws \Exception On creation failure or database errors.
     */
    public function createTunnelCharge(array $data): TunnelCharge
    {
        // Additional business logic validation: ensure end date is not before start date
        if (isset($data['charge_start_date']) && isset($data['charge_end_date'])) {
            if (strtotime($data['charge_end_date']) < strtotime($data['charge_start_date'])) {
                throw new ValidationException(
                    "The given data was invalid.",
                    ['charge_end_date' => ['Charge end date cannot be before charge start date.']]
                );
            }
        }

        try {
            $tunnelCharge = new TunnelCharge(
                id: null,
                chargeStartDate: $data['charge_start_date'],
                chargeEndDate: $data['charge_end_date'],
                chargeAmount: (float) $data['charge_amount'],
                status: (int) $data['status']
            );

            $newId = $this->tunnelChargeRepository->create($tunnelCharge);
            if (!$newId) {
                throw new \Exception("Failed to create tunnel charge in the database.");
            }
            $tunnelCharge->id = $newId;
            return $tunnelCharge;
        } catch (PDOException $e) {
            error_log("Database error in createTunnelCharge: " . $e->getMessage());
            throw new \Exception("Failed to create tunnel charge due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in createTunnelCharge: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing tunnel charge.
     * @param int $id The ID of the tunnel charge to update.
     * @param array $data Validated data for updating the tunnel charge.
     * @return TunnelCharge
     * @throws NotFoundException If the tunnel charge is not found.
     * @throws ValidationException If charge_end_date is before charge_start_date.
     * @throws \Exception On update failure or database errors.
     */
    public function updateTunnelCharge(int $id, array $data): TunnelCharge
    {
        try {
            $tunnelCharge = $this->tunnelChargeRepository->findById($id);
            if (!$tunnelCharge) {
                throw new NotFoundException("Tunnel Charge with ID {$id} not found.");
            }

            // Apply updates
            if (isset($data['charge_start_date'])) $tunnelCharge->chargeStartDate = $data['charge_start_date'];
            if (isset($data['charge_end_date'])) $tunnelCharge->chargeEndDate = $data['charge_end_date'];
            if (isset($data['charge_amount'])) $tunnelCharge->chargeAmount = (float) $data['charge_amount'];
            if (isset($data['status'])) $tunnelCharge->status = (int) $data['status'];

            // Additional business logic validation: ensure end date is not before start date after updates
            if (strtotime($tunnelCharge->chargeEndDate) < strtotime($tunnelCharge->chargeStartDate)) {
                throw new ValidationException(
                    "The given data was invalid.",
                    ['charge_end_date' => ['Charge end date cannot be before charge start date.']]
                );
            }

            $tunnelCharge->updatedAt = date('Y-m-d H:i:s');

            if (!$this->tunnelChargeRepository->update($tunnelCharge)) {
                throw new \Exception("Failed to update tunnel charge in the database.");
            }

            return $tunnelCharge;
        } catch (PDOException $e) {
            error_log("Database error in updateTunnelCharge: " . $e->getMessage());
            throw new \Exception("Failed to update tunnel charge due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in updateTunnelCharge: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a tunnel charge.
     * @param int $id The ID of the tunnel charge to delete.
     * @return bool True on successful deletion.
     * @throws NotFoundException If the tunnel charge is not found.
     * @throws \Exception On deletion failure or database errors.
     */
    public function deleteTunnelCharge(int $id): bool
    {
        try {
            $tunnelCharge = $this->tunnelChargeRepository->findById($id);
            if (!$tunnelCharge) {
                throw new NotFoundException("Tunnel Charge with ID {$id} not found.");
            }

            if (!$this->tunnelChargeRepository->delete($id)) {
                throw new \Exception("Failed to delete tunnel charge from the database.");
            }

            return true;
        } catch (PDOException $e) {
            error_log("Database error in deleteTunnelCharge: " . $e->getMessage());
            throw new \Exception("Failed to delete tunnel charge due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in deleteTunnelCharge: " . $e->getMessage());
            throw $e;
        }
    }
}