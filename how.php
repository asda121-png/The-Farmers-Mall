<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>How It Works – Farmers Mall</title>

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
        <section 
            class="relative flex items-center justify-center min-h-screen bg-cover bg-center text-white pt-[100px]" 
            style="background-image: url('images/farmers-mall-cover.jpg');"
            onerror="this.style.backgroundImage='url(\'https://placehold.co/1920x1080/222/white?text=Farm+Fresh+Box\')'"
        >
            <!-- Dark Overlay -->
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm z-0"></div>

            <!-- Content -->
            <div class="relative z-10 w-full max-w-6xl mx-auto px-6 text-center">
                <div class="max-w-lg mx-auto"> <!-- Centering content block -->
                    <h1 class="text-5xl font-bold mb-4">How It Works</h1>
                    <p class="text-xl text-green-100">From the Farm to Your Table, Made Simple.</p>
                </div>
            </div>
        </section>

        <!-- Step 1: Browse & Shop -->
        <section class="px-6 py-20">
            <div class="max-w-4xl mx-auto grid md:grid-cols-2 gap-12 items-center">
                <!-- Image Column -->
                <div>
                    <img src="images/browse-shop.jpg" alt="Browse and shop online" class="rounded-xl shadow-lg w-full" onerror="this.src='https://placehold.co/600x400/16a34a/white?text=1.+Browse+%26+Shop'">
                </div>
                <!-- Text Column -->
                <div class="text-gray-700">
                    <span class="inline-block px-4 py-1 bg-green-100 text-green-700 rounded-full font-semibold mb-3">Step 1</span>
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">You Browse & Shop</h2>
                    <p class="mb-4">
                        Explore our digital marketplace filled with the best produce from farms right here in Davao Oriental. Our platform shows you real-time inventory, so you always know what's fresh and in season.
                    </p>
                    <p>
                        Simply add your desired items to your cart, choose your delivery date, and check out securely. It's like a weekend farmers' market, available 24/7 from the comfort of your home.
                    </p>
                </div>
            </div>
        </section>

        <!-- Step 2: We Pick & Pack -->
        <section class="px-6 py-20 bg-[#f1fbf4]">
            <div class="max-w-4xl mx-auto grid md:grid-cols-2 gap-12 items-center">
                
                <!-- Text Column (Order changed) -->
                <!-- REMOVED md:order-last -->
                <div class="text-gray-700">
                    <span class="inline-block px-4 py-1 bg-green-100 text-green-700 rounded-full font-semibold mb-3">Step 2</span>
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">We Pick & Pack</h2>
                    <p class="mb-4">
                        As soon as your order is confirmed, our system alerts our partner farmers. Your produce isn't sitting in a warehouse; it's often harvested *after* you place your order to ensure maximum freshness.
                    </p>
                    <p>
                        Our team then carefully inspects and packs your order in eco-friendly packaging, checking for quality and ensuring everything is perfect for delivery.
                    </p>
                </div>
                <!-- Image Column (Order changed) -->
                <!-- REMOVED md:order-first -->
                <div>
                    <img src="images/pick-pack.jpg" alt="We pick and pack your order" class="rounded-xl shadow-lg w-full">
                </div>
            </div>
        </section>

        <!-- Step 3: We Deliver Fresh -->
        <section class="px-6 py-20">
            <div class="max-w-4xl mx-auto grid md:grid-cols-2 gap-12 items-center">
                <!-- Image Column -->
                <div>
                    <img src="images/deliver-fresh.jpg" alt="Fresh produce delivered to your door" class="rounded-xl shadow-lg w-full">
                </div>
                <!-- Text Column -->
                <div class="text-gray-700">
                    <span class="inline-block px-4 py-1 bg-green-100 text-green-700 rounded-full font-semibold mb-3">Step 3</span>
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">We Deliver Fresh</h2>
                    <p class="mb-4">
                        Your carefully packed order is dispatched with our local delivery team. We handle your groceries with care, ensuring they arrive at your doorstep fresh, secure, and ready to enjoy.
                    </p>
                    <p>
                        We cover areas all around the City of Mati, bringing the best of local agriculture right to you. You'll get a notification when your delivery is on its way and once it has arrived.
                    </p>
                </div>
            </div>
        </section>


        <!-- Our Guarantee Section -->
        <section id="guarantee" class="px-6 py-20 bg-[#f1fbf4]">
            <div class="max-w-6xl mx-auto">
                <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Our Freshness Guarantee</h2>
                
                <div class="grid md:grid-cols-3 gap-8">
                    
                    <div class="bg-white p-8 rounded-xl shadow text-center">
                        <i class="fas fa-tractor text-green-600 text-4xl mb-4"></i>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Direct from the Farm</h3>
                        <p class="text-gray-600">
                            We cut out the middlemen. Our direct partnership with local farms means less time in transit and more time enjoying peak flavor and nutrition.
                        </p>
                    </div>

                    
                    <div class="bg-white p-8 rounded-xl shadow text-center">
                        <i class="fas fa-clipboard-check text-green-600 text-4xl mb-4"></i>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Rigorously Quality Checked</h3>
                        <p class="text-gray-600">
                            Every item is inspected by our team before it's packed. We stand by our promise of quality and freshness in every single order.
                        </p>
                    </div>

                    
                    <div class="bg-white p-8 rounded-xl shadow text-center">
                        <i class="fas fa-box-open text-green-600 text-4xl mb-4"></i>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Packed with Care</h3>
                        <p class="text-gray-600">
                            We use sustainable and protective packaging to ensure your produce arrives in perfect condition, from delicate herbs to hearty vegetables.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="px-6 py-20">
            <div class="max-w-4xl mx-auto text-center bg-white p-12 rounded-xl shadow-lg">
                <i class="fas fa-shopping-basket text-green-600 text-5xl mb-6"></i>
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Ready to Start Shopping?</h2>
                <p class="text-lg text-gray-700 mb-6">
                    Experience the difference of truly fresh, local food. Browse our categories and see what's in season today.
                </p>
                <a href="register.php" class="inline-block px-6 py-3 bg-green-600 text-white font-semibold rounded-full hover:bg-green-700 transition-all duration-300">
                    Browse All Categories
                </a>
            </div>
        </section>

    </main>

    <?php
        include 'footer.html';
    ?>

</body>
</html>