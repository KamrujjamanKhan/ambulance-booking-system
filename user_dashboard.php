<?php
require_once __DIR__ . '/auth.php';
require_role(['patient']);

$current_name = current_user_name() ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="User Dashboard - AmbulanceHub">
  <title>User Dashboard - AmbulanceHub</title>
  
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <!-- ============================================
       NAVBAR
       ============================================ -->
  <nav class="navbar navbar-expand-lg">
    <div class="container-with-gaps">
      <a class="navbar-brand" href="index.php">
        <span class="navbar-logo-icon">
          <i class="fas fa-ambulance"></i>
        </span>
        AmbulanceHub
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="index.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#bookings">My Bookings</a>
          </li>
          <li class="nav-item">
            <span class="nav-link">Welcome, <?php echo htmlspecialchars($current_name, ENT_QUOTES, 'UTF-8'); ?></span>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#" onclick="logout(); return false;">Logout</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- ============================================
       DASHBOARD CONTAINER
       ============================================ -->
  <div class="dashboard-container">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="sidebar-brand">
        User Dashboard
      </div>
      <ul class="sidebar-menu">
        <li>
          <a href="#booking-section" class="active">
            Book Ambulance
          </a>
        </li>
        <li>
          <a href="#bookings">
            My Bookings
          </a>
        </li>
        <li>
          <a href="#profile">
            My Profile
          </a>
        </li>
        <li>
          <a href="#settings">
            Settings
          </a>
        </li>
        <li>
          <a href="#" onclick="logout(); return false;">
            Logout
          </a>
        </li>
      </ul>
    </aside>

    <!-- MAIN CONTENT REMOVED: Booking sidebar-only view -->
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

