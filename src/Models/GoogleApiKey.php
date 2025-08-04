<?php

namespace App\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "GoogleApiKey",
    title: "GoogleApiKey",
    description: "Google API Key Model representing a Google API key.",
    properties: [
        new OA\Property(property: "id", type: "integer", format: "int64", readOnly: true, example: 1),
        new OA\Property(property: "api_key", type: "string", example: "A new API Key"),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], description: "0 = Inactive, 1 = Active", example: 1),
        new OA\Property(property: "created_at", type: "string", format: "date-time", readOnly: true),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", readOnly: true)
    ],
    required: ["api_key", "status"]
)]

class GoogleApiKey
{
    /**
     * Status of the key:
     * 0 = Inactive
     * 1 = Active
     */
    public function __construct(
        public ?int $id,
        public string $apiKey,
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
     * Creates an GoogleApiKey object from an associative array (e.g., from database result).
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['api_key'],
            (int) $data['status'],
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null
        );
    }

    /**
     * Converts the GoogleApiKey object to an associative array for database insertion/update.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'api_key' => $this->apiKey,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}