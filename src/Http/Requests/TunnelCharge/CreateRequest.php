<?php

namespace App\Http\Requests\TunnelCharge;

use App\Http\Requests\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "CreateTunnelChargeRequest",
    title: "Create Tunnel Charge Request",
    properties: [
        new OA\Property(property: "charge_start_date", type: "string", format: "date", example: "2025-01-01", description: "The start date from which the charge is applicable (YYYY-MM-DD)."),
        new OA\Property(property: "charge_end_date", type: "string", format: "date", example: "2025-12-31", description: "The end date until which the charge is applicable (YYYY-MM-DD)."),
        new OA\Property(property: "charge_amount", type: "number", format: "float", example: 10.50, description: "The amount of the tunnel charge."),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], example: 1, description: "Status of the tunnel charge: 0 = Inactive, 1 = Active.")
    ],
    required: ["charge_start_date", "charge_end_date", "charge_amount", "status"]
)]
class CreateRequest extends FormRequest
{
    /**
     * Define the validation rules for creating a new tunnel charge.
     * @return array
     */
    protected function rules(): array
    {
        return [
            'charge_start_date' => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}$/'], // YYYY-MM-DD format
            'charge_end_date' => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}$/'], // YYYY-MM-DD format
            'charge_amount' => ['required', 'numeric', 'min:0'],
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
            'charge_start_date.required' => 'Charge start date is required.',
            'charge_start_date.string' => 'Charge start date must be a string.',
            'charge_start_date.regex' => 'Charge start date must be in YYYY-MM-DD format.',
            'charge_end_date.required' => 'Charge end date is required.',
            'charge_end_date.string' => 'Charge end date must be a string.',
            'charge_end_date.regex' => 'Charge end date must be in YYYY-MM-DD format.',
            'charge_amount.required' => 'Charge amount is required.',
            'charge_amount.numeric' => 'Charge amount must be a number.',
            'charge_amount.min' => 'Charge amount must be at least 0.',
            'status.required' => 'Status is required.',
            'status.integer' => 'Status must be an integer.',
            'status.in' => 'Status must be either 0 (Inactive) or 1 (Active).',
        ];
    }
}