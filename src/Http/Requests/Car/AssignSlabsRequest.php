<?php

namespace App\Http\Requests\Car;

use App\Http\Requests\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "CarAssignSlabsRequest",
    title: "Car Assign Slabs Request",
    description: "Request body to assign or update slabs and their fare amounts for a specific car. Multiple entries for the same slab_id are allowed if they represent different fare ranges or conditions.",
    properties: [
        new OA\Property(
            property: "slabs",
            type: "array",
            description: "An array of slab assignments for the car. Multiple entries for the same slab ID are permitted if they correspond to different fare conditions (e.g., mileage ranges defined by the slab itself).",
            items: new OA\Items(
                type: "object",
                required: ["slab_id", "fare_amount", "status"],
                properties: [
                    new OA\Property(property: "slab_id", type: "integer", format: "int64", example: 1, description: "The ID of the slab."),
                    new OA\Property(property: "fare_amount", type: "number", format: "float", example: 5.00, description: "The fare amount for this slab."),
                    new OA\Property(property: "status", type: "integer", enum: [0, 1], example: 1, description: "Status of the fare: 0 = Inactive, 1 = Active.")
                ]
            ),
            example: [
                ["slab_id" => 1, "fare_amount" => 5.00, "status" => 1], // Example: Fare amount below 6 miles
                ["slab_id" => 1, "fare_amount" => 5.00, "status" => 1], // Example: Fare amount from 6 miles & below 9 miles
                ["slab_id" => 2, "fare_amount" => 4.50, "status" => 1]
            ]
        )
    ],
    required: ["slabs"]
)]
class AssignSlabsRequest extends FormRequest
{
    /**
     * Define the validation rules for assigning slabs to a car.
     * @return array
     */
    protected function rules(): array
    {
        // Get all input data, including the 'slabs' array
        $data = $this->request->all();
        $rules = [
            'slabs' => ['required', 'array', 'min:1'], // Ensure 'slabs' itself is an array and not empty
        ];

        // Explicitly loop through each slab entry to apply nested rules
        if (isset($data['slabs']) && is_array($data['slabs'])) {
            foreach ($data['slabs'] as $index => $slabEntry) {
                $rules["slabs.{$index}.slab_id"] = ['required', 'integer', 'exists:slabs,id'];
                $rules["slabs.{$index}.fare_amount"] = ['required', 'numeric', 'min:0'];
                $rules["slabs.{$index}.status"] = ['required', 'integer', 'in:0,1'];
            }
        }

        return $rules;
    }

    /**
     * Define custom validation messages.
     * @return array
     */
    protected function messages(): array
    {
        return [
            'slabs.required' => 'At least one slab assignment is required.',
            'slabs.array' => 'Slabs must be an array.',
            'slabs.min' => 'At least one slab entry is required.', // Added message for min:1
            'slabs.*.slab_id.required' => 'Each slab entry must have a slab ID.',
            'slabs.*.slab_id.integer' => 'Each slab ID must be an integer.',
            'slabs.*.slab_id.exists' => 'One or more slab IDs do not exist.',
            'slabs.*.fare_amount.required' => 'Each slab entry must have a fare amount.',
            'slabs.*.fare_amount.numeric' => 'Each fare amount must be a number.',
            'slabs.*.fare_amount.min' => 'Each fare amount must be at least 0.',
            'slabs.*.status.required' => 'Each slab entry must have a status.',
            'slabs.*.status.integer' => 'Each status must be an integer.',
            'slabs.*.status.in' => 'Each status must be either 0 (Inactive) or 1 (Active).',
        ];
    }
}