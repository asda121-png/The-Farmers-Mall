document.addEventListener('DOMContentLoaded', () => {
  // --- PAYMENT METHOD TOGGLE ---
  const paymentRadios = document.querySelectorAll('input[name="payment"]');
  const cardInfo = document.getElementById('card-info');

  paymentRadios.forEach(radio => {
    radio.addEventListener('change', () => {
      cardInfo.style.display = (radio.value === 'card') ? 'block' : 'none';
    });
  });

  // --- LOAD ORDER SUMMARY FROM CART ---
  const orderItemsContainer = document.getElementById('orderItems');
  const subtotalEl = document.getElementById('subtotal');
  const totalEl = document.getElementById('total');
  const cart = JSON.parse(localStorage.getItem('cart')) || [];

  orderItemsContainer.innerHTML = '';

  if (cart.length === 0) {
    orderItemsContainer.innerHTML = '<p class="text-sm text-gray-500">No items in cart.</p>';
  } else {
    cart.forEach(item => {
      const itemTotal = (item.price * (item.quantity || 1)).toFixed(2);
      const itemDiv = document.createElement('div');
      itemDiv.className = 'flex items-center justify-between';
      itemDiv.innerHTML = `
        <div class="flex items-center gap-3">
          <img src="${item.image || 'images/products/img.png'}" class="w-12 h-12 rounded-lg object-cover">
          <div>
            <span class="text-sm font-medium">${item.name}</span>
            <p class="text-xs text-gray-500">Qty: ${item.quantity || 1}</p>
          </div>
        </div>
        <span class="text-sm text-gray-700">₱${itemTotal}</span>
      `;
      orderItemsContainer.appendChild(itemDiv);
    });
  }

  // Update totals
  const subtotal = cart.reduce((sum, item) => sum + item.price * (item.quantity || 1), 0);
  subtotalEl.textContent = `₱${subtotal.toFixed(2)}`;
  totalEl.textContent = `₱${subtotal.toFixed(2)}`;

  // --- HANDLE PLACING THE ORDER ---
  document.getElementById('placeOrderBtn').addEventListener('click', () => {
    if (cart.length === 0) {
      alert("Your cart is empty. Cannot place order.");
      return;
    }

    const total = cart.reduce((sum, item) => sum + item.price * (item.quantity || 1), 0);
    const orderId = `FM-${Date.now().toString().slice(-6)}`;

    // Create a notification for the new order
    const newNotification = {
      id: Date.now(),
      type: 'order_placed',
      title: `Order #${orderId} Placed Successfully!`,
      message: `Your order for ${cart.length} item(s) with a total of ₱${total.toFixed(2)} has been confirmed.`,
      timestamp: new Date().toISOString(),
      read: false
    };

    // Save notification to localStorage
    let notifications = JSON.parse(localStorage.getItem('notifications')) || [];
    notifications.unshift(newNotification); // Add to the top
    localStorage.setItem('notifications', JSON.stringify(notifications));
    
    // Create and save the order for the seller dashboard
    const newSellerOrder = {
      id: orderId,
      customerName: 'Piodos De Blanco', // Assuming a logged-in user
      total: total,
      status: 'Pending',
      timestamp: new Date().toISOString()
    };

    let sellerOrders = JSON.parse(localStorage.getItem('sellerOrders')) || [];
    sellerOrders.unshift(newSellerOrder);
    // Keep only the last 10 orders for the dashboard view
    if (sellerOrders.length > 10) sellerOrders.pop();
    localStorage.setItem('sellerOrders', JSON.stringify(sellerOrders));

    // Clear the cart
    localStorage.removeItem('cart');

    // Redirect to success page
    window.location.href = 'ordersuccessfull.php';
  });
});