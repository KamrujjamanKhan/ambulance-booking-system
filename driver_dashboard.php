<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';
require_role(['driver']);

$current_name = current_user_name() ?? 'Driver';
$driver_id = $_SESSION['user_id'];

// Get driver profile details
$stmtUser = db()->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
$stmtUser->execute([$driver_id]);
$driver_user = $stmtUser->fetch();

// Get ambulance details
$stmt = db()->prepare("SELECT * FROM ambulances WHERE driver_id = ?");
$stmt->execute([$driver_id]);
$ambulance = $stmt->fetch();

$status = $ambulance ? $ambulance['status'] : 'Offline';
$vehicle_type = $ambulance ? $ambulance['vehicle_type'] : '';
$license_plate = $ambulance ? $ambulance['license_plate'] : '';

// Fetch pending bookings
$pending_stmt = db()->prepare("SELECT * FROM bookings WHERE status = 'Pending' ORDER BY created_at DESC");
$pending_stmt->execute();
$pending_bookings = $pending_stmt->fetchAll();

// Fetch active trip for this driver
$active_stmt = db()->prepare("SELECT * FROM bookings WHERE driver_id = ? AND status IN ('Accepted', 'On the way', 'Arrived') ORDER BY updated_at DESC LIMIT 1");
$active_stmt->execute([$driver_id]);
$active_trip = $active_stmt->fetch();

// Fetch history of trips for this driver
$history_stmt = db()->prepare("
    SELECT b.*, p.full_name as patient_name, p.phone as patient_phone 
    FROM bookings b 
    JOIN users p ON b.patient_id = p.id 
    WHERE b.driver_id = ? AND b.status IN ('Completed', 'Cancelled') 
    ORDER BY b.updated_at DESC LIMIT 20
");
$history_stmt->execute([$driver_id]);
$trip_history = $history_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Driver Dashboard - AmbulanceHub">
  <title>Driver Dashboard - AmbulanceHub</title>
  
  <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

  <div class="dashboard-container">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="sidebar-brand">
        Driver Panel
      </div>
      <ul class="sidebar-menu nav flex-column" id="driverTab" role="tablist">
        <li class="nav-item" role="presentation">
          <a class="nav-link active" id="requests-tab" data-bs-toggle="pill" data-bs-target="#requests" type="button" role="tab" aria-controls="requests" aria-selected="true" style="cursor: pointer;">
            <i class="fas fa-list-ul me-2"></i>
            Active & Pending
          </a>
        </li>
        <li class="nav-item" role="presentation">
          <a class="nav-link" id="history-tab" data-bs-toggle="pill" data-bs-target="#history" type="button" role="tab" aria-controls="history" aria-selected="false" style="cursor: pointer;">
            <i class="fas fa-history me-2"></i>
            Trip History
          </a>
        </li>
        <li class="nav-item" role="presentation">
          <a class="nav-link" id="profile-tab" data-bs-toggle="pill" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false" style="cursor: pointer;">
            <i class="fas fa-user-edit me-2"></i>
            Profile
          </a>
        </li>
        <li class="nav-item" role="presentation">
          <a class="nav-link" id="vehicle-tab" data-bs-toggle="pill" data-bs-target="#vehicle" type="button" role="tab" aria-controls="vehicle" aria-selected="false" style="cursor: pointer;">
            <i class="fas fa-ambulance me-2"></i>
            Vehicle Info
          </a>
        </li>
        <li>
          <a href="#" onclick="logout(); return false;">
            <i class="fas fa-sign-out-alt me-2"></i>
            Logout
          </a>
        </li>
      </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-content">
      <header class="top-navbar">
        <div class="brand-title">
          <i class="fas fa-ambulance text-primary"></i> 
          <span>AmbulanceHub <span class="badge bg-primary ms-2 pb-1">Driver</span></span>
        </div>
        <div class="user-info d-flex align-items-center">
          <a href="index.php" class="btn btn-sm btn-outline-primary me-3">
            <i class="fas fa-home"></i> Back to Home
          </a>
          <form id="header-status-form" class="me-3 mb-0">
            <select class="form-select form-select-sm" id="header-status" name="status" style="border-radius:20px; font-weight:bold; background-color: <?= $status === 'Available' ? '#10b981' : ($status === 'Busy' ? '#f59e0b' : '#6b7280') ?>; color: white; border: none; cursor:pointer;" onchange="updateHeaderStatus(this.value)">
              <option value="Offline" <?= $status === 'Offline' ? 'selected' : '' ?>>Offline</option>
              <option value="Available" <?= $status === 'Available' ? 'selected' : '' ?>>Available</option>
              <option value="Busy" <?= $status === 'Busy' ? 'selected' : '' ?>>Busy</option>
            </select>
          </form>
          <div class="user-profile">
            <div class="user-avatar" style="background: #10b981;">
              <i class="fas fa-user"></i>
            </div>
            <span class="user-name"><?= htmlspecialchars($current_name, ENT_QUOTES, 'UTF-8') ?></span>
          </div>
          <a href="#" onclick="logout(); return false;" class="logout-btn ms-3">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </div>
      </header>

      <div class="content-area tab-content" id="driverTabContent">

        <!-- Active/Pending Tab -->
        <div class="tab-pane fade show active" id="requests" role="tabpanel" aria-labelledby="requests-tab">
          
          <?php if ($active_trip): ?>
          <div class="card mb-4 border-success" style="max-width: 800px; margin: 0 auto;">
            <div class="card-header bg-success text-white border-bottom">
              <h5 class="card-title mb-0">Active Trip - <?= htmlspecialchars($active_trip['status']) ?></h5>
            </div>
            <div class="card-body p-4">
              <p><strong>Pickup:</strong> <?= htmlspecialchars($active_trip['pickup_location']) ?></p>
              <p><strong>Destination:</strong> <?= htmlspecialchars($active_trip['destination']) ?></p>
              <p><strong>Details:</strong><br><?= nl2br(htmlspecialchars($active_trip['emergency_details'])) ?></p>
              <form method="post" action="update_trip_status.php" class="mt-3 update-trip-form">
                <input type="hidden" name="booking_id" value="<?= $active_trip['id'] ?>">
                <?php if ($active_trip['status'] === 'Accepted'): ?>
                  <button type="submit" name="new_status" value="On the way" class="btn btn-warning">Mark as On The Way</button>
                <?php elseif ($active_trip['status'] === 'On the way'): ?>
                  <button type="submit" name="new_status" value="Arrived" class="btn btn-info">Mark as Arrived</button>
                <?php elseif ($active_trip['status'] === 'Arrived'): ?>
                  <button type="submit" name="new_status" value="Completed" class="btn btn-success">Complete Trip</button>
                <?php endif; ?>
              </form>
            </div>
          </div>
          <?php else: ?>
          
          <div class="data-card mb-4" style="max-width: 800px; margin: 0 auto;">
            <div class="card-header border-bottom">
              <h5 class="card-title mb-0">Pending Requests</h5>
            </div>
            <div class="card-body p-4">
              <?php if (count($pending_bookings) === 0): ?>
                <p>No pending requests.</p>
              <?php else: ?>
                <?php foreach ($pending_bookings as $booking): ?>
                  <div class="border rounded p-3 mb-3">
                    <p><strong>Pickup:</strong> <?= htmlspecialchars($booking['pickup_location']) ?></p>
                    <p><strong>Destination:</strong> <?= htmlspecialchars($booking['destination']) ?></p>
                    <p><strong>Details:</strong><br><?= nl2br(htmlspecialchars($booking['emergency_details'])) ?></p>
                    <form method="post" action="accept_booking.php" class="mt-2 accept-booking-form">
                      <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                      <button type="submit" class="btn btn-success" <?= $status !== 'Available' ? 'disabled' : '' ?>>
                        <?= $status !== 'Available' ? 'Go Available to Accept' : 'Accept Request' ?>
                      </button>
                    </form>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- Trip History Tab -->
        <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
          <h4 class="mb-4">Recent Trip History</h4>
          <div class="data-card" style="max-width: 800px; margin: 0 auto;">
            <div class="card-body p-4">
              <?php if (count($trip_history) === 0): ?>
                <p class="text-muted">No past trips.</p>
              <?php else: ?>
                <div class="list-group">
                  <?php foreach ($trip_history as $history): ?>
                    <div class="list-group-item list-group-item-action flex-column align-items-start p-3 mb-2 border rounded">
                      <div class="d-flex w-100 justify-content-between mb-2">
                        <h6 class="mb-1">To: <?= htmlspecialchars($history['destination']) ?></h6>
                        <span class="badge <?= $history['status'] === 'Completed' ? 'bg-success' : 'bg-danger' ?>"><?= $history['status'] ?></span>
                      </div>
                      <p class="mb-1"><small><strong>From:</strong> <?= htmlspecialchars($history['pickup_location']) ?></small></p>
                      <p class="mb-1"><small><strong>Patient:</strong> <?= htmlspecialchars($history['patient_name']) ?> (<?= htmlspecialchars($history['patient_phone']) ?>)</small></p>
                      <small class="text-muted"><?= date('M d, Y - h:i A', strtotime($history['updated_at'])) ?></small>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Profile Tab -->
        <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
          <div class="data-card" style="max-width: 600px; margin: 0 auto;">
            <div class="card-header border-bottom">
              <h5 class="card-title mb-0">Personal Profile</h5>
            </div>
            <div class="card-body p-4">
              <form id="driver-profile-form">
                <div class="mb-3">
                  <label class="form-label">Full Name</label>
                  <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($driver_user['full_name'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Email Address</label>
                  <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($driver_user['email'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Phone Number</label>
                  <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($driver_user['phone'] ?? '') ?>" required>
                </div>
                <button type="submit" class="btn btn-primary" id="update-profile-btn">Save Profile Changes</button>
                <div id="profile-message" class="mt-3"></div>
              </form>
            </div>
          </div>
        </div>

        <!-- Vehicle Tab -->
        <div class="tab-pane fade" id="vehicle" role="tabpanel" aria-labelledby="vehicle-tab">
          <div class="data-card" style="max-width: 600px; margin: 0 auto;">
            <div class="card-header border-bottom">
              <h5 class="card-title mb-0">Vehicle Information</h5>
            </div>
            <div class="card-body p-4">
              <form id="driver-status-form">
                <div class="mb-3">
                  <label for="vehicle_type" class="form-label">Vehicle Type (e.g., ICU, Basic, Freezer)</label>
                  <input type="text" class="form-control" id="vehicle_type" name="vehicle_type" value="<?= htmlspecialchars($vehicle_type) ?>" required>
                </div>
                <div class="mb-3">
                  <label for="license_plate" class="form-label">License Plate</label>
                  <input type="text" class="form-control" id="license_plate" name="license_plate" value="<?= htmlspecialchars($license_plate) ?>" required>
                </div>
                <button type="submit" class="btn btn-primary" id="update-status-btn">Save Vehicle Details</button>
              </form>
              <div id="status-message" class="mt-3"></div>
            </div>
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
  <script>
    // Handle Header Status Quick Toggle
    function updateHeaderStatus(statusValue) {
      const selectObj = document.getElementById('header-status');
      selectObj.disabled = true;
      
      const formData = new FormData();
      formData.append('status', statusValue);
      
      fetch('update_driver_status.php', { method: 'POST', body: formData })
      .then(response => response.json())
      .then(data => {
        selectObj.disabled = false;
        if(data.success) {
          showToast('Status updated successfully.', 'success');
          // Color coding
          selectObj.style.backgroundColor = (statusValue === 'Available') ? '#10b981' : ((statusValue === 'Busy') ? '#f59e0b' : '#6b7280');
        } else {
          showToast(data.message, 'error');
        }
      }).catch(error => {
        selectObj.disabled = false;
        showToast('Error updating status.', 'error');
      });
    }

    // Handle profile update form
    document.getElementById('driver-profile-form').addEventListener('submit', function(e) {
      e.preventDefault();
      const btn = document.getElementById('update-profile-btn');
      const msgDiv = document.getElementById('profile-message');
      const formData = new FormData(this);
      
      btn.disabled = true;
      btn.textContent = 'Saving...';
      msgDiv.innerHTML = '';
      
      fetch('update_driver_profile.php', { method: 'POST', body: formData })
      .then(res => res.json())
      .then(data => {
        btn.disabled = false;
        btn.textContent = 'Save Profile Changes';
        if(data.success) {
          msgDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
          setTimeout(() => window.location.reload(), 1500);
        } else {
          msgDiv.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
        }
      }).catch(err => {
        btn.disabled = false;
        btn.textContent = 'Save Profile Changes';
        msgDiv.innerHTML = '<div class="alert alert-danger">An error occurred.</div>';
      });
    });

    // Handle vehicle update form (formerly driver-status-form)
    document.getElementById('driver-status-form').addEventListener('submit', function(e) {
      e.preventDefault();
      const btn = document.getElementById('update-status-btn');
      const msgDiv = document.getElementById('status-message');
      const formData = new FormData(this);
      
      btn.disabled = true;
      btn.textContent = 'Saving...';
      msgDiv.innerHTML = '';
      
      fetch('update_driver_status.php', { method: 'POST', body: formData })
      .then(response => response.json())
      .then(data => {
        btn.disabled = false;
        btn.textContent = 'Save Vehicle Details';
        if(data.success) {
          msgDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
          setTimeout(() => window.location.reload(), 1500);
        } else {
          msgDiv.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
        }
      })
      .catch(error => {
        btn.disabled = false;
        btn.textContent = 'Save Vehicle Details';
        msgDiv.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
      });
    });

    // Active trip status updates
    document.querySelectorAll('.update-trip-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = e.submitter; 
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Updating...';

            const formData = new FormData(this);
            formData.append('new_status', btn.value);

            fetch('update_trip_status.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success) window.location.reload();
                else { alert('Error: ' + data.message); btn.disabled = false; btn.textContent = originalText; }
            }).catch(error => {
                console.error('Error:', error);
                alert('An error occurred.');
                btn.disabled = false;
                btn.textContent = originalText;
            });
        });
    });

    // Accept booking form
    document.querySelectorAll('.accept-booking-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Accepting...';

            const formData = new FormData(this);

            fetch('accept_booking.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success) window.location.reload();
                else { alert('Error: ' + data.message); btn.disabled = false; btn.textContent = originalText; }
            }).catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Make sure your status is Available.');
                btn.disabled = false;
                btn.textContent = originalText;
            });
        });
    });
  </script>
</body>
</html>

