<?php

namespace App\Http\Requests\Airport;

use OpenApi\Attributes as OA;
use App\Http\Requests\FormRequest;

#[OA\Schema(
    schema: "CreateAirportRequest",
    title: "Create Airport Request",
    properties: [
        new OA\Property(property: "name", type: "string", example: "New City Airport"),
        new OA\Property(property: "description", type: "string", nullable: true, example: "A new airport serving the city."),
        new OA\Property(property: "from_tax_toll", type: "number", format: "float", example: 3.25),
        new OA\Property(property: "to_tax_toll", type: "number", format: "float", example: 3.75),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], example: 1)
    ],
    required: ["name", "from_tax_toll", "to_tax_toll", "status"]
)]
class CreateRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'name' => 'required|min:3|max:100',
            'description' => 'nullable|max:1000',
            'from_tax_toll' => 'required|numeric|min:0',
            'to_tax_toll' => 'required|numeric|min:0',
            'status' => 'required|numeric|min:0|max:1',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Airport name is required.',
            'name.min' => 'Airport name must be at least 3 characters.',
            'from_tax_toll.required' => 'From tax toll is required.',
            'from_tax_toll.numeric' => 'From tax toll must be a number.',
            'to_tax_toll.required' => 'To tax toll is required.',
            'to_tax_toll.numeric' => 'To tax toll must be a number.',
            'status.required' => 'Status is required.',
            'status.numeric' => 'Status must be 0 or 1.',
        ];
    }
}