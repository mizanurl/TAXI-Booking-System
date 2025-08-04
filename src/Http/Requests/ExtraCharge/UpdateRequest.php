<?php

namespace App\Http\Requests\ExtraCharge;

use App\Http\Requests\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UpdateExtraChargeRequest",
    title: "Update Extra Charge Request",
    properties: [
        new OA\Property(property: "area_name", type: "string", nullable: true, example: "Updated Downtown Boston", description: "Name of the area where the extra charge applies."),
        new OA\Property(property: "zip_codes", type: "string", nullable: true, example: "02108,02111", description: "Comma-separated list of zip codes for the area."),
        new OA\Property(property: "extra_charge", type: "number", format: "float", nullable: true, example: 3.00, description: "The primary extra charge amount."),
        new OA\Property(property: "extra_toll_charge", type: "number", format: "float", nullable: true, example: 2.00, description: "Additional toll charge for the area."),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], nullable: true, example: 0, description: "Status of the extra charge: 0 = Inactive, 1 = Active.")
    ]
)]
class UpdateRequest extends FormRequest
{
    /**
     * Define the validation rules for updating an existing extra charge.
     * All fields are nullable as they might not be provided in a partial update.
     * @return array
     */
    protected function rules(): array
    {
        return [
            'area_name' => ['nullable', 'string', 'min:3', 'max:100'],
            'zip_codes' => ['nullable', 'string', 'min:1'],
            'extra_charge' => ['nullable', 'numeric', 'min:0'],
            'extra_toll_charge' => ['nullable', 'numeric', 'min:0'],
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
            'area_name.string' => 'Area name must be a string.',
            'area_name.min' => 'Area name must be at least 3 characters.',
            'area_name.max' => 'Area name may not be greater than 100 characters.',
            'zip_codes.string' => 'Zip codes must be a string.',
            'zip_codes.min' => 'Zip codes must contain at least 1 character.',
            'extra_charge.numeric' => 'Extra charge must be a number.',
            'extra_charge.min' => 'Extra charge must be at least 0.',
            'extra_toll_charge.numeric' => 'Extra toll charge must be a number.',
            'extra_toll_charge.min' => 'Extra toll charge must be at least 0.',
            'status.integer' => 'Status must be an integer.',
            'status.in' => 'Status must be either 0 (Inactive) or 1 (Active).',
        ];
    }
}