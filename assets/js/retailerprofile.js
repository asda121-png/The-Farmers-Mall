document.addEventListener('DOMContentLoaded', function () {
  // Profile Edit Elements
  const editProfileBtn = document.getElementById('editProfileBtn');
  const saveChangesBtn = document.getElementById('saveChangesBtn');
  const infoBlocks = document.querySelectorAll('.info-block');
  const formInputs = document.querySelectorAll('#my-profile form input:not([type="file"])');
  const imageUpload = document.getElementById('imageUpload');
  const changePictureBtn = document.getElementById('changePictureBtn');

  // Profile Data Elements
  const profileImage = document.getElementById('profileImage');
  const sidebarProfilePic = document.getElementById('sidebarProfilePic');
  const shopNameInput = document.getElementById('shopName');
  const sidebarShopName = document.getElementById('sidebarShopName');
  const emailInput = document.getElementById('email');
  const sidebarEmail = document.getElementById('sidebarEmail');

  // Logout Modal Elements
  const logoutBtn = document.getElementById('logoutBtn');
  const logoutModal = document.getElementById('logoutModal');
  const cancelLogout = document.getElementById('cancelLogout');
  const confirmLogout = document.getElementById('confirmLogout');

  // Save Changes Modal Elements
  const saveChangesModal = document.getElementById('saveChangesModal');
  const cancelSaveChanges = document.getElementById('cancelSaveChanges');
  const confirmSaveChanges = document.getElementById('confirmSaveChanges');

  // Business Permit Modal Elements
  const seePermitBtn = document.getElementById('seePermitBtn');
  const permitModal = document.getElementById('permitModal');
  const closePermitModal = document.getElementById('closePermitModal');
  const permitImageContainer = document.getElementById('permitImageContainer');

  let isEditMode = false;

  // --- Main Profile Edit Logic ---

  const toggleEditMode = (enable) => {
    isEditMode = enable;

    // Toggle visibility of display vs. edit views
    infoBlocks.forEach(block => {
      block.querySelector('.display-view').classList.toggle('hidden', enable);
      block.querySelector('.edit-view').classList.toggle('hidden', !enable);
    });

    // Enable/disable form inputs
    formInputs.forEach(input => {
      input.disabled = !enable;
    });
    imageUpload.disabled = !enable;

    // Show/hide buttons
    saveChangesBtn.classList.toggle('hidden', !enable);
    changePictureBtn.classList.toggle('hidden', !enable);

    // Update Edit/Cancel button
    if (enable) {
      editProfileBtn.innerHTML = '<i class="fas fa-times"></i> Cancel';
      editProfileBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
      editProfileBtn.classList.add('bg-gray-500', 'hover:bg-gray-600');
    } else {
      editProfileBtn.innerHTML = '<i class="fas fa-pen"></i> Edit Profile';
      editProfileBtn.classList.remove('bg-gray-500', 'hover:bg-gray-600');
      editProfileBtn.classList.add('bg-green-600', 'hover:bg-green-700');
    }
  };

  editProfileBtn.addEventListener('click', () => {
    // If we are entering edit mode, populate inputs with current values
    if (!isEditMode) {
      infoBlocks.forEach(block => {
        const displayValue = block.querySelector('.display-view p').textContent;
        const inputValue = block.querySelector('.edit-view input');
        if (inputValue) {
          inputValue.value = displayValue;
        }
      });
    }
    toggleEditMode(!isEditMode);
  });

  // --- Image Upload Logic ---

  imageUpload.addEventListener('change', function (event) {
    if (event.target.files && event.target.files[0]) {
      const reader = new FileReader();
      reader.onload = function (e) {
        profileImage.src = e.target.result;
        sidebarProfilePic.src = e.target.result;
      };
      reader.readAsDataURL(event.target.files[0]);
    }
  });

  // --- Save Changes Logic ---

  saveChangesBtn.addEventListener('click', (e) => {
    e.preventDefault(); // Prevent form submission
    saveChangesModal.classList.remove('hidden');
  });

  cancelSaveChanges.addEventListener('click', () => {
    saveChangesModal.classList.add('hidden');
  });

  confirmSaveChanges.addEventListener('click', () => {
    // Update display views with values from input fields
    infoBlocks.forEach(block => {
      const displayPara = block.querySelector('.display-view p');
      const inputValue = block.querySelector('.edit-view input').value;
      if (displayPara) {
        displayPara.textContent = inputValue;
      }
    });

    // Update sidebar info
    sidebarShopName.textContent = shopNameInput.value;
    sidebarEmail.textContent = emailInput.value;

    // Hide modal and switch back to view mode
    saveChangesModal.classList.add('hidden');
    toggleEditMode(false);
    // Here you would typically send the data to the server via an API call (e.g., using fetch)
    console.log('Changes saved (simulated).');
  });

  // --- Logout Modal Logic ---

  logoutBtn.addEventListener('click', () => {
    logoutModal.classList.remove('hidden');
  });

  cancelLogout.addEventListener('click', () => {
    logoutModal.classList.add('hidden');
  });

  // The confirmLogout button is an <a> tag, so it will navigate automatically.
  // If you needed to do something before logging out (like an API call),
  // you would change it to a <button> and handle the redirect in JS.
  // For example:
  // confirmLogout.addEventListener('click', () => {
  //   window.location.href = '../auth/login.php';
  // });

  // --- Business Permit Modal Logic ---

  seePermitBtn.addEventListener('click', () => {
    // In a real application, you would fetch the permit URL from the user's data.
    // For this example, we'll use a placeholder image.
    const permitUrl = 'https://via.placeholder.com/800x1100.png?text=Business+Permit+Sample';

    // Clear previous content and show loading state if needed
    permitImageContainer.innerHTML = '<p class="text-center text-gray-500">Loading permit...</p>';
    permitModal.classList.remove('hidden');

    // Create and load the image
    const img = new Image();
    img.onload = function () {
      permitImageContainer.innerHTML = ''; // Clear loading text
      permitImageContainer.appendChild(img);
    };
    img.onerror = function () {
      permitImageContainer.innerHTML = '<p class="text-center text-red-500">Could not load business permit.</p>';
    };
    img.src = permitUrl;
    img.alt = 'Business Permit';
    img.className = 'w-full h-auto rounded-md';
  });

  const closePermit = () => {
    permitModal.classList.add('hidden');
    permitImageContainer.innerHTML = ''; // Clean up
  };

  closePermitModal.addEventListener('click', closePermit);

  // Close modal if clicking outside the content area
  permitModal.addEventListener('click', (event) => {
    if (event.target === permitModal) {
      closePermit();
    }
  });

  // --- Initial Data Load (Simulation) ---
  // In a real app, you'd fetch this data from a server/API and populate the fields.
  const loadInitialData = () => {
    const userData = {
      shopName: "Green Valley Organics",
      shopAddress: "Mati, Davao Oriental",
      firstName: "Juan",
      lastName: "Dela Cruz",
      mobileNumber: "09123456789",
      email: "juan.delacruz@example.com",
      profilePic: "https://randomuser.me/api/portraits/men/32.jpg",
      hasPermit: true,
    };

    // Populate sidebar
    document.getElementById('sidebarProfilePic').src = userData.profilePic;
    document.getElementById('sidebarShopName').textContent = userData.shopName;
    document.getElementById('sidebarEmail').textContent = userData.email;

    // Populate main profile
    document.getElementById('profileImage').src = userData.profilePic;
    document.getElementById('shopName').value = userData.shopName;
    document.getElementById('shopAddress').value = userData.shopAddress;
    document.getElementById('displayFirstName').textContent = userData.firstName;
    document.getElementById('displayLastName').textContent = userData.lastName;
    document.getElementById('displayMobileNumber').textContent = userData.mobileNumber;
    document.getElementById('displayEmail').textContent = userData.email;

    // Enable or disable permit button
    document.getElementById('seePermitBtn').disabled = !userData.hasPermit;
  };

  loadInitialData();
});