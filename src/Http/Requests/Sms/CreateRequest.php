<?php

namespace App\Http\Requests\Sms;

use App\Http\Requests\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "CreateSmsServiceRequest",
    title: "Create SMS Service Request",
    properties: [
        new OA\Property(property: "phone_number", type: "string", example: "+1234567890", description: "The phone number for the SMS service."),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], example: 1, description: "Status of the SMS service: 0 = Inactive, 1 = Active.")
    ],
    required: ["phone_number", "status"]
)]
class CreateRequest extends FormRequest
{
    /**
     * Define the validation rules for creating a new SMS service entry.
     * @return array
     */
    protected function rules(): array
    {
        return [
            'phone_number' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9]{7,15}$/'], // Basic phone number validation
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
            'phone_number.required' => 'Phone number is required.',
            'phone_number.string' => 'Phone number must be a string.',
            'phone_number.max' => 'Phone number may not be greater than 20 characters.',
            'phone_number.regex' => 'Phone number format is invalid. It should start with a "+" and contain only digits.',
            'status.required' => 'Status is required.',
            'status.integer' => 'Status must be an integer.',
            'status.in' => 'Status must be either 0 (Inactive) or 1 (Active).',
        ];
    }
}