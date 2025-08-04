<?php

namespace App\Http\Requests\ExtraCharge;

use App\Http\Requests\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "CreateExtraChargeRequest",
    title: "Create Extra Charge Request",
    properties: [
        new OA\Property(property: "area_name", type: "string", example: "Downtown Boston", description: "Name of the area where the extra charge applies."),
        new OA\Property(property: "zip_codes", type: "string", example: "02108,02109,02110", description: "Comma-separated list of zip codes for the area."),
        new OA\Property(property: "extra_charge", type: "number", format: "float", example: 2.50, description: "The primary extra charge amount."),
        new OA\Property(property: "extra_toll_charge", type: "number", format: "float", example: 1.75, description: "Additional toll charge for the area."),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], example: 1, description: "Status of the extra charge: 0 = Inactive, 1 = Active.")
    ],
    required: ["area_name", "zip_codes", "extra_charge", "extra_toll_charge", "status"]
)]
class CreateRequest extends FormRequest
{
    /**
     * Define the validation rules for creating a new extra charge.
     * @return array
     */
    protected function rules(): array
    {
        return [
            'area_name' => ['required', 'string', 'min:3', 'max:100'],
            'zip_codes' => ['required', 'string', 'min:1'], // Can be a single zip or comma-separated
            'extra_charge' => ['required', 'numeric', 'min:0'],
            'extra_toll_charge' => ['required', 'numeric', 'min:0'],
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
            'area_name.required' => 'Area name is required.',
            'area_name.string' => 'Area name must be a string.',
            'area_name.min' => 'Area name must be at least 3 characters.',
            'area_name.max' => 'Area name may not be greater than 100 characters.',
            'zip_codes.required' => 'Zip codes are required.',
            'zip_codes.string' => 'Zip codes must be a string.',
            'zip_codes.min' => 'Zip codes must contain at least 1 character.',
            'extra_charge.required' => 'Extra charge is required.',
            'extra_charge.numeric' => 'Extra charge must be a number.',
            'extra_charge.min' => 'Extra charge must be at least 0.',
            'extra_toll_charge.required' => 'Extra toll charge is required.',
            'extra_toll_charge.numeric' => 'Extra toll charge must be a number.',
            'extra_toll_charge.min' => 'Extra toll charge must be at least 0.',
            'status.required' => 'Status is required.',
            'status.integer' => 'Status must be an integer.',
            'status.in' => 'Status must be either 0 (Inactive) or 1 (Active).',
        ];
    }
}