<?php

namespace App\Models;

use OpenApi\Attributes as OA; // Import OpenApi Attributes

#[OA\Schema(
    schema: "TunnelCharge",
    title: "Tunnel Charge",
    description: "Represents a specific tunnel charge entry with start/end dates, amount, and status.",
    properties: [
        new OA\Property(property: "id", type: "integer", format: "int64", readOnly: true, example: 1),
        new OA\Property(property: "charge_start_date", type: "string", format: "date", example: "2025-01-01", description: "The start date from which the charge is applicable (YYYY-MM-DD)."),
        new OA\Property(property: "charge_end_date", type: "string", format: "date", example: "2025-12-31", description: "The end date until which the charge is applicable (YYYY-MM-DD)."),
        new OA\Property(property: "charge_amount", type: "number", format: "float", example: 10.50, description: "The amount of the tunnel charge."),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], description: "Status of the tunnel charge: 0 = Inactive, 1 = Active", example: 1),
        new OA\Property(property: "created_at", type: "string", format: "date-time", readOnly: true, example: "2025-07-26 10:00:00"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", readOnly: true, example: "2025-07-26 10:00:00")
    ],
    required: ["charge_start_date", "charge_end_date", "charge_amount", "status"]
)]
class TunnelCharge
{
    public ?int $id;
    public string $chargeStartDate;
    public string $chargeEndDate;
    public float $chargeAmount;
    public int $status; // 0 for inactive, 1 for active
    public string $createdAt;
    public string $updatedAt;

    public function __construct(
        ?int $id,
        string $chargeStartDate,
        string $chargeEndDate,
        float $chargeAmount,
        int $status,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->chargeStartDate = $chargeStartDate;
        $this->chargeEndDate = $chargeEndDate;
        $this->chargeAmount = $chargeAmount;
        $this->status = $status;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
        $this->updatedAt = $updatedAt ?? date('Y-m-d H:i:s');
    }

    /**
     * Creates an TunnelCharge object from an associative array (e.g., from database result).
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['charge_start_date'],
            $data['charge_end_date'],
            (float) $data['charge_amount'],
            (int) $data['status'],
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null
        );
    }

    /**
     * Converts the TunnelCharge object to an associative array for API response and database insertion/update.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'charge_start_date' => $this->chargeStartDate,
            'charge_end_date'   => $this->chargeEndDate,
            'charge_amount' => number_format($this->chargeAmount, 2, '.', ''), // Format to two decimal points for API
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    /**
     * Converts the TunnelCharge object to an associative array for database insertion/update.
     * This version ensures float values are not string-formatted for database operations.
     * @return array
     */
    public function toDatabaseArray(): array
    {
        return [
            'id' => $this->id,
            'charge_start_date' => $this->chargeStartDate,
            'charge_end_date'   => $this->chargeEndDate,
            'charge_amount' => $this->chargeAmount, // Keep as float for DB
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}