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

$driver_id = (int)($_SESSION['user_id'] ?? 0);
$booking_id = (int)($_POST['booking_id'] ?? 0);
$progress = (int)($_POST['progress'] ?? -1);
$eta_minutes = isset($_POST['eta_minutes']) && $_POST['eta_minutes'] !== '' ? (int)$_POST['eta_minutes'] : null;

if ($booking_id <= 0) {
  echo json_encode(['success' => false, 'message' => 'Invalid booking id']);
  exit;
}

if ($progress < 0 || $progress > 100) {
  echo json_encode(['success' => false, 'message' => 'Progress must be between 0 and 100']);
  exit;
}

if ($eta_minutes !== null && ($eta_minutes < 0 || $eta_minutes > 180)) {
  echo json_encode(['success' => false, 'message' => 'ETA must be between 0 and 180 minutes']);
  exit;
}

try {
  db()->beginTransaction();

  $stmt = db()->prepare("SELECT status FROM bookings WHERE id = ? AND driver_id = ? FOR UPDATE");
  $stmt->execute([$booking_id, $driver_id]);
  $booking = $stmt->fetch();

  if (!$booking) {
    throw new Exception('Unauthorized trip access.');
  }

  if (!in_array($booking['status'], ['Accepted', 'On the way', 'Arrived'], true)) {
    throw new Exception('Only active trips can be updated.');
  }

  $final_progress = $progress;
  if ($booking['status'] === 'Arrived') {
    $final_progress = 100;
    $eta_minutes = 0;
  }

  $stmt = db()->prepare("
    UPDATE bookings
    SET simulated_progress = ?, eta_minutes = ?, last_progress_at = NOW(), updated_at = NOW()
    WHERE id = ?
  ");
  $stmt->execute([$final_progress, $eta_minutes, $booking_id]);

  db()->commit();

  echo json_encode([
    'success' => true,
    'message' => 'Trip progress updated',
    'data' => [
      'progress' => $final_progress,
      'eta_minutes' => $eta_minutes
    ]
  ]);
} catch (Exception $e) {
  if (db()->inTransaction()) {
    db()->rollBack();
  }
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
