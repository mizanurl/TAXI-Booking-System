<?php

namespace App\Http\Requests\Slab;

use App\Http\Requests\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "SlabUpdateRequest",
    title: "Slab Update Request",
    properties: [
        new OA\Property(property: "slab_value", type: "number", format: "float", nullable: true, example: 8.00, description: "The value of the slab (e.g., price per mile/hour)."),
        new OA\Property(property: "slab_unit", type: "integer", enum: [0, 1], nullable: true, example: 1, description: "Unit of the slab: 0 = Mile, 1 = Hour."),
        new OA\Property(property: "slab_type", type: "integer", enum: [0, 1], nullable: true, example: 1, description: "Type of service: 0 = Distance, 1 = Hourly Service."),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], nullable: true, example: 0, description: "Status of the slab: 0 = Inactive, 1 = Active.")
    ]
)]
class UpdateRequest extends FormRequest
{
    /**
     * Define the validation rules for updating an existing slab.
     * All fields are nullable as they might not be provided in a partial update.
     * @return array
     */
    protected function rules(): array
    {
        return [
            'slab_value' => ['nullable', 'numeric', 'min:0.01'],
            'slab_unit' => ['nullable', 'integer', 'in:0,1'],
            'slab_type' => ['nullable', 'integer', 'in:0,1'],
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
            'slab_value.numeric' => 'Slab value must be a number.',
            'slab_value.min' => 'Slab value must be at least 0.01.',
            'slab_unit.integer' => 'Slab unit must be an integer.',
            'slab_unit.in' => 'Slab unit must be either 0 (Mile) or 1 (Hour).',
            'slab_type.integer' => 'Slab type must be an integer.',
            'slab_type.in' => 'Slab type must be either 0 (Distance) or 1 (Hourly Service).',
            'status.integer' => 'Status must be an integer.',
            'status.in' => 'Status must be either 0 (Inactive) or 1 (Active).',
        ];
    }
}