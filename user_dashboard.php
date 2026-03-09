<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';
require_role(['patient']);

$current_name = current_user_name() ?? 'User';
$patient_id = $_SESSION['user_id'];

// Fetch patient's bookings
$stmt = db()->prepare("SELECT b.*, u.full_name as driver_name, u.phone as driver_phone, a.vehicle_type, a.license_plate 
                       FROM bookings b 
                       LEFT JOIN users u ON b.driver_id = u.id 
                       LEFT JOIN ambulances a ON b.driver_id = a.driver_id
                       WHERE b.patient_id = ? 
                       ORDER BY b.created_at DESC");
$stmt->execute([$patient_id]);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="User Dashboard - AmbulanceHub">
  <title>User Dashboard - AmbulanceHub</title>
  
  <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

  <div class="dashboard-container">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="sidebar-brand">
        User Dashboard
      </div>
      <ul class="sidebar-menu">
        <li>
          <a href="user_dashboard.php" class="active">
            <i class="fas fa-history me-2"></i> My Bookings
          </a>
        </li>
        <li>
          <a href="a_booking.php">
            <i class="fas fa-ambulance me-2"></i> Book Ambulance
          </a>
        </li>
        <li>
          <a href="#" onclick="logout(); return false;">
            <i class="fas fa-sign-out-alt me-2"></i> Logout
          </a>
        </li>
      </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-content">
      <header class="top-navbar">
        <div class="brand-title">
          <i class="fas fa-ambulance text-primary me-2"></i> AmbulanceHub <span class="badge bg-secondary ms-2">Patient</span>
        </div>
        <div class="user-info d-flex align-items-center">
          <a href="index.php" class="btn btn-sm btn-outline-primary me-3">
            <i class="fas fa-home"></i> Back to Home
          </a>
          <div class="user-profile">
            <div class="user-avatar" style="background: #3b82f6;">
              <i class="fas fa-user"></i>
            </div>
            <span class="user-name"><?= htmlspecialchars($current_name, ENT_QUOTES, 'UTF-8') ?></span>
          </div>
          <a href="#" onclick="logout(); return false;" class="logout-btn ms-3">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </div>
      </header>

      <div class="content-area">
        
        <?php if (isset($_GET['msg'])): ?>
          <div class="alert alert-success mt-2 mb-4"><?= htmlspecialchars($_GET['msg']) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['err'])): ?>
          <div class="alert alert-danger mt-2 mb-4"><?= htmlspecialchars($_GET['err']) ?></div>
        <?php endif; ?>

        <div class="data-card mb-4" style="max-width: 800px; margin: 0 auto;">
          <div class="card-header border-bottom d-flex justify-content-between align-items-center p-3">
            <h5 class="card-title mb-0">My Bookings</h5>
            <a href="a_booking.php" class="btn btn-sm btn-primary">Book New</a>
          </div>
          <div class="card-body p-4">
            <?php if (count($bookings) === 0): ?>
              <p>You have no bookings yet.</p>
            <?php else: ?>
              <?php foreach ($bookings as $b): ?>
                <div class="border rounded p-3 mb-3 <?= ($b['status'] != 'Completed' && $b['status'] != 'Cancelled') ? 'border-primary border-4 border-start' : '' ?>">
                  <div class="d-flex justify-content-between mb-2">
                    <strong><?= date('M d, Y - h:i A', strtotime($b['created_at'])) ?></strong>
                    <span class="badge rounded-pill 
                      <?php 
                        if($b['status'] == 'Pending') echo 'bg-warning text-dark';
                        elseif($b['status'] == 'Accepted' || $b['status'] == 'On the way') echo 'bg-info text-dark';
                        elseif($b['status'] == 'Completed') echo 'bg-success';
                        else echo 'bg-secondary';
                      ?>
                    "><?= htmlspecialchars($b['status']) ?></span>
                  </div>
                  <p class="mb-1"><strong>From:</strong> <?= htmlspecialchars($b['pickup_location']) ?></p>
                  <p class="mb-1"><strong>To:</strong> <?= htmlspecialchars($b['destination']) ?></p>
                  
                  <?php if ($b['status'] === 'Pending'): ?>
                    <form method="post" action="user_action.php" class="mt-2">
                      <input type="hidden" name="action" value="cancel_booking">
                      <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this booking?');">Cancel Request</button>
                    </form>
                  <?php endif; ?>
                  
                  <?php if ($b['driver_id']): ?>
                    <div class="mt-3 p-3 bg-light rounded">
                      <strong class="d-block mb-2">Driver Details:</strong>
                      <p class="mb-1"><i class="fas fa-user"></i> <?= htmlspecialchars($b['driver_name']) ?></p>
                      <p class="mb-1"><i class="fas fa-phone"></i> <?= htmlspecialchars($b['driver_phone']) ?></p>
                      <p class="mb-0"><i class="fas fa-truck-medical"></i> <?= htmlspecialchars($b['vehicle_type']) ?> (<?= htmlspecialchars($b['license_plate']) ?>)</p>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Loading Overlay -->
  <div class="loading-overlay">
    <div class="spinner"></div>
  </div>

  <!-- Toast Container -->
  <div class="toast-container"></div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Font Awesome -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
  <!-- Custom JS -->
  <script src="js/script.js"></script>
</body>
</html>

