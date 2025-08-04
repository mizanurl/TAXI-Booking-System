<?php

namespace App\Http\Requests\Car;

use App\Http\Requests\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "CarUpdateRequest",
    title: "Car Update Request",
    properties: [
        new OA\Property(property: "regular_name", type: "string", nullable: true, example: "Updated 4 Passenger Luxury Vehicle", description: "Full name of the car."),
        new OA\Property(property: "short_name", type: "string", nullable: true, example: "Updated 4pv Luxury Vehicle", description: "Short name or alias for the car."),
        new OA\Property(property: "color", type: "string", nullable: true, example: "Blue", description: "Color of the car."),
        new OA\Property(
            property: "car_photo",
            type: "string",
            format: "binary",
            nullable: true,
            description: "Car photo file. Max 100KB, max dimensions 300x200px. Allowed types: jpg, gif, png, webp. Send null to remove, omit to keep existing."
        ),
        new OA\Property(property: "car_features", type: "string", nullable: true, example: "Updated features: Leather Seats, Sunroof", description: "Features of the car, can be HTML content."),
        new OA\Property(property: "base_fare", type: "number", format: "float", nullable: true, example: 38.00, description: "Base fare for the car."),
        new OA\Property(property: "minimum_fare", type: "number", format: "float", nullable: true, example: 105.00, description: "Minimum fare for the car."),
        new OA\Property(property: "small_luggage_capacity", type: "integer", nullable: true, example: 2, description: "Capacity for small luggage."),
        new OA\Property(property: "large_luggage_capacity", type: "integer", nullable: true, example: 3, description: "Capacity for large luggage."),
        new OA\Property(property: "extra_luggage_capacity", type: "integer", nullable: true, example: 1, description: "Capacity for extra luggage."),
        new OA\Property(property: "num_of_passengers", type: "integer", nullable: true, example: 5, description: "Maximum number of passengers."),
        new OA\Property(property: "is_child_seat", type: "integer", enum: [0, 1], nullable: true, example: 0, description: "Child seat availability: 0 = Not Available, 1 = Available."),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], nullable: true, example: 0, description: "Status of the car: 0 = Inactive, 1 = Active.")
    ]
)]
class UpdateRequest extends FormRequest
{
    /**
     * Define the validation rules for updating an existing car.
     * All fields are nullable as they might not be provided in a partial update.
     * @return array
     */
    protected function rules(): array
    {
        return [
            'regular_name' => ['nullable', 'string', 'max:100'],
            'short_name' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:30'],
            'car_photo' => ['nullable', 'image', 'mimes:jpg,gif,png,webp', 'max_size:100', 'dimensions:max_width=300,max_height=200'], // 100 KB
            'car_features' => ['nullable', 'string'],
            'base_fare' => ['nullable', 'numeric', 'min:0'],
            'minimum_fare' => ['nullable', 'numeric', 'min:0'],
            'small_luggage_capacity' => ['nullable', 'integer', 'min:0'],
            'large_luggage_capacity' => ['nullable', 'integer', 'min:0'],
            'extra_luggage_capacity' => ['nullable', 'integer', 'min:0'],
            'num_of_passengers' => ['nullable', 'integer', 'min:1'],
            'is_child_seat' => ['nullable', 'integer', 'in:0,1'],
            'status' => ['nullable', 'integer', 'in:0,1'],
        ];
    }

    /**
     * Define custom validation messages.
     * @return array
     */
    protected function messages(): array
    {
        return [
            'car_photo.max_size' => 'The car photo may not be greater than 100 KB.',
            'car_photo.dimensions' => 'The car photo dimensions must not exceed 300x200 pixels.',
        ];
    }
}