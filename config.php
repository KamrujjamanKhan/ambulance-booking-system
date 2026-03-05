<?php
// Basic database configuration for AmbulanceHub.
// Adjust these values to match your local MySQL setup.

define('DB_HOST', 'localhost');
define('DB_NAME', 'ambulancehub');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create a shared PDO instance.
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            // In production you would log this instead of displaying it.
            die('Database connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
    }

    return $pdo;
}

