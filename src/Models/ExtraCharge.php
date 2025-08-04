<?php

namespace App\Models;

use OpenApi\Attributes as OA; // Import OpenApi Attributes

#[OA\Schema(
    schema: "ExtraCharge",
    title: "Extra Charge",
    description: "Represents an extra charge or toll for a specific area defined by zip codes.",
    properties: [
        new OA\Property(property: "id", type: "integer", format: "int64", readOnly: true, example: 1),
        new OA\Property(property: "area_name", type: "string", example: "Downtown Boston", description: "Name of the area where the extra charge applies."),
        new OA\Property(property: "zip_codes", type: "string", example: "02108,02109,02110", description: "Comma-separated list of zip codes for the area."),
        new OA\Property(property: "extra_charge", type: "number", format: "float", example: 2.50, description: "The primary extra charge amount."),
        new OA\Property(property: "extra_toll_charge", type: "number", format: "float", example: 1.75, description: "Additional toll charge for the area."),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], description: "Status of the extra charge: 0 = Inactive, 1 = Active", example: 1),
        new OA\Property(property: "created_at", type: "string", format: "date-time", readOnly: true, example: "2025-07-26 10:00:00"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", readOnly: true, example: "2025-07-26 10:00:00")
    ],
    required: ["area_name", "zip_codes", "extra_charge", "extra_toll_charge", "status"]
)]
class ExtraCharge
{
    public ?int $id;
    public string $areaName;
    public string $zipCodes;
    public float $extraCharge;
    public float $extraTollCharge;
    public int $status; // 0 for inactive, 1 for active
    public string $createdAt;
    public string $updatedAt;

    public function __construct(
        ?int $id,
        string $areaName,
        string $zipCodes,
        float $extraCharge,
        float $extraTollCharge,
        int $status,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->areaName = $areaName;
        $this->zipCodes = $zipCodes;
        $this->extraCharge = $extraCharge;
        $this->extraTollCharge = $extraTollCharge;
        $this->status = $status;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
        $this->updatedAt = $updatedAt ?? date('Y-m-d H:i:s');
    }

    /**
     * Creates an ExtraCharge object from an associative array (e.g., from database result).
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['area_name'],
            $data['zip_codes'],
            (float) $data['extra_charge'],
            (float) $data['extra_toll_charge'],
            (int) $data['status'],
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null
        );
    }

    /**
     * Converts the ExtraCharge object to an associative array for API response.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'area_name' => $this->areaName,
            'zip_codes' => $this->zipCodes,
            'extra_charge' => number_format($this->extraCharge, 2, '.', ''), // Format for API response
            'extra_toll_charge' => number_format($this->extraTollCharge, 2, '.', ''), // Format for API response
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    /**
     * Converts the ExtraCharge object to an associative array for database insertion/update.
     * This version ensures float values are not string-formatted for database operations.
     * @return array
     */
    public function toDatabaseArray(): array
    {
        return [
            'id' => $this->id,
            'area_name' => $this->areaName,
            'zip_codes' => $this->zipCodes,
            'extra_charge' => $this->extraCharge, // Keep as float for DB
            'extra_toll_charge' => $this->extraTollCharge, // Keep as float for DB
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}