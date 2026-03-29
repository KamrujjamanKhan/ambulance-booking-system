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
      const container = el.closest('.form-group') || el.closest('.terms-check');
      if (container) {
        container.classList.remove('error');
      }
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
    if (field.type === 'checkbox') {
      if (field.required && !field.checked) {
        this.showError(field, 'This field is required');
        return false;
      }
      return true;
    }

    const value = field.value.trim();

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
    const container = field.closest('.form-group') || field.closest('.terms-check');
    if (container) {
      let errorElement = container.querySelector('.form-error');
      if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'form-error';
        container.appendChild(errorElement);
      }
      errorElement.textContent = message;
      container.classList.add('error');
    }
  }

  submitForm() {
    const formType = this.form.id;

    // For authentication and booking forms, let the browser submit to PHP backend
    if (formType === 'loginForm' || formType === 'registerForm' || formType === 'bookingForm') {
      this.form.submit();
      return;
    }

    // For other forms we keep the existing front-end only behaviour
    if (formType === 'contactForm') {
      this.handleContact();
    }
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

  // Handle logic differently based on whether it's booking form or driver dashboard
  const isBooking = document.getElementById('bookingForm') !== null;
  let selectingPickup = true;

  if (isBooking) {
    // Helper for fetching place name
    function geocodeAndUpdate(latlng, inputElement, typeName) {
      inputElement.value = 'Fetching location...';
      showToast(`Fetching ${typeName} location name...`, 'info');

      fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latlng.lat}&lon=${latlng.lng}`)
        .then(res => res.json())
        .then(data => {
          if (data.display_name) {
            inputElement.value = data.display_name;
            showToast(`${typeName} location set`, 'success');
          } else {
            inputElement.value = `${latlng.lat.toFixed(5)}, ${latlng.lng.toFixed(5)}`;
            showToast('Location set as coordinates', 'success');
          }
        }).catch(err => {
          console.error(err);
          inputElement.value = `${latlng.lat.toFixed(5)}, ${latlng.lng.toFixed(5)}`;
          showToast('Ready', 'success');
        });
    }

    // Advanced booking map with reverse geocoding and color markers
    map.on('click', function (e) {
      const pickupInput = document.getElementById('pickup');
      const destinationInput = document.getElementById('destination');

      if (selectingPickup) {
        if (pickupMarker) map.removeLayer(pickupMarker);
        pickupMarker = L.marker(e.latlng, {
          draggable: true,
          icon: L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41]
          })
        }).addTo(map).bindPopup('Pickup (Drag to adjust)').openPopup();

        pickupMarker.on('dragend', function (event) {
          const latlng = event.target.getLatLng();
          document.getElementById('pickup_lat').value = latlng.lat;
          document.getElementById('pickup_lng').value = latlng.lng;
          geocodeAndUpdate(latlng, pickupInput, 'Pickup');
        });

        document.getElementById('pickup_lat').value = e.latlng.lat;
        document.getElementById('pickup_lng').value = e.latlng.lng;
        geocodeAndUpdate(e.latlng, pickupInput, 'Pickup');
        selectingPickup = false;
      } else {
        if (destinationMarker) map.removeLayer(destinationMarker);
        destinationMarker = L.marker(e.latlng, {
          draggable: true,
          icon: L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41]
          })
        }).addTo(map).bindPopup('Destination (Drag to adjust)').openPopup();

        destinationMarker.on('dragend', function (event) {
          const latlng = event.target.getLatLng();
          document.getElementById('dest_lat').value = latlng.lat;
          document.getElementById('dest_lng').value = latlng.lng;
          geocodeAndUpdate(latlng, destinationInput, 'Destination');
        });

        document.getElementById('dest_lat').value = e.latlng.lat;
        document.getElementById('dest_lng').value = e.latlng.lng;
        geocodeAndUpdate(e.latlng, destinationInput, 'Destination');
        selectingPickup = true;
      }
    });

    // Reset selection if input clicked directly
    const pInput = document.getElementById('pickup');
    const dInput = document.getElementById('destination');
    if (pInput) pInput.addEventListener('focus', () => selectingPickup = true);
    if (dInput) dInput.addEventListener('focus', () => selectingPickup = false);

  } else {
    // Dashboard map handler is called directly from the PHP files when needed
  }
}

// Global function to initialize dashboard map
window.initDashboardMap = function(mapId, startLat, startLng, endLat, endLng) {
  if (!document.getElementById(mapId)) return null;
  const dashMap = L.map(mapId).setView([startLat, startLng], 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(dashMap);
  
  const startIcon = L.icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
    iconSize: [25, 41], iconAnchor: [12, 41]
  });
  const endIcon = L.icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
    iconSize: [25, 41], iconAnchor: [12, 41]
  });
  const ambulanceIcon = L.icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
    iconSize: [25, 41], iconAnchor: [12, 41]
  });

  L.marker([startLat, startLng], {icon: startIcon}).addTo(dashMap).bindPopup('Pickup');
  L.marker([endLat, endLng], {icon: endIcon}).addTo(dashMap).bindPopup('Destination');
  
  const ambMarker = L.marker([startLat, startLng], {icon: ambulanceIcon, zIndexOffset: 1000}).addTo(dashMap).bindPopup('Ambulance');
  
  let routePolyline = null;
  let decodedPath = [];

  // Fetch route from OSRM
  fetch(`https://router.project-osrm.org/route/v1/driving/${startLng},${startLat};${endLng},${endLat}?overview=full&geometries=geojson`)
    .then(res => res.json())
    .then(data => {
      if(data.routes && data.routes.length > 0) {
        const coords = data.routes[0].geometry.coordinates; // [lng, lat]
        decodedPath = coords.map(c => [c[1], c[0]]); // Leaflet needs [lat, lng]
        routePolyline = L.polyline(decodedPath, {color: 'blue', weight: 4}).addTo(dashMap);
        dashMap.fitBounds(routePolyline.getBounds(), {padding: [30, 30]});
      }
    });

  setTimeout(() => dashMap.invalidateSize(), 500);

  return {
    map: dashMap,
    ambMarker: ambMarker,
    updateProgress: function(progressPercent) {
      if(!decodedPath || decodedPath.length === 0) return;
      const progress = Math.min(Math.max(progressPercent, 0), 100) / 100.0;
      
      // Calculate total distance (simple segmented proportional distance)
      let totalDist = 0;
      const segDists = [];
      for(let i=0; i<decodedPath.length-1; i++) {
        const d = dashMap.distance(decodedPath[i], decodedPath[i+1]);
        segDists.push(d);
        totalDist += d;
      }
      
      const targetDist = totalDist * progress;
      let currDist = 0;
      for(let i=0; i<decodedPath.length-1; i++) {
        if(currDist + segDists[i] >= targetDist) {
          const segProgress = (targetDist - currDist) / segDists[i];
          const lat = decodedPath[i][0] + (decodedPath[i+1][0] - decodedPath[i][0]) * segProgress;
          const lng = decodedPath[i][1] + (decodedPath[i+1][1] - decodedPath[i][1]) * segProgress;
          ambMarker.setLatLng([lat, lng]);
          return;
        }
        currDist += segDists[i];
      }
      if(decodedPath.length > 0) {
        ambMarker.setLatLng(decodedPath[decodedPath.length-1]);
      }
    }
  };
}

function logout() {
  // Let PHP handle session destruction and redirect.
  window.location.href = 'logout.php';
}

// ============================================
// SMOOTH SCROLL FOR ANCHOR LINKS
// ============================================

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
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
  link.addEventListener('click', function () {
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
