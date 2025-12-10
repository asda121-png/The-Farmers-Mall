
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Seller Customers – Farmers Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
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
    <div class="max-w-6xl mx-auto px-6 py-8 min-h-[92vh]">
      <!-- Back & Title -->
      <div class="flex items-center space-x-3 mb-6">
        <button onclick="window.location.href='retailerdashboard.php'" class="text-gray-600 hover:text-black">
          <i class="fa-solid fa-arrow-left text-lg"></i>
        </button>
        <h2 class="text-lg font-semibold">My Customers</h2>
      </div>

      <!-- Controls -->
      <div class="bg-white rounded-lg shadow-sm p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div id="customerCount" class="text-gray-600 text-sm">Showing 0 of 0 customers</div>
        <div class="flex items-center gap-2">
          <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input id="searchInput" type="text" placeholder="Search customers..." class="border rounded-md pl-9 pr-3 py-1.5 w-full md:w-56 text-sm focus:outline-none focus:ring-2 focus:ring-green-600">
          </div>
          <button id="sortBtn" class="border px-3 py-1.5 rounded-md text-gray-700 text-sm flex items-center gap-1 hover:bg-gray-100">
            <i class="fa-solid fa-sort"></i> Sort
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="mt-6 bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full text-sm text-gray-700">
          <thead class="border-b bg-gray-50 text-left text-xs uppercase text-gray-500">
            <tr>
              <th class="px-6 py-3 font-medium">Customer</th>
              <th class="px-6 py-3 font-medium">Last Order</th>
              <th class="px-6 py-3 font-medium">Total Orders</th>
              <th class="px-6 py-3 font-medium">Total Spent</th>
              <th class="px-6 py-3 font-medium text-right">Action</th>
            </tr>
          </thead>
          <tbody id="customersTableBody" class="divide-y"></tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div id="paginationContainer" class="flex justify-center items-center gap-2 mt-6"></div>
    </div>
  </main>

  <!-- Sort Modal -->
  <div id="sortModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm">
      <h3 class="font-semibold text-lg mb-4">Sort Customers By</h3>
      <div class="space-y-2">
        <label class="flex items-center"><input type="radio" name="sort" value="name-asc" class="accent-green-600 mr-2" checked> Name (A-Z)</label>
        <label class="flex items-center"><input type="radio" name="sort" value="name-desc" class="accent-green-600 mr-2"> Name (Z-A)</label>
        <label class="flex items-center"><input type="radio" name="sort" value="spent-desc" class="accent-green-600 mr-2"> Total Spent (High to Low)</label>
        <label class="flex items-center"><input type="radio" name="sort" value="spent-asc" class="accent-green-600 mr-2"> Total Spent (Low to High)</label>
        <label class="flex items-center"><input type="radio" name="sort" value="orders-desc" class="accent-green-600 mr-2"> Total Orders (High to Low)</label>
      </div>
      <div class="mt-6 flex justify-end gap-3">
        <button id="closeSortModal" class="px-4 py-2 border rounded-md text-sm">Cancel</button>
        <button id="applySort" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm">Apply</button>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const customersTableBody = document.getElementById('customersTableBody');
      const customerCountEl = document.getElementById('customerCount');
      const paginationContainer = document.getElementById('paginationContainer');
      const searchInput = document.getElementById('searchInput');

      // Modal elements
      const sortBtn = document.getElementById('sortBtn');
      const sortModal = document.getElementById('sortModal');
      const closeSortModal = document.getElementById('closeSortModal');
      const applySort = document.getElementById('applySort');

      // Dynamically generate customers from orders
      const generateCustomersFromOrders = () => {
        const orders = JSON.parse(localStorage.getItem('sellerOrders')) || [];
        if (orders.length === 0) return [];

        const customerData = orders.reduce((acc, order) => {
          const name = order.customerName;
          if (!acc[name]) {
            acc[name] = {
              id: name.replace(/\s+/g, '-').toLowerCase(), // Create a unique ID from name
              name: name,
              totalOrders: 0,
              totalSpent: 0,
              lastOrder: new Date(0), // Initialize with a very old date
              avatar: (Math.random() > 0.5 ? 'men' : 'women') + `/${Math.floor(Math.random() * 50)}.jpg`
            };
          }

          acc[name].totalOrders += 1;
          acc[name].totalSpent += order.total;
          const orderDate = new Date(order.timestamp);
          if (orderDate > acc[name].lastOrder) {
            acc[name].lastOrder = orderDate;
          }
          return acc;
        }, {});

        return Object.values(customerData);
      };

      const allCustomers = generateCustomersFromOrders();

      let currentCustomers = [...allCustomers];
      let currentPage = 1;
      const itemsPerPage = 5;

      const renderCustomers = () => {
        customersTableBody.innerHTML = '';
        
        const startIndex = (currentPage - 1) * itemsPerPage;
        const paginatedCustomers = currentCustomers.slice(startIndex, startIndex + itemsPerPage);

        if (currentCustomers.length === 0) {
          customersTableBody.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-gray-500">No customers found.</td></tr>';
          customerCountEl.textContent = 'Showing 0 of 0 customers';
          renderPagination();
          return;
        }

        paginatedCustomers.forEach(customer => {
          const tr = document.createElement('tr');
          tr.className = 'hover:bg-gray-50';
          tr.innerHTML = `
            <td class="px-6 py-4 flex items-center space-x-3">
              <img src="https://randomuser.me/api/portraits/${customer.avatar}" class="w-8 h-8 rounded-full" alt="">
              <p class="font-medium">${customer.name}</p>
            </td>
            <td class="px-6 py-4">${new Date(customer.lastOrder).toLocaleDateString()}</td>
            <td class="px-6 py-4">${customer.totalOrders}</td>
            <td class="px-6 py-4 font-medium">₱${customer.totalSpent.toFixed(2)}</td>
            <td class="px-6 py-4 text-right">
              <a href="retailermessage.php" class="text-green-600 hover:underline text-sm">Message</a>
            </td>
          `;
          customersTableBody.appendChild(tr);
        });

        customerCountEl.textContent = `Showing ${paginatedCustomers.length} of ${currentCustomers.length} customers`;
        renderPagination();
      };

      const renderPagination = () => {
        paginationContainer.innerHTML = '';
        const totalPages = Math.ceil(currentCustomers.length / itemsPerPage);
        if (totalPages <= 1) return;

        for (let i = 1; i <= totalPages; i++) {
          const pageBtn = document.createElement('button');
          pageBtn.textContent = i;
          pageBtn.className = `border w-8 h-8 rounded-md text-sm ${i === currentPage ? 'bg-green-600 text-white' : 'text-gray-600 hover:bg-gray-100'}`;
          pageBtn.onclick = () => { currentPage = i; renderCustomers(); };
          paginationContainer.appendChild(pageBtn);
        }
      };

      const filterAndSort = () => {
        const searchTerm = searchInput.value.toLowerCase();
        currentCustomers = allCustomers.filter(c => c.name.toLowerCase().includes(searchTerm));
        
        const sortValue = document.querySelector('input[name="sort"]:checked').value;
        sortCustomers(sortValue, false); // false to prevent re-filtering

        currentPage = 1;
        renderCustomers();
      };

      const sortCustomers = (sortValue, shouldRender = true) => {
        switch (sortValue) {
          case 'name-asc': currentCustomers.sort((a, b) => a.name.localeCompare(b.name)); break;
          case 'name-desc': currentCustomers.sort((a, b) => b.name.localeCompare(a.name)); break;
          case 'spent-desc': currentCustomers.sort((a, b) => b.totalSpent - a.totalSpent); break;
          case 'spent-asc': currentCustomers.sort((a, b) => a.totalSpent - b.totalSpent); break;
          case 'orders-desc': currentCustomers.sort((a, b) => b.totalOrders - a.totalOrders); break;
        }
        if (shouldRender) {
          currentPage = 1;
          renderCustomers();
        }
      };

      // Event Listeners
      searchInput.addEventListener('input', filterAndSort);

      sortBtn.addEventListener('click', () => sortModal.classList.remove('hidden'));
      closeSortModal.addEventListener('click', () => sortModal.classList.add('hidden'));

      applySort.addEventListener('click', () => {
        const sortValue = document.querySelector('input[name="sort"]:checked').value;
        sortCustomers(sortValue);
        sortModal.classList.add('hidden');
      });

      window.addEventListener('click', (e) => {
        if (e.target === sortModal) sortModal.classList.add('hidden');
      });

      // Initial render
      renderCustomers();
    });
  </script>

</body>
</html>
