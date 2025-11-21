document.addEventListener('DOMContentLoaded', () => {
  // --- Elements ---
  const retailersTableBody = document.getElementById('retailersTableBody');
  const searchInput = document.getElementById('searchInput');
  const addRetailerBtn = document.getElementById('addRetailerBtn');
  const retailerModal = document.getElementById('retailerModal');
  const closeRetailerModal = document.getElementById('closeRetailerModal');
  const retailerForm = document.getElementById('retailerForm');
  const modalTitle = document.getElementById('modalTitle');

  // --- Data ---
  const initialRetailers = [
    { id: 1, storeName: 'Mesa Farm', email: 'mesafarm@example.com', status: 'Verified', joinedDate: '2025-10-20', avatar: 'men/32.jpg' },
    { id: 2, storeName: 'Davao Bees', email: 'davaobees@example.com', status: 'Pending', joinedDate: '2025-10-22', avatar: 'women/10.jpg' },
    { id: 3, storeName: 'Baguio Greens', email: 'baguio.greens@example.com', status: 'Suspended', joinedDate: '2025-09-15', avatar: 'men/15.jpg' }
  ];
  let allRetailers = JSON.parse(localStorage.getItem('adminRetailers')) || initialRetailers;

  // --- Functions ---
  const saveAndRender = () => {
    localStorage.setItem('adminRetailers', JSON.stringify(allRetailers));
    renderRetailers();
  };

  const getStatusBadge = (status) => {
    const classes = {
      'Verified': 'bg-green-100 text-green-700',
      'Pending': 'bg-yellow-100 text-yellow-700',
      'Suspended': 'bg-red-100 text-red-700'
    };
    return `<span class="${classes[status] || 'bg-gray-100 text-gray-700'} px-2 py-1 rounded-full text-xs font-medium">${status}</span>`;
  };

  const getActionButtons = (retailer) => {
    if (retailer.status === 'Pending') {
      return `<button class="action-btn text-green-600 hover:underline" data-action="approve" data-id="${retailer.id}">Approve</button>
                  <button class="action-btn text-red-600 hover:underline" data-action="deny" data-id="${retailer.id}">Deny</button>`;
    }
    if (retailer.status === 'Verified') {
      return `<button class="action-btn text-blue-600 hover:underline" data-action="edit" data-id="${retailer.id}">Edit</button>
                  <button class="action-btn text-red-600 hover:underline" data-action="suspend" data-id="${retailer.id}">Suspend</button>`;
    }
    if (retailer.status === 'Suspended') {
      return `<button class="action-btn text-green-600 hover:underline" data-action="approve" data-id="${retailer.id}">Re-approve</button>`;
    }
    return '';
  };

  const renderRetailers = () => {
    const searchTerm = searchInput.value.toLowerCase();
    const filteredRetailers = allRetailers.filter(r =>
      r.storeName.toLowerCase().includes(searchTerm) ||
      r.email.toLowerCase().includes(searchTerm)
    );

    retailersTableBody.innerHTML = '';
    if (filteredRetailers.length === 0) {
      retailersTableBody.innerHTML = '<tr><td colspan="5" class="text-center p-8 text-gray-500">No retailers found.</td></tr>';
      return;
    }

    filteredRetailers.forEach(retailer => {
      const tr = document.createElement('tr');
      tr.className = 'hover:bg-gray-50';
      tr.innerHTML = `
            <td class="p-4 flex items-center gap-3">
              <img src="https://randomuser.me/api/portraits/${retailer.avatar}" class="w-8 h-8 rounded-full">
              ${retailer.storeName}
            </td>
            <td class="p-4">${retailer.email}</td>
            <td class="p-4">${getStatusBadge(retailer.status)}</td>
            <td class="p-4">${new Date(retailer.joinedDate).toLocaleDateString()}</td>
            <td class="p-4 space-x-2">${getActionButtons(retailer)}</td>
          `;
      retailersTableBody.appendChild(tr);
    });
  };

  const openModalForEdit = (id) => {
    const retailer = allRetailers.find(r => r.id == id);
    if (!retailer) return;
    modalTitle.textContent = 'Edit Retailer';
    document.getElementById('retailerId').value = retailer.id;
    document.getElementById('storeName').value = retailer.storeName;
    document.getElementById('retailerEmail').value = retailer.email;
    retailerModal.classList.remove('hidden');
  };

  // --- Event Listeners ---
  searchInput.addEventListener('input', renderRetailers);

  addRetailerBtn.addEventListener('click', () => {
    modalTitle.textContent = 'Add New Retailer';
    retailerForm.reset();
    document.getElementById('retailerId').value = '';
    retailerModal.classList.remove('hidden');
  });

  closeRetailerModal.addEventListener('click', () => retailerModal.classList.add('hidden'));

  retailerForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const id = document.getElementById('retailerId').value;
    const newRetailerData = {
      storeName: document.getElementById('storeName').value,
      email: document.getElementById('retailerEmail').value,
    };

    if (id) { // Editing
      allRetailers = allRetailers.map(r => r.id == id ? { ...r, ...newRetailerData } : r);
    } else { // Adding
      const newRetailer = {
        id: Date.now(),
        status: 'Pending',
        joinedDate: new Date().toISOString().split('T')[0],
        avatar: (Math.random() > 0.5 ? 'men' : 'women') + `/${Math.floor(Math.random() * 50)}.jpg`,
        ...newRetailerData
      };
      allRetailers.unshift(newRetailer);
    }
    retailerModal.classList.add('hidden');
    saveAndRender();
  });

  retailersTableBody.addEventListener('click', (e) => {
    const target = e.target;
    if (!target.classList.contains('action-btn')) return;

    const action = target.dataset.action;
    const id = target.dataset.id;
    const retailer = allRetailers.find(r => r.id == id);
    if (!retailer) return;

    switch (action) {
      case 'edit':
        openModalForEdit(id);
        break;
      case 'approve':
        retailer.status = 'Verified';
        saveAndRender();
        break;
      case 'deny':
      case 'suspend':
        retailer.status = 'Suspended';
        saveAndRender();
        break;
    }
  });

  // --- Logout Modal Logic ---
  const logoutButton = document.getElementById('logoutButton');
  const logoutModal = document.getElementById('logoutModal');
  const cancelLogoutBtn = document.getElementById('cancelLogout');
  logoutButton.addEventListener('click', (e) => {
    e.preventDefault();
    logoutModal.classList.remove('hidden');
  });
  cancelLogoutBtn.addEventListener('click', () => logoutModal.classList.add('hidden'));
  logoutModal.addEventListener('click', (e) => {
    if (e.target === logoutModal) logoutModal.classList.add('hidden');
  });

  // --- Initial Render ---
  saveAndRender();
});