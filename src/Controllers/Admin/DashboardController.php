<?php

namespace App\Controllers\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Services\AuthService;
use App\Services\DashboardService;

class DashboardController
{
    private AuthService $authService;
    private DashboardService $dashboardService;
    private Request $request;
    private Response $response;

    public function __construct(
        AuthService $authService,
        DashboardService $dashboardService,
        Request $request,
        Response $response
    ) {
        $this->authService = $authService;
        $this->dashboardService = $dashboardService;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Display the admin dashboard.
     * GET /admin or GET /admin/dashboard
     */
    public function index(): void
    {
        // Check if the user is authenticated
        if (!$this->authService->isAuthenticated()) {
            // Store a message in session to inform the user they need to log in
            $_SESSION['message'] = "Please log in to access the admin dashboard.";
            $this->response->redirect('/admin/login');
            return;
        }

        // Get dashboard data (for now, just a placeholder message)
        $dashboardData = $this->dashboardService->getDashboardData();

        // Render the dashboard view
        $this->response->render('Admin/dashboard.php', [
            'appUrl' => getenv('APP_URL') ?: '/',
            'dashboardData' => $dashboardData,
            'userName' => $this->authService->getAuthenticatedUserName(), // Pass authenticated user's name
        ]);
    }
}