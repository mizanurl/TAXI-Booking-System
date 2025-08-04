<?php

namespace App\Models;

use OpenApi\Attributes as OA; // Import OpenApi Attributes

#[OA\Schema(
    schema: "SmsService",
    title: "SMS Service",
    description: "Represents an SMS service entry with a phone number and status.",
    properties: [
        new OA\Property(property: "id", type: "integer", format: "int64", readOnly: true, example: 1),
        new OA\Property(property: "phone_number", type: "string", example: "+1234567890", description: "The phone number for the SMS service."),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], description: "Status of the SMS service: 0 = Inactive, 1 = Active", example: 1),
        new OA\Property(property: "created_at", type: "string", format: "date-time", readOnly: true, example: "2025-07-26 10:00:00"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", readOnly: true, example: "2025-07-26 10:00:00")
    ],
    required: ["phone_number", "status"]
)]
class SmsService
{
    public ?int $id;
    public string $phoneNumber;
    public int $status; // 0 for inactive, 1 for active
    public string $createdAt;
    public string $updatedAt;

    public function __construct(
        ?int $id,
        string $phoneNumber,
        int $status,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->phoneNumber = $phoneNumber;
        $this->status = $status;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
        $this->updatedAt = $updatedAt ?? date('Y-m-d H:i:s');
    }

    /**
     * Creates an SmsService object from an associative array (e.g., from database result).
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['phone_number'],
            (int) $data['status'],
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null
        );
    }

    /**
     * Converts the SmsService object to an associative array for API response.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'phone_number' => $this->phoneNumber,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    /**
     * Converts the SmsService object to an associative array for database insertion/update.
     * @return array
     */
    public function toDatabaseArray(): array
    {
        return [
            'id' => $this->id,
            'phone_number' => $this->phoneNumber,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}