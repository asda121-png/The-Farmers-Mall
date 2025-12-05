<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Seller Inventory – The Farmer’s Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">
     <?php
// Include the header
include '../retailer/retailerheader.php';
?>
</body>

  <!-- MAIN CONTENT -->
  <main class="flex-grow">
    <div class="max-w-6xl mx-auto px-6 py-8 space-y-6 w-full">
      <!-- Back & Title -->
      <div class="flex items-center space-x-3">
        <button onclick="window.location.href='retailerdashboard.php'" class="text-gray-600 hover:text-black">
          <i class="fa-solid fa-arrow-left text-lg"></i>
        </button>
        <h2 class="text-lg font-semibold">Inventory Status</h2>
      </div>

      <!-- Controls -->
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2 w-full md:w-auto">
          <input id="tableSearchInput" type="text" placeholder="Search products..." class="border rounded-md px-3 py-2 w-full md:w-64 focus:outline-none focus:ring-2 focus:ring-green-600">
          <button id="filterBtn" class="border px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100 flex items-center gap-1">
            <i class="fa-solid fa-filter"></i> Filter
          </button>
          <button id="sortBtn" class="border px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100 flex items-center gap-1">
            <i class="fa-solid fa-sort"></i> Sort
          </button>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div id="filterTabs" class="flex flex-wrap items-center gap-3 mt-2">
        <button data-filter="All" class="filter-tab bg-green-600 text-white px-3 py-1 rounded-full text-sm font-medium">All</button>
        <button data-filter="In Stock" class="filter-tab bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm font-medium">In Stock</button>
        <button data-filter="Low Stock" class="filter-tab bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm font-medium">Low Stock</button>
        <button data-filter="Out of Stock" class="filter-tab bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm font-medium">Out of Stock</button>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden min-h-[500px]">
        <table class="min-w-full text-sm text-left">
          <thead class="bg-gray-100 border-b text-gray-700 font-semibold">
            <tr>
              <th class="py-3 px-5">Product</th>
              <th class="py-3 px-5">Stock Level</th>
              <th class="py-3 px-5">Status</th>
              <th class="py-3 px-5">Last Updated</th>
              <th class="py-3 px-5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody id="inventoryTableBody" class="divide-y"></tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div id="paginationContainer" class="flex justify-center items-center gap-2 mt-6"></div>
    </div>
  </main>

  <!-- Add/Edit Product Modal (same as sellerproducts.html) -->
  <div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
      <h3 id="modalTitle" class="font-semibold text-lg mb-4">Add New Product</h3>
      <form id="productForm" class="space-y-4">
        <input type="hidden" id="productId">
        <div>
          <label for="productName" class="block text-sm font-medium text-gray-700">Product Name</label>
          <input type="text" id="productName" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
        </div>
        <div>
          <label for="productCategory" class="block text-sm font-medium text-gray-700">Category</label>
          <select id="productCategory" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none bg-white">
            <!-- Options will be populated by JS -->
          </select>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="productPrice" class="block text-sm font-medium text-gray-700">Price (per unit)</label>
            <input type="number" id="productPrice" step="0.01" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
          </div>
          <div>
            <label for="productUnit" class="block text-sm font-medium text-gray-700">Unit (e.g., kg)</label>
            <input type="text" id="productUnit" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
          </div>
        </div>
        <div>
          <label for="productStock" class="block text-sm font-medium text-gray-700">Stock</label>
          <input type="number" id="productStock" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
        </div>
        <div class="mt-6 flex justify-end gap-3">
          <button type="button" id="closeProductModal" class="px-4 py-2 border rounded-md text-sm">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm">Save Product</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Sort Modal -->
  <div id="sortModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm">
      <h3 class="font-semibold text-lg mb-4">Sort Inventory By</h3>
      <div class="space-y-2">
        <label class="flex items-center"><input type="radio" name="sort" value="name-asc" class="accent-green-600 mr-2" checked> Name (A-Z)</label>
        <label class="flex items-center"><input type="radio" name="sort" value="name-desc" class="accent-green-600 mr-2"> Name (Z-A)</label>
        <label class="flex items-center"><input type="radio" name="sort" value="stock-asc" class="accent-green-600 mr-2"> Stock (Low to High)</label>
        <label class="flex items-center"><input type="radio" name="sort" value="stock-desc" class="accent-green-600 mr-2"> Stock (High to Low)</label>
        <label class="flex items-center"><input type="radio" name="sort" value="updated-desc" class="accent-green-600 mr-2"> Last Updated (Newest)</label>
        <label class="flex items-center"><input type="radio" name="sort" value="updated-asc" class="accent-green-600 mr-2"> Last Updated (Oldest)</label>
      </div>
      <div class="mt-6 flex justify-end gap-3">
        <button id="closeSortModal" class="px-4 py-2 border rounded-md text-sm">Cancel</button>
        <button id="applySort" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm">Apply</button>
      </div>
    </div>
  </div>

  <!-- Filter Modal -->
  <div id="filterModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm">
      <h3 class="font-semibold text-lg mb-4">Filter by Category</h3>
      <div id="categoryFilterContainer" class="space-y-2 max-h-60 overflow-y-auto">
        <!-- Categories will be populated by JS -->
      </div>
      <div class="mt-6 flex justify-between">
        <button id="clearCategoryFilters" class="px-4 py-2 border rounded-md text-sm text-gray-700">Clear</button>
        <div class="flex gap-3">
          <button id="closeFilterModal" class="px-4 py-2 border rounded-md text-sm">Cancel</button>
          <button id="applyCategoryFilters" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm">Apply</button>
        </div>
      </div>
    </div>
  </div>

  <footer class="text-white py-12" style="background-color: #1B5E20;">
    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-4 gap-8">
      
      <!-- Logo/About -->
      <div>
        <h3 class="font-bold text-lg mb-3">Farmers Mall</h3>
        <p class="text-gray-300 text-sm">
          Fresh, organic produce delivered straight to your home from local farmers.
        </p>
      </div>
      
      <!-- Quick Links -->
      <div>
        <h3 class="font-bold text-lg mb-3">Quick Links</h3>
        <ul class="space-y-2 text-sm text-gray-300">
          <li><a href="#" class="hover:underline">About Us</a></li>
          <li><a href="#" class="hover:underline">Contact</a></li>
          <li><a href="#" class="hover:underline">FAQ</a></li>
          <li><a href="#" class="hover:underline">Support</a></li>
        </ul>
      </div>

      <!-- Categories -->
      <div>
        <h3 class="font-bold text-lg mb-3">Categories</h3>
        <ul class="space-y-2 text-sm text-gray-300">
          <li><a href="#" class="hover:underline">Vegetables</a></li>
          <li><a href="#" class="hover:underline">Fruits</a></li>
          <li><a href="#" class="hover:underline">Dairy</a></li>
          <li><a href="#" class="hover:underline">Meat</a></li>
        </ul>
      </div>

      <!-- Social -->
      <div>
        <h3 class="font-bold text-lg mb-3">Follow Us</h3>
        <div class="flex space-x-4 text-xl">
          <a href="#"><i class="fab fa-facebook"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
      </div>
    </div>

    <!-- Divider -->
    <div class="border-t border-green-800 text-center text-gray-400 text-sm mt-10 pt-6">
      © 2025 Farmers Mall. All rights reserved.
    </div>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const productModal = document.getElementById('productModal');
      const closeProductModal = document.getElementById('closeProductModal');
      const productForm = document.getElementById('productForm');
      const modalTitle = document.getElementById('modalTitle');

      const sortModal = document.getElementById('sortModal');
      const filterModal = document.getElementById('filterModal');
      const closeSortModal = document.getElementById('closeSortModal');
      const closeFilterModal = document.getElementById('closeFilterModal');
      const applySort = document.getElementById('applySort');
      const applyCategoryFilters = document.getElementById('applyCategoryFilters');
      const clearCategoryFilters = document.getElementById('clearCategoryFilters');
      const sortBtn = document.getElementById('sortBtn');
      const filterBtn = document.getElementById('filterBtn');
      const filterTabs = document.getElementById('filterTabs');
      const searchInput = document.getElementById('tableSearchInput');

      const inventoryTableBody = document.getElementById('inventoryTableBody');
      const paginationContainer = document.getElementById('paginationContainer');

      const allCategories = [
        'Vegetables',
        'Fruits',
        'Dairy',
        'Meat',
        'Seafood',
        'Bakery'
      ];

      const initialProducts = [
        { id: 1, name: 'Red Onions', category: 'Vegetables', price: 55, unit: 'kg', stock: 5, img: 'seller/red-onion.png', lastUpdated: '2023-10-28T10:00:00Z' },
        { id: 2, name: 'Iceberg Lettuce', category: 'Vegetables', price: 40, unit: 'kg', stock: 42, img: 'seller/lettuce.png', lastUpdated: '2023-10-28T11:00:00Z' },
        { id: 3, name: 'Organic Garlic', category: 'Vegetables', price: 80, unit: 'kg', stock: 0, img: 'seller/garlic.png', lastUpdated: '2023-10-27T09:00:00Z' },
        { id: 4, name: 'Honeycrisp Apples', category: 'Fruits', price: 150, unit: 'kg', stock: 128, img: 'seller/apple.png', lastUpdated: '2023-10-28T12:00:00Z' },
        { id: 5, name: 'Baby Carrots', category: 'Vegetables', price: 70, unit: 'kg', stock: 8, img: 'seller/carrots.png', lastUpdated: '2023-10-28T13:00:00Z' },
        { id: 6, name: 'Roma Tomatoes', category: 'Vegetables', price: 60, unit: 'kg', stock: 67, img: 'seller/tomato.png', lastUpdated: '2023-10-28T14:00:00Z' },
      ];

      let allProducts = JSON.parse(localStorage.getItem('sellerProducts')) || initialProducts;
      if (!localStorage.getItem('sellerProducts')) {
        localStorage.setItem('sellerProducts', JSON.stringify(allProducts));
      }

      let displayedProducts = [...allProducts];
      let currentSort = 'name-asc';
      let currentPage = 1;
      const itemsPerPage = 6;

      const getStatus = (stock) => {
        if (stock === 0) return 'Out of Stock';
        if (stock <= 10) return 'Low Stock';
        return 'In Stock';
      };

      const getStatusClass = (status) => {
        if (status === 'Out of Stock') return 'bg-red-100 text-red-700';
        if (status === 'Low Stock') return 'bg-orange-100 text-orange-600';
        return 'bg-green-100 text-green-600';
      };

      const renderInventory = () => {
        inventoryTableBody.innerHTML = '';
        const startIndex = (currentPage - 1) * itemsPerPage;
        const paginatedProducts = displayedProducts.slice(startIndex, startIndex + itemsPerPage);

        paginatedProducts.forEach(p => {
          const status = getStatus(p.stock);
          const statusClass = getStatusClass(status);
          const tr = document.createElement('tr');
          tr.className = 'hover:bg-gray-50';
          tr.innerHTML = `
            <td class="py-3 px-5 flex items-center gap-2">
              <img src="${p.img || 'seller/default.png'}" class="w-8 h-8 object-cover">
              ${p.name}
            </td>
            <td class="py-3 px-5"><span class="font-medium">${p.stock}</span> ${p.unit}</td>
            <td class="py-3 px-5"><span class="${statusClass} px-2 py-1 rounded-full text-xs font-medium">${status}</span></td>
            <td class="py-3 px-5 text-gray-500">${new Date(p.lastUpdated || Date.now()).toLocaleDateString()}</td>
            <td class="py-3 px-5 text-right">
              <button class="edit-btn text-green-600 hover:text-green-800" data-id="${p.id}"><i class="fa-regular fa-pen-to-square"></i></button>
            </td>
          `;
          inventoryTableBody.appendChild(tr);
        });
        renderPagination();
      };

      const renderPagination = () => {
        paginationContainer.innerHTML = '';
        const totalPages = Math.ceil(displayedProducts.length / itemsPerPage);
        if (totalPages <= 1) return;

        for (let i = 1; i <= totalPages; i++) {
          const pageBtn = document.createElement('button');
          pageBtn.textContent = i;
          pageBtn.className = `border w-8 h-8 rounded-md text-gray-600 hover:bg-gray-100 text-sm ${i === currentPage ? 'bg-green-600 text-white' : ''}`;
          pageBtn.onclick = () => { currentPage = i; renderInventory(); };
          paginationContainer.appendChild(pageBtn);
        }
      };

      const applyFiltersAndSearch = () => {
        const activeFilter = document.querySelector('.filter-tab.bg-green-600').dataset.filter;
        const searchTerm = searchInput.value.toLowerCase();

        // Get selected categories from the filter modal
        const categoryCheckboxes = document.querySelectorAll('#categoryFilterContainer input[type="checkbox"]');
        const selectedCategories = Array.from(categoryCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        let filtered = allProducts.filter(p => {
          const statusMatch = activeFilter === 'All' || getStatus(p.stock) === activeFilter;
          const searchMatch = p.name.toLowerCase().includes(searchTerm);
          const categoryMatch = selectedCategories.length === 0 || selectedCategories.includes(p.category);
          return statusMatch && searchMatch && categoryMatch;
        });

        // Apply sorting
        switch (currentSort) {
            case 'name-asc':
                filtered.sort((a, b) => a.name.localeCompare(b.name));
                break;
            case 'name-desc':
                filtered.sort((a, b) => b.name.localeCompare(a.name));
                break;
            case 'stock-asc':
                filtered.sort((a, b) => a.stock - b.stock);
                break;
            case 'stock-desc':
                filtered.sort((a, b) => b.stock - a.stock);
                break;
            case 'updated-desc':
                filtered.sort((a, b) => new Date(b.lastUpdated) - new Date(a.lastUpdated));
                break;
            case 'updated-asc':
                filtered.sort((a, b) => new Date(a.lastUpdated) - new Date(b.lastUpdated));
                break;
        }
        displayedProducts = filtered;
        currentPage = 1;
        renderInventory();
      };

      // Modal Logic
      const openModal = (modal) => modal.classList.remove('hidden');
      const closeModal = (modal) => modal.classList.add('hidden');

      closeProductModal.addEventListener('click', () => closeModal(productModal));
      
      // Sort and Filter Modal Listeners
      sortBtn.addEventListener('click', () => openModal(sortModal));
      closeSortModal.addEventListener('click', () => closeModal(sortModal));
      filterBtn.addEventListener('click', () => openModal(filterModal));
      closeFilterModal.addEventListener('click', () => closeModal(filterModal));



      // Form submission
      productForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const id = document.getElementById('productId').value;
        const newProductData = {
          id: id ? parseInt(id) : Date.now(),
          name: document.getElementById('productName').value,
          category: document.getElementById('productCategory').value,
          price: parseFloat(document.getElementById('productPrice').value),
          unit: document.getElementById('productUnit').value,
          stock: parseInt(document.getElementById('productStock').value),
          img: 'seller/default.png',
          lastUpdated: new Date().toISOString()
        };

        if (id) {
          allProducts = allProducts.map(p => p.id === newProductData.id ? {...p, ...newProductData} : p);
        } else {
          allProducts.unshift(newProductData);
        }
        localStorage.setItem('sellerProducts', JSON.stringify(allProducts));
        applyFiltersAndSearch();
        closeModal(productModal);
      });

      // Edit button
      inventoryTableBody.addEventListener('click', (e) => {
        const editBtn = e.target.closest('.edit-btn');
        if (editBtn) {
          const id = editBtn.dataset.id;
          const product = allProducts.find(p => p.id == id);
          if (product) {
            modalTitle.textContent = 'Edit Product';
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productCategory').value = product.category;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productUnit').value = product.unit;
            document.getElementById('productStock').value = product.stock;
            openModal(productModal);
          }
        }
      });

      // Filter Tabs Logic
      filterTabs.addEventListener('click', (e) => {
        if (e.target.classList.contains('filter-tab')) {
          document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.classList.remove('bg-green-600', 'text-white');
            tab.classList.add('bg-gray-200', 'text-gray-700');
          });
          e.target.classList.add('bg-green-600', 'text-white');
          e.target.classList.remove('bg-gray-200', 'text-gray-700');
          applyFiltersAndSearch();
        }
      });

      // Search Logic
      searchInput.addEventListener('input', () => {
        applyFiltersAndSearch();
      });
      document.getElementById('tableSearchInput').addEventListener('input', (e) => {
        searchInput.value = e.target.value;
        applyFiltersAndSearch();
      });

      // Sort Logic
      applySort.addEventListener('click', () => {
        currentSort = document.querySelector('input[name="sort"]:checked').value;
        applyFiltersAndSearch();
        closeModal(sortModal);
      });

      // Category Filter Logic
      const populateCategoryFilter = () => {
        const container = document.getElementById('categoryFilterContainer');
        container.innerHTML = '';
        allCategories.forEach(cat => {
          const label = document.createElement('label');
          label.className = 'flex items-center';
          label.innerHTML = `<input type="checkbox" value="${cat}" class="accent-green-600 mr-2"> ${cat}`;
          container.appendChild(label);
        });
      };

      const populateCategoryDropdown = () => {
        const select = document.getElementById('productCategory');
        select.innerHTML = '';
        allCategories.forEach(cat => {
          const option = document.createElement('option');
          option.value = cat;
          option.textContent = cat;
          select.appendChild(option);
        });
      };
      applyCategoryFilters.addEventListener('click', () => {
        applyFiltersAndSearch();
        closeModal(filterModal);
      });

      clearCategoryFilters.addEventListener('click', () => {
        document.querySelectorAll('#categoryFilterContainer input[type="checkbox"]').forEach(cb => {
          cb.checked = false;
        });
        applyFiltersAndSearch();
        closeModal(filterModal);
      });

      // Initial render
      renderInventory();
      populateCategoryFilter();
      populateCategoryDropdown();
    });
  </script>
</body>
</html>
