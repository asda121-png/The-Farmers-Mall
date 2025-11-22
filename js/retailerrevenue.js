document.addEventListener('DOMContentLoaded', () => {
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
      callback: function (value) { return '₱' + value; }
     }
    },
    x: { grid: { display: false } }
   }
  }
 });
});

  <script src="js/retailerpending.js"></script>

      const pendingOrders = allOrders.filter(order => order.status === 'Pending');

      pendingOrdersTableBody.innerHTML = '';

      if (pendingOrders.length === 0) {
        pendingOrdersTableBody.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-gray-500">No pending orders. Great job!</td></tr>';
        return;
      }

      pendingOrders.forEach(order => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50 cursor-pointer';
        tr.onclick = () => {
          localStorage.setItem('selectedSellerOrder', JSON.stringify(order));
          window.location.href = `retailerorderdetails.html?orderId=${order.id}`;
        };
  
        const statusClass = 'bg-orange-100 text-orange-700';

        tr.innerHTML = `
          <td class="px-6 py-4 flex items-center space-x-3">
            <img src="https://randomuser.me/api/portraits/men/${Math.floor(Math.random() * 20)}.jpg" class="w-8 h-8 rounded-full" alt="">
            <div>
              <p class="font-medium">${order.customerName}</p>
            </div>
          </td>
          <td class="px-6 py-4">${order.id}</td>
          <td class="px-6 py-4 font-medium">₱${order.total.toFixed(2)}</td>
          <td class="px-6 py-4">${new Date(order.timestamp).toLocaleDateString()}</td>
          <td class="px-6 py-4 text-right">
            <a href="retailerorderdetails.html?orderId=${order.id}" onclick="event.stopPropagation(); localStorage.setItem('selectedSellerOrder', JSON.stringify(order))" class="text-green-600 hover:underline text-sm">View Details</a>
          </td>
        `;
        pendingOrdersTableBody.appendChild(tr);
      });
document.addEventListener('DOMContentLoaded', () => {
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
