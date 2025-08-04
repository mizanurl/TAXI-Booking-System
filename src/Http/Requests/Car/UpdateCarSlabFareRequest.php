<?php

namespace App\Http\Requests\Car;

use App\Http\Requests\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UpdateCarSlabFareRequest",
    title: "Update Car Slab Fare Request",
    description: "Request body to update a specific car slab fare entry.",
    properties: [
        new OA\Property(property: "slab_id", type: "integer", format: "int64", nullable: true, example: 1, description: "The ID of the slab. Must exist in the 'slabs' table."),
        new OA\Property(property: "fare_amount", type: "number", format: "float", nullable: true, example: 5.50, description: "The updated fare amount for this slab."),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], nullable: true, example: 1, description: "Status of the fare: 0 = Inactive, 1 = Active.")
    ]
)]
class UpdateCarSlabFareRequest extends FormRequest
{
    /**
     * Define the validation rules for updating a car slab fare.
     * @return array
     */
    protected function rules(): array
    {
        return [
            'slab_id' => ['nullable', 'integer', 'exists:slabs,id'],
            'fare_amount' => ['nullable', 'numeric', 'min:0'],
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
            'slab_id.integer' => 'Slab ID must be an integer.',
            'slab_id.exists' => 'The provided Slab ID does not exist.',
            'fare_amount.numeric' => 'Fare amount must be a number.',
            'fare_amount.min' => 'Fare amount must be at least 0.',
            'status.integer' => 'Status must be an integer.',
            'status.in' => 'Status must be either 0 (Inactive) or 1 (Active).',
        ];
    }
}