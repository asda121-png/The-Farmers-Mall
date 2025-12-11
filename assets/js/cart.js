    // CART FUNCTIONALITY
    const cartContainer = document.getElementById('cartItems');
    const subtotalEl = document.getElementById('subtotal');
    const totalEl = document.getElementById('total');

    if (!cartContainer) {
      console.error('Cart container not found! #cartItems element is missing.');
    }

    let cart = [];
    let selectedItems = new Set(); // Track selected items for checkout

    // Load cart from database
    async function loadCartFromDB() {
      try {
        console.log('🔄 Loading cart from database...');
        const response = await fetch('../api/cart.php');
        console.log('📡 Response status:', response.status);
        
        const data = await response.json();
        console.log('📦 Cart data received:', data);
        
        if (data.success) {
          cart = data.items || [];
          console.log('✅ Cart items loaded successfully:', cart.length, 'items');
          console.log('🛒 Cart contents:', cart);
          renderCart();
          updateCartIcon();
        } else {
          console.error('❌ Failed to load cart:', data.message);
          showNotification('Failed to load cart', 'error');
        }
      } catch (error) {
        console.error('💥 Error loading cart:', error);
        showNotification('Error loading cart', 'error');
      }
    }

    // Add item to cart in database
    async function addToCartDB(productId, quantity = 1) {
      try {
        const response = await fetch('../api/cart.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            product_id: productId,
            quantity: quantity
          })
        });
        
        const data = await response.json();
        if (data.success) {
          await loadCartFromDB();
          return true;
        } else {
          showNotification(data.message || 'Failed to add to cart', 'error');
          return false;
        }
      } catch (error) {
        console.error('Error adding to cart:', error);
        showNotification('Error adding to cart', 'error');
        return false;
      }
    }

    // Update cart item quantity in database
    async function updateCartItemDB(cartId, quantity) {
      try {
        const response = await fetch('../api/cart.php', {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            cart_id: cartId,
            quantity: quantity
          })
        });
        
        const data = await response.json();
        if (data.success) {
          await loadCartFromDB();
          return true;
        } else {
          showNotification(data.message || 'Failed to update cart', 'error');
          return false;
        }
      } catch (error) {
        console.error('Error updating cart:', error);
        showNotification('Error updating cart', 'error');
        return false;
      }
    }

    // Delete cart item from database
    async function deleteCartItemDB(cartId) {
      try {
        const response = await fetch('../api/cart.php', {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            cart_id: cartId
          })
        });
        
        const data = await response.json();
        if (data.success) {
          await loadCartFromDB();
          return true;
        } else {
          showNotification(data.message || 'Failed to remove item', 'error');
          return false;
        }
      } catch (error) {
        console.error('Error removing item:', error);
        showNotification('Error removing item', 'error');
        return false;
      }
    }

    // Clear entire cart from database
    async function clearCartDB() {
      try {
        const response = await fetch('../api/cart.php', {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            clear_all: true
          })
        });
        
        const data = await response.json();
        if (data.success) {
          cart = [];
          renderCart();
          updateCartIcon();
          return true;
        } else {
          showNotification(data.message || 'Failed to clear cart', 'error');
          return false;
        }
      } catch (error) {
        console.error('Error clearing cart:', error);
        showNotification('Error clearing cart', 'error');
        return false;
      }
    }

    function renderCart() {
      console.log('🎨 Rendering cart with', cart.length, 'items');
      cartContainer.innerHTML = '';
      
      // Update cart count in header
      const cartCountEl = document.getElementById('cartCount');
      if (cartCountEl) {
        cartCountEl.textContent = cart.length;
      }
      
      if(cart.length === 0){
        console.log('📭 Cart is empty, showing empty state');
        cartContainer.innerHTML = `
          <div class="text-center py-16 col-span-2">
            <i class="fas fa-shopping-cart text-gray-300 text-6xl mb-4"></i>
            <p class="text-gray-500 text-xl mb-2">Your cart is empty</p>
            <p class="text-gray-400 mb-6">Add some products to get started!</p>
            <a href="products.php" class="inline-block bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition shadow-md">
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

      console.log('🔨 Building cart items HTML...');
      cart.forEach((item, index) => {
        const itemTotal = (item.price * (item.quantity || 1)).toFixed(2);
        const itemImage = item.image || item.image_url || '../images/products/Fresh Vegetable Box.png';
        console.log(`  📦 Item ${index + 1}:`, item.name, '| Price:', item.price, '| Qty:', item.quantity, '| Image:', itemImage);
        const div = document.createElement('div');
        div.className = 'bg-white p-4 rounded-xl shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between hover:shadow-md transition-shadow gap-4';
        div.innerHTML = `
          <div class="flex items-start gap-4 flex-1 w-full">
            <div class="flex items-center gap-4 flex-1">
              <input type="checkbox" class="item-checkbox w-5 h-5 rounded border-gray-300 cursor-pointer accent-green-600" data-cart-id="${item.cart_id}">
              <img src="${itemImage}" class="w-20 h-20 md:w-24 md:h-24 rounded-lg object-cover border border-gray-200 flex-shrink-0">
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-gray-800 text-base md:text-lg">${escapeHtml(item.name)}</h3>
              <p class="text-green-700 font-medium text-sm">₱${item.price.toFixed(2)} each</p>
              ${item.description ? `<p class="text-gray-500 text-xs mt-1 line-clamp-1 md:line-clamp-2">${escapeHtml(item.description)}</p>` : ''}
              <div class="flex items-center gap-2 mt-2 md:hidden">
                <div class="flex items-center gap-2 border rounded-lg px-2 py-1 bg-gray-50">
                  <button class="quantity-btn text-gray-600 hover:text-green-600 font-bold text-base w-7 h-7 flex items-center justify-center rounded hover:bg-white transition" data-action="decrease">−</button>
                  <span class="quantity text-base font-semibold min-w-[1.5rem] text-center">${item.quantity || 1}</span>
                  <button class="quantity-btn text-gray-600 hover:text-green-600 font-bold text-base w-7 h-7 flex items-center justify-center rounded hover:bg-white transition" data-action="increase">+</button>
                </div>
                <p class="text-green-700 font-bold text-base item-total">₱${itemTotal}</p>
              </div>
            </div>
          </div>
          <div class="hidden md:flex items-center gap-3">
            <div class="flex items-center gap-2 border rounded-lg px-2 py-1 bg-gray-50">
              <button class="quantity-btn text-gray-600 hover:text-green-600 font-bold text-lg w-8 h-8 flex items-center justify-center rounded hover:bg-white transition" data-action="decrease">−</button>
              <span class="quantity text-lg font-semibold min-w-[2rem] text-center">${item.quantity || 1}</span>
              <button class="quantity-btn text-gray-600 hover:text-green-600 font-bold text-lg w-8 h-8 flex items-center justify-center rounded hover:bg-white transition" data-action="increase">+</button>
            </div>
            <p class="text-green-700 font-bold text-lg w-24 text-right item-total">₱${itemTotal}</p>
            <button class="wishlist text-gray-400 hover:text-red-500 transition-colors" title="Add to wishlist">
              <i class="far fa-heart text-lg"></i>
            </button>
            <button class="remove-item text-red-500 hover:text-red-700 transition-colors" title="Remove item">
              <i class="fa-solid fa-trash text-lg"></i>
            </button>
          </div>
          <div class="flex md:hidden gap-2 w-full justify-end">
            <button class="wishlist text-gray-400 hover:text-red-500 transition-colors p-2" title="Add to wishlist">
              <i class="far fa-heart text-lg"></i>
            </button>
            <button class="remove-item text-red-500 hover:text-red-700 transition-colors p-2" title="Remove item">
              <i class="fa-solid fa-trash text-lg"></i>
            </button>
          </div>
        `;
        cartContainer.appendChild(div);

        // Quantity buttons
        div.querySelectorAll('.quantity-btn').forEach(btn => {
          btn.addEventListener('click', async () => {
            if(!item.quantity) item.quantity = 1;
            let newQuantity = item.quantity;
            
            if(btn.dataset.action === 'increase') {
              newQuantity++;
            } else if(btn.dataset.action === 'decrease' && item.quantity > 1) {
              newQuantity--;
            }
            
            // Update in database
            await updateCartItemDB(item.cart_id, newQuantity);
          });
        });

        // Checkbox toggle
        const checkbox = div.querySelector('.item-checkbox');
        if (checkbox) {
          checkbox.addEventListener('change', (e) => {
            if (e.target.checked) {
              selectedItems.add(item.cart_id);
              div.classList.add('bg-green-50');
            } else {
              selectedItems.delete(item.cart_id);
              div.classList.remove('bg-green-50');
            }
            updateCheckoutButtonState();
            // Update Select All checkbox state (checked / indeterminate)
            const selectAll = document.getElementById('selectAllCheckbox');
            if (selectAll) {
              const allCheckboxesNow = document.querySelectorAll('.item-checkbox[data-cart-id]');
              const allCheckedNow = allCheckboxesNow.length > 0 && Array.from(allCheckboxesNow).every(cb => cb.checked);
              const someCheckedNow = Array.from(allCheckboxesNow).some(cb => cb.checked);
              selectAll.checked = allCheckedNow;
              selectAll.indeterminate = false;
            }
          });
        }

        // Remove item with confirmation
        div.querySelector('.remove-item').addEventListener('click', () => {
          showDeleteModal(item, index);
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
      updateCheckoutButtonState();

      // Wire the top Select All checkbox to toggle item checkboxes
      const selectAllCheckbox = document.getElementById('selectAllCheckbox');
      if (selectAllCheckbox) {
        const allCheckboxes = document.querySelectorAll('.item-checkbox[data-cart-id]');
        // Initialize state
        if (allCheckboxes.length === 0) {
          selectAllCheckbox.checked = false;
          selectAllCheckbox.indeterminate = false;
        } else {
          const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
          const someChecked = Array.from(allCheckboxes).some(cb => cb.checked);
          selectAllCheckbox.checked = allChecked;
          selectAllCheckbox.indeterminate = false;
        }

        // Remove previous listeners by replacing the node, then add a fresh listener
        const newSelect = selectAllCheckbox.cloneNode(true);
        selectAllCheckbox.parentNode.replaceChild(newSelect, selectAllCheckbox);
        newSelect.addEventListener('change', (e) => {
          const isChecked = e.target.checked;
          allCheckboxes.forEach(cb => {
            cb.checked = isChecked;
            cb.dispatchEvent(new Event('change', { bubbles: true }));
          });
        });
      }
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    function updateTotals(){
      // Calculate totals only for selected items
      const selectedItemsArray = cart.filter(item => selectedItems.has(item.cart_id));
      let subtotal = selectedItemsArray.reduce((sum, item) => sum + item.price * (item.quantity || 1), 0);
      let tax = subtotal * 0.12; // 12% tax
      let total = subtotal + tax;
      
      subtotalEl.textContent = `₱${subtotal.toFixed(2)}`;
      // Update tax display
      const taxDisplayEl = document.getElementById('tax');
      if (taxDisplayEl) taxDisplayEl.textContent = `₱${tax.toFixed(2)}`;
      totalEl.textContent = `₱${total.toFixed(2)}`;
      
      const itemCountEl = document.getElementById('itemCount');
      const taxEl = document.getElementById('tax');
      const totalItems = selectedItemsArray.reduce((sum, item) => sum + (item.quantity || 1), 0);
      
      if (itemCountEl) itemCountEl.textContent = totalItems;
    }

    function updateCheckoutButtonState() {
      const checkoutBtn = document.getElementById('checkoutBtn');
      if (!checkoutBtn) return;
      
      if (selectedItems.size === 0) {
        checkoutBtn.disabled = true;
        checkoutBtn.classList.add('opacity-50', 'cursor-not-allowed');
        checkoutBtn.innerHTML = '<i class="fa-solid fa-lock mr-2"></i>Select items to checkout<i class="fa-solid fa-arrow-right ml-2"></i>';
      } else {
        checkoutBtn.disabled = false;
        checkoutBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        checkoutBtn.innerHTML = `<i class="fa-solid fa-lock mr-2"></i>Proceed to Checkout (${selectedItems.size})<i class="fa-solid fa-arrow-right ml-2"></i>`;
      }
      updateTotals();
    }

    function toggleSelectAll() {
      const allCheckboxes = document.querySelectorAll('.item-checkbox[data-cart-id]');
      const allSelected = allCheckboxes.length > 0 && Array.from(allCheckboxes).every(cb => cb.checked);
      
      allCheckboxes.forEach(checkbox => {
        checkbox.checked = !allSelected;
        checkbox.dispatchEvent(new Event('change', { bubbles: true }));
      });
    }

    function updateCartIcon() {
      const cartIcon = document.querySelector('a[href="cart.php"]');
      if (!cartIcon) return;

      let badge = cartIcon.querySelector('.cart-badge');
      if (!badge) {
        badge = document.createElement('span');
        badge.className = 'cart-badge absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold rounded-full px-1.5 min-w-[1.125rem] h-[1.125rem] flex items-center justify-center';
        cartIcon.classList.add('relative');
        cartIcon.appendChild(badge);
      }
      const totalItems = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
      badge.textContent = totalItems;
      badge.style.display = totalItems > 0 ? 'flex' : 'none';
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
        
        showClearCartModal();
      });
    }

    // Checkout button validation
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) {
      checkoutBtn.addEventListener('click', (e) => {
        if (selectedItems.size === 0) {
          e.preventDefault();
          showNotification('Please select items to checkout', 'info');
        } else {
          // Store selected items in sessionStorage for payment method page
          const selectedItemsArray = Array.from(selectedItems);
          sessionStorage.setItem('selectedCartItems', JSON.stringify(selectedItemsArray));
          
          // Pass selected cart IDs via URL parameter
          const cartIdsParam = selectedItemsArray.join(',');
          window.location.href = `paymentmethod.php?cart_ids=${cartIdsParam}`;
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
        const deleteModal = document.getElementById('deleteModal');
        const clearModal = document.getElementById('clearCartModal');
        if (deleteModal && deleteModal.classList.contains('show')) {
          hideDeleteModal();
        } else if (clearModal && clearModal.classList.contains('show')) {
          hideClearCartModal();
        } else {
          window.history.back();
        }
      }
    });

    // Delete Modal Functions
    function showDeleteModal(item, index) {
      const modal = document.getElementById('deleteModal');
      const productImage = document.getElementById('modalProductImage');
      const productName = document.getElementById('modalProductName');
      const productPrice = document.getElementById('modalProductPrice');
      const productQuantity = document.getElementById('modalProductQuantity');
      const confirmBtn = document.getElementById('confirmDeleteBtn');
      const cancelBtn = document.getElementById('cancelDeleteBtn');
      const closeBtn = document.getElementById('closeDeleteModal');

      // Set product info
      productImage.src = item.image || 'images/products/Fresh Vegetable Box.png';
      productName.textContent = item.name;
      productPrice.textContent = `₱${item.price.toFixed(2)}`;
      productQuantity.textContent = `Quantity: ${item.quantity || 1}`;

      // Show modal
      modal.classList.remove('hidden');
      modal.classList.add('show');

      // Remove existing event listeners by cloning buttons
      const newConfirmBtn = confirmBtn.cloneNode(true);
      const newCancelBtn = cancelBtn.cloneNode(true);
      const newCloseBtn = closeBtn.cloneNode(true);
      confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
      cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
      closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);

      // Add event listeners
      newConfirmBtn.addEventListener('click', async () => {
        const success = await deleteCartItemDB(item.cart_id);
        if (success) {
          hideDeleteModal();
          showNotification(`${item.name} removed from cart`, 'info');
        }
      });

      newCancelBtn.addEventListener('click', hideDeleteModal);
      newCloseBtn.addEventListener('click', hideDeleteModal);

      // Click outside to close
      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          hideDeleteModal();
        }
      });
    }

    function hideDeleteModal() {
      const modal = document.getElementById('deleteModal');
      modal.classList.remove('show');
      setTimeout(() => {
        modal.classList.add('hidden');
      }, 200);
    }

    // Clear Cart Modal Functions
    function showClearCartModal() {
      const modal = document.getElementById('clearCartModal');
      const itemCount = document.getElementById('clearCartItemCount');
      const confirmBtn = document.getElementById('confirmClearBtn');
      const cancelBtn = document.getElementById('cancelClearBtn');

      // Set item count
      const totalItems = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
      itemCount.textContent = totalItems;

      // Show modal
      modal.classList.remove('hidden');
      modal.classList.add('show');

      // Remove existing event listeners by cloning buttons
      const newConfirmBtn = confirmBtn.cloneNode(true);
      const newCancelBtn = cancelBtn.cloneNode(true);
      confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
      cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);

      // Add event listeners
      newConfirmBtn.addEventListener('click', async () => {
        const success = await clearCartDB();
        if (success) {
          hideClearCartModal();
          showNotification('Cart cleared successfully', 'success');
        }
      });

      newCancelBtn.addEventListener('click', hideClearCartModal);
      
      // Click outside to close
      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          hideClearCartModal();
        }
      });
    }

    function hideClearCartModal() {
      const modal = document.getElementById('clearCartModal');
      modal.classList.remove('show');
      setTimeout(() => {
        modal.classList.add('hidden');
      }, 200);
    }

    // Load cart from database on page load
    console.log('🚀 Initializing cart page...');
    loadCartFromDB();

    // Auto-reload cart when products are added from other pages
    window.addEventListener('storage', (e) => {
      if (e.key === 'cartUpdated') {
        console.log('🔄 Cart updated from another tab, reloading...');
        loadCartFromDB();
        localStorage.removeItem('cartUpdated'); // Clear flag
      }
    });

    // Also check for cart updates every 2 seconds when on cart page
    let lastCartCount = cart.length;
    setInterval(async () => {
      try {
        const response = await fetch('../api/cart.php');
        const data = await response.json();
        if (data.success && data.items && data.items.length !== lastCartCount) {
          console.log('🔄 Cart count changed from', lastCartCount, 'to', data.items.length, '- reloading...');
          lastCartCount = data.items.length;
          await loadCartFromDB();
        }
      } catch (error) {
        console.error('⚠️ Error checking for cart updates:', error);
      }
    }, 2000);