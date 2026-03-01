/* ============================================
   AMBULANCE BOOKING SYSTEM - JAVASCRIPT
   Modern Vanilla JS with Smooth Animations
   ============================================ */

// ============================================
// FORM VALIDATION CLASS
// ============================================

class FormValidator {
  constructor(formId) {
    this.form = document.getElementById(formId);
    this.isValid = true;
    if (this.form) {
      this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    }
  }

  handleSubmit(e) {
    e.preventDefault();
    this.isValid = true;
    this.clearErrors();
    this.validateForm();
    
    if (this.isValid) {
      this.submitForm();
    }
  }

  clearErrors() {
    const errorElements = this.form.querySelectorAll('.form-error');
    errorElements.forEach(el => {
      el.textContent = '';
      el.closest('.form-group').classList.remove('error');
    });
  }

  validateForm() {
    const fields = this.form.querySelectorAll('[required]');
    fields.forEach(field => {
      if (!this.validateField(field)) {
        this.isValid = false;
      }
    });
  }

  validateField(field) {
    const value = field.value.trim();
    const errorElement = field.closest('.form-group').querySelector('.form-error');
    
    if (!value) {
      this.showError(field, 'This field is required');
      return false;
    }

    if (field.type === 'email' && !this.isValidEmail(value)) {
      this.showError(field, 'Please enter a valid email address');
      return false;
    }

    if (field.name === 'phone' && !this.isValidPhone(value)) {
      this.showError(field, 'Please enter a valid phone number');
      return false;
    }

    if (field.name === 'password' && value.length < 6) {
      this.showError(field, 'Password must be at least 6 characters');
      return false;
    }

    if (field.name === 'confirm_password') {
      const passwordField = this.form.querySelector('input[name="password"]');
      if (value !== passwordField.value) {
        this.showError(field, 'Passwords do not match');
        return false;
      }
    }

    return true;
  }

  isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  isValidPhone(phone) {
    const phoneRegex = /^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/;
    return phoneRegex.test(phone.replace(/\s/g, ''));
  }

  showError(field, message) {
    const errorElement = field.closest('.form-group').querySelector('.form-error');
    if (errorElement) {
      errorElement.textContent = message;
      field.closest('.form-group').classList.add('error');
    }
  }

  submitForm() {
    const formType = this.form.id;
    
    if (formType === 'loginForm') {
      this.handleLogin();
    } else if (formType === 'registerForm') {
      this.handleRegister();
    } else if (formType === 'bookingForm') {
      this.handleBooking();
    } else if (formType === 'contactForm') {
      this.handleContact();
    }
  }

  handleLogin() {
    const email = this.form.querySelector('#email').value;
    const password = this.form.querySelector('#password').value;
    const rememberMe = this.form.querySelector('#remember').checked;

    showLoadingOverlay();
    
    // TODO: Replace with actual API endpoint
    // POST /api/login.php
    setTimeout(() => {
      hideLoadingOverlay();
      showToast('Login successful! Redirecting...', 'success');
      setTimeout(() => {
        window.location.href = 'user_dashboard.html';
      }, 1500);
    }, 1500);
  }

  handleRegister() {
    const fullName = this.form.querySelector('#fullname').value;
    const phone = this.form.querySelector('#phone').value;
    const email = this.form.querySelector('#email').value;
    const password = this.form.querySelector('#password').value;
    const role = this.form.querySelector('#role').value;

    showLoadingOverlay();
    
    // TODO: Replace with actual API endpoint
    // POST /api/register.php
    setTimeout(() => {
      hideLoadingOverlay();
      showToast('Registration successful! Redirecting to login...', 'success');
      setTimeout(() => {
        window.location.href = 'login.html';
      }, 1500);
    }, 1500);
  }

  handleBooking() {
    const patientName = this.form.querySelector('#patient_name').value;
    const phone = this.form.querySelector('#phone').value;
    const pickupAddress = this.form.querySelector('#pickup_address').value;
    const destinationHospital = this.form.querySelector('#destination_hospital').value;
    const city = this.form.querySelector('#city').value;
    const ambulanceType = this.form.querySelector('#ambulance_type').value;
    const isEmergency = this.form.querySelector('#is_emergency').checked;
    const needOxygen = this.form.querySelector('#need_oxygen').checked;
    const needVentilator = this.form.querySelector('#need_ventilator').checked;
    const notes = this.form.querySelector('#notes').value;

    showLoadingOverlay();
    
    // TODO: Replace with actual API endpoint
    // POST /api/book_ambulance.php
    setTimeout(() => {
      hideLoadingOverlay();
      showToast('Ambulance booked successfully! Driver assigned.', 'success');
      this.form.reset();
    }, 1500);
  }

  handleContact() {
    const name = this.form.querySelector('#name').value;
    const email = this.form.querySelector('#email').value;
    const subject = this.form.querySelector('#subject').value;
    const message = this.form.querySelector('#message').value;

    showLoadingOverlay();
    
    // TODO: Replace with actual API endpoint
    // POST /api/contact.php
    setTimeout(() => {
      hideLoadingOverlay();
      showToast('Message sent successfully! We will get back to you soon.', 'success');
      this.form.reset();
    }, 1500);
  }
}

// ============================================
// TOAST NOTIFICATION SYSTEM
// ============================================

function showToast(message, type = 'info', duration = 3000) {
  const container = document.querySelector('.toast-container');
  
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  
  const icons = {
    success: 'fas fa-check-circle',
    error: 'fas fa-times-circle',
    warning: 'fas fa-exclamation-circle',
    info: 'fas fa-info-circle'
  };

  toast.innerHTML = `
    <i class="fas ${icons[type]} toast-icon"></i>
    <span>${message}</span>
    <button class="toast-close" onclick="this.parentElement.remove()">
      <i class="fas fa-times"></i>
    </button>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    if (toast.parentElement) {
      toast.remove();
    }
  }, duration);
}

// ============================================
// LOADING OVERLAY
// ============================================

function showLoadingOverlay() {
  const overlay = document.querySelector('.loading-overlay');
  if (overlay) {
    overlay.classList.add('active');
  }
}

function hideLoadingOverlay() {
  const overlay = document.querySelector('.loading-overlay');
  if (overlay) {
    overlay.classList.remove('active');
  }
}

// ============================================
// MAP INTEGRATION (LEAFLET.JS)
// ============================================

let map = null;
let pickupMarker = null;
let destinationMarker = null;

function initializeMap() {
  const mapElement = document.getElementById('map');
  if (!mapElement) return;

  // Initialize map centered on Dhaka, Bangladesh
  map = L.map('map').setView([23.8103, 90.4125], 13);

  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
  }).addTo(map);

  // Map click handler for location selection
  map.on('click', function(e) {
    const pickupInput = document.getElementById('pickup');
    const destinationInput = document.getElementById('destination');

    if (pickupInput && !pickupInput.value) {
      // Set pickup location
      pickupMarker = L.marker(e.latlng).addTo(map);
      pickupInput.value = `${e.latlng.lat.toFixed(4)}, ${e.latlng.lng.toFixed(4)}`;
      showToast('Pickup location set', 'success');
    } else if (destinationInput && !destinationInput.value) {
      // Set destination location
      destinationMarker = L.marker(e.latlng).addTo(map);
      destinationInput.value = `${e.latlng.lat.toFixed(4)}, ${e.latlng.lng.toFixed(4)}`;
      showToast('Destination location set', 'success');
    }
  });
}

// ============================================
// DRIVER LOCATION UPDATE
// ============================================

function updateDriverLocation() {
  if (navigator.geolocation) {
    showLoadingOverlay();
    
    navigator.geolocation.getCurrentPosition(
      function(position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        
        // TODO: Replace with actual API endpoint
        // POST /api/update_location.php
        
        hideLoadingOverlay();
        showToast(`Location updated: ${lat.toFixed(4)}, ${lng.toFixed(4)}`, 'success');
        
        // Update map if it exists
        if (map) {
          map.setView([lat, lng], 15);
          L.marker([lat, lng]).addTo(map).bindPopup('Your current location');
        }
      },
      function(error) {
        hideLoadingOverlay();
        showToast('Unable to get your location. Please enable location services.', 'error');
      }
    );
  } else {
    showToast('Geolocation is not supported by your browser.', 'error');
  }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function formatCurrency(amount) {
  return new Intl.NumberFormat('bn-BD', {
    style: 'currency',
    currency: 'BDT'
  }).format(amount);
}

function formatDate(date) {
  return new Intl.DateTimeFormat('bn-BD', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(date));
}

function getStatusBadgeClass(status) {
  const statusMap = {
    'pending': 'badge-pending',
    'accepted': 'badge-accepted',
    'on-way': 'badge-on-way',
    'on_way': 'badge-on-way',
    'completed': 'badge-completed',
    'cancelled': 'badge-cancelled'
  };
  return statusMap[status.toLowerCase()] || 'badge-pending';
}

function logout() {
  showLoadingOverlay();
  
  // TODO: Replace with actual API endpoint
  // POST /api/logout.php
  
  setTimeout(() => {
    hideLoadingOverlay();
    showToast('Logged out successfully', 'success');
    setTimeout(() => {
      window.location.href = 'index.html';
    }, 1000);
  }, 1000);
}

// ============================================
// SMOOTH SCROLL FOR ANCHOR LINKS
// ============================================

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    const href = this.getAttribute('href');
    if (href !== '#' && document.querySelector(href)) {
      e.preventDefault();
      document.querySelector(href).scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }
  });
});

// ============================================
// NAVBAR ACTIVE LINK HIGHLIGHTING
// ============================================

window.addEventListener('scroll', () => {
  const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
  
  navLinks.forEach(link => {
    const href = link.getAttribute('href');
    if (href && href.startsWith('#')) {
      const section = document.querySelector(href);
      if (section) {
        const rect = section.getBoundingClientRect();
        if (rect.top <= 100 && rect.bottom >= 100) {
          navLinks.forEach(l => l.classList.remove('active'));
          link.classList.add('active');
        }
      }
    }
  });
});

// ============================================
// SIDEBAR ACTIVE LINK
// ============================================

document.querySelectorAll('.sidebar-menu a').forEach(link => {
  link.addEventListener('click', function() {
    document.querySelectorAll('.sidebar-menu a').forEach(l => l.classList.remove('active'));
    this.classList.add('active');
  });
});

// ============================================
// INITIALIZE ON PAGE LOAD
// ============================================

document.addEventListener('DOMContentLoaded', () => {
  // Initialize forms
  if (document.getElementById('loginForm')) {
    new FormValidator('loginForm');
  }
  
  if (document.getElementById('registerForm')) {
    new FormValidator('registerForm');
  }
  
  if (document.getElementById('bookingForm')) {
    new FormValidator('bookingForm');
    initializeMap();
  }
  
  if (document.getElementById('contactForm')) {
    new FormValidator('contactForm');
  }

  // Initialize map on driver dashboard
  if (document.getElementById('map') && !document.getElementById('bookingForm')) {
    initializeMap();
  }

  // Add animation to elements on scroll
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, observerOptions);

  document.querySelectorAll('.service-card, .step-card, .stat-card').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'all 0.6s ease';
    observer.observe(el);
  });
});

// ============================================
// RESPONSIVE SIDEBAR TOGGLE
// ============================================

const navbarToggler = document.querySelector('.navbar-toggler');
if (navbarToggler) {
  navbarToggler.addEventListener('click', () => {
    const navbarCollapse = document.querySelector('.navbar-collapse');
    navbarCollapse.classList.toggle('show');
  });
}

console.log('✅ AmbulanceHub JavaScript loaded successfully');
