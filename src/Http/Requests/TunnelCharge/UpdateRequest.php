<?php

namespace App\Http\Requests\TunnelCharge;

use App\Http\Requests\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UpdateTunnelChargeRequest",
    title: "Update Tunnel Charge Request",
    properties: [
        new OA\Property(property: "charge_start_date", type: "string", format: "date", nullable: true, example: "2025-01-01", description: "The start date from which the charge is applicable (YYYY-MM-DD)."),
        new OA\Property(property: "charge_end_date", type: "string", format: "date", nullable: true, example: "2025-12-31", description: "The end date until which the charge is applicable (YYYY-MM-DD)."),
        new OA\Property(property: "charge_amount", type: "number", format: "float", nullable: true, example: 10.50, description: "The amount of the tunnel charge."),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], nullable: true, example: 1, description: "Status of the tunnel charge: 0 = Inactive, 1 = Active.")
    ]
)]
class UpdateRequest extends FormRequest
{
    /**
     * Define the validation rules for updating an existing tunnel charge.
     * All fields are nullable as they might not be provided in a partial update.
     * @return array
     */
    protected function rules(): array
    {
        return [
            'charge_start_date' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'charge_end_date' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'charge_amount' => ['nullable', 'numeric', 'min:0'],
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
            'charge_start_date.string' => 'Charge start date must be a string.',
            'charge_start_date.regex' => 'Charge start date must be in YYYY-MM-DD format.',
            'charge_end_date.string' => 'Charge end date must be a string.',
            'charge_end_date.regex' => 'Charge end date must be in YYYY-MM-DD format.',
            'charge_amount.numeric' => 'Charge amount must be a number.',
            'charge_amount.min' => 'Charge amount must be at least 0.',
            'status.integer' => 'Status must be an integer.',
            'status.in' => 'Status must be either 0 (Inactive) or 1 (Active).',
        ];
    }
}