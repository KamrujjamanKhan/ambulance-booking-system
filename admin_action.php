<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: admin_dashboard.php');
  exit;
}

$action = $_POST['action'] ?? '';

try {
  if ($action === 'cancel_booking') {
    $booking_id = (int)($_POST['booking_id'] ?? 0);
    
    // Admin can only cancel Pending bookings from this shortcut
    $stmt = db()->prepare("UPDATE bookings SET status = 'Cancelled', updated_at = NOW() WHERE id = ? AND status = 'Pending'");
    $stmt->execute([$booking_id]);
    
    header("Location: admin_dashboard.php?msg=" . urlencode("Booking #$booking_id was cancelled."));
    exit;
  }
} catch (Exception $e) {
  header("Location: admin_dashboard.php?err=" . urlencode("Action failed: " . $e->getMessage()));
  exit;
}

header('Location: admin_dashboard.php');
exit;
