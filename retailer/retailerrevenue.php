<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Seller Revenue – Farmers Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

  <!-- Header -->
   <?php
// Include the header
include '../retailer/retailerheader.php';
?>
  <!-- MAIN CONTENT -->
  <main class="flex-grow">
    <div class="max-w-6xl mx-auto px-6 py-8">
      <!-- Back & Title -->
      <div class="flex items-center space-x-3 mb-6">
        <button onclick="window.location.href='retailerdashboard.php'" class="text-gray-600 hover:text-black">
          <i class="fa-solid fa-arrow-left text-lg"></i>
        </button>
        <h2 class="text-lg font-semibold">Revenue Analytics</h2>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white shadow-sm rounded-lg p-5">
          <p class="text-gray-500 text-sm">Total Revenue</p>
          <h3 id="totalRevenue" class="text-xl font-semibold mt-1">₱0.00</h3>
        </div>
        <div class="bg-white shadow-sm rounded-lg p-5">
          <p class="text-gray-500 text-sm">Total Orders</p>
          <h3 id="totalOrders" class="text-xl font-semibold mt-1">0</h3>
        </div>
        <div class="bg-white shadow-sm rounded-lg p-5">
          <p class="text-gray-500 text-sm">Avg. Order Value</p>
          <h3 id="avgOrderValue" class="text-xl font-semibold mt-1">₱0.00</h3>
        </div>
        <div class="bg-white shadow-sm rounded-lg p-5">
          <p class="text-gray-500 text-sm">Revenue This Month</p>
          <h3 id="monthlyRevenue" class="text-xl font-semibold mt-1">₱0.00</h3>
        </div>
      </div>

      <!-- Chart -->
      <div class="bg-white shadow-sm rounded-lg p-5">
        <h3 class="font-semibold text-lg mb-4">Revenue Over Time</h3>
        <canvas id="revenueChart"></canvas>
      </div>

    </div>
  </main>

  <!-- Footer -->
  <footer class="text-white py-12 mt-12" style="background-color: #1B5E20;">
    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-4 gap-8">
      <div><h3 class="font-bold text-lg mb-3">Farmers Mall</h3><p class="text-gray-300 text-sm">Fresh, organic produce delivered straight to your home from local farmers.</p></div>
      <div><h3 class="font-bold text-lg mb-3">Quick Links</h3><ul class="space-y-2 text-sm text-gray-300"><li><a href="#" class="hover:underline">About Us</a></li><li><a href="#" class="hover:underline">Contact</a></li><li><a href="#" class="hover:underline">FAQ</a></li><li><a href="#" class="hover:underline">Support</a></li></ul></div>
      <div><h3 class="font-bold text-lg mb-3">Categories</h3><ul class="space-y-2 text-sm text-gray-300"><li><a href="#" class="hover:underline">Vegetables</a></li><li><a href="#" class="hover:underline">Fruits</a></li><li><a href="#" class="hover:underline">Dairy</a></li><li><a href="#" class="hover:underline">Meat</a></li></ul></div>
      <div><h3 class="font-bold text-lg mb-3">Follow Us</h3><div class="flex space-x-4 text-xl"><a href="#"><i class="fab fa-facebook"></i></a><a href="#"><i class="fab fa-twitter"></i></a><a href="#"><i class="fab fa-instagram"></i></a></div></div>
    </div>
    <div class="border-t border-green-800 text-center text-gray-400 text-sm mt-10 pt-6">© 2025 Farmers Mall. All rights reserved.</div>
  </footer>


  <script src="../js/retailerrevenue.js"></script>
  <script>    document.addEventListener('DOMContentLoaded', () => {
      const allOrders = JSON.parse(localStorage.getItem('sellerOrders')) || [];

      // --- Calculate Stats ---
      const totalRevenue = allOrders.reduce((sum, order) => sum + order.total, 0);
      const totalOrders = allOrders.length;
      const avgOrderValue = totalOrders > 0 ? totalRevenue / totalOrders : 0;

      const currentMonth = new Date().getMonth();
      const monthlyRevenue = allOrders
        .filter(order => new Date(order.timestamp).getMonth() === currentMonth)
        .reduce((sum, order) => sum + order.total, 0);

      // --- Update Stat Cards ---
      document.getElementById('totalRevenue').textContent = `₱${totalRevenue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
      document.getElementById('totalOrders').textContent = totalOrders;
      document.getElementById('avgOrderValue').textContent = `₱${avgOrderValue.toFixed(2)}`;
      document.getElementById('monthlyRevenue').textContent = `₱${monthlyRevenue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

      // --- Prepare Chart Data ---
      const chartLabels = [];
      const chartData = [];
      const salesByDay = {};

      // Get data for the last 30 days
      for (let i = 29; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        const dateString = date.toISOString().split('T')[0]; // YYYY-MM-DD
        chartLabels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
        salesByDay[dateString] = 0;
      }

      allOrders.forEach(order => {
        const orderDateString = new Date(order.timestamp).toISOString().split('T')[0];
        if (salesByDay.hasOwnProperty(orderDateString)) {
          salesByDay[orderDateString] += order.total;
        }
      });

      for (const dateString in salesByDay) {
        chartData.push(salesByDay[dateString]);
      }

      // --- Initialize Chart ---
      const ctx = document.getElementById('revenueChart').getContext('2d');
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: chartLabels,
          datasets: [{
            label: 'Revenue (₱)',
            data: chartData,
            backgroundColor: 'rgba(46, 125, 50, 0.6)',
            borderColor: '#2E7D32',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function(value) { return '₱' + value; }
              }
            },
            x: { grid: { display: false } }
          }
        }
      });
    });
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // --- Real-time Badge Update ---
      const count = localStorage.getItem('unreadNotifications');
      const badge = document.querySelector('a[href="retailernotifications.php"] .absolute');
      if (badge) {
        if (count && parseInt(count) > 0) {
          badge.textContent = count;
          badge.style.display = 'inline-flex';
        } else {
          badge.style.display = 'none';
        }
      }
    });
  </script>

</body>
</html>
