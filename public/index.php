<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Farmers Mall – Landing</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Font Awesome (same as your working footer) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

 <style>
   html {
     scroll-behavior: smooth;
     scroll-padding-top: 100px; /* Prevents header overlap */
   }

   /* Optional: Add smooth hover for links */
   nav a {
     transition: color 0.2s ease;
   }

   nav a:hover {
     color: #15803d;
   }

   /* START: Login Modal Styles */
   .login-modal {
     transition: opacity 0.5s ease, visibility 0.5s ease, backdrop-filter 0.5s ease;
     backdrop-filter: blur(0px);
   }
   .login-modal.hidden {
     opacity: 0;
     visibility: hidden;
   }GI
   .login-modal:not(.hidden) {
     backdrop-filter: blur(4px); /* Corresponds to backdrop-blur-sm */
   }
   .login-modal .modal-content {
     transition: transform 0.5s cubic-bezier(0.165, 0.84, 0.44, 1), opacity 0.4s ease-out;
     transform: translateY(20px) scale(0.98);
     opacity: 0;
   }
   .login-modal:not(.hidden) .modal-content {
     transform: translateY(0) scale(1);
     opacity: 1;
   }
   .input-focus:focus-within {
      box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.2);
      border-color: #15803d;
    }
    @keyframes float {
      0% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
      100% { transform: translateY(0px); }
   }

   /* Centered Notification */
   .centered-notification {
     position: fixed;
     top: 50%;
     left: 50%;
     transform: translate(-50%, -50%) scale(0.9);
     opacity: 0;
     transition: all 0.3s ease-out;
     z-index: 101; /* Higher than modals */
     padding: 1rem 1.5rem;
     border-radius: 0.5rem;
     box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
     display: flex;
     align-items: center;
     gap: 0.75rem;
     color: white;
   }
   /* END: Login Modal Styles */

   /* START: Register Modal Styles */
   .register-modal {
     transition: opacity 0.5s ease, visibility 0.5s ease, backdrop-filter 0.5s ease;
     backdrop-filter: blur(0px);
   }
   .register-modal.hidden {
     opacity: 0;
     visibility: hidden;
   }
   .register-modal:not(.hidden) {
     backdrop-filter: blur(4px);
   }
   .register-modal .modal-content {
     transition: transform 0.5s cubic-bezier(0.165, 0.84, 0.44, 1), opacity 0.4s ease-out;
     transform: translateY(20px) scale(0.98);
     opacity: 0;
   }
   .register-modal:not(.hidden) .modal-content {
     transform: translateY(0) scale(1);
     opacity: 1;
   }
   .progress-bar-fill {
     transition: width 0.4s ease-in-out;
   }
   .form-step {
     transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
   }
   .form-step.hidden {
     display: none;
   }
   /* Field Error Styles */
   .input-error {
       border-color: #dc2626 !important;
   }
   .error-message {
       color: #dc2626;
       font-size: 0.75rem;
       margin-top: 0.25rem;
       display: block;
       min-height: 1.25rem;
   }
   /* END: Login Modal Styles */

</style>

<!-- START: Active Nav Link Script -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // These are the classes we'll toggle
    const activeClasses = 'text-green-700 font-bold';
    const inactiveClasses = 'text-gray-700 font-medium';
    
    // Get all the links inside your main <nav>
    // We select the 'a' tags directly for simplicity
    const navLinks = document.querySelectorAll('nav ul li a');

    function highlightActiveLink() {
      // Get the current URL's hash (e.g., "#about")
      const currentHash = window.location.hash;
      // Get the current URL's pathname (e.g., "/index.php" or "/")
      const currentPath = window.location.pathname.split('/').pop() || 'index.php';

      let homeLink = null;
      let bestMatch = null;

      navLinks.forEach(link => {
        const linkPath = link.pathname.split('/').pop() || 'index.php';
        const linkHash = link.hash;

        // Reset all links to the inactive style first
        link.classList.remove(...activeClasses.split(' '));
        // Ensure inactive classes are present
        if (!link.classList.contains(inactiveClasses.split(' ')[0])) {
             link.classList.add(...inactiveClasses.split(' '));
        }

        // Find the 'Home' link (no hash, points to index.php)
        if (linkPath === 'index.php' && !linkHash) {
          homeLink = link;
        }

        // Check for a match
        if (linkPath === currentPath) {
          if (linkHash === currentHash) {
            // Perfect match (e.g., index.php#about)
            bestMatch = link;
          }
        }
      });

      // If no hash match (e.g., user is just on "index.php")
      // highlight the 'Home' link.
      if (!bestMatch && currentPath === 'index.php' && homeLink && !currentHash) {
        bestMatch = homeLink;
      }

      // If we found a link to highlight...
      if (bestMatch) {
        bestMatch.classList.remove(...inactiveClasses.split(' '));
        bestMatch.classList.add(...activeClasses.split(' '));
      } else if (homeLink) {
        // Default to highlighting Home if no other match (e.g. on page load with no hash)
        // but only if there's no hash
        if (!currentHash) {
           homeLink.classList.remove(...inactiveClasses.split(' '));
           homeLink.classList.add(...activeClasses.split(' '));
        }
      }
    }

    // Run the function when the page loads
    highlightActiveLink();
    
    // Run the function again if the user clicks an anchor link (e.g., #about)
    window.addEventListener('hashchange', highlightActiveLink);

    // --- Hero Animation ---
    // Select the elements we want to animate
    const heroH1 = document.getElementById('hero-h1');
    const heroP = document.getElementById('hero-p');
    const heroA = document.getElementById('hero-a');

    // Create an array of elements and their delays
    const elementsToAnimate = [
      { el: heroH1, delay: 200 },
      { el: heroP, delay: 400 },
      { el: heroA, delay: 600 }
    ];

    // Loop through them and apply the animation after a short delay
    elementsToAnimate.forEach(item => {
      if (item.el) {
        // Check if the element is already visible (e.g., if page was reloaded and animation already ran)
        // This prevents re-animating on hashchange if the element is already in its final state
        if (!item.el.classList.contains('opacity-0')) {
          return; 
        }

        // If the element is part of the hero section and the page is not at the top,
        // we might want to skip the animation or adjust it.
        // For now, we'll assume the animation should always run if the element is hidden.
        // If you want to prevent animation on scroll, you'd need an IntersectionObserver.

        // If the element is hidden, animate it
        if (item.el.classList.contains('opacity-0')) {
          setTimeout(() => {
            item.el.classList.remove('opacity-0', 'translate-y-5');
          }, item.delay);
        }
      } else { // If element doesn't exist, still wait for its delay to keep timing consistent
        setTimeout(() => {
          // Removing these classes triggers the transition
          item.el.classList.remove('opacity-0', 'translate-y-5');
        }, item.delay);
      }
    });

    // --- START: Login Modal Logic ---
    const loginModal = document.getElementById('loginModal');
    const loginForm = document.getElementById('loginForm');
    const loginEmailError = document.getElementById('loginEmailError');
    const loginPasswordError = document.getElementById('loginPasswordError');
    const registerModal = document.getElementById('registerModal');
    const registerForm = document.getElementById('registerForm');
    const closeRegisterBtn = document.getElementById('closeRegisterModal');
    let sentVerificationCode = null; // Stores the OTP for client-side validation (dev only)
    let verificationCodeSent = false; // Flag to indicate if code was sent


    // Function to open the modal
    function openLoginModal() {
      if (loginModal) {
        loginModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
      }
    }
    
    // Function to open the register modal
    function openRegisterModal() {
      if (registerModal) {
        registerModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
      }
    }

    // Function to close the modal
    function closeLoginModal() {
      if (loginModal) {
        loginModal.classList.add('hidden');
        document.body.style.overflow = ''; // Restore scrolling
      }
    }

    // Function to close the register modal
    function closeRegisterModal() {
      if (registerModal) {
        registerModal.classList.add('hidden');
        document.body.style.overflow = ''; // Restore scrolling
      }
    }

    // Find all login/register links and attach event listeners
    document.querySelectorAll('a[href*="login.php"], a[href*="register.php"]').forEach(link => {
      link.addEventListener('click', function(e) {
        // Check if the link is for login specifically
        if (this.href.includes('login.php')) {
          e.preventDefault(); // Prevent navigating to login.php
          openLoginModal();
        }
        if (this.href.includes('register.php')) {
          e.preventDefault(); // Prevent navigating to register.php
          openRegisterModal();
        }
      });
    });

    // Close modal when clicking on the background overlay
    if (loginModal) {
      loginModal.addEventListener('click', function(e) {
        if (e.target === loginModal) {
          closeLoginModal();
        }
      });
    }

    // Close modal with the close button
    const closeLoginBtn = document.getElementById('closeLoginModal');
    if (closeLoginBtn) {
      closeLoginBtn.addEventListener('click', closeLoginModal);
    }

    // Close register modal when clicking on the background overlay
    if (registerModal) {
      registerModal.addEventListener('click', function(e) {
        if (e.target === registerModal) {
          closeRegisterModal();
        }
      });
    }

    // Close register modal with the close button
    if (closeRegisterBtn) {
      closeRegisterBtn.addEventListener('click', closeRegisterModal);
    }

    // Handle form submission via AJAX
    if (loginForm) {
      loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        // The submission logic is now handled by the script inside the modal HTML itself.
      });
    }
    if (registerForm) {
      registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        // The submission logic is now handled by the script inside the modal HTML itself.
      });
    }

    // --- START: Modal Switching Logic ---
    // Handle clicking "Log In" from within the register modal
    const switchToLoginLink = document.querySelector('#registerModal a[href*="login.php"]');
    if (switchToLoginLink) {
      switchToLoginLink.addEventListener('click', function(e) {
        e.preventDefault();
        // Close the current (register) modal first
        closeRegisterModal();
        // Open the login modal after a short delay to allow the close animation to start
        setTimeout(openLoginModal, 150); 
      });
    }
    // --- END: Modal Switching Logic ---

    // --- START: Login Form AJAX ---
    const loginSubmitBtn = document.getElementById('loginSubmitBtn');
    if (loginForm) {
      loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(loginForm);
        const btnText = loginSubmitBtn.querySelector('.btn-text');

        // Reset errors
        document.getElementById('loginEmailError').classList.add('hidden');
        document.getElementById('loginPasswordError').classList.add('hidden');

        // Show loading state
        loginSubmitBtn.disabled = true;
        btnText.textContent = 'Logging in...';

        fetch('../auth/login.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            showToast(data.message, 'success');
            if (data.redirect_url) {
              window.location.href = `../public/loading.php?redirect_to=${encodeURIComponent(data.redirect_url)}`;
            }
          } else {
            showToast(data.message, 'error');
            if (data.message === 'Invalid email/username or password.') {
                document.getElementById('loginEmailError').classList.remove('hidden');
                document.getElementById('loginPasswordError').classList.remove('hidden');
            }
          }
        })
        .catch(error => {
          console.error('Login Error:', error);
          showToast('A network error occurred. Please try again.', 'error');
        })
        .finally(() => {
          loginSubmitBtn.disabled = false;
          btnText.textContent = 'Login';
        });
      });
    }
    // --- END: Login Form AJAX ---

    // --- START: Registration Form Logic ---
    if (registerForm) {
      // ====== VALIDATION REGEX PATTERNS ======
      const validationPatterns = {
        name: /^[a-zA-Z\s\-']{2,50}$/,
        username: /^[a-zA-Z0-9_-]{3,20}$/,
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        phone: /^09\d{9}$/,
        password: /^(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{6,}$/,
        street: /^[a-zA-Z0-9\s,.\-]{3,100}$/,
        otp: /^\d{4,6}$/
      };

      // ====== VALIDATION ERROR MESSAGES ======
      const errorMessages = {
        firstname: "First name must contain only letters and be 2-50 characters",
        lastname: "Last name must contain only letters and be 2-50 characters",
        middlename: "Middle name must contain only letters and be 2-50 characters",
        suffix: "Suffix must contain only letters and be 2-50 characters",
        username: "Username must be 3-20 characters (letters, numbers, _, -)",
        email: "Please enter a valid email address",
        phone: "Phone must be in format 09XXXXXXXXX (11 digits)",
        password: "Password must be 6+ characters with at least one number and symbol (!@#$%^&* etc)",
        confirm: "Passwords do not match",
        street: "Street address must be 3-100 characters",
        barangay: "Please select a barangay",
        otp: "Verification code must be 4-6 digits"
      };

      // ====== VALIDATION FUNCTIONS ======
      function validateField(fieldId, fieldType) {
        const field = document.getElementById(fieldId);
        const errorElement = document.getElementById(`${fieldId}-error`);
        if (!field) return true;
        const value = field.value.trim();
        const isRequired = field.hasAttribute('required');
        if (!isRequired && !value) { clearFieldError(fieldId); return true; }
        if (isRequired && !value) { setFieldError(fieldId, `${field.name || fieldId} is required`); return false; }
        if (validationPatterns[fieldType] && value) {
          if (!validationPatterns[fieldType].test(value)) { setFieldError(fieldId, errorMessages[fieldId] || "Invalid format"); return false; }
        }
        if (fieldId === 'confirm') {
          const passwordValue = document.getElementById('password').value;
          if (value !== passwordValue) { setFieldError(fieldId, errorMessages.confirm); return false; }
        }
        clearFieldError(fieldId);
        return true;
      }

      function setFieldError(fieldId, message) {
        const field = document.getElementById(fieldId);
        const errorElement = document.getElementById(`${fieldId}-error`);
        if (field) field.parentElement.classList.add('input-error');
        if (errorElement) { errorElement.textContent = message; errorElement.classList.remove('hidden'); }
      }

      function clearFieldError(fieldId) {
        const field = document.getElementById(fieldId);
        const errorElement = document.getElementById(`${fieldId}-error`);
        if (field) field.parentElement.classList.remove('input-error');
        if (errorElement) { errorElement.textContent = ''; errorElement.classList.add('hidden'); }
      }

      // ====== PASSWORD STRENGTH METER ======
      function calculatePasswordStrength(password) {
        let strength = 0;
        if (!password) return { strength: 0, text: 'Weak', color: '#ef4444' };
        if (password.length >= 8) strength += 2;
        if (/\d/.test(password)) strength += 1;
        if (/[a-z]/.test(password)) strength += 1;
        if (/[A-Z]/.test(password)) strength += 1;
        if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) strength += 2;
        let strengthLevel = 'Weak', color = '#ef4444';
        if (strength <= 2) { strengthLevel = 'Weak'; color = '#ef4444'; }
        else if (strength <= 4) { strengthLevel = 'Medium'; color = '#f59e0b'; }
        else if (strength <= 6) { strengthLevel = 'Strong'; color = '#10b981'; }
        else { strengthLevel = 'Very Strong'; color = '#059669'; }
        return { strength: Math.min((strength / 8) * 100, 100), text: strengthLevel, color: color };
      }

      function updatePasswordStrength(password) {
        const strengthDiv = document.getElementById('password-strength');
        const strengthBar = document.getElementById('strength-bar');
        const strengthText = document.getElementById('strength-text');
        if (!strengthDiv) return;
        const strength = calculatePasswordStrength(password);
        if (!password) { strengthDiv.classList.add('hidden'); return; }
        strengthDiv.classList.remove('hidden');
        strengthBar.style.width = strength.strength + '%';
        strengthBar.style.backgroundColor = strength.color;
        strengthText.textContent = strength.text;
        strengthText.style.color = strength.color;
      }

      // ====== FORM MULTI-STEP LOGIC ======
      const steps = Array.from(document.querySelectorAll('.form-step'));
      const progressBar = document.getElementById('progress-bar');
      const stepNameEl = document.getElementById('step-name');
      const stepCurrentEl = document.getElementById('step-current');
      const otpInput = document.getElementById('otp');
      const prevBtn = document.getElementById('prevBtn');
      const nextBtn = document.getElementById('nextBtn');
      const submitBtn = document.getElementById('submitBtn');
      const termsContainer = document.getElementById('terms-container');
      const barangaySelect = document.getElementById('barangay');
      let currentStep = 0;
      const stepNames = ["Personal Info", "Address", "Account Details", "Verification", "Finalize"];
      const matiBarangays = [ "Badas", "Bobon", "Buso", "Cawayanan", "Central", "Dahican", "Danao", "Dawan", "Don Enrique Lopez", "Don Martin Marundan", "Don Salvador Lopez Sr.", "Langka", "Lantawan", "Lawigan", "Libudon", "Luban", "Macambol", "Magsaysay", "Manay", "Matiao", "New Bataan", "New Libudon", "Old Macambol", "Poblacion", "Sainz", "San Isidro", "San Roque", "Tagabakid", "Tagbinonga", "Taguibo", "Tamisan" ];

      function populateBarangays() { if (!barangaySelect) return; barangaySelect.innerHTML = '<option value="">Select Barangay</option>'; matiBarangays.sort().forEach(b => { const o = document.createElement('option'); o.value = b; o.textContent = b; barangaySelect.appendChild(o); }); }
      function updateProgress() { steps.forEach(s => s.classList.add('hidden')); steps[currentStep].classList.remove('hidden'); progressBar.style.width = `${((currentStep + 1) / steps.length) * 100}%`; stepNameEl.textContent = stepNames[currentStep]; stepCurrentEl.textContent = currentStep + 1; }
      function goToStep(stepIndex) { steps[currentStep].classList.add('hidden'); currentStep = stepIndex; updateProgress(); updateButtons(); }
      function updateButtons() { const isLastStep = currentStep === steps.length - 1; termsContainer.classList.toggle('hidden', !isLastStep); prevBtn.classList.toggle('hidden', currentStep === 0); nextBtn.classList.toggle('hidden', isLastStep); submitBtn.classList.toggle('hidden', !isLastStep); if (currentStep === 3) { nextBtn.disabled = !isOtpValid(); } }
      function validateStep(stepIndex) { const inputs = steps[stepIndex].querySelectorAll('input[required], select[required]'); let isValid = true; inputs.forEach(i => { const id = i.id; const type = getFieldValidationType(id); if (!validateField(id, type)) isValid = false; }); if (stepIndex === 3) { if (!verificationCodeSent) { showToast("Please send a verification code first.", 'error'); return false; } if (!isOtpValid()) { showToast("Please enter a valid verification code.", 'error'); return false; } } if (stepIndex === 4) { const terms = document.getElementById('terms'); if (!terms || !terms.checked) { showToast("You must agree to the terms and conditions.", 'error'); return false; } } if (!isValid && stepIndex !== 3) { showToast("Please fix the highlighted errors.", 'error'); } return isValid; }
      function getFieldValidationType(id) { const map = { 'firstname': 'name', 'lastname': 'name', 'middlename': 'name', 'suffix': 'name', 'username': 'username', 'email': 'email', 'phone': 'phone', 'password': 'password', 'confirm': 'password', 'street': 'street', 'otp': 'otp', 'barangay': 'barangay' }; return map[id] || 'text'; }
      function isOtpValid() { return verificationCodeSent && otpInput.value.trim().length >= 4 && (sentVerificationCode === null || otpInput.value.trim() === sentVerificationCode); }

      nextBtn.addEventListener('click', () => { if (validateStep(currentStep)) { if (currentStep < steps.length - 1) goToStep(currentStep + 1); } });
      prevBtn.addEventListener('click', () => { if (currentStep > 0) goToStep(currentStep - 1); });

      registerForm.addEventListener('submit', function(e) { e.preventDefault(); if (validateStep(currentStep)) { const formData = new FormData(this); formData.append('register_submitted', '1'); submitBtn.disabled = true; submitBtn.textContent = 'Registering...'; fetch('../auth/register.php', { method: 'POST', body: formData }).then(r => r.json()).then(d => { if (d.status === 'success') { showToast(d.message, 'success'); setTimeout(() => window.location.href = d.redirect, 2000); } else { showToast(d.message, 'error'); submitBtn.disabled = false; submitBtn.textContent = 'Sign Up'; } }).catch(err => { showToast('A network error occurred.', 'error'); submitBtn.disabled = false; submitBtn.textContent = 'Sign Up'; }); } });

      populateBarangays(); updateProgress(); updateButtons();
      document.getElementById('togglePassword')?.addEventListener('click', function() { const i = document.getElementById('password'); const icon = this.querySelector('i'); i.type = i.type === 'password' ? 'text' : 'password'; icon.classList.toggle('fa-eye'); icon.classList.toggle('fa-eye-slash'); });
      document.getElementById('toggleConfirm')?.addEventListener('click', function() { const i = document.getElementById('confirm'); const icon = this.querySelector('i'); i.type = i.type === 'password' ? 'text' : 'password'; icon.classList.toggle('fa-eye'); icon.classList.toggle('fa-eye-slash'); });
      document.getElementById('password')?.addEventListener('input', (e) => updatePasswordStrength(e.target.value));

      // --- START: Verification Code Logic ---
      const sendVerificationBtn = document.getElementById('sendVerificationBtn');
      const verificationMessage = document.getElementById('verificationMessage');
      const emailInput = document.getElementById('email');
      const resendBtn = document.getElementById('resendVerificationBtn');
      const otpInputField = document.getElementById('otp');

      if (currentStep === 3) { nextBtn.disabled = true; }

      if (sendVerificationBtn) {
        sendVerificationBtn.addEventListener('click', function() {
          const email = emailInput.value.trim();
          if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showToast('Please enter a valid email address.', 'error'); return; }
          this.disabled = true; this.textContent = 'Sending...'; verificationMessage.classList.add('hidden');
          fetch('../auth/verify-email.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ email: email }) })
          .then(response => response.json())
          .then(data => {
            showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) {
              verificationCodeSent = true; sentVerificationCode = data.code || null;
              showCenteredNotification("Please check your email for the verification code.", 'success');
              this.textContent = 'Code Sent!'; this.classList.add('opacity-50', 'cursor-not-allowed');
              resendBtn.classList.remove('hidden');
              verificationMessage.textContent = data.message; verificationMessage.classList.remove('hidden');
              otpInputField.focus();
            } else {
              this.disabled = false; this.textContent = 'Send Verification Code';
              verificationMessage.textContent = data.message; verificationMessage.classList.remove('hidden');
            }
          });
        });
      }

      if (resendBtn) {
        resendBtn.addEventListener('click', function(e) { e.preventDefault(); if (sendVerificationBtn) { sendVerificationBtn.click(); showToast('Sending new verification code...', 'success'); } });
      }

      if (otpInputField) {
        otpInputField.addEventListener('input', () => {
          const otp = otpInputField.value.trim();
          if (isOtpValid()) {
            nextBtn.disabled = false; clearFieldError('otp');
          } else {
            nextBtn.disabled = true;
            if (otp.length > 0) setFieldError('otp', 'Invalid or incomplete verification code.');
          }
        });
      }
    }
    // --- END: Registration Form Logic ---

    // --- START: Auto-open Login Modal on Redirect ---
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('registered') && urlParams.get('registered') === 'success') {
        // A slight delay to ensure the page is fully rendered before the modal pops up
        setTimeout(() => {
            openLoginModal();
            // Optional: remove the query parameter from the URL without reloading the page
            const newUrl = window.location.pathname;
            window.history.pushState({path: newUrl}, '', newUrl);
        }, 500);
    }
    // --- END: Auto-open Login Modal on Redirect ---
    // --- END: Login Modal Logic ---
  });

  // Centered Notification Function
  function showCenteredNotification(message, type = 'success') {
    const existing = document.querySelector('.centered-notification');
    if (existing) existing.remove();

    const notification = document.createElement('div');
    notification.className = `centered-notification ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
    notification.innerHTML = `
      <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} text-xl"></i>
      <span>${message}</span>
    `;
    document.body.appendChild(notification);

    setTimeout(() => notification.classList.add('show'), 10);
    setTimeout(() => {
      notification.classList.remove('show');
      setTimeout(() => notification.remove(), 300);
    }, 4000); // Display for 4 seconds
  }

  // Global Toast function
  function showToast(message, type = 'error') {
    const toastContainer = document.getElementById('toast-container');
    if (!toastContainer) return;
    const toast = document.createElement('div');
    const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-circle-exclamation';
    const bgColor = type === 'success' ? 'bg-green-600' : 'bg-red-600';
    toast.className = `flex items-center gap-3 p-4 mb-3 text-white rounded-lg shadow-lg ${bgColor} transform transition-all duration-300 opacity-0 translate-x-10`;
    toast.innerHTML = `<i class="fas ${iconClass}"></i><span>${message}</span>`;
    toastContainer.appendChild(toast);
    setTimeout(() => { toast.classList.remove('opacity-0', 'translate-x-10'); }, 10);
    setTimeout(() => {
      toast.classList.add('opacity-0');
      setTimeout(() => toast.remove(), 500);
    }, 3500);
  }
</script>
<!-- END: Active Nav Link Script -->

</head>

<body class="bg-[#f6fff8] text-gray-800">

<?php
// Include the header
include '../includes/header.php';
?>
  <!-- 
    Hero 
    ---
    MODIFIED:
    - Changed to a full-width background image.
    - Added a dark overlay for text readability.
    - Removed the side-by-side image.
    - Adjusted text colors to be light.
  -->
  <section 
    class="relative flex items-center justify-center min-h-screen pt-[100px] bg-cover bg-center" 
    style="background-image: url('../images/farmer-hero-banner-img.jpg');"
  >
    <!-- Dark Overlay -->
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm z-0"></div>

    <!-- NEW Site-Wide Container (to align with header) -->
    <div class="relative z-10 w-full max-w-6xl mx-auto px-6">
      <!-- Content (now inside the container) -->
      <div class="max-w-lg">
        <h1 id="hero-h1" class="text-6xl font-bold text-white mb-4 opacity-0 translate-y-5 transition-all ease-out duration-700">Your Local Farm,<br>On Your Device</h1>
        <p id="hero-p" class="text-gray-200 text-xl mb-6 opacity-0 translate-y-5 transition-all ease-out duration-700" style="animation-delay: 200ms;">Fresh, local, and organic produce delivered right to your door.</p>
        <a id="hero-a" href="../auth/register.php" class="inline-block px-6 py-3 bg-green-600 text-white font-semibold rounded-full hover:bg-green-700 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg opacity-0 translate-y-5 ease-out duration-700" style="animation-delay: 400ms;">Shop Now</a>
      </div>
    </div>
  </section>

  <!-- Mission -->
  <section id="about" class="px-6 py-16 bg-[#f1fbf4] text-center">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Our Mission</h2>
      <p class="text-gray-600">
        At Farmer’s Mall, our mission is to connect local communities with the freshest, highest-quality produce from nearby farms. We believe in sustainable agriculture, supporting local economies, and making healthy eating accessible to everyone. By cutting out the middlemen, we ensure fair prices for farmers and exceptional freshness for you.
      </p>
    </div>
  </section>

  <!-- How It Works -->
  <section id="how" class="px-6 py-16 text-center">
    <h2 class="text-2xl font-bold text-gray-900">How It Works</h2>
    <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto mt-10">
      <div class="bg-white p-6 rounded-xl shadow">
        <img src="https://img.icons8.com/fluency-systems-filled/48/1fa02d/shopping-cart.png" class="mx-auto mb-4" alt="Shop Icon">
        <h3 class="font-bold mb-2">1. Shop</h3>
        <p class="text-gray-600">Browse a wide variety of fresh, seasonal products from local farms on our easy-to-use website.</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow">
        <img src="https://img.icons8.com/fluency-systems-filled/48/1fa02d/box.png" class="mx-auto mb-4" alt="Pack Icon">
        <h3 class="font-bold mb-2">2. Pack</h3>
        <p class="text-gray-600">Our team carefully selects and packs your order, ensuring every item is fresh and of the highest quality.</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow">
        <img src="https://img.icons8.com/fluency-systems-filled/48/1fa02d/delivery.png" class="mx-auto mb-4" alt="Deliver Icon">
        <h3 class="font-bold mb-2">3. Deliver</h3>
        <p class="text-gray-600">Your fresh produce is delivered to your doorstep, often on the same day it was harvested!</p>
      </div>
    </div>
  </section>

  <!-- 
    Categories
    ---
    MODIFIED:
    - Wrapped each item in an <a> tag to make it clickable.
    - Added 'group' to the <a> tag.
    - Added hover effects to the <a> tag (shadow, lift).
    - Added hover effect to the <img> tag (scale) using 'group-hover'.
    - Made images uniform with h-48 object-cover.
  -->
  <section id="shop" class="px-6 py-16 text-center">
    <h2 class="text-2xl font-bold text-gray-900">Shop Our Categories</h2>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-6 max-w-6xl mx-auto mt-10">
      
      <!-- Category Item 1 -->
      <a href="../auth/login.php" class="group block bg-white rounded-xl shadow overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
        <img src="../images/vegetable.png" class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105" alt="Vegetables">
        <p class="py-4 font-semibold">Vegetable</p>
      </a>
      
      <!-- Category Item 2 -->
      <a href="../auth/login.php" class="group block bg-white rounded-xl shadow overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
        <img src="../images/fruits.png" class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105" alt="Fruits">
        <p class="py-4 font-semibold">Fruits</p>
      </a>

      <!-- Category Item 3 -->
      <a href="../auth/login.php" class="group block bg-white rounded-xl shadow overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
        <img src="../images/meat.png" class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105" alt="Meat">
        <p class="py-4 font-semibold">Meat</p>
      </a>

      <!-- Category Item 4 -->
      <a href="../auth/login.php" class="group block bg-white rounded-xl shadow overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
        <img src="../images/pantry.png" class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105" alt="Pantry">
        <p class="py-4 font-semibold">Pantry</p>
      </a>

      <!-- Category Item 5 -->
      <a href="../auth/login.php" class="group block bg-white rounded-xl shadow overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
        <img src="../images/equipment.png" class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105" alt="Equipment">
        <p class="py-4 font-semibold">Equipment</p>
      </a>

    </div>
  </section>

<section class="px-6 py-16 text-center">
    <h2 class="text-2xl font-bold text-gray-900">Meet Our Featured Farmers</h2>
    <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto mt-10">
      <div class="bg-white p-6 rounded-xl shadow">
        <h3 class="text-green-600 font-bold mb-2">James Blanco</h3>
        <p>Green Valley Farm – Specializing in organic leafy greens and fresh herbs.</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow">
        <h3 class="text-green-600 font-bold mb-2">Dirk Dimpas</h3>
        <p>Sunrise Dairy – Providing creamy, pasture-raised milk and cheese.</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow">
        <h3 class="text-green-600 font-bold mb-2">Jayson Bustamante</h3>
        <p>Orchard Heights – Home to the sweetest apples and stone fruits.</p>
      </div>
    </div>

    <!-- NEW "Start Selling" Button -->
    <div class="mt-12">
      <a href="../retailer/startselling.php" class="inline-block px-8 py-3 bg-green-600 text-white font-semibold rounded-full hover:bg-green-700 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
        Start Selling
      </a>
    </div>
    <!-- END NEW Button --> 

  </section>

  
  <section class="px-6 py-16"> <!-- Removed text-center -->
    <div class="max-w-6xl mx-auto"> <!-- Use a wider container -->
      <h2 class="text-2xl font-bold text-gray-900 text-center mb-10">Delivery Coverage</h2> <!-- Center the main title -->
      
      <!-- Two-column grid -->
      <div class="grid md:grid-cols-2 gap-8 items-center">
        
        <!-- Left Column: Text Content -->
        <div class="bg-white p-8 rounded-xl shadow text-center h-full flex flex-col justify-center"> <!-- Re-apply the card style here, added flex -->
          
          <!-- New Icon -->
          <div>
            <i class="fas fa-truck-fast text-green-600 text-5xl mb-4"></i>
          </div>
          <!-- End New Icon -->
          
          <p class="text-gray-600 text-lg">We currently serve areas in and around the <strong>City of Mati</strong></p>
          <div class="flex flex-wrap justify-center gap-3 mt-6">
            <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full font-medium">Central</span>
            <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full font-medium">Dahican</span>
            <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full font-medium">Badas</span>
            <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full font-medium">Matiao</span>
            <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full font-medium">Madang</span>
          </div>
          <p class="mt-8 text-sm text-gray-500">Don't see your area? <a href="support.php" class="text-green-600 hover:underline font-medium">Contact us</a> to let us know where you are!</p>
        </div>

        <!-- Right Column: Google Map -->
        <div>
          <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d63183.65961011504!2d126.2132717513672!3d6.945417999999996!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x325603b55c366f0b%3A0x6d11a5113959544c!2sMati%2C%20Davao%20Oriental!5e0!3m2!1sen!2sph!4v1678888888888!5m2!1sen!2sph" 
            width="100%" 
            height="400" 
            style="border:0;" 
            allowfullscreen="" 
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade"
            class="rounded-xl shadow-lg w-full"
          ></iframe>
        </div>

      </div>
    </div>
  </section>


  <section class="px-6 py-16">
    <div class="bg-green-600 text-white rounded-xl p-10 text-center max-w-4xl mx-auto">
      <h2 class="text-2xl font-bold mb-4">Got a Question or Issue? Don’t Wait — Get Instant Support Now!</h2>
      <p class="mb-6">Let us know what you need, and we’ll guide you every step of the way.</p>
      <a href="../public/support.php" class="px-6 py-3 bg-white text-green-700 font-semibold rounded-full hover:bg-gray-200 transition">Get Help</a>
    </div>
  </section>

  <?php
    include '../includes/footer.php';
  ?>

  <!-- START: Login Modal -->
  <div id="loginModal" class="login-modal hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
    <!-- Modal Content - Restored Original Layout -->
    <div class="modal-content w-full max-w-5xl bg-white rounded-2xl shadow-xl overflow-hidden relative lg:flex">
      <!-- Close Button -->
      <button id="closeLoginModal" class="absolute top-6 left-6 h-12 w-12 flex items-center justify-center bg-black bg-opacity-30 rounded-full text-white hover:bg-opacity-50 transition-all z-20">
        <i class="fas fa-arrow-left text-xl"></i>
      </button>

      <!-- Left Side - Branding with Image -->
      <div class="hidden lg:flex lg:w-1/2 p-16 flex-col justify-center items-center text-white text-center relative bg-cover bg-center" style="background-image: url('../images/img.png'); min-height: 680px;">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-green-800 opacity-60"></div>
        <!-- Content -->
        <div class="relative z-10">
          <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg" style="animation: float 3s ease-in-out infinite;">
            <i class="fas fa-leaf text-green-600 text-4xl"></i>
          </div>
          <h2 class="text-3xl font-bold">Farmer's Mall</h2>
          <p class="mt-2 text-green-100">Connecting farmers and consumers directly, offering fresh, local, and organic produce.</p>
        </div>
      </div>

      <!-- Right Side - Form -->
      <div class="w-full lg:w-1/2 p-8 sm:p-16 flex flex-col justify-center" style="min-height: 680px;">
        <div class="text-center mb-8">
          <h2 class="text-2xl font-bold text-gray-800">Welcome Back</h2>
          <p class="text-gray-600 mt-1">Sign in to your account</p>
        </div>
        
        <form id="loginForm" method="POST" class="space-y-6">
            <input type="hidden" name="login_submitted" value="1">
            <!-- Email Input -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email or Username</label>
              <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2 transition-all">
                <i class="far fa-envelope text-gray-400 mr-2"></i>
                <input id="login_email" name="email" type="text" placeholder="Enter your email or username" class="w-full outline-none text-gray-700 py-1" required>
              </div>
              <p id="loginEmailError" class="text-red-600 text-sm mt-1 hidden">Invalid email/username or password.</p>
            </div>

            <!-- Password Input -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
              <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2 transition-all">
                <i class="fas fa-lock text-gray-400 mr-2"></i>
                <input id="login_password" name="password" type="password" placeholder="Enter your password" class="w-full outline-none text-gray-700 py-1" required>
              </div>
              <p id="loginPasswordError" class="text-red-600 text-sm mt-1 hidden">Invalid email/username or password.</p>
            </div>

            <div class="flex items-center justify-between text-sm text-gray-600">
              <label class="flex items-center">
                <input type="checkbox" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded mr-2">
                Remember me
              </label>
              <a href="#" class="text-green-600 hover:underline">Forgot Password?</a>
            </div>
            
            <button id="loginSubmitBtn" type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition-colors shadow-md hover:shadow-lg">
              <span class="btn-text">Login</span>
              <i class="fas fa-sign-in-alt ml-2"></i>
            </button>

            <div class="relative my-4">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
              </div>
              <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">Or continue with</span>
              </div>
            </div>

            <div class="flex justify-center">
              <button type="button" class="w-full flex items-center justify-center gap-2 bg-white border border-gray-300 rounded-lg py-3 text-gray-700 font-bold hover:bg-gray-50 transition-colors shadow-md hover:shadow-lg">
                <i class="fab fa-google text-red-500 mr-2"></i>
                <span>Google</span>
              </button>
            </div>

            <p class="text-center text-sm text-gray-600 mt-6">
              Don’t have an account?
              <a href="../auth/register.php" class="text-green-600 font-medium hover:underline">Create an Account</a>
            </p>

            <small class="block text-center text-xs text-gray-500 mt-4">
              By continuing, you agree to our
              <a href="#" class="text-green-600 font-medium hover:underline">Terms of Service</a> and
              <a href="#" class="text-green-600 font-medium hover:underline">Privacy Policy.</a>
            </p>
          </form>
      </div>
    </div>
  </div>
  <!-- END: Login Modal -->

  <!-- START: Register Modal -->
  <div id="registerModal" class="register-modal hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
    <div class="modal-content w-full max-w-5xl bg-white rounded-2xl shadow-xl overflow-hidden relative lg:flex" style="min-height: 680px;">
      <!-- Left Side - Branding with Image -->
      <div class="hidden lg:flex lg:w-1/2 p-16 flex-col justify-center items-center text-white text-center relative bg-cover bg-center" style="background-image: url('../images/img.png');">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-green-800 opacity-60"></div>
        
        <button id="closeRegisterModal" class="absolute top-6 left-6 h-12 w-12 flex items-center justify-center bg-black bg-opacity-30 rounded-full text-white hover:bg-opacity-50 transition-all z-20">
          <i class="fas fa-arrow-left text-xl"></i>
        </button>

        <!-- Content -->
        <div class="relative z-10">
          <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg" style="animation: float 3s ease-in-out infinite;">
            <i class="fas fa-leaf text-green-600 text-4xl"></i>
          </div>
          <h2 class="text-3xl font-bold">Join The Community</h2>
          <p class="mt-2 text-green-100">Connecting farmers and consumers directly, offering fresh, local, and organic produce.</p>
        </div>
      </div>

      <!-- Right Side - Form -->
      <div class="w-full lg:w-1/2 p-8 md:p-16 flex flex-col">
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-gray-800">Create an Account</h2>
          <p class="text-gray-600 mt-1">Join us and start shopping for fresh produce!</p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-8">
          <div class="flex justify-between text-xs text-gray-500 mb-1">
            <span id="step-name">Personal Info</span>
            <span>Step <span id="step-current">1</span> of 5</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div id="progress-bar" class="bg-green-600 h-2 rounded-full progress-bar-fill" style="width: 25%;"></div>
          </div>
        </div>

        <div class="flex-grow flex flex-col">
          <form id="registerForm" method="POST" action="../auth/register.php" class="flex-grow flex flex-col">
            <div class="flex-grow" style="min-height: 360px;">
              <div class="flex-grow" style="height: 320px; overflow-y: auto;">
              <!-- Step 1: Personal Info -->
              <div class="form-step active space-y-4 text-left">
                <div>
                  <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                  <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                    <input type="text" id="firstname" name="firstname" required placeholder="Enter your first name" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                  </div>
                  <span id="firstname-error" class="error-message hidden"></span>
                </div>
                <div>
                  <label for="middlename" class="block text-sm font-medium text-gray-700 mb-1">Middle Name <span class="text-gray-400">(Optional)</span></label>
                  <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                    <input type="text" id="middlename" name="middlename" placeholder="Enter your middle name" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                  </div>
                  <span id="middlename-error" class="error-message hidden"></span>
                </div>
                <div>
                  <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                  <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                    <input type="text" id="lastname" name="lastname" required placeholder="Enter your last name" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                  </div>
                  <span id="lastname-error" class="error-message hidden"></span>
                </div>
                <div>
                  <label for="suffix" class="block text-sm font-medium text-gray-700 mb-1">Suffix <span class="text-gray-400">(Optional)</span></label>
                  <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                    <input type="text" id="suffix" name="suffix" placeholder="e.g. Jr., Sr." class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                  </div>
                  <span id="suffix-error" class="error-message hidden"></span>
                </div>
              </div>
      
              <!-- Step 2: Account Details -->
              <div class="form-step hidden space-y-4 text-left">
                <div>
                  <label for="street" class="block text-sm font-medium text-gray-700 mb-1">Street</label>
                  <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                    <input type="text" id="street" name="street" required placeholder="Enter your street address" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                  </div>
                  <span id="street-error" class="error-message hidden"></span>
                </div>
                <div>
                  <label for="barangay" class="block text-sm font-medium text-gray-700 mb-1">Barangay</label>
                  <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3">
                    <select id="barangay" name="barangay" required class="w-full outline-none text-gray-700 text-sm bg-transparent py-2">
                      <option value="">Select Barangay</option>
                    </select>
                  </div>
                  <span id="barangay-error" class="error-message hidden"></span>
                </div>
                <div>
                  <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                  <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2 bg-gray-100">
                    <input type="text" id="city" name="city" value="Mati City" readonly class="w-full outline-none text-gray-700 text-sm placeholder:text-sm bg-gray-100 cursor-not-allowed">
                  </div>
                </div>
                <div>
                  <label for="province" class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                  <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2 bg-gray-100">
                    <input type="text" id="province" name="province" value="Davao Oriental" readonly class="w-full outline-none text-gray-700 text-sm placeholder:text-sm bg-gray-100 cursor-not-allowed">
                  </div>
                </div>
              </div>
       
              <input type="hidden" name="city" value="Mati City">
              <input type="hidden" name="province" value="Davao Oriental">

              <!-- Step 3: Contact Info -->
              <div class="form-step hidden space-y-4 text-left" id="step3">
                <div>
                  <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                  <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                    <input type="text" id="username" name="username" required placeholder="Choose a username" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                  </div>
                  <span id="username-error" class="error-message hidden"></span>
                </div>
                <div>
                  <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                  <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                    <input type="tel" id="phone" name="phone" required placeholder="09XXXXXXXXX" pattern="09[0-9]{9}" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                  </div>
                  <span id="phone-error" class="error-message hidden"></span>
                  <p class="text-xs text-gray-500 mt-1">Format: 09XXXXXXXXX (11 digits)</p>
                </div>
                <div>
                  <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                  <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                    <input type="password" id="password" name="password" required placeholder="Enter your password" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                    <button type="button" id="togglePassword" class="ml-2 text-gray-500 hover:text-gray-700 focus:outline-none">
                      <i class="fas fa-eye text-lg"></i>
                    </button>
                  </div>
                  <span id="password-error" class="error-message hidden"></span>
                  <div id="password-strength" class="mt-2 hidden">
                    <div class="flex items-center gap-2 mb-1">
                      <div class="flex-grow h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div id="strength-bar" class="h-full w-0 rounded-full transition-all duration-300" style="background-color: #ef4444;"></div>
                      </div>
                      <span id="strength-text" class="text-xs font-semibold text-red-600">Weak</span>
                    </div>
                    <p class="text-xs text-gray-600">Requires: numbers, symbols, 6+ characters</p>
                  </div>
                </div>
                <div>
                  <label for="confirm" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                  <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                    <input type="password" id="confirm" name="confirm" required placeholder="Confirm your password" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                    <button type="button" id="toggleConfirm" class="ml-2 text-gray-500 hover:text-gray-700 focus:outline-none">
                      <i class="fas fa-eye text-lg"></i>
                    </button>
                  </div>
                  <span id="confirm-error" class="error-message hidden"></span>
                </div>
              </div>
       
              <!-- Step 4: Verification -->
              <div class="form-step hidden space-y-4 text-left" id="step4">
                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email or Phone Number for Verification</label>
                  <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                    <input type="text" id="email" name="email" required placeholder="Enter your email or phone" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                  </div>
                  <span id="email-error" class="error-message hidden"></span>
                </div>
                <button type="button" id="sendVerificationBtn" class="w-full text-center text-sm text-green-600 hover:underline font-medium py-2">Send Verification Code</button>
                <button type="button" id="resendVerificationBtn" class="w-full text-center text-sm text-blue-600 hover:underline font-medium hidden py-2">Resend Code</button>
                <div id="verificationMessage" class="text-sm text-center hidden"></div>
                <div>
                  <label for="otp" class="block text-sm font-medium text-gray-700 mb-1">Verification Code</label>
                  <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                    <input type="text" id="otp" name="otp" required placeholder="Enter the code you received" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                  </div>
                  <span id="otp-error" class="error-message hidden"></span>
                </div>
              </div>

              <!-- Step 5: Finalize (Terms & Conditions) -->
              <div class="form-step hidden space-y-4 text-left" id="step5">
                <h3 class="text-lg font-semibold text-gray-800">Final Agreement</h3>
                <p class="text-sm text-gray-600">Please review and agree to the following terms before creating your account. By proceeding, you acknowledge and accept:</p>
                <ul class="space-y-2 text-sm text-gray-600 list-disc list-inside bg-gray-50 p-4 rounded-lg">
                  <li>You agree to our <a href="#" id="termsLink" class="text-green-600 font-medium hover:underline cursor-pointer">Terms of Service</a>, which govern your use of our platform.</li>
                  <li>You have read and understood our <a href="#" id="privacyLink" class="text-green-600 font-medium hover:underline cursor-pointer">Privacy Policy</a>, which details how we handle your data.</li>
                  <li>You consent to receive communications from us regarding your account and our services.</li>
                  <li>You confirm that all information provided is accurate and that you are at least 18 years of age..</li>
                </ul>
              </div>
              </div>
    
              <div id="terms-container" class="flex items-start mt-4 hidden">
                <input type="checkbox" id="terms" name="terms" required class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded mt-1">
                <label for="terms" class="ml-3 block text-sm text-gray-700">
                  I have read and agree to all the terms and conditions listed above.
                </label>
              </div>
            </div>

            <div id="navigation-buttons" class="mt-auto pt-6 flex gap-4">
              <button type="button" id="prevBtn" class="prev-btn w-32 justify-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-5 rounded-lg transition-colors text-sm hidden">Previous</button>
              <button type="button" id="nextBtn" class="next-btn w-32 justify-center bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-5 rounded-lg transition-colors text-sm ml-auto">Next</button>
              <button type="submit" id="submitBtn" form="registerForm" class="w-32 justify-center bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-5 rounded-lg transition-colors shadow-md hover:shadow-lg text-sm hidden ml-auto">
                Sign Up
              </button>
            </div>
          </form>
    
          <p class="text-center text-sm text-gray-600 mt-8">
            Already have an account? 
            <a href="login.php" class="text-green-600 font-medium hover:underline">Log In</a>
          </p>
        </div>
      </div>
    </div>
  </div>
  <!-- END: Register Modal -->

  <!-- Toast Notification Container -->
  <div id="toast-container" class="fixed top-5 right-5 z-[100]"></div>

</body>
</html>
