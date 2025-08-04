<?php

namespace App\Http;

class Response
{
    // Removed setCorsHeaders() and handlePreflight() as they are now in public/index.php

    /**
     * Send a JSON response.
     * @param array $data
     * @param int $statusCode
     * @param array $headers
     */
    public function json(array $data, int $statusCode = 200, array $headers = []): void
    {
        // CORS headers are now handled globally in public/index.php
        http_response_code($statusCode);
        header('Content-Type: application/json');
        foreach ($headers as $name => $value) {
            header("{$name}: {$value}");
        }
        echo json_encode($data);
        exit; // Terminate script execution after sending response
    }

    /**
     * Send an error response.
     * @param string $message
     * @param int $statusCode
     * @param array $errors Optional array of validation errors.
     */
    public function error(string $message, int $statusCode = 500, array $errors = []): void
    {
        // CORS headers are now handled globally in public/index.php
        $response = [
            'status' => 'error',
            'message' => $message,
        ];
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        $this->json($response, $statusCode); // Use json method to send the response
    }

    /**
     * Renders a PHP view file and outputs its content.
     * This method is for direct view rendering without a layout.
     *
     * @param string $viewPath The path to the view file relative to src/Views/.
     * E.g., 'Admin/Auth/login.php' maps to src/Views/Admin/Auth/login.php
     * @param array $data An associative array of data to pass to the view.
     */
    public function render(string $viewPath, array $data = []): void
    {
        $fullViewPath = __DIR__ . '/../Views/' . $viewPath;

        if (!file_exists($fullViewPath)) {
            throw new \Exception("View file not found: " . $fullViewPath);
        }

        // Start output buffering to capture the view's content
        ob_start();

        // Extract data variables so they are available directly in the view
        // e.g., $data['name'] becomes $name in the view
        extract($data);

        // Include the view file. This executes the PHP code within the view.
        require $fullViewPath;

        // Get the buffered content and clean the buffer
        $content = ob_get_clean(); // This is the content of the specific view (e.g., home.php)

        // We need to ensure $appUrl is available to the layout.
        $appUrl = $data['appUrl'] ?? getenv('APP_URL') ?: '/'; // Make $appUrl available to the layout

        // Include the layout file. The $content variable (from ob_get_clean above)
        // and $appUrl (from this scope) will be available within the layout.
        require __DIR__ . '/../Views/layouts/frontend.php';

        exit(); // Terminate script execution
    }

    /**
     * Renders a view within a specified layout.
     *
     * @param string $viewPath The path to the content view file relative to src/Views/.
     * E.g., 'frontend.home' maps to src/Views/frontend/home.php
     * @param array $data An associative array of data to pass to the view and layout.
     * @param string $layoutPath The path to the layout file relative to src/Views/layouts/.
     * E.g., 'frontend' maps to src/Views/layouts/frontend.php
     */
    public function view(string $viewPath, array $data = [], string $layoutPath = 'layouts.frontend'): void
    {
        try {
            // Render the content view first
            $content = $this->includeView($viewPath, $data);

            // Add the rendered content to the data array for the layout
            $data['content'] = $content;

            // Construct the full path to the layout file
            $fullLayoutPath = __DIR__ . '/../Views/' . str_replace('.', '/', $layoutPath) . '.php';

            if (!file_exists($fullLayoutPath)) {
                error_log("Layout file not found: " . $fullLayoutPath);
                throw new \Exception("Server Error: Layout template not found: " . $layoutPath);
            }

            // Extract data for the layout
            extract($data);

            // Start output buffering for the layout
            ob_start();
            require $fullLayoutPath;
            $finalContent = ob_get_clean();

            http_response_code(200);
            header('Content-Type: text/html; charset=UTF-8');
            echo $finalContent;
            exit(); // Terminate script execution after sending response

        } catch (\Exception $e) {
            // Handle view rendering errors gracefully
            error_log("View rendering error: " . $e->getMessage());
            $this->error("Server Error: Unable to render page.", 500);
        }
    }

    /**
     * Helper method to include a view file and capture its output.
     *
     * @param string $viewPath The path to the view file relative to src/Views/.
     * @param array $data An associative array of data to pass to the view.
     * @return string The rendered content of the view.
     * @throws \Exception If the view file is not found.
     */
    protected function includeView(string $viewPath, array $data = []): string
    {
        $fullViewPath = __DIR__ . '/../Views/' . str_replace('.', '/', $viewPath) . '.php';

        if (!file_exists($fullViewPath)) {
            throw new \Exception("View file not found: " . $fullViewPath);
        }

        ob_start();
        extract($data); // Make data variables available in the view
        require $fullViewPath;
        return ob_get_clean();
    }

    /**
     * Sends an HTTP redirect header to the browser.
     *
     * @param string $url The URL to redirect to.
     * @param int $statusCode The HTTP status code for the redirect (e.g., 302 Found).
     */
    public function redirect(string $url, int $statusCode = 302): void
    {
        http_response_code($statusCode);
        header("Location: " . $url);
        exit(); // Important to exit after sending redirect header
    }

    /**
     * Send a success response.
     * @param string $message
     * @param array $data
     * @param int $statusCode
     */
    public function success(string $message, array $data = [], int $statusCode = 200): void
    {
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ];
        $this->json($response, $statusCode);
    }
}