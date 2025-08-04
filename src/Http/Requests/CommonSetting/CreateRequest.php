<?php

namespace App\Http\Requests\CommonSetting;

use App\Http\Requests\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "CreateCommonSettingRequest",
    title: "Create Common Settings Request",
    description: "Request body for creating the initial common settings.",
    properties: [
        new OA\Property(property: "company_name", type: "string", example: "New Taxi Co."),
        new OA\Property(property: "company_logo", type: "string", format: "binary", nullable: true, description: "Company logo image file (jpg, gif, png, webp, max 100KB, max 300x200px)."),
        new OA\Property(property: "address", type: "string", example: "456 Oak Ave, Town, State, Zip"),
        new OA\Property(property: "booking_call_number", type: "string", nullable: true, example: "+1122334455"),
        new OA\Property(property: "telephone_number", type: "string", example: "+19988776655"),
        new OA\Property(property: "email", type: "string", format: "email", example: "contact@newtaxico.com"),
        new OA\Property(property: "website", type: "string", nullable: true, format: "url", example: "https://www.newtaxico.com"),
        new OA\Property(property: "holidays", type: "string", nullable: true, example: "[\"2025-12-25\"]", description: "JSON string of holiday dates."),
        new OA\Property(property: "holiday_surcharge", type: "number", format: "float", nullable: true, example: 10.00),
        new OA\Property(property: "tunnel_charge", type: "number", format: "float", nullable: true, example: 3.00),
        new OA\Property(property: "gratuity", type: "number", format: "float", nullable: true, example: 18.00),
        new OA\Property(property: "stop_over_charge", type: "number", format: "float", nullable: true, example: 6.00),
        new OA\Property(property: "infant_front_facing_seat_charge", type: "number", format: "float", nullable: true, example: 12.00),
        new OA\Property(property: "infant_rear_facing_seat_charge", type: "number", format: "float", nullable: true, example: 9.00),
        new OA\Property(property: "infant_booster_seat_charge", type: "number", format: "float", nullable: true, example: 8.00),
        new OA\Property(property: "night_charge", type: "number", format: "float", nullable: true, example: 25.00),
        new OA\Property(property: "night_charge_start_time", type: "string", format: "time", nullable: true, example: "21:00"),
        new OA\Property(property: "night_charge_end_time", type: "string", format: "time", nullable: true, example: "07:00"),
        new OA\Property(property: "hidden_night_charge", type: "number", format: "float", nullable: true, example: 15.00),
        new OA\Property(property: "hidden_night_charge_start_time", type: "string", format: "time", nullable: true, example: "00:00"),
        new OA\Property(property: "hidden_night_charge_end_time", type: "string", format: "time", nullable: true, example: "04:00"),
        new OA\Property(property: "snow_strom_charge", type: "number", format: "float", nullable: true, example: 15.00),
        new OA\Property(property: "rush_hour_charge", type: "number", format: "float", nullable: true, example: 10.00),
        new OA\Property(property: "extra_luggage_charge", type: "number", format: "float", nullable: true, example: 4.00),
        new OA\Property(property: "pets_charge", type: "number", format: "float", nullable: true, example: 20.00),
        new OA\Property(property: "convenience_fee", type: "number", format: "float", nullable: true, example: 3.00),
        new OA\Property(property: "cash_discount", type: "number", format: "float", nullable: true, example: 1.50),
        new OA\Property(property: "paypal_charge", type: "number", format: "float", nullable: true, example: 3.50),
        new OA\Property(property: "square_charge", type: "number", format: "float", nullable: true, example: 3.20),
        new OA\Property(property: "credit_card_charge", type: "number", format: "float", nullable: true, example: 3.00),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], example: 1)
    ],
    required: ["company_name", "address", "email", "telephone_number", "status"]
)]
class CreateRequest extends FormRequest
{
    /**
     * Define validation rules for the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:100'],
            'company_logo' => ['nullable', 'image', 'max_size:100', 'dimensions:max_width=300,max_height=200', 'mimes:jpg,gif,png,webp'], // FIX: Changed 'max' to 'max_size'
            'address' => ['required', 'string'],
            'booking_call_number' => ['nullable', 'string', 'min:10'],
            'telephone_number' => ['required', 'string', 'min:10'],
            'email' => ['required', 'email', 'max:100'],
            'website' => ['nullable', 'string', 'max:100'],
            'holidays' => ['nullable', 'string'], // Expecting JSON string
            'holiday_surcharge' => ['nullable', 'numeric', 'min:0'],
            'tunnel_charge' => ['nullable', 'numeric', 'min:0'],
            'gratuity' => ['nullable', 'numeric', 'min:0'],
            'stop_over_charge' => ['nullable', 'numeric', 'min:0'],
            'infant_front_facing_seat_charge' => ['nullable', 'numeric', 'min:0'],
            'infant_rear_facing_seat_charge' => ['nullable', 'numeric', 'min:0'],
            'infant_booster_seat_charge' => ['nullable', 'numeric', 'min:0'],
            'night_charge' => ['nullable', 'numeric', 'min:0'],
            'night_charge_start_time' => ['nullable', 'string', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'], // HH:MM format
            'night_charge_end_time' => ['nullable', 'string', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'hidden_night_charge' => ['nullable', 'numeric', 'min:0'],
            'hidden_night_charge_start_time' => ['nullable', 'string', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'hidden_night_charge_end_time' => ['nullable', 'string', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'snow_strom_charge' => ['nullable', 'numeric', 'min:0'],
            'rush_hour_charge' => ['nullable', 'numeric', 'min:0'],
            'extra_luggage_charge' => ['nullable', 'numeric', 'min:0'],
            'pets_charge' => ['nullable', 'numeric', 'min:0'],
            'convenience_fee' => ['nullable', 'numeric', 'min:0', 'max:100'], // Percentage
            'cash_discount' => ['nullable', 'numeric', 'min:0', 'max:100'],   // Percentage
            'paypal_charge' => ['nullable', 'numeric', 'min:0', 'max:100'],   // Percentage
            'square_charge' => ['nullable', 'numeric', 'min:0', 'max:100'],   // Percentage
            'credit_card_charge' => ['nullable', 'numeric', 'min:0', 'max:100'], // Percentage
            'status' => ['required', 'integer', 'in:0,1'],
        ];
    }
}