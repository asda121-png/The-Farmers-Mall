document.addEventListener("DOMContentLoaded", () => {
  // --- PAYMENT METHOD TOGGLE ---
  const paymentRadios = document.querySelectorAll('input[name="payment"]');
  const cardInfo = document.getElementById("card-info");
  const placeOrderBtn = document.getElementById("placeOrderBtn");
  const paypalButtonContainer = document.getElementById(
    "paypal-button-container"
  );
  const gcashButtonContainer = document.getElementById(
    "gcash-button-container"
  );

  // Toggle payment UI based on selection
  paymentRadios.forEach((radio) => {
    radio.addEventListener("change", () => {
      const selectedPayment = radio.value;

      // Hide/show card info
      cardInfo.classList.toggle(
        "invisible-placeholder",
        selectedPayment !== "card"
      );

      // Hide/show place order button
      if (selectedPayment === "paypal" || selectedPayment === "gcash") {
        placeOrderBtn.classList.add("hidden");
      } else {
        placeOrderBtn.classList.remove("hidden");
      }

      // Hide/show PayPal button
      if (selectedPayment === "paypal") {
        paypalButtonContainer.classList.remove("hidden");
        gcashButtonContainer.classList.add("hidden");
        initializePayPalButton();
      } else {
        paypalButtonContainer.classList.add("hidden");
      }

      // Hide/show GCash button
      if (selectedPayment === "gcash") {
        gcashButtonContainer.classList.remove("hidden");
        paypalButtonContainer.classList.add("hidden");
      } else {
        gcashButtonContainer.classList.add("hidden");
      }
    });
  });

  // --- USE PHP-RENDERED DATA (NO RECALCULATION) ---
  const orderItemsContainer = document.getElementById("orderItems");
  const subtotalEl = document.getElementById("subtotal");
  const taxEl = document.getElementById("tax");
  const totalEl = document.getElementById("total");

  // Use data passed from PHP
  const { cart, subtotal, tax, total } = window.paymentData || {
    cart: [],
    subtotal: 0,
    tax: 0,
    total: 0,
  };

  // Optional: Re-render items if needed (e.g. after filtering)
  const renderOrderItems = () => {
    orderItemsContainer.innerHTML = "";

    if (cart.length === 0) {
      orderItemsContainer.innerHTML =
        '<p class="text-sm text-gray-500">No items in cart.</p>';
      return;
    }

    cart.forEach((item) => {
      const imgSrc = item.image
        ? item.image.startsWith("http")
          ? item.image
          : "../" + item.image.replace(/^\/+/, "")
        : "https://via.placeholder.com/100x100?text=No+Image";

      const itemDiv = document.createElement("div");
      itemDiv.className = "flex gap-3";
      itemDiv.innerHTML = `
        <img src="${imgSrc}" class="w-12 h-12 rounded object-cover border bg-gray-50">
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-800 truncate">${item.name}</p>
          <p class="text-xs text-gray-500">Qty: ${item.quantity}</p>
        </div>
        <div class="text-sm font-semibold text-gray-700">₱${parseFloat(
          item.subtotal
        ).toFixed(2)}</div>
      `;
      orderItemsContainer.appendChild(itemDiv);
    });
  };

  // Render items (in case JS needs to re-render)
  renderOrderItems();

  // --- PAYPAL INTEGRATION ---
  let paypalButtonRendered = false;

  function initializePayPalButton() {
    if (paypalButtonRendered) {
      return;
    }

    // Clear container first
    paypalButtonContainer.innerHTML = "";

    // Create simple redirect button instead of SDK
    const paypalBtn = document.createElement("button");
    paypalBtn.className =
      "bg-blue-600 w-full text-white py-3 rounded-md font-medium hover:bg-blue-700 transition flex items-center justify-center gap-2";
    paypalBtn.innerHTML =
      '<i class="fa-brands fa-paypal text-xl"></i> Pay with PayPal';

    paypalBtn.addEventListener("click", async () => {
      if (cart.length === 0) {
        alert("Your cart is empty. Cannot place order.");
        return;
      }

      paypalBtn.disabled = true;
      paypalBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin mr-2"></i> Redirecting to PayPal...';

      try {
        console.log("Creating PayPal order...");

        // Get selected cart IDs
        const urlParams = new URLSearchParams(window.location.search);
        const cartIds = urlParams.get("cart_ids");
        const selectedCartIds = cartIds ? cartIds.split(",") : [];

        // Create order via our API
        const response = await fetch("../api/paypal-payment.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            action: "create_paypal_order",
            amount: total,
            cart_items: cart,
            cart_ids: selectedCartIds,
          }),
        });

        const result = await response.json();
        console.log("PayPal API Response:", result);

        if (result.success && result.approval_url) {
          console.log("Redirecting to:", result.approval_url);
          // Redirect to PayPal website
          window.location.href = result.approval_url;
        } else {
          throw new Error(result.message || "Failed to create PayPal order");
        }
      } catch (error) {
        console.error("PayPal Error:", error);
        alert("Failed to initialize PayPal payment: " + error.message);
        paypalBtn.disabled = false;
        paypalBtn.innerHTML =
          '<i class="fa-brands fa-paypal text-xl"></i> Pay with PayPal';
      }
    });

    paypalButtonContainer.appendChild(paypalBtn);
    paypalButtonRendered = true;
  }

  // --- GCASH INTEGRATION ---
  const gcashPayBtn = document.getElementById("gcashPayBtn");

  if (gcashPayBtn) {
    gcashPayBtn.addEventListener("click", async () => {
      if (cart.length === 0) {
        alert("Your cart is empty. Cannot place order.");
        return;
      }

      gcashPayBtn.disabled = true;
      gcashPayBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';

      try {
        // Get selected cart IDs
        const urlParams = new URLSearchParams(window.location.search);
        const cartIds = urlParams.get("cart_ids");
        const selectedCartIds = cartIds ? cartIds.split(",") : [];

        // Create GCash payment source
        const response = await fetch("../api/gcash-payment.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            action: "create_gcash_source",
            amount: total,
            cart_items: cart,
          }),
        });

        const result = await response.json();

        if (result.success && result.checkout_url) {
          // Store cart IDs in session storage for later
          sessionStorage.setItem(
            "gcash_payment_cart_ids",
            JSON.stringify(selectedCartIds)
          );
          sessionStorage.setItem("gcash_source_id", result.source_id);

          // Redirect to GCash checkout
          window.location.href = result.checkout_url;
        } else {
          throw new Error(result.message || "Failed to create GCash payment");
        }
      } catch (error) {
        console.error("GCash Error:", error);
        alert("Failed to initialize GCash payment: " + error.message);
        gcashPayBtn.disabled = false;
        gcashPayBtn.innerHTML =
          '<img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/GCash_logo.svg" alt="GCash" class="h-5"> Pay with GCash';
      }
    });
  }

  // --- HANDLE PLACING THE ORDER (for card and COD) ---
  placeOrderBtn.addEventListener("click", async () => {
    if (cart.length === 0) {
      alert("Your cart is empty. Cannot place order.");
      return;
    }

    placeOrderBtn.disabled = true;
    placeOrderBtn.textContent = "Processing...";

    try {
      const selectedPayment = document.querySelector(
        'input[name="payment"]:checked'
      );
      const paymentMethod = selectedPayment ? selectedPayment.value : "card";

      // Get selected cart IDs from URL params
      const urlParams = new URLSearchParams(window.location.search);
      const cartIds = urlParams.get("cart_ids");
      const selectedCartIds = cartIds ? cartIds.split(",") : [];

      const response = await fetch("../api/order.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          action: "place_order",
          payment_method: paymentMethod,
          cart_ids: selectedCartIds,
        }),
      });

      const result = await response.json();

      if (result.success) {
        // Save notification
        const newNotification = {
          id: Date.now(),
          type: "order_success",
          title: "Order Placed Successfully!",
          message: `Your order #${result.order_id} has been confirmed.`,
          time: new Date().toISOString(),
          read: false,
          link: `ordersuccessfull.php?order_id=${result.order_id}`,
        };
        const notifications =
          JSON.parse(localStorage.getItem("userNotifications")) || [];
        notifications.unshift(newNotification);
        localStorage.setItem(
          "userNotifications",
          JSON.stringify(notifications)
        );

        // Clear cart
        localStorage.removeItem("cart");
        sessionStorage.removeItem("selectedCartItems");

        // Redirect
        window.location.href = `ordersuccessfull.php?order_id=${result.order_id}`;
      } else {
        alert("Error: " + (result.message || "Failed to place order"));
        placeOrderBtn.disabled = false;
        placeOrderBtn.textContent = "Place Order";
      }
    } catch (error) {
      console.error("Order error:", error);
      alert("Network error. Please try again.");
      placeOrderBtn.disabled = false;
      placeOrderBtn.textContent = "Place Order";
    }
  });
});
