<?php

namespace App\Models;

use App\Models\Booking;

class Order
{
    private ?int $id;
    private int $bookingId;
    private string $squarePaymentId;
    private float $amount;
    private int $status; // '0=pending, 1=paid, 2=failed'
    private string $createdAt;
    private string $updatedAt;

    // Add getters/setters (or use public properties)
    public function __construct(
        ?int $id,
        int $bookingId,
        string $squarePaymentId,
        float $amount,
        int $status = 0,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->bookingId = $bookingId;
        $this->squarePaymentId = $squarePaymentId;
        $this->amount = $amount;
        $this->status = $status;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
        $this->updatedAt = $updatedAt ?? date('Y-m-d H:i:s');
    }

    // Add toArray() if needed for database operations
    public function toArray(): array
    {
        return [
            'booking_id' => $this->bookingId,
            'square_payment_id' => $this->squarePaymentId,
            'amount' => $this->amount,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}