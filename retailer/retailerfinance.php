<?php
session_start();

// Check if user is logged in and is a retailer
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'retailer' && $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Load database connection
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/supabase-api.php';

// Get user data from database
$api = getSupabaseAPI();
$userId = $_SESSION['user_id'];
$userData = null;
$profilePicture = '../images/default-avatar.svg'; // Default avatar
$userFullName = $_SESSION['full_name'] ?? 'Retailer';
$userEmail = $_SESSION['email'] ?? '';
$shopName = 'My Shop';

try {
    // Fetch user data from database
    $users = $api->select('users', ['id' => $userId]);
    if (!empty($users)) {
        $userData = $users[0];
        $userFullName = $userData['full_name'] ?? $userFullName;
        $userEmail = $userData['email'] ?? $userEmail;
        
        // Check if user has a profile picture
        if (!empty($userData['profile_picture'])) {
            $profilePath = '../' . ltrim($userData['profile_picture'], '/');
            if (file_exists($profilePath)) {
                $profilePicture = $profilePath;
            }
        }
        
        // Try to get retailer shop name
        if ($userData['user_type'] === 'retailer') {
            $retailers = $api->select('retailers', ['user_id' => $userId]);
            if (!empty($retailers)) {
                $shopName = $retailers[0]['shop_name'] ?? $shopName;
            }
        }
    }
} catch (Exception $e) {
    error_log("Error fetching user data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Reports – The Farmer's Mall</title>
    <script src="https://cdn.tailwindcss.com">    
    function toggleMobileMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }
    
    document.querySelectorAll('#sidebar a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 768) {
                toggleMobileMenu();
            }
        });
    });
</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7fbf8;
        }
        #content {
            min-height: calc(100vh - 200px);
        }
        footer {
            margin-top: auto;
        }
        /* Force sidebar to be full height with logout at bottom */
        #sidebar {
            min-height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
        }
        #sidebar > *:last-child {
            margin-top: auto !important;
            padding-top: 1rem !important;
            border-top: 1px solid #e5e7eb !important;
        }
        /* Mobile menu toggle */
        #mobileMenuBtn {
            display: none;
        }
        @media (max-width: 768px) {
            #mobileMenuBtn {
                display: flex;
            }
            #sidebar {
                position: fixed;
                left: -100%;
                top: 0;
                height: 100vh;
                z-index: 50;
                transition: left 0.3s ease;
            }
        #sidebar {
            min-height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
        }
        #sidebar > div:last-child {
            margin-top: auto !important;
        }
            #sidebar.active {
                left: 0;
            }
            #overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 40;
            }
            #overlay.active {
                display: block;
            }
        }
    </style>
</head>
<body>

<div class="flex flex-col min-h-screen">
    <!-- Mobile Menu Overlay -->
    <div id="overlay" onclick="toggleMobileMenu()"></div>
    
    <!-- Mobile Menu Button -->
    <button id="mobileMenuBtn" class="fixed top-4 left-4 z-50 bg-green-600 text-white p-3 rounded-lg shadow-lg md:hidden" onclick="toggleMobileMenu()">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>
    
    <div id="app" class="flex flex-1">
        
        <!-- Sidebar Navigation -->
        <nav id="sidebar" class="w-64 md:w-64 bg-white shadow-xl flex flex-col p-4 space-y-2 flex-shrink-0">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center mr-2">
                    <i class="fas fa-leaf text-white text-lg"></i>
                </div>
                <h1 class="text-2xl font-bold text-green-700">Farmers Mall</h1>
            </div>
            <a href="retailer-dashboard2.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 4h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Dashboard
            </a>
            <a href="retailerinventory.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0V9m0 2v2m-4-2h1m-1 0h-2m2 0v2m-2-2h-1m-1 0H5m-2 4h18m-9-4v8m-7 4h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Products & Inventory
            </a>
            <a href="retailerfulfillment.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5h6"></path></svg>
                Order Fulfillment
            </a>
            <a href="retailerfinance.php" class="nav-item flex items-center p-3 rounded-xl text-white bg-green-600 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2M9 14h6m-5 4h4m-4-8h4m-5-8h6a2 2 0 012 2v10a2 2 0 01-2 2h-6a2 2 0 01-2-2V6a2 2 0 012-2z"></path></svg>
                Financial Reports
            </a>
            <a href="retailercoupons.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11l4-4-4-4m0 16l4-4-4-4m-1-5a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Vouchers & Coupons
            </a>
            <a href="retailerreviews.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.193a2.003 2.003 0 013.902 0l1.018 2.062 2.277.33a2.003 2.003 0 011.11 3.407l-1.652 1.61.39 2.269a2.003 2.003 0 01-2.906 2.108L12 15.698l-2.035 1.071a2.003 2.003 0 01-2.906-2.108l.39-2.269-1.652-1.61a2.003 2.003 0 011.11-3.407l2.277-.33 1.018-2.062z"></path></svg>
                Reviews & Customers
            </a>

            <div class="mt-auto pt-4 border-t border-gray-100">
                <a href="../auth/logout.php" class="w-full flex items-center justify-center p-2 rounded-xl text-red-600 bg-red-50 hover:bg-red-100 transition duration-150 font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </a>
            </div>
        </nav>

        <div class="flex-1 flex flex-col min-h-screen">
            <header class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-end">
                    <div class="flex items-center space-x-6">
                        <a href="retailer-dashboard2.php" class="text-gray-600 hover:text-green-600"><i class="fa-solid fa-house"></i></a>
                        <a href="retailermessage.php" class="text-gray-600 hover:text-green-600"><i class="fa-regular fa-comment"></i></a>
                        <a href="retailernotifications.php" class="text-gray-600 hover:text-green-600 relative">
                        <i class="fa-regular fa-bell"></i>
                        </a>

                        <div class="relative inline-block text-left">
                            <button id="profileDropdownBtn" class="flex items-center" title="<?php echo htmlspecialchars($userFullName); ?>">
                                <img id="headerProfilePic" src="<?php echo htmlspecialchars($profilePicture); ?>?v=<?php echo time(); ?>" alt="<?php echo htmlspecialchars($userFullName); ?>" class="w-8 h-8 rounded-full cursor-pointer object-cover border-2 border-gray-200" onerror="this.src='../images/default-avatar.svg'">
                            </button>
                            <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-40 bg-white rounded-md shadow-lg border z-50">
                                <a href="retailerprofile.php" class="block px-4 py-2 hover:bg-gray-100">Profile</a>
                                <a href="retailerprofile.php#settings" class="block px-4 py-2 hover:bg-gray-100">Settings</a>
                                <a href="../auth/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
        
            <main id="content" class="p-8 transition-all duration-300 flex-1">
                <h2 class="text-3xl font-bold text-gray-800 mb-8">Payouts & Financial Reporting</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Next Payout Schedule</h3>
                        <p class="text-3xl font-bold text-gray-800">₱1,540.20</p>
                        <p class="text-gray-500 mt-2">Scheduled for: <?php echo date('l, M d, Y', strtotime('+7 days')); ?></p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Reports & Exports</h3>
                        <div class="space-y-3">
                            <button onclick="downloadSalesReport()" class="w-full text-left p-3 bg-green-50 hover:bg-green-100 rounded-lg text-green-700 font-medium transition duration-150">
                                <i class="fas fa-download mr-2"></i> Download Total Sales Report (CSV)
                            </button>
                            <button onclick="downloadTaxReport()" class="w-full text-left p-3 bg-green-50 hover:bg-green-100 rounded-lg text-green-700 font-medium transition duration-150">
                                <i class="fas fa-download mr-2"></i> Download Tax & Commission Report (CSV)
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                    <h3 class="px-6 py-4 text-xl font-semibold text-gray-700 border-b">Transaction History</h3>
                    <div class="p-6 text-gray-500">
                        <ul class="space-y-3">
                            <li class="flex justify-between items-center border-b pb-2 hover:bg-gray-50 cursor-pointer rounded-lg p-2 transition" onclick="showTransactionDetails('8291', 'Sale', 'Apples, Bread', 21.50, '2024-12-10', 'completed')">
                                <span class="text-sm font-medium">Sale #8291 (Apples, Bread)</span>
                                <span class="text-sm text-green-600 font-semibold">+ ₱21.50</span>
                            </li>
                            <li class="flex justify-between items-center border-b pb-2 hover:bg-gray-50 cursor-pointer rounded-lg p-2 transition" onclick="showTransactionDetails('P001-2024', 'Payout', 'Monthly payout to bank account', -890.00, '2024-12-09', 'completed')">
                                <span class="text-sm font-medium">Payout ID P001-2024</span>
                                <span class="text-sm text-red-600 font-semibold">- ₱890.00</span>
                            </li>
                            <li class="flex justify-between items-center border-b pb-2 hover:bg-gray-50 cursor-pointer rounded-lg p-2 transition" onclick="showTransactionDetails('8290', 'Sale', 'Potatoes', 12.00, '2024-12-09', 'completed')">
                                <span class="text-sm font-medium">Sale #8290 (Potatoes)</span>
                                <span class="text-sm text-green-600 font-semibold">+ ₱12.00</span>
                            </li>
                            <li class="flex justify-between items-center border-b pb-2 hover:bg-gray-50 cursor-pointer rounded-lg p-2 transition" onclick="showTransactionDetails('8289', 'Sale', 'Tomatoes, Lettuce', 35.75, '2024-12-08', 'completed')">
                                <span class="text-sm font-medium">Sale #8289 (Tomatoes, Lettuce)</span>
                                <span class="text-sm text-green-600 font-semibold">+ ₱35.75</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
</div>

<!-- Transaction Details Modal -->
<div id="transactionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
            <h3 class="text-2xl font-bold text-gray-800">Transaction Details</h3>
            <button onclick="closeTransactionModal()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        
        <div class="p-6 space-y-6">
            <!-- Transaction ID -->
            <div class="flex items-center justify-between pb-4 border-b">
                <span class="text-gray-600 font-medium">Transaction ID</span>
                <span id="modalTransactionId" class="text-gray-800 font-semibold text-lg"></span>
            </div>
            
            <!-- Type -->
            <div class="flex items-center justify-between pb-4 border-b">
                <span class="text-gray-600 font-medium">Type</span>
                <span id="modalTransactionType" class="px-4 py-2 rounded-full text-sm font-semibold"></span>
            </div>
            
            <!-- Description -->
            <div class="pb-4 border-b">
                <span class="text-gray-600 font-medium block mb-2">Description</span>
                <p id="modalTransactionDesc" class="text-gray-800"></p>
            </div>
            
            <!-- Amount -->
            <div class="flex items-center justify-between pb-4 border-b">
                <span class="text-gray-600 font-medium">Amount</span>
                <span id="modalTransactionAmount" class="text-2xl font-bold"></span>
            </div>
            
            <!-- Date -->
            <div class="flex items-center justify-between pb-4 border-b">
                <span class="text-gray-600 font-medium">Date</span>
                <span id="modalTransactionDate" class="text-gray-800 font-semibold"></span>
            </div>
            
            <!-- Status -->
            <div class="flex items-center justify-between pb-4 border-b">
                <span class="text-gray-600 font-medium">Status</span>
                <span id="modalTransactionStatus" class="px-4 py-2 rounded-full text-sm font-semibold"></span>
            </div>
            
            <!-- Additional Info -->
            <div class="bg-green-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-700 mb-2">Additional Information</h4>
                <div class="text-sm text-gray-600 space-y-1">
                    <p><i class="fas fa-info-circle mr-2 text-green-600"></i>Transaction processed successfully</p>
                    <p><i class="fas fa-shield-alt mr-2 text-green-600"></i>Secure payment verified</p>
                    <p><i class="fas fa-receipt mr-2 text-green-600"></i>Receipt available for download</p>
                </div>
            </div>
        </div>
        
        <div class="sticky bottom-0 bg-gray-50 px-6 py-4 border-t flex justify-end space-x-3">
            <button onclick="closeTransactionModal()" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg font-medium text-gray-700 transition">
                Close
            </button>
            <button class="px-6 py-2 bg-green-600 hover:bg-green-700 rounded-lg font-medium text-white transition">
                <i class="fas fa-download mr-2"></i>Download Receipt
            </button>
        </div>
    </div>
</div>

<script>
    // Transaction Modal Functions
    function showTransactionDetails(id, type, description, amount, date, status) {
        const modal = document.getElementById('transactionModal');
        
        // Set transaction details
        document.getElementById('modalTransactionId').textContent = id;
        document.getElementById('modalTransactionDesc').textContent = description;
        
        // Format amount
        const amountElement = document.getElementById('modalTransactionAmount');
        const formattedAmount = new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP'
        }).format(Math.abs(amount));
        
        if (amount >= 0) {
            amountElement.textContent = '+ ' + formattedAmount;
            amountElement.className = 'text-2xl font-bold text-green-600';
        } else {
            amountElement.textContent = '- ' + formattedAmount;
            amountElement.className = 'text-2xl font-bold text-red-600';
        }
        
        // Set type badge
        const typeElement = document.getElementById('modalTransactionType');
        typeElement.textContent = type;
        if (type === 'Sale') {
            typeElement.className = 'px-4 py-2 rounded-full text-sm font-semibold bg-green-100 text-green-700';
        } else if (type === 'Payout') {
            typeElement.className = 'px-4 py-2 rounded-full text-sm font-semibold bg-blue-100 text-blue-700';
        } else {
            typeElement.className = 'px-4 py-2 rounded-full text-sm font-semibold bg-gray-100 text-gray-700';
        }
        
        // Format date
        const dateObj = new Date(date);
        document.getElementById('modalTransactionDate').textContent = dateObj.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        // Set status badge
        const statusElement = document.getElementById('modalTransactionStatus');
        statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        if (status === 'completed') {
            statusElement.className = 'px-4 py-2 rounded-full text-sm font-semibold bg-green-100 text-green-700';
        } else if (status === 'pending') {
            statusElement.className = 'px-4 py-2 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-700';
        } else {
            statusElement.className = 'px-4 py-2 rounded-full text-sm font-semibold bg-red-100 text-red-700';
        }
        
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function closeTransactionModal() {
        const modal = document.getElementById('transactionModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    
    // Download Reports Functions
    function downloadSalesReport() {
        // Sample sales data
        const salesData = [
            ['Order ID', 'Customer', 'Products', 'Amount', 'Date', 'Status'],
            ['8291', 'John Doe', 'Apples, Bread', '₱21.50', '2024-12-10', 'Completed'],
            ['8290', 'Jane Smith', 'Potatoes', '₱12.00', '2024-12-09', 'Completed'],
            ['8289', 'Bob Johnson', 'Tomatoes, Lettuce', '₱35.75', '2024-12-08', 'Completed'],
            ['8288', 'Alice Brown', 'Carrots, Onions', '₱28.30', '2024-12-07', 'Completed'],
            ['8287', 'Charlie Wilson', 'Cabbage, Peppers', '₱45.60', '2024-12-06', 'Completed']
        ];
        
        // Convert to CSV
        const csv = salesData.map(row => row.join(',')).join('\n');
        
        // Create download
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `sales_report_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        
        // Show success message
        alert('Sales report downloaded successfully!');
    }
    
    function downloadTaxReport() {
        // Sample tax and commission data
        const taxData = [
            ['Transaction ID', 'Type', 'Gross Amount', 'Commission (10%)', 'Tax (12%)', 'Net Amount', 'Date'],
            ['8291', 'Sale', '₱21.50', '₱2.15', '₱2.58', '₱16.77', '2024-12-10'],
            ['8290', 'Sale', '₱12.00', '₱1.20', '₱1.44', '₱9.36', '2024-12-09'],
            ['8289', 'Sale', '₱35.75', '₱3.58', '₱4.29', '₱27.88', '2024-12-08'],
            ['8288', 'Sale', '₱28.30', '₱2.83', '₱3.40', '₱22.07', '2024-12-07'],
            ['P001-2024', 'Payout', '-₱890.00', '₱0.00', '₱0.00', '-₱890.00', '2024-12-09']
        ];
        
        // Convert to CSV
        const csv = taxData.map(row => row.join(',')).join('\n');
        
        // Create download
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `tax_commission_report_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        
        // Show success message
        alert('Tax & Commission report downloaded successfully!');
    }
    
    const btn = document.getElementById('profileDropdownBtn');
    const menu = document.getElementById('profileDropdown');

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        menu.classList.toggle('hidden');
    });

    document.addEventListener('click', () => {
        if (!menu.classList.contains('hidden')) {
            menu.classList.add('hidden');
        }
    });

    let lastProfilePicture = '<?php echo htmlspecialchars($profilePicture); ?>';
    
    async function checkProfileUpdates() {
        try {
            const response = await fetch('../api/get-profile.php');
            const result = await response.json();
            
            if (result.success && result.data) {
                const profilePicElement = document.getElementById('headerProfilePic');
                if (result.data.profile_picture && result.data.profile_picture !== lastProfilePicture) {
                    profilePicElement.src = result.data.profile_picture + '?t=' + new Date().getTime();
                    lastProfilePicture = result.data.profile_picture;
                }
                if (result.data.full_name) {
                    profilePicElement.parentElement.setAttribute('title', result.data.full_name);
                }
            }
        } catch (error) {
            console.error('Error checking profile updates:', error);
        }
    }
    
    setInterval(checkProfileUpdates, 5000);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            checkProfileUpdates();
        }
    });
    window.addEventListener('load', () => {
        setTimeout(checkProfileUpdates, 1000);
    });
    
    function toggleMobileMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }
    
    document.querySelectorAll('#sidebar a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 768) {
                toggleMobileMenu();
            }
        });
    });
</script>
</body>
</html>
