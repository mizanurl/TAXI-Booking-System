<?php

namespace App\Traits;

trait FileUploadTrait
{
    private static ?string $staticPublicRoot = null;

    private function getPublicRoot(): string
    {
        if (self::$staticPublicRoot === null) {
            self::$staticPublicRoot = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public');
            if (self::$staticPublicRoot === false) {
                error_log("CRITICAL ERROR: FileUploadTrait: public directory not found or inaccessible at " . __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public');
                self::$staticPublicRoot = '';
            }
        }
        return self::$staticPublicRoot;
    }

    protected function uploadFixedNameFile(
        array $fileData,
        string $uploadDirRelative,
        string $finalBaseFilename,
        array $allowedMimeTypes,
        int $maxFileSize
    ): ?string {
        $publicRoot = $this->getPublicRoot();
        if (empty($publicRoot)) {
            error_log("FileUploadTrait: publicRoot is not initialized. Cannot upload file.");
            return null;
        }

        $fullUploadPathAbsolute = $publicRoot . DIRECTORY_SEPARATOR . trim($uploadDirRelative, '/\\');

        error_log("FileUploadTrait: Attempting to create directory: " . $fullUploadPathAbsolute);
        if (!is_dir($fullUploadPathAbsolute)) {
            error_log("FileUploadTrait: Directory does NOT exist. Attempting mkdir.");
            if (!mkdir($fullUploadPathAbsolute, 0777, true)) {
                error_log("FileUploadTrait: FAILED to create upload directory: " . $fullUploadPathAbsolute);
                return null;
            }
            error_log("FileUploadTrait: Successfully created directory: " . $fullUploadPathAbsolute);
        } else {
            error_log("FileUploadTrait: Directory ALREADY exists: " . $fullUploadPathAbsolute);
        }

        if (!is_writable($fullUploadPathAbsolute)) {
            error_log("FileUploadTrait: CRITICAL: Target directory is NOT writable: " . $fullUploadPathAbsolute);
            return null;
        } else {
            error_log("FileUploadTrait: Target directory IS writable: " . $fullUploadPathAbsolute);
        }

        // --- File Validation ---
        if ($fileData['error'] !== UPLOAD_ERR_OK) {
            error_log("Upload error for file: " . ($fileData['name'] ?? 'N/A') . " Error code: " . $fileData['error']);
            return null;
        }

        // Determine if this is a "native" uploaded file (from $_POST) or a manually parsed one (from PUT/PATCH)
        $isNativeUploadedFile = is_uploaded_file($fileData['tmp_name']);

        // Only perform is_uploaded_file check if it's expected to be a native upload
        if (!$isNativeUploadedFile && $_SERVER['REQUEST_METHOD'] === 'POST') {
            // This case should ideally not happen if $_FILES is correctly populated for POST
            error_log("FileUploadTrait: For POST request, temporary uploaded file is not recognized by is_uploaded_file: " . $fileData['tmp_name']);
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($fileData['tmp_name']);
        if (!in_array($mimeType, $allowedMimeTypes)) {
            error_log("Invalid MIME type for file: " . ($fileData['name'] ?? 'N/A') . " Type: " . $mimeType . ". Allowed: " . implode(', ', $allowedMimeTypes));
            return null;
        }

        if ($fileData['size'] > $maxFileSize) {
            error_log("File size exceeds limit for file: " . ($fileData['name'] ?? 'N/A') . " Size: " . $fileData['size'] . " bytes. Max: " . $maxFileSize . " bytes.");
            return null;
        }

        $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
        if (empty($extension)) {
            error_log("Could not determine file extension for " . ($fileData['name'] ?? 'uploaded file') . ".");
            return null;
        }
        // --- End File Validation ---

        $finalFilename = $finalBaseFilename . '.' . $extension;
        $finalDestinationAbsolutePath = $fullUploadPathAbsolute . DIRECTORY_SEPARATOR . $finalFilename;

        error_log("FileUploadTrait: Deleting old files matching pattern: " . $fullUploadPathAbsolute . DIRECTORY_SEPARATOR . $finalBaseFilename . '.*');
        $existingFiles = glob($fullUploadPathAbsolute . DIRECTORY_SEPARATOR . $finalBaseFilename . '.*');
        foreach ($existingFiles as $existingFile) {
            if (file_exists($existingFile) && is_file($existingFile)) {
                error_log("FileUploadTrait: Attempting to delete existing file: " . $existingFile);
                clearstatcache(true, $existingFile);
                if (!unlink($existingFile)) {
                    error_log("FileUploadTrait: FAILED to delete old file: " . $existingFile . " (Possible file lock).");
                } else {
                    error_log("FileUploadTrait: Successfully deleted old file: " . $existingFile);
                }
            }
        }

        // --- Conditional File Movement ---
        $moveSuccess = false;
        error_log("FileUploadTrait: Preparing to move file. Is native uploaded file: " . ($isNativeUploadedFile ? 'Yes' : 'No') . ". Request Method: " . $_SERVER['REQUEST_METHOD']);

        if ($isNativeUploadedFile) {
            // Use move_uploaded_file for native PHP uploads (typically POST requests)
            error_log("FileUploadTrait: Using move_uploaded_file for '{$fileData['tmp_name']}' to '{$finalDestinationAbsolutePath}'.");
            $moveSuccess = move_uploaded_file($fileData['tmp_name'], $finalDestinationAbsolutePath);
        } else {
            // For manually parsed files (typically PUT/PATCH requests), use rename
            error_log("FileUploadTrait: Using rename for '{$fileData['tmp_name']}' to '{$finalDestinationAbsolutePath}'.");
            $moveSuccess = rename($fileData['tmp_name'], $finalDestinationAbsolutePath);
        }

        if ($moveSuccess) {
            error_log("FileUploadTrait: Successfully moved uploaded file.");
            return $finalFilename;
        }

        error_log("FileUploadTrait: FAILED to move uploaded file '{$fileData['tmp_name']}' to '{$finalDestinationAbsolutePath}'.");
        return null;
    }

    

    /**
     * Handles a single file upload, saving it with a unique name (CURRENT_TIMESTAMP.EXTENSION).
     * This method does NOT delete old files automatically, as the filename is unique.
     *
     * @param array $fileData The $_FILES array entry for the file.
     * @param string $uploadDirRelative The desired subdirectory within 'public/' (e.g., 'uploads/cars/').
     * @param array $allowedMimeTypes Allowed MIME types.
     * @param int $maxFileSize Max file size in bytes.
     * @return string|null The unique filename (e.g., '1721904000.png') on success, or null on failure.
     */
    protected function uploadUniqueNameFile(
        array $fileData,
        string $uploadDirRelative,
        array $allowedMimeTypes,
        int $maxFileSize
    ): ?string {
        $publicRoot = $this->getPublicRoot();
        if (empty($publicRoot)) {
            error_log("FileUploadTrait: publicRoot is not initialized. Cannot upload unique file.");
            return null;
        }

        $fullUploadPathAbsolute = $publicRoot . DIRECTORY_SEPARATOR . trim($uploadDirRelative, '/\\');

        error_log("FileUploadTrait: Attempting to create directory for unique file: " . $fullUploadPathAbsolute);
        if (!is_dir($fullUploadPathAbsolute)) {
            if (!mkdir($fullUploadPathAbsolute, 0777, true)) {
                error_log("FileUploadTrait: FAILED to create upload directory for unique file: " . $fullUploadPathAbsolute);
                return null;
            }
        }

        if (!is_writable($fullUploadPathAbsolute)) {
            error_log("FileUploadTrait: CRITICAL: Target directory for unique file is NOT writable: " . $fullUploadPathAbsolute);
            return null;
        }

        // --- File Validation ---
        if ($fileData['error'] !== UPLOAD_ERR_OK) {
            error_log("Upload error for unique file: " . ($fileData['name'] ?? 'N/A') . " Error code: " . $fileData['error']);
            return null;
        }

        $isNativeUploadedFile = is_uploaded_file($fileData['tmp_name']);

        if (!$isNativeUploadedFile && $_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SERVER['CONTENT_TYPE']) || !str_contains($_SERVER['CONTENT_TYPE'], 'multipart/form-data')) {
                error_log("FileUploadTrait: Non-multipart POST request with non-native uploaded file for unique file: " . $fileData['tmp_name']);
                return null;
            }
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($fileData['tmp_name']);
        if (!in_array($mimeType, $allowedMimeTypes)) {
            error_log("Invalid MIME type for unique file: " . ($fileData['name'] ?? 'N/A') . " Type: " . $mimeType . ". Allowed: " . implode(', ', $allowedMimeTypes));
            return null;
        }

        if ($fileData['size'] > $maxFileSize) {
            error_log("File size exceeds limit for unique file: " . ($fileData['name'] ?? 'N/A') . " Size: " . $fileData['size'] . " bytes. Max: " . $maxFileSize . " bytes.");
            return null;
        }

        $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
        if (empty($extension)) {
            error_log("Could not determine file extension for unique uploaded file " . ($fileData['name'] ?? 'N/A') . ".");
            return null;
        }
        // --- End File Validation ---

        $uniqueFilename = time() . '.' . $extension; // CURRENT_TIMESTAMP.EXTENSION
        $finalDestinationAbsolutePath = $fullUploadPathAbsolute . DIRECTORY_SEPARATOR . $uniqueFilename;

        $moveSuccess = false;
        error_log("FileUploadTrait: Preparing to move unique file. Is native uploaded file: " . ($isNativeUploadedFile ? 'Yes' : 'No') . ". Request Method: " . $_SERVER['REQUEST_METHOD']);

        if ($isNativeUploadedFile) {
            error_log("FileUploadTrait: Using move_uploaded_file for unique file '{$fileData['tmp_name']}' to '{$finalDestinationAbsolutePath}'.");
            $moveSuccess = move_uploaded_file($fileData['tmp_name'], $finalDestinationAbsolutePath);
        } else {
            error_log("FileUploadTrait: Using rename for unique file '{$fileData['tmp_name']}' to '{$finalDestinationAbsolutePath}'.");
            $moveSuccess = rename($fileData['tmp_name'], $finalDestinationAbsolutePath);
        }

        if ($moveSuccess) {
            error_log("FileUploadTrait: Successfully moved unique uploaded file.");
            return $uniqueFilename;
        }

        error_log("FileUploadTrait: FAILED to move unique uploaded file '{$fileData['tmp_name']}' to '{$finalDestinationAbsolutePath}'.");
        return null;
    }

    protected function deleteFile(string $filePathRelative): bool
    {
        $publicRoot = $this->getPublicRoot();
        if (empty($publicRoot)) {
            error_log("FileUploadTrait: publicRoot is not initialized. Cannot delete file.");
            return false;
        }
        $fullFilePathAbsolute = $publicRoot . DIRECTORY_SEPARATOR . trim($filePathRelative, '/\\');
        error_log("FileUploadTrait: Attempting to delete file: " . $fullFilePathAbsolute);
        if (file_exists($fullFilePathAbsolute)) {
            clearstatcache(true, $fullFilePathAbsolute);
            if (unlink($fullFilePathAbsolute)) {
                error_log("FileUploadTrait: Successfully deleted file: " . $fullFilePathAbsolute);
                return true;
            } else {
                error_log("FileUploadTrait: FAILED to delete file: " . $fullFilePathAbsolute . " (Possible file lock).");
                return false;
            }
        }
        error_log("FileUploadTrait: File not found for deletion: " . $fullFilePathAbsolute);
        return false;
    }
}