<?php

namespace App\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Slab Settings",
    title: "Slab Settings",
    description: "Represents a pricing slab for distance or hourly services.",
    properties: [
        new OA\Property(property: "id", type: "integer", format: "int64", readOnly: true, example: 1),
        new OA\Property(property: "slab_value", type: "number", format: "float", example: 6.00, description: "The value of the slab (e.g., price per mile/hour)."),
        new OA\Property(property: "slab_unit", type: "integer", enum: [0, 1], example: 0, description: "Unit of the slab: 0 = Mile, 1 = Hour."),
        new OA\Property(property: "slab_type", type: "integer", enum: [0, 1], example: 0, description: "Type of service: 0 = Distance, 1 = HourlyService."),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], example: 1, description: "Status of the slab: 0 = Inactive, 1 = Active."),
        new OA\Property(property: "created_at", type: "string", format: "date-time", readOnly: true, example: "2025-07-25 10:00:00"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", readOnly: true, example: "2025-07-25 10:00:00")
    ],
    required: ["slab_value", "slab_unit", "slab_type", "status"]
)]
class Slab
{
    public ?int $id;
    public float $slabValue;
    public int $slabUnit; // 0 for Mile, 1 for Hour
    public int $slabType; // 0 for Distance, 1 for Hourly Service
    public int $status; // 0 for inactive, 1 for active
    public string $createdAt;
    public string $updatedAt;

    public function __construct(
        ?int $id,
        float $slabValue,
        int $slabUnit,
        int $slabType,
        int $status,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->slabValue = $slabValue;
        $this->slabUnit = $slabUnit;
        $this->slabType = $slabType;
        $this->status = $status;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
        $this->updatedAt = $updatedAt ?? date('Y-m-d H:i:s');
    }

    /**
     * Creates an Slab object from an associative array (e.g., from database result).
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            (float) $data['slab_value'],
            (int) $data['slab_unit'],
            (int) $data['slab_type'],
            (int) $data['status'],
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null
        );
    }

    /**
     * Converts the Slab object to an associative array for API response.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'slab_value' => number_format($this->slabValue, 2, '.', ''), // Format to two decimal points
            'slab_unit' => $this->slabUnit,
            'slab_type' => $this->slabType,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    /**
     * Converts the Slab object to an associative array for database insertion/update.
     * @return array
     */
    public function toDatabaseArray(): array
    {
        return [
            'id' => $this->id,
            'slab_value' => $this->slabValue,
            'slab_unit' => $this->slabUnit,
            'slab_type' => $this->slabType,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}