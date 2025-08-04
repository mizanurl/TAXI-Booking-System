<?php

namespace App\Models;

class PaypalSetting
{
    public function __construct(
        public ?int $id,
        public string $clientId,
        public string $clientSecret,
        public string $currency,
        public int $environment, // 0 for Sandbox, 1 for Live - Moved before optional
        public int $status, // 0 for inactive, 1 for active - Moved before optional
        public ?string $accountEmail = null,
        public ?string $webhookId = null,
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
     * Creates a PaypalSetting object from an associative array (e.g., from database result).
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['client_id'] ?? '',
            $data['client_secret'] ?? '',
            $data['currency'] ?? '',
            (int) ($data['environment'] ?? 0),
            (int) ($data['status'] ?? 0),
            $data['account_email'] ?? null,
            $data['webhook_id'] ?? null,
            $data['webhook_url'] ?? null,
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null
        );
    }

    /**
     * Converts the PaypalSetting object to an associative array for database insertion/update.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'account_email' => $this->accountEmail,
            'currency' => $this->currency,
            'webhook_id' => $this->webhookId,
            'webhook_url' => $this->webhookUrl,
            'environment' => $this->environment,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}