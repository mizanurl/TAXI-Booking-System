<?php

namespace App\Models;

class Booking
{
    public function __construct(
        public ?int $id,
        public int $service,
        public string $serviceTime,
        public string $chargeEndDate,
        public string $pickupLocation,
        public string $dropLocation,
        public int $numberOfPassengers,
        public int $numberOfLuggages,
        public int $carId,
        public float $distance, // In Miles
        public float $totalPayable,
        public int $paymentStatus, // 0 for Pending, 1 for Paid
        public string $passengerName,
        public string $passengerPhone,
        public int $hasSpecialNeeds, // 0 for No, 1 for Yes - Moved before optional params
        public int $isTraveller, // 0 for No, 1 for Yes - Moved before optional params
        public int $status, // 0 for inactive, 1 for active - Moved before optional params
        public ?string $passengerEmail = null, // Optional
        public ?string $passengerAddress = null, // Optional
        public ?string $airlineName = null, // Optional
        public ?string $flightNumber = null, // Optional
        public string $createdAt = '', // Default to empty string for initial creation
        public string $updatedAt = ''  // Default to empty string for initial creation
    ) {
        // Assign default timestamps if not provided (only if they are empty strings)
        if (empty($this->createdAt)) {
            $this->createdAt = date('Y-m-d H:i:s');
        }
        if (empty($this->updatedAt)) {
            $this->updatedAt = date('Y-m-d H:i:s');
        }
    }

    /**
     * Creates a Booking object from an associative array (e.g., from database result).
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            (int) ($data['service'] ?? 0),
            $data['service_time'] ?? '',
            $data['charge_end_date'] ?? '',
            $data['pickup_location'] ?? '',
            $data['drop_location'] ?? '',
            (int) ($data['number_of_passengers'] ?? 0),
            (int) ($data['number_of_luggages'] ?? 0),
            (int) ($data['car_id'] ?? 0),
            (float) ($data['distance'] ?? 0.0),
            (float) ($data['total_payable'] ?? 0.0),
            (int) ($data['payment_status'] ?? 0),
            $data['passenger_name'] ?? '',
            $data['passenger_phone'] ?? '',
            (int) ($data['has_special_needs'] ?? 0),
            (int) ($data['is_traveller'] ?? 0),
            (int) ($data['status'] ?? 0),
            $data['passenger_email'] ?? null,
            $data['passenger_address'] ?? null,
            $data['airline_name'] ?? null,
            $data['flight_number'] ?? null,
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null
        );
    }

    /**
     * Converts the Booking object to an associative array for database insertion/update.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'service' => $this->service,
            'service_time' => $this->serviceTime,
            'charge_end_date' => $this->chargeEndDate,
            'pickup_location' => $this->pickupLocation,
            'drop_location' => $this->dropLocation,
            'number_of_passengers' => $this->numberOfPassengers,
            'number_of_luggages' => $this->numberOfLuggages,
            'car_id' => $this->carId,
            'distance' => number_format($this->distance, 2, '.', ''), // Format to two decimal points
            'total_payable' => number_format($this->totalPayable, 2, '.', ''),
            'payment_status' => $this->paymentStatus,
            'passenger_name' => $this->passengerName,
            'passenger_phone' => $this->passengerPhone,
            'passenger_email' => $this->passengerEmail,
            'passenger_address' => $this->passengerAddress,
            'airline_name' => $this->airlineName,
            'flight_number' => $this->flightNumber,
            'has_special_needs' => $this->hasSpecialNeeds,
            'is_traveller' => $this->isTraveller,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}