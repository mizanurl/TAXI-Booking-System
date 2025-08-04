<?php

namespace App\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "CommonSetting",
    title: "Common Settings",
    description: "Global application settings.",
    properties: [
        new OA\Property(property: "id", type: "integer", format: "int64", readOnly: true, example: 1),
        new OA\Property(property: "company_name", type: "string", example: "Taxi Co. Ltd."),
        new OA\Property(property: "company_logo", type: "string", nullable: true, example: "http://localhost:8000/uploads/company/company_logo.png", description: "Absolute URL to the company logo image."),
        new OA\Property(property: "address", type: "string", example: "123 Main St, City, State, Zip"),
        new OA\Property(property: "booking_call_number", type: "string", nullable: true, example: "+11234567890"),
        new OA\Property(property: "telephone_number", type: "string", example: "+19876543210"),
        new OA\Property(property: "email", type: "string", format: "email", example: "info@taxico.com"),
        new OA\Property(property: "website", type: "string", nullable: true, format: "url", example: "https://www.taxico.com"),
        new OA\Property(property: "holidays", type: "string", nullable: true, example: "[\"2025-12-25\", \"2026-01-01\"]", description: "JSON encoded string of holiday dates."),
        new OA\Property(property: "holiday_surcharge", type: "number", format: "float", nullable: true, example: 10.50),
        new OA\Property(property: "tunnel_charge", type: "number", format: "float", nullable: true, example: 2.75),
        new OA\Property(property: "gratuity", type: "number", format: "float", nullable: true, example: 15.00),
        new OA\Property(property: "stop_over_charge", type: "number", format: "float", nullable: true, example: 5.00),
        new OA\Property(property: "infant_front_facing_seat_charge", type: "number", format: "float", nullable: true, example: 10.00),
        new OA\Property(property: "infant_front_facing_seat_charge", type: "number", format: "float", nullable: true, example: 8.00),
        new OA\Property(property: "infant_booster_seat_charge", type: "number", format: "float", nullable: true, example: 7.00),
        new OA\Property(property: "night_charge", type: "number", format: "float", nullable: true, example: 20.00),
        new OA\Property(property: "night_charge_start_time", type: "string", format: "time", nullable: true, example: "10:00 PM"),
        new OA\Property(property: "night_charge_end_time", type: "string", format: "time", nullable: true, example: "06:00 AM"),
        new OA\Property(property: "hidden_night_charge", type: "number", format: "float", nullable: true, example: 10.00),
        new OA\Property(property: "hidden_night_charge_start_time", type: "string", format: "time", nullable: true, example: "11:00 PM"),
        new OA\Property(property: "hidden_night_charge_end_time", type: "string", format: "time", nullable: true, example: "05:00 AM"),
        new OA\Property(property: "snow_strom_charge", type: "number", format: "float", nullable: true, example: 12.00),
        new OA\Property(property: "rush_hour_charge", type: "number", format: "float", nullable: true, example: 8.00),
        new OA\Property(property: "extra_luggage_charge", type: "number", format: "float", nullable: true, example: 3.50),
        new OA\Property(property: "pets_charge", type: "number", format: "float", nullable: true, example: 15.00),
        new OA\Property(property: "convenience_fee", type: "number", format: "float", nullable: true, example: 2.50, description: "Percentage of total amount."),
        new OA\Property(property: "cash_discount", type: "number", format: "float", nullable: true, example: 1.00, description: "Percentage of total amount."),
        new OA\Property(property: "paypal_charge", type: "number", format: "float", nullable: true, example: 3.00, description: "Percentage of total amount."),
        new OA\Property(property: "square_charge", type: "number", format: "float", nullable: true, example: 2.90, description: "Percentage of total amount."),
        new OA\Property(property: "credit_card_charge", type: "number", format: "float", nullable: true, example: 2.70, description: "Percentage of total amount."),
        new OA\Property(property: "status", type: "integer", enum: [0, 1], description: "0 = Inactive, 1 = Active", example: 1),
        new OA\Property(property: "created_at", type: "string", format: "date-time", readOnly: true),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", readOnly: true)
    ],
    required: ["company_name", "address", "email", "telephone_number", "status"]
)]
class CommonSetting
{
    public function __construct(
        public ?int $id,
        public string $companyName,
        public string $address,
        public string $email,
        public string $telephoneNumber,
        public int $status,
        public ?string $companyLogo = null, // This property should ALWAYS hold just the filename (e.g., company_logo.png)
        public ?string $bookingCallNumber = null,
        public ?string $website = null,
        public ?string $holidays = null,
        public ?float $holidaySurcharge = null,
        public ?float $tunnelCharge = null,
        public ?float $gratuity = null,
        public ?float $stopOverCharge = null,
        public ?float $infantFrontFacingSeatCharge = null,
        public ?float $infantRearFacingSeatCharge = null,
        public ?float $infantBoosterSeatCharge = null,
        public ?float $nightCharge = null,
        public ?string $nightChargeStartTime = null,
        public ?string $nightChargeEndTime = null,
        public ?float $hiddenNightCharge = null,
        public ?string $hiddenNightChargeStartTime = null,
        public ?string $hiddenNightChargeEndTime = null,
        public ?float $snowStromCharge = null,
        public ?float $rushHourCharge = null,
        public ?float $extraLuggageCharge = null,
        public ?float $petsCharge = null,
        public ?float $convenienceFee = null,
        public ?float $cashDiscount = null,
        public ?float $paypalCharge = null,
        public ?float $squareCharge = null,
        public ?float $creditCardCharge = null,
        public string $createdAt = '',
        public string $updatedAt = ''
    ) {
        if (empty($this->createdAt)) {
            $this->createdAt = date('Y-m-d H:i:s');
        }
        if (empty($this->updatedAt)) {
            $this->updatedAt = date('Y-m-d H:i:s');
        }
    }

    /**
     * Creates a CommonSetting object from an associative array (e.g., from database result).
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $companyLogo = $data['company_logo'] ?? null;
        // If the database returns a full URL, extract just the filename
        if ($companyLogo && str_contains($companyLogo, 'http')) {
            $parts = explode('/', $companyLogo);
            $companyLogo = end($parts); // Get the last part (filename)
        }

        return new self(
            $data['id'] ?? null,
            $data['company_name'] ?? '',
            $data['address'] ?? '',
            $data['email'] ?? '',
            $data['telephone_number'] ?? '',
            (int) ($data['status'] ?? 0),
            $companyLogo, // Ensure only filename is passed here
            $data['booking_call_number'] ?? null,
            $data['website'] ?? null,
            $data['holidays'] ?? null,
            (float) ($data['holiday_surcharge'] ?? 0.00),
            (float) ($data['tunnel_charge'] ?? 0.00),
            (float) ($data['gratuity'] ?? 0.00),
            (float) ($data['stop_over_charge'] ?? 0.00),
            (float) ($data['infant_front_facing_seat_charge'] ?? 0.00),
            (float) ($data['infant_rear_facing_seat_charge'] ?? 0.00),
            (float) ($data['infant_booster_seat_charge'] ?? 0.00),
            (float) ($data['night_charge'] ?? 0.00),
            $data['night_charge_start_time'] ?? null,
            $data['night_charge_end_time'] ?? null,
            (float) ($data['hidden_night_charge'] ?? 0.00),
            $data['hidden_night_charge_start_time'] ?? null,
            $data['hidden_night_charge_end_time'] ?? null,
            (float) ($data['snow_strom_charge'] ?? 0.00),
            (float) ($data['rush_hour_charge'] ?? 0.00),
            (float) ($data['extra_luggage_charge'] ?? 0.00),
            (float) ($data['pets_charge'] ?? 0.00),
            (float) ($data['convenience_fee'] ?? 0.00),
            (float) ($data['cash_discount'] ?? 0.00),
            (float) ($data['paypal_charge'] ?? 0.00),
            (float) ($data['square_charge'] ?? 0.00),
            (float) ($data['credit_card_charge'] ?? 0.00),
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null
        );
    }

    /**
     * Converts the CommonSetting object to an associative array for database insertion/update.
     * Also formats specific fields for API response.
     * @return array
     */
    public function toArray(): array
    {
        // Define the relative upload directory for logo
        // This should match CommonSettingService::LOGO_UPLOAD_DIR_RELATIVE
        $logoUploadDirRelative = 'uploads/company/';

        $array = [
            'id' => $this->id,
            'company_name' => $this->companyName,
            // Construct absolute URL for company_logo ONLY for API response
            // This assumes companyLogo property stores just the filename (e.g., "company_logo.png")
            'company_logo' => $this->companyLogo ? (getenv('APP_URL') ?: 'http://localhost:8000') . '/' . trim($logoUploadDirRelative, '/\\') . '/' . $this->companyLogo : null,
            'address' => $this->address,
            'booking_call_number' => $this->bookingCallNumber,
            'telephone_number' => $this->telephoneNumber,
            'email' => $this->email,
            'website' => $this->website,
            'holidays' => $this->holidays,
            'holiday_surcharge' => number_format($this->holidaySurcharge, 2, '.', ''),
            'tunnel_charge' => number_format($this->tunnelCharge, 2, '.', ''),
            'gratuity' => number_format($this->gratuity, 2, '.', ''),
            'stop_over_charge' => number_format($this->stopOverCharge, 2, '.', ''),
            'infant_front_facing_seat_charge' => number_format($this->infantFrontFacingSeatCharge, 2, '.', ''),
            'infant_rear_facing_seat_charge' => number_format($this->infantRearFacingSeatCharge, 2, '.', ''),
            'infant_booster_seat_charge' => number_format($this->infantBoosterSeatCharge, 2, '.', ''),
            'night_charge' => number_format($this->nightCharge, 2, '.', ''),
            'night_charge_start_time' => $this->nightChargeStartTime ? date('h:i A', strtotime($this->nightChargeStartTime)) : null,
            'night_charge_end_time' => $this->nightChargeEndTime ? date('h:i A', strtotime($this->nightChargeEndTime)) : null,
            'hidden_night_charge' => number_format($this->hiddenNightCharge, 2, '.', ''),
            'hidden_night_charge_start_time' => $this->hiddenNightChargeStartTime ? date('h:i A', strtotime($this->hiddenNightChargeStartTime)) : null,
            'hidden_night_charge_end_time' => $this->hiddenNightChargeEndTime ? date('h:i A', strtotime($this->hiddenNightChargeEndTime)) : null,
            'snow_strom_charge' => number_format($this->snowStromCharge, 2, '.', ''),
            'rush_hour_charge' => number_format($this->rushHourCharge, 2, '.', ''),
            'extra_luggage_charge' => number_format($this->extraLuggageCharge, 2, '.', ''),
            'pets_charge' => number_format($this->petsCharge, 2, '.', ''),
            'convenience_fee' => number_format($this->convenienceFee, 2, '.', ''),
            'cash_discount' => number_format($this->cashDiscount, 2, '.', ''),
            'paypal_charge' => number_format($this->paypalCharge, 2, '.', ''),
            'square_charge' => number_format($this->squareCharge, 2, '.', ''),
            'credit_card_charge' => number_format($this->creditCardCharge, 2, '.', ''),
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];

        foreach ([
            'holiday_surcharge', 'tunnel_charge', 'gratuity', 'stop_over_charge',
            'infant_front_facing_seat_charge', 'infant_rear_facing_seat_charge', 'infant_booster_seat_charge',
            'night_charge', 'hidden_night_charge', 'snow_strom_charge', 'rush_hour_charge',
            'extra_luggage_charge', 'pets_charge', 'convenience_fee', 'cash_discount',
            'paypal_charge', 'square_charge', 'credit_card_charge'
        ] as $floatField) {
            if (isset($array[$floatField]) && $array[$floatField] === '0.00') {
                $array[$floatField] = 0.00;
            }
        }

        return $array;
    }

    /**
     * Converts the CommonSetting object to an associative array for database insertion/update.
     * This method is specifically for saving to the database, so company_logo will be just the filename.
     * @return array
     */
    public function toDatabaseArray(): array
    {
        $array = [
            'id' => $this->id,
            'company_name' => $this->companyName,
            'company_logo' => $this->companyLogo, // Store just the filename
            'address' => $this->address,
            'booking_call_number' => $this->bookingCallNumber,
            'telephone_number' => $this->telephoneNumber,
            'email' => $this->email,
            'website' => $this->website,
            'holidays' => $this->holidays,
            'holiday_surcharge' => number_format($this->holidaySurcharge, 2, '.', ''),
            'tunnel_charge' => number_format($this->tunnelCharge, 2, '.', ''),
            'gratuity' => number_format($this->gratuity, 2, '.', ''),
            'stop_over_charge' => number_format($this->stopOverCharge, 2, '.', ''),
            'infant_front_facing_seat_charge' => number_format($this->infantFrontFacingSeatCharge, 2, '.', ''),
            'infant_rear_facing_seat_charge' => number_format($this->infantRearFacingSeatCharge, 2, '.', ''),
            'infant_booster_seat_charge' => number_format($this->infantBoosterSeatCharge, 2, '.', ''),
            'night_charge' => number_format($this->nightCharge, 2, '.', ''),
            'night_charge_start_time' => $this->nightChargeStartTime, // Store as HH:MM:SS
            'night_charge_end_time' => $this->nightChargeEndTime,     // Store as HH:MM:SS
            'hidden_night_charge' => number_format($this->hiddenNightCharge, 2, '.', ''),
            'hidden_night_charge_start_time' => $this->hiddenNightChargeStartTime, // Store as HH:MM:SS
            'hidden_night_charge_end_time' => $this->hiddenNightChargeEndTime,     // Store as HH:MM:SS
            'snow_strom_charge' => number_format($this->snowStromCharge, 2, '.', ''),
            'rush_hour_charge' => number_format($this->rushHourCharge, 2, '.', ''),
            'extra_luggage_charge' => number_format($this->extraLuggageCharge, 2, '.', ''),
            'pets_charge' => number_format($this->petsCharge, 2, '.', ''),
            'convenience_fee' => number_format($this->convenienceFee, 2, '.', ''),
            'cash_discount' => number_format($this->cashDiscount, 2, '.', ''),
            'paypal_charge' => number_format($this->paypalCharge, 2, '.', ''),
            'square_charge' => number_format($this->squareCharge, 2, '.', ''),
            'credit_card_charge' => number_format($this->creditCardCharge, 2, '.', ''),
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];

        // Ensure float values are correctly formatted and not null if they are 0.00
        foreach ([
            'holiday_surcharge', 'tunnel_charge', 'gratuity', 'stop_over_charge',
            'infant_front_facing_seat_charge', 'infant_rear_facing_seat_charge', 'infant_booster_seat_charge',
            'night_charge', 'hidden_night_charge', 'snow_strom_charge', 'rush_hour_charge',
            'extra_luggage_charge', 'pets_charge', 'convenience_fee', 'cash_discount',
            'paypal_charge', 'square_charge', 'credit_card_charge'
        ] as $floatField) {
            if (isset($array[$floatField]) && $array[$floatField] === '0.00') {
                $array[$floatField] = 0.00; // Convert "0.00" string back to float 0.00
            }
        }

        return $array;
    }
}