<?php

namespace App\Http\Requests\GoogleApiKey;

use OpenApi\Attributes as OA;
use App\Http\Requests\FormRequest;

#[OA\Schema(
    schema: "UpdateGoogleApiKeyRequest",
    title: "Update Google API Key Request",
    properties: [
        new OA\Property(property: "api_key", type: "string", nullable: true, example: "UpdatedApiKey"),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], nullable: true, example: 0)
    ]
)]
class UpdateRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'api_key' => 'nullable|min:20|max:100',
            'status' => 'nullable|numeric|min:0|max:1',
        ];
    }

    protected function messages(): array
    {
        return [
            'api_key.min' => 'API key must be at least 20 characters.',
            'status.numeric' => 'Status must be 0 or 1.',
        ];
    }
}