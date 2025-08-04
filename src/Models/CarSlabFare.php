<?php

namespace App\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "CarSlabFare",
    title: "Car Slab Fare",
    description: "Represents the fare amount for a specific car and slab combination.",
    properties: [
        new OA\Property(property: "id", type: "integer", format: "int64", readOnly: true, example: 1),
        new OA\Property(property: "car_id", type: "integer", format: "int64", example: 1, description: "The ID of the car."),
        new OA\Property(property: "slab_id", type: "integer", format: "int64", example: 1, description: "The ID of the associated slab."),
        new OA\Property(property: "fare_amount", type: "number", format: "float", example: 5.00, description: "The fare amount for this car-slab combination."),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], example: 1, description: "Status of the fare: 0 = Inactive, 1 = Active."),
        new OA\Property(property: "created_at", type: "string", format: "date-time", readOnly: true, example: "2025-07-25 10:00:00"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", readOnly: true, example: "2025-07-25 10:00:00")
    ],
    required: ["car_id", "slab_id", "fare_amount", "status"]
)]
class CarSlabFare
{
    public ?int $id;
    public int $carId;
    public int $slabId;
    public float $fareAmount;
    public int $status; // 0 for inactive, 1 for active
    public string $createdAt;
    public string $updatedAt;

    public function __construct(
        ?int $id,
        int $carId,
        int $slabId,
        float $fareAmount,
        int $status,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->carId = $carId;
        $this->slabId = $slabId;
        $this->fareAmount = $fareAmount;
        $this->status = $status;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
        $this->updatedAt = $updatedAt ?? date('Y-m-d H:i:s');
    }

    /**
     * Creates an CarSlabFare object from an associative array (e.g., from database result).
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            (int) $data['car_id'],
            (int) $data['slab_id'],
            (float) $data['fare_amount'],
            (int) $data['status'],
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null
        );
    }

    /**
     * Converts the CarSlabFare object to an associative array for API response and database insertion/update.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'car_id' => $this->carId,
            'slab_id' => $this->slabId,
            'fare_amount' => number_format($this->fareAmount, 2, '.', ''), // Format for API response
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    /**
     * Converts the CarSlabFare object to an associative array for database insertion/update.
     * @return array
     */
    public function toDatabaseArray(): array
    {
        return [
            'id' => $this->id,
            'car_id' => $this->carId,
            'slab_id' => $this->slabId,
            'fare_amount' => $this->fareAmount, // Store as float, not formatted string
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}