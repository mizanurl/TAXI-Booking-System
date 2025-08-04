<?php

namespace App\Services;

use App\Models\Slab;
use App\Repositories\Contracts\SlabInterface;
use App\Exceptions\NotFoundException;
use PDOException; // Import PDOException for specific error handling

class SlabService
{
    private SlabInterface $slabRepository;

    public function __construct(SlabInterface $slabRepository)
    {
        $this->slabRepository = $slabRepository;
    }

    /**
     * Get a single slab by ID.
     * @param int $id
     * @return Slab
     * @throws NotFoundException If the slab is not found.
     * @throws \Exception On database errors.
     */
    public function getSlab(int $id): Slab
    {
        try {
            $slab = $this->slabRepository->findById($id);
            if (!$slab) {
                throw new NotFoundException("Slab with ID {$id} not found.");
            }
            return $slab;
        } catch (PDOException $e) {
            error_log("Database error in getSlab: " . $e->getMessage());
            throw new \Exception("Failed to retrieve slab due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in getSlab: " . $e->getMessage());
            throw $e; // Re-throw other exceptions
        }
    }

    /**
     * Get all slabs.
     * @return Slab[]
     * @throws \Exception On database errors.
     */
    public function getAllSlabs(): array
    {
        try {
            return $this->slabRepository->all();
        } catch (PDOException $e) {
            error_log("Database error in getAllSlabs: " . $e->getMessage());
            throw new \Exception("Failed to retrieve slabs due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in getAllSlabs: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new slab.
     * @param array $data Validated data for the new slab.
     * @return Slab
     * @throws \Exception On creation failure or database errors.
     */
    public function createSlab(array $data): Slab
    {
        try {
            $slab = new Slab(
                id: null,
                slabValue: (float) $data['slab_value'],
                slabUnit: (int) $data['slab_unit'],
                slabType: (int) $data['slab_type'],
                status: (int) $data['status']
            );

            $newId = $this->slabRepository->create($slab);
            if (!$newId) {
                throw new \Exception("Failed to create slab in the database.");
            }
            $slab->id = $newId;
            return $slab;
        } catch (PDOException $e) {
            error_log("Database error in createSlab: " . $e->getMessage());
            throw new \Exception("Failed to create slab due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in createSlab: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing slab.
     * @param int $id The ID of the slab to update.
     * @param array $data Validated data for updating the slab.
     * @return Slab
     * @throws NotFoundException If the slab is not found.
     * @throws \Exception On update failure or database errors.
     */
    public function updateSlab(int $id, array $data): Slab
    {
        try {
            $slab = $this->slabRepository->findById($id);
            if (!$slab) {
                throw new NotFoundException("Slab with ID {$id} not found.");
            }

            // Update properties only if they are provided in the data
            $slab->slabValue = $data['slab_value'] ?? $slab->slabValue;
            $slab->slabUnit = $data['slab_unit'] ?? $slab->slabUnit;
            $slab->slabType = $data['slab_type'] ?? $slab->slabType;
            $slab->status = $data['status'] ?? $slab->status;
            $slab->updatedAt = date('Y-m-d H:i:s'); // Update timestamp

            if (!$this->slabRepository->update($slab)) {
                throw new \Exception("Failed to update slab in the database.");
            }

            return $slab;
        } catch (PDOException $e) {
            error_log("Database error in updateSlab: " . $e->getMessage());
            throw new \Exception("Failed to update slab due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in updateSlab: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a slab.
     * @param int $id The ID of the slab to delete.
     * @return bool True on successful deletion.
     * @throws NotFoundException If the slab is not found.
     * @throws \Exception On deletion failure or database errors.
     */
    public function deleteSlab(int $id): bool
    {
        try {
            $slab = $this->slabRepository->findById($id);
            if (!$slab) {
                throw new NotFoundException("Slab with ID {$id} not found.");
            }

            if (!$this->slabRepository->delete($id)) {
                throw new \Exception("Failed to delete slab from the database.");
            }
            return true;
        } catch (PDOException $e) {
            error_log("Database error in deleteSlab: " . $e->getMessage());
            throw new \Exception("Failed to delete slab due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in deleteSlab: " . $e->getMessage());
            throw $e;
        }
    }
}