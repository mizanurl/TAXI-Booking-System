<?php

namespace App\Http\Requests\Car;

use App\Http\Requests\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "CarCreateRequest",
    title: "Car Create Request",
    required: [
        "regular_name", "color", "car_photo", "car_features", "base_fare",
        "minimum_fare", "small_luggage_capacity", "large_luggage_capacity",
        "num_of_passengers", "is_child_seat", "status"
    ],
    properties: [
        new OA\Property(property: "regular_name", type: "string", example: "4 Passenger Luxury Vehicle", description: "Full name of the car."),
        new OA\Property(property: "short_name", type: "string", nullable: true, example: "4pv Luxury Vehicle", description: "Short name or alias for the car."),
        new OA\Property(property: "color", type: "string", example: "Black", description: "Color of the car."),
        new OA\Property(
            property: "car_photo",
            type: "string",
            format: "binary",
            description: "Car photo file. Max 100KB, max dimensions 300x200px. Allowed types: jpg, gif, png, webp."
        ),
        new OA\Property(property: "car_features", type: "string", example: "Air Conditioning, GPS, Bluetooth", description: "Features of the car, can be HTML content."),
        new OA\Property(property: "base_fare", type: "number", format: "float", example: 35.00, description: "Base fare for the car."),
        new OA\Property(property: "minimum_fare", type: "number", format: "float", example: 99.00, description: "Minimum fare for the car."),
        new OA\Property(property: "small_luggage_capacity", type: "integer", example: 1, description: "Capacity for small luggage."),
        new OA\Property(property: "large_luggage_capacity", type: "integer", example: 4, description: "Capacity for large luggage."),
        new OA\Property(property: "extra_luggage_capacity", type: "integer", example: 0, description: "Capacity for extra luggage."),
        new OA\Property(property: "num_of_passengers", type: "integer", example: 4, description: "Maximum number of passengers."),
        new OA\Property(property: "is_child_seat", type: "integer", enum: [0, 1], example: 1, description: "Child seat availability: 0 = Not Available, 1 = Available."),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], example: 1, description: "Status of the car: 0 = Inactive, 1 = Active.")
    ]
)]
class CreateRequest extends FormRequest
{
    /**
     * Define the validation rules for creating a new car.
     * @return array
     */
    protected function rules(): array
    {
        return [
            'regular_name' => ['required', 'string', 'max:100'],
            'short_name' => ['nullable', 'string', 'max:100'],
            'color' => ['required', 'string', 'max:30'],
            'car_photo' => ['required', 'image', 'mimes:jpg,gif,png,webp', 'max_size:100', 'dimensions:max_width=300,max_height=200'], // 100 KB
            'car_features' => ['required', 'string'],
            'base_fare' => ['required', 'numeric', 'min:0'],
            'minimum_fare' => ['required', 'numeric', 'min:0'],
            'small_luggage_capacity' => ['required', 'integer', 'min:0'],
            'large_luggage_capacity' => ['required', 'integer', 'min:0'],
            'extra_luggage_capacity' => ['required', 'integer', 'min:0'],
            'num_of_passengers' => ['required', 'integer', 'min:1'],
            'is_child_seat' => ['required', 'integer', 'in:0,1'],
            'status' => ['required', 'integer', 'in:0,1'],
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