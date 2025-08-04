<?php

namespace App\Http\Requests;

use App\Http\Request;
use App\Exceptions\ValidationException;

abstract class FormRequest
{
    protected Request $request;
    protected array $errors = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Define the validation rules.
     * @return array
     */
    abstract protected function rules(): array;

    /**
     * Define custom validation messages.
     * @return array
     */
    protected function messages(): array
    {
        return [];
    }

    /**
     * Perform validation and return validated data.
     * @return array
     * @throws ValidationException
     */
    public function validate(): array
    {
        $rules = $this->rules();
        $messages = $this->messages();
        $data = $this->request->all(); // Get all input data (body + query)
        $files = $this->request->files; // Get uploaded files (from $_FILES or custom parsed)

        foreach ($rules as $field => $fieldRules) {
            // Use the new helper to get the value, supporting dot notation
            // This is the key change for nested array validation
            $value = $this->getNestedValue($data, $field);
            // For file fields, we still need to check the $_FILES structure directly
            // or ensure getNestedValue can handle it if files array uses dot notation.
            // For now, we'll keep file handling separate if it's a top-level file field.
            // For nested files (e.g., 'images.0.file'), getNestedValue would work.
            $file = null;
            if (str_contains($field, '.')) { // If it's a nested path, try to get file from nested structure
                 $file = $this->getNestedValue($files, $field);
            } else { // If it's a top-level field, check files array directly
                $file = $files[$field] ?? null;
            }


            $isFileField = in_array('image', $fieldRules);
            $isNullable = in_array('nullable', $fieldRules);

            // If the field is nullable and not provided (for non-file fields) or no file uploaded (for file fields),
            // we can skip further validation for this field as it will be treated as null or omitted.
            // This logic needs to be careful with dot notation fields.
            if ($isNullable) {
                if ($isFileField) {
                    // For nullable file fields, if no file was uploaded AND the field is not present as a text input
                    // OR if it's present as an empty string (which we'll ignore later)
                    // We need to check if the file data is actually 'no file uploaded' or truly absent
                    if (($file === null || (is_array($file) && isset($file['error']) && $file['error'] === UPLOAD_ERR_NO_FILE)) &&
                        ($value === null || (is_string($value) && trim($value) === ''))) {
                        continue; // Skip further validation, this field will be omitted or null in validatedData
                    }
                } else {
                    // For nullable regular fields, if the key is not present or value is empty/null
                    if ($value === null || (is_string($value) && trim($value) === '') || (is_array($value) && empty($value))) {
                        continue; // Skip to next field, it will be null in validatedData
                    }
                }
            }


            foreach ($fieldRules as $rule) {
                $ruleParts = explode(':', $rule, 2);
                $ruleName = $ruleParts[0];
                $ruleParam = $ruleParts[1] ?? null;

                $isValid = true;
                // Use the field name with dot notation for messages if available, otherwise just the last segment
                $displayFieldName = str_replace(['.', '_'], [' ', ' '], $field);
                $errorMessage = $messages["{$field}.{$ruleName}"] ?? "The {$displayFieldName} field is invalid.";

                if ($ruleName === 'nullable') {
                    continue; // Already handled above
                }

                switch ($ruleName) {
                    case 'required':
                        // Check for required for non-file fields
                        if (!$isFileField && ($value === null || (is_string($value) && trim($value) === '') || (is_array($value) && empty($value)))) {
                            $isValid = false;
                            $errorMessage = $messages["{$field}.required"] ?? "The {$displayFieldName} field is required.";
                        }
                        // Check for required for file fields
                        if ($isFileField && ($file === null || (is_array($file) && isset($file['error']) && $file['error'] !== UPLOAD_ERR_OK))) {
                            $isValid = false;
                            $errorMessage = $messages["{$field}.required"] ?? "The {$displayFieldName} field is required.";
                        }
                        break;
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $isValid = false;
                            $errorMessage = $messages["{$field}.email"] ?? "The {$displayFieldName} must be a valid email address.";
                        }
                        break;
                    case 'min':
                        if (is_string($value)) {
                            if (strlen($value) < (int)$ruleParam) {
                                $isValid = false;
                                $errorMessage = $messages["{$field}.min"] ?? "The {$displayFieldName} must be at least {$ruleParam} characters.";
                            }
                        } elseif (is_numeric($value)) {
                            if ($value < (float)$ruleParam) {
                                $isValid = false;
                                $errorMessage = $messages["{$field}.min"] ?? "The {$displayFieldName} must be at least {$ruleParam}.";
                            }
                        } elseif (is_array($value)) { // For array min size
                            if (count($value) < (int)$ruleParam) {
                                $isValid = false;
                                $errorMessage = $messages["{$field}.min"] ?? "The {$displayFieldName} must have at least {$ruleParam} items.";
                            }
                        }
                        break;
                    case 'max':
                        if (is_string($value)) {
                            if (strlen($value) > (int)$ruleParam) {
                                $isValid = false;
                                $errorMessage = $messages["{$field}.max"] ?? "The {$displayFieldName} may not be greater than {$ruleParam} characters.";
                            }
                        } elseif (is_numeric($value)) {
                            if ($value > (float)$ruleParam) {
                                $isValid = false;
                                $errorMessage = $messages["{$field}.max"] ?? "The {$displayFieldName} may not be greater than {$ruleParam}.";
                            }
                        } elseif (is_array($value)) { // For array max size
                            if (count($value) > (int)$ruleParam) {
                                $isValid = false;
                                $errorMessage = $messages["{$field}.max"] ?? "The {$displayFieldName} may not have more than {$ruleParam} items.";
                            }
                        }
                        break;
                    case 'confirmed':
                        // For 'foo.bar' field, check 'foo.bar_confirmation'
                        $confirmField = $field . '_confirmation';
                        $confirmValue = $this->getNestedValue($data, $confirmField);
                        if ($value !== $confirmValue) {
                            $isValid = false;
                            $errorMessage = $messages["{$field}.confirmed"] ?? "The {$displayFieldName} confirmation does not match.";
                        }
                        break;
                    case 'numeric':
                        if (!is_numeric($value)) {
                            $isValid = false;
                            $errorMessage = $messages["{$field}.numeric"] ?? "The {$displayFieldName} must be a number.";
                        }
                        break;
                    case 'image':
                        // This rule primarily checks if it's a valid image file type.
                        // Specific MIME types are handled by 'mimes' rule.
                        // If file is provided and not OK, it's invalid.
                        if ($file && $file['error'] !== UPLOAD_ERR_OK && $file['error'] !== UPLOAD_ERR_NO_FILE) {
                            $isValid = false;
                            $errorMessage = $messages["{$field}.image"] ?? "The {$displayFieldName} upload failed.";
                        }
                        break;
                    case 'mimes':
                        if ($file && $file['error'] === UPLOAD_ERR_OK) {
                            $allowedExtensions = explode(',', $ruleParam);
                            $allowedMimeTypes = [];
                            foreach ($allowedExtensions as $ext) {
                                $ext = trim($ext);
                                // Map common extensions to MIME types
                                switch ($ext) {
                                    case 'jpg':
                                    case 'jpeg': $allowedMimeTypes[] = 'image/jpeg'; break;
                                    case 'png': $allowedMimeTypes[] = 'image/png'; break;
                                    case 'gif': $allowedMimeTypes[] = 'image/gif'; break;
                                    case 'webp': $allowedMimeTypes[] = 'image/webp'; break;
                                    case 'pdf': $allowedMimeTypes[] = 'application/pdf'; break;
                                    // Add more as needed
                                }
                            }

                            $finfo = new \finfo(FILEINFO_MIME_TYPE);
                            $detectedMimeType = $finfo->file($file['tmp_name']);
                            $clientMimeType = $file['type'] ?? '';

                            // Check both client-provided and detected MIME types
                            if (!in_array($clientMimeType, $allowedMimeTypes) && !in_array($detectedMimeType, $allowedMimeTypes)) {
                                $isValid = false;
                                $errorMessage = $messages["{$field}.mimes"] ?? "The {$displayFieldName} must be a file of type: " . str_replace(',', ', ', $ruleParam) . ".";
                            }
                        }
                        break;
                    case 'max_size': // Rule in KB
                        if ($file && $file['error'] === UPLOAD_ERR_OK) {
                            $maxBytes = (int)$ruleParam * 1024;
                            if ($file['size'] > $maxBytes) {
                                $isValid = false;
                                $errorMessage = $messages["{$field}.max_size"] ?? "The {$displayFieldName} may not be greater than {$ruleParam} KB.";
                            }
                        }
                        break;
                    case 'dimensions': // Example: dimensions:max_width=300,max_height=200
                        if ($file && $file['error'] === UPLOAD_ERR_OK) {
                            $dimensions = [];
                            parse_str(str_replace(',', '&', $ruleParam), $dimensions);
                            list($width, $height) = getimagesize($file['tmp_name']);

                            if (isset($dimensions['max_width']) && $width > (int)$dimensions['max_width']) {
                                $isValid = false;
                                $errorMessage = $messages["{$field}.dimensions"] ?? "The {$displayFieldName} width may not be greater than {$dimensions['max_width']} pixels.";
                            }
                            if (isset($dimensions['max_height']) && $height > (int)$dimensions['max_height']) {
                                $isValid = false;
                                $errorMessage = $messages["{$field}.dimensions"] ?? "The {$displayFieldName} height may not be greater than {$dimensions['max_height']} pixels.";
                            }
                            // Add min_width, min_height, ratio etc. if needed
                        }
                        break;
                    case 'string':
                        if (!is_string($value)) {
                            $isValid = false;
                            $errorMessage = $messages["{$field}.string"] ?? "The {$displayFieldName} must be a string.";
                        }
                        break;
                    case 'integer':
                        if (!is_numeric($value) || filter_var($value, FILTER_VALIDATE_INT) === false) { // Use filter_var for stricter integer check
                            $isValid = false;
                            $errorMessage = $messages["{$field}.integer"] ?? "The {$displayFieldName} must be an integer.";
                        }
                        break;
                    case 'in':
                        $allowedValues = explode(',', $ruleParam);
                        if (!in_array($value, $allowedValues)) {
                            $isValid = false;
                            $errorMessage = $messages["{$field}.in"] ?? "The selected {$displayFieldName} is invalid.";
                        }
                        break;
                    case 'regex':
                        // Ensure the regex pattern is correctly formatted (e.g., '/^pattern$/')
                        if (!preg_match($ruleParam, $value)) {
                            $isValid = false;
                            $errorMessage = $messages["{$field}.regex"] ?? "The {$displayFieldName} format is invalid.";
                        }
                        break;
                    case 'array': // Checks if the value is an array
                        if (!is_array($value)) {
                            $isValid = false;
                            $errorMessage = $messages["{$field}.array"] ?? "The {$displayFieldName} must be an array.";
                        }
                        break;
                    case 'exists':
                        // This rule requires database access. In a simple FormRequest,
                        // this is often delegated to the service layer or a more
                        // robust validation library. For now, we'll assume the service
                        // handles the actual database existence check.
                        // For the purpose of FormRequest, we'll just ensure it's not empty if required.
                        // If you need actual DB check here, you'd need to inject PDO or a repository.
                        $isValid = !empty($value); // Simplified check for 'exists' within FormRequest
                        $errorMessage = $messages["{$field}.exists"] ?? "The selected {$displayFieldName} does not exist.";
                        break;
                    // Add more validation rules as needed
                    default:
                        // Unknown rule, consider it valid or log an error
                        error_log("Unknown validation rule: {$ruleName} for field {$field}");
                        break;
                }

                if (!$isValid) {
                    if (!isset($this->errors[$field])) {
                        $this->errors[$field] = [];
                    }
                    $this->errors[$field][] = $errorMessage;
                    break; // Stop checking further rules for this field if one fails
                }
            }
        }

        if (!empty($this->errors)) {
            throw new ValidationException("The given data was invalid.", $this->errors);
        }

        // --- Construct validatedData carefully ---
        $validatedData = [];
        foreach ($rules as $field => $fieldRules) {
            $isFileField = in_array('image', $fieldRules);
            $isNullable = in_array('nullable', $fieldRules);

            // Get the value using dot notation for both data and files
            $fieldValue = $this->getNestedValue($data, $field);
            $fileValue = $this->getNestedValue($files, $field);

            if ($isFileField) {
                // Check if a new file was successfully uploaded for this field
                if (is_array($fileValue) && isset($fileValue['error']) && $fileValue['error'] === UPLOAD_ERR_OK) {
                    $this->setNestedValue($validatedData, $field, $fileValue);
                }
                // Check if the client explicitly sent `null` to remove an existing file
                // This is for cases where `car_photo: null` is sent in JSON/form data
                elseif ($isNullable && $fieldValue === null) {
                    $this->setNestedValue($validatedData, $field, null);
                }
                // If it's a file field and no new file was uploaded and it wasn't explicitly nulled,
                // we don't add it to validatedData. This allows the service layer to retain the old value.
            } else {
                // This is a regular data field (not a file)
                // If the field exists in the input data (even if null or empty string)
                if ($this->hasNestedValue($data, $field)) {
                    $this->setNestedValue($validatedData, $field, $fieldValue);
                }
                // If it's nullable and not present in input, set it to null in validatedData
                elseif ($isNullable) {
                    $this->setNestedValue($validatedData, $field, null);
                }
                // If it's a required non-file field and not set, it would have failed 'required' validation earlier.
            }
        }

        return $validatedData;
    }

    /**
     * Safely retrieves a nested value from an array using dot notation.
     *
     * @param array $array The array to search in.
     * @param string $path The dot-notation path (e.g., 'slabs.0.slab_id').
     * @param mixed $default The default value to return if the path is not found.
     * @return mixed
     */
    protected function getNestedValue(array $array, string $path, mixed $default = null): mixed
    {
        $keys = explode('.', $path);
        $temp = &$array; // Use reference to traverse the original array

        foreach ($keys as $key) {
            // Handle wildcard '*' for array elements
            if ($key === '*') {
                // If we encounter a wildcard, it means we're looking for any element in the current array.
                // This method is for retrieving a *single* value for a specific validation rule.
                // For 'slabs.*.slab_id', this getNestedValue will be called for 'slabs.0.slab_id', 'slabs.1.slab_id' etc.
                // So, the '*' should ideally not be part of the $path for this method.
                // If it is, it indicates a mismatch in how rules are defined vs. how values are fetched.
                // For now, return default as we can't resolve a single value for a wildcard path.
                error_log("Attempted to get nested value with wildcard in path: {$path}. This method expects concrete paths.");
                return $default;
            }

            if (is_array($temp) && array_key_exists($key, $temp)) {
                $temp = &$temp[$key];
            } else {
                return $default; // Path not found
            }
        }

        return $temp;
    }

    /**
     * Sets a nested value in an array using dot notation.
     * Creates intermediate arrays if they don't exist.
     *
     * @param array $array The array to modify (passed by reference).
     * @param string $path The dot-notation path (e.g., 'slabs.0.slab_id').
     * @param mixed $value The value to set.
     */
    protected function setNestedValue(array &$array, string $path, mixed $value): void
    {
        $keys = explode('.', $path);
        $temp = &$array;

        foreach ($keys as $index => $key) {
            if ($index === count($keys) - 1) {
                // Last key, set the value
                $temp[$key] = $value;
            } else {
                // Not the last key, ensure it's an array
                if (!isset($temp[$key]) || !is_array($temp[$key])) {
                    $temp[$key] = [];
                }
                $temp = &$temp[$key];
            }
        }
    }

    /**
     * Checks if a nested value exists in an array using dot notation.
     *
     * @param array $array The array to search in.
     * @param string $path The dot-notation path.
     * @return bool
     */
    protected function hasNestedValue(array $array, string $path): bool
    {
        $keys = explode('.', $path);
        $temp = $array; // No reference needed for checking existence

        foreach ($keys as $key) {
            if (is_array($temp) && array_key_exists($key, $temp)) {
                $temp = $temp[$key];
            } else {
                return false;
            }
        }

        return true;
    }
}