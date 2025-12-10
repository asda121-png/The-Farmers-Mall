document.addEventListener('DOMContentLoaded', () => {
  // --- PAYMENT METHOD TOGGLE ---
  const paymentRadios = document.querySelectorAll('input[name="payment"]');
  const cardInfo = document.getElementById('card-info');

  paymentRadios.forEach(radio => {
    radio.addEventListener('change', () => {
      cardInfo.classList.toggle('invisible-placeholder', radio.value !== 'card');
    });
  });

  // --- USE PHP-RENDERED DATA (NO RECALCULATION) ---
  const orderItemsContainer = document.getElementById('orderItems');
  const subtotalEl = document.getElementById('subtotal');
  const taxEl = document.getElementById('tax');
  const totalEl = document.getElementById('total');

  // Use data passed from PHP
  const { cart, subtotal, tax, total } = window.paymentData || { cart: [], subtotal: 0, tax: 0, total: 0 };

  // Optional: Re-render items if needed (e.g. after filtering)
  const renderOrderItems = () => {
    orderItemsContainer.innerHTML = '';

    if (cart.length === 0) {
      orderItemsContainer.innerHTML = '<p class="text-sm text-gray-500">No items in cart.</p>';
      return;
    }

    cart.forEach(item => {
      const imgSrc = item.image 
        ? (item.image.startsWith('http') ? item.image : '../' + item.image.replace(/^\/+/, ''))
        : 'https://via.placeholder.com/100x100?text=No+Image';

      const itemDiv = document.createElement('div');
      itemDiv.className = 'flex gap-3';
      itemDiv.innerHTML = `
        <img src="${imgSrc}" class="w-12 h-12 rounded object-cover border bg-gray-50">
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-800 truncate">${item.name}</p>
          <p class="text-xs text-gray-500">Qty: ${item.quantity}</p>
        </div>
        <div class="text-sm font-semibold text-gray-700">₱${parseFloat(item.subtotal).toFixed(2)}</div>
      `;
      orderItemsContainer.appendChild(itemDiv);
    });
  };

  // Render items (in case JS needs to re-render)
  renderOrderItems();

  // DO NOT recalculate totals — trust PHP
  // Only update if you allow dynamic changes (not needed here)

  // --- HANDLE PLACING THE ORDER ---
  document.getElementById('placeOrderBtn').addEventListener('click', async () => {
    if (cart.length === 0) {
      alert("Your cart is empty. Cannot place order.");
      return;
    }

    const placeOrderBtn = document.getElementById('placeOrderBtn');
    placeOrderBtn.disabled = true;
    placeOrderBtn.textContent = 'Processing...';

    try {
      const selectedPayment = document.querySelector('input[name="payment"]:checked');
      const paymentMethod = selectedPayment ? selectedPayment.value : 'card';

      const response = await fetch('../api/order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'place_order',
          payment_method: paymentMethod
        })
      });

      const result = await response.json();

      if (result.success) {
        // Save notification
        const newNotification = {
          id: Date.now(),
          type: 'order_success',
          title: 'Order Placed Successfully!',
          message: `Your order #${result.order_id} has been confirmed.`,
          time: new Date().toISOString(),
          read: false,
          link: `ordersuccessfull.php?order_id=${result.order_id}`
        };
        const notifications = JSON.parse(localStorage.getItem('userNotifications')) || [];
        notifications.unshift(newNotification);
        localStorage.setItem('userNotifications', JSON.stringify(notifications));

        // Clear cart
        localStorage.removeItem('cart');
        sessionStorage.removeItem('selectedCartItems');

        // Redirect
        window.location.href = `ordersuccessfull.php?order_id=${result.order_id}`;
      } else {
        alert('Error: ' + (result.message || 'Failed to place order'));
        placeOrderBtn.disabled = false;
        placeOrderBtn.textContent = 'Place Order';
      }
    } catch (error) {
      console.error('Order error:', error);
      alert('Network error. Please try again.');
      placeOrderBtn.disabled = false;
      placeOrderBtn.textContent = 'Place Order';
    }
  });
});