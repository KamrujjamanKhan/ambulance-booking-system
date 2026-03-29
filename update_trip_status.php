<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';
require_role(['driver']);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
  exit;
}
require_csrf(true);

$driver_id = $_SESSION['user_id'];
$booking_id = (int)($_POST['booking_id'] ?? 0);
$new_status = $_POST['new_status'] ?? '';

$valid_statuses = ['On the way', 'Arrived', 'Completed'];

if (!in_array($new_status, $valid_statuses)) {
  echo json_encode(['success' => false, 'message' => 'Invalid status limit']);
  exit;
}

try {
  db()->beginTransaction();

  // Verify ownership and lock current trip row.
  $stmt = db()->prepare("SELECT id, status FROM bookings WHERE id = ? AND driver_id = ? FOR UPDATE");
  $stmt->execute([$booking_id, $driver_id]);
  $booking = $stmt->fetch();
  if (!$booking) {
    throw new Exception("Unauthorized.");
  }

  $current_status = $booking['status'];
  $allowed_next_status = [
    'Accepted' => 'On the way',
    'On the way' => 'Arrived',
    'Arrived' => 'Completed',
  ];
  if (!isset($allowed_next_status[$current_status]) || $allowed_next_status[$current_status] !== $new_status) {
    throw new Exception('Invalid status transition.');
  }

  // Update booking status
  $progress = null;
  $eta = null;
  if ($new_status === 'On the way') {
    $progress = 20;
    $eta = 15;
  } elseif ($new_status === 'Arrived') {
    $progress = 100;
    $eta = 0;
  } elseif ($new_status === 'Completed') {
    $progress = 100;
    $eta = 0;
  }

  $stmt = db()->prepare("
    UPDATE bookings
    SET status = ?,
        simulated_progress = COALESCE(?, simulated_progress),
        eta_minutes = ?,
        last_progress_at = NOW(),
        updated_at = NOW()
    WHERE id = ?
  ");
  $stmt->execute([$new_status, $progress, $eta, $booking_id]);

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
