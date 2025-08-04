<?php

namespace App\Http\Requests\Slab;

use App\Http\Requests\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "SlabCreateRequest",
    title: "Slab Create Request",
    required: ["slab_value", "slab_unit", "slab_type", "status"],
    properties: [
        new OA\Property(property: "slab_value", type: "number", format: "float", example: 7.50, description: "The value of the slab (e.g., price per mile/hour)."),
        new OA\Property(property: "slab_unit", type: "integer", enum: [0, 1], example: 0, description: "Unit of the slab: 0 = Mile, 1 = Hour."),
        new OA\Property(property: "slab_type", type: "integer", enum: [0, 1], example: 0, description: "Type of service: 0 = Distance, 1 = Hourly Service."),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], example: 1, description: "Status of the slab: 0 = Inactive, 1 = Active.")
    ]
)]
class CreateRequest extends FormRequest
{
    /**
     * Define the validation rules for creating a new slab.
     * @return array
     */
    protected function rules(): array
    {
        return [
            'slab_value' => ['required', 'numeric', 'min:0.01'],
            'slab_unit' => ['required', 'integer', 'in:0,1'], // 0 for Mile, 1 for Hour
            'slab_type' => ['required', 'integer', 'in:0,1'], // 0 for Distance, 1 for Hourly Service
            'status' => ['required', 'integer', 'in:0,1'], // 0 for inactive, 1 for active
        ];
    }

    /**
     * Define custom validation messages.
     * @return array
     */
    protected function messages(): array
    {
        return [
            'slab_value.required' => 'Slab value is required.',
            'slab_value.numeric' => 'Slab value must be a number.',
            'slab_value.min' => 'Slab value must be at least 0.01.',
            'slab_unit.required' => 'Slab unit is required.',
            'slab_unit.integer' => 'Slab unit must be an integer.',
            'slab_unit.in' => 'Slab unit must be either 0 (Mile) or 1 (Hour).',
            'slab_type.required' => 'Slab type is required.',
            'slab_type.integer' => 'Slab type must be an integer.',
            'slab_type.in' => 'Slab type must be either 0 (Distance) or 1 (Hourly Service).',
            'status.required' => 'Status is required.',
            'status.integer' => 'Status must be an integer.',
            'status.in' => 'Status must be either 0 (Inactive) or 1 (Active).',
        ];
    }
}