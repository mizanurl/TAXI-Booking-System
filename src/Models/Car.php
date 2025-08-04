<?php

namespace App\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Car Features",
    title: "Car Features",
    description: "Represents a car vehicle available for booking.",
    properties: [
        new OA\Property(property: "id", type: "integer", format: "int64", readOnly: true, example: 1),
        new OA\Property(property: "regular_name", type: "string", example: "2 Passenger Luxury Minivan", description: "Full name of the car."),
        new OA\Property(property: "short_name", type: "string", nullable: true, example: "2pv Luxury Vehicle", description: "Short name or alias for the car."),
        new OA\Property(property: "color", type: "string", example: "Silver", description: "Color of the car."),
        new OA\Property(property: "car_photo", type: "string", example: "http://localhost:8000/uploads/cars/1721904000.webp", description: "Absolute URL to the car's photo."),
        new OA\Property(property: "car_features", type: "string", example: "Air Conditioning, GPS, Bluetooth", description: "Features of the car, can be HTML content."),
        new OA\Property(property: "base_fare", type: "number", format: "float", example: 25.00, description: "Base fare for the car."),
        new OA\Property(property: "minimum_fare", type: "number", format: "float", example: 60.00, description: "Minimum fare for the car."),
        new OA\Property(property: "small_luggage_capacity", type: "integer", example: 1, description: "Capacity for small luggage."),
        new OA\Property(property: "large_luggage_capacity", type: "integer", example: 2, description: "Capacity for large luggage."),
        new OA\Property(property: "extra_luggage_capacity", type: "integer", example: 0, description: "Capacity for extra luggage."),
        new OA\Property(property: "num_of_passengers", type: "integer", example: 2, description: "Maximum number of passengers."),
        new OA\Property(property: "is_child_seat", type: "integer", enum: [0, 1], example: 1, description: "Child seat availability: 0 = Not Available, 1 = Available."),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], example: 1, description: "Status of the car: 0 = Inactive, 1 = Active."),
        new OA\Property(property: "created_at", type: "string", format: "date-time", readOnly: true, example: "2025-07-25 10:00:00"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", readOnly: true, example: "2025-07-25 10:00:00")
    ],
    required: [
        "regular_name", "color", "car_photo", "car_features", "base_fare",
        "minimum_fare", "small_luggage_capacity", "large_luggage_capacity",
        "num_of_passengers", "is_child_seat", "status"
    ]
)]
class Car
{
    public ?int $id;
    public string $regularName;
    public string $color;
    public string $carPhoto; // This property should ALWAYS hold just the filename (e.g., 1721904000.webp)
    public string $carFeatures;
    public float $baseFare;
    public float $minimumFare;
    public int $smallLuggageCapacity;
    public int $largeLuggageCapacity;
    public int $extraLuggageCapacity;
    public int $numOfPassengers;
    public int $isChildSeat; // 0 for Not Available, 1 for Available
    public int $status; // 0 for inactive, 1 for active
    public ?string $shortName; // Optional
    public string $createdAt;
    public string $updatedAt;

    public function __construct(
        ?int $id,
        string $regularName,
        string $color,
        string $carPhoto,
        string $carFeatures,
        float $baseFare,
        float $minimumFare,
        int $smallLuggageCapacity,
        int $largeLuggageCapacity,
        int $extraLuggageCapacity,
        int $numOfPassengers,
        int $isChildSeat,
        int $status,
        ?string $shortName = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->regularName = $regularName;
        $this->color = $color;
        $this->carPhoto = $carPhoto;
        $this->carFeatures = $carFeatures;
        $this->baseFare = $baseFare;
        $this->minimumFare = $minimumFare;
        $this->smallLuggageCapacity = $smallLuggageCapacity;
        $this->largeLuggageCapacity = $largeLuggageCapacity;
        $this->extraLuggageCapacity = $extraLuggageCapacity;
        $this->numOfPassengers = $numOfPassengers;
        $this->isChildSeat = $isChildSeat;
        $this->status = $status;
        $this->shortName = $shortName;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
        $this->updatedAt = $updatedAt ?? date('Y-m-d H:i:s');
    }

    /**
     * Creates a Car object from an associative array (e.g., from database result).
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $carPhoto = $data['car_photo'] ?? null;
        // If the database returns a full URL, extract just the filename
        if ($carPhoto && str_contains($carPhoto, 'http')) {
            $parts = explode('/', $carPhoto);
            $carPhoto = end($parts); // Get the last part (filename)
        }

        return new self(
            $data['id'] ?? null,
            $data['regular_name'] ?? '',
            $data['color'] ?? '',
            $carPhoto ?? '', // Ensure only filename is passed here
            $data['car_features'] ?? '',
            (float) ($data['base_fare'] ?? 0.0),
            (float) ($data['minimum_fare'] ?? 0.0),
            (int) ($data['small_luggage_capacity'] ?? 0),
            (int) ($data['large_luggage_capacity'] ?? 0),
            (int) ($data['extra_luggage_capacity'] ?? 0),
            (int) ($data['num_of_passengers'] ?? 0),
            (int) ($data['is_child_seat'] ?? 0),
            (int) ($data['status'] ?? 0),
            $data['short_name'] ?? null,
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null
        );
    }

    /**
     * Converts the Car object to an associative array for API response.
     * @return array
     */
    public function toArray(): array
    {
        // Define the relative upload directory for car photo
        // This should match CarService::CAR_PHOTO_UPLOAD_DIR_RELATIVE
        $carPhotoUploadDirRelative = 'uploads/cars/';

        return [
            'id' => $this->id,
            'regular_name' => $this->regularName,
            'short_name' => $this->shortName,
            'color' => $this->color,
            // Construct absolute URL for car_photo ONLY for API response
            'car_photo' => $this->carPhoto ? (getenv('APP_URL') ?: 'http://localhost:8000') . '/' . trim($carPhotoUploadDirRelative, '/\\') . '/' . $this->carPhoto : null,
            'car_features' => $this->carFeatures,
            'base_fare' => number_format($this->baseFare, 2, '.', ''),
            'minimum_fare' => number_format($this->minimumFare, 2, '.', ''),
            'small_luggage_capacity' => $this->smallLuggageCapacity,
            'large_luggage_capacity' => $this->largeLuggageCapacity,
            'extra_luggage_capacity' => $this->extraLuggageCapacity,
            'num_of_passengers' => $this->numOfPassengers,
            'is_child_seat' => $this->isChildSeat,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    /**
     * Converts the Car object to an associative array for database insertion/update.
     * This method is specifically for saving to the database, so car_photo will be just the filename.
     * @return array
     */
    public function toDatabaseArray(): array
    {
        return [
            'id' => $this->id,
            'regular_name' => $this->regularName,
            'short_name' => $this->shortName,
            'color' => $this->color,
            'car_photo' => $this->carPhoto, // Store just the filename
            'car_features' => $this->carFeatures,
            'base_fare' => $this->baseFare, // Store as float, not formatted string
            'minimum_fare' => $this->minimumFare, // Store as float, not formatted string
            'small_luggage_capacity' => $this->smallLuggageCapacity,
            'large_luggage_capacity' => $this->largeLuggageCapacity,
            'extra_luggage_capacity' => $this->extraLuggageCapacity,
            'num_of_passengers' => $this->numOfPassengers,
            'is_child_seat' => $this->isChildSeat,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}