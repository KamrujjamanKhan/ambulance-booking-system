<?php
require_once __DIR__ . '/auth.php';

$is_logged_in = isset($_SESSION['user_id']);
$current_role = current_user_role();
$current_name = current_user_name();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Emergency Ambulance Booking System - Fast, Reliable Medical Transport Services">
  <meta name="keywords" content="ambulance, emergency, medical, booking, Bangladesh">
  <title>AmbulanceHub - Emergency Medical Transport</title>
  
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
            <a class="nav-link active" href="#home">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#services">Services</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#about">About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#how-it-works">How It Works</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#contact">Contact</a>
          </li>
        </ul>
        <div class="ms-3">
          <?php if ($is_logged_in): ?>
            <?php if ($current_role === 'admin'): ?>
              <a href="admin_dashboard.php" class="btn btn-login">Admin Dashboard</a>
            <?php elseif ($current_role === 'driver'): ?>
              <a href="driver_dashboard.php" class="btn btn-login">Driver Dashboard</a>
            <?php else: ?>
              <a href="user_dashboard.php" class="btn btn-login">My Dashboard</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-register">Logout</a>
          <?php else: ?>
            <a href="login.php" class="btn btn-login">Login</a>
            <a href="register.php" class="btn btn-register">Register</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>

  <!-- ============================================
       HERO SECTION
       ============================================ -->
  <section class="hero-section" id="home">
    <div class="container-with-gaps">
      <div class="row align-items-center g-5">
        <div class="col-lg-6 hero-content">
          <div class="hero-badge">
            <i class="fas fa-check-circle"></i> 24/7 Emergency Response
          </div>
          <h1 class="hero-title">Emergency <br>Medical <span class="highlight">Transport</span></h1>
          <p class="hero-subtitle">Professional ambulance services with advanced life support. When every second counts, trust AmbulanceHub for rapid response and expert care.</p>
          
          <div class="hero-buttons">
            <?php if ($is_logged_in && $current_role === 'patient'): ?>
              <a href="a_booking.php" class="btn btn-hero-primary">
                <i class="fas fa-phone"></i> Book Ambulance Now
              </a>
            <?php else: ?>
              <a href="login.php" class="btn btn-hero-primary">
                <i class="fas fa-phone"></i> Book Ambulance Now
              </a>
            <?php endif; ?>
            <a href="#services" class="btn btn-hero-secondary">
              <i class="fas fa-arrow-right"></i> Learn More
            </a>
          </div>

          <div class="hero-stats">
            <div class="stat-item">
              <div class="stat-number">2000+</div>
              <div class="stat-label">Lives Saved</div>
            </div>
            <div class="stat-item">
              <div class="stat-number">5min</div>
              <div class="stat-label">Avg Response</div>
            </div>
            <div class="stat-item">
              <div class="stat-number">24/7</div>
              <div class="stat-label">Available</div>
            </div>
          </div>
        </div>

        <div class="col-lg-6 hero-image">
          <div class="hero-image-circle">
            <i class="fas fa-ambulance"></i>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================================
       SERVICES SECTION
       ============================================ -->
  <section class="services-section" id="services">
    <div class="container-with-gaps">
      <div class="section-title">
        <h2>Our Services</h2>
        <p class="section-subtitle">Comprehensive ambulance solutions for every medical need</p>
      </div>

      <div class="row g-4">
        <!-- ICU Ambulance -->
        <div class="col-md-6 col-lg-4">
          <div class="service-card">
            <div class="service-icon">
              <i class="fas fa-hospital"></i>
            </div>
            <h4>ICU Ambulance</h4>
            <p>Fully equipped with advanced life support systems, ventilators, and trained medical staff for critical care transport and intensive monitoring.</p>
          </div>
        </div>

        <!-- Normal Ambulance -->
        <div class="col-md-6 col-lg-4">
          <div class="service-card">
            <div class="service-icon">
              <i class="fas fa-car-side"></i>
            </div>
            <h4>Standard Ambulance</h4>
            <p>Reliable transportation with basic medical equipment and trained paramedics for routine medical transport and non-critical patients.</p>
          </div>
        </div>

        <!-- Emergency Support -->
        <div class="col-md-6 col-lg-4">
          <div class="service-card">
            <div class="service-icon">
              <i class="fas fa-headset"></i>
            </div>
            <h4>Emergency Support</h4>
            <p>24/7 emergency dispatch with trained operators, real-time tracking, and immediate response to critical situations and medical emergencies.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================================
       ABOUT SECTION
       ============================================ -->
  <section class="about-section" id="about">
    <div class="container-with-gaps">
      <div class="about-content">
        <div class="about-text">
          <h2>Why Choose AmbulanceHub?</h2>
          <p>We are committed to providing the fastest and most reliable ambulance services across Bangladesh. With a modern fleet and certified medical professionals, we ensure every patient receives exceptional care during transport.</p>
          
          <ul class="about-features">
            <li>
              <i class="fas fa-check"></i>
              <span><strong>24/7 Availability:</strong> Round-the-clock emergency response</span>
            </li>
            <li>
              <i class="fas fa-check"></i>
              <span><strong>Trained Staff:</strong> Certified paramedics and medical professionals</span>
            </li>
            <li>
              <i class="fas fa-check"></i>
              <span><strong>Modern Equipment:</strong> Latest medical technology and life support</span>
            </li>
            <li>
              <i class="fas fa-check"></i>
              <span><strong>Real-time Tracking:</strong> GPS tracking for transparency and safety</span>
            </li>
            <li>
              <i class="fas fa-check"></i>
              <span><strong>Affordable Rates:</strong> Competitive pricing without compromising quality</span>
            </li>
            <li>
              <i class="fas fa-check"></i>
              <span><strong>Quick Response:</strong> Average response time under 10 minutes</span>
            </li>
          </ul>
        </div>

        <div class="about-image">
          <i class="fas fa-heartbeat"></i>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================================
       HOW IT WORKS SECTION
       ============================================ -->
  <section class="how-it-works" id="how-it-works">
    <div class="container-with-gaps">
      <div class="section-title">
        <h2>How It Works</h2>
        <p class="section-subtitle">Simple and quick process to book an ambulance</p>
      </div>

      <div class="row g-4">
        <!-- Step 1 -->
        <div class="col-md-6 col-lg-4">
          <div class="step-card">
            <div class="step-number">1</div>
            <h4>Request Ambulance</h4>
            <p>Fill in your location, destination, and ambulance type. Our system instantly matches you with the nearest available ambulance.</p>
          </div>
        </div>

        <!-- Step 2 -->
        <div class="col-md-6 col-lg-4">
          <div class="step-card">
            <div class="step-number">2</div>
            <h4>Driver Assigned</h4>
            <p>A qualified driver and medical team are assigned to your request. You'll receive real-time updates and driver details on your phone.</p>
          </div>
        </div>

        <!-- Step 3 -->
        <div class="col-md-6 col-lg-4">
          <div class="step-card">
            <div class="step-number">3</div>
            <h4>Track & Arrive</h4>
            <p>Track your ambulance in real-time on the map. Our team arrives quickly and safely transports you to your destination.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================================
       CONTACT SECTION
       ============================================ -->
  <section class="contact-section" id="contact">
    <div class="container-with-gaps">
      <div class="section-title">
        <h2>Get In Touch</h2>
        <p class="section-subtitle">Have questions? We'd love to hear from you</p>
      </div>

      <div class="contact-container">
        <form id="contactForm" class="needs-validation">
          <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="Your name" required>
            <div class="form-error"></div>
          </div>

          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="your@email.com" required>
            <div class="form-error"></div>
          </div>

          <div class="form-group">
            <label for="subject">Subject</label>
            <input type="text" id="subject" name="subject" placeholder="Message subject" required>
            <div class="form-error"></div>
          </div>

          <div class="form-group">
            <label for="message">Message</label>
            <textarea id="message" name="message" placeholder="Your message here..." required></textarea>
            <div class="form-error"></div>
          </div>

          <button type="submit" class="btn btn-submit">Send Message</button>
        </form>
      </div>
    </div>
  </section>

  <!-- ============================================
       FOOTER
       ============================================ -->
  <footer>
    <div class="container-with-gaps">
      <div class="footer-content">
        <div class="footer-section">
          <h5><i class="fas fa-ambulance"></i> AmbulanceHub</h5>
          <p>Providing fast and reliable ambulance services across Bangladesh with a commitment to saving lives.</p>
        </div>

        <div class="footer-section">
          <h5>Quick Links</h5>
          <ul>
            <li><a href="#home">Home</a></li>
            <li><a href="#services">Services</a></li>
            <li><a href="#about">About Us</a></li>
            <li><a href="#contact">Contact</a></li>
          </ul>
        </div>

        <div class="footer-section">
          <h5>Services</h5>
          <ul>
            <li><a href="#">ICU Ambulance</a></li>
            <li><a href="#">Standard Ambulance</a></li>
            <li><a href="#">Emergency Support</a></li>
            <li><a href="#">Medical Transport</a></li>
          </ul>
        </div>

        <div class="footer-section">
          <h5>Contact Info</h5>
          <ul>
            <li><i class="fas fa-phone"></i> +880 1234-567890</li>
            <li><i class="fas fa-envelope"></i> info@ambulancehub.com</li>
            <li><i class="fas fa-map-marker-alt"></i> Dhaka, Bangladesh</li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <p>&copy; 2026 AmbulanceHub. All rights reserved. | <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
      </div>
    </div>
  </footer>

  <!-- Loading Overlay -->
  <div class="loading-overlay">
    <div class="spinner"></div>
  </div>

  <!-- Toast Container -->
  <div class="toast-container"></div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Leaflet JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
  <!-- Font Awesome -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
  <!-- Custom JS -->
  <script src="js/script.js"></script>
</body>
</html>

