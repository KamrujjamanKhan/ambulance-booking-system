<?php
require_once __DIR__ . '/config.php';

// Simple one-time script to create or reset an admin user.
// After running this in the browser, DELETE this file for security.

try {
    $pdo = db();

    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "
        INSERT INTO users (full_name, email, phone, password_hash, role, created_at)
        VALUES ('Site Administrator', 'admin@example.com', '+8801000000000', :hash, 'admin', NOW())
        ON DUPLICATE KEY UPDATE
          password_hash = VALUES(password_hash),
          full_name = VALUES(full_name),
          phone = VALUES(phone),
          role = VALUES(role)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':hash' => $hash]);

    echo 'Admin user created/updated successfully.<br>';
    echo 'Email: admin@example.com<br>';
    echo 'Password: ' . htmlspecialchars($password, ENT_QUOTES, 'UTF-8') . '<br>';
    echo 'You can now delete create_admin.php for security.';
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Error creating admin user: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

