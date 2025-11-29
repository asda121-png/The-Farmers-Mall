document.addEventListener('DOMContentLoaded', () => {
  // --- Real-time Badge Update ---
  const updateNotificationBadgeOnLoad = () => {
    const count = localStorage.getItem('unreadNotifications');
    const badge = document.querySelector('a[href="retailernotifications.php"] .absolute');
    if (badge) {
      if (count && parseInt(count) > 0) {
        badge.textContent = count;
        badge.style.display = 'inline-flex';
      } else {
        badge.style.display = 'none';
      }
    }
  };

  // Call it on page load
  updateNotificationBadgeOnLoad();

  // --- Load Profile Data from localStorage ---
  const loadProfileData = () => {
    const sellerProfile = JSON.parse(localStorage.getItem('sellerProfile'));
    if (sellerProfile) {
      // Populate inputs for edit mode
      document.getElementById('firstName').value = sellerProfile.firstName || '';
      document.getElementById('lastName').value = sellerProfile.lastName || '';
      document.getElementById('mobileNumber').value = sellerProfile.mobile || '';
      document.getElementById('email').value = sellerProfile.email || '';
      document.getElementById('shopAddress').value = sellerProfile.shopAddress || 'Not provided';
      document.getElementById('shopName').value = sellerProfile.shopName || 'Not provided';

      // --- NEW: Populate sidebar info ---
      document.getElementById('sidebarShopName').textContent = sellerProfile.shopName || 'Shop Name';
      document.getElementById('sidebarEmail').textContent = sellerProfile.email || 'email@example.com';

      // Populate display fields for view mode
      document.getElementById('displayFirstName').textContent = sellerProfile.firstName || 'Not provided';
      document.getElementById('displayLastName').textContent = sellerProfile.lastName || 'Not provided';
      document.getElementById('displayMobileNumber').textContent = sellerProfile.mobile || 'Not provided';
      document.getElementById('displayEmail').textContent = sellerProfile.email || 'Not provided';

      // --- Business Permit Logic ---
      const seePermitBtn = document.getElementById('seePermitBtn');
      const permitModal = document.getElementById('permitModal');
      const closePermitModal = document.getElementById('closePermitModal');
      const permitImageContainer = document.getElementById('permitImageContainer');

      if (sellerProfile.permitImage) {
        seePermitBtn.disabled = false;
        permitImageContainer.innerHTML = `<img src="https://filipinobusinesshub.com/wp-content/uploads/2025/08/Barangay-Business-Permit-Template-and-Sample-4-1-1086x1536.webp" alt="Business Permit" class="w-full max-w-lg h-auto rounded-lg shadow-md border">`;

        seePermitBtn.addEventListener('click', () => {
          if (!seePermitBtn.disabled) {
            permitModal.classList.remove('hidden');
          }
        });
      } else {
        seePermitBtn.disabled = true;
      }

      // Listeners to close the permit modal
      if (permitModal && closePermitModal) {
        closePermitModal.addEventListener('click', () => {
          permitModal.classList.add('hidden');
        });
        permitModal.addEventListener('click', (e) => {
          if (e.target === permitModal) { // Click on overlay
            permitModal.classList.add('hidden');
          }
        });
      }
    }
  };

  // Load data when the page loads
  loadProfileData();


  // --- All button and form logic must be inside the DOMContentLoaded event listener ---
  
  // --- Profile Edit Mode Logic ---
  const editProfileBtn = document.getElementById('editProfileBtn');
  const saveChangesBtn = document.getElementById('saveChangesBtn');
  const logoutBtn = document.getElementById('logoutBtn');
  const firstNameInput = document.getElementById('firstName');
  const lastNameInput = document.getElementById('lastName');
  const mobileNumberInput = document.getElementById('mobileNumber');
  const emailInput = document.getElementById('email');
  const shopNameInput = document.getElementById('shopName');
  const shopAddressInput = document.getElementById('shopAddress');
  const changePictureBtn = document.getElementById('changePictureBtn');
  const imageUpload = document.getElementById('imageUpload');

  const toggleEditMode = (isEditing) => {
    // Toggle personal info inputs
    firstNameInput.disabled = !isEditing;
    lastNameInput.disabled = !isEditing;
    mobileNumberInput.disabled = !isEditing;
    emailInput.disabled = !isEditing;

    // Toggle shop info inputs
    shopNameInput.disabled = !isEditing;
    shopAddressInput.disabled = !isEditing;
    changePictureBtn.disabled = !isEditing;
    imageUpload.disabled = !isEditing; // Disable the hidden file input as well

    // Toggle between display and edit views for info blocks
    document.querySelectorAll('.info-block').forEach(block => {
      const displayView = block.querySelector('.display-view');
      const editView = block.querySelector('.edit-view');
      displayView.classList.toggle('hidden', isEditing);
      editView.classList.toggle('hidden', !isEditing);
    });

    if (isEditing) {
      editProfileBtn.classList.add('hidden');
      saveChangesBtn.classList.remove('hidden');
    } else {
      editProfileBtn.classList.remove('hidden');
      saveChangesBtn.classList.add('hidden');
    }
  };

  // Event listener for Edit Profile button
  if (editProfileBtn) {
    editProfileBtn.addEventListener('click', () => toggleEditMode(true));
  }

  // --- Save Changes Modal Logic ---
  const saveChangesModal = document.getElementById('saveChangesModal');
  const cancelSaveChangesBtn = document.getElementById('cancelSaveChanges');
  const confirmSaveChangesBtn = document.getElementById('confirmSaveChanges');

  // Event listener for Save Changes button (form submission)
  const profileForm = document.querySelector('.space-y-6'); // Assuming this is the form
  if (profileForm && saveChangesModal && cancelSaveChangesBtn && confirmSaveChangesBtn) {
    profileForm.addEventListener('submit', (e) => {
      e.preventDefault(); // Prevent default form submission for now
      saveChangesModal.classList.remove('hidden'); // Show the confirmation modal
    });

    // Hide modal on "Cancel"
    cancelSaveChangesBtn.addEventListener('click', () => {
      saveChangesModal.classList.add('hidden');
    });

    // Perform save on "Save"
    confirmSaveChangesBtn.addEventListener('click', () => {
      // In a real application, you would send data to a server here.
      console.log('Saving changes...');

      saveChangesModal.classList.add('hidden'); // Hide the modal

      // After successful save, switch back to view mode
      toggleEditMode(false);

      // Update display fields with new values from inputs
      document.getElementById('displayFirstName').textContent = firstNameInput.value;
      document.getElementById('displayLastName').textContent = lastNameInput.value;
      document.getElementById('displayMobileNumber').textContent = mobileNumberInput.value;
      document.getElementById('displayEmail').textContent = emailInput.value;

      // Optionally, show a success message
      showNotification('Profile changes saved!', 'success');
    });
  }

  // --- Change Picture Logic ---
  const profileImage = document.getElementById('profileImage');

  if (changePictureBtn && imageUpload && profileImage) {
    changePictureBtn.addEventListener('click', () => { // Only trigger if not disabled
      if (!changePictureBtn.disabled) {
        imageUpload.click(); // Trigger the hidden file input
      }
    });

    imageUpload.addEventListener('change', (event) => {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          // Update the src of the profile image to the selected file
          profileImage.src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // --- Logout Modal Logic ---
  if (logoutBtn && logoutModal) {
    logoutBtn.addEventListener('click', () => {
      logoutModal.classList.remove('hidden');
    });
    
    cancelLogoutBtn.addEventListener('click', () => {
      logoutModal.classList.add('hidden');
    });

    // Close modal if user clicks on the background overlay
    logoutModal.addEventListener('click', (e) => {
      if (e.target === logoutModal) {
        logoutModal.classList.add('hidden');
      }
    });
  }

  // Set initial state to non-editing after all elements are found
  toggleEditMode(false);

  // --- Toast Notification Logic ---
  function showNotification(message, type = 'success') {
    const existing = document.querySelector('.toast-notification');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-600' : 'bg-red-600';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

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
});