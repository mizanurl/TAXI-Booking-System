<?php

namespace App\Http\Requests\Airport;

use OpenApi\Attributes as OA;
use App\Http\Requests\FormRequest;

#[OA\Schema(
    schema: "UpdateAirportRequest",
    title: "Update Airport Request",
    properties: [
        new OA\Property(property: "name", type: "string", nullable: true, example: "Updated Airport Name"),
        new OA\Property(property: "description", type: "string", nullable: true, example: "Updated description."),
        new OA\Property(property: "from_tax_toll", type: "number", format: "float", nullable: true, example: 4.00),
        new OA\Property(property: "to_tax_toll", type: "number", format: "float", nullable: true, example: 4.50),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], nullable: true, example: 0)
    ]
)]
class UpdateRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'name' => 'nullable|min:3|max:100',
            'description' => 'nullable|max:1000',
            'from_tax_toll' => 'nullable|numeric|min:0',
            'to_tax_toll' => 'nullable|numeric|min:0',
            'status' => 'nullable|numeric|min:0|max:1',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.min' => 'Airport name must be at least 3 characters.',
            'from_tax_toll.numeric' => 'From tax toll must be a number.',
            'to_tax_toll.numeric' => 'To tax toll must be a number.',
            'status.numeric' => 'Status must be 0 or 1.',
        ];
    }
}