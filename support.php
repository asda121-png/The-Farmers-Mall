<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Support – Farmers Mall</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        html {
            scroll-behavior: smooth;
            scroll-padding-top: 100px;
        }
        
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        main {
            flex-grow: 1;
        }

        /* * =================================================================
         * PRO-LEVEL FAQ ACCORDION
         * Uses CSS Grid to animate height instead of 'max-height'.
         * This animates to the *exact* content height every time.
         * =================================================================
        */
        .faq-answer {
            display: grid;
            grid-template-rows: 0fr; /* Start collapsed */
            transition: grid-template-rows 0.3s ease-out;
        }
        
        /* This class is toggled by JS */
        .faq-item.active .faq-answer {
            grid-template-rows: 1fr; /* Animate to 1 fraction (full content) */
        }
        
        /* The direct child of .faq-answer must have overflow: hidden */
        .faq-answer > div {
            overflow: hidden;
        }
        
        /* Rotate the arrow icon (JS toggles .active on .faq-item) */
        .faq-item.active .faq-toggle i {
            transform: rotate(180deg);
        }
    </style>
</head>

<body class="bg-[#f6fff8] text-gray-800 antialiased">

    <?php
        // Include the header
        include 'header.php';
    ?>

    <main class="pt-[100px]"> 
        

        <section class="px-6 pb-24">
            <div class="max-w-6xl mx-auto">
                <h2 class="text-3xl font-bold text-gray-900 text-center mb-10">How can we help?</h2>
                
                <div class="relative max-w-2xl mx-auto mb-16">
                    <input 
                        type="text" 
                        placeholder="Search for articles, topics, etc." 
                        class="w-full pl-6 pr-16 py-4 border border-gray-300 rounded-full shadow-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 text-lg transition-all duration-200"
                    />
                    <button class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-green-600 transition-colors duration-200">
                        <i class="fas fa-search text-xl"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <a href="#" class="bg-white p-8 rounded-2xl shadow-lg flex flex-col items-center text-center hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-5 group-hover:bg-green-200 transition-colors duration-300">
                            <i class="fas fa-box text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2 group-hover:text-green-700 transition-colors duration-300">My Orders</h3>
                        <p class="text-gray-600 text-sm">Track shipments, view order history, and manage returns.</p>
                    </a>

                    <a href="#" class="bg-white p-8 rounded-2xl shadow-lg flex flex-col items-center text-center hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-5 group-hover:bg-green-200 transition-colors duration-300">
                            <i class="fas fa-credit-card text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2 group-hover:text-green-700 transition-colors duration-300">Payments & Billing</h3>
                        <p class="text-gray-600 text-sm">Information on payment methods, invoices, and refunds.</p>
                    </a>

                    <a href="#" class="bg-white p-8 rounded-2xl shadow-lg flex flex-col items-center text-center hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-5 group-hover:bg-green-200 transition-colors duration-300">
                            <i class="fas fa-user-circle text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2 group-hover:text-green-700 transition-colors duration-300">Account Management</h3>
                        <p class="text-gray-600 text-sm">Update profile, change password, and privacy settings.</p>
                    </a>

                    <a href="#" class="bg-white p-8 rounded-2xl shadow-lg flex flex-col items-center text-center hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-5 group-hover:bg-green-200 transition-colors duration-300">
                            <i class="fas fa-truck text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2 group-hover:text-green-700 transition-colors duration-300">Delivery & Shipping</h3>
                        <p class="text-gray-600 text-sm">Learn about our delivery zones, times, and policies.</p>
                    </a>

                    <a href="#" class="bg-white p-8 rounded-2xl shadow-lg flex flex-col items-center text-center hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-5 group-hover:bg-green-200 transition-colors duration-300">
                            <i class="fas fa-leaf text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2 group-hover:text-green-700 transition-colors duration-300">Product Information</h3>
                        <p class="text-gray-600 text-sm">Details on our fresh produce, sourcing, and quality.</p>
                    </a>

                    <a href="#" class="bg-white p-8 rounded-2xl shadow-lg flex flex-col items-center text-center hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-5 group-hover:bg-green-200 transition-colors duration-300">
                            <i class="fas fa-laptop-code text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2 group-hover:text-green-700 transition-colors duration-300">Technical Support</h3>
                        <p class="text-gray-600 text-sm">Help with website issues, app usage, and bugs.</p>
                    </a>
                </div>
            </div>
        </section>

        <section id="contact" class="px-6 pb-24">
            <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-12 items-start">
                
                <div class="bg-white p-8 rounded-2xl shadow-lg">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6">Send Us a Message</h2>
                    
                    <form action="#" method="POST" class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" id="name" name="name" required class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" id="email" name="email" required class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                            <input type="text" id="subject" name="subject" required class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                        </div>
                        
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                            <textarea id="message" name="message" rows="5" required class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 transition-all duration-200"></textarea>
                        </div>
                        
                        <div>
                            <button type="submit" class="w-full px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                Send Message
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white p-8 rounded-2xl shadow-lg">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Other Ways to Reach Us</h3>
                    <p class="text-gray-600 mb-8">If you need immediate assistance, please don't hesitate to reach out through these channels.</p>
                    
                    <div class="space-y-6">
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-envelope text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold text-gray-900">Email</h4>
                                <a href="mailto:support@farmersmall.com" class="text-green-600 hover:underline">support@farmersmall.com</a>
                                <p class="text-sm text-gray-500">We respond within 24 hours.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-phone text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold text-gray-900">Phone</h4>
                                <a href="tel:+1234567890" class="text-gray-800">(123) 456-7890</a>
                                <p class="text-sm text-gray-500">Mon-Sat, 8:00 AM - 5:00 PM</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-map-marker-alt text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold text-gray-900">Our Office</h4>
                                <p class="text-gray-800">123 Farm Road, Brgy. Central</p>
                                <p class="text-gray-800">City of Mati, Davao Oriental</p>
                                <p class="text-sm text-gray-500">Please note: This is not a storefront.</p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </section>

        <section id="faq" class="px-6 py-24 bg-[#f1fbf4]">
            <div class="max-w-4xl mx-auto">
                
                <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Frequently Asked Questions</h2>

                <div class="space-y-4">

                    <div class="faq-item bg-white rounded-2xl shadow-lg overflow-hidden">
                        <button class="faq-toggle w-full flex justify-between items-center text-left p-6 hover:bg-gray-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-inset">
                            <h3 class="text-lg font-semibold text-gray-900">What are your delivery hours?</h3>
                            <i class="fas fa-chevron-down text-green-600 transition-transform duration-200"></i>
                        </button>
                        <div class="faq-answer">
                            <div>
                                <div class="p-6 pt-0 text-gray-600">
                                    <p>We deliver from 8:00 AM to 5:00 PM, Monday through Saturday. You can select your preferred 2-hour delivery window during checkout.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item bg-white rounded-2xl shadow-lg overflow-hidden">
                        <button class="faq-toggle w-full flex justify-between items-center text-left p-6 hover:bg-gray-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-inset">
                            <h3 class="text-lg font-semibold text-gray-900">What if an item is missing or damaged?</h3>
                            <i class="fas fa-chevron-down text-green-600 transition-transform duration-200"></i>
                        </button>
                        <div class="faq-answer">
                            <div>
                                <div class="p-6 pt-0 text-gray-600">
                                    <p>We're so sorry for any trouble! Please contact us within 24 hours of your delivery at <a href="mailto:support@farmersmall.com" class="text-green-600 hover:underline">support@farmersmall.com</a> or call us at (123) 456-7890. We will happily arrange a refund or a re-delivery for the affected items.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item bg-white rounded-2xl shadow-lg overflow-hidden">
                        <button class="faq-toggle w-full flex justify-between items-center text-left p-6 hover:bg-gray-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-inset">
                            <h3 class="text-lg font-semibold text-gray-900">Do you deliver outside the City of Mati?</h3>
                            <i class="fas fa-chevron-down text-green-600 transition-transform duration-200"></i>
                        </button>
                        <div class="faq-answer">
                            <div>
                                <div class="p-6 pt-0 text-gray-600">
                                    <p>Currently, we deliver to Central, Dahican, Badas, Matiao, and Madang. We are expanding quickly! Please <a href="#contact" class="text-green-600 hover:underline">contact us</a> to let us know where you'd like us to deliver next.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item bg-white rounded-2xl shadow-lg overflow-hidden">
                        <button class="faq-toggle w-full flex justify-between items-center text-left p-6 hover:bg-gray-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-inset">
                            <h3 class="text-lg font-semibold text-gray-900">How do I return a product?</h3>
                            <i class="fas fa-chevron-down text-green-600 transition-transform duration-200"></i>
                        </button>
                        <div class="faq-answer">
                            <div>
                                <div class="p-6 pt-0 text-gray-600">
                                    <p>Due to the perishable nature of our products, we do not accept returns. However, if you are unsatisfied with the quality of any item, please refer to our policy on missing or damaged items, and we will make it right.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item bg-white rounded-2xl shadow-lg overflow-hidden">
                        <button class="faq-toggle w-full flex justify-between items-center text-left p-6 hover:bg-gray-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-inset">
                            <h3 class="text-lg font-semibold text-gray-900">Do I need an account to place an order?</h3>
                            <i class="fas fa-chevron-down text-green-600 transition-transform duration-200"></i>
                        </button>
                        <div class="faq-answer">
                            <div>
                                <div class="p-6 pt-0 text-gray-600">
                                    <p>Yes, creating an account allows you to track your orders, save your delivery address, and manage your subscriptions for a faster checkout experience in the future.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item bg-white rounded-2xl shadow-lg overflow-hidden">
                        <button class="faq-toggle w-full flex justify-between items-center text-left p-6 hover:bg-gray-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-inset">
                            <h3 class="text-lg font-semibold text-gray-900">What payment methods do you accept?</h3>
                            <i class="fas fa-chevron-down text-green-600 transition-transform duration-200"></i>
                        </button>
                        <div class="faq-answer">
                            <div>
                                <div class="p-6 pt-0 text-gray-600">
                                    <p>We accept all major credit cards (Visa, MasterCard, American Express), G-Cash, and Cash on Delivery (COD). You can select your preferred payment method at checkout.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item bg-white rounded-2xl shadow-lg overflow-hidden">
                        <button class="faq-toggle w-full flex justify-between items-center text-left p-6 hover:bg-gray-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-inset">
                            <h3 class="text-lg font-semibold text-gray-900">Can I change my order after it's placed?</h3>
                            <i class="fas fa-chevron-down text-green-600 transition-transform duration-200"></i>
                        </button>
                        <div class="faq-answer">
                            <div>
                                <div class="p-6 pt-0 text-gray-600">
                                    <p>If you need to make changes to your order, please contact us immediately at (123) 456-7890. We can modify orders as long as they have not yet been prepared for delivery by our team.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main>

    <?php
        include 'footer.html';
    ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const faqItems = document.querySelectorAll('.faq-item');

            faqItems.forEach(item => {
                const toggle = item.querySelector('.faq-toggle');

                toggle.addEventListener('click', () => {
                    // Check if this item is already active
                    const isActive = item.classList.contains('active');

                    // Optional: Close all other items
                    faqItems.forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.classList.remove('active');
                        }
                    });

                    // Toggle the clicked item
                    if (isActive) {
                        item.classList.remove('active');
                    } else {
                        item.classList.add('active');
                    }
                });
            });
        });
    </script>

</body>
</html>