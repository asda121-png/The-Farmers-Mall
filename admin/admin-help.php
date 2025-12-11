<?php
// admin-help.php
// Mock Help Data
$faqs = [
    [
        "question" => "How do I add a new product?",
        "answer" => "Navigate to the 'Products' page using the sidebar. Click the 'Add Product' button in the top right corner. Fill in the required details such as name, category, price, and stock level, then click 'Save'."
    ],
    [
        "question" => "How do I process a refund?",
        "answer" => "Go to the 'Orders' page and select the specific order. If the order status is eligible, you will see a 'Refund' button. Click it and follow the prompts to return funds to the customer's wallet or original payment method."
    ],
    [
        "question" => "Can I export my sales data?",
        "answer" => "Yes. On the 'Dashboard' or 'Orders' page, look for the 'Export' button near the top. You can download reports in CSV or PDF format."
    ],
    [
        "question" => "How do I approve a new retailer?",
        "answer" => "Visit the 'Retailers' page. Filter by 'Pending' status. Review the retailer's documents and profile, then click the 'Approve' checkmark icon to activate their account."
    ],
    [
        "question" => "How do I change my password?",
        "answer" => "Go to 'Settings' > 'Security'. Enter your current password and your new desired password, then click 'Update Password'."
    ]
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Farmers Mall Admin Panel - Help Center</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="admin-theme.css">
  <style>
    /* Global Styles */
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f7f9fc;
    }

    .custom-scrollbar::-webkit-scrollbar {
      width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
      background-color: #4b5563;
      border-radius: 2px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
      background: transparent;
    }

    .card-shadow {
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
    }
    
    .bg-green-950 {
        background-color: #184D34;
    }

    /* Accordion Styles */
    .faq-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
    }
    .faq-toggle:checked + .faq-label + .faq-content {
        max-height: 200px; /* Approximate max height */
    }
    .faq-toggle:checked + .faq-label i {
        transform: rotate(180deg);
    }
  </style>
</head>

<body class="flex min-h-screen bg-gray-50 text-gray-800">

  <aside class="w-64 flex flex-col justify-between p-4 bg-green-950 text-gray-100 rounded-r-xl shadow-2xl transition-all duration-300">
    <div>
      <div class="flex items-center gap-3 mb-8 px-2 py-2">
        <div class="w-8 h-8 flex items-center justify-center rounded-full bg-white">
          <i class="fas fa-leaf text-green-700 text-lg"></i>
        </div>
        <h1 class="text-xl font-bold">Farmers Mall</h1>
      </div>

      <p class="text-xs font-semibold text-green-300 uppercase tracking-widest mb-2 px-2">GENERAL</p>
      <nav class="space-y-1">
        <a href="admin-dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-tachometer-alt w-5"></i>
          <span>Dashboard</span>
        </a>

        <!-- UPDATED: Removed 'bg-green-700 text-white' to remove permanent highlight. Added hover effects. -->
        
      </nav>
        
        <a href="admin-orders.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-receipt w-5"></i>
          <span>Orders</span>
        </a>
        <a href="admin-manage-users.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-user-gear w-5"></i>
          <span>Manage Users</span>
        </a>
      </nav>

      <p class="text-xs font-semibold text-green-300 uppercase tracking-widest my-4 px-2">ACCOUNT</p>
      <nav class="space-y-1">
        <a href="admin-settings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-cog w-5"></i>
          <span>Settings</span>
        </a>
        <a href="admin-help.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white bg-green-700 font-semibold card-shadow">
          <i class="fa-solid fa-circle-info w-5 text-green-200"></i>
          <span>Help</span>
        </a>
        
      </nav>
    </div>

    <div class="mt-8 pt-4 border-t border-green-800">
      <button id="logoutButton" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-red-600 hover:text-white transition-colors duration-200 text-gray-300">
        <i class="fa-solid fa-sign-out-alt w-5"></i>
        <span>Logout</span>
      </button>
    </div>
  </aside>

  <div class="flex-1 p-6 space-y-6 custom-scrollbar">

    <header class="bg-white p-4 rounded-xl card-shadow flex justify-between items-center sticky top-6 z-10 w-full">
      <div class="relative w-full max-w-lg hidden md:block">
        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        <input type="text" placeholder="Search for help topics..."
          class="w-full py-2 pl-10 pr-4 border border-gray-200 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors">
      </div>

      <div class="flex items-center gap-4 ml-auto">
        <i class="fa-regular fa-bell text-xl text-gray-500 hover:text-green-600 cursor-pointer relative">
            <span class="absolute -top-1 -right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
        </i>
        <div class="w-px h-6 bg-gray-200 mx-2 hidden sm:block"></div>
        <div class="flex items-center gap-2 cursor-pointer">
          <img src="https://randomuser.me/api/portraits/men/40.jpg" class="w-9 h-9 rounded-full border-2 border-green-500" alt="Admin">
        </div>
      </div>
    </header>

    <div class="text-center md:text-left mb-4">
        <h2 class="text-3xl font-bold text-gray-900">Help Center</h2>
        <p class="text-sm text-gray-500">Find answers and get support for the admin panel</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl card-shadow text-center hover:shadow-lg transition-shadow cursor-pointer group">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-200 transition-colors">
                <i class="fa-solid fa-book text-2xl text-blue-600"></i>
            </div>
            <h3 class="font-bold text-gray-900 mb-2">User Guide</h3>
            <p class="text-sm text-gray-500">Comprehensive documentation on how to use the admin panel features.</p>
        </div>
        <div class="bg-white p-6 rounded-xl card-shadow text-center hover:shadow-lg transition-shadow cursor-pointer group">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-green-200 transition-colors">
                <i class="fa-solid fa-play-circle text-2xl text-green-600"></i>
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Video Tutorials</h3>
            <p class="text-sm text-gray-500">Watch step-by-step videos on managing products, orders, and more.</p>
        </div>
        <div class="bg-white p-6 rounded-xl card-shadow text-center hover:shadow-lg transition-shadow cursor-pointer group">
            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-purple-200 transition-colors">
                <i class="fa-solid fa-life-ring text-2xl text-purple-600"></i>
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Technical Support</h3>
            <p class="text-sm text-gray-500">Contact our support team for technical issues or account problems.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-4">
            <h3 class="font-bold text-xl text-gray-900 mb-2">Frequently Asked Questions</h3>
            
            <?php foreach ($faqs as $index => $faq): ?>
            <div class="bg-white rounded-xl card-shadow overflow-hidden">
                <input type="checkbox" id="faq-<?php echo $index; ?>" class="faq-toggle hidden">
                <label for="faq-<?php echo $index; ?>" class="faq-label flex justify-between items-center p-4 cursor-pointer hover:bg-gray-50 transition-colors">
                    <span class="font-medium text-gray-800"><?php echo $faq['question']; ?></span>
                    <i class="fa-solid fa-chevron-down text-gray-400 transition-transform duration-300"></i>
                </label>
                <div class="faq-content bg-gray-50 px-4 border-t border-gray-100">
                    <p class="py-4 text-sm text-gray-600 leading-relaxed"><?php echo $faq['answer']; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-xl card-shadow sticky top-28">
                <h3 class="font-bold text-xl text-gray-900 mb-4">Still need help?</h3>
                <p class="text-sm text-gray-500 mb-6">Fill out the form below and our support team will get back to you within 24 hours.</p>
                
                <form>
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Subject</label>
                        <select class="w-full p-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-green-500 focus:border-green-500">
                            <option>General Inquiry</option>
                            <option>Technical Issue</option>
                            <option>Billing Question</option>
                            <option>Feature Request</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Message</label>
                        <textarea rows="4" class="w-full p-2 border border-gray-300 rounded-lg text-sm outline-none focus:ring-green-500 focus:border-green-500" placeholder="Describe your issue..."></textarea>
                    </div>
                    <button type="button" class="w-full bg-green-700 text-white py-2 rounded-lg font-bold hover:bg-green-800 transition-colors">
                        Send Message
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-gray-100 text-center">
                    <p class="text-xs text-gray-400">Or email us directly at</p>
                    <a href="mailto:support@farmersmall.com" class="text-green-600 font-medium hover:underline">support@farmersmall.com</a>
                </div>
            </div>
        </div>

    </div>

    <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-30 hidden flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl card-shadow p-8 w-full max-w-sm text-center">
        <div class="text-red-500 text-4xl mb-4">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="font-bold text-xl mb-2 text-gray-900">Confirm Logout</h3>
        <p class="text-gray-600 text-sm mb-6">Are you sure you want to log out of the Farmers Mall Admin Panel?</p>
        <div class="flex justify-center gap-4">
          <button id="cancelLogout" class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
            Cancel
          </button>
          <a href="../public/index.php" id="confirmLogout" class="px-6 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">
            Logout
          </a>
        </div>
      </div>
    </div>

  </div> <script src="admin-theme.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Logout Modal Logic
      const logoutButton = document.getElementById('logoutButton');
      const logoutModal = document.getElementById('logoutModal');
      const cancelLogout = document.getElementById('cancelLogout');

      logoutButton.addEventListener('click', function() {
        logoutModal.classList.remove('hidden');
        logoutModal.classList.add('flex');
      });

      cancelLogout.addEventListener('click', function() {
        logoutModal.classList.add('hidden');
        logoutModal.classList.remove('flex');
      });

      logoutModal.addEventListener('click', function(e) {
          if (e.target === logoutModal) {
              logoutModal.classList.add('hidden');
              logoutModal.classList.remove('flex');
          }
      });
    });
  </script>
</body>
</html>