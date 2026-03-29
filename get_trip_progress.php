<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';
require_role(['patient']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
  exit;
}

$patient_id = (int)($_SESSION['user_id'] ?? 0);
$booking_id = (int)($_GET['booking_id'] ?? 0);

if ($booking_id <= 0) {
  echo json_encode(['success' => false, 'message' => 'Invalid booking id']);
  exit;
}

try {
  $stmt = db()->prepare("
    SELECT id, status, simulated_progress, eta_minutes, last_progress_at, updated_at
    FROM bookings
    WHERE id = ? AND patient_id = ?
    LIMIT 1
  ");
  $stmt->execute([$booking_id, $patient_id]);
  $booking = $stmt->fetch();

  if (!$booking) {
    throw new Exception('Booking not found.');
  }

  echo json_encode([
    'success' => true,
    'data' => [
      'id' => (int)$booking['id'],
      'status' => $booking['status'],
      'progress' => (int)$booking['simulated_progress'],
      'eta_minutes' => $booking['eta_minutes'] === null ? null : (int)$booking['eta_minutes'],
      'last_progress_at' => $booking['last_progress_at'],
      'updated_at' => $booking['updated_at']
    ]
  ]);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
