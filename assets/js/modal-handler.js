document.addEventListener('DOMContentLoaded', function () {
  // --- START: Modal Element Selection ---
  const loginModal = document.getElementById('loginModal');
  const loginForm = document.getElementById('loginForm');
  const loginEmailError = document.getElementById('loginEmailError');
  const loginPasswordError = document.getElementById('loginPasswordError');
  const registerModal = document.getElementById('registerModal');
  const registerForm = document.getElementById('registerForm');
  const closeRegisterBtn = document.getElementById('closeRegisterModal');
  let sentVerificationCode = null; // Stores the OTP for client-side validation (dev only)
  let verificationCodeSent = false; // Flag to indicate if code was sent
  // --- END: Modal Element Selection ---

  // --- START: Modal Open/Close Functions ---
  function openLoginModal() {
    if (loginModal) {
      loginModal.classList.remove('hidden');
      document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
  }

  function openRegisterModal() {
    if (registerModal) {
      registerModal.classList.remove('hidden');
      document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
  }

  function closeLoginModal() {
    if (loginModal) {
      loginModal.classList.add('hidden');
      document.body.style.overflow = ''; // Restore scrolling
    }
  }

  function closeRegisterModal() {
    if (registerModal) {
      registerModal.classList.add('hidden');
      document.body.style.overflow = ''; // Restore scrolling
    }
  }
  // --- END: Modal Open/Close Functions ---

  // --- START: Event Listeners for Modal Triggers ---
  document.querySelectorAll('a[href*="login.php"], a[href*="register.php"]').forEach(link => {
    link.addEventListener('click', function (e) {
      if (this.href.includes('login.php')) {
        e.preventDefault();
        openLoginModal();
      }
      if (this.href.includes('register.php')) {
        e.preventDefault();
        openRegisterModal();
      }
    });
  });

  if (loginModal) {
    loginModal.addEventListener('click', (e) => e.target === loginModal && closeLoginModal());
  }
  const closeLoginBtn = document.getElementById('closeLoginModal');
  if (closeLoginBtn) {
    closeLoginBtn.addEventListener('click', closeLoginModal);
  }

  if (registerModal) {
    registerModal.addEventListener('click', (e) => e.target === registerModal && closeRegisterModal());
  }
  if (closeRegisterBtn) {
    closeRegisterBtn.addEventListener('click', closeRegisterModal);
  }
  // --- END: Event Listeners for Modal Triggers ---

  // --- START: Modal Switching Logic ---
  const switchToLoginLink = document.querySelector('#registerModal a[href*="login.php"]');
  if (switchToLoginLink) {
    switchToLoginLink.addEventListener('click', function (e) {
      e.preventDefault();
      closeRegisterModal();
      setTimeout(openLoginModal, 150);
    });
  }
  const switchToRegisterLink = document.querySelector('#loginModal a[href*="register.php"]');
  if (switchToRegisterLink) {
    switchToRegisterLink.addEventListener('click', function (e) {
      e.preventDefault();
      closeLoginModal();
      setTimeout(openRegisterModal, 150);
    });
  }
  // --- END: Modal Switching Logic ---

  // --- START: Login Form AJAX ---
  const loginSubmitBtn = document.getElementById('loginSubmitBtn');
  if (loginForm) {
    loginForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(loginForm);
      const btnText = loginSubmitBtn.querySelector('.btn-text');

      document.getElementById('loginEmailError').classList.add('hidden');
      document.getElementById('loginPasswordError').classList.add('hidden');

      loginSubmitBtn.disabled = true;
      btnText.textContent = 'Logging in...';

      fetch('../auth/login.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            showCenteredNotification(data.message, 'success');
            if (data.redirect_url) {
              window.location.href = `../public/loading.php?redirect_to=${encodeURIComponent(data.redirect_url)}`;
            }
          } else {
            // Show a centered notification for any login error.
            showCenteredNotification(data.message || 'An unknown error occurred.', 'error');
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
    const validationPatterns = {
      name: /^[a-zA-Z\s\-']{2,50}$/,
      username: /^[a-zA-Z0-9_-]{3,20}$/,
      email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
      phone: /^09\d{9}$/,
      password: /^(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{6,}$/,
      street: /^[a-zA-Z0-9\s,.\-]{3,100}$/,
      otp: /^\d{4,6}$/
    };
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

    function validateField(fieldId, fieldType) {
      const field = document.getElementById(fieldId);
      if (!field) return true;
      const value = field.value.trim();
      const isRequired = field.hasAttribute('required');
      if (!isRequired && !value) {
        clearFieldError(fieldId);
        return true;
      }
      if (isRequired && !value) {
        setFieldError(fieldId, `${field.name || fieldId} is required`);
        return false;
      }
      if (validationPatterns[fieldType] && value) {
        if (!validationPatterns[fieldType].test(value)) {
          setFieldError(fieldId, errorMessages[fieldId] || "Invalid format");
          return false;
        }
      }
      if (fieldId === 'confirm') {
        const passwordValue = document.getElementById('password').value;
        if (value !== passwordValue) {
          setFieldError(fieldId, errorMessages.confirm);
          return false;
        }
      }
      clearFieldError(fieldId);
      return true;
    }

    function setFieldError(fieldId, message) {
      const field = document.getElementById(fieldId);
      const errorElement = document.getElementById(`${fieldId}-error`);
      if (field) field.parentElement.classList.add('input-error');
      if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.remove('hidden');
      }
    }

    function clearFieldError(fieldId) {
      const field = document.getElementById(fieldId);
      const errorElement = document.getElementById(`${fieldId}-error`);
      if (field) field.parentElement.classList.remove('input-error');
      if (errorElement) {
        errorElement.textContent = '';
        errorElement.classList.add('hidden');
      }
    }

    function calculatePasswordStrength(password) {
      let strength = 0;
      if (!password) return {
        strength: 0,
        text: 'Weak',
        color: '#ef4444'
      };
      if (password.length >= 8) strength += 2;
      if (/\d/.test(password)) strength += 1;
      if (/[a-z]/.test(password)) strength += 1;
      if (/[A-Z]/.test(password)) strength += 1;
      if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) strength += 2;
      let strengthLevel = 'Weak',
        color = '#ef4444';
      if (strength <= 2) {
        strengthLevel = 'Weak';
        color = '#ef4444';
      } else if (strength <= 4) {
        strengthLevel = 'Medium';
        color = '#f59e0b';
      } else if (strength <= 6) {
        strengthLevel = 'Strong';
        color = '#10b981';
      } else {
        strengthLevel = 'Very Strong';
        color = '#059669';
      }
      return {
        strength: Math.min((strength / 8) * 100, 100),
        text: strengthLevel,
        color: color
      };
    }

    function updatePasswordStrength(password) {
      const strengthDiv = document.getElementById('password-strength');
      const strengthBar = document.getElementById('strength-bar');
      const strengthText = document.getElementById('strength-text');
      if (!strengthDiv) return;
      const strength = calculatePasswordStrength(password);
      if (!password) {
        strengthDiv.classList.add('hidden');
        return;
      }
      strengthDiv.classList.remove('hidden');
      strengthBar.style.width = strength.strength + '%';
      strengthBar.style.backgroundColor = strength.color;
      strengthText.textContent = strength.text;
      strengthText.style.color = strength.color;
    }

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
    const matiBarangays = ["Badas", "Bobon", "Buso", "Cawayanan", "Central", "Dahican", "Danao", "Dawan", "Don Enrique Lopez", "Don Martin Marundan", "Don Salvador Lopez Sr.", "Langka", "Lantawan", "Lawigan", "Libudon", "Luban", "Macambol", "Magsaysay", "Manay", "Matiao", "New Bataan", "New Libudon", "Old Macambol", "Poblacion", "Sainz", "San Isidro", "San Roque", "Tagabakid", "Tagbinonga", "Taguibo", "Tamisan"];

    function populateBarangays() {
      if (!barangaySelect) return;
      barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
      matiBarangays.sort().forEach(b => {
        const o = document.createElement('option');
        o.value = b;
        o.textContent = b;
        barangaySelect.appendChild(o);
      });
    }

    function updateProgress() {
      steps.forEach(s => s.classList.add('hidden'));
      steps[currentStep].classList.remove('hidden');
      progressBar.style.width = `${((currentStep + 1) / steps.length) * 100}%`;
      stepNameEl.textContent = stepNames[currentStep];
      stepCurrentEl.textContent = currentStep + 1;
    }

    function goToStep(stepIndex) {
      steps[currentStep].classList.add('hidden');
      currentStep = stepIndex;
      updateProgress();
      updateButtons();
    }

    function updateButtons() {
      const isLastStep = currentStep === steps.length - 1;
      termsContainer.classList.toggle('hidden', !isLastStep);
      prevBtn.classList.toggle('hidden', currentStep === 0);
      nextBtn.classList.toggle('hidden', isLastStep);
      submitBtn.classList.toggle('hidden', !isLastStep);
      if (currentStep === 3) {
        nextBtn.disabled = !isOtpValid();
      }
    }

    function validateStep(stepIndex) {
      const inputs = steps[stepIndex].querySelectorAll('input[required], select[required]');
      let isValid = true;
      inputs.forEach(i => {
        const id = i.id;
        const type = getFieldValidationType(id);
        if (!validateField(id, type)) isValid = false;
      });
      if (stepIndex === 3) {
        if (!verificationCodeSent) {
          showCenteredNotification("Please send a verification code first.", 'error');
          return false;
        }
        if (!isOtpValid()) {
          showCenteredNotification("Please enter a valid verification code.", 'error');
          return false;
        }
      }
      if (stepIndex === 4) {
        const terms = document.getElementById('terms');
        if (!terms || !terms.checked) {
          showCenteredNotification("You must agree to the terms and conditions.", 'error');
          return false;
        }
      }
      if (!isValid && stepIndex !== 3) {
        showCenteredNotification("Please fix the highlighted errors.", 'error');
      }
      return isValid;
    }

    function getFieldValidationType(id) {
      const map = {
        'firstname': 'name',
        'lastname': 'name',
        'middlename': 'name',
        'suffix': 'name',
        'username': 'username',
        'email': 'email',
        'phone': 'phone',
        'password': 'password',
        'confirm': 'password',
        'street': 'street',
        'otp': 'otp',
        'barangay': 'barangay'
      };
      return map[id] || 'text';
    }

    function isOtpValid() {
      return verificationCodeSent && otpInput.value.trim().length >= 4 && (sentVerificationCode === null || otpInput.value.trim() === sentVerificationCode);
    }

    nextBtn.addEventListener('click', () => {
      if (validateStep(currentStep)) {
        if (currentStep < steps.length - 1) goToStep(currentStep + 1);
      }
    });
    prevBtn.addEventListener('click', () => {
      if (currentStep > 0) goToStep(currentStep - 1);
    });

    registerForm.addEventListener('submit', function (e) {
      e.preventDefault();
      if (validateStep(currentStep)) {
        const formData = new FormData(this);
        formData.append('register_submitted', '1');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Registering...';
        fetch('../auth/register.php', {
            method: 'POST',
            body: formData
          }).then(r => r.json()).then(d => {
            if (d.status === 'success') {
              showCenteredNotification(d.message, 'success');
              setTimeout(() => window.location.href = d.redirect, 2000);
            } else {
              // The user wants to completely remove toast notifications for registration errors.
              // We will attempt to parse the error and display it inline.
              const errorString = d.message || "An unknown error occurred.";
              const errors = errorString.split(' | ');
              errors.forEach(error => {
                // This is a simple mapping. A more robust solution would be ideal.
                // For now, we just log it, as inline validation is handled separately.
                console.error("Registration validation error:", error);
              });
              submitBtn.disabled = false;
              submitBtn.textContent = 'Sign Up';
            }
          })
          .catch(err => {
            showCenteredNotification('A network error occurred.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Sign Up';
          });
      }
    });

    populateBarangays();
    updateProgress();
    updateButtons();
    document.getElementById('togglePassword')?.addEventListener('click', function () {
      const i = document.getElementById('password');
      const icon = this.querySelector('i');
      i.type = i.type === 'password' ? 'text' : 'password';
      icon.classList.toggle('fa-eye');
      icon.classList.toggle('fa-eye-slash');
    });
    document.getElementById('toggleConfirm')?.addEventListener('click', function () {
      const i = document.getElementById('confirm');
      const icon = this.querySelector('i');
      i.type = i.type === 'password' ? 'text' : 'password';
      icon.classList.toggle('fa-eye');
      icon.classList.toggle('fa-eye-slash');
    });
    document.getElementById('password')?.addEventListener('input', (e) => updatePasswordStrength(e.target.value));

    const sendVerificationBtn = document.getElementById('sendVerificationBtn');
    const verificationMessage = document.getElementById('verificationMessage');
    const emailInput = document.getElementById('email');
    const resendBtn = document.getElementById('resendVerificationBtn');
    const otpInputField = document.getElementById('otp');

    if (currentStep === 3) {
      nextBtn.disabled = true;
    }

    if (sendVerificationBtn) {
      sendVerificationBtn.addEventListener('click', function () {
        const email = emailInput.value.trim();
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
          showCenteredNotification('Please enter a valid email address.', 'error');
          return;
        }
        this.disabled = true;
        this.textContent = 'Sending...';
        verificationMessage.classList.add('hidden');
        fetch('../auth/verify-email.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              email: email
            })
          })
          .then(response => response.json())
          .then(data => {
            showCenteredNotification(data.message, data.success ? 'success' : 'error');
            if (data.success) {
              verificationCodeSent = true;
              sentVerificationCode = data.code || null;
              showCenteredNotification("Please check your email for the verification code.", 'success');
              this.textContent = 'Code Sent!';
              this.classList.add('opacity-50', 'cursor-not-allowed');
              resendBtn.classList.remove('hidden');
              verificationMessage.textContent = data.message;
              verificationMessage.classList.remove('hidden');
              otpInputField.focus();
            } else {
              this.disabled = false;
              this.textContent = 'Send Verification Code';
              verificationMessage.textContent = data.message;
              verificationMessage.classList.remove('hidden');
            }
          });
      });
    }

    if (resendBtn) {
      resendBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (sendVerificationBtn) {
          sendVerificationBtn.click();
          showCenteredNotification('Sending new verification code...', 'success');
        }
      });
    }

    if (otpInputField) {
      otpInputField.addEventListener('input', () => {
        const otp = otpInputField.value.trim();
        if (isOtpValid()) {
          nextBtn.disabled = false;
          clearFieldError('otp');
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
    setTimeout(() => {
      openLoginModal();
      const newUrl = window.location.pathname;
      window.history.pushState({
        path: newUrl
      }, '', newUrl);
    }, 500);
  }
  // --- END: Auto-open Login Modal on Redirect ---
});

// --- START: Global Toast and Notification Functions ---
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

  setTimeout(() => {
    notification.style.transform = 'translate(-50%, -50%) scale(1)';
    notification.style.opacity = '1';
  }, 10);
  setTimeout(() => {
    notification.style.opacity = '0';
    setTimeout(() => notification.remove(), 300);
  }, 4000);
}

function showToast(message, type = 'error') {
  const toastContainer = document.getElementById('toast-container');
  if (!toastContainer) return;
  const toast = document.createElement('div');
  const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-circle-exclamation';
  const bgColor = type === 'success' ? 'bg-green-600' : 'bg-red-600';
  toast.className = `flex items-center gap-3 p-4 mb-3 text-white rounded-lg shadow-lg ${bgColor} transform transition-all duration-300 opacity-0 translate-x-10`;
  toast.innerHTML = `<i class="fas ${iconClass}"></i><span>${message}</span>`;
  toastContainer.appendChild(toast);
  setTimeout(() => {
    toast.classList.remove('opacity-0', 'translate-x-10');
  }, 10);
  setTimeout(() => {
    toast.classList.add('opacity-0');
    setTimeout(() => toast.remove(), 500);
  }, 3500);
}
// --- END: Global Toast and Notification Functions ---