<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>About Us – Farmers Mall</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        html {
            scroll-behavior: smooth;
            scroll-padding-top: 100px;
        }
        
        /* This ensures the page has a min-height to push the footer down */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        main {
            flex-grow: 1; /* This makes the main content area grow to fill available space */
        }
    </style>
</head>

<body class="bg-[#f6fff8] text-gray-800">

    <?php
        // Include the header
        include 'header.php';
    ?>

    <!-- Main Content Area -->
    <main>
        <!-- Page Header -->
        <section class="relative flex items-center justify-center min-h-screen bg-cover bg-center text-white pt-[100px]" style="background-image: url('images/farmer-image.jpg');">
            <!-- Dark Overlay --><!-- Adjusted tint back to bg-black/60 for better contrast --><div class="absolute inset-0 bg-black/60 backdrop-blur-sm z-0"></div>

            <!-- Content --><div class="relative z-10 w-full max-w-6xl mx-auto px-6 text-center">
                <div class="max-w-lg mx-auto"> <!-- Centering content block --><h1 class="text-5xl font-bold mb-4">Our Story</h1>
                    <p class="text-xl text-green-100">Connecting our community, one harvest at a time.</p>
                </div>
            </div>
        </section>

        <!-- Our Origins Section -->
        <section class="px-6 py-20">
            <div class="max-w-4xl mx-auto grid md:grid-cols-2 gap-12 items-center">
                <!-- Image Column -->
                <div>
                    <img src="images/our-farm-partners.jpg" alt="Local farm partner" class="rounded-xl shadow-lg w-full">
                    <!-- Placeholder image. Replace with a real photo of a local farm! -->
                </div>
                <!-- Text Column -->
                <div class="text-gray-700">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">From a Simple Idea</h2>
                    <p class="mb-4">
                        Farmers Mall was born from a simple but powerful idea: what if we could close the gap between the dedicated farmers of Davao Oriental and our neighbors in the City of Mati who seek fresh, high-quality food?
                    </p>
                    <p class="mb-4">
                        We saw local producers with incredible harvests and communities wanting healthier, more convenient options. We decided to be the bridge.
                    </p>
                    <p>
                        Our platform isn't just a marketplace; it's a local food ecosystem built on fairness, freshness, and a deep love for our home.
                    </p>
                </div>
            </div>
        </section>

        <!-- Our Core Values Section -->
        <section id="values" class="px-6 py-20 bg-[#f1fbf4]">
            <div class="max-w-6xl mx-auto">
                <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Our Core Values</h2>
                
                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Value 1: Community Empowerment -->
                    <div class="bg-white p-8 rounded-xl shadow text-center">
                        <i class="fas fa-users text-green-600 text-4xl mb-4"></i>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Community Empowerment</h3>
                        <p class="text-gray-600">
                            We're more than just "local." We're committed to empowering our farmers by providing a platform that ensures they get fair prices for their hard work. Every purchase directly contributes to the strength and sustainability of our local economy.
                        </p>
                    </div>

                    <!-- Value 2: Unwavering Quality -->
                    <div class="bg-white p-8 rounded-xl shadow text-center">
                        <!-- MODIFIED: Replaced <i> icon with <img> tag -->
                        <img src="https://img.icons8.com/ios-filled/50/1fa02d/natural-food.png" alt="Unwavering Quality Icon" class="h-12 w-12 mx-auto mb-4">
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Unwavering Quality</h3>
                        <p class="text-gray-600">
                            Our standard is simple: if it's not fresh enough for our own families, it's not fresh enough for yours. We personally vet our farm partners and our team ensures every order is packed with care, guaranteeing farm-to-table freshness.
                        </p>
                    </div>

                    <!-- Value 3: Sustainable Practices -->
                    <div class="bg-white p-8 rounded-xl shadow text-center">
                        <i class="fas fa-seedling text-green-600 text-4xl mb-4"></i>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Sustainable Practices</h3>
                        <p class="text-gray-600">
                            We champion farmers who use sustainable and organic methods. By reducing food miles and supporting responsible agriculture, we're not just nourishing our community—we're also taking care of the beautiful land we all call home.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Commitment to Mati -->
        <section class="px-6 py-20">
            <div class="max-w-4xl mx-auto text-center bg-white p-12 rounded-xl shadow-lg">
                <i class="fas fa-map-marker-alt text-green-600 text-5xl mb-6"></i>
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Our Commitment to Mati</h2>
                <p class="text-lg text-gray-700 mb-6">
                    Our roots are right here in the City of Mati. We're not just a service in the community; we are *of the community. We are passionate about making fresh, healthy food accessible to every family in the areas we serve, from Central to Dahican and beyond.
                </p>
                <a href="register.php" class="inline-block px-6 py-3 bg-green-600 text-white font-semibold rounded-full hover:bg-green-700 transition-all duration-300">
                    Browse Local Products
                </a>
            </div>
        </section>

    </main>

    <?php
        include 'footer.html';
    ?>

</body>
</html>