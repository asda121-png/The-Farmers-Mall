<?php
// Session and authentication check
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'rider') {
    header('Location: ../auth/login.php');
    exit();
}

$rider_name = $_SESSION['user_name'] ?? 'John Rider';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Delivery Map – Farmers Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <style>
    #map {
      height: 500px;
      border-radius: 0.5rem;
    }
    .status-indicator {
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0% { opacity: 1; }
      50% { opacity: 0.6; }
      100% { opacity: 1; }
    }
  </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

<?php
// Include the rider header
include 'riderheader.php';
?>

  <!-- Map Content -->
  <main class="max-w-7xl mx-auto px-6 py-6 space-y-6 flex-grow w-full mb-20">

    <!-- Page Title -->
    <div>
      <h2 class="text-2xl font-semibold">Delivery Map</h2>
      <p class="text-gray-600">Track your location and navigate to delivery points</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      
      <!-- Map Area -->
      <div class="lg:col-span-3">
        <div class="bg-white rounded-lg shadow-sm p-4">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Live Map View</h3>
            <div class="flex gap-2">
              <button class="px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                <i class="fas fa-location-crosshairs mr-1"></i> Center on Me
              </button>
              <button class="px-3 py-1 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                <i class="fas fa-expand mr-1"></i> Fullscreen
              </button>
            </div>
          </div>
          <div id="map" class="w-full"></div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        
        <!-- Current Status -->
        <div class="bg-white rounded-lg shadow-sm">
          <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold">Current Status</h3>
          </div>
          <div class="p-4 space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Status</span>
              <span class="status-indicator bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Online</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Active Orders</span>
              <span class="font-semibold">2</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Next Pickup</span>
              <span class="font-semibold">2.3 km</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Est. Time</span>
              <span class="font-semibold">8 mins</span>
            </div>
          </div>
        </div>

        <!-- Navigation Steps -->
        <div class="bg-white rounded-lg shadow-sm">
          <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold">Navigation</h3>
          </div>
          <div class="p-4">
            <div class="space-y-3">
              <div class="flex items-start space-x-3">
                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                  <i class="fas fa-store text-green-600 text-xs"></i>
                </div>
                <div class="flex-1">
                  <p class="font-medium text-sm">Pick-up Point</p>
                  <p class="text-xs text-gray-500">Fresh Farm Market</p>
                  <p class="text-xs text-green-600 mt-1">2.3 km away</p>
                </div>
              </div>
              
              <div class="flex items-start space-x-3">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                  <i class="fas fa-home text-blue-600 text-xs"></i>
                </div>
                <div class="flex-1">
                  <p class="font-medium text-sm">Drop-off Point</p>
                  <p class="text-xs text-gray-500">123 Main St, Quezon City</p>
                  <p class="text-xs text-blue-600 mt-1">5.7 km from pickup</p>
                </div>
              </div>
            </div>
            
            <button class="w-full mt-4 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
              <i class="fas fa-directions mr-2"></i> Start Navigation
            </button>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm">
          <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold">Quick Actions</h3>
          </div>
          <div class="p-4 space-y-2">
            <button class="w-full bg-green-600 text-white px-3 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm">
              <i class="fas fa-check-circle mr-2"></i> Arrived at Pickup
            </button>
            <button class="w-full border border-gray-300 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors text-sm">
              <i class="fas fa-phone mr-2"></i> Call Customer
            </button>
            <button class="w-full border border-gray-300 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors text-sm">
              <i class="fas fa-exclamation-triangle mr-2"></i> Report Issue
            </button>
          </div>
        </div>

      </div>
    </div>

  </main>

<?php
// Include the global footer
include '../includes/footer.php';
?>

<script>
// Initialize map (centered on Manila as default)
const map = L.map('map').setView([14.5995, 120.9842], 13);

// Add tile layer
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Custom icons
const riderIcon = L.divIcon({
  html: '<div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center"><i class="fas fa-motorcycle text-white text-xs"></i></div>',
  iconSize: [32, 32],
  className: 'custom-marker'
});

const pickupIcon = L.divIcon({
  html: '<div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center"><i class="fas fa-store text-white text-xs"></i></div>',
  iconSize: [32, 32],
  className: 'custom-marker'
});

const dropoffIcon = L.divIcon({
  html: '<div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center"><i class="fas fa-home text-white text-xs"></i></div>',
  iconSize: [32, 32],
  className: 'custom-marker'
});

// Add markers (mock locations)
const riderLocation = L.marker([14.5995, 120.9842], {icon: riderIcon}).addTo(map)
  .bindPopup('<b>Your Location</b><br>Current position');

const pickupLocation = L.marker([14.6050, 120.9890], {icon: pickupIcon}).addTo(map)
  .bindPopup('<b>Pick-up Point</b><br>Fresh Farm Market');

const dropoffLocation = L.marker([14.6100, 120.9950], {icon: dropoffIcon}).addTo(map)
  .bindPopup('<b>Drop-off Point</b><br>123 Main St, Quezon City');

// Draw route line (mock straight line)
const routeCoordinates = [
  [14.5995, 120.9842],
  [14.6050, 120.9890],
  [14.6100, 120.9950]
];

const routeLine = L.polyline(routeCoordinates, {
  color: '#10b981',
  weight: 4,
  opacity: 0.7,
  dashArray: '10, 10'
}).addTo(map);

// Center on rider button
document.querySelector('.fa-location-crosshairs').parentElement.addEventListener('click', function() {
  map.setView([14.5995, 120.9842], 15);
});

// Simulate real-time rider movement
setInterval(() => {
  // In a real implementation, this would update from GPS data
  const lat = 14.5995 + (Math.random() - 0.5) * 0.002;
  const lng = 120.9842 + (Math.random() - 0.5) * 0.002;
  riderLocation.setLatLng([lat, lng]);
}, 5000);

// Start navigation button
document.querySelector('.fa-directions').parentElement.addEventListener('click', function() {
  // In a real implementation, this would open Google Maps or similar
  alert('Opening navigation app...');
  // window.open('https://maps.google.com/?q=14.6050,120.9890');
});
</script>

</body>
</html>
