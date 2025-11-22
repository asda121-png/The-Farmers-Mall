 document.addEventListener('DOMContentLoaded', () => {
  const productsTableBody = document.getElementById('productsTableBody');
  const sellerProducts = JSON.parse(localStorage.getItem('sellerProducts')) || [];
  const adminRetailers = JSON.parse(localStorage.getItem('adminRetailers')) || [];

  productsTableBody.innerHTML = '';
  sellerProducts.forEach((product, index) => {
   // Assign a retailer to each product for demonstration
   const retailer = adminRetailers[index % adminRetailers.length] || { storeName: 'Unknown' };
   const tr = document.createElement('tr');
   tr.className = 'hover:bg-gray-50';
   tr.innerHTML = `
    <td class="p-4 flex items-center gap-3"><img src="${product.img}" class="w-8 h-8 object-cover rounded">${product.name}</td>
    <td class="p-4">${retailer.storeName}</td>
    <td class="p-4">${product.category}</td>
    <td class="p-4">₱${product.price.toFixed(2)}</td>
    <td class="p-4">${product.stock}</td>
    <td class="p-4"><button class="text-red-600 hover:underline">Remove</button></td>
   `;
   productsTableBody.appendChild(tr);
  });

 // Logout Modal Logic
  const logoutButton = document.getElementById('logoutButton');
  const logoutModal = document.getElementById('logoutModal');
  const cancelLogoutBtn = document.getElementById('cancelLogout');
  logoutButton.addEventListener('click', (e) => { e.preventDefault(); logoutModal.classList.remove('hidden'); });
  cancelLogoutBtn.addEventListener('click', () => logoutModal.classList.add('hidden'));
  logoutModal.addEventListener('click', (e) => { if (e.target === logoutModal) logoutModal.classList.add('hidden'); });
});