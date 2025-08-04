<?php

namespace App\Services;

use App\Models\Airport;
use App\Repositories\Contracts\AirportInterface;
use App\Exceptions\ValidationException;
use App\Exceptions\DuplicateEntryException;
use App\Exceptions\NotFoundException;
use PDOException; // Import PDOException for database errors

class AirportService
{
    private AirportInterface $airportRepository;

    public function __construct(AirportInterface $airportRepository)
    {
        $this->airportRepository = $airportRepository;
    }

    /**
     * Get all airports.
     * @return Airport[]
     */
    public function getAllAirports(): array
    {
        try {
            return $this->airportRepository->all();
        } catch (PDOException $e) {
            error_log("Database error in getAllAirports: " . $e->getMessage());
            throw new \Exception("Failed to retrieve airports due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in getAllAirports: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all active airports.
     * @return Airport[]
     */
    public function getActiveAirports(): array
    {
        try {
            return $this->airportRepository->allActive();
        } catch (PDOException $e) {
            error_log("Database error in getActiveAirports: " . $e->getMessage());
            throw new \Exception("Failed to retrieve active airports due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in getActiveAirports: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get an airport by ID.
     * @param int $id
     * @return Airport|null
     */
    public function getAirportById(int $id): ?Airport
    {
        try {
            return $this->airportRepository->findById($id);
        } catch (PDOException $e) {
            error_log("Database error in getAirportById: " . $e->getMessage());
            throw new \Exception("Failed to retrieve airport due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in getAirportById: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new airport.
     * @param array $data
     * @return Airport
     * @throws DuplicateEntryException If an airport with the same name already exists.
     * @throws \Exception If creation fails.
     */
    public function createAirport(array $data): Airport
    {
        // Basic validation (can be replaced by a FormRequest)
        if (empty($data['name']) || !isset($data['from_tax_toll']) || !isset($data['to_tax_toll']) || !isset($data['status'])) {
            throw new ValidationException("Missing required fields for airport creation.", ['name', 'from_tax_toll', 'to_tax_toll', 'status']);
        }

        // Check for duplicate name
        if ($this->airportRepository->findByName($data['name'])) {
            throw new DuplicateEntryException("An airport with the name '{$data['name']}' already exists.");
        }

        try {
            $airport = new Airport(
                id: null,
                name: $data['name'],
                description: $data['description'] ?? '',
                fromTaxToll: (float) $data['from_tax_toll'],
                toTaxToll: (float) $data['to_tax_toll'],
                status: (int) $data['status']
            );

            $newId = $this->airportRepository->create($airport);
            if (!$newId) {
                throw new \Exception("Failed to create airport in the database.");
            }
            $airport->id = $newId;
            return $airport;
        } catch (PDOException $e) {
            error_log("Database error in createAirport: " . $e->getMessage());
            throw new \Exception("Failed to create airport due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in createAirport: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing airport.
     * @param int $id
     * @param array $data
     * @return Airport
     * @throws \Exception If airport not found or update fails.
     * @throws DuplicateEntryException If updating to a name that already exists for another airport.
     */
    public function updateAirport(int $id, array $data): Airport
    {
        try {
            $airport = $this->airportRepository->findById($id);

            if (!$airport) {
                throw new NotFoundException("Airport with ID {$id} not found.");
            }

            // Check for duplicate name only if name is being updated
            if (isset($data['name']) && $data['name'] !== $airport->name) {
                $existingAirport = $this->airportRepository->findByName($data['name']);
                if ($existingAirport && $existingAirport->id !== $id) {
                    throw new DuplicateEntryException("An airport with the name '{$data['name']}' already exists.");
                }
            }

            if (isset($data['name'])) $airport->name = $data['name'];
            if (isset($data['description'])) $airport->description = $data['description'];
            if (isset($data['from_tax_toll'])) $airport->fromTaxToll = (float) $data['from_tax_toll'];
            if (isset($data['to_tax_toll'])) $airport->toTaxToll = (float) $data['to_tax_toll'];
            if (isset($data['status'])) $airport->status = (int) $data['status'];

            $airport->updatedAt = date('Y-m-d H:i:s');

            if (!$this->airportRepository->update($airport)) {
                throw new \Exception("Failed to update airport.");
            }

            return $airport;
        } catch (PDOException $e) {
            error_log("Database error in updateAirport: " . $e->getMessage());
            throw new \Exception("Failed to update airport due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in updateAirport: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete an airport.
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deleteAirport(int $id): bool
    {
        try {
            $airport = $this->airportRepository->findById($id);
            if (!$airport) {
                throw new NotFoundException("Airport with ID {$id} not found.");
            }

            if (!$this->airportRepository->delete($id)) {
                throw new \Exception("Failed to delete airport from the database.");
            }

            return true;
        } catch (PDOException $e) {
            error_log("Database error in deleteAirport: " . $e->getMessage());
            throw new \Exception("Failed to delete airport due to a database error.");
        } catch (\Exception $e) {
            error_log("Error in deleteAirport: " . $e->getMessage());
            throw $e;
        }
    }
}