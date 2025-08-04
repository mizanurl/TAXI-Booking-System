<?php

namespace App\Models;

class SquareSetting
{
    public function __construct(
        public ?int $id,
        public string $applicationId,
        public string $accessToken,
        public string $locationId,
        public string $currency,
        public int $environment, // 0 for Sandbox, 1 for Live - Moved before optional
        public int $status, // 0 for inactive, 1 for active - Moved before optional
        public ?string $accountEmail = null,
        public ?string $webhookSignatureKey = null,
        public ?string $webhookUrl = null,
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
     * Creates a SquareSetting object from an associative array (e.g., from database result).
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['application_id'] ?? '',
            $data['access_token'] ?? '',
            $data['location_id'] ?? '',
            $data['currency'] ?? '',
            (int) ($data['environment'] ?? 0),
            (int) ($data['status'] ?? 0),
            $data['account_email'] ?? null,
            $data['webhook_signature_key'] ?? null,
            $data['webhook_url'] ?? null,
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null
        );
    }

    /**
     * Converts the SquareSetting object to an associative array for database insertion/update.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'application_id' => $this->applicationId,
            'access_token' => $this->accessToken,
            'account_email' => $this->accountEmail,
            'currency' => $this->currency,
            'location_id' => $this->locationId,
            'webhook_signature_key' => $this->webhookSignatureKey,
            'webhook_url' => $this->webhookUrl,
            'environment' => $this->environment,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}