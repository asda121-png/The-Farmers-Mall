<?php
// Start session (authentication check temporarily removed for testing)
session_start();

// Mock rider data for testing
$rider_name = 'Test Rider';
$rider_id = 'RIDER_TEST_001';

// Set test data in session for other functionality
$_SESSION['user_id'] = $rider_id;
$_SESSION['user_name'] = $rider_name;
$_SESSION['user_role'] = 'rider';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rider Dashboard – Farmers Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .status-badge {
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0% { opacity: 1; }
      50% { opacity: 0.7; }
      100% { opacity: 1; }
    }
    .order-card {
      transition: all 0.3s ease;
    }
    .order-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

<?php
// Include the rider header
include 'riderheader.php';
?>

  <!-- Dashboard Content -->
  <main class="max-w-7xl mx-auto px-6 py-6 space-y-6 flex-grow w-full mb-20">

    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-lg p-6 text-white">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold mb-2">Welcome back, <?php echo htmlspecialchars($rider_name); ?>!</h2>
          <p class="text-green-100">Ready to deliver fresh produce to our customers?</p>
        </div>
        <div class="text-right">
          <div class="bg-white/20 rounded-lg px-4 py-2">
            <p class="text-sm text-green-100">Current Status</p>
            <p class="text-lg font-semibold">Online</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <a href="ordermanagement.php" class="bg-white shadow-sm rounded-lg p-5 flex items-center space-x-4 hover:shadow-md transition-transform transform hover:-translate-y-1">
        <div class="text-green-600 text-2xl"><i class="fas fa-box"></i></div>
        <div>
          <p class="text-gray-500 text-sm">Available Orders</p>
          <h3 class="text-xl font-semibold">12</h3>
        </div>
        <div class="ml-auto text-gray-400">
          <i class="fas fa-chevron-right"></i>
        </div>
      </a>

      <a href="ordermanagement.php?status=assigned" class="bg-white shadow-sm rounded-lg p-5 flex items-center space-x-4 hover:shadow-md transition-transform transform hover:-translate-y-1">
        <div class="text-blue-600 text-2xl"><i class="fas fa-motorcycle"></i></div>
        <div>
          <p class="text-gray-500 text-sm">Active Deliveries</p>
          <h3 class="text-xl font-semibold">2</h3>
        </div>
        <div class="ml-auto text-gray-400">
          <i class="fas fa-chevron-right"></i>
        </div>
      </a>

      <div class="bg-white shadow-sm rounded-lg p-5 flex items-center space-x-4 hover:shadow-md transition-shadow">
        <div class="text-green-600 text-2xl"><i class="fas fa-peso-sign"></i></div>
        <div>
          <p class="text-gray-500 text-sm">Today's Earnings</p>
          <h3 class="text-xl font-semibold">₱1,250.00</h3>
        </div>
      </div>

      <div class="bg-white shadow-sm rounded-lg p-5 flex items-center space-x-4 hover:shadow-md transition-shadow">
        <div class="text-yellow-600 text-2xl"><i class="fas fa-star"></i></div>
        <div>
          <p class="text-gray-500 text-sm">Rating</p>
          <div class="flex items-center">
            <span class="text-yellow-500 mr-1">4.8</span>
            <i class="fas fa-star text-yellow-400"></i>
            <span class="text-gray-500 text-sm ml-1">(24)</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
      <div class="border-b border-gray-200 px-6 py-4">
        <h2 class="text-lg font-semibold text-gray-800">Quick Actions</h2>
      </div>
      <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="ordermanagement.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
          <div class="bg-green-100 p-3 rounded-full mr-4">
            <i class="fas fa-box text-green-600"></i>
          </div>
          <div>
            <h3 class="font-medium text-gray-900">Manage Orders</h3>
            <p class="text-sm text-gray-500">View and manage all orders</p>
          </div>
          <div class="ml-auto text-gray-400">
            <i class="fas fa-chevron-right"></i>
          </div>
        </a>
        
        <a href="ridermap.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
          <div class="bg-blue-100 p-3 rounded-full mr-4">
            <i class="fas fa-map-marked-alt text-blue-600"></i>
          </div>
          <div>
            <h3 class="font-medium text-gray-900">Delivery Map</h3>
            <p class="text-sm text-gray-500">View delivery locations</p>
          </div>
          <div class="ml-auto text-gray-400">
            <i class="fas fa-chevron-right"></i>
          </div>
        </a>
        
        <a href="#" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
          <div class="bg-yellow-100 p-3 rounded-full mr-4">
            <i class="fas fa-chart-line text-yellow-600"></i>
          </div>
          <div>
            <h3 class="font-medium text-gray-900">Earnings</h3>
            <p class="text-sm text-gray-500">View your earnings and reports</p>
          </div>
          <div class="ml-auto text-gray-400">
            <i class="fas fa-chevron-right"></i>
          </div>
        </a>
      </div>
    </div>
    
    <!-- Recent Orders -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
      <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800">Recent Orders</h2>
        <a href="ordermanagement.php" class="text-sm text-green-600 hover:text-green-700 font-medium">View All</a>
      </div>
      
      <div class="divide-y divide-gray-200">
        <!-- Sample Order 1 -->
        <div class="p-4 hover:bg-gray-50 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium text-gray-900">#ORD-001234</p>
              <p class="text-sm text-gray-500">Juan Dela Cruz • Quezon City</p>
            </div>
            <div class="text-right">
              <p class="font-medium">₱1,250.00</p>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                Pending
              </span>
            </div>
          </div>
        </div>
        
        <!-- Sample Order 2 -->
        <div class="p-4 hover:bg-gray-50 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium text-gray-900">#ORD-001233</p>
              <p class="text-sm text-gray-500">Maria Santos • Makati City</p>
            </div>
            <div class="text-right">
              <p class="font-medium">₱890.00</p>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                In Transit
              </span>
            </div>
          </div>
        </div>
        
        <!-- Sample Order 3 -->
        <div class="p-4 hover:bg-gray-50 transition-colors">
          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium text-gray-900">#ORD-001232</p>
              <p class="text-sm text-gray-500">Pedro Cruz • Taguig City</p>
            </div>
            <div class="text-right">
              <p class="font-medium">₱1,750.00</p>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                Delivered
              </span>
            </div>
          </div>
        </div>
      </div>
      
      <div class="px-6 py-3 border-t border-gray-200 text-center">
        <a href="riderorders.php" class="text-sm font-medium text-green-600 hover:text-green-700">
          View all orders <span aria-hidden="true">→</span>
        </a>
      </div>
    </div>
          <h3 class="text-xl font-semibold">₱1,250</h3>
        </div>
      </div>

      <div class="bg-white shadow-sm rounded-lg p-5 flex items-center space-x-4 hover:shadow-md transition-shadow">
        <div class="text-purple-600 text-2xl"><i class="fas fa-star"></i></div>
        <div>
          <p class="text-gray-500 text-sm">Rating</p>
          <h3 class="text-xl font-semibold">4.8</h3>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- Available Orders Section -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm">
          <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold flex items-center">
              <i class="fas fa-box text-green-600 mr-2"></i>
              Available Orders
              <span class="ml-2 bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">12 New</span>
            </h3>
          </div>
          <div class="p-4 space-y-3 max-h-96 overflow-y-auto">
            
            <!-- Order Card 1 -->
            <div class="order-card border border-gray-200 rounded-lg p-4 hover:border-green-400">
              <div class="flex justify-between items-start mb-3">
                <div>
                  <h4 class="font-semibold text-gray-800">Order #ORD-2025-001</h4>
                  <p class="text-sm text-gray-500">15 minutes ago</p>
                </div>
                <div class="text-right">
                  <p class="text-lg font-semibold text-green-600">₱85</p>
                  <p class="text-xs text-gray-500">2.5 km</p>
                </div>
              </div>
              
              <div class="space-y-2 text-sm">
                <div class="flex items-center text-gray-600">
                  <i class="fas fa-store w-4 text-green-500"></i>
                  <span class="ml-2">Pick-up: Fresh Farm Market</span>
                </div>
                <div class="flex items-center text-gray-600">
                  <i class="fas fa-home w-4 text-blue-500"></i>
                  <span class="ml-2">Drop-off: 123 Main St, Quezon City</span>
                </div>
              </div>
              
              <div class="flex gap-2 mt-3">
                <button class="flex-1 bg-green-600 text-white px-3 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                  <i class="fas fa-check mr-1"></i> Accept
                </button>
                <button class="flex-1 border border-gray-300 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium">
                  <i class="fas fa-times mr-1"></i> Decline
                </button>
              </div>
            </div>

            <!-- Order Card 2 -->
            <div class="order-card border border-gray-200 rounded-lg p-4 hover:border-green-400">
              <div class="flex justify-between items-start mb-3">
                <div>
                  <h4 class="font-semibold text-gray-800">Order #ORD-2025-002</h4>
                  <p class="text-sm text-gray-500">8 minutes ago</p>
                </div>
                <div class="text-right">
                  <p class="text-lg font-semibold text-green-600">₱120</p>
                  <p class="text-xs text-gray-500">4.1 km</p>
                </div>
              </div>
              
              <div class="space-y-2 text-sm">
                <div class="flex items-center text-gray-600">
                  <i class="fas fa-store w-4 text-green-500"></i>
                  <span class="ml-2">Pick-up: Organic Vegetables Co.</span>
                </div>
                <div class="flex items-center text-gray-600">
                  <i class="fas fa-home w-4 text-blue-500"></i>
                  <span class="ml-2">Drop-off: 456 Oak Ave, Mandaluyong</span>
                </div>
              </div>
              
              <div class="flex gap-2 mt-3">
                <button class="flex-1 bg-green-600 text-white px-3 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                  <i class="fas fa-check mr-1"></i> Accept
                </button>
                <button class="flex-1 border border-gray-300 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium">
                  <i class="fas fa-times mr-1"></i> Decline
                </button>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- Active Orders & Quick Actions -->
      <div class="space-y-6">
        
        <!-- Active Orders -->
        <div class="bg-white rounded-lg shadow-sm">
          <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold flex items-center">
              <i class="fas fa-motorcycle text-blue-600 mr-2"></i>
              Active Deliveries
            </h3>
          </div>
          <div class="p-4 space-y-3">
            
            <!-- Active Order 1 -->
            <div class="border-l-4 border-blue-500 pl-3 py-2">
              <div class="flex justify-between items-start">
                <div>
                  <h4 class="font-medium text-gray-800">ORD-2024-998</h4>
                  <p class="text-sm text-gray-500">En Route to Pick-up</p>
                </div>
                <span class="status-badge bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Active</span>
              </div>
              <div class="mt-2">
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div class="bg-blue-600 h-2 rounded-full" style="width: 25%"></div>
                </div>
              </div>
            </div>

            <!-- Active Order 2 -->
            <div class="border-l-4 border-green-500 pl-3 py-2">
              <div class="flex justify-between items-start">
                <div>
                  <h4 class="font-medium text-gray-800">ORD-2024-997</h4>
                  <p class="text-sm text-gray-500">Picked Up - En Route</p>
                </div>
                <span class="status-badge bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">In Transit</span>
              </div>
              <div class="mt-2">
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div class="bg-green-600 h-2 rounded-full" style="width: 75%"></div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm">
          <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold">Quick Actions</h3>
          </div>
          <div class="p-4 space-y-2">
            <button class="w-full bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 transition-colors font-medium">
              <i class="fas fa-map-marked-alt mr-2"></i> View Map
            </button>
            <button class="w-full border border-gray-300 text-gray-700 px-4 py-3 rounded-lg hover:bg-gray-50 transition-colors font-medium">
              <i class="fas fa-history mr-2"></i> Order History
            </button>
            <button class="w-full border border-gray-300 text-gray-700 px-4 py-3 rounded-lg hover:bg-gray-50 transition-colors font-medium">
              <i class="fas fa-headset mr-2"></i> Support
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- Earnings Overview -->
    <div class="bg-white rounded-lg shadow-sm">
      <div class="p-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold">Today's Earnings Overview</h3>
      </div>
      <div class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="text-center p-4 bg-green-50 rounded-lg">
            <p class="text-sm text-gray-600 mb-1">Completed Deliveries</p>
            <p class="text-2xl font-bold text-green-600">8</p>
          </div>
          <div class="text-center p-4 bg-blue-50 rounded-lg">
            <p class="text-sm text-gray-600 mb-1">Total Distance</p>
            <p class="text-2xl font-bold text-blue-600">24.5 km</p>
          </div>
          <div class="text-center p-4 bg-purple-50 rounded-lg">
            <p class="text-sm text-gray-600 mb-1">Average per Delivery</p>
            <p class="text-2xl font-bold text-purple-600">₱156</p>
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
// Auto-refresh available orders every 30 seconds
setInterval(() => {
  // In a real implementation, this would fetch new orders from the server
  console.log('Checking for new orders...');
}, 30000);

// Handle order acceptance
document.querySelectorAll('.order-card button').forEach(button => {
  button.addEventListener('click', function() {
    if (this.textContent.includes('Accept')) {
      // Show confirmation
      if (confirm('Are you sure you want to accept this order?')) {
        // In a real implementation, this would send the acceptance to the server
        alert('Order accepted! Redirecting to order details...');
        // window.location.href = 'riderorderdetails.php?id=ORDER_ID';
      }
    }
  });
});
</script>

</body>
</html>
