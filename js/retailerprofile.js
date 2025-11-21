document.addEventListener('DOMContentLoaded', () => {
  // --- Real-time Badge Update ---
  const updateNotificationBadgeOnLoad = () => {
    const count = localStorage.getItem('unreadNotifications');
    const badge = document.querySelector('a[href="retailernotifications.html"] .absolute');
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


  // --- Profile Edit Mode Logic ---
  const editProfileBtn = document.getElementById('editProfileBtn');
  const saveChangesBtn = document.getElementById('saveChangesBtn');
  const shopNameInput = document.getElementById('shopName');
  const emailInput = document.getElementById('email');
  const changePasswordBtn = document.getElementById('changePasswordBtn');
  const changePictureBtn = document.getElementById('changePictureBtn');
  const imageUpload = document.getElementById('imageUpload');

  const toggleEditMode = (isEditing) => {
    shopNameInput.disabled = !isEditing;
    emailInput.disabled = !isEditing;
    changePasswordBtn.disabled = !isEditing;
    changePictureBtn.disabled = !isEditing;
    imageUpload.disabled = !isEditing; // Disable the hidden file input as well

    if (isEditing) {
      editProfileBtn.classList.add('hidden');
      saveChangesBtn.classList.remove('hidden');
    } else {
      editProfileBtn.classList.remove('hidden');
      saveChangesBtn.classList.add('hidden');
    }
  };

  // Set initial state to non-editing
  toggleEditMode(false);

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
  const logoutModal = document.getElementById('logoutModal');
  const cancelLogoutBtn = document.getElementById('cancelLogout');

  if (logoutBtn && logoutModal && cancelLogoutBtn) {
    logoutBtn.addEventListener('click', (e) => {
      e.preventDefault();
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