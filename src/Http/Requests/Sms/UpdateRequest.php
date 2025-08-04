<?php

namespace App\Http\Requests\Sms;

use App\Http\Requests\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UpdateSmsServiceRequest",
    title: "Update SMS Service Request",
    properties: [
        new OA\Property(property: "phone_number", type: "string", nullable: true, example: "+1987654321", description: "The updated phone number for the SMS service."),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], nullable: true, example: 0, description: "Status of the SMS service: 0 = Inactive, 1 = Active.")
    ]
)]
class UpdateRequest extends FormRequest
{
    /**
     * Define the validation rules for updating an existing SMS service entry.
     * All fields are nullable as they might not be provided in a partial update.
     * @return array
     */
    protected function rules(): array
    {
        return [
            'phone_number' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9]{7,15}$/'],
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
            'phone_number.string' => 'Phone number must be a string.',
            'phone_number.max' => 'Phone number may not be greater than 20 characters.',
            'phone_number.regex' => 'Phone number format is invalid. It should start with a "+" and contain only digits.',
            'status.integer' => 'Status must be an integer.',
            'status.in' => 'Status must be either 0 (Inactive) or 1 (Active).',
        ];
    }
}