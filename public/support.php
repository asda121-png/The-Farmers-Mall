<?php
// Only include the modal HTML if this is NOT a POST request for the contact form.
// This prevents the modal's HTML from being sent during an AJAX call, which would corrupt the JSON response.
if (!isset($_POST['contact_form'])) {
    include '../auth/login.php';
    include '../auth/register.php';
}
 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer-master/src/Exception.php';
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';

$form_message = '';

// Function to send a JSON response and exit
function send_json_response($status, $message) {
    header('Content-Type: application/json');
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['contact_form'])) {    
    // Check if it's an AJAX request
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    // --- Form Processing Logic ---

    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $subject = strip_tags(trim($_POST["subject"]));
    $message = trim($_POST["message"]);

    if (!empty($name) && !empty($email) && !empty($subject) && !empty($message) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mail = new PHPMailer(true); // Passing `true` enables exceptions

        try {
            // Server settings for sending email via Gmail SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            // IMPORTANT: Replace with your Gmail address and an App Password.
            // It's best practice to use environment variables for credentials.
            $mail->Username   = 'mati.farmersmall@gmail.com'; // Your Gmail address
            $mail->Password   = 'hiov goyk hblw nizf';    // Your Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('mati.farmersmall@gmail.com', 'Farmers Mall Contact Form'); // Set a fixed "from" address
            $mail->addAddress('mati.farmersmall@gmail.com', 'Farmers Mall Support'); // The address that will receive the form submission
            $mail->addReplyTo($email, $name); // So you can reply directly to the user

            // Content
            $mail->isHTML(true);
            $mail->Subject = "Subject: " . htmlspecialchars($subject);
            $mail->Body    = "<p>You have received a new message from your support form.</p><strong>Name:</strong> " . htmlspecialchars($name) . "<br><strong>Email:</strong> {$email}<br><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message));
            $mail->AltBody = "Name: {$name}\nEmail: {$email}\nMessage:\n{$message}";

            $mail->send();
            $success_message = 'Thank you for your message! We will get back to you shortly.';
            if ($is_ajax) {
                send_json_response('success', $success_message);
            }
            $form_message = '<div id="form-success-msg" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50">' . $success_message . '</div>';
        } catch (Exception $e) {
            $error_message = 'Message could not be sent. Please try again later.';
            if ($is_ajax) {
                send_json_response('error', $error_message);
            }
            $form_message = '<div id="form-error-msg" class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50">' . $error_message . '</div>';
            error_log("Mailer Error: " . $mail->ErrorInfo); // Log the detailed error
        }
    } else {
        $error_message = 'Please fill out all fields correctly.';
        if ($is_ajax) { send_json_response('error', $error_message); }
        $form_message = '<div id="form-error-msg" class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50">' . $error_message . '</div>';
    }
    // If this was an AJAX request, we've already sent a JSON response and exited.
    // If it's a non-AJAX POST, we'll fall through and render the page with the message.
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - Farmers Mall</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        html { scroll-behavior: smooth; scroll-padding-top: 100px; }
        .faq-item .faq-answer { display: grid; grid-template-rows: 0fr; transition: grid-template-rows 0.3s ease-out; }
        .faq-item.active .faq-answer { grid-template-rows: 1fr; }
        .faq-item.active .faq-toggle i { transform: rotate(180deg); }
    </style>
</head>
<body class="bg-[#f6fff8] text-gray-800 antialiased">
    <?php
        // Include modal logic at the top to handle sessions and form posts before any HTML is sent.
        // Note: This is a placeholder for where the includes *should* go if they weren't already at the top.
        // The actual includes have been moved to the very top of the file.


        // Include the header
        include '../includes/header.php';
    ?>

    <main class="pt-[100px] bg-[#f6fff8] text-gray-800 antialiased"> 
        <style>
            /* Style for highlighting search results */
            .highlight {
                background-color: #d1fae5; /* Tailwind green-100 */
                transition: background-color 0.5s ease;
            }
            .search-notification {
                transition: opacity 0.3s ease-out;
            }
        </style>
        

        <section class="px-6 pb-24">
            <div class="max-w-6xl mx-auto">
                <h2 class="text-3xl font-bold text-gray-900 text-center mb-10">How can we help?</h2>
                
                <div class="relative max-w-2xl mx-auto mb-16">
                    <input 
                        id="faq-search"
                        type="text" 
                        placeholder="Search for articles, topics, etc." 
                        class="w-full pl-6 pr-16 py-4 border border-gray-300 rounded-full shadow-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 text-lg transition-all duration-200"
                    />
                    <button id="faq-search-btn" class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-green-600 transition-colors duration-200">
                        <i class="fas fa-search text-xl"></i>
                    </button>
                    <!-- Notification for no search results -->
                    <div id="search-notification" class="text-center text-red-600 mt-2 hidden opacity-0 search-notification"></div>
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

                    <!-- New Card 1 -->
                    <a href="../retailer/startselling.php" class="bg-white p-8 rounded-2xl shadow-lg flex flex-col items-center text-center hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-5 group-hover:bg-green-200 transition-colors duration-300">
                            <i class="fas fa-store text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2 group-hover:text-green-700 transition-colors duration-300">Selling on Farmers Mall</h3>
                        <p class="text-gray-600 text-sm">Information for our valued farm partners and sellers.</p>
                    </a>

                    <!-- New Card 2 -->
                    <a href="#" class="bg-white p-8 rounded-2xl shadow-lg flex flex-col items-center text-center hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-5 group-hover:bg-green-200 transition-colors duration-300">
                            <i class="fas fa-shield-alt text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2 group-hover:text-green-700 transition-colors duration-300">Safety & Security</h3>
                        <p class="text-gray-600 text-sm">Learn how we protect your account and data.</p>
                    </a>

                    <!-- New Card 3 -->
                    <a href="#" class="bg-white p-8 rounded-2xl shadow-lg flex flex-col items-center text-center hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-5 group-hover:bg-green-200 transition-colors duration-300">
                            <i class="fas fa-handshake text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2 group-hover:text-green-700 transition-colors duration-300">Community Guidelines</h3>
                        <p class="text-gray-600 text-sm">Our rules for a safe and respectful marketplace.</p>
                    </a>

                </div>
            </div>
        </section>

        <section id="contact" class="px-6 pb-24">
            <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-12 items-start">
                
                <div class="bg-white p-8 rounded-2xl shadow-lg">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6" id="form-title">Send Us a Message</h2>
                    <div id="form-notification-area"><?php echo $form_message; ?></div>
                    <form id="support-form" action="#contact" method="POST" class="space-y-6">
                        <input type="hidden" name="contact_form" value="1">
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
                            <button type="submit" id="submit-btn" class="w-full px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
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
                                <a href="mailto:mati.farmersmall@gmail.com" class="text-green-600 hover:underline">mati.farmersmall@gmail.com</a>
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
                        <button class="faq-toggle w-full flex justify-between items-center text-left p-6 hover:bg-gray-50 transition-colors duration-200 focus:outline-none ">
                            <h3 class="text-lg font-semibold text-gray-900">What are your delivery hours?</h3>
                            <i class="fas fa-chevron-down text-green-600 transition-transform duration-200"></i>
                        </button>
                        <div class="faq-answer">
                            <div class="overflow-hidden">
                                <div class="p-6 pt-0 text-gray-600">
                                    <p>We deliver from 8:00 AM to 5:00 PM, Monday through Saturday. You can select your preferred 2-hour delivery window during checkout.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item bg-white rounded-2xl shadow-lg overflow-hidden">
                        <button class="faq-toggle w-full flex justify-between items-center text-left p-6 hover:bg-gray-50 transition-colors duration-200 focus:outline-none">
                            <h3 class="text-lg font-semibold text-gray-900">What if an item is missing or damaged?</h3>
                            <i class="fas fa-chevron-down text-green-600 transition-transform duration-200"></i>
                        </button>
                        <div class="faq-answer">
                            <div class="overflow-hidden">
                                <div class="p-6 pt-0 text-gray-600">
                                    <p>We're so sorry for any trouble! Please contact us within 24 hours of your delivery at <a href="mailto:support@farmersmall.com" class="text-green-600 hover:underline">support@farmersmall.com</a> or call us at (123) 456-7890. We will happily arrange a refund or a re-delivery for the affected items.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item bg-white rounded-2xl shadow-lg overflow-hidden">
                        <button class="faq-toggle w-full flex justify-between items-center text-left p-6 hover:bg-gray-50 transition-colors duration-200 focus:outline-none">
                            <h3 class="text-lg font-semibold text-gray-900">Do you deliver outside the City of Mati?</h3>
                            <i class="fas fa-chevron-down text-green-600 transition-transform duration-200"></i>
                        </button>
                        <div class="faq-answer">
                            <div class="overflow-hidden">
                                <div class="p-6 pt-0 text-gray-600">
                                    <p>Currently, we deliver to Central, Dahican, Badas, Matiao, and Madang. We are expanding quickly! Please <a href="#contact" class="text-green-600 hover:underline">contact us</a> to let us know where you'd like us to deliver next.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item bg-white rounded-2xl shadow-lg overflow-hidden">
                        <button class="faq-toggle w-full flex justify-between items-center text-left p-6 hover:bg-gray-50 transition-colors duration-200 focus:outline-none">
                            <h3 class="text-lg font-semibold text-gray-900">How do I return a product?</h3>
                            <i class="fas fa-chevron-down text-green-600 transition-transform duration-200"></i>
                        </button>
                        <div class="faq-answer">
                            <div class="overflow-hidden">
                                <div class="p-6 pt-0 text-gray-600">
                                    <p>Due to the perishable nature of our products, we do not accept returns. However, if you are unsatisfied with the quality of any item, please refer to our policy on missing or damaged items, and we will make it right.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item bg-white rounded-2xl shadow-lg overflow-hidden">
                        <button class="faq-toggle w-full flex justify-between items-center text-left p-6 hover:bg-gray-50 transition-colors duration-200 focus:outline-none ">
                            <h3 class="text-lg font-semibold text-gray-900">Do I need an account to place an order?</h3>
                            <i class="fas fa-chevron-down text-green-600 transition-transform duration-200"></i>
                        </button>
                        <div class="faq-answer">
                            <div class="overflow-hidden">
                                <div class="p-6 pt-0 text-gray-600">
                                    <p>Yes, creating an account allows you to track your orders, save your delivery address, and manage your subscriptions for a faster checkout experience in the future.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item bg-white rounded-2xl shadow-lg overflow-hidden">
                        <button class="faq-toggle w-full flex justify-between items-center text-left p-6 hover:bg-gray-50 transition-colors duration-200 focus:outline-none">
                            <h3 class="text-lg font-semibold text-gray-900">What payment methods do you accept?</h3>
                            <i class="fas fa-chevron-down text-green-600 transition-transform duration-200"></i>
                        </button>
                        <div class="faq-answer">
                            <div class="overflow-hidden">
                                <div class="p-6 pt-0 text-gray-600">
                                    <p>We accept all major credit cards (Visa, MasterCard, American Express), G-Cash, and Cash on Delivery (COD). You can select your preferred payment method at checkout.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item bg-white rounded-2xl shadow-lg overflow-hidden">
                        <button class="faq-toggle w-full flex justify-between items-center text-left p-6 hover:bg-gray-50 transition-colors duration-200 focus:outline-none">
                            <h3 class="text-lg font-semibold text-gray-900">Can I change my order after it's placed?</h3>
                            <i class="fas fa-chevron-down text-green-600 transition-transform duration-200"></i>
                        </button>
                        <div class="faq-answer">
                            <div class="overflow-hidden">
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
        include '../includes/footer.php';
    ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- FAQ Accordion Logic ---
            const faqItems = document.querySelectorAll('.faq-item');

            faqItems.forEach(item => {
                const toggle = item.querySelector('.faq-toggle');

                toggle.addEventListener('click', () => {
                    item.classList.toggle('active');
                });
            });

            // --- Search Bar Logic ---
            const searchInput = document.getElementById('faq-search');
            const searchBtn = document.getElementById('faq-search-btn');
            const notificationEl = document.getElementById('search-notification');

            function performSearch() {
                const query = searchInput.value.toLowerCase().trim();
                if (!query) return;

                let matchFound = false;

                // Clear previous highlights
                faqItems.forEach(item => {
                    item.classList.remove('highlight');
                });
                notificationEl.classList.add('hidden', 'opacity-0');

                for (const item of faqItems) {
                    const question = item.querySelector('h3').textContent.toLowerCase();
                    const answer = item.querySelector('.faq-answer p').textContent.toLowerCase();

                    if (question.includes(query) || answer.includes(query)) {
                        matchFound = true;

                        // Scroll the found item into the center of the view
                        item.scrollIntoView({ behavior: 'smooth', block: 'center' });

                        // Expand and highlight the item
                        item.classList.add('active', 'highlight');

                        // Remove highlight after a delay
                        setTimeout(() => {
                            item.classList.remove('highlight');
                        }, 2500);

                        break; // Stop after finding the first match
                    }
                }

                if (!matchFound) {
                    notificationEl.textContent = `No results found for "${searchInput.value}".`;
                    notificationEl.classList.remove('hidden');
                    setTimeout(() => notificationEl.classList.remove('opacity-0'), 10);
                }
            }

            // Trigger search on button click
            searchBtn.addEventListener('click', performSearch);

            // Trigger search on "Enter" key press
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault(); // Prevent form submission if it's in a form
                    performSearch();
                }
            });

            // Hide form submission messages after a delay
            setTimeout(() => {
                const formMsg = document.getElementById('form-success-msg') || document.getElementById('form-error-msg');
                if (formMsg) {
                    formMsg.style.transition = 'opacity 0.5s ease';
                    formMsg.style.opacity = '0';
                    setTimeout(() => formMsg.remove(), 500);
                }
            }, 5000);
        });

        // --- AJAX Form Submission ---
        const supportForm = document.getElementById('support-form');
        if (supportForm) {
            supportForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent the default page reload

                const submitBtn = document.getElementById('submit-btn');
                const notificationArea = document.getElementById('form-notification-area');
                const formData = new FormData(this);

                // Disable button and show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sending...';

                // Clear previous messages
                notificationArea.innerHTML = '';

                fetch('support.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Use the existing centered notification for a better UX
                        if (typeof showCenteredNotification === 'function') {
                            showCenteredNotification(data.message, 'success');
                        } else {
                            notificationArea.innerHTML = `<div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50">${data.message}</div>`;
                        }
                        supportForm.reset(); // Clear the form on success
                    } else {
                        // Show error message above the form
                        notificationArea.innerHTML = `<div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50">${data.message}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    notificationArea.innerHTML = `<div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50">A network error occurred. Please try again.</div>`;
                })
                .finally(() => {
                    // Re-enable button
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Send Message';
                });
            });
        }
    </script>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed top-5 right-5 z-[100]"></div>

    <style>
        /* Field Error Styles for Modals */
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

        /* Centered Notification Style */
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
    </style>
    <script src="../assets/js/modal-handler.js"></script>
</body>
</html>