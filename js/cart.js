 // Dynamically load header.html
    fetch('header.html')
      .then(res => res.text())
      .then(data => {
        document.getElementById('header').innerHTML = data;

        // Highlight the cart icon
        const cartIcon = document.querySelector('a[href="cart.html"] i');
        if (cartIcon) {
          cartIcon.parentElement.classList.remove('text-gray-600');
          cartIcon.parentElement.classList.add('text-green-600');
        }

        // Add search functionality to the loaded header
        const headerSearchInput = document.querySelector('#header input[type="text"]');
        if (headerSearchInput) {
          const form = document.createElement('form');
          form.action = 'products.html';
          form.method = 'GET';
          headerSearchInput.name = 'search';
          headerSearchInput.parentElement.insertBefore(form, headerSearchInput);
          form.appendChild(headerSearchInput);
        }

        // Update cart icon badge
        updateCartIcon();
      });

    // CART FUNCTIONALITY
    const cartContainer = document.getElementById('cartItems');
    const subtotalEl = document.getElementById('subtotal');
    const totalEl = document.getElementById('total');

    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    function renderCart() {
      cartContainer.innerHTML = '';
      
      // Update cart count in header
      const cartCountEl = document.getElementById('cartCount');
      if (cartCountEl) {
        cartCountEl.textContent = cart.length;
      }
      
      if(cart.length === 0){
        cartContainer.innerHTML = `
          <div class="text-center py-16 col-span-2">
            <i class="fas fa-shopping-cart text-gray-300 text-6xl mb-4"></i>
            <p class="text-gray-500 text-xl mb-2">Your cart is empty</p>
            <p class="text-gray-400 mb-6">Add some products to get started!</p>
            <a href="products.html" class="inline-block bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition shadow-md">
              <i class="fa-solid fa-shopping-bag mr-2"></i>
              Browse Products
            </a>
          </div>
        `;
        subtotalEl.textContent = '₱0.00';
        totalEl.textContent = '₱0.00';
        const itemCountEl = document.getElementById('itemCount');
        const taxEl = document.getElementById('tax');
        if (itemCountEl) itemCountEl.textContent = '0';
        if (taxEl) taxEl.textContent = '₱0.00';
        return;
      }

      cart.forEach((item, index) => {
        const itemTotal = (item.price * (item.quantity || 1)).toFixed(2);
        const div = document.createElement('div');
        div.className = 'bg-white p-4 rounded-xl shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between hover:shadow-md transition-shadow gap-4';
        div.innerHTML = `
          <div class="flex items-center gap-4 flex-1">
            <img src="${item.image || 'images/products/Fresh Vegetable Box.png'}" class="w-20 h-20 rounded-lg object-cover border border-gray-200 flex-shrink-0">
            <div class="flex-1">
              <h3 class="font-semibold text-gray-800 text-lg">${escapeHtml(item.name)}</h3>
              <p class="text-green-700 font-medium text-sm">₱${item.price.toFixed(2)} each</p>
              ${item.description ? `<p class="text-gray-500 text-xs mt-1 line-clamp-2">${escapeHtml(item.description)}</p>` : ''}
            </div>
          </div>
          <div class="flex items-center gap-4 w-full md:w-auto justify-between md:justify-start">
            <div class="flex items-center gap-3 border rounded-lg px-2 py-1 bg-gray-50">
              <button class="quantity-btn text-gray-600 hover:text-green-600 font-bold text-lg w-8 h-8 flex items-center justify-center rounded hover:bg-white transition" data-action="decrease">−</button>
              <span class="quantity text-lg font-semibold min-w-[2rem] text-center">${item.quantity || 1}</span>
              <button class="quantity-btn text-gray-600 hover:text-green-600 font-bold text-lg w-8 h-8 flex items-center justify-center rounded hover:bg-white transition" data-action="increase">+</button>
            </div>
            <p class="text-green-700 font-bold text-lg w-24 text-right item-total">₱${itemTotal}</p>
            <button class="wishlist text-gray-400 hover:text-red-500 transition-colors" title="Add to wishlist">
              <i class="far fa-heart text-xl"></i>
            </button>
            <button class="remove-item text-red-500 hover:text-red-700 text-xl transition-colors" title="Remove item">
              <i class="fa-solid fa-trash"></i>
            </button>
          </div>
        `;
        cartContainer.appendChild(div);

        // Quantity buttons
        div.querySelectorAll('.quantity-btn').forEach(btn => {
          btn.addEventListener('click', () => {
            if(!item.quantity) item.quantity = 1;
            if(btn.dataset.action === 'increase') {
              item.quantity++;
            } else if(btn.dataset.action === 'decrease' && item.quantity > 1) {
              item.quantity--;
            }
            localStorage.setItem('cart', JSON.stringify(cart));
            renderCart();
            updateCartIcon();
          });
        });

        // Remove item with confirmation
        div.querySelector('.remove-item').addEventListener('click', () => {
          if (confirm(`Remove ${item.name} from cart?`)) {
            cart.splice(index, 1);
            localStorage.setItem('cart', JSON.stringify(cart));
            renderCart();
            updateCartIcon();
            showNotification(`${item.name} removed from cart`, 'info');
          }
        });

        // Wishlist toggle
        div.querySelector('.wishlist').addEventListener('click', (e) => {
          const icon = e.currentTarget.querySelector('i');
          if (icon.classList.contains('far')) {
            icon.classList.remove('far');
            icon.classList.add('fas');
            e.currentTarget.classList.add('text-red-500');
            e.currentTarget.classList.remove('text-gray-400');
            showNotification(`${item.name} added to wishlist!`, 'success');
          } else {
            icon.classList.remove('fas');
            icon.classList.add('far');
            e.currentTarget.classList.remove('text-red-500');
            e.currentTarget.classList.add('text-gray-400');
            showNotification(`${item.name} removed from wishlist`, 'info');
          }
        });
      });

      updateTotals();
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    function updateTotals(){
      let subtotal = cart.reduce((sum, item) => sum + item.price * (item.quantity || 1), 0);
      let tax = subtotal * 0.12; // 12% tax
      let total = subtotal + tax;
      
      subtotalEl.textContent = `₱${subtotal.toFixed(2)}`;
      totalEl.textContent = `₱${total.toFixed(2)}`;
      
      const itemCountEl = document.getElementById('itemCount');
      const taxEl = document.getElementById('tax');
      const totalItems = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
      
      if (itemCountEl) itemCountEl.textContent = totalItems;
      if (taxEl) taxEl.textContent = `₱${tax.toFixed(2)}`;
    }

    function updateCartIcon() {
      const cartIcon = document.querySelector('a[href="cart.html"]');
      if (!cartIcon) return;

      let badge = cartIcon.querySelector('.cart-badge');
      if (!badge) {
        badge = document.createElement('span');
        badge.className = 'cart-badge absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold rounded-full px-1.5 min-w-[1.25rem] text-center';
        cartIcon.classList.add('relative');
        cartIcon.appendChild(badge);
      }
      const totalItems = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
      badge.textContent = totalItems;
      badge.style.display = totalItems > 0 ? 'block' : 'none';
    }

    function showNotification(message, type = 'success') {
      const existing = document.querySelector('.toast-notification');
      if (existing) existing.remove();

      const toast = document.createElement('div');
      const bgColor = type === 'success' ? 'bg-green-600' : type === 'info' ? 'bg-blue-600' : 'bg-red-600';
      const icon = type === 'success' ? 'fa-check-circle' : type === 'info' ? 'fa-info-circle' : 'fa-exclamation-circle';
      
      toast.className = `toast-notification fixed top-20 right-6 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full ${bgColor} text-white`;
      toast.innerHTML = `
        <div class="flex items-center gap-3">
          <i class="fas ${icon} text-xl"></i>
          <span class="font-medium">${message}</span>
        </div>
      `;
      document.body.appendChild(toast);

      setTimeout(() => toast.classList.remove('translate-x-full'), 10);
      setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    }

    // Clear cart functionality
    const clearCartBtn = document.getElementById('clearCartBtn');
    if (clearCartBtn) {
      clearCartBtn.addEventListener('click', () => {
        if (cart.length === 0) {
          showNotification('Cart is already empty', 'info');
          return;
        }
        
        if (confirm('Are you sure you want to clear your entire cart?')) {
          cart = [];
          localStorage.setItem('cart', JSON.stringify(cart));
          renderCart();
          updateCartIcon();
          showNotification('Cart cleared successfully', 'success');
        }
      });
    }

    // Checkout button validation
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) {
      checkoutBtn.addEventListener('click', (e) => {
        if (cart.length === 0) {
          e.preventDefault();
          showNotification('Your cart is empty. Add items before checkout.', 'info');
        }
      });
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
      // Ctrl/Cmd + D to clear cart
      if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
        e.preventDefault();
        if (clearCartBtn) clearCartBtn.click();
      }
      // Escape to go back
      if (e.key === 'Escape') {
        window.history.back();
      }
    });

    renderCart();