<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';
require_role(['admin']);

$current_name = current_user_name() ?? 'Admin';

// Fetch summary stats
$stats = [
    'users' => db()->query("SELECT COUNT(*) FROM users WHERE role = 'patient'")->fetchColumn(),
    'drivers' => db()->query("SELECT COUNT(*) FROM users WHERE role = 'driver'")->fetchColumn(),
    'ambulances' => db()->query("SELECT COUNT(*) FROM ambulances")->fetchColumn(),
    'bookings' => db()->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'pending_bookings' => db()->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'")->fetchColumn()
];

// Fetch recent bookings
$recent_bookings = db()->query("
    SELECT b.*, p.full_name as patient_name, p.phone as patient_phone, d.full_name as driver_name 
    FROM bookings b 
    LEFT JOIN users p ON b.patient_id = p.id 
    LEFT JOIN users d ON b.driver_id = d.id 
    ORDER BY created_at DESC LIMIT 10
")->fetchAll();

// Fetch ambulances
$ambulances = db()->query("
    SELECT a.*, u.full_name as driver_name, u.phone as driver_phone 
    FROM ambulances a 
    JOIN users u ON a.driver_id = u.id
")->fetchAll();

// Fetch patients and drivers separately
$patients = db()->query("SELECT * FROM users WHERE role = 'patient' ORDER BY created_at DESC")->fetchAll();
$drivers = db()->query("SELECT * FROM users WHERE role = 'driver' ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Admin Dashboard - AmbulanceHub">
  <title>Admin Dashboard - AmbulanceHub</title>
  
  <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

  <div class="dashboard-container">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="sidebar-brand">
        Admin Panel
      </div>
      <ul class="sidebar-menu nav flex-column" id="adminTab" role="tablist">
        <li class="nav-item" role="presentation">
          <a class="nav-link" id="dashboard-tab" data-bs-toggle="pill" data-bs-target="#dashboard" type="button" role="tab" aria-controls="dashboard" aria-selected="false" style="cursor: pointer;">
            <i class="fas fa-chart-pie me-2"></i>
            Dashboard
          </a>
        </li>
        <li class="nav-item" role="presentation">
          <a class="nav-link" id="users-tab" data-bs-toggle="pill" data-bs-target="#users" type="button" role="tab" aria-controls="users" aria-selected="false" style="cursor: pointer;">
            <i class="fas fa-users me-2"></i>
            Manage Users
          </a>
        </li>
        <li class="nav-item" role="presentation">
          <a class="nav-link" id="drivers-tab" data-bs-toggle="pill" data-bs-target="#drivers" type="button" role="tab" aria-controls="drivers" aria-selected="false" style="cursor: pointer;">
            <i class="fas fa-id-card me-2"></i>
            Manage Drivers
          </a>
        </li>
        <li class="nav-item" role="presentation">
          <a class="nav-link" id="ambulances-tab" data-bs-toggle="pill" data-bs-target="#ambulances" type="button" role="tab" aria-controls="ambulances" aria-selected="false" style="cursor: pointer;">
            <i class="fas fa-ambulance me-2"></i>
            Fleet Status
          </a>
        </li>
        <li class="nav-item" role="presentation">
          <a class="nav-link active" id="bookings-tab" data-bs-toggle="pill" data-bs-target="#bookings" type="button" role="tab" aria-controls="bookings" aria-selected="true" style="cursor: pointer;">
            <i class="fas fa-list me-2"></i>
            All Bookings
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
          <span>AmbulanceHub <span class="badge bg-primary ms-2 pb-1">Admin</span></span>
        </div>
        <div class="user-info d-flex align-items-center">
          <a href="index.php" class="btn btn-sm btn-outline-primary me-3">
            <i class="fas fa-home"></i> Back to Home
          </a>
          <div class="user-profile">
            <div class="user-avatar">
              <i class="fas fa-user-shield"></i>
            </div>
            <span class="user-name"><?= htmlspecialchars($current_name, ENT_QUOTES, 'UTF-8') ?></span>
          </div>
          <a href="#" onclick="logout(); return false;" class="logout-btn ms-3">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </div>
      </header>

      <div class="content-area tab-content" id="adminTabContent">
        
        <?php if (isset($_GET['msg'])): ?>
          <div class="alert alert-success mt-2 mb-4"><?= htmlspecialchars($_GET['msg']) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['err'])): ?>
          <div class="alert alert-danger mt-2 mb-4"><?= htmlspecialchars($_GET['err']) ?></div>
        <?php endif; ?>

        <!-- Dashboard Overview -->
        <div class="tab-pane fade" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
          <h4 class="mb-4 fw-bold text-dark">System Overview</h4>
          <div class="row g-4 mb-5">
            <div class="col-md-3">
              <div class="stat-card">
                <div class="icon"><i class="fas fa-users"></i></div>
                <div class="info">
                  <h4><?= $stats['users'] ?></h4>
                  <span>Total Patients</span>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="stat-card success">
                <div class="icon"><i class="fas fa-user-tie"></i></div>
                <div class="info">
                  <h4><?= $stats['drivers'] ?></h4>
                  <span>Total Drivers</span>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="stat-card warning">
                <div class="icon"><i class="fas fa-ambulance"></i></div>
                <div class="info">
                  <h4><?= $stats['ambulances'] ?></h4>
                  <span>Ambulances</span>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="stat-card danger">
                <div class="icon"><i class="fas fa-clock"></i></div>
                <div class="info">
                  <h4><?= $stats['pending_bookings'] ?></h4>
                  <span>Pending Trips</span>
                </div>
              </div>
            </div>
          </div>
        </div>

      <!-- Recent Bookings Table -->
      <div class="tab-pane fade show active" id="bookings" role="tabpanel" aria-labelledby="bookings-tab">
        <h4 class="mb-4">Recent Bookings</h4>
        <div class="data-card">
          <div class="card-body">
            <table class="table table-striped table-hover mb-0">
              <thead class="table-dark">
                <tr>
                  <th>ID</th>
                  <th>Patient</th>
                  <th>Pickup</th>
                  <th>Destination</th>
                  <th>Status</th>
                  <th>Driver</th>
                  <th>Date</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($recent_bookings) === 0): ?>
                  <tr><td colspan="8" class="text-center py-3">No bookings found.</td></tr>
                <?php else: ?>
                  <?php foreach ($recent_bookings as $b): ?>
                    <tr>
                      <td>#<?= $b['id'] ?></td>
                      <td><?= htmlspecialchars($b['patient_name']) ?> <br> <small><?= htmlspecialchars($b['patient_phone']) ?></small></td>
                      <td><?= htmlspecialchars($b['pickup_location']) ?></td>
                      <td><?= htmlspecialchars($b['destination']) ?></td>
                      <td>
                        <span class="badge 
                          <?php 
                            if($b['status'] == 'Pending') echo 'bg-warning text-dark';
                            elseif($b['status'] == 'Accepted' || $b['status'] == 'On the way') echo 'bg-info text-dark';
                            elseif($b['status'] == 'Completed') echo 'bg-success';
                            else echo 'bg-secondary';
                          ?>
                        "><?= htmlspecialchars($b['status']) ?></span>
                      </td>
                      <td><?= $b['driver_name'] ? htmlspecialchars($b['driver_name']) : '<span class="text-muted">Unassigned</span>' ?></td>
                      <td><?= date('M d, y g:i A', strtotime($b['created_at'])) ?></td>
                      <td>
                        <?php if ($b['status'] === 'Pending'): ?>
                          <form method="post" action="admin_action.php" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="action" value="cancel_booking">
                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this booking?');">Cancel</button>
                          </form>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Ambulance Fleet Table -->
      <div class="tab-pane fade" id="ambulances" role="tabpanel" aria-labelledby="ambulances-tab">
        <h4 class="mb-4">Ambulance Fleet</h4>
        <div class="data-card">
          <div class="card-body">
            <table class="table table-hover mb-0">
              <thead class="table-dark">
                <tr>
                  <th>Vehicle ID</th>
                  <th>Type</th>
                  <th>License Plate</th>
                  <th>Driver</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($ambulances) === 0): ?>
                  <tr><td colspan="5" class="text-center py-3">No ambulances registered.</td></tr>
                <?php else: ?>
                  <?php foreach ($ambulances as $a): ?>
                    <tr>
                      <td>#<?= $a['id'] ?></td>
                      <td><?= htmlspecialchars($a['vehicle_type']) ?></td>
                      <td><?= htmlspecialchars($a['license_plate']) ?></td>
                      <td><?= htmlspecialchars($a['driver_name']) ?> <br> <small><?= htmlspecialchars($a['driver_phone']) ?></small></td>
                      <td>
                        <span class="badge 
                          <?php 
                            if($a['status'] == 'Available') echo 'bg-success';
                            elseif($a['status'] == 'Busy') echo 'bg-warning text-dark';
                            else echo 'bg-secondary';
                          ?>
                        "><?= htmlspecialchars($a['status']) ?></span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab">
        <h4 class="mb-4">Manage Patients</h4>
        <div class="data-card">
          <div class="card-body">
            <table class="table table-hover mb-0">
              <thead class="table-dark">
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Joined Date</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($patients) === 0): ?>
                  <tr><td colspan="5" class="text-center py-3">No patients found.</td></tr>
                <?php else: ?>
                  <?php foreach ($patients as $u): ?>
                    <tr>
                      <td>#<?= $u['id'] ?></td>
                      <td><?= htmlspecialchars($u['full_name']) ?></td>
                      <td><?= htmlspecialchars($u['email']) ?></td>
                      <td><?= htmlspecialchars($u['phone']) ?></td>
                      <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Drivers Table -->
      <div class="tab-pane fade" id="drivers" role="tabpanel" aria-labelledby="drivers-tab">
        <h4 class="mb-4">Manage Drivers</h4>
        <div class="data-card">
          <div class="card-body">
            <table class="table table-hover mb-0">
              <thead class="table-dark">
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Joined Date</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($drivers) === 0): ?>
                  <tr><td colspan="5" class="text-center py-3">No drivers found.</td></tr>
                <?php else: ?>
                  <?php foreach ($drivers as $d): ?>
                    <tr>
                      <td>#<?= $d['id'] ?></td>
                      <td><?= htmlspecialchars($d['full_name']) ?></td>
                      <td><?= htmlspecialchars($d['email']) ?></td>
                      <td><?= htmlspecialchars($d['phone']) ?></td>
                      <td><?= date('M d, Y', strtotime($d['created_at'])) ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      </div> <!-- content-area -->
    </div> <!-- main-content -->
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

