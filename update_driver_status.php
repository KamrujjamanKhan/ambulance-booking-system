<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';
require_role(['driver']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
  exit;
}
require_csrf(true);

$driver_id = $_SESSION['user_id'];
$vehicle_type = $_POST['vehicle_type'] ?? '';
$license_plate = $_POST['license_plate'] ?? '';
$status = $_POST['status'] ?? 'Offline';

// Allow partial updates depending on which form is submitted.
// The header status toggle only sends 'status' (and empty vehicle_type).
// The vehicle form sends both.

// Fetch current ambulance info so we don't accidentally overwrite fields with blanks during a partial update
try {
  $stmt = db()->prepare("SELECT * FROM ambulances WHERE driver_id = ?");
  $stmt->execute([$driver_id]);
  $existing = $stmt->fetch();

  if ($existing) {
    // Use existing values if not provided
    $v_type = !empty($vehicle_type) ? $vehicle_type : $existing['vehicle_type'];
    $v_plate = !empty($license_plate) ? $license_plate : $existing['license_plate'];
    $s = !empty($status) ? $status : $existing['status'];
    
    $updateStmt = db()->prepare("UPDATE ambulances SET vehicle_type = ?, license_plate = ?, status = ? WHERE driver_id = ?");
    $updateStmt->execute([$v_type, $v_plate, $s, $driver_id]);
  } else {
    // If no ambulance exists, we need all fields. If any are missing, fall back to "Not Set" or "Offline"
    $v_type = !empty($vehicle_type) ? $vehicle_type : 'Not Set';
    $v_plate = !empty($license_plate) ? $license_plate : 'Not Set';
    $s = !empty($status) ? $status : 'Offline';
    
    $insertStmt = db()->prepare("INSERT INTO ambulances (driver_id, vehicle_type, license_plate, status) VALUES (?, ?, ?, ?)");
    $insertStmt->execute([$driver_id, $v_type, $v_plate, $s]);
  }

  echo json_encode(['success' => true, 'message' => 'Status updated successfully.']);

} catch (PDOException $e) {
  // Handle unique constraint error for license_plate
  if ($e->getCode() == 23000) {
    echo json_encode(['success' => false, 'message' => 'License plate is already registered to another user.']);
  } else {
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
  }
}
