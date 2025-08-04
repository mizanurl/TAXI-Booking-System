<?php

namespace App\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Airport",
    title: "Airport",
    description: "Airport model representing a location with associated tax/toll.",
    properties: [
        new OA\Property(property: "id", type: "integer", format: "int64", readOnly: true, example: 1),
        new OA\Property(property: "name", type: "string", example: "Dhaka International Airport"),
        new OA\Property(property: "description", type: "string", nullable: true, example: "Main international airport in Bangladesh."),
        new OA\Property(property: "from_tax_toll", type: "number", format: "float", example: 5.50),
        new OA\Property(property: "to_tax_toll", type: "number", format: "float", example: 6.00),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], description: "0 = Inactive, 1 = Active", example: 1),
        new OA\Property(property: "created_at", type: "string", format: "date-time", readOnly: true),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", readOnly: true)
    ],
    required: ["name", "from_tax_toll", "to_tax_toll", "status"] // latitude, longitude, place_id, zip_code are nullable
)]
class Airport
{
    /**
     * Status of the airport:
     * 0 = Inactive
     * 1 = Active
     */
    public function __construct(
        public ?int $id,
        public string $name,
        public string $description,
        public float $fromTaxToll,
        public float $toTaxToll,
        public int $status,
        public string $createdAt = '',
        public string $updatedAt = ''
    ) {
        if (empty($this->createdAt)) {
            $this->createdAt = date('Y-m-d H:i:s');
        }
        if (empty($this->updatedAt)) {
            $this->updatedAt = date('Y-m-d H:i:s');
        }
    }

    /**
     * Creates an Airport object from an associative array (e.g., from database result).
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['name'] ?? '',
            $data['description'] ?? '',
            (float) ($data['from_tax_toll'] ?? 0.0),
            (float) ($data['to_tax_toll'] ?? 0.0),
            (int) ($data['status'] ?? 1),
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null
        );
    }

    /**
     * Converts the Airport object to an associative array for database insertion/update.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'from_tax_toll' => number_format($this->fromTaxToll, 2, '.', ''),
            'to_tax_toll' => number_format($this->toTaxToll, 2, '.', ''),
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
    
    // Getters for properties
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getDescription(): ?string { return $this->description; }
    public function getFromTaxToll(): float { return $this->fromTaxToll; }
    public function getToTaxToll(): float { return $this->toTaxToll; }
    public function getStatus(): int { return $this->status; }
}