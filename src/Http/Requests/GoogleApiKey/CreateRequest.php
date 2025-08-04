<?php

namespace App\Http\Requests\GoogleApiKey;

use OpenApi\Attributes as OA;
use App\Http\Requests\FormRequest;

#[OA\Schema(
    schema: "CreateGoogleApiKeyRequest",
    title: "Create Google API Key Request",
    properties: [
        new OA\Property(property: "api_key", type: "string", example: "NewApiKey"),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], example: 1)
    ],
    required: ["api_key", "status"]
)]
class CreateRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'api_key' => 'required|min:20|max:100',
            'status' => 'required|numeric|min:0|max:1',
        ];
    }

    protected function messages(): array
    {
        return [
            'api_key.required' => 'API key is required.',
            'api_key.min' => 'API key must be at least 20 characters.',
            'status.required' => 'Status is required.',
            'status.numeric' => 'Status must be 0 or 1.',
        ];
    }
}