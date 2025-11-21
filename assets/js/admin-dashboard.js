    // Logout Modal Logic
    document.addEventListener('DOMContentLoaded', () => {
      // --- DYNAMIC DATA LOGIC ---

      // 1. Load Data from localStorage
      const sellerOrders = JSON.parse(localStorage.getItem('sellerOrders')) || [];
      const adminRetailers = JSON.parse(localStorage.getItem('adminRetailers')) || [];

      // 2. Calculate Stats
      // Total Revenue from all seller orders
      const totalRevenue = sellerOrders.reduce((sum, order) => sum + order.total, 0);

      // Total Users (unique customers from orders)
      const customerNames = new Set(sellerOrders.map(order => order.customerName));
      const totalUsers = customerNames.size;

      // Active Retailers (count of verified retailers)
      const activeRetailers = adminRetailers.filter(r => r.status === 'Verified').length;

      // 3. Update Stat Cards
      document.getElementById('totalRevenueStat').textContent = `₱${totalRevenue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
      document.getElementById('totalUsersStat').textContent = totalUsers.toLocaleString();
      document.getElementById('activeRetailersStat').textContent = activeRetailers.toLocaleString();
      // 'Issues Reported' is kept static as there's no data source for it yet.

      // 4. Update Recent Activity Feed
      const recentActivityContainer = document.getElementById('recentActivityContainer');
      recentActivityContainer.innerHTML = ''; // Clear static content

      // Get last 2 pending retailers
      const recentRetailers = adminRetailers.filter(r => r.status === 'Pending').slice(0, 2);
      recentRetailers.forEach(retailer => {
        const activityDiv = document.createElement('div');
        activityDiv.className = 'flex items-start gap-3';
        activityDiv.innerHTML = `
          <div class="bg-yellow-100 text-yellow-600 p-2 rounded-full text-xs"><i class="fa-solid fa-store"></i></div>
          <div>
            <p>New retailer application from <span class="font-medium">${retailer.storeName}</span>.</p>
            <p class="text-xs text-gray-400">${new Date(retailer.joinedDate).toLocaleDateString()}</p>
          </div>
        `;
        recentActivityContainer.appendChild(activityDiv);
      });

      // Get last 2 orders
      const recentOrders = sellerOrders.slice(0, 2);
      recentOrders.forEach(order => {
        const activityDiv = document.createElement('div');
        activityDiv.className = 'flex items-start gap-3';
        activityDiv.innerHTML = `
          <div class="bg-blue-100 text-blue-600 p-2 rounded-full text-xs"><i class="fa-solid fa-receipt"></i></div>
          <div>
            <p>Order <span class="font-medium">${order.id}</span> was placed by ${order.customerName}.</p>
            <p class="text-xs text-gray-400">${new Date(order.timestamp).toLocaleDateString()}</p>
          </div>
        `;
        recentActivityContainer.appendChild(activityDiv);
      });

      if (recentActivityContainer.innerHTML === '') {
        recentActivityContainer.innerHTML = '<p class="text-gray-500">No recent activity.</p>';
      }

      // 5. Update Chart with Dynamic Data
      const monthlyRevenue = {};
      const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
      const currentMonth = new Date().getMonth();
      
      for (let i = 5; i >= 0; i--) {
        const monthIndex = (currentMonth - i + 12) % 12;
        const year = new Date().getFullYear() - (currentMonth < i ? 1 : 0);
        const monthLabel = `${monthNames[monthIndex]} ${year}`;
        monthlyRevenue[monthLabel] = 0;
      }

      sellerOrders.forEach(order => {
        const orderDate = new Date(order.timestamp);
        const monthLabel = `${monthNames[orderDate.getMonth()]} ${orderDate.getFullYear()}`;
        if (monthlyRevenue.hasOwnProperty(monthLabel)) {
          monthlyRevenue[monthLabel] += order.total;
        }
      });

      new Chart(document.getElementById('adminChart'), {
        type: 'line',
        data: {
          labels: Object.keys(monthlyRevenue),
          datasets: [{ label: 'Revenue (₱)', data: Object.values(monthlyRevenue), borderColor: '#16a34a', backgroundColor: 'rgba(22, 163, 74, 0.1)', tension: 0.3, fill: true }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
      });

      // --- LOGOUT MODAL LOGIC ---
      const logoutButton = document.getElementById('logoutButton');
      const logoutModal = document.getElementById('logoutModal');
      const cancelLogoutBtn = document.getElementById('cancelLogout');
  
      if (logoutButton && logoutModal && cancelLogoutBtn) {
        logoutButton.addEventListener('click', (e) => {
          e.preventDefault(); // Prevent the link from navigating immediately
          logoutModal.classList.remove('hidden');
        });
  
        cancelLogoutBtn.addEventListener('click', () => {
          logoutModal.classList.add('hidden');
        });
  
        // Also close modal if user clicks on the background overlay
        logoutModal.addEventListener('click', (e) => {
          if (e.target === logoutModal) {
            logoutModal.classList.add('hidden');
          }
        });
      }
    });