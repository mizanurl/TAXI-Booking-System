<?php

namespace App\Controllers\Api;

use App\Http\Request;
use App\Http\Response;
use App\Services\FareCalculatorService;
use App\Exceptions\ValidationException;
use App\Http\Requests\Booking\FareCalculatorRequest;

class FareCalculatorController
{
    private FareCalculatorService $fareCalculatorService;
    private Request $request;
    private Response $response;

    public function __construct(FareCalculatorService $fareCalculatorService, Request $request, Response $response)
    {
        $this->fareCalculatorService = $fareCalculatorService;
        $this->request = $request;
        $this->response = $response;
    }
    
    public function calculate(): void
    {
        try {

            $fareCalculationRequest = new FareCalculatorRequest($this->request);
            $data = $fareCalculationRequest->validate();

            //echo "<pre>";
            //print_r($data);
            //echo "</pre>";

            $fareBreakdown = $this->fareCalculatorService->calculateFare($data);

            //echo "<pre>";
            //print_r($fareBreakdown);
            //echo "</pre>";

            //$this->response->success('Fare calculated successfully.', $fareBreakdown);

            $this->response->json([
                'status' => 'success',
                'data' => $fareBreakdown
            ], 200);
        } catch (ValidationException $e) {
            //$this->response->error($e->getMessage(), 422, $e->getErrors());
            $this->response->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $e->getErrors()
            ], 422);
        } catch (\Exception $e) {
            //error_log("FareCalculatorController error in calculate: " . $e->getMessage());
            //$this->response->error("Failed to calculate fare: " . $e->getMessage(), 500);

            echo "<pre>";
            print_r($e->getMessage());
            echo "</pre>";

            $this->response->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred during fare calculation.'
            ], 500);
        }
    }
}
