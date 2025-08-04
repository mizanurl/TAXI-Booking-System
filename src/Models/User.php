<?php

namespace App\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "User",
    title: "User",
    description: "Represents a user in the system, typically an administrator.",
    properties: [
        new OA\Property(property: "id", type: "integer", format: "int64", readOnly: true, example: 1),
        new OA\Property(property: "name", type: "string", example: "Admin User"),
        new OA\Property(property: "email", type: "string", format: "email", example: "admin@example.com"),
        new OA\Property(property: "password", type: "string", writeOnly: true, description: "Hashed password of the user."), // Password should not be read directly
        new OA\Property(property: "status", type: "integer", enum: [0, 1], description: "User status: 0 = Inactive, 1 = Active", example: 1),
        new OA\Property(property: "created_at", type: "string", format: "date-time", readOnly: true),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", readOnly: true)
    ],
    required: ["name", "email", "password", "status"]
)]
class User
{
    public ?int $id;
    public string $name;
    public string $email;
    public string $password; // This will store the hashed password
    public int $status; // 0 for inactive, 1 for active
    public string $createdAt;
    public string $updatedAt;

    public function __construct(
        ?int $id,
        string $name,
        string $email,
        string $password, // Expects hashed password
        int $status,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->status = $status;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
        $this->updatedAt = $updatedAt ?? date('Y-m-d H:i:s');
    }

    /**
     * Creates a User object from an associative array (e.g., from database result).
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['name'],
            $data['email'],
            $data['password'],
            (int) $data['status'],
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null
        );
    }

    /**
     * Converts the User object to an associative array for API response.
     * Note: Does NOT include the password for security.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    /**
     * Converts the User object to an associative array for database insertion/update.
     * Includes the password.
     * @return array
     */
    public function toDatabaseArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password, // Include hashed password for DB operations
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}