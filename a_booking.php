<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

// Only logged-in patients can access the booking form.
require_role(['patient']);

$current_name = current_user_name() ?? '';
$patient_id = $_SESSION['user_id'];

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_csrf();
  $pickup = $_POST['pickup_location'] ?? '';
  $pickup_lat = $_POST['pickup_lat'] ?? null;
  $pickup_lng = $_POST['pickup_lng'] ?? null;
  $destination = $_POST['destination'] ?? '';
  $dest_lat = $_POST['dest_lat'] ?? null;
  $dest_lng = $_POST['dest_lng'] ?? null;
  $patient_name = $_POST['patient_name'] ?? '';
  $contact_phone = $_POST['contact_phone'] ?? '';
  $pickup_date = $_POST['pickup_date'] ?? '';
  $pickup_time = $_POST['pickup_time'] ?? '';
  $ambulance_type = $_POST['ambulance_type'] ?? '';
  $patient_condition = $_POST['patient_condition'] ?? '';
  
  if (empty($pickup) || empty($destination)) {
    $error = 'Pickup and destination are required.';
  } else {
    $details = "Name: $patient_name\nPhone: $contact_phone\nType: $ambulance_type\nDate: $pickup_date $pickup_time\nCondition: $patient_condition";
    
    try {
      $stmt = db()->prepare("INSERT INTO bookings (patient_id, pickup_location, pickup_lat, pickup_lng, destination, dest_lat, dest_lng, status, emergency_details, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', ?, NOW(), NOW())");
      $stmt->execute([$patient_id, $pickup, $pickup_lat, $pickup_lng, $destination, $dest_lat, $dest_lng, $details]);
      
      $message = "Booking request submitted successfully! We are finding an available ambulance for you.";
    } catch (PDOException $e) {
      $error = "An error occurred while submitting your request.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Book an Ambulance - AmbulanceHub">
  <title>Book Ambulance - AmbulanceHub</title>
  
  <link rel="stylesheet" href="css/style.css">
  <!-- Leaflet CSS for OpenStreetMap -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">
  <style>
    body {
      background: linear-gradient(135deg, #f8f9fa 0%, #f1faee 50%, #ffffff 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 0;
    }

    .booking-wrapper {
      width: 100%;
      max-width: 520px;
      animation: fadeInUp 0.8s ease;
    }

    .booking-container {
      background: white;
      border-radius: 1.5rem;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .booking-header {
      background: linear-gradient(135deg, #e63946 0%, #d62828 100%);
      color: white;
      padding: 3rem 2rem;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .booking-header::before {
      content: '';
      position: absolute;
      top: -50px;
      right: -50px;
      width: 200px;
      height: 200px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
    }

    .booking-header::after {
      content: '';
      position: absolute;
      bottom: -30px;
      left: -30px;
      width: 150px;
      height: 150px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 50%;
    }

    .booking-header i {
      font-size: 3.5rem;
      margin-bottom: 1rem;
      display: block;
      animation: float 3s ease-in-out infinite;
    }

    .booking-header h2 {
      color: white;
      margin-bottom: 0.5rem;
      font-size: 1.8rem;
    }

    .booking-header p {
      color: rgba(255, 255, 255, 0.9);
      margin-bottom: 0;
      font-size: 0.95rem;
    }

    .booking-body {
      padding: 2.5rem;
    }

    .booking-body .form-group label {
      color: #212529;
      font-weight: 600;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      background-color: #f8f9fa;
      border: 1px solid #e9ecef;
      color: #212529;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      background-color: white;
    }

    input[type="date"],
    input[type="time"] {
      color: #212529;
    }

    .form-group textarea {
      min-height: 90px;
      resize: vertical;
    }

    .booking-meta {
      font-size: 0.85rem;
      color: #6c757d;
      margin-bottom: 1.5rem;
    }

    .booking-meta i {
      color: #e63946;
      margin-right: 0.25rem;
    }

    .map-wrapper {
      margin-top: 1.5rem;
      margin-bottom: 1.5rem;
    }

    #map {
      width: 100%;
      height: 260px;
      border-radius: 1rem;
      border: 1px solid #e9ecef;
      overflow: hidden;
    }

    .back-home,
    .login-link {
      text-align: center;
      margin-top: 1.5rem;
      color: #495057;
    }

    .back-home a {
      display: inline-block;
      padding: 0.6rem 1.2rem;
      border: 2px solid #e9ecef;
      border-radius: 0.75rem;
      color: #495057;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .back-home a:hover {
      border-color: #e63946;
      color: #e63946;
      background-color: #f8f9fa;
    }

    .divider {
      text-align: center;
      margin: 1.5rem 0;
      color: #6c757d;
      font-size: 0.9rem;
    }

    .divider::before,
    .divider::after {
      content: '';
      display: inline-block;
      width: 40%;
      height: 1px;
      background: #e9ecef;
      vertical-align: middle;
    }

    .divider::before {
      margin-right: 1rem;
    }

    .divider::after {
      margin-left: 1rem;
    }
  </style>
</head>
<body>
  <div class="booking-wrapper">
    <div class="booking-container">
      <!-- Booking Header -->
      <div class="booking-header">
        <i class="fas fa-ambulance"></i>
        <h2>Book an Ambulance</h2>
        <p>Fast, reliable emergency medical transport</p>
      </div>

      <!-- Booking Form -->
      <div class="booking-body">
        <div class="booking-meta">
          <p><i class="fas fa-clock"></i> 24/7 service &nbsp; • &nbsp; <i class="fas fa-map-marker-alt"></i> All over Bangladesh</p>
        </div>

        <form id="bookingForm" class="needs-validation" method="post" action="a_booking.php" novalidate>
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
          
          <?php if ($message): ?>
            <div class="alert alert-success" style="background:#d4edda; color:#155724; padding:10px; border-radius:5px; margin-bottom:15px;"><?php echo htmlspecialchars($message); ?></div>
            <div style="text-align: center; margin-bottom: 20px;">
              <a href="user_dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            </div>
          <?php endif; ?>

          <?php if ($error): ?>
            <div class="alert alert-danger" style="background:#f8d7da; color:#721c24; padding:10px; border-radius:5px; margin-bottom:15px;"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>

          <!-- Your Name -->
          <div class="form-group">
            <label for="patient_name">Your Name</label>
            <input
              type="text"
              id="patient_name"
              name="patient_name"
              placeholder="Enter your name"
              required
              value="<?php echo htmlspecialchars($current_name, ENT_QUOTES, 'UTF-8'); ?>"
            >
            <div class="form-error"></div>
          </div>

          <!-- Phone Number -->
          <div class="form-group">
            <label for="contact_phone">Contact Phone</label>
            <input type="tel" id="contact_phone" name="contact_phone" placeholder="Active phone number" required>
            <div class="form-error"></div>
          </div>

          <!-- Pickup Location -->
          <div class="form-group">
            <label for="pickup">Pickup Location</label>
            <input type="text" id="pickup" name="pickup_location" placeholder="Click on map to select pickup location" required readonly style="background-color: #e9ecef; cursor: pointer;">
            <div class="form-error"></div>
          </div>

          <!-- Destination Hospital -->
          <div class="form-group">
            <label for="destination">Destination Hospital / Location</label>
            <input type="text" id="destination" name="destination" placeholder="Click on map to select destination" required readonly style="background-color: #e9ecef; cursor: pointer;">
            <div class="form-error"></div>
          </div>

          <!-- Map for selecting pickup & destination -->
          <div class="map-wrapper">
            <label style="display:block; font-weight:600; margin-bottom:0.5rem; color:#212529;">
              Select locations on map (first click = pickup, second click = destination).<br>
              <small style="color:#6c757d; font-weight:normal;"><i>You can drag the markers to adjust them if you clicked the wrong place.</i></small>
            </label>
            <div id="map"></div>
            <input type="hidden" id="pickup_lat" name="pickup_lat">
            <input type="hidden" id="pickup_lng" name="pickup_lng">
            <input type="hidden" id="dest_lat" name="dest_lat">
            <input type="hidden" id="dest_lng" name="dest_lng">
          </div>

          <!-- Date & Time -->
          <div class="row">
            <div class="col-6">
              <div class="form-group">
                <label for="pickup_date">Pickup Date</label>
                <input type="date" id="pickup_date" name="pickup_date" required>
                <div class="form-error"></div>
              </div>
            </div>
            <div class="col-6">
              <div class="form-group">
                <label for="pickup_time">Pickup Time</label>
                <input type="time" id="pickup_time" name="pickup_time" required>
                <div class="form-error"></div>
              </div>
            </div>
          </div>

          <!-- Ambulance Type -->
          <div class="form-group">
            <label for="ambulance_type">Ambulance Type</label>
            <select id="ambulance_type" name="ambulance_type" required>
              <option value="">Select ambulance type</option>
              <option value="icu">ICU Ambulance (Critical Care)</option>
              <option value="standard">Standard Ambulance</option>
              <option value="air">Air Ambulance</option>
              <option value="freeze">Freezer Ambulance</option>
            </select>
            <div class="form-error"></div>
          </div>

          <!-- Patient Condition -->
          <div class="form-group">
            <label for="patient_condition">Patient Condition / Notes</label>
            <textarea id="patient_condition" name="patient_condition" placeholder="Short description of patient condition, special requirements, etc." required></textarea>
            <div class="form-error"></div>
          </div>

          <!-- Submit Button -->
          <button type="submit" class="btn btn-submit w-100">Confirm Booking Request</button>
        </form>

        <!-- Divider -->
        <div class="divider">or</div>

        <!-- Back & Dashboard Links -->
        <div class="back-home">
          <a href="user_dashboard.php">
            <i class="fas fa-home"></i> Back to Dashboard
          </a>
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
  <!-- Leaflet JS for OpenStreetMap -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
  <!-- Font Awesome -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
  <!-- Custom JS -->
  <script src="js/script.js?v=<?= time() ?>"></script>
  <script>
    // Map is now fully initialized and reverse geocoding is handled in js/script.js


  </script>
</body>
</html>

