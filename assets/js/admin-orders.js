 document.addEventListener('DOMContentLoaded', () => {
  const ordersTableBody = document.getElementById('ordersTableBody');
  const searchInput = document.getElementById('searchInput');
  const filterTabs = document.getElementById('filterTabs');
  const paginationContainer = document.getElementById('paginationContainer');

  const sellerOrders = JSON.parse(localStorage.getItem('sellerOrders')) || [];
  const adminRetailers = JSON.parse(localStorage.getItem('adminRetailers')) || [];

  let currentPage = 1;
  const itemsPerPage = 10;

  const getStatusBadge = (status) => {
   const classes = { 'Delivered': 'bg-green-100 text-green-700', 'Pending': 'bg-yellow-100 text-yellow-700', 'Shipped': 'bg-blue-100 text-blue-700', 'Cancelled': 'bg-red-100 text-red-700' };
   return `<span class="${classes[status] || 'bg-gray-100 text-gray-700'} px-2 py-1 rounded-full text-xs font-medium">${status}</span>`;
  };

  const renderOrders = () => {
   const searchTerm = searchInput.value.toLowerCase();
   const activeFilter = document.querySelector('.filter-tab.bg-white').dataset.filter;

   const filteredOrders = sellerOrders.filter(order => {
    const searchMatch = order.id.toLowerCase().includes(searchTerm) || order.customerName.toLowerCase().includes(searchTerm);
    const filterMatch = activeFilter === 'All' || order.status === activeFilter;
    return searchMatch && filterMatch;
   });

   ordersTableBody.innerHTML = '';
   const startIndex = (currentPage - 1) * itemsPerPage;
   const paginatedOrders = filteredOrders.slice(startIndex, startIndex + itemsPerPage);

   if (paginatedOrders.length === 0) {
    ordersTableBody.innerHTML = '<tr><td colspan="7" class="text-center p-8 text-gray-500">No orders found.</td></tr>';
    renderPagination(0);
    return;
   }

   paginatedOrders.forEach((order, index) => {
    const retailer = adminRetailers[index % adminRetailers.length] || { storeName: 'Mesa Farm' };
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-gray-50';
    tr.innerHTML = `
            <td class="p-4 font-medium text-blue-600">${order.id}</td>
            <td class="p-4">${order.customerName}</td>
            <td class="p-4">${retailer.storeName}</td>
            <td class="p-4 font-medium">₱${order.total.toFixed(2)}</td>
            <td class="p-4">${getStatusBadge(order.status)}</td>
            <td class="p-4">${new Date(order.timestamp).toLocaleDateString()}</td>
            <td class="p-4 text-right"><button class="text-blue-600 hover:underline">View</button></td>
          `;
    ordersTableBody.appendChild(tr);
   });

   renderPagination(filteredOrders.length);
  };

  const renderPagination = (totalItems) => {
   paginationContainer.innerHTML = '';
   const totalPages = Math.ceil(totalItems / itemsPerPage);
   if (totalPages <= 1) return;

   for (let i = 1; i <= totalPages; i++) {
    const pageBtn = document.createElement('button');
    pageBtn.textContent = i;
    pageBtn.className = `border w-8 h-8 rounded-md text-sm ${i === currentPage ? 'bg-green-600 text-white' : 'text-gray-600 hover:bg-gray-100'}`;
    pageBtn.onclick = () => { currentPage = i; renderOrders(); };
    paginationContainer.appendChild(pageBtn);
   }
  };

  // Event Listeners
  searchInput.addEventListener('input', () => { currentPage = 1; renderOrders(); });

  filterTabs.addEventListener('click', (e) => {
   if (e.target.classList.contains('filter-tab')) {
    document.querySelectorAll('.filter-tab').forEach(tab => {
     tab.classList.remove('bg-white', 'text-green-700', 'shadow');
     tab.classList.add('text-gray-600');
    });
    e.target.classList.add('bg-white', 'text-green-700', 'shadow');
    currentPage = 1;
    renderOrders();
   }
  });

  // Logout Modal Logic
  const logoutButton = document.getElementById('logoutButton');
  const logoutModal = document.getElementById('logoutModal');
  const cancelLogoutBtn = document.getElementById('cancelLogout');
  logoutButton.addEventListener('click', (e) => { e.preventDefault(); logoutModal.classList.remove('hidden'); });
  cancelLogoutBtn.addEventListener('click', () => logoutModal.classList.add('hidden'));
  logoutModal.addEventListener('click', (e) => { if (e.target === logoutModal) logoutModal.classList.add('hidden'); });

  // Initial Render
  renderOrders();
 });