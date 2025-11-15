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


      });

    // CART FUNCTIONALITY
    const cartContainer = document.getElementById('cartItems');
    const subtotalEl = document.getElementById('subtotal');
    const totalEl = document.getElementById('total');

    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    function renderCart() {
      cartContainer.innerHTML = '';
      if(cart.length === 0){
        cartContainer.innerHTML = '<p class="text-gray-500 text-center">Your cart is empty</p>';
        subtotalEl.textContent = '₱0.00';
        totalEl.textContent = '₱0.00';
        return;
      }

      cart.forEach((item, index) => {
        const itemTotal = (item.price * (item.quantity || 1)).toFixed(2);
        const div = document.createElement('div');
        div.className = 'bg-white p-4 rounded-xl shadow-sm flex items-center justify-between';
        div.innerHTML = `
          <div class="flex items-center gap-4">
            <img src="${item.image || 'images/products/img.png'}" class="w-16 h-16 rounded-lg object-cover">
            <div>
              <h3 class="font-semibold text-gray-800">${item.name}</h3>
              <p class="text-green-700 font-medium">₱${item.price.toFixed(2)}</p>
            </div>
          </div>
          <div class="flex items-center gap-4">
            <button class="quantity-btn text-gray-600 px-2 py-1 border rounded-full" data-action="decrease">–</button>
            <span class="quantity text-lg font-medium">${item.quantity || 1}</span>
            <button class="quantity-btn text-gray-600 px-2 py-1 border rounded-full" data-action="increase">+</button>
            <p class="text-green-700 font-semibold w-20 text-right item-total">₱${itemTotal}</p>
            <button class="wishlist text-gray-400 hover:text-red-500 transition"><i class="fa-regular fa-heart"></i></button>
            <button class="remove-item text-red-500 hover:text-red-700 text-lg"><i class="fa-solid fa-xmark"></i></button>
          </div>
        `;
        cartContainer.appendChild(div);

        // Quantity buttons
        div.querySelectorAll('.quantity-btn').forEach(btn => {
          btn.addEventListener('click', () => {
            if(!item.quantity) item.quantity = 1;
            if(btn.dataset.action === 'increase') item.quantity++;
            else if(btn.dataset.action === 'decrease' && item.quantity > 1) item.quantity--;
            localStorage.setItem('cart', JSON.stringify(cart));
            renderCart();
          });
        });

        // Remove item
        div.querySelector('.remove-item').addEventListener('click', () => {
          cart.splice(index,1);
          localStorage.setItem('cart', JSON.stringify(cart));
          renderCart();
        });

        // Wishlist toggle
        div.querySelector('.wishlist').addEventListener('click', () => {
          div.querySelector('.wishlist').classList.toggle('active');
        });
      });

      updateTotals();
    }

    function updateTotals(){
      let subtotal = cart.reduce((sum, item) => sum + item.price * (item.quantity || 1), 0);
      subtotalEl.textContent = `₱${subtotal.toFixed(2)}`;
      totalEl.textContent = `₱${subtotal.toFixed(2)}`;
    }

    renderCart();