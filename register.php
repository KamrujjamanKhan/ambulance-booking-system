<?php
require_once __DIR__ . '/auth.php';

// If already logged in, send user away from registration.
if (isset($_SESSION['user_id'])) {
    $role = current_user_role();
    if ($role === 'admin') {
        header('Location: admin_dashboard.php');
    } elseif ($role === 'driver') {
        header('Location: driver_dashboard.php');
    } else {
        header('Location: user_dashboard.php');
    }
    exit;
}

$form_values = [
    'fullname' => '',
    'phone'    => '',
    'email'    => '',
    'role'     => '',
];

$register_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $form_values['fullname'] = trim($_POST['fullname'] ?? '');
    $form_values['phone']    = trim($_POST['phone'] ?? '');
    $form_values['email']    = trim($_POST['email'] ?? '');
    $password                = $_POST['password'] ?? '';
    $confirm_password        = $_POST['confirm_password'] ?? '';
    $form_values['role']     = $_POST['role'] ?? '';

    if (
        $form_values['fullname'] === '' ||
        $form_values['phone'] === '' ||
        $form_values['email'] === '' ||
        $password === '' ||
        $confirm_password === '' ||
        $form_values['role'] === ''
    ) {
        $register_error = 'Please fill in all required fields.';
    } elseif (!filter_var($form_values['email'], FILTER_VALIDATE_EMAIL)) {
        $register_error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $register_error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_password) {
        $register_error = 'Passwords do not match.';
    } else {
        // Map front-end role to DB role names.
        $role = $form_values['role'] === 'driver' ? 'driver' : 'patient';

        if (!create_user($form_values['fullname'], $form_values['email'], $form_values['phone'], $password, $role)) {
            $register_error = 'Could not create account. Email or phone may already be registered.';
        } else {
            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Register with AmbulanceHub - Emergency Ambulance Booking System">
  <title>Register - AmbulanceHub</title>
  
  <link rel="stylesheet" href="css/style.css">
  <style>
    body {
      background: linear-gradient(135deg, #f8f9fa 0%, #f1faee 50%, #ffffff 100%);
      min-height: 100vh;
      padding: 2rem 0;
    }

    .register-wrapper {
      width: 100%;
      max-width: 500px;
      margin: 0 auto;
      animation: fadeInUp 0.8s ease;
    }

    .register-container {
      background: white;
      border-radius: 1.5rem;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .register-header {
      background: linear-gradient(135deg, #e63946 0%, #d62828 100%);
      color: white;
      padding: 3rem 2rem;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .register-header::before {
      content: '';
      position: absolute;
      top: -50px;
      right: -50px;
      width: 200px;
      height: 200px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
    }

    .register-header::after {
      content: '';
      position: absolute;
      bottom: -30px;
      left: -30px;
      width: 150px;
      height: 150px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 50%;
    }

    .register-header i {
      font-size: 3.5rem;
      margin-bottom: 1rem;
      display: block;
      animation: float 3s ease-in-out infinite;
    }

    .register-header h2 {
      color: white;
      margin-bottom: 0.5rem;
      font-size: 1.8rem;
    }

    .register-header p {
      color: rgba(255, 255, 255, 0.9);
      margin-bottom: 0;
      font-size: 0.95rem;
    }

    .register-body {
      padding: 2.5rem;
    }

    .register-body .form-group label {
      color: #212529;
      font-weight: 600;
    }

    .form-group input,
    .form-group select {
      background-color: #f8f9fa;
      border: 1px solid #e9ecef;
      color: #212529;
    }

    .form-group input:focus,
    .form-group select:focus {
      background-color: white;
      color: #212529;
    }

    .terms-check {
      margin-bottom: 1.5rem;
      padding: 1rem;
      background-color: #f8f9fa;
      border-radius: 0.75rem;
      border: 1px solid #e9ecef;
    }

    .terms-check .checkbox-group {
      margin-bottom: 0;
    }

    .terms-check a {
      color: #e63946;
      font-weight: 600;
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

    .back-home {
      text-align: center;
      margin-top: 1.5rem;
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

    .login-link {
      text-align: center;
      margin-top: 1.5rem;
      color: #495057;
    }

    .login-link a {
      color: #e63946;
      font-weight: 700;
    }

    .login-link a:hover {
      text-decoration: underline;
    }

    .global-message {
      margin-bottom: 1rem;
      text-align: center;
      font-weight: 600;
      font-size: 0.95rem;
      color: #e63946;
    }
  </style>
</head>
<body>
  <div class="register-wrapper">
    <div class="register-container">
      <!-- Register Header -->
      <div class="register-header">
        <i class="fas fa-user-plus"></i>
        <h2>Create Account</h2>
        <p>Join AmbulanceHub Today</p>
      </div>

      <!-- Register Form -->
      <div class="register-body">
        <?php if ($register_error !== ''): ?>
          <div class="global-message">
            <?php echo htmlspecialchars($register_error, ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php endif; ?>

        <form id="registerForm" class="needs-validation" method="post" action="register.php" novalidate>
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
          <!-- Full Name -->
          <div class="form-group">
            <label for="fullname">Full Name</label>
            <input
              type="text"
              id="fullname"
              name="fullname"
              placeholder="Enter your full name"
              required
              value="<?php echo htmlspecialchars($form_values['fullname'], ENT_QUOTES, 'UTF-8'); ?>"
            >
            <div class="form-error"></div>
          </div>

          <!-- Phone Number -->
          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input
              type="tel"
              id="phone"
              name="phone"
              placeholder="Enter your phone number"
              required
              value="<?php echo htmlspecialchars($form_values['phone'], ENT_QUOTES, 'UTF-8'); ?>"
            >
            <div class="form-error"></div>
          </div>

          <!-- Email -->
          <div class="form-group">
            <label for="email">Email Address</label>
            <input
              type="email"
              id="email"
              name="email"
              placeholder="Enter your email address"
              required
              value="<?php echo htmlspecialchars($form_values['email'], ENT_QUOTES, 'UTF-8'); ?>"
            >
            <div class="form-error"></div>
          </div>

          <!-- Password -->
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password (min. 6 characters)" required>
            <div class="form-error"></div>
          </div>

          <!-- Confirm Password -->
          <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
            <div class="form-error"></div>
          </div>

          <!-- Role Selection -->
          <div class="form-group">
            <label for="role">Register As</label>
            <select id="role" name="role" required>
              <option value="">Select your role</option>
              <option value="patient" <?php echo $form_values['role'] === 'patient' ? 'selected' : ''; ?>>
                User (Patient/Requester)
              </option>
              <option value="driver" <?php echo $form_values['role'] === 'driver' ? 'selected' : ''; ?>>
                Driver (Ambulance Driver)
              </option>
            </select>
            <div class="form-error"></div>
          </div>

          <!-- Terms & Conditions -->
          <div class="terms-check">
            <div class="checkbox-group">
              <input type="checkbox" id="terms" name="terms" required>
              <label for="terms">I agree to the <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a></label>
            </div>
          </div>

          <!-- Submit Button -->
          <button type="submit" class="btn btn-submit w-100">Create My Account</button>
        </form>

        <!-- Login Link -->
        <div class="login-link">
          <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>

        <!-- Divider -->
        <div class="divider">or</div>

        <!-- Back Home -->
        <div class="back-home">
          <a href="index.php">
            <i class="fas fa-home"></i> Back to Home
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
  <!-- Font Awesome -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
  <!-- Custom JS -->
  <script src="js/script.js"></script>
</body>
</html>

