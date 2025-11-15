(function () {
  // helpers
  const $ = sel => document.querySelector(sel);
  const $$ = sel => Array.from(document.querySelectorAll(sel));

  // dynamic references (use functions to always get current nodes)
  const getCategoryCheckboxes = () => Array.from(document.querySelectorAll('.category-checkbox'));
  const getProductCards = () => Array.from(document.querySelectorAll('.product-card'));
  const getAddButtons = () => Array.from(document.querySelectorAll('.add-btn'));

  const organicCheckbox = $('#organicOnly');
  const minPriceInput = $('#minPrice');
  const maxPriceInput = $('#maxPrice');
  const priceRange = $('#priceRange');
  const applyBtn = $('#applyFilters');
  const clearBtn = $('#clearFilters');
  const productsGrid = $('#productsGrid');
  const sortSelect = $('#sortSelect');
  const globalSearch = $('#globalSearch');
  const loadMoreBtn = $('#loadMore');

  const params = new URLSearchParams(window.location.search);
  const initialCategory = params.get('category'); // may be comma separated
  const initialSearch = params.get('search');

  // Show/hide helpers
  function showCard(card) { card.style.display = ''; }
  function hideCard(card) { card.style.display = 'none'; }

  // Filter logic
  function filterProducts() {
    const categoryCheckboxes = getCategoryCheckboxes();
    const activeCats = categoryCheckboxes.filter(cb => cb.checked).map(cb => cb.dataset.cat.toLowerCase());
    const organicOnly = organicCheckbox.checked;
    const minP = parseFloat(minPriceInput.value) || 0;
    const maxP = parseFloat(maxPriceInput.value) || Number.POSITIVE_INFINITY;
    const searchText = (globalSearch.value || '').trim().toLowerCase();

    getProductCards().forEach(card => {
      const catStr = (card.dataset.category || '').toLowerCase();
      const itemCats = catStr.split(',').map(s => s.trim()).filter(Boolean);
      const price = parseFloat(card.dataset.price) || 0;
      const isOrganic = String(card.dataset.organic) === 'true';
      const name = (card.dataset.name || '').toLowerCase();

      const categoryMatch = activeCats.length === 0 || activeCats.some(c => itemCats.includes(c));
      const organicMatch = !organicOnly || isOrganic;
      const priceMatch = price >= minP && price <= maxP;
      const searchMatch = !searchText || name.includes(searchText);

      if (categoryMatch && organicMatch && priceMatch && searchMatch) {
        showCard(card);
      } else {
        hideCard(card);
      }
    });

    applySort(); // reorder visible cards
  }

  // Sorting
  function applySort() {
    const mode = sortSelect.value;
    const visible = getProductCards().filter(c => c.style.display !== 'none');
    let sorted = visible.slice();

    if (mode === 'price-asc') {
      sorted.sort((a,b) => (parseFloat(a.dataset.price)||0) - (parseFloat(b.dataset.price)||0));
    } else if (mode === 'price-desc') {
      sorted.sort((a,b) => (parseFloat(b.dataset.price)||0) - (parseFloat(a.dataset.price)||0));
    } else if (mode === 'newest') {
      // if you had data-date you could sort here; leave as-is for now
    } else {
      // featured - do nothing
    }

    sorted.forEach(card => productsGrid.appendChild(card));
  }

  // Initialize from URL (auto-check category and filter)
  function initFromURL() {
    if (initialCategory) {
      const catsFromUrl = initialCategory.split(',').map(s => s.trim().toLowerCase()).filter(Boolean);
      getCategoryCheckboxes().forEach(cb => {
        cb.checked = catsFromUrl.includes(cb.dataset.cat.toLowerCase());
      });
      filterProducts();
      setTimeout(() => {
        const top = productsGrid.getBoundingClientRect().top + window.scrollY - 80;
        window.scrollTo({ top, behavior: 'smooth' });
      }, 120);
    }
    if (initialSearch) {
      globalSearch.value = initialSearch;
    }
    // Always filter on load to apply any URL params from homepage or category links
    filterProducts();
  }

  // Wire up event listeners
  function bindEventListeners() {
    getCategoryCheckboxes().forEach(cb => cb.addEventListener('change', filterProducts));
    organicCheckbox.addEventListener('change', filterProducts);
    applyBtn.addEventListener('click', filterProducts);

    priceRange.addEventListener('input', (e) => {
      maxPriceInput.value = e.target.value;
    });
    minPriceInput.addEventListener('change', filterProducts);
    maxPriceInput.addEventListener('change', filterProducts);

    sortSelect.addEventListener('change', applySort);

    clearBtn.addEventListener('click', () => {
      getCategoryCheckboxes().forEach(cb => cb.checked = false);
      organicCheckbox.checked = false;
      minPriceInput.value = '';
      maxPriceInput.value = '';
      priceRange.value = priceRange.max || 500;
      globalSearch.value = '';
      filterProducts();
    });

    let searchTimeout = null;
    globalSearch.addEventListener('input', () => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(filterProducts, 250);
    });

    // Event delegation for product grid clicks
    productsGrid.addEventListener('click', (e) => {
      const card = e.target.closest('.product-card');
      if (card && !e.target.closest('.add-btn')) {
        e.preventDefault();
        
        const name = card.dataset.name || 'Item';
        const price = card.dataset.price || '0';
        const imgElement = card.querySelector('img');
        // Get relative path instead of absolute URL
        const img = imgElement?.getAttribute('src') || '';
        const description = card.dataset.description || 'No description available.';
        
        const params = new URLSearchParams({
          name: name,
          price: price,
          img: img,
          description: description,
        });
        
        window.location.href = `productdetails.html?${params.toString()}`;
      }
    });

    // Load more: append another batch of 12 products
    loadMoreBtn.addEventListener('click', () => {
      appendMoreProducts();
    });
  }

  // Utility to create a product card element
  function createProductCard(product) {
    const div = document.createElement('div');
    div.className = 'product-card bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition';
    div.setAttribute('data-category', product.category);
    div.setAttribute('data-price', product.price);
    div.setAttribute('data-organic', product.organic ? 'true' : 'false');
    div.setAttribute('data-name', product.name);
    div.setAttribute('data-description', product.description || 'Fresh and high-quality product from local farms.');

    div.innerHTML = `
      <img src="${product.img}" alt="${escapeHtml(product.name)}" class="w-full h-40 object-cover">
      <div class="p-4">
        <h3 class="font-medium text-gray-800">${escapeHtml(product.name)}</h3>
        <p class="text-sm text-gray-500">${escapeHtml(product.unit)}</p>
        <div class="flex justify-between items-center mt-2">
          <p class="font-semibold text-green-700">₱${Number(product.price).toFixed(2)}</p>
          <button class="add-btn bg-white text-green-600 border border-green-600 rounded-full w-8 h-8 flex items-center justify-center hover:bg-green-600 hover:text-white shadow transition" title="Add to cart">
            <i class="fa-solid fa-plus"></i>
          </button>
        </div>
      </div>
    `;
    return div;
  }

  // Escape HTML to prevent accidental injection from generated content
  function escapeHtml(s) {
    return String(s).replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'",'&#39;');
  }

  // Sample batch generator - returns 12 product objects (can be adjusted to fetch from API)
  let loadBatchCount = 0;
  function generateBatchProducts() {
    // simple rotating sample products to append — images reused from your set
    const sample = [
      { name: 'Kangkong Bunch', category: 'vegetables', price: 22.50, organic: false, img: 'images/products/fresh spinach.png', unit: 'Per bunch', description: 'Fresh kangkong bunch, perfect for soups and stir-fries, harvested daily from local farms.' },
      { name: 'Cucumber', category: 'vegetables', price: 30.00, organic: false, img: 'images/products/organic cucumber.png', unit: 'Per kg', description: 'Crisp and refreshing cucumbers, ideal for salads and pickles, locally sourced.' },
      { name: 'Green Pepper', category: 'vegetables', price: 55.00, organic: false, img: 'images/products/bell pepper mix.png', unit: 'Per kg', description: 'Fresh green bell peppers, rich in vitamins, perfect for cooking and grilling.' },
      { name: 'Strawberry Basket', category: 'fruits', price: 120.00, organic: false, img: 'images/products/strawberry.png', unit: 'Per kg', description: 'Sweet and juicy strawberries, handpicked for the best quality and taste.' },
      { name: 'Banana Bunch', category: 'fruits', price: 80.00, organic: false, img: 'images/products/banana.png', unit: 'Each', description: 'Fresh banana bunch, naturally sweet and perfect for snacking or baking.' },
      { name: 'Chocolate Milk', category: 'dairy', price: 150.00, organic: false, img: 'images/products/chocolate milk.jpg', unit: 'Per liter', description: 'Rich and creamy chocolate milk, made from fresh farm milk, perfect for all ages.' },
      { name: 'Ube Cheese Pandesal', category: 'bakery', price: 65.00, organic: false, img: 'images/products/ube cheese pandesal.jpg', unit: 'Per loaf', description: 'Delicious ube cheese pandesal, soft and flavorful, freshly baked daily.' },
      { name: 'Pork Liempo', category: 'meat', price: 320.00, organic: false, img: 'images/products/fresh pork liempo.jpg', unit: 'Per kg', description: 'Premium pork liempo, tender and flavorful, perfect for grilling and roasting.' },
      { name: 'Tilapia (Fresh)', category: 'seafood', price: 140.00, organic: false, img: 'images/products/tilapia.jpg', unit: 'Per kg', description: 'Fresh tilapia fish, sustainably sourced, ideal for frying, grilling, or steaming.' },
      { name: 'Butter Spread', category: 'dairy', price: 95.00, organic: false, img: 'images/products/Butter Spread.jpg', unit: 'Per 200g', description: 'Creamy butter spread, perfect for toast, baking, and cooking.' },
      { name: 'Fresh Eggs', category: 'dairy', price: 70.00, organic: true, img: 'images/products/fresh eggs.jpeg', unit: 'Dozen', description: 'Farm fresh organic eggs, rich in protein and nutrients, from free-range chickens.' },
      { name: 'Fresh Okra', category: 'vegetables', price: 40.00, organic: false, img: 'images/products/fresh okra.jpg', unit: 'Per kg', description: 'Fresh okra, perfect for soups and stir-fries, rich in fiber and vitamins.' }
    ];

    // shift starting index based on batch count to vary names if user clicks many times
    const start = (loadBatchCount * 12) % sample.length;
    const batch = [];
    for (let i=0; i<12; i++) {
      const s = sample[(start + i) % sample.length];
      // create a shallow copy and tweak name to remain unique-ish
      batch.push({
        name: `${s.name}${loadBatchCount ? ' — batch ' + (loadBatchCount+1) : ''}`,
        category: s.category,
        price: s.price,
        organic: s.organic,
        img: s.img,
        unit: s.unit,
        description: s.description
      });
    }
    loadBatchCount++;
    return batch;
  }

  // Append more products (12)
  function appendMoreProducts() {
    const more = generateBatchProducts();
    const fragment = document.createDocumentFragment();
    more.forEach(p => {
      const card = createProductCard(p);
      fragment.appendChild(card);
    });
    productsGrid.appendChild(fragment);

    // After appending, immediately apply current filters so newly added products respect active filters
    filterProducts();

    // smooth scroll to the first newly added card
    const all = getProductCards();
    const firstNew = all[all.length - more.length];
    if (firstNew) {
      firstNew.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }

  // Update cart icon with item count
  function updateCartIcon() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const cartIcon = document.querySelector('a[href="cart.html"]');
    if (!cartIcon) return;

    // Create or update a badge for the count
    let badge = cartIcon.querySelector('.cart-badge');
    if (!badge) {
      badge = document.createElement('span');
      badge.className = 'cart-badge absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold rounded-full px-1.5';
      cartIcon.classList.add('relative');
      cartIcon.appendChild(badge);
    }
    const totalItems = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
    badge.textContent = totalItems;
    badge.style.display = totalItems > 0 ? 'block' : 'none';
  }

  // Add to cart functionality
  function addToCart(product) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    // Check if product already exists in cart
    const existingIndex = cart.findIndex(item => item.name === product.name);
    
    if (existingIndex > -1) {
      // Increment quantity if product exists
      cart[existingIndex].quantity = (cart[existingIndex].quantity || 1) + 1;
    } else {
      // Add new product to cart
      cart.push({
        name: product.name,
        price: parseFloat(product.price),
        image: product.image,
        quantity: 1,
        description: product.description
      });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartIcon();
    showNotification(`${product.name} added to cart!`);
  }

  // Show toast notification
  function showNotification(message, type = 'success') {
    // Remove existing notification if any
    const existing = document.querySelector('.toast-notification');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = `toast-notification fixed top-20 right-6 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full ${type === 'success' ? 'bg-green-600' : 'bg-red-600'} text-white`;
    toast.innerHTML = `
      <div class="flex items-center gap-3">
        <i class="fas fa-check-circle text-xl"></i>
        <span class="font-medium">${message}</span>
      </div>
    `;
    document.body.appendChild(toast);

    // Animate in
    setTimeout(() => toast.classList.remove('translate-x-full'), 10);

    // Animate out and remove
    setTimeout(() => {
      toast.classList.add('translate-x-full');
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  // Handle add to cart button clicks
  function handleAddToCart(card) {
    const product = {
      name: card.dataset.name,
      price: card.dataset.price,
      image: card.querySelector('img')?.getAttribute('src') || '',
      description: card.dataset.description || ''
    };
    addToCart(product);
  }

  // Bind add to cart buttons with event delegation
  function bindAddToCartButtons() {
    productsGrid.addEventListener('click', (e) => {
      const addBtn = e.target.closest('.add-btn');
      if (addBtn) {
        e.stopPropagation(); // Prevent triggering the card click
        const card = addBtn.closest('.product-card');
        if (card) {
          handleAddToCart(card);
        }
      }
    });
  }

  // Initialization
  function init() {
    bindEventListeners();
    bindAddToCartButtons();
    initFromURL();
    updateCartIcon(); // Update on page load
  }

  init();
})();
