<?php
// Authentication and authorization helpers for AmbulanceHub.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';

/**
 * Get or create CSRF token for the session.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate submitted CSRF token.
 */
function csrf_validate(?string $token): bool
{
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    return is_string($token) && is_string($sessionToken) && $sessionToken !== '' && hash_equals($sessionToken, $token);
}

/**
 * Abort request when CSRF token is invalid.
 */
function require_csrf(bool $jsonResponse = false): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (csrf_validate($token)) {
        return;
    }

    if ($jsonResponse) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token. Please refresh and try again.']);
    } else {
        http_response_code(403);
        echo 'Invalid CSRF token. Please go back and try again.';
    }
    exit;
}

/**
 * Find a user by email or phone.
 */
function find_user_by_identifier(string $identifier): ?array
{
    $sql = 'SELECT * FROM users WHERE email = :id OR phone = :id LIMIT 1';
    $stmt = db()->prepare($sql);
    $stmt->execute([':id' => $identifier]);
    $user = $stmt->fetch();

    return $user ?: null;
}

/**
 * Create a new user (patient or driver).
 */
function create_user(string $name, string $email, string $phone, string $password, string $role): bool
{
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = 'INSERT INTO users (full_name, email, phone, password_hash, role, created_at)
            VALUES (:full_name, :email, :phone, :password_hash, :role, NOW())';

    $stmt = db()->prepare($sql);

    try {
        return $stmt->execute([
            ':full_name'     => $name,
            ':email'         => $email,
            ':phone'         => $phone,
            ':password_hash' => $hash,
            ':role'          => $role,
        ]);
    } catch (PDOException $e) {
        // Likely a duplicate email/phone or other constraint error.
        return false;
    }
}

/**
 * Log a user into the session.
 */
function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_role'] = $user['role'];
}

/**
 * Log the current user out.
 */
function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

/**
 * Get the currently authenticated user role or null.
 */
function current_user_role(): ?string
{
    return $_SESSION['user_role'] ?? null;
}

/**
 * Get the currently authenticated user name or null.
 */
function current_user_name(): ?string
{
    return $_SESSION['user_name'] ?? null;
}

/**
 * Require that a user is logged in, otherwise redirect to login page.
 */
function require_login(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Require that the current user has one of the allowed roles.
 *
 * @param string[] $allowedRoles
 */
function require_role(array $allowedRoles): void
{
    require_login();

    $role = current_user_role();
    if ($role === null || !in_array($role, $allowedRoles, true)) {
        header('HTTP/1.1 403 Forbidden');
        echo 'Access denied. You do not have permission to view this page.';
        exit;
    }
}

