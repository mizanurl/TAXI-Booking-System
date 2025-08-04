<?php

namespace App\Repositories\MySQL;

use App\Models\SmsService;
use App\Repositories\Contracts\SmsServiceInterface;
use PDO;
use PDOException;

class SmsServiceDatabase implements SmsServiceInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find an SMS service entry by its ID.
     * @param int $id
     * @return SmsService|null
     * @throws PDOException
     */
    public function findById(int $id): ?SmsService
    {
        $stmt = $this->db->prepare("SELECT * FROM sms_services WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $smsServiceData = $stmt->fetch(PDO::FETCH_ASSOC);

        return $smsServiceData ? SmsService::fromArray($smsServiceData) : null;
    }

    /**
     * Get all SMS service entries.
     * @return SmsService[]
     * @throws PDOException
     */
    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM sms_services");
        $smsServicesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $smsServices = [];
        foreach ($smsServicesData as $data) {
            $smsServices[] = SmsService::fromArray($data);
        }
        return $smsServices;
    }

    /**
     * Create a new SMS service entry.
     * @param SmsService $smsService
     * @return int The ID of the newly created SMS service entry.
     * @throws PDOException
     */
    public function create(SmsService $smsService): int
    {
        $data = $smsService->toDatabaseArray();
        unset($data['id']); // ID is auto-incremented

        $sql = "INSERT INTO sms_services (phone_number, status, created_at, updated_at)
                VALUES (:phone_number, :status, :created_at, :updated_at)";
        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':phone_number', $data['phone_number']);
        $stmt->bindParam(':status', $data['status'], PDO::PARAM_INT);
        $stmt->bindParam(':created_at', $data['created_at']);
        $stmt->bindParam(':updated_at', $data['updated_at']);

        $stmt->execute();
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing SMS service entry.
     * @param SmsService $smsService
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function update(SmsService $smsService): bool
    {
        $data = $smsService->toDatabaseArray();
        if (!isset($data['id'])) {
            throw new PDOException("SmsService ID is required for update.");
        }

        $sql = "UPDATE sms_services SET
                    phone_number = :phone_number,
                    status = :status,
                    updated_at = :updated_at
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
        $stmt->bindParam(':phone_number', $data['phone_number']);
        $stmt->bindParam(':status', $data['status'], PDO::PARAM_INT);
        $stmt->bindParam(':updated_at', $data['updated_at']);

        return $stmt->execute();
    }

    /**
     * Delete an SMS service entry by its ID.
     * @param int $id
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM sms_services WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}