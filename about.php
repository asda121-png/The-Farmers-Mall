<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>About Us – Farmers Mall</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Font Awesome (same as yocur working footer) -->
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

<!-- START: Active Nav Link Script (Copied from index.php for consistency) -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // These are the classes we'll toggle
    const activeClasses = 'text-green-700 font-bold';
    const inactiveClasses = 'text-gray-700 font-medium';
    
    // Get all the links inside your main <nav>
    const navLinks = document.querySelectorAll('nav ul li a');

    function highlightActiveLink() {
      // Get the current URL's hash (e.g., "#about")
      const currentHash = window.location.hash;
      // Get the current URL's pathname (e.g., "/index.php" or "/about.php")
      const currentPath = window.location.pathname.split('/').pop() || 'index.php';

      let homeLink = null;
      let bestMatch = null;

      navLinks.forEach(link => {
        const linkPath = link.pathname.split('/').pop() || 'index.php';
        const linkHash = link.hash;

        // Reset all links to the inactive style first
        link.classList.remove(...activeClasses.split(' '));
        if (!link.classList.contains(inactiveClasses.split(' ')[0])) {
             link.classList.add(...inactiveClasses.split(' '));
        }

        // Find the 'Home' link (no hash, points to index.php)
        if (linkPath === 'index.php' && !linkHash) {
          homeLink = link;
        }

        // Check for a match
        if (linkPath === currentPath) {
          if (linkHash === currentHash || !linkHash) { // Match if path is same, even if no hash
            bestMatch = link;
          }
        }
      });

      // If no hash match (e.g., user is just on "index.php")
      if (!bestMatch && currentPath === 'index.php' && homeLink && !currentHash) {
        bestMatch = homeLink;
      }
      
      // If user is on about.php, currentPath will be 'about.php'
      // and bestMatch will be the 'about.php' link

      // If we found a link to highlight...
      if (bestMatch) {
        bestMatch.classList.remove(...inactiveClasses.split(' '));
        bestMatch.classList.add(...activeClasses.split(' '));
      } else if (homeLink && !currentHash) {
         // Default to highlighting Home if no other match and no hash
         homeLink.classList.remove(...inactiveClasses.split(' '));
         homeLink.classList.add(...activeClasses.split(' '));
      }
    }

    // Run the function when the page loads
    highlightActiveLink();
    
    // Run the function again if the user clicks an anchor link
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

  <!-- Page Title Hero -->
  <section class="py-16 bg-[#f1fbf4] text-center">
    <div class="max-w-3xl mx-auto px-6">
      <h1 class="text-4xl font-bold text-gray-900">About The Farmer's Mall</h1>
      <p class="text-lg text-gray-600 mt-2">Connecting communities with fresh, local produce.</p>
    </div>
  </section>

  <!-- Our Story -->
  <section id="story" class="px-6 py-16">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow">
      <h2 class="text-2xl font-bold text-gray-900 mb-4 text-center">Our Story</h2>
      <p class="text-gray-600 mb-4">
        At Farmer’s Mall, our mission is to connect local communities with the freshest, highest-quality produce from nearby farms. We believe in sustainable agriculture, supporting local economies, and making healthy eating accessible to everyone.
      </p>
      <p class="text-gray-600">
        It all started with a simple idea: what if we could cut out the middlemen and ensure fair prices for farmers and exceptional freshness for customers? Today, we're proud to be that direct link, bringing the farm-to-table movement to your doorstep, one delivery at a time.
      </p>
    </div>
  </section>

  <!-- Our Values (Adapted from 'Why Choose Us') -->
  <section id="values" class="px-6 py-16 text-center bg-[#f1fbf4]">
    <h2 class="text-2xl font-bold text-gray-900">Our Values</h2>
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

  <!-- Featured Farmers -->
  <section class="px-6 py-16 text-center">
    <h2 class="text-2xl font-bold text-gray-900">Meet Our Featured Farmers</h2>
    <p class="text-gray-600 max-w-2xl mx-auto mt-2">We partner with the best local farmers to bring you a diverse range of quality products.</p>
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


  <?php
    // Include the footer
    include 'footer.php';
  ?>

</body>
</html> 