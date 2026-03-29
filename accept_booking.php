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

if ($booking_id <= 0) {
  echo json_encode(['success' => false, 'message' => 'Invalid booking id']);
  exit;
}

try {
  db()->beginTransaction();

  // Lock ambulance row first to keep driver availability consistent.
  $stmt = db()->prepare("SELECT status FROM ambulances WHERE driver_id = ? FOR UPDATE");
  $stmt->execute([$driver_id]);
  $ambulance_status = $stmt->fetchColumn();

  if ($ambulance_status !== 'Available') {
    throw new Exception("You must be 'Available' to accept requests.");
  }

  // Driver can only keep one active trip at a time.
  $stmt = db()->prepare("SELECT id FROM bookings WHERE driver_id = ? AND status IN ('Accepted', 'On the way', 'Arrived') LIMIT 1");
  $stmt->execute([$driver_id]);
  if ($stmt->fetch()) {
    throw new Exception("You already have an active trip.");
  }

  // Lock booking row and verify it is still pending.
  $stmt = db()->prepare("SELECT id, status FROM bookings WHERE id = ? FOR UPDATE");
  $stmt->execute([$booking_id]);
  $booking = $stmt->fetch();
  if (!$booking || $booking['status'] !== 'Pending') {
    throw new Exception("This booking is no longer available.");
  }

  // Race-safe conditional assignment.
  $stmt = db()->prepare("
    UPDATE bookings
    SET status = 'Accepted',
        driver_id = ?,
        simulated_progress = 5,
        eta_minutes = 20,
        last_progress_at = NOW(),
        updated_at = NOW()
    WHERE id = ? AND status = 'Pending' AND driver_id IS NULL
  ");
  $stmt->execute([$driver_id, $booking_id]);
  if ($stmt->rowCount() !== 1) {
    throw new Exception("This booking was accepted by another driver.");
  }

  // Update driver status to Busy
  $stmt = db()->prepare("UPDATE ambulances SET status = 'Busy' WHERE driver_id = ?");
  $stmt->execute([$driver_id]);

  db()->commit();
  echo json_encode(['success' => true, 'message' => 'Booking accepted successfully']);

} catch (Exception $e) {
  if (db()->inTransaction()) {
    db()->rollBack();
  }
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
