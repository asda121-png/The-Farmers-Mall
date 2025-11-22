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
        setTimeout(() => {
          // Removing these classes triggers the transition
          item.el.classList.remove('opacity-0', 'translate-y-5');
        }, item.delay);
      }
    });
  });
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
      <div class="group bg-white rounded-xl shadow overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
        <img src="../images/vegetable.png" class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105" alt="Vegetables">
        <p class="py-4 font-semibold">Vegetable</p>
      </div>
      
      <!-- Category Item 2 -->
      <div class="group bg-white rounded-xl shadow overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
        <img src="../images/fruits.png" class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105" alt="Fruits">
        <p class="py-4 font-semibold">Fruits</p>
      </div>

      <!-- Category Item 3 -->
      <div class="group bg-white rounded-xl shadow overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
        <img src="../images/meat.png" class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105" alt="Meat">
        <p class="py-4 font-semibold">Meat</p>
      </div>

      <!-- Category Item 4 -->
      <div class="group bg-white rounded-xl shadow overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
        <img src="../images/pantry.png" class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105" alt="Pantry">
        <p class="py-4 font-semibold">Pantry</p>
      </div>

      <!-- Category Item 5 -->
      <div class="group bg-white rounded-xl shadow overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
        <img src="../images/equipment.png" class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105" alt="Equipment">
        <p class="py-4 font-semibold">Equipment</p>
      </div>

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
      <a href="../retailer/startselling.html" class="inline-block px-8 py-3 bg-green-600 text-white font-semibold rounded-full hover:bg-green-700 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
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
    include '../includes/footer.html';
  ?>

</body>
</html>
