<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserInterface;
use App\Exceptions\NotFoundException;
use App\Exceptions\AuthenticationException; // We'll create this custom exception
use PDOException;

class AuthService
{
    private UserInterface $userRepository;

    public function __construct(UserInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Authenticates a user.
     *
     * @param string $email
     * @param string $password
     * @return User The authenticated user object.
     * @throws AuthenticationException If authentication fails (invalid credentials or inactive user).
     * @throws \Exception On database errors.
     */
    public function login(string $email, string $password): User
    {
        try {
            $user = $this->userRepository->findByEmail($email);

            if (!$user) {
                // Log attempt for security, but don't reveal if user exists
                error_log("Login attempt failed for email: {$email} (User not found)");
                throw new AuthenticationException("Invalid credentials.");
            }

            // Verify password against the hashed password stored in the database
            if (!password_verify($password, $user->password)) {
                error_log("Login attempt failed for email: {$email} (Incorrect password)");
                throw new AuthenticationException("Invalid credentials.");
            }

            // Check if the user is active
            if ($user->status !== 1) {
                error_log("Login attempt failed for email: {$email} (User inactive)");
                throw new AuthenticationException("Your account is inactive. Please contact support.");
            }

            // If authentication is successful, regenerate session ID to prevent session fixation
            // This assumes session_start() has already been called in App.php's executeHandler
            if (session_status() == PHP_SESSION_ACTIVE) {
                session_regenerate_id(true); // true deletes the old session file
                $_SESSION['user_id'] = $user->id;
                $_SESSION['user_email'] = $user->email;
                $_SESSION['user_name'] = $user->name;
                $_SESSION['last_activity'] = time(); // Track last activity for idle timeout
                $_SESSION['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'] ?? ''; // Store user agent
                $_SESSION['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? ''; // Store IP address
            } else {
                error_log("Session not active when attempting to log in user: {$email}");
                throw new \Exception("Session not initialized. Cannot log in user.");
            }

            error_log("User logged in successfully: {$user->email}");
            return $user;

        } catch (PDOException $e) {
            error_log("Database error during login for email {$email}: " . $e->getMessage());
            throw new \Exception("An error occurred during login. Please try again later.");
        } catch (AuthenticationException $e) {
            throw $e; // Re-throw custom authentication exceptions
        } catch (\Exception $e) {
            error_log("Unexpected error during login for email {$email}: " . $e->getMessage());
            throw new \Exception("An unexpected error occurred during login.");
        }
    }

    /**
     * Logs out the current user.
     * @return void
     */
    public function logout(): void
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            // Unset all session variables
            $_SESSION = array();

            // Destroy the session cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }

            // Destroy the session
            session_destroy();
            error_log("User logged out successfully.");
        }
    }

    /**
     * Checks if a user is currently authenticated.
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        if (session_status() == PHP_SESSION_NONE) {
            // Session not started, so not authenticated
            return false;
        }

        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        // Optional: Check for idle timeout
        $maxIdleTime = 3600; // 1 hour (in seconds)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $maxIdleTime)) {
            $this->logout(); // Log out if idle for too long
            error_log("Session expired due to inactivity for user ID: " . $_SESSION['user_id'] ?? 'N/A');
            return false;
        }

        // Optional: Session Hijack prevention checks
        if (isset($_SESSION['HTTP_USER_AGENT']) && $_SESSION['HTTP_USER_AGENT'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            error_log("Session hijack attempt detected: User Agent mismatch for user ID: " . $_SESSION['user_id'] ?? 'N/A');
            $this->logout();
            return false;
        }

        if (isset($_SESSION['REMOTE_ADDR']) && $_SESSION['REMOTE_ADDR'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
            error_log("Session hijack attempt detected: IP address mismatch for user ID: " . $_SESSION['user_id'] ?? 'N/A');
            $this->logout();
            return false;
        }

        // Update last activity on successful check
        $_SESSION['last_activity'] = time();

        return true;
    }

    /**
     * Get the currently authenticated user's ID.
     * @return int|null
     */
    public function getAuthenticatedUserId(): ?int
    {
        return $this->isAuthenticated() ? ($_SESSION['user_id'] ?? null) : null;
    }

    /**
     * Get the currently authenticated user's email.
     * @return string|null
     */
    public function getAuthenticatedUserEmail(): ?string
    {
        return $this->isAuthenticated() ? ($_SESSION['user_email'] ?? null) : null;
    }

    /**
     * Get the currently authenticated user's name.
     * @return string|null
     */
    public function getAuthenticatedUserName(): ?string
    {
        return $this->isAuthenticated() ? ($_SESSION['user_name'] ?? null) : null;
    }

    /**
     * Create a new user (e.g., for initial admin setup).
     * This method should be used carefully and possibly only via a CLI command or a secure setup page.
     *
     * @param string $name
     * @param string $email
     * @param string $password
     * @param int $status
     * @return User
     * @throws \Exception If user already exists or creation fails.
     */
    public function registerUser(string $name, string $email, string $password, int $status = 1): User
    {
        if ($this->userRepository->findByEmail($email)) {
            throw new \Exception("User with this email already exists.");
        }

        // Hash the password before storing
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $user = new User(
            id: null,
            name: $name,
            email: $email,
            password: $hashedPassword,
            status: $status
        );

        $newId = $this->userRepository->create($user);
        if (!$newId) {
            throw new \Exception("Failed to register user.");
        }
        $user->id = $newId;
        return $user;
    }
}