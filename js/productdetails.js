document.addEventListener('DOMContentLoaded', () => {
    // --- Get product data from localStorage ---
    const selectedProduct = JSON.parse(localStorage.getItem('selectedProduct'));
    const productContent = document.getElementById('product-content');

    if (selectedProduct && productContent) {
        // --- Populate the page with product data ---
        document.getElementById('productImage').src = selectedProduct.img;
        document.getElementById('productImage').alt = selectedProduct.name;
        document.getElementById('productName').textContent = selectedProduct.name;
        document.getElementById('productPrice').textContent = `₱${Number(selectedProduct.price).toFixed(2)}`;
        document.getElementById('productDescription').textContent = selectedProduct.description;
    } else if (productContent) {
        // --- Handle case where no product is found ---
        productContent.innerHTML = '<p class="text-center text-red-500 col-span-2">Could not load product details. Please go back and try again.</p>';
        return; // Stop executing the rest of the script
    }

    // --- Element References ---
    const quantityInput = document.getElementById('quantityInput');
    const increaseBtn = document.getElementById('increaseQty');
    const decreaseBtn = document.getElementById('decreaseQty');
    const addToCartBtn = document.getElementById('addToCartBtn');
    const heartBtn = document.getElementById('heartBtn');
    const heartIcon = document.getElementById('heartIcon');

    // --- Event Listeners ---
    increaseBtn.addEventListener('click', () => {
      let value = parseInt(quantityInput.value);
      quantityInput.value = value + 1;
    });

    decreaseBtn.addEventListener('click', () => {
      let value = parseInt(quantityInput.value);
      if (value > 1) quantityInput.value = value - 1;
    });

    addToCartBtn.addEventListener('click', () => {
      // 1. Get product details from the page
      const name = document.querySelector('h2').textContent.trim();
      const priceString = document.querySelector('.text-2xl.font-bold.text-green-700').textContent;
      const price = parseFloat(priceString.replace('₱', ''));
      const image = document.querySelector('main > div:first-child img').src;
      const quantity = parseInt(quantityInput.value);

      // 2. Get existing cart from localStorage or create a new one
      let cart = JSON.parse(localStorage.getItem('cart')) || [];

      // 3. Check if item already exists in cart
      const existingItem = cart.find(item => item.name === name);

      if (existingItem) {
        // If it exists, just update the quantity
        existingItem.quantity += quantity;
      } else {
        // If not, add the new item to the cart
        cart.push({ name, price, image, quantity });
      }

      // 4. Save the updated cart back to localStorage
      localStorage.setItem('cart', JSON.stringify(cart));

      // 5. Redirect to the cart page
      window.location.href = 'cart.html';
    });

    heartBtn.addEventListener('click', () => {
      const isFavorited = heartIcon.classList.contains('fa-solid');
      if (isFavorited) {
        heartIcon.classList.remove('fa-solid', 'text-red-500');
        heartIcon.classList.add('fa-regular');
      } else {
        heartIcon.classList.remove('fa-regular');
        heartIcon.classList.add('fa-solid', 'text-red-500');
      }
    });
});