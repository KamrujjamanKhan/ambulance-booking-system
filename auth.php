<?php
// Authentication and authorization helpers for AmbulanceHub.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';

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

