<?php

namespace App\Controllers\Frontend;

use App\Http\Request;
use App\Http\Response;
use App\Services\AirportService;
use App\Services\CommonSettingService;

class HomeController
{
    private Request $request;
    private Response $response;
    private AirportService $airportService;
    private CommonSettingService $commonSettingService;

    public function __construct(
        Request $request,
        Response $response,
        AirportService $airportService,
        CommonSettingService $commonSettingService
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->airportService = $airportService;
        $this->commonSettingService = $commonSettingService;
    }

    /**
     * Display the frontend home page with necessary data.
     *
     * @return void
     */
    public function index(): void
    {
        try {
            // Fetch all active airports to pass to the view
            $activeAirports = $this->airportService->getActiveAirports();

            // Pass the data to the home view
            $this->response->render('frontend/home.php', [
                'appUrl' => getenv('APP_URL') ?: '/',
                'commonSettings' => $this->commonSettingService->getCommonSettings(),
                'airports' => array_map(function ($airport) {
                    // Convert Airport object to array, ensuring lat/lng/zip/place_id are included
                    return [
                        'id' => $airport->id,
                        'name' => $airport->name,
                        'from_tax_toll' => $airport->fromTaxToll,
                        'to_tax_toll' => $airport->toTaxToll,
                        'status' => $airport->status
                    ];
                }, $activeAirports),
            ]);
        } catch (\Exception $e) {
            error_log("HomeController error in index: " . $e->getMessage());
            // Render an error page or show a message
            $this->response->error("Failed to load home page: " . $e->getMessage(), 500);
        }
    }
}