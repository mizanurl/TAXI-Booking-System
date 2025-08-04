<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\FormRequest;
use OpenApi\Attributes as OA; // Although this is for admin UI, it's good practice to add if you ever expose login via API

#[OA\Schema(
    schema: "AdminLoginRequest",
    title: "Admin Login Request",
    properties: [
        new OA\Property(property: "email", type: "string", format: "email", example: "admin@example.com", description: "Administrator's email address."),
        new OA\Property(property: "password", type: "string", format: "password", example: "password123", description: "Administrator's password.")
    ],
    required: ["email", "password"]
)]
class LoginRequest extends FormRequest
{
    /**
     * Define the validation rules for the admin login request.
     * @return array
     */
    protected function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:100'],
            'password' => ['required', 'string', 'min:6'], // Minimum password length
        ];
    }

    /**
     * Define custom validation messages.
     * @return array
     */
    protected function messages(): array
    {
        return [
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Email may not be greater than 100 characters.',
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
            'password.min' => 'Password must be at least 6 characters.',
        ];
    }
}