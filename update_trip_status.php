<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';
require_role(['driver']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
  exit;
}

$driver_id = $_SESSION['user_id'];
$booking_id = $_POST['booking_id'] ?? 0;
$new_status = $_POST['new_status'] ?? '';

$valid_statuses = ['On the way', 'Arrived', 'Completed'];

if (!in_array($new_status, $valid_statuses)) {
  echo json_encode(['success' => false, 'message' => 'Invalid status limit']);
  exit;
}

try {
  db()->beginTransaction();

  // Verify ownership
  $stmt = db()->prepare("SELECT id FROM bookings WHERE id = ? AND driver_id = ?");
  $stmt->execute([$booking_id, $driver_id]);
  if (!$stmt->fetch()) {
    throw new Exception("Unauthorized.");
  }

  // Update booking status
  $stmt = db()->prepare("UPDATE bookings SET status = ?, updated_at = NOW() WHERE id = ?");
  $stmt->execute([$new_status, $booking_id]);

  // If completed, make driver available again
  if ($new_status === 'Completed') {
    $stmt = db()->prepare("UPDATE ambulances SET status = 'Available' WHERE driver_id = ?");
    $stmt->execute([$driver_id]);
  }

  db()->commit();
  echo json_encode(['success' => true, 'message' => "Status updated to $new_status"]);

} catch (Exception $e) {
  if (db()->inTransaction()) {
    db()->rollBack();
  }
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
