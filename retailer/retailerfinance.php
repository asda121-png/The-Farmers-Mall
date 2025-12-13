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
        /* Sticky sidebar - fixed position */
        #sidebar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            height: 100vh !important;
            overflow-y: auto !important;
            display: flex !important;
            flex-direction: column !important;
            z-index: 40 !important;
        }
        /* Add left margin to main content to account for fixed sidebar */
        #app {
            margin-left: 16rem !important;
        }
        /* Mobile menu toggle */
        #mobileMenuBtn {
            display: none;
        }
        @media (max-width: 768px) {
            #mobileMenuBtn {
                display: flex;
            }
            #app {
                margin-left: 0 !important;
            }
            #sidebar {
                left: -100%;
                z-index: 50;
                transition: left 0.3s ease;
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
        <i class="fas fa-bars text-xl"></i>
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
                <i class="fas fa-tachometer-alt text-lg mr-3"></i>
                Dashboard
            </a>
            <a href="retailerinventory.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <i class="fas fa-boxes text-lg mr-3"></i>
                Products & Inventory
            </a>
            <a href="retailerfulfillment.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <i class="fas fa-clipboard-list text-lg mr-3"></i>
                Order Fulfillment
            </a>
            <a href="retailerfinance.php" class="nav-item flex items-center p-3 rounded-xl text-white bg-green-600 transition duration-150">
                <i class="fas fa-chart-line text-lg mr-3"></i>
                Financial Reports
            </a>
            <a href="retailerreviews.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <i class="fas fa-star text-lg mr-3"></i>
                Reviews & Customers
            </a>
        </nav>

        <div class="flex-1 flex flex-col min-h-screen">
            <header class="bg-white shadow-sm sticky top-0 z-30">
                <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-end">
                    <div class="flex items-center space-x-6">
                        <a href="retailer-dashboard2.php" class="text-gray-600 hover:text-green-600 transition" title="Home"><i class="fa-solid fa-house text-xl"></i></a>

                        <!-- Notifications Icon -->
                        <div class="relative" id="notificationPreviewContainer">
                            <a href="retailernotifications.php" class="text-gray-600 hover:text-green-600 transition relative" title="Notifications" id="notificationIcon">
                                <i class="fa-regular fa-bell text-xl"></i>
                                <span id="notificationBadge" class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold rounded-full px-1.5 min-w-[1.125rem] h-[1.125rem] flex items-center justify-center hidden">0</span>
                            </a>
                            
                            <!-- Notification Preview Dropdown -->
                            <div id="notificationPreview" class="hidden absolute right-0 mt-3 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                                <div class="p-4 border-b border-gray-100">
                                    <h3 class="font-semibold text-gray-800">Notifications</h3>
                                </div>
                                <div id="notificationPreviewItems" class="max-h-96 overflow-y-auto">
                                    <div class="p-8 text-center text-gray-500">
                                        <i class="fas fa-bell text-4xl mb-2 text-gray-300"></i>
                                        <p class="text-sm">No notifications</p>
                                    </div>
                                </div>
                                <div class="p-4 border-t border-gray-100 bg-gray-50">
                                    <a href="retailernotifications.php" class="block w-full bg-green-600 text-white text-center py-2 rounded-lg hover:bg-green-700 transition font-medium">
                                        View All Notifications
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="relative" id="profileDropdownContainer">
                            <button id="profileDropdownBtn" class="flex items-center focus:outline-none" title="<?php echo htmlspecialchars($userFullName); ?>">
                                <?php if (!empty($profilePicture) && $profilePicture !== '../images/default-avatar.svg' && file_exists(__DIR__ . '/' . $profilePicture)): ?>
                                    <img id="headerProfilePic" src="<?php echo htmlspecialchars($profilePicture); ?>?v=<?php echo time(); ?>" alt="<?php echo htmlspecialchars($userFullName); ?>" class="w-8 h-8 rounded-full cursor-pointer object-cover border-2 border-gray-200" onerror="this.src='../images/default-avatar.svg'">
                                <?php else: ?>
                                    <div class="w-8 h-8 rounded-full cursor-pointer bg-green-600 flex items-center justify-center">
                                        <i class="fas fa-user text-white text-sm"></i>
                                    </div>
                                <?php endif; ?>
                            </button>
                            <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-64 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                                <!-- Profile Header -->
                                <div class="p-4 border-b border-gray-200">
                                    <div class="flex items-center space-x-3">
                                        <?php if (!empty($profilePicture) && $profilePicture !== '../images/default-avatar.svg' && file_exists(__DIR__ . '/' . $profilePicture)): ?>
                                            <img src="<?php echo htmlspecialchars($profilePicture); ?>?v=<?php echo time(); ?>" alt="<?php echo htmlspecialchars($userFullName); ?>" class="w-12 h-12 rounded-full object-cover border-2 border-gray-200" onerror="this.src='../images/default-avatar.svg'">
                                        <?php else: ?>
                                            <div class="w-12 h-12 rounded-full bg-green-600 flex items-center justify-center">
                                                <i class="fas fa-user text-white text-lg"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($userFullName); ?></p>
                                            <p class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars($userEmail); ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Menu Items -->
                                <div class="py-2">
                                    <a href="retailerprofile.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 transition">
                                        <i class="fas fa-user-circle text-gray-400 text-lg w-5"></i>
                                        <span class="ml-3 text-sm">Profile & Settings</span>
                                    </a>
                                    <a href="../auth/logout.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 transition">
                                        <i class="fas fa-sign-out-alt text-gray-400 text-lg w-5"></i>
                                        <span class="ml-3 text-sm">Logout</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
        
            <main id="content" class="p-8 transition-all duration-300 flex-1">
                <h2 class="text-3xl font-bold text-gray-800 mb-8">Financial Reporting</h2>
                
                <!-- Total Revenue Card -->
                <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 mb-8">
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">Total Revenue</h3>
                    <p class="text-3xl font-bold text-gray-800">₱12,540.50</p>
                    <div class="grid grid-cols-3 gap-4 mt-4">
                        <div>
                            <p class="text-xs text-gray-500">This Week</p>
                            <p class="text-lg font-semibold text-gray-800">₱2,890.00</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">This Month</p>
                            <p class="text-lg font-semibold text-gray-800">₱8,450.50</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Total Sales</p>
                            <p class="text-lg font-semibold text-gray-800">156</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 mb-8">
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">Reports & Exports</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <button onclick="downloadSalesReport()" class="text-left p-4 bg-green-50 hover:bg-green-100 rounded-lg text-green-700 font-medium transition duration-150 flex items-center">
                            <i class="fas fa-download mr-3 text-xl"></i>
                            <div>
                                <div class="font-semibold">Sales Report</div>
                                <div class="text-sm text-gray-600">Download complete sales history (CSV)</div>
                            </div>
                        </button>
                        <button onclick="downloadTaxReport()" class="text-left p-4 bg-green-50 hover:bg-green-100 rounded-lg text-green-700 font-medium transition duration-150 flex items-center">
                            <i class="fas fa-file-invoice mr-3 text-xl"></i>
                            <div>
                                <div class="font-semibold">Tax & Commission Report</div>
                                <div class="text-sm text-gray-600">Download tax calculations (CSV)</div>
                            </div>
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                    <h3 class="px-6 py-4 text-xl font-semibold text-gray-700 border-b">Transaction History</h3>
                    <div class="p-6 text-gray-500">
                        <ul class="space-y-3">
                            <li class="flex justify-between items-center border-b pb-2 hover:bg-gray-50 cursor-pointer rounded-lg p-2 transition" onclick="showTransactionDetails('8291', 'Sale', 'Apples, Bread', 21.50, '2024-12-10', 'completed')">
                                <span class="text-sm font-medium">Sale #8291 (Apples, Bread)</span>
                                <span class="text-sm text-green-600 font-semibold">₱21.50</span>
                            </li>
                            <li class="flex justify-between items-center border-b pb-2 hover:bg-gray-50 cursor-pointer rounded-lg p-2 transition" onclick="showTransactionDetails('8290', 'Sale', 'Potatoes', 12.00, '2024-12-09', 'completed')">
                                <span class="text-sm font-medium">Sale #8290 (Potatoes)</span>
                                <span class="text-sm text-green-600 font-semibold">₱12.00</span>
                            </li>
                            <li class="flex justify-between items-center border-b pb-2 hover:bg-gray-50 cursor-pointer rounded-lg p-2 transition" onclick="showTransactionDetails('8289', 'Sale', 'Tomatoes, Lettuce', 35.75, '2024-12-08', 'completed')">
                                <span class="text-sm font-medium">Sale #8289 (Tomatoes, Lettuce)</span>
                                <span class="text-sm text-green-600 font-semibold">₱35.75</span>
                            </li>
                            <li class="flex justify-between items-center border-b pb-2 hover:bg-gray-50 cursor-pointer rounded-lg p-2 transition" onclick="showTransactionDetails('8288', 'Sale', 'Carrots, Onions', 28.30, '2024-12-07', 'completed')">
                                <span class="text-sm font-medium">Sale #8288 (Carrots, Onions)</span>
                                <span class="text-sm text-green-600 font-semibold">₱28.30</span>
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
            ['Order ID', 'Customer', 'Products', 'Amount', 'Payment Method', 'Date', 'Status'],
            ['8291', 'John Doe', 'Apples, Bread', '₱21.50', 'COD', '2024-12-10', 'Completed'],
            ['8290', 'Jane Smith', 'Potatoes', '₱12.00', 'GCash', '2024-12-09', 'Completed'],
            ['8289', 'Bob Johnson', 'Tomatoes, Lettuce', '₱35.75', 'COD', '2024-12-08', 'Completed'],
            ['8288', 'Alice Brown', 'Carrots, Onions', '₱28.30', 'GCash', '2024-12-07', 'Completed'],
            ['8287', 'Charlie Wilson', 'Cabbage, Peppers', '₱45.60', 'COD', '2024-12-06', 'Completed']
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
            ['8287', 'Sale', '₱45.60', '₱4.56', '₱5.47', '₱35.57', '2024-12-06']
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
    
    // Profile dropdown hover handlers (matching user header style)
    let profileDropdownTimeout = null;
    let notificationPreviewTimeout = null;
    const HOVER_DELAY = 200;
    
    const profileContainer = document.getElementById('profileDropdownContainer');
    const profileDropdown = document.getElementById('profileDropdown');
    const profileBtn = document.getElementById('profileDropdownBtn');
    
    if (profileContainer && profileDropdown && profileBtn) {
        profileContainer.addEventListener('mouseenter', function() {
            clearTimeout(profileDropdownTimeout);
            profileDropdown.classList.remove('hidden');
        });
        
        profileContainer.addEventListener('mouseleave', function() {
            profileDropdownTimeout = setTimeout(function() {
                profileDropdown.classList.add('hidden');
            }, HOVER_DELAY);
        });
        
        profileDropdown.addEventListener('mouseenter', function() {
            clearTimeout(profileDropdownTimeout);
        });
        
        profileDropdown.addEventListener('mouseleave', function() {
            profileDropdownTimeout = setTimeout(function() {
                profileDropdown.classList.add('hidden');
            }, HOVER_DELAY);
        });
        
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('hidden');
        });
        
        document.addEventListener('click', function() {
            if (!profileDropdown.classList.contains('hidden')) {
                profileDropdown.classList.add('hidden');
            }
        });
    }

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

    const notificationContainer = document.getElementById('notificationPreviewContainer');
    const notificationPreview = document.getElementById('notificationPreview');
    if (notificationContainer && notificationPreview) {
        notificationContainer.addEventListener('mouseenter', function() {
            clearTimeout(notificationPreviewTimeout);
            loadRetailerNotificationPreview();
            notificationPreview.classList.remove('hidden');
        });
        notificationContainer.addEventListener('mouseleave', function() {
            notificationPreviewTimeout = setTimeout(function() {
                notificationPreview.classList.add('hidden');
            }, HOVER_DELAY);
        });
        notificationPreview.addEventListener('mouseenter', function() {
            clearTimeout(notificationPreviewTimeout);
        });
        notificationPreview.addEventListener('mouseleave', function() {
            notificationPreviewTimeout = setTimeout(function() {
                notificationPreview.classList.add('hidden');
            }, HOVER_DELAY);
        });
    }

    function loadRetailerNotificationBadge() {
        const badge = document.getElementById('notificationBadge');
        if (!badge) return;
        fetch('../api/get-retailer-notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.notifications) {
                    const unreadCount = data.unreadCount || 0;
                    if (unreadCount > 0) {
                        badge.textContent = unreadCount;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                }
            })
            .catch(error => console.error('Error loading notification badge:', error));
    }
    
    function loadRetailerNotificationPreview() {
        const notificationPreviewItems = document.getElementById('notificationPreviewItems');
        if (!notificationPreviewItems) return;
        fetch('../api/get-retailer-notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.notifications && data.notifications.length > 0) {
                    notificationPreviewItems.innerHTML = data.notifications.slice(0, 5).map(notif => {
                        const isUnread = !notif.read;
                        const unreadClass = isUnread ? 'bg-green-50 border-l-4 border-green-500' : '';
                        const timeAgo = getTimeAgo(new Date(notif.timestamp));
                        let iconClass = 'fa-info-circle', iconBgClass = 'bg-blue-100', iconTextClass = 'text-blue-700';
                        if (notif.type === 'order') { iconClass = 'fa-box'; iconBgClass = 'bg-green-100'; iconTextClass = 'text-green-700'; }
                        else if (notif.type === 'stock') { iconClass = 'fa-exclamation-triangle'; iconBgClass = 'bg-yellow-100'; iconTextClass = 'text-yellow-700'; }
                        else if (notif.type === 'review') { iconClass = 'fa-star'; iconBgClass = 'bg-yellow-100'; iconTextClass = 'text-yellow-700'; }
                        const title = escapeHtml(notif.title || 'Notification');
                        const message = escapeHtml(notif.message || '');
                        const link = notif.link || 'retailernotifications.php';
                        return `<a href="${link}" class="block p-3 border-b border-gray-100 hover:bg-gray-50 transition ${unreadClass}" data-notification-id="${notif.id}" onclick="markNotificationAsRead(event, ${notif.id})"><div class="flex items-start gap-3"><div class="${iconBgClass} ${iconTextClass} p-2 rounded-full flex-shrink-0"><i class="fas ${iconClass} text-sm"></i></div><div class="flex-1 min-w-0"><p class="font-medium text-gray-800 text-sm truncate">${title}</p><p class="text-xs text-gray-500 mt-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${message}</p><span class="text-xs text-gray-400 block mt-1">${timeAgo}</span></div>${isUnread ? '<div class="w-2 h-2 bg-green-500 rounded-full flex-shrink-0 mt-2"></div>' : ''}</div></a>`;
                    }).join('');
                } else {
                    notificationPreviewItems.innerHTML = `<div class="p-8 text-center text-gray-500"><i class="fas fa-bell text-4xl mb-2 text-gray-300"></i><p class="text-sm">No notifications</p></div>`;
                }
            })
            .catch(error => console.error('Error:', error));
    }
    
    function getTimeAgo(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        if (seconds < 60) return 'Just now';
        if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
        if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
        if (seconds < 604800) return `${Math.floor(seconds / 86400)}d ago`;
        return date.toLocaleDateString();
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function markNotificationAsRead(event, notificationId) {
        fetch('../api/mark-retailer-notification-read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                notification_id: notificationId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload badge count after marking as read
                setTimeout(() => {
                    loadRetailerNotificationBadge();
                }, 100);
            }
        })
        .catch(error => console.error('Error marking notification as read:', error));
    }
    
    loadRetailerNotificationBadge();
    setInterval(loadRetailerNotificationBadge, 5000);

    // Listen for notification updates from other pages (e.g., retailernotifications.php)
    window.addEventListener('storage', (e) => {
        if (e.key === 'notificationsUpdated') {
            loadRetailerNotificationBadge();
            loadRetailerNotificationPreview();
        }
    });

    // Listen for custom event from same page
    window.addEventListener('notificationsUpdated', () => {
        loadRetailerNotificationBadge();
        loadRetailerNotificationPreview();
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
