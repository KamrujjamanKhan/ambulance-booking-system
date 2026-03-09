<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';
require_role(['driver']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
  exit;
}

$driver_id = $_SESSION['user_id'];
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');

if (empty($full_name) || empty($email) || empty($phone)) {
  echo json_encode(['success' => false, 'message' => 'All fields are required.']);
  exit;
}

try {
  $stmt = db()->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
  $stmt->execute([$full_name, $email, $phone, $driver_id]);
  
  // Update session name if it changed
  $_SESSION['user_name'] = $full_name;

  echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);

} catch (PDOException $e) {
  if ($e->getCode() == 23000) {
    echo json_encode(['success' => false, 'message' => 'Email or phone number is already in use by another account.']);
  } else {
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
  }
}
