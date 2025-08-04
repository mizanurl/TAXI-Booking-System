<?php

namespace App\Repositories\Contracts;

use App\Models\CommonSetting;

/**
 * Interface for Common Setting Repository.
 */
interface CommonSettingInterface
{
    /**
     * Get the single common setting record.
     * @return CommonSetting|null
     */
    public function get(): ?CommonSetting;

    /**
     * Create the initial common setting record.
     * @param CommonSetting $setting
     * @return int The ID of the newly created setting.
     */
    public function create(CommonSetting $setting): int;

    /**
     * Update the existing common setting record.
     * @param CommonSetting $setting
     * @return bool True on success, false otherwise.
     */
    public function update(CommonSetting $setting): bool;
}