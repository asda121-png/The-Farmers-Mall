document.addEventListener('DOMContentLoaded', async () => {
  // --- PAYMENT METHOD TOGGLE ---
  const paymentRadios = document.querySelectorAll('input[name="payment"]');
  const cardInfo = document.getElementById('card-info');

  paymentRadios.forEach(radio => {
    radio.addEventListener('change', () => {
      if (radio.value === 'card') {
        cardInfo.classList.remove('invisible-placeholder');
      } else {
        cardInfo.classList.add('invisible-placeholder');
      }
    });
  });

  // --- LOAD ORDER SUMMARY FROM DATABASE ---
  const orderItemsContainer = document.getElementById('orderItems');
  const subtotalEl = document.getElementById('subtotal');
  const totalEl = document.getElementById('total');
  
  let cart = [];
  
  try {
    // Fetch cart items from database
    const response = await fetch('../api/cart.php');
    const data = await response.json();
    
    if (data.success && Array.isArray(data.items)) {
      cart = data.items;
      // Update localStorage for consistency
      localStorage.setItem('cart', JSON.stringify(cart));
    } else {
      // Fallback to localStorage if API fails
      cart = JSON.parse(localStorage.getItem('cart')) || [];
    }
  } catch (error) {
    console.error('Error fetching cart:', error);
    // Fallback to localStorage
    cart = JSON.parse(localStorage.getItem('cart')) || [];
  }

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
            <span class="text-sm font-medium">${item.name || item.product_name}</span>
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
  document.getElementById('placeOrderBtn').addEventListener('click', async () => {
    if (cart.length === 0) {
      alert("Your cart is empty. Cannot place order.");
      return;
    }

    // Disable button to prevent double submission
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    placeOrderBtn.disabled = true;
    placeOrderBtn.textContent = 'Processing...';

    try {
      // Get selected payment method
      const selectedPayment = document.querySelector('input[name="payment"]:checked');
      const paymentMethod = selectedPayment ? selectedPayment.value : 'card';

      // Place order via API
      const response = await fetch('../api/order.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          action: 'place_order',
          payment_method: paymentMethod
        })
      });

      const result = await response.json();

      if (result.success) {
        // Create a notification for the successful order
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
        notifications.unshift(newNotification); // Add to the beginning
        localStorage.setItem('userNotifications', JSON.stringify(notifications));

        // Clear localStorage cart
        localStorage.removeItem('cart');
        
        // Redirect to success page with order ID
        window.location.href = `ordersuccessfull.php?order_id=${result.order_id}`;
      } else {
        alert('Error placing order: ' + (result.message || 'Unknown error'));
        placeOrderBtn.disabled = false;
        placeOrderBtn.textContent = 'Place Order';
      }
    } catch (error) {
      console.error('Error placing order:', error);
      alert('Error placing order. Please try again.');
      placeOrderBtn.disabled = false;
      placeOrderBtn.textContent = 'Place Order';
    }
  });
});