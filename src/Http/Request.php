<?php

namespace App\Http;

class Request
{
    public string $method;
    public string $uri;
    public array $headers;
    public array $body;
    public array $query;
    public array $files;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = strtok($_SERVER['REQUEST_URI'], '?');
        $this->headers = getallheaders();
        $this->query = $_GET;

        // Initialize $files and $body to empty arrays to ensure they are always set
        $this->files = [];
        $this->body = [];

        // Determine content type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        error_log("Request Method: " . $this->method);
        error_log("Content-Type Header: " . $contentType);
        error_log("Raw _POST content: " . print_r($_POST, true)); // Log $_POST
        error_log("Raw _FILES content: " . print_r($_FILES, true)); // Log $_FILES

        // Handle JSON input for POST/PUT/PATCH requests
        if (str_contains($contentType, 'application/json') && in_array($this->method, ['POST', 'PUT', 'PATCH'])) {
            $input = file_get_contents('php://input');
            $decodedInput = json_decode($input, true);
            $this->body = is_array($decodedInput) ? $decodedInput : [];
            error_log("Request Body (JSON): " . print_r($this->body, true));
        }
        // Handle application/x-www-form-urlencoded for POST (PHP populates $_POST)
        elseif (str_contains($contentType, 'application/x-www-form-urlencoded') && $this->method === 'POST') {
            $this->body = $_POST;
            error_log("Request Body (POST x-www-form-urlencoded): " . print_r($this->body, true));
        }
        // Handle multipart/form-data for POST (PHP populates $_POST and $_FILES)
        elseif (str_contains($contentType, 'multipart/form-data') && $this->method === 'POST') {
            $this->body = $_POST;
            $this->files = $_FILES; // For POST, $_FILES is directly available
            error_log("Request Body (POST Multipart/Form-data): " . print_r($this->body, true));
        }
        // Handle multipart/form-data for PUT/PATCH (PHP does NOT populate $_POST or $_FILES, must parse manually)
        elseif (str_contains($contentType, 'multipart/form-data') && in_array($this->method, ['PUT', 'PATCH'])) {
            $this->parsePutMultipart(); // This method will populate $this->body and $this->files
            error_log("Request Body (PUT/PATCH Multipart/Form-data Custom Parsed): " . print_r($this->body, true));
            error_log("Request Files (PUT/PATCH Multipart/Form-data Custom Parsed): " . print_r($this->files, true));
        }
        // Default for other methods or if no specific content type is matched (e.g., raw body)
        else {
            $input = file_get_contents('php://input');
            parse_str($input, $parsedInput); // Attempt to parse as URL-encoded if not JSON/Multipart
            $this->body = $parsedInput;
            error_log("Request Body (Default/Raw Input): " . print_r($this->body, true));
        }
    }

    /**
     * Custom parser for multipart/form-data when $_POST is not populated (e.g., for PUT requests).
     * This method directly populates $this->body and $this->files properties.
     */
    private function parsePutMultipart(): void
    {
        $rawInput = file_get_contents('php://input');
        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
        $boundary = $matches[1] ?? null;

        if (!$boundary) {
            error_log("Multipart boundary not found in Content-Type header for PUT/PATCH.");
            return;
        }

        // Split by boundary, but keep the boundary string in the parts to handle leading/trailing newlines
        $blocks = explode("--$boundary", $rawInput);
        array_pop($blocks); // Remove the last empty block after the final boundary

        $data = [];
        $files = [];

        foreach ($blocks as $block) {
            // Trim leading/trailing newlines and hyphens from the block
            $block = trim($block, "\r\n-");

            if (empty($block)) {
                continue;
            }

            // Split block into headers and body
            $parts = explode("\r\n\r\n", $block, 2);
            if (count($parts) < 2) {
                // This might be a field with empty content, or a malformed part.
                // Try to parse headers even if body is missing.
                $headers = $parts[0];
                $body = ''; // Assume empty body if not found
            } else {
                $headers = $parts[0];
                $body = $parts[1];
            }

            // Extract Content-Disposition header
            if (preg_match('/Content-Disposition: form-data; name="([^"]+)"(?:; filename="([^"]+)")?/', $headers, $matches)) {
                $name = $matches[1];
                $filename = $matches[2] ?? null;

                if ($filename) {
                    // This is a file upload
                    $tmpName = tempnam(sys_get_temp_dir(), 'php_upload_');
                    file_put_contents($tmpName, $body);

                    $files[$name] = [
                        'name' => $filename,
                        'type' => preg_match('/Content-Type: ([^\r\n]+)/', $headers, $mimeMatches) ? trim($mimeMatches[1]) : 'application/octet-stream',
                        'tmp_name' => $tmpName,
                        'error' => UPLOAD_ERR_OK,
                        'size' => strlen($body)
                    ];
                } else {
                    // This is a regular form field
                    // Ensure that even empty fields are captured
                    $data[$name] = $body;
                }
            }
        }

        $this->body = $data;
        $this->files = $files; // Assign parsed files to the Request object's files property
    }

    /**
     * Get a specific input value from body or query.
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    /**
     * Get all inputs (body + query).
     * @return array
     */
    public function all(): array
    {
        return array_merge($this->body, $this->query);
    }

    /**
     * Get uploaded file data.
     * @param string $key
     * @return array|null
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }
}