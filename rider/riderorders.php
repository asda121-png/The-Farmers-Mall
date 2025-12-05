<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if rider is logged in (temporarily disabled for testing)
/*
if (!isset($_SESSION['rider_id'])) {
    header('Location: riderlogin.php');
    exit();
}
*/

// Set mock rider data for testing
$_SESSION['rider_id'] = 1;
$_SESSION['rider_name'] = 'John Rider';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Rider Dashboard</title>
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Custom styles can be added here */
        .status-pending { background-color: #FEF3C7; color: #92400E; }
        .status-in-progress { background-color: #DBEAFE; color: #1E40AF; }
        .status-delivered { background-color: #D1FAE5; color: #065F46; }
        .status-cancelled { background-color: #FEE2E2; color: #991B1B; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

<?php include 'riderheader.php'; ?>

<!-- Main Content -->
<main class="max-w-7xl mx-auto px-6 py-6 flex-grow w-full mb-20">
    <!-- Order Management Section -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800">Order Management</h2>
            <a href="riderdashboard.php" class="text-sm text-green-600 hover:text-green-700 font-medium">
                <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
            </a>
        </div>
        
        <!-- Search and Filter Bar -->
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-3 sm:space-y-0">
            <div class="relative w-full sm:w-64">
                <input type="text" id="searchOrders" placeholder="Search orders..." 
                       class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <div class="absolute left-3 top-2.5 text-gray-400">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            <div class="flex space-x-2 w-full sm:w-auto">
                <select id="filterStatus" class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="assigned">Assigned</option>
                    <option value="in_transit">In Transit</option>
                    <option value="delivered">Delivered</option>
                </select>
                <button id="refreshOrders" class="bg-white text-gray-600 hover:bg-gray-50 border border-gray-300 rounded-lg px-4 py-2 text-sm flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i> Refresh
                </button>
            </div>
        </div>
        
        <!-- Orders Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody" class="bg-white divide-y divide-gray-200">
                    <!-- Orders will be loaded here dynamically -->
                </tbody>
            </table>
        </div>
        
        <!-- No Orders State -->
        <div id="noOrders" class="p-12 text-center text-gray-500 hidden">
            <i class="fas fa-box-open text-4xl mb-3 text-gray-300"></i>
            <p class="text-gray-500">No orders available at the moment</p>
            <button id="refreshOrdersBtn" class="mt-3 text-green-600 hover:text-green-700 text-sm font-medium">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </button>
        </div>
        
        <!-- Pagination -->
        <div class="bg-white px-6 py-3 flex items-center justify-between border-t border-gray-200">
            <div class="flex-1 flex justify-between sm:hidden">
                <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Previous
                </a>
                <a href="#" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Next
                </a>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">1</span> to <span class="font-medium">5</span> of <span class="font-medium">12</span> results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Previous</span>
                            <i class="fas fa-chevron-left h-5 w-5"></i>
                        </a>
                        <a href="#" aria-current="page" class="z-10 bg-green-50 border-green-500 text-green-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            1
                        </a>
                        <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            2
                        </a>
                        <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            3
                        </a>
                        <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Next</span>
                            <i class="fas fa-chevron-right h-5 w-5"></i>
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center border-b pb-4 mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Order #<span id="modalOrderId">12345</span> Details</h3>
                    <button id="closeModal" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Order Summary -->
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Order Summary</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Order Status:</span>
                                <span id="modalOrderStatus" class="font-medium">Pending</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Order Date:</span>
                                <span id="modalOrderDate">Nov 15, 2023 14:30</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Payment Method:</span>
                                <span id="modalPaymentMethod">Cash on Delivery</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Delivery Fee:</span>
                                <span id="modalDeliveryFee">₱50.00</span>
                            </div>
                            <div class="flex justify-between font-medium pt-2 border-t mt-2">
                                <span>Total Amount:</span>
                                <span id="modalTotalAmount">₱1,250.00</span>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <h4 class="font-medium text-gray-900 mb-3">Delivery Address</h4>
                            <p id="modalDeliveryAddress" class="text-sm text-gray-600">123 Main Street, Barangay 123, Quezon City, Metro Manila</p>
                            <div class="mt-3">
                                <button id="viewOnMapBtn" class="text-green-600 hover:text-green-700 text-sm font-medium flex items-center">
                                    <i class="fas fa-map-marker-alt mr-1"></i> View on Map
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Items -->
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Order Items</h4>
                        <div id="orderItemsList" class="space-y-3">
                            <!-- Order items will be added here dynamically -->
                        </div>
                        
                        <!-- Delivery Notes -->
                        <div class="mt-6">
                            <h5 class="text-sm font-medium text-gray-900 mb-2">Delivery Notes</h5>
                            <p id="deliveryNotes" class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg">Please call before delivery. The gate might be closed.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="mt-8 pt-4 border-t flex justify-end space-x-3">
                    <button id="rejectOrderBtn" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Reject
                    </button>
                    <button id="acceptOrderBtn" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Accept Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sample order data - in a real app, this would come from an API
    const orders = [
        {
            id: 'ORD-001234',
            customer: 'Juan Dela Cruz',
            location: 'Quezon City',
            amount: '₱1,250.00',
            status: 'pending',
            statusText: 'Pending',
            date: 'Nov 15, 2023 14:30',
            paymentMethod: 'Cash on Delivery',
            deliveryFee: '₱50.00',
            totalAmount: '₱1,250.00',
            address: '123 Main Street, Barangay 123, Quezon City, Metro Manila',
            notes: 'Please call before delivery. The gate might be closed.',
            items: [
                { name: 'Fresh Tomatoes', quantity: 2, price: '₱120.00', subtotal: '₱240.00' },
                { name: 'Organic Brown Rice (5kg)', quantity: 1, price: '₱350.00', subtotal: '₱350.00' },
                { name: 'Fresh Eggs (1 dozen)', quantity: 2, price: '₱150.00', subtotal: '₱300.00' },
                { name: 'Banana Bunch', quantity: 1, price: '₱60.00', subtotal: '₱60.00' },
                { name: 'Delivery Fee', quantity: 1, price: '₱50.00', subtotal: '₱50.00' }
            ]
        },
        // Add more sample orders as needed
    ];
    
    // DOM Elements
    const ordersTableBody = document.getElementById('ordersTableBody');
    const searchInput = document.getElementById('searchOrders');
    const filterStatus = document.getElementById('filterStatus');
    const refreshButtons = document.querySelectorAll('#refreshOrders, #refreshOrdersBtn');
    const noOrdersDiv = document.getElementById('noOrders');
    const orderDetailsModal = document.getElementById('orderDetailsModal');
    const closeModalBtn = document.getElementById('closeModal');
    const viewOnMapBtn = document.getElementById('viewOnMapBtn');
    const rejectOrderBtn = document.getElementById('rejectOrderBtn');
    const acceptOrderBtn = document.getElementById('acceptOrderBtn');
    
    // Render orders table
    function renderOrders(ordersToRender) {
        if (ordersToRender.length === 0) {
            ordersTableBody.innerHTML = '';
            noOrdersDiv.classList.remove('hidden');
            return;
        }
        
        noOrdersDiv.classList.add('hidden');
        
        const rows = ordersToRender.map(order => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${order.id}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${order.customer}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${order.location}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${order.amount}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusBadgeClass(order.status)}">
                        ${order.statusText}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button onclick="viewOrderDetails('${order.id}')" class="text-green-600 hover:text-green-900 mr-3">View</button>
                    <button onclick="acceptOrder('${order.id}')" class="text-blue-600 hover:text-blue-900">Accept</button>
                </td>
            </tr>
        `).join('');
        
        ordersTableBody.innerHTML = rows;
    }
    
    // Get status badge class based on status
    function getStatusBadgeClass(status) {
        const statusClasses = {
            'pending': 'bg-yellow-100 text-yellow-800',
            'assigned': 'bg-blue-100 text-blue-800',
            'in_transit': 'bg-indigo-100 text-indigo-800',
            'delivered': 'bg-green-100 text-green-800',
            'cancelled': 'bg-red-100 text-red-800'
        };
        return statusClasses[status] || 'bg-gray-100 text-gray-800';
    }
    
    // Filter and search orders
    function filterAndSearchOrders() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusFilter = filterStatus.value;
        
        const filtered = orders.filter(order => {
            const matchesSearch = 
                order.id.toLowerCase().includes(searchTerm) ||
                order.customer.toLowerCase().includes(searchTerm) ||
                order.location.toLowerCase().includes(searchTerm);
                
            const matchesStatus = statusFilter === 'all' || order.status === statusFilter;
            
            return matchesSearch && matchesStatus;
        });
        
        renderOrders(filtered);
    }
    
    // View order details
    window.viewOrderDetails = function(orderId) {
        const order = orders.find(o => o.id === orderId);
        if (!order) return;
        
        // Update modal content
        document.getElementById('modalOrderId').textContent = order.id;
        document.getElementById('modalOrderStatus').textContent = order.statusText;
        document.getElementById('modalOrderDate').textContent = order.date;
        document.getElementById('modalPaymentMethod').textContent = order.paymentMethod;
        document.getElementById('modalDeliveryFee').textContent = order.deliveryFee;
        document.getElementById('modalTotalAmount').textContent = order.totalAmount;
        document.getElementById('modalDeliveryAddress').textContent = order.address;
        document.getElementById('deliveryNotes').textContent = order.notes;
        
        // Update order items
        const orderItemsList = document.getElementById('orderItemsList');
        orderItemsList.innerHTML = order.items.map(item => `
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <div>
                    <p class="text-sm font-medium text-gray-900">${item.name}</p>
                    <p class="text-xs text-gray-500">${item.quantity} x ${item.price}</p>
                </div>
                <span class="text-sm font-medium text-gray-900">${item.subtotal}</span>
            </div>
        `).join('');
        
        // Show modal
        orderDetailsModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };
    
    // Accept order
    window.acceptOrder = function(orderId) {
        if (confirm('Are you sure you want to accept this order?')) {
            // In a real app, this would be an API call
            console.log(`Order ${orderId} accepted`);
            // Update UI or show success message
            alert('Order accepted successfully!');
        }
    };
    
    // Close modal
    closeModalBtn.addEventListener('click', () => {
        orderDetailsModal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    });
    
    // View on map
    viewOnMapBtn.addEventListener('click', () => {
        // In a real app, this would open a map with the delivery location
        window.location.href = 'ridermap.php';
    });
    
    // Reject order
    rejectOrderBtn.addEventListener('click', () => {
        if (confirm('Are you sure you want to reject this order?')) {
            // In a real app, this would be an API call
            console.log('Order rejected');
            // Close modal and update UI
            orderDetailsModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            alert('Order has been rejected.');
        }
    });
    
    // Accept order from modal
    acceptOrderBtn.addEventListener('click', () => {
        const orderId = document.getElementById('modalOrderId').textContent;
        window.acceptOrder(orderId);
    });
    
    // Event listeners
    searchInput.addEventListener('input', filterAndSearchOrders);
    filterStatus.addEventListener('change', filterAndSearchOrders);
    refreshButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            // In a real app, this would refresh data from the server
            filterAndSearchOrders();
        });
    });
    
    // Close modal when clicking outside
    orderDetailsModal.addEventListener('click', (e) => {
        if (e.target === orderDetailsModal) {
            orderDetailsModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    });
    
    // Initial render
    renderOrders(orders);
});
</script>

</body>
</html>
