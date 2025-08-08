<?php

namespace App\Repositories\MySQL;

use App\Models\ExtraCharge;
use App\Repositories\Contracts\ExtraChargeInterface;
use PDO;
use PDOException;

class ExtraChargeDatabase implements ExtraChargeInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find an extra charge by its ID.
     * @param int $id
     * @return ExtraCharge|null
     * @throws PDOException
     */
    public function findById(int $id): ?ExtraCharge
    {
        $stmt = $this->db->prepare("SELECT * FROM extra_charges WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $extraChargeData = $stmt->fetch(PDO::FETCH_ASSOC);

        return $extraChargeData ? ExtraCharge::fromArray($extraChargeData) : null;
    }

    /**
     * Find an extra charge by area name.
     * 
     * @param string $areaName
     * @param bool $partial
     * 
     * @return ExtraCharge|null
     * 
     * @throws PDOException
     */
    public function findByAreaName(string $areaName, bool $partial = true): ?ExtraCharge
    {
        $query = $partial
            ? "SELECT * FROM extra_charges WHERE area_name LIKE :area_name LIMIT 1"
            : "SELECT * FROM extra_charges WHERE area_name = :area_name LIMIT 1";

        try {
            $stmt = $this->db->prepare($query);
            $paramValue = $partial ? '%' . $areaName . '%' : $areaName;
            $stmt->bindValue(':area_name', $paramValue, PDO::PARAM_STR);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                return null;
            }

            return ExtraCharge::fromArray($data);
        } catch (PDOException $e) {
            // Log or handle the exception appropriately
            throw $e;
        }
    }

    /**
     * Get all extra charges.
     * @return ExtraCharge[]
     * @throws PDOException
     */
    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM extra_charges");
        $extraChargesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $extraCharges = [];
        foreach ($extraChargesData as $data) {
            $extraCharges[] = ExtraCharge::fromArray($data);
        }
        return $extraCharges;
    }

    /**
     * Create a new extra charge.
     * @param ExtraCharge $extraCharge
     * @return int The ID of the newly created extra charge.
     * @throws PDOException
     */
    public function create(ExtraCharge $extraCharge): int
    {
        $data = $extraCharge->toDatabaseArray();
        unset($data['id']); // ID is auto-incremented

        $sql = "INSERT INTO extra_charges (area_name, zip_codes, extra_charge, extra_toll_charge, status, created_at, updated_at)
                VALUES (:area_name, :zip_codes, :extra_charge, :extra_toll_charge, :status, :created_at, :updated_at)";
        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':area_name', $data['area_name']);
        $stmt->bindParam(':zip_codes', $data['zip_codes']);
        $stmt->bindParam(':extra_charge', $data['extra_charge']);
        $stmt->bindParam(':extra_toll_charge', $data['extra_toll_charge']);
        $stmt->bindParam(':status', $data['status'], PDO::PARAM_INT);
        $stmt->bindParam(':created_at', $data['created_at']);
        $stmt->bindParam(':updated_at', $data['updated_at']);

        $stmt->execute();
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing extra charge.
     * @param ExtraCharge $extraCharge
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function update(ExtraCharge $extraCharge): bool
    {
        $data = $extraCharge->toDatabaseArray();
        if (!isset($data['id'])) {
            throw new PDOException("ExtraCharge ID is required for update.");
        }

        $sql = "UPDATE extra_charges SET
                    area_name = :area_name,
                    zip_codes = :zip_codes,
                    extra_charge = :extra_charge,
                    extra_toll_charge = :extra_toll_charge,
                    status = :status,
                    updated_at = :updated_at
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
        $stmt->bindParam(':area_name', $data['area_name']);
        $stmt->bindParam(':zip_codes', $data['zip_codes']);
        $stmt->bindParam(':extra_charge', $data['extra_charge']);
        $stmt->bindParam(':extra_toll_charge', $data['extra_toll_charge']);
        $stmt->bindParam(':status', $data['status'], PDO::PARAM_INT);
        $stmt->bindParam(':updated_at', $data['updated_at']);

        return $stmt->execute();
    }

    /**
     * Delete an extra charge by its ID.
     * @param int $id
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM extra_charges WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
