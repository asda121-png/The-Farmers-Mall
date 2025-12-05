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
    <title>Earnings & Stats - Rider Dashboard</title>
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

<?php include 'riderheader.php'; ?>

<!-- Main Content -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex-grow w-full">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Earnings & Statistics
            </h2>
            <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                <div class="mt-2 flex items-center text-sm text-gray-500">
                    <i class="far fa-calendar-alt mr-1.5 h-4 w-4 text-gray-400"></i>
                    Last updated: <?= date('F j, Y') ?>
                </div>
                <div class="mt-2 flex items-center text-sm text-gray-500">
                    <i class="fas fa-sync-alt mr-1.5 h-4 w-4 text-gray-400"></i>
                    Updates every 24 hours
                </div>
            </div>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <div class="relative">
                <select id="timeRange" class="appearance-none bg-white border border-gray-300 rounded-md pl-3 pr-8 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <option>Last 7 days</option>
                    <option>Last 30 days</option>
                    <option>This Month</option>
                    <option>Last Month</option>
                    <option>This Year</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                    <i class="fas fa-chevron-down text-xs"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <!-- Total Earnings -->
        <div class="stat-card bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                        <i class="fas fa-peso-sign text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Earnings</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900">₱24,567.00</div>
                                <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                                    <i class="fas fa-arrow-up text-xs"></i>
                                    <span class="sr-only">Increased by</span>
                                    12%
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="#" class="font-medium text-green-600 hover:text-green-700">View all transactions</a>
                </div>
            </div>
        </div>

        <!-- Completed Deliveries -->
        <div class="stat-card bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                        <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Completed Deliveries</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900">1,286</div>
                                <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                                    <i class="fas fa-arrow-up text-xs"></i>
                                    <span class="sr-only">Increased by</span>
                                    8.6%
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="riderorders.php?status=delivered" class="font-medium text-blue-600 hover:text-blue-700">View completed orders</a>
                </div>
            </div>
        </div>

        <!-- Average Rating -->
        <div class="stat-card bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                        <i class="fas fa-star text-yellow-500 text-xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Average Rating</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900">4.8</div>
                                <div class="ml-2 flex items-baseline text-sm font-semibold text-yellow-600">
                                    <i class="fas fa-star text-xs"></i>
                                    <span class="ml-1">(1,024 ratings)</span>
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="#" class="font-medium text-yellow-600 hover:text-yellow-700">View feedback</a>
                </div>
            </div>
        </div>

        <!-- Active Hours -->
        <div class="stat-card bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                        <i class="fas fa-clock text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Hours</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900">42.5</div>
                                <span class="ml-2 text-sm text-gray-500">hours this week</span>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="#" class="font-medium text-purple-600 hover:text-purple-700">View schedule</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Earnings Chart -->
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Earnings Overview</h3>
                <div class="relative">
                    <select class="appearance-none bg-white border border-gray-300 rounded-md pl-3 pr-8 py-1 text-left text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <option>Weekly</option>
                        <option>Monthly</option>
                        <option>Yearly</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>
            <div class="h-64">
                <canvas id="earningsChart"></canvas>
            </div>
        </div>

        <!-- Deliveries Chart -->
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Deliveries Completed</h3>
                <div class="relative">
                    <select class="appearance-none bg-white border border-gray-300 rounded-md pl-3 pr-8 py-1 text-left text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <option>Weekly</option>
                        <option>Monthly</option>
                        <option>Yearly</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>
            <div class="h-64">
                <canvas id="deliveriesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Transactions</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Your most recent earnings and deductions</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Transaction 1 -->
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Nov 30, 2023</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#ORD-001234</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Delivery Fee</td>
                        <td class="px-6 py-4 text-sm text-gray-500">Completed delivery to Juan Dela Cruz</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-green-600">+₱120.00</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Completed
                            </span>
                        </td>
                    </tr>
                    <!-- Transaction 2 -->
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Nov 29, 2023</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#ORD-001233</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Delivery Fee</td>
                        <td class="px-6 py-4 text-sm text-gray-500">Completed delivery to Maria Santos</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-green-600">+₱150.00</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Completed
                            </span>
                        </td>
                    </tr>
                    <!-- Transaction 3 -->
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Nov 28, 2023</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#ORD-001232</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Bonus</td>
                        <td class="px-6 py-4 text-sm text-gray-500">On-time delivery bonus</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-green-600">+₱50.00</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Completed
                            </span>
                        </td>
                    </tr>
                    <!-- Transaction 4 -->
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Nov 27, 2023</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#ORD-001231</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Delivery Fee</td>
                        <td class="px-6 py-4 text-sm text-gray-500">Completed delivery to Pedro Cruz</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-green-600">+₱130.00</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Completed
                            </span>
                        </td>
                    </tr>
                    <!-- Transaction 5 -->
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Nov 27, 2023</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#FEE-00012</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Service Fee</td>
                        <td class="px-6 py-4 text-sm text-gray-500">Platform service fee</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-red-600">-₱15.00</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                Processed
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
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
                        Showing <span class="font-medium">1</span> to <span class="font-medium">5</span> of <span class="font-medium">24</span> results
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
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                            ...
                        </span>
                        <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            5
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
</main>

<!-- Footer -->
<footer class="bg-white border-t border-gray-200 mt-12">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <p class="text-center text-sm text-gray-500">
            &copy; <?= date('Y') ?> The Farmers Mall. All rights reserved.
        </p>
    </div>
</footer>

<!-- JavaScript -->
<script>
// Initialize charts when the document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Earnings Chart
    const earningsCtx = document.getElementById('earningsChart').getContext('2d');
    const earningsChart = new Chart(earningsCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Earnings (₱)',
                data: [1200, 1900, 1500, 2500, 2200, 3000, 2800],
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderColor: 'rgba(16, 185, 129, 1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true,
                pointBackgroundColor: 'white',
                pointBorderColor: 'rgba(16, 185, 129, 1)',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'white',
                    titleColor: '#1F2937',
                    bodyColor: '#4B5563',
                    borderColor: '#E5E7EB',
                    borderWidth: 1,
                    padding: 12,
                    boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
                    callbacks: {
                        label: function(context) {
                            return '₱' + context.raw.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Deliveries Chart
    const deliveriesCtx = document.getElementById('deliveriesChart').getContext('2d');
    const deliveriesChart = new Chart(deliveriesCtx, {
        type: 'bar',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Deliveries',
                data: [8, 12, 10, 15, 14, 20, 18],
                backgroundColor: 'rgba(99, 102, 241, 0.8)',
                borderColor: 'rgba(99, 102, 241, 1)',
                borderWidth: 0,
                borderRadius: 4,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        stepSize: 5
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Time range selector
    const timeRangeSelect = document.getElementById('timeRange');
    if (timeRangeSelect) {
        timeRangeSelect.addEventListener('change', function() {
            // In a real app, this would fetch new data based on the selected time range
            console.log('Time range changed to:', this.value);
            // Example: fetchNewData(this.value);
        });
    }
});
</script>

</body>
</html>
