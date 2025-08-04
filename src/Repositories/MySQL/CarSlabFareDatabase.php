<?php

namespace App\Repositories\MySQL;

use App\Models\CarSlabFare;
use App\Repositories\Contracts\CarSlabFareInterface;
use PDO;
use PDOException;

class CarSlabFareDatabase implements CarSlabFareInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all car slab fares for a specific car.
     * @param int $carId
     * @return CarSlabFare[]
     * @throws PDOException
     */
    public function getByCarId(int $carId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM car_slab_fares WHERE car_id = :car_id");
        $stmt->bindParam(':car_id', $carId, PDO::PARAM_INT);
        $stmt->execute();
        $faresData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $fares = [];
        foreach ($faresData as $fareData) {
            $fares[] = CarSlabFare::fromArray($fareData);
        }
        return $fares;
    }

    /**
     * Find a car slab fare by its ID.
     * @param int $id
     * @return CarSlabFare|null
     * @throws PDOException
     */
    public function findById(int $id): ?CarSlabFare
    {
        $stmt = $this->db->prepare("SELECT * FROM car_slab_fares WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $fareData = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fareData ? CarSlabFare::fromArray($fareData) : null;
    }

    /**
     * Create a new car slab fare entry.
     * @param CarSlabFare $carSlabFare
     * @return int The ID of the newly created entry.
     * @throws PDOException
     */
    public function create(CarSlabFare $carSlabFare): int
    {
        $data = $carSlabFare->toDatabaseArray(); // Use toDatabaseArray for insertion

        $sql = "INSERT INTO car_slab_fares (car_id, slab_id, fare_amount, status, created_at, updated_at)
                VALUES (:car_id, :slab_id, :fare_amount, :status, :created_at, :updated_at)";
        $stmt = $this->db->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':car_id', $data['car_id'], PDO::PARAM_INT);
        $stmt->bindParam(':slab_id', $data['slab_id'], PDO::PARAM_INT);
        $stmt->bindParam(':fare_amount', $data['fare_amount']);
        $stmt->bindParam(':status', $data['status'], PDO::PARAM_INT);
        $stmt->bindParam(':created_at', $data['created_at']);
        $stmt->bindParam(':updated_at', $data['updated_at']);

        $stmt->execute();
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing car slab fare entry.
     * @param CarSlabFare $carSlabFare
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function update(CarSlabFare $carSlabFare): bool
    {
        $data = $carSlabFare->toDatabaseArray();
        if (!isset($data['id'])) {
            throw new PDOException("CarSlabFare ID is required for update.");
        }

        $sql = "UPDATE car_slab_fares SET
                    car_id = :car_id,
                    slab_id = :slab_id,
                    fare_amount = :fare_amount,
                    status = :status,
                    updated_at = :updated_at
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
        $stmt->bindParam(':car_id', $data['car_id'], PDO::PARAM_INT);
        $stmt->bindParam(':slab_id', $data['slab_id'], PDO::PARAM_INT);
        $stmt->bindParam(':fare_amount', $data['fare_amount']);
        $stmt->bindParam(':status', $data['status'], PDO::PARAM_INT);
        $stmt->bindParam(':updated_at', $data['updated_at']);

        return $stmt->execute();
    }

    /**
     * Delete a car slab fare by its ID.
     * @param int $id
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM car_slab_fares WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Delete all car slab fares associated with a specific car ID.
     * @param int $carId
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function deleteByCarId(int $carId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM car_slab_fares WHERE car_id = :car_id");
        $stmt->bindParam(':car_id', $carId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}