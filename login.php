<?php
require_once __DIR__ . '/auth.php';

// If already logged in, send user to their dashboard directly.
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

$identifier  = '';
$login_error = '';
$login_success = '';

if (isset($_GET['registered']) && $_GET['registered'] === '1') {
    $login_success = 'Registration successful. Please log in.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        $login_error = 'Please fill in both fields.';
    } else {
        $user = find_user_by_identifier($identifier);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $login_error = 'Invalid email/phone or password.';
        } else {
            login_user($user);

            // Redirect based on role.
            if ($user['role'] === 'admin') {
                header('Location: admin_dashboard.php');
            } elseif ($user['role'] === 'driver') {
                header('Location: driver_dashboard.php');
            } else {
                header('Location: user_dashboard.php');
            }
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
  <meta name="description" content="Login to AmbulanceHub - Emergency Ambulance Booking System">
  <title>Login - AmbulanceHub</title>
  
  <link rel="stylesheet" href="css/style.css">
  <style>
    body {
      background: linear-gradient(135deg, #f8f9fa 0%, #f1faee 50%, #ffffff 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 0;
    }
    
    .login-wrapper {
      width: 100%;
      max-width: 450px;
      animation: fadeInUp 0.8s ease;
    }

    .login-container {
      background: white;
      border-radius: 1.5rem;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .login-header {
      background: linear-gradient(135deg, #e63946 0%, #d62828 100%);
      color: white;
      padding: 3rem 2rem;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .login-header::before {
      content: '';
      position: absolute;
      top: -50px;
      right: -50px;
      width: 200px;
      height: 200px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
    }

    .login-header::after {
      content: '';
      position: absolute;
      bottom: -30px;
      left: -30px;
      width: 150px;
      height: 150px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 50%;
    }

    .login-header i {
      font-size: 3.5rem;
      margin-bottom: 1rem;
      display: block;
      animation: float 3s ease-in-out infinite;
    }

    .login-header h2 {
      color: white;
      margin-bottom: 0.5rem;
      font-size: 1.8rem;
    }

    .login-header p {
      color: rgba(255, 255, 255, 0.9);
      margin-bottom: 0;
      font-size: 0.95rem;
    }

    .login-body {
      padding: 2.5rem;
    }

    .login-body .form-group label {
      color: #212529;
      font-weight: 600;
    }

    .form-group input {
      background-color: #f8f9fa;
      border: 1px solid #e9ecef;
      color: #212529;
    }

    .form-group input:focus {
      background-color: white;
      color: #212529;
    }

    .forgot-password {
      text-align: right;
      margin-bottom: 1.5rem;
    }

    .forgot-password a {
      font-size: 0.9rem;
      color: #e63946;
      font-weight: 600;
    }

    .forgot-password a:hover {
      text-decoration: underline;
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

    .register-link {
      text-align: center;
      margin-top: 1.5rem;
      color: #495057;
    }

    .register-link a {
      color: #e63946;
      font-weight: 700;
    }

    .register-link a:hover {
      text-decoration: underline;
    }

    .global-message {
      margin-bottom: 1rem;
      text-align: center;
      font-weight: 600;
      font-size: 0.95rem;
    }

    .global-message.error {
      color: #e63946;
    }

    .global-message.success {
      color: #198754;
    }
  </style>
</head>
<body>
  <div class="login-wrapper">
    <div class="login-container">
      <!-- Login Header -->
      <div class="login-header">
        <i class="fas fa-ambulance"></i>
        <h2>AmbulanceHub</h2>
        <p>Welcome Back</p>
      </div>

      <!-- Login Form -->
      <div class="login-body">
        <?php if ($login_error !== ''): ?>
          <div class="global-message error">
            <?php echo htmlspecialchars($login_error, ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php elseif ($login_success !== ''): ?>
          <div class="global-message success">
            <?php echo htmlspecialchars($login_success, ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php endif; ?>

        <form id="loginForm" class="needs-validation" method="post" action="login.php" novalidate>
          <!-- Email/Phone Input -->
          <div class="form-group">
            <label for="email">Email or Phone Number</label>
            <input
              type="text"
              id="email"
              name="email"
              placeholder="Enter your email or phone"
              required
              value="<?php echo htmlspecialchars($identifier, ENT_QUOTES, 'UTF-8'); ?>"
            >
            <div class="form-error"></div>
          </div>

          <!-- Password Input -->
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
            <div class="form-error"></div>
          </div>

          <!-- Remember Me & Forgot Password -->
          <div class="row mb-3">
            <div class="col-6">
              <div class="checkbox-group">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me</label>
              </div>
            </div>
            <div class="col-6 forgot-password">
              <a href="#">Forgot Password?</a>
            </div>
          </div>

          <!-- Submit Button -->
          <button type="submit" class="btn btn-submit w-100">Login to Your Account</button>
        </form>

        <!-- Register Link -->
        <div class="register-link">
          <p>Don't have an account? <a href="register.php">Create one now</a></p>
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

