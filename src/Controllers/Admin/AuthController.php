<?php

namespace App\Controllers\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Services\AuthService;
use App\Http\Requests\Admin\LoginRequest;
use App\Exceptions\AuthenticationException;
use App\Exceptions\ValidationException;

class AuthController
{
    private AuthService $authService;
    private Request $request;
    private Response $response;
    private string $appUrl;

    public function __construct(AuthService $authService, Request $request, Response $response)
    {
        $this->authService = $authService;
        $this->request = $request;
        $this->response = $response;
        $this->appUrl = getenv('APP_URL') ?: '/';
    }

    /**
     * Displays the admin login form.
     * GET /admin/login
     *
     * If already authenticated, redirects to /admin.
     */
    public function showLoginForm(): void
    {
        if ($this->authService->isAuthenticated()) {
            $this->response->redirect('/admin/dashboard'); // Redirect to dashboard if already logged in
            return;
        }

        // Render the login form view
        $this->response->render('Admin/Auth/login.php', [
            'appUrl' => $this->appUrl,
            'errors' => $_SESSION['errors'] ?? [],
            'oldInput' => $_SESSION['old_input'] ?? [],
            'message' => $_SESSION['message'] ?? null,
        ]);

        // Clear session flash data after rendering
        unset($_SESSION['errors']);
        unset($_SESSION['old_input']);
        unset($_SESSION['message']);
    }

    /**
     * Handles the admin login attempt.
     * POST /admin/login
     */
    public function login(): void
    {
        try {
            // Validate the incoming request data
            $loginRequest = new LoginRequest($this->request);
            $validatedData = $loginRequest->validate();

            $email = $validatedData['email'];
            $password = $validatedData['password'];

            // Attempt to authenticate the user
            $user = $this->authService->login($email, $password);

            // If login is successful, redirect to the admin dashboard
            $this->response->redirect('/admin/dashboard');

        } catch (ValidationException $e) {
            // Store validation errors and old input in session for display
            $_SESSION['errors'] = $e->getErrors();
            $_SESSION['old_input'] = $this->request->all(); // Keep old input
            $this->response->redirect('/admin/login'); // Redirect back to login form
        } catch (AuthenticationException $e) {
            // Store authentication error message in session
            $_SESSION['message'] = $e->getMessage();
            $_SESSION['old_input'] = $this->request->all(); // Keep old input
            $this->response->redirect('/admin/login'); // Redirect back to login form
        } catch (\Exception $e) {
            // Log unexpected errors
            error_log("Admin login error: " . $e->getMessage());
            $_SESSION['message'] = "An unexpected error occurred during login. Please try again.";
            $_SESSION['old_input'] = $this->request->all(); // Keep old input

            $this->response->redirect('/admin/login'); // Redirect back to login form
        }
    }

    /**
     * Handles the admin logout.
     * POST /admin/logout
     */
    public function logout(): void
    {
        $this->authService->logout();
        $_SESSION['message'] = "You have been logged out.";
        
        $this->response->redirect('/admin/login'); // Redirect to login page after logout
    }
}