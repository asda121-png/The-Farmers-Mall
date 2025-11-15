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
  });
</script>
<!-- END: Active Nav Link Script -->

</head>

<body class="bg-[#f6fff8] text-gray-800">

  <?php
    // Include the header
    include 'header.php';
  ?>

  <!-- Hero -->
  <section class="flex flex-col md:flex-row items-center justify-center gap-10 px-8 py-16 bg-[#fffaf6]">
    <div class="max-w-lg">
      <h1 class="text-4xl font-bold text-gray-900 mb-4">Your Local Farm,<br>On Your Device</h1>
      <p class="text-gray-600 mb-6">Fresh, local, and organic produce delivered right to your door.</p>
      <a href="#shop" class="px-6 py-3 bg-green-600 text-white font-semibold rounded-full hover:bg-green-700 transition">Shop Now</a>
    </div>
    <div>
      <img src="images/veg 1.png" alt="Farm produce" class="rounded-xl shadow-md w-[350px] md:w-[420px]">
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

  <!-- Categories -->
  <section id="shop" class="px-6 py-16 text-center">
    <h2 class="text-2xl font-bold text-gray-900">Shop Our Categories</h2>
    <div class="grid md:grid-cols-5 gap-6 max-w-6xl mx-auto mt-10">
      <div><img src="images/vegetable.png" class="rounded-xl" alt="Vegetables"><p class="mt-2 font-semibold">Vegetable</p></div>
      <div><img src="images/fruits.png" class="rounded-xl" alt="Fruits"><p class="mt-2 font-semibold">Fruits</p></div>
      <div><img src="images/meat.png" class="rounded-xl" alt="Meat"><p class="mt-2 font-semibold">Meat</p></div>
      <div><img src="images/pantry.png" class="rounded-xl" alt="Pantry"><p class="mt-2 font-semibold">Pantry</p></div>
      <div><img src="images/equipment.png" class="rounded-xl" alt="Equipment"><p class="mt-2 font-semibold">Equipment</p></div>
    </div>
  </section>

  <!-- Featured Farmers -->
  <section class="px-6 py-16 text-center">
    <h2 class="text-2xl font-bold text-gray-900">Meet Our Featured Farmers</h2>
    <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto mt-10">
      <div class="bg-white p-6 rounded-xl shadow">
        <h3 class="text-green-600 font-bold mb-2">Farmer A</h3>
        <p>Green Valley Farm – Specializing in organic leafy greens and fresh herbs.</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow">
        <h3 class="text-green-600 font-bold mb-2">Farmer B</h3>
        <p>Sunrise Dairy – Providing creamy, pasture-raised milk and cheese.</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow">
        <h3 class="text-green-600 font-bold mb-2">Farmer C</h3>
        <p>Orchard Heights – Home to the sweetest apples and stone fruits.</p>
      </div>
    </div>
  </section>

  <!-- Why Choose Us -->
  <section class="px-6 py-16 text-center bg-[#f1fbf4]">
    <h2 class="text-2xl font-bold text-gray-900">Why Choose Us?</h2>
    <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto mt-10">
      <div class="bg-white p-6 rounded-xl shadow">
        <img src="https://img.icons8.com/ios-filled/50/1fa02d/organic-food.png" class="mx-auto mb-4" alt="Organic Icon">
        <h3 class="font-bold mb-2">Organic & Natural</h3>
        <p class="text-gray-600">Our products are grown with care, without harmful pesticides or chemicals.</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow">
        <img src="images/vegetables-box.png" class="mx-auto mb-4" alt="Freshness Icon">
        <h3 class="font-bold mb-2">Unmatched Freshness</h3>
        <p class="text-gray-600">From the farm to your table in record time, guaranteeing peak flavor and nutrition.</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow">
        <img src="https://img.icons8.com/ios-filled/50/1fa02d/handshake.png" class="mx-auto mb-4" alt="Support Local Icon">
        <h3 class="font-bold mb-2">Supporting Local</h3>
        <p class="text-gray-600">Every purchase directly supports small, independent farmers in your community.</p>
      </div>
    </div>
  </section>

  <!-- Delivery Coverage -->
  <section class="px-6 py-16 text-center">
    <h2 class="text-2xl font-bold text-gray-900">Delivery Coverage</h2>
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow mt-6">
      <p class="text-gray-600">We currently serve the following areas:</p>
      <div class="flex flex-wrap justify-center gap-3 mt-4">
        <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full">Cityville</span>
        <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full">Greendale</span>
        <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full">Northwood</span>
        <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full">Riverside</span>
        <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full">Oakland</span>
      </div>
      <p class="mt-4 text-sm text-gray-500">Don’t see your area? <a href="#" class="text-green-600 hover:underline">Contact us</a> to let us know where you are!</p>
    </div>
  </section>

  <!-- CTA -->
  <section class="px-6 py-16">
    <div class="bg-green-600 text-white rounded-xl p-10 text-center max-w-4xl mx-auto">
      <h2 class="text-2xl font-bold mb-4">Ready for a Taste of Freshness?</h2>
      <p class="mb-6">Join the farm-to-table movement today and get the best local produce delivered to you.</p>
      <a href="login.html" class="px-6 py-3 bg-white text-green-700 font-semibold rounded-full hover:bg-gray-200 transition">Shop Now</a>
    </div>
  </section>

  <?php
    // Include the footer
    // We are including 'footer.php' as it's the correct PHP file we created.
    include 'footer.html';
  ?>

</body>
</html>