<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';
require_role(['patient']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: user_dashboard.php');
  exit;
}

$action = $_POST['action'] ?? '';
$patient_id = $_SESSION['user_id'];

try {
  if ($action === 'cancel_booking') {
    $booking_id = (int)($_POST['booking_id'] ?? 0);
    
    // Patient can only cancel their own Pending bookings
    $stmt = db()->prepare("UPDATE bookings SET status = 'Cancelled', updated_at = NOW() WHERE id = ? AND patient_id = ? AND status = 'Pending'");
    $stmt->execute([$booking_id, $patient_id]);
    
    // If no rows were affected, the booking might not be pending or doesn't belong to them
    if ($stmt->rowCount() > 0) {
        header("Location: user_dashboard.php?msg=" . urlencode("Booking request cancelled successfully."));
    } else {
        header("Location: user_dashboard.php?err=" . urlencode("Could not cancel. Booking might already be accepted or you are not authorized."));
    }
    exit;
  }
} catch (Exception $e) {
  header("Location: user_dashboard.php?err=" . urlencode("Action failed: " . $e->getMessage()));
  exit;
}

header('Location: user_dashboard.php');
exit;
