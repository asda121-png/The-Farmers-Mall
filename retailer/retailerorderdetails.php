<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Order Details – The Farmer’s Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

   <?php
// Include the header
include '../retailer/retailerheader.php';
?>

  <!-- Main Content -->
  <main class="flex-grow">
    <div class="max-w-4xl mx-auto px-6 py-8 mb-80">
      <!-- Back & Title -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-3">
          <button onclick="window.location.href='retailerorders.php'" class="text-gray-600 hover:text-black">
            <i class="fa-solid fa-arrow-left text-lg"></i>
          </button>
          <div>
            <h2 class="text-lg font-semibold">Order Details</h2>
            <p id="orderIdDate" class="text-sm text-gray-500"></p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button class="border px-3 py-1.5 rounded-md text-gray-700 text-sm flex items-center gap-1 hover:bg-gray-100">
            <i class="fa-solid fa-print"></i> Print
          </button>
          <div class="relative">
            <button id="actionBtn" class="bg-green-600 text-white px-4 py-1.5 rounded-md text-sm hover:bg-green-700 flex items-center gap-1">
              Action <i class="fa-solid fa-chevron-down text-xs"></i>
            </button>
            <div id="actionDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border hidden z-10">
              <a href="#" data-status="Pending" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mark as Pending</a>
              <a href="#" data-status="Shipped" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mark as Shipped</a>
              <a href="#" data-status="Delivered" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mark as Delivered</a>
              <a href="#" data-status="Cancelled" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Cancel Order</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Details Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Items & Summary -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm p-6 space-y-6">
          <div>
            <h3 class="font-semibold mb-2">Items in Order</h3>
            <div id="orderItemsContainer" class="space-y-3 divide-y">
              <!-- Items will be loaded here -->
            </div>
          </div>
          <div class="border-t pt-4 text-right space-y-2">
            <p class="flex justify-end gap-4"><span>Subtotal:</span> <span id="subtotal" class="font-medium">₱0.00</span></p>
            <p class="flex justify-end gap-4"><span>Shipping:</span> <span class="font-medium">₱0.00</span></p>
            <p class="flex justify-end gap-4 text-lg"><strong>Total:</strong> <strong id="total">₱0.00</strong></p>
          </div>
        </div>

        <!-- Right: Customer & Shipping -->
        <div class="space-y-6">
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="font-semibold mb-3">Customer</h3>
            <div class="flex items-center space-x-3">
              <img id="customerAvatar" src="" class="w-10 h-10 rounded-full" alt="">
              <div>
                <p id="customerName" class="font-medium"></p>
                <p id="customerEmail" class="text-sm text-gray-500 break-all"></p>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="font-semibold mb-3">Shipping Address</h3>
            <address id="shippingAddress" class="not-italic text-sm text-gray-600 leading-relaxed">
              <!-- Address will be loaded here -->
            </address>
          </div>
        </div>
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

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const order = JSON.parse(localStorage.getItem('selectedSellerOrder'));
      const cart = JSON.parse(localStorage.getItem('cart')) || []; // Assuming cart items are relevant

      if (!order) {
        document.querySelector('main').innerHTML = '<p class="text-center py-20">Order not found. Please go back to the orders list.</p>';
        return;
      }

      // Populate header
      document.getElementById('orderIdDate').textContent = `Order ${order.id} · ${new Date(order.timestamp).toLocaleDateString()}`;

      // Populate customer and shipping
      document.getElementById('customerName').textContent = order.customerName;
      document.getElementById('customerEmail').textContent = `${order.customerName.replace(/\s/g, '.').toLowerCase()}@example.com`;
      document.getElementById('customerAvatar').src = `https://randomuser.me/api/portraits/men/${Math.floor(Math.random() * 10)}.jpg`;
      document.getElementById('shippingAddress').innerHTML = `
        ${order.customerName}<br>
        123 Farmer's Lane<br>
        Baguio City, Benguet, 2600<br>
        Philippines
      `;

      // Populate items (using cart as a placeholder for order items)
      const itemsContainer = document.getElementById('orderItemsContainer');
      itemsContainer.innerHTML = '';
      // In a real app, you'd fetch order items. We'll simulate with cart items.
      const itemsInOrder = JSON.parse(localStorage.getItem('lastOrderedCart')) || cart.slice(0,2); // Placeholder
      itemsInOrder.forEach(item => {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'flex justify-between items-center pt-3';
        itemDiv.innerHTML = `
          <div class="flex items-center gap-3">
            <img src="${item.image}" class="w-12 h-12 rounded-lg object-cover">
            <div>
              <p class="font-medium">${item.name}</p>
              <p class="text-sm text-gray-500">Qty: ${item.quantity}</p>
            </div>
          </div>
          <p class="font-medium">₱${(item.price * item.quantity).toFixed(2)}</p>
        `;
        itemsContainer.appendChild(itemDiv);
      });

      // Populate totals
      document.getElementById('subtotal').textContent = `₱${order.total.toFixed(2)}`;
      document.getElementById('total').textContent = `₱${order.total.toFixed(2)}`;

      // Action button logic
      const actionBtn = document.getElementById('actionBtn');
      const actionDropdown = document.getElementById('actionDropdown');
      actionBtn.addEventListener('click', () => {
        actionDropdown.classList.toggle('hidden');
      });

      actionDropdown.addEventListener('click', (e) => {
        e.preventDefault();
        const newStatus = e.target.dataset.status;
        if (newStatus) {
          // Update status in localStorage
          let sellerOrders = JSON.parse(localStorage.getItem('sellerOrders')) || [];
          sellerOrders = sellerOrders.map(o => {
            if (o.id === order.id) {
              return { ...o, status: newStatus };
            }
            return o;
          });
          localStorage.setItem('sellerOrders', JSON.stringify(sellerOrders));
          
          // Optionally, show a confirmation and refresh
          alert(`Order status updated to ${newStatus}`);
          actionDropdown.classList.add('hidden');
          // You might want to visually update the status on the page as well
        }
      });

      // Hide dropdown on outside click
      window.addEventListener('click', (e) => {
        if (!actionBtn.contains(e.target) && !actionDropdown.contains(e.target)) {
          actionDropdown.classList.add('hidden');
        }
      });
    });
  </script>
</body>
</html>
