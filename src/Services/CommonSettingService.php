<?php

namespace App\Services;

use App\Models\CommonSetting;
use App\Repositories\Contracts\CommonSettingInterface;
use App\Traits\FileUploadTrait;
use App\Exceptions\DuplicateEntryException;
use App\Exceptions\NotFoundException;

class CommonSettingService
{
    use FileUploadTrait;

    private CommonSettingInterface $commonSettingRepository;

    // Define constants for logo upload
    private const LOGO_UPLOAD_DIR_RELATIVE = 'uploads/company/'; // Relative path from public/
    private const LOGO_ALLOWED_MIMES = ['image/jpeg', 'image/gif', 'image/png', 'image/webp'];
    private const LOGO_MAX_SIZE_BYTES = 100 * 1024; // 100 KB in bytes
    private const LOGO_MAX_WIDTH = 300;
    private const LOGO_MAX_HEIGHT = 200;
    private const LOGO_FILENAME_BASE = 'company_logo'; // Base name, extension will be added

    public function __construct(CommonSettingInterface $commonSettingRepository)
    {
        $this->commonSettingRepository = $commonSettingRepository;
    }

    /**
     * Get the single common setting record.
     * @return CommonSetting|null
     */
    public function getCommonSettings(): ?CommonSetting
    {
        return $this->commonSettingRepository->get();
    }

    /**
     * Create the initial common setting record.
     * This method should only be called once.
     *
     * @param array $data All request data.
     * @param array|null $fileData The $_FILES entry for 'company_logo'.
     * @return CommonSetting
     * @throws DuplicateEntryException If a record already exists.
     * @throws \Exception If creation fails or file upload/validation fails.
     */
    public function createCommonSettings(array $data, ?array $fileData = null): CommonSetting
    {
        // Check if a record already exists
        if ($this->commonSettingRepository->get()) {
            throw new DuplicateEntryException("Common settings already exist. Use the update API instead.");
        }

        $companyLogoFilenameForDb = null; // This will store just the filename (e.g., company_logo.png)

        if ($fileData && $fileData['error'] === UPLOAD_ERR_OK) {
            // Validate image dimensions from the temporary uploaded file (before moving permanently)
            list($width, $height) = getimagesize($fileData['tmp_name']);
            if ($width > self::LOGO_MAX_WIDTH || $height > self::LOGO_MAX_HEIGHT) {
                throw new \InvalidArgumentException("Company logo dimensions exceed maximum allowed: " . self::LOGO_MAX_WIDTH . "x" . self::LOGO_MAX_HEIGHT . " pixels.");
            }

            // Use the new trait method to upload and rename the file
            $companyLogoFilenameForDb = $this->uploadFixedNameFile(
                $fileData,
                self::LOGO_UPLOAD_DIR_RELATIVE,
                self::LOGO_FILENAME_BASE,
                self::LOGO_ALLOWED_MIMES,
                self::LOGO_MAX_SIZE_BYTES
            );

            if (!$companyLogoFilenameForDb) {
                throw new \Exception("Failed to upload company logo.");
            }
        }

        $setting = new CommonSetting(
            id: null,
            companyName: $data['company_name'],
            address: $data['address'],
            email: $data['email'],
            telephoneNumber: $data['telephone_number'],
            status: (int) $data['status'],
            companyLogo: $companyLogoFilenameForDb, // Store just the filename
            bookingCallNumber: $data['booking_call_number'] ?? null,
            website: $data['website'] ?? null,
            holidays: $data['holidays'] ?? null,
            holidaySurcharge: (float) ($data['holiday_surcharge'] ?? 0.00),
            tunnelCharge: (float) ($data['tunnel_charge'] ?? 0.00),
            gratuity: (float) ($data['gratuity'] ?? 0.00),
            stopOverCharge: (float) ($data['stop_over_charge'] ?? 0.00),
            infantFrontFacingSeatCharge: (float) ($data['infant_front_facing_seat_charge'] ?? 0.00),
            infantRearFacingSeatCharge: (float) ($data['infant_rear_facing_seat_charge'] ?? 0.00),
            infantBoosterSeatCharge: (float) ($data['infant_booster_seat_charge'] ?? 0.00),
            nightCharge: (float) ($data['night_charge'] ?? 0.00),
            nightChargeStartTime: $data['night_charge_start_time'] ?? null,
            nightChargeEndTime: $data['night_charge_end_time'] ?? null,
            hiddenNightCharge: (float) ($data['hidden_night_charge'] ?? 0.00),
            hiddenNightChargeStartTime: $data['hidden_night_charge_start_time'] ?? null,
            hiddenNightChargeEndTime: $data['hidden_night_charge_end_time'] ?? null,
            snowStromCharge: (float) ($data['snow_strom_charge'] ?? 0.00),
            rushHourCharge: (float) ($data['rush_hour_charge'] ?? 0.00),
            extraLuggageCharge: (float) ($data['extra_luggage_charge'] ?? 0.00),
            petsCharge: (float) ($data['pets_charge'] ?? 0.00),
            convenienceFee: (float) ($data['convenience_fee'] ?? 0.00),
            cashDiscount: (float) ($data['cash_discount'] ?? 0.00),
            paypalCharge: (float) ($data['paypal_charge'] ?? 0.00),
            squareCharge: (float) ($data['square_charge'] ?? 0.00),
            creditCardCharge: (float) ($data['credit_card_charge'] ?? 0.00)
        );

        $newId = $this->commonSettingRepository->create($setting);
        if (!$newId) {
            // If creation fails at DB level, attempt to delete uploaded logo if it exists
            if ($companyLogoFilenameForDb) {
                $this->deleteFile(self::LOGO_UPLOAD_DIR_RELATIVE . $companyLogoFilenameForDb);
            }
            throw new \Exception("Failed to create common settings.");
        }
        $setting->id = $newId;

        return $setting;
    }

    /**
     * Update the existing common setting record.
     *
     * @param array $data All request data (validated).
     * @param array|null $fileData The $_FILES entry for 'company_logo' (raw file data).
     * @return CommonSetting
     * @throws NotFoundException If no common settings record exists.
     * @throws \Exception If update fails or file upload/validation fails.
     */
    public function updateCommonSettings(array $data, ?array $fileData = null): CommonSetting
    {
        $setting = $this->commonSettingRepository->get();

        if (!$setting) {
            throw new NotFoundException("Common settings record not found. Please create it first.");
        }

        $currentLogoFilenameInDb = $setting->companyLogo; // This is the filename currently in the DB

        // Determine the logo filename for the database after this update operation
        $logoFilenameForDb = $currentLogoFilenameInDb; // Default: keep the current logo

        // Case 1: A new file was uploaded successfully
        if ($fileData && $fileData['error'] === UPLOAD_ERR_OK) {
            // Validate image dimensions from the temporary uploaded file (before moving permanently)
            list($width, $height) = getimagesize($fileData['tmp_name']);
            if ($width > self::LOGO_MAX_WIDTH || $height > self::LOGO_MAX_HEIGHT) {
                throw new \InvalidArgumentException("Company logo dimensions exceed maximum allowed: " . self::LOGO_MAX_WIDTH . "x" . self::LOGO_MAX_HEIGHT . " pixels.");
            }

            // Use the trait method to upload and rename the file.
            // This method handles deleting old versions and returns the new filename.
            $uploadedFilename = $this->uploadFixedNameFile(
                $fileData,
                self::LOGO_UPLOAD_DIR_RELATIVE,
                self::LOGO_FILENAME_BASE,
                self::LOGO_ALLOWED_MIMES,
                self::LOGO_MAX_SIZE_BYTES
            );

            if (!$uploadedFilename) {
                throw new \Exception("Failed to upload new company logo.");
            }
            $logoFilenameForDb = $uploadedFilename; // Set to the new filename
        }
        // Case 2: The 'company_logo' field was explicitly sent as null in the request
        // This signifies an intention to remove the existing logo.
        // This relies on FormRequest converting an empty string to null, which it does.
        // We need to ensure we only act if the key exists AND it's null.
        // If the key is *not* present in $data, it means it was omitted, and we should preserve.
        // If the key *is* present and its value is null, it means explicit removal.
        // The `!$fileData` check ensures we're not trying to remove when a file was actually uploaded.
        elseif (!$fileData && array_key_exists('company_logo', $data) && $data['company_logo'] === null) {
            if ($currentLogoFilenameInDb) {
                $this->deleteFile(self::LOGO_UPLOAD_DIR_RELATIVE . $currentLogoFilenameInDb); // Delete the old file
            }
            $logoFilenameForDb = null; // Set logo path to null in DB
        }
        // Case 3: No new file was uploaded AND the 'company_logo' field was NOT explicitly sent as null.
        // This covers two scenarios:
        //   a) The 'company_logo' field was completely omitted from the request.
        //   b) The 'company_logo' field was sent as an empty string (which FormRequest converts to null),
        //      but we don't want to treat it as an explicit removal.
        // In both these scenarios, we want to preserve the existing logo.
        // No code needed here, as $logoFilenameForDb already defaults to $currentLogoFilenameInDb.
        // The previous `elseif` handles explicit null. If we reach here, and $fileData is empty,
        // it means no new file, and no explicit null, so keep current.


        // Update properties only if they are provided in the data
        $setting->companyName = $data['company_name'] ?? $setting->companyName;
        $setting->companyLogo = $logoFilenameForDb; // Use the determined filename for DB
        $setting->address = $data['address'] ?? $setting->address;
        $setting->bookingCallNumber = $data['booking_call_number'] ?? $setting->bookingCallNumber;
        $setting->telephoneNumber = $data['telephone_number'] ?? $setting->telephoneNumber;
        $setting->email = $data['email'] ?? $setting->email;
        $setting->website = $data['website'] ?? $setting->website;
        $setting->holidays = $data['holidays'] ?? $setting->holidays;
        $setting->holidaySurcharge = (float) ($data['holiday_surcharge'] ?? $setting->holidaySurcharge);
        $setting->tunnelCharge = (float) ($data['tunnel_charge'] ?? $setting->tunnelCharge);
        $setting->gratuity = (float) ($data['gratuity'] ?? $setting->gratuity);
        $setting->stopOverCharge = (float) ($data['stop_over_charge'] ?? $setting->stopOverCharge);
        $setting->infantFrontFacingSeatCharge = (float) ($data['infant_front_facing_seat_charge'] ?? $setting->infantFrontFacingSeatCharge);
        $setting->infantRearFacingSeatCharge = (float) ($data['infant_rear_facing_seat_charge'] ?? $setting->infantRearFacingSeatCharge);
        $setting->infantBoosterSeatCharge = (float) ($data['infant_booster_seat_charge'] ?? $setting->infantBoosterSeatCharge);
        $setting->nightCharge = (float) ($data['night_charge'] ?? $setting->nightCharge);
        $setting->nightChargeStartTime = $data['night_charge_start_time'] ?? $setting->nightChargeStartTime;
        $setting->nightChargeEndTime = $data['night_charge_end_time'] ?? $setting->nightChargeEndTime;
        $setting->hiddenNightCharge = (float) ($data['hidden_night_charge'] ?? $setting->hiddenNightCharge);
        $setting->hiddenNightChargeStartTime = $data['hidden_night_charge_start_time'] ?? $setting->hiddenNightChargeStartTime;
        $setting->hiddenNightChargeEndTime = $data['hidden_night_charge_end_time'] ?? $setting->hiddenNightChargeEndTime;
        $setting->snowStromCharge = (float) ($data['snow_strom_charge'] ?? $setting->snowStromCharge);
        $setting->rushHourCharge = (float) ($data['rush_hour_charge'] ?? $setting->rushHourCharge);
        $setting->extraLuggageCharge = (float) ($data['extra_luggage_charge'] ?? $setting->extraLuggageCharge);
        $setting->petsCharge = (float) ($data['pets_charge'] ?? $setting->petsCharge);
        $setting->convenienceFee = (float) ($data['convenience_fee'] ?? $setting->convenienceFee);
        $setting->cashDiscount = (float) ($data['cash_discount'] ?? $setting->cashDiscount);
        $setting->paypalCharge = (float) ($data['paypal_charge'] ?? $setting->paypalCharge);
        $setting->squareCharge = (float) ($data['square_charge'] ?? $setting->squareCharge);
        $setting->creditCardCharge = (float) ($data['credit_card_charge'] ?? $setting->creditCardCharge);
        $setting->status = (int) ($data['status'] ?? $setting->status);

        $setting->updatedAt = date('Y-m-d H:i:s'); // Update timestamp

        if (!$this->commonSettingRepository->update($setting)) {
            throw new \Exception("Failed to update common settings.");
        }

        return $setting;
    }
}