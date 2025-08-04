<?php

namespace App\Repositories\MySQL;

use App\Models\CommonSetting;
use App\Repositories\Contracts\CommonSettingInterface;
use PDO;
use PDOException;

class CommonSettingDatabase implements CommonSettingInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get the single common setting record.
     * There should only be one row in this table.
     * @return CommonSetting|null
     * @throws PDOException
     */
    public function get(): ?CommonSetting
    {
        $stmt = $this->db->query("SELECT * FROM common_settings LIMIT 1");
        $settingData = $stmt->fetch(PDO::FETCH_ASSOC);

        return $settingData ? CommonSetting::fromArray($settingData) : null;
    }

    /**
     * Create the initial common setting record.
     * This method should only be called if no record exists.
     * @param CommonSetting $setting
     * @return int The ID of the newly created setting.
     * @throws PDOException
     */
    public function create(CommonSetting $setting): int
    {
        $data = $setting->toDatabaseArray(); // Use the new method for database array

        $fields = [
            'company_name', 'company_logo', 'address', 'booking_call_number', 'telephone_number',
            'email', 'website', 'holidays', 'holiday_surcharge', 'tunnel_charge',
            'gratuity', 'stop_over_charge', 'infant_front_facing_seat_charge', 'infant_rear_facing_seat_charge',
            'infant_booster_seat_charge', 'night_charge', 'night_charge_start_time', 'night_charge_end_time',
            'hidden_night_charge', 'hidden_night_charge_start_time', 'hidden_night_charge_end_time',
            'snow_strom_charge', 'rush_hour_charge', 'extra_luggage_charge', 'pets_charge',
            'convenience_fee', 'cash_discount', 'paypal_charge', 'square_charge',
            'credit_card_charge', 'status', 'created_at', 'updated_at'
        ];

        $placeholders = implode(', ', array_map(fn($field) => ":$field", $fields));
        $columns = implode(', ', $fields);

        $sql = "INSERT INTO common_settings ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);

        $bindData = [];
        foreach ($fields as $field) {
            $bindData[":$field"] = $data[$field] ?? null;

            // Special handling for time fields if they are empty strings, convert to null
            if (str_contains($field, '_time') && ($bindData[":$field"] === '' || $bindData[":$field"] === '00:00:00')) {
                $bindData[":$field"] = null;
            }
        }

        $stmt->execute($bindData);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update the existing common setting record.
     * @param CommonSetting $setting
     * @return bool True on success, false otherwise.
     * @throws PDOException
     */
    public function update(CommonSetting $setting): bool
    {
        $data = $setting->toDatabaseArray(); // Use the new method for database array

        $fields = [
            'company_name', 'company_logo', 'address', 'booking_call_number', 'telephone_number',
            'email', 'website', 'holidays', 'holiday_surcharge', 'tunnel_charge',
            'gratuity', 'stop_over_charge', 'infant_front_facing_seat_charge', 'infant_rear_facing_seat_charge',
            'infant_booster_seat_charge', 'night_charge', 'night_charge_start_time', 'night_charge_end_time',
            'hidden_night_charge', 'hidden_night_charge_start_time', 'hidden_night_charge_end_time',
            'snow_strom_charge', 'rush_hour_charge', 'extra_luggage_charge', 'pets_charge',
            'convenience_fee', 'cash_discount', 'paypal_charge', 'square_charge',
            'credit_card_charge', 'status', 'updated_at'
        ];
        $setClauses = implode(', ', array_map(fn($field) => "{$field} = :{$field}", $fields));

        $sql = "UPDATE common_settings SET {$setClauses} WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        $bindData = [];
        foreach ($fields as $field) {
            $bindData[":$field"] = $data[$field] ?? null;

            // Special handling for time fields if they are empty strings, convert to null
            if (str_contains($field, '_time') && ($bindData[":$field"] === '' || $bindData[":$field"] === '00:00:00')) {
                $bindData[":$field"] = null;
            }
        }

        $bindData[':id'] = $data['id'];

        return $stmt->execute($bindData);
    }
}