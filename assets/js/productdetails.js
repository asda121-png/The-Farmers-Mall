document.addEventListener('DOMContentLoaded', () => {
  // Get URL parameters
  const params = new URLSearchParams(window.location.search);
  const productName = params.get('name') || 'Fresh Organic Vegetable Bundle';
  const productPrice = (params.get('price') || '24.99').replace(/[^\d.]/g, ''); // Remove non-numeric characters except dot
  const productImg = params.get('img') || '../images/products/Fresh Vegetable Box.png';
  const productDescription = params.get('description') || 'A fresh assortment of seasonal vegetables including carrots, spinach, and broccoli, perfect for healthy meals.';

  // Update page elements
  const productNameEl = document.getElementById('product-name');
  const productPriceEl = document.getElementById('product-price');
  const mainImageEl = document.getElementById('main-image');
  const breadcrumbEl = document.getElementById('product-name-breadcrumb');

  if (productNameEl) productNameEl.textContent = productName;
  if (productPriceEl) productPriceEl.textContent = `₱${parseFloat(productPrice).toFixed(2)}`;
  if (mainImageEl) mainImageEl.src = productImg;
  if (breadcrumbEl) breadcrumbEl.textContent = productName;

  // Update product description
  const descriptionEl = document.querySelector('main p.text-gray-600');
  if (descriptionEl) {
    descriptionEl.textContent = productDescription;
  }

  // Image gallery functionality
  const thumbnails = document.querySelectorAll('.thumbnail-gallery img');
  thumbnails.forEach(thumb => {
    thumb.addEventListener('click', () => {
      // Remove active class from all thumbnails
      thumbnails.forEach(t => t.classList.remove('border-green-500'));
      // Add active class to clicked thumbnail
      thumb.classList.add('border-green-500');
      // Update main image
      if (mainImageEl) mainImageEl.src = thumb.src;
    });
  });

  // Set first thumbnail as active and main image
  if (thumbnails.length > 0 && mainImageEl) {
    thumbnails[0].classList.add('border-green-500');
    mainImageEl.src = productImg;
  }

  // Quantity controls
  const quantityInput = document.getElementById('quantity');
  const decreaseBtn = document.querySelector('.quantity-input button:first-child');
  const increaseBtn = document.querySelector('.quantity-input button:last-child');

  if (decreaseBtn) {
    decreaseBtn.addEventListener('click', () => {
      let currentValue = parseInt(quantityInput.value) || 1;
      if (currentValue > 1) {
        quantityInput.value = currentValue - 1;
      }
    });
  }

  if (increaseBtn) {
    increaseBtn.addEventListener('click', () => {
      let currentValue = parseInt(quantityInput.value) || 1;
      quantityInput.value = currentValue + 1;
    });
  }

  // Prevent non-numeric input in quantity field
  if (quantityInput) {
    quantityInput.addEventListener('input', (e) => {
      let value = parseInt(e.target.value);
      if (isNaN(value) || value < 1) {
        e.target.value = 1;
      }
    });
  }

  // Add to Cart functionality
  const addToCartBtn = document.querySelector('.add-to-cart-btn');
  if (addToCartBtn) {
    addToCartBtn.addEventListener('click', () => {
      const quantity = parseInt(quantityInput.value) || 1;
      const product = {
        name: productName,
        price: parseFloat(productPrice),
        image: productImg,
        quantity: quantity,
        description: productDescription
      };

      addToCart(product);
    });
  }

  // Wishlist functionality
  const wishlistBtn = document.querySelector('.wishlist-btn');
  if (wishlistBtn) {
    wishlistBtn.addEventListener('click', () => {
      const icon = wishlistBtn.querySelector('i');
      if (icon.classList.contains('far')) {
        icon.classList.remove('far');
        icon.classList.add('fas');
        wishlistBtn.classList.add('text-red-500');
        showNotification('Added to wishlist!', 'success');
      } else {
        icon.classList.remove('fas');
        icon.classList.add('far');
        wishlistBtn.classList.remove('text-red-500');
        showNotification('Removed from wishlist', 'info');
      }
    });
  }

  // Cart functions
  function addToCart(product) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    const existingIndex = cart.findIndex(item => item.name === product.name);
    
    if (existingIndex > -1) {
      cart[existingIndex].quantity += product.quantity;
    } else {
      cart.push(product);
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartIcon();
    showNotification(`${product.quantity}x ${product.name} added to cart!`);
  }

  function updateCartIcon() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const cartIcon = document.querySelector('a[href="cart.php"]');
    if (!cartIcon) return;

    let badge = cartIcon.querySelector('.cart-badge');
    if (!badge) {
      badge = document.createElement('span');
      badge.className = 'cart-badge absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold rounded-full px-1.5 min-w-[1.125rem] h-[1.125rem] flex items-center justify-center';
      cartIcon.classList.add('relative', 'inline-block');
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
    toast.className = `toast-notification fixed top-20 right-6 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full ${bgColor} text-white`;
    toast.innerHTML = `
      <div class="flex items-center gap-3">
        <i class="fas fa-check-circle text-xl"></i>
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

  // Initialize cart icon on page load
  updateCartIcon();

  // Make "You Might Also Like" products clickable
  const relatedProducts = document.querySelectorAll('.product-card[data-name]');
  relatedProducts.forEach(card => {
    card.addEventListener('click', () => {
      const name = card.dataset.name;
      const price = card.dataset.price;
      const img = card.dataset.img;
      const description = card.dataset.description || 'Fresh and high-quality product from local farms.';
      
      const params = new URLSearchParams({
        name: name,
        price: price,
        img: img,
        description: description
      });
      
      window.location.href = `productdetails.php?${params.toString()}`;
    });
  });
});
