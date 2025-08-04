<?php

namespace App\Core;

use App\Config\AppConfig;
use App\Config\DatabaseConfig;
use App\Core\Router;
use App\Core\Database;
use App\Http\Request;
use App\Http\Response;

// Import all necessary Controllers
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\AuthController;
use App\Controllers\Frontend\HomeController;

use App\Controllers\Api\AirportController;
use App\Controllers\Api\CarController;
use App\Controllers\Api\CommonSettingController;
use App\Controllers\Api\GoogleApiKeyController;
use App\Controllers\Api\LocationController;
use App\Controllers\Api\SlabController;
use App\Controllers\Api\TunnelChargeController;
use App\Controllers\Api\ExtraChargeController;
use App\Controllers\Api\SmsServiceController;
use App\Controllers\Api\FareCalculatorController;

// Import all necessary Repositories
use App\Repositories\MySQL\AirportDatabase;
use App\Repositories\MySQL\CommonSettingDatabase;
use App\Repositories\MySQL\GoogleApiKeyDatabase;
use App\Repositories\MySQL\LocationDatabase;
use App\Repositories\MySQL\SlabDatabase;
use App\Repositories\MySQL\CarDatabase;
use App\Repositories\MySQL\CarSlabFareDatabase;
use App\Repositories\MySQL\TunnelChargeDatabase;
use App\Repositories\MySQL\ExtraChargeDatabase;
use App\Repositories\MySQL\SmsServiceDatabase;
use App\Repositories\MySQL\UserDatabase;

// Import all necessary Services
use App\Services\AirportService;
use App\Services\AuthService;
use App\Services\CommonSettingService;
use App\Services\GoogleApiKeyService;
use App\Services\LocationService;
use App\Services\SlabService;
use App\Services\CarService;
use App\Services\TunnelChargeService;
use App\Services\ExtraChargeService;
use App\Services\SmsServiceService;
use App\Services\GoogleMapsService;
use App\Services\DashboardService;
use App\Services\FareCalculatorService;

use App\Exceptions\NotFoundException;
use App\Exceptions\MethodNotAllowedException;
use App\Exceptions\ValidationException;

use PDOException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LogLevel;

use Dotenv\Dotenv;

class App
{
    private Router $router;
    private DatabaseConfig $dbConfig;
    private AppConfig $appConfig;
    private Request $request;
    private Response $response;
    private ?\PDO $dbConnection = null;
    private Logger $logger;

    public function __construct(Router $router, DatabaseConfig $dbConfig, AppConfig $appConfig)
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../'); // Path to the project root (where .env is)
        $dotenv->load();

        $this->router = $router;
        $this->dbConfig = $dbConfig;
        $this->appConfig = $appConfig;
        $this->request = new Request();
        $this->response = new Response();

        // Initialize Logger
        $this->logger = new Logger('taxi-api');

        // Define the log directory path
        $logDirectory = __DIR__ . '/../../logs'; // This resolves to your project_root/logs/

        // Ensure the log directory exists and is writable
        if (!is_dir($logDirectory)) {
            if (!mkdir($logDirectory, 0777, true) && !is_dir($logDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $logDirectory));
            }
        }

        $this->logger->pushHandler(new StreamHandler($logDirectory . '/app.log', LogLevel::WARNING));

        // Set up error handling
        $this->setupErrorHandling();
    }

    private function setupErrorHandling(): void
    {
        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'handleFatalError']);
    }

    public function handleException(\Throwable $exception): void
    {
        $statusCode = 500;
        $message = 'An unexpected error occurred.';
        $errors = [];

        if ($exception instanceof NotFoundException) {
            $statusCode = 404;
            $message = $exception->getMessage();
        } elseif ($exception instanceof MethodNotAllowedException) {
            $statusCode = 405;
            $message = $exception->getMessage();
        } elseif ($exception instanceof ValidationException) {
            $statusCode = 422; // Unprocessable Entity
            $message = $exception->getMessage();
            $errors = $exception->getErrors();
        } elseif ($exception instanceof PDOException) {
            $statusCode = 500;
            $message = 'Database error: ' . $exception->getMessage();
            $this->logger->error("PDOException: " . $exception->getMessage(), [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
                'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
            ]);
        } else {
            // Log other unexpected errors
            $this->logger->error("Uncaught Exception: " . $exception->getMessage(), [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
                'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
            ]);
            // In production, avoid exposing detailed error messages
            if ($this->appConfig->debug) {
                $message = $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
            }
        }

        $this->response->error($message, $statusCode, $errors);
    }

    public function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public function handleFatalError(): void
    {
        $error = error_get_last();
        if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_COMPILE_ERROR)) {
            $message = 'A fatal error occurred.';
            if ($this->appConfig->debug) {
                $message = "Fatal Error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line'];
            }
            $this->logger->critical($message, ['error_details' => $error]);
            $this->response->error($message, 500);
        }
    }


    /**
     * Run the application.
     */
    public function run(): void
    {
        try {
            $this->dbConnection = Database::getInstance($this->dbConfig);
            $this->defineRoutes();
            list($handler, $params) = $this->router->resolve();
            $this->executeHandler($handler, $params);
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    /**
     * Defines all application routes.
     */
    private function defineRoutes(): void
    {
        // Frontend Root Route
        $this->router->get('/', \App\Controllers\Frontend\HomeController::class . '@index');

        // Admin Login/Dashboard Routes (SSR PHP)
        $this->router->get('/admin', DashboardController::class . '@index'); // Redirects to login if not authenticated
        $this->router->get('/admin/dashboard', DashboardController::class . '@index');
        $this->router->get('/admin/login', AuthController::class . '@showLoginForm'); // Show login form
        $this->router->post('/admin/login', AuthController::class . '@login'); // Handle login submission
        $this->router->post('/admin/logout', AuthController::class . '@logout'); // Handle logout

        // API Routes (Existing and New)
        // Moved to Api/ subdirectory for clarity
        $this->router->get('/api/v1/airports', AirportController::class . '@index');
        // Corrected route for active airports to match frontend call and controller method
        $this->router->get('/api/v1/airports/active', AirportController::class . '@activeAirports');
        $this->router->get('/api/v1/airports/{id}', AirportController::class . '@show');
        $this->router->post('/api/v1/airports', AirportController::class . '@store');
        $this->router->put('/api/v1/airports/{id}', AirportController::class . '@update');
        $this->router->delete('/api/v1/airports/{id}', AirportController::class . '@destroy');

        $this->router->get('/api/v1/google-api-keys', GoogleApiKeyController::class . '@index');
        $this->router->get('/api/v1/google-api-keys/active/single', GoogleApiKeyController::class . '@getOneActive');
        $this->router->get('/api/v1/google-api-keys/{id}', GoogleApiKeyController::class . '@show');
        $this->router->post('/api/v1/google-api-keys', GoogleApiKeyController::class . '@store');
        $this->router->put('/api/v1/google-api-keys/{id}', GoogleApiKeyController::class . '@update');

        $this->router->get('/api/v1/locations/suggest', LocationController::class . '@suggest');

        $this->router->get('/api/v1/settings', CommonSettingController::class . '@get');
        $this->router->post('/api/v1/settings', CommonSettingController::class . '@create');
        $this->router->put('/api/v1/settings', CommonSettingController::class . '@update');

        $this->router->get('/api/v1/slabs', SlabController::class . '@index');
        $this->router->get('/api/v1/slabs/{id}', SlabController::class . '@show');
        $this->router->post('/api/v1/slabs', SlabController::class . '@store');
        $this->router->put('/api/v1/slabs/{id}', SlabController::class . '@update');
        $this->router->delete('/api/v1/slabs/{id}', SlabController::class . '@destroy');

        $this->router->get('/api/v1/cars', CarController::class . '@index');
        $this->router->get('/api/v1/cars/{id}', CarController::class . '@show');
        $this->router->post('/api/v1/cars', CarController::class . '@store');
        $this->router->put('/api/v1/cars/{id}', CarController::class . '@update');
        $this->router->delete('/api/v1/cars/{id}', CarController::class . '@destroy');
        $this->router->post('/api/v1/cars/{carId}/slabs', CarController::class . '@assignSlabs');
        $this->router->delete('/api/v1/cars/{carId}/slabs/{slabFareId}', CarController::class . '@deleteSlabFare');
        $this->router->put('/api/v1/cars/{carId}/slabs/{slabFareId}', CarController::class . '@updateSlabFare');

        $this->router->get('/api/v1/tunnel-charges', TunnelChargeController::class . '@index');
        $this->router->get('/api/v1/tunnel-charges/{id}', TunnelChargeController::class . '@show');
        $this->router->post('/api/v1/tunnel-charges', TunnelChargeController::class . '@store');
        $this->router->put('/api/v1/tunnel-charges/{id}', TunnelChargeController::class . '@update');
        $this->router->delete('/api/v1/tunnel-charges/{id}', TunnelChargeController::class . '@destroy');

        $this->router->get('/api/v1/extra-charges', ExtraChargeController::class . '@index');
        $this->router->get('/api/v1/extra-charges/{id}', ExtraChargeController::class . '@show');
        $this->router->post('/api/v1/extra-charges', ExtraChargeController::class . '@store');
        $this->router->put('/api/v1/extra-charges/{id}', ExtraChargeController::class . '@update');
        $this->router->delete('/api/v1/extra-charges/{id}', ExtraChargeController::class . '@destroy');

        $this->router->get('/api/v1/sms-services', SmsServiceController::class . '@index');
        $this->router->get('/api/v1/sms-services/{id}', SmsServiceController::class . '@show');
        $this->router->post('/api/v1/sms-services', SmsServiceController::class . '@store');
        $this->router->put('/api/v1/sms-services/{id}', SmsServiceController::class . '@update');
        $this->router->delete('/api/v1/sms-services/{id}', SmsServiceController::class . '@destroy');

        $this->router->post('/api/v1/fare-calculation', FareCalculatorController::class . '@calculate');

        // NEW: Booking and Payment APIs will go here later
        // $this->router->post('/api/v1/bookings', \App\Controllers\Api\BookingController::class . '@store');
        // $this->router->post('/api/v1/payments/process', \App\Controllers\Api\PaymentController::class . '@process');
    }

    /**
     * Executes the route handler.
     * @param callable|string $handler
     * @param array $params
     */
    private function executeHandler(callable|string $handler, array $params): void
    {
        // Start session at the beginning of executeHandler
        // This ensures session is available for all PHP-handled requests (APIs and Admin UI)
        if (session_status() == PHP_SESSION_NONE) {
            // Set secure session cookie parameters before starting the session
            // IMPORTANT: These settings require HTTPS for 'secure' flag to work.
            // For local development on HTTP, you might need to temporarily set 'secure' to false.
            // For production, ALWAYS true.
            session_set_cookie_params([
                'lifetime' => 0, // Session cookie lasts until browser is closed
                'path' => '/',
                'domain' => '', // Empty for current domain
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // Only send over HTTPS
                'httponly' => true, // Prevent JavaScript access to cookie
                'samesite' => 'Lax' // Protect against CSRF
            ]);
            session_start();
        }

        if (is_callable($handler)) {
            // If the handler is a closure (like our '/' route), execute it directly.
            call_user_func_array($handler, $params);
        } elseif (is_string($handler) && str_contains($handler, '@')) {
            // If the handler is a string like 'ControllerClass@method', parse it.
            list($controllerClass, $method) = explode('@', $handler);

            // Special handling for DashboardController and AuthController to inject multiple services
            // and ensure Request/Response are passed correctly.
            if ($controllerClass === DashboardController::class) {
                $userRepository = new UserDatabase($this->dbConnection);
                $authService = new AuthService($userRepository);
                $dashboardService = new DashboardService();

                $controllerInstance = new $controllerClass(
                    $authService,
                    $dashboardService,
                    $this->request,
                    $this->response
                );
            } elseif ($controllerClass === AuthController::class) {
                $userRepository = new UserDatabase($this->dbConnection);
                $authService = new AuthService($userRepository);
                $controllerInstance = new $controllerClass(
                    $authService,
                    $this->request,
                    $this->response
                );
            } elseif ($controllerClass === HomeController::class) {
                // Frontend controllers might not need a service for simple pages, or could have a generic one
                $airportRepository = new AirportDatabase($this->dbConnection);
                $airportService = new AirportService($airportRepository);
                $commonSettingRepository = new CommonSettingDatabase($this->dbConnection);
                $commonSettingService = new CommonSettingService($commonSettingRepository);
                $controllerInstance = new $controllerClass(
                    $this->request,
                    $this->response,
                    $airportService,
                    $commonSettingService
                );
            } else {
                // For all other controllers (API controllers), assume the first argument is THE service
                $controllerInstance = new $controllerClass(
                    $this->getServiceForController($controllerClass), // Dynamically get the service
                    $this->request,
                    $this->response
                );
            }
            call_user_func_array([$controllerInstance, $method], $params);
        } else {
            // Handle cases where the handler format is unexpected.
            $this->response->error("Invalid route handler provided.", 500);
        }
    }

    /**
     * Helper to get service instance based on controller class name.
     * This is a simplified example and might need more robust dependency injection
     * for a larger application (e.g., a service container).
     * @param string $controllerClass
     * @return object
     * @throws \Exception
     */
    private function getServiceForController(string $controllerClass): object
    {
        // This is a basic mapping. For a real application, consider a DIC.
        switch ($controllerClass) {

            // API Controllers (Existing ones moved to Api namespace)
            case AirportController::class:
                $airportRepository = new AirportDatabase($this->dbConnection);
                return new AirportService($airportRepository);

            case CommonSettingController::class:
                $commonSettingRepository = new CommonSettingDatabase($this->dbConnection);
                return new CommonSettingService($commonSettingRepository);

            case GoogleApiKeyController::class:
                $googleApiKeyRepository = new GoogleApiKeyDatabase($this->dbConnection);
                return new GoogleApiKeyService($googleApiKeyRepository);

            case LocationController::class:
                $locationRepository = new LocationDatabase($this->dbConnection);
                return new LocationService($locationRepository);

            case SlabController::class:
                $slabRepository = new SlabDatabase($this->dbConnection);
                return new SlabService($slabRepository);

            case CarController::class:
                $carRepository = new CarDatabase($this->dbConnection);
                $carSlabFareRepository = new CarSlabFareDatabase($this->dbConnection);
                return new CarService($carRepository, $carSlabFareRepository);

            case TunnelChargeController::class:
                $tunnelChargeRepository = new TunnelChargeDatabase($this->dbConnection);
                return new TunnelChargeService($tunnelChargeRepository);

            case ExtraChargeController::class:
                $extraChargeRepository = new ExtraChargeDatabase($this->dbConnection);
                return new ExtraChargeService($extraChargeRepository);

            case SmsServiceController::class:
                $smsServiceRepository = new SmsServiceDatabase($this->dbConnection);
                return new SmsServiceService($smsServiceRepository);

            case FareCalculatorController::class:
                // Repositories
                $googleApiKeyRepository = new GoogleApiKeyDatabase($this->dbConnection);
                $slabRepository = new SlabDatabase($this->dbConnection);
                $carRepository = new CarDatabase($this->dbConnection);
                $carSlabFareRepository = new CarSlabFareDatabase($this->dbConnection);
                $commonSettingRepository = new CommonSettingDatabase($this->dbConnection);
                $extraChargeRepository = new ExtraChargeDatabase($this->dbConnection);
                $airportRepository = new AirportDatabase($this->dbConnection);

                // Services
                $googleApiKeyService = new GoogleApiKeyService($googleApiKeyRepository);
                $googleMapsService = new GoogleMapsService($googleApiKeyService);
                $slabService = new SlabService($slabRepository);
                $carService = new CarService($carRepository, $carSlabFareRepository);
                $commonSettingService = new CommonSettingService($commonSettingRepository);
                $extraChargeService = new ExtraChargeService($extraChargeRepository);
                $airportService = new AirportService($airportRepository);

                return new FareCalculatorService(
                    $googleMapsService,
                    $slabService,
                    $carService,
                    $commonSettingService,
                    $extraChargeService,
                    $airportService
                );
            default:
                throw new \Exception("Service not found for controller: {$controllerClass}");
        }
    }
}