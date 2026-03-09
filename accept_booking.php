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

try {
  db()->beginTransaction();

  // Check if driver is available
  $stmt = db()->prepare("SELECT status FROM ambulances WHERE driver_id = ?");
  $stmt->execute([$driver_id]);
  $ambulance_status = $stmt->fetchColumn();

  if ($ambulance_status !== 'Available') {
    throw new Exception("You must be 'Available' to accept requests.");
  }

  // Check if booking is still pending
  $stmt = db()->prepare("SELECT id FROM bookings WHERE id = ? AND status = 'Pending'");
  $stmt->execute([$booking_id]);
  if (!$stmt->fetch()) {
    throw new Exception("This booking is no longer available.");
  }

  // Update booking
  $stmt = db()->prepare("UPDATE bookings SET status = 'Accepted', driver_id = ?, updated_at = NOW() WHERE id = ?");
  $stmt->execute([$driver_id, $booking_id]);

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
