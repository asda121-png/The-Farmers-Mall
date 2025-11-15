document.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  const name = params.get('name');
  const price = params.get('price');
  const img = params.get('img');
  const description = params.get('description');

  if (name && price && img && description) {
    document.getElementById('main-image').src = img;
    document.getElementById('main-image').alt = name;
    document.querySelector('h1.text-4xl').textContent = name;
    document.querySelector('.text-green-600').textContent = `₱${Number(price).toFixed(2)}`;
    
    // Since the description is now in a different section, we need to find it.
    const descriptionContainer = document.querySelector('.mt-16 p');
    if(descriptionContainer) {
        descriptionContainer.textContent = description;
    }

  } else {
    const productGrid = document.querySelector('.grid.md\\:grid-cols-2');
    if(productGrid) {
        productGrid.innerHTML = '<p class="text-center text-red-500 col-span-2">Could not load product details. Please go back and try again.</p>';
    }
    return;
  }

  const quantityInput = document.getElementById('quantity');
  const increaseBtn = quantityInput.nextElementSibling;
  const decreaseBtn = quantityInput.previousElementSibling;
  const addToCartBtn = document.querySelector('.add-to-cart-btn');
  const heartBtn = document.querySelector('.wishlist-btn');

  increaseBtn.addEventListener('click', () => {
    let value = parseInt(quantityInput.value);
    quantityInput.value = value + 1;
  });

  decreaseBtn.addEventListener('click', () => {
    let value = parseInt(quantityInput.value);
    if (value > 1) quantityInput.value = value - 1;
  });

  addToCartBtn.addEventListener('click', () => {
    const quantity = parseInt(quantityInput.value);
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    const existingItem = cart.find(item => item.name === name);

    if (existingItem) {
      existingItem.quantity += quantity;
    } else {
      cart.push({ name, price, image: img, quantity });
    }

    localStorage.setItem('cart', JSON.stringify(cart));
    window.location.href = 'cart.html';
  });

  heartBtn.addEventListener('click', () => {
    const heartIcon = heartBtn.querySelector('i');
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
