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

<!-- ✅ START: Active Nav Link Script -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // These are the classes we'll toggle
    const activeClasses = 'text-green-700 font-bold';
    const inactiveClasses = 'text-gray-700 font-medium';
    
    // Get all the links inside your main <nav>
    const navLinks = document.querySelectorAll('nav ul li a');

    function highlightActiveLink() {
      const currentPath = window.location.pathname.split('/').pop(); // e.g., "index.php"
      const currentHash = window.location.hash; // e.g., "#about"

      let homeLink = null;
      let bestMatch = null;

      navLinks.forEach(link => {
        const linkPath = link.pathname.split('/').pop();
        const linkHash = link.hash;

        // Reset all links to the inactive style first
        link.classList.remove(...activeClasses.split(' '));
        link.classList.add(...inactiveClasses.split(' '));

        // Find the 'Home' link
        if (linkPath === 'index.php' && !linkHash) {
          homeLink = link;
        }

        // Check if the link matches the current URL
        if (linkPath === currentPath) {
          if (linkHash === currentHash) {
            // Perfect match (e.g., index.php#about)
            bestMatch = link;
          }
        }
      });

      // If no perfect match was found (e.g., user is just on "index.php")
      // but we are on the index page, highlight the 'Home' link.
      if (!bestMatch && currentPath === 'index.php' && homeLink) {
        bestMatch = homeLink;
      }

      // If we found a link to highlight...
      if (bestMatch) {
        bestMatch.classList.remove(...inactiveClasses.split(' '));
        bestMatch.classList.add(...activeClasses.split(' '));
      }
    }

    // Run the function when the page loads
    highlightActiveLink();
    
    // Run the function again if the user clicks an anchor link (e.g., #about)
    window.addEventListener('hashchange', highlightActiveLink);
  });
</script>
<!-- ✅ END: Active Nav Link Script -->

</head>

<body class="bg-[#f6fff8] text-gray-800">

  <!-- Header -->
  <header class="flex justify-between items-center py-4 bg-white shadow sticky top-0 z-50">
    <div class="flex items-center gap-2 ml-[96px]">
        <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
          <i class="fas fa-leaf text-white text-lg"></i>
        </div>
        <h1 class="text-xl font-bold" style="color: #2E7D32;">Farmers Mall</h1>
      </div>

    <nav>
      <ul class="flex gap-6"> <!-- Removed default text color, script will handle it -->
        <!-- ✅ CHANGED: Updated links to work from any page -->
        <li><a href="index.php" class="hover:text-green-700">Home</a></li>
        <li><a href="about.php" class="hover:text-green-700">About</a></li>
        <li><a href="how.php" class="hover:text-green-700">How It Works</a></li>
        <li><a href="support.php" class="hover:text-green-700">Support</a></li>
      </ul>
    </nav>

    <div class="flex gap-3 mr-[96px]">
      <a href="register.php" class="px-4 py-2 border-2 border-green-600 text-green-600 font-semibold rounded-full hover:bg-green-600 hover:text-white transition">
        Sign Up
      </a>
      <a href="login.php" class="px-4 py-2 bg-green-600 text-white font-semibold rounded-full hover:bg-green-700 transition">
        Shop Now
      </a>
    </div>
  </header>