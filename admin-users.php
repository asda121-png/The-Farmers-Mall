<?php
// =============================================================================
// WARNING: Database connection logic is highly sensitive.
// Using 'root' with no password, as requested, is extremely insecure and is 
// only suitable for local development/testing. For production, use a dedicated 
// user with strong credentials and proper security measures.
// =============================================================================

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "farmers";

// Attempt to connect to MySQL database
$conn = new mysqli($servername, $username, $password, $dbname);

$users = [];
$totalUsers = 0;
$activeCustomers = 0;
$retailers = 0;
$newUsersThisMonth = 0;
$db_error = null;

if ($conn->connect_error) {
    // Store error message to display in the HTML
    $db_error = "Connection failed: " . $conn->connect_error;
} else {
    // Assuming the 'users' table has columns: id, name, email, role, status, joined_date
    $sql = "SELECT id, name, email, role, status, joined_date FROM users ORDER BY joined_date DESC";
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        
        $totalUsers = count($users);
        $currentMonth = date('m');
        $currentYear = date('Y');

        foreach ($users as $user) {
            // Calculate statistics
            if ($user['role'] === 'Customer' && $user['status'] === 'Active') {
                $activeCustomers++;
            }
            if ($user['role'] === 'Retailer') {
                $retailers++;
            }

            // Check for new users this month (assuming joined_date is in YYYY-MM-DD format)
            $joinedMonth = date('m', strtotime($user['joined_date']));
            $joinedYear = date('Y', strtotime($user['joined_date']));

            if ($joinedMonth == $currentMonth && $joinedYear == $currentYear) {
                $newUsersThisMonth++;
            }
        }
    } else {
        $db_error = "Query failed: " . $conn->error;
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Users – The Farmer's Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
          },
        }
      }
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* Custom style for the Active/Inactive badges */
    .badge-active {
      background-color: #d1fae5;
      color: #059669;
    }

    .badge-inactive {
      background-color: #fee2e2;
      color: #ef4444;
    }
    /* Ensure action modal is correctly positioned and visible */
    #actionModal, #logoutModal {
        z-index: 1000; /* High z-index to overlay all content */
    }
  </style>
</head>

<body class="bg-gray-100 text-gray-800 flex font-sans">

  <!-- Sidebar -->
  <aside class="bg-green-800 text-white w-64 min-h-screen p-4 flex flex-col justify-between">
    <div>
      <div class="text-center mb-10">
        <h1 class="text-2xl font-bold">The Farmer's Mall</h1>
        <p class="text-sm text-green-200">Admin Panel</p>
      </div>
      <nav class="space-y-2">
        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
          <i class="fa-solid fa-tachometer-alt w-5"></i>
          <span>Dashboard</span>
        </a>
        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg bg-green-700 shadow-md">
          <i class="fa-solid fa-users w-5"></i>
          <span>Users</span>
        </a>
        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
          <i class="fa-solid fa-store w-5"></i>
          <span>Retailers</span>
        </a>
        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
          <i class="fa-solid fa-box w-5"></i>
          <span>Products</span>
        </a>
        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
          <i class="fa-solid fa-receipt w-5"></i>
          <span>Orders</span>
        </a>
        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
          <i class="fa-solid fa-cog w-5"></i>
          <span>Settings</span>
        </a>
      </nav>
    </div>
    <div>
      <button id="logoutButton" class="w-full flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
        <i class="fa-solid fa-sign-out-alt w-5"></i>
        <span>Logout</span>
      </button>
    </div>
  </aside>

  <!-- Main Content -->
  <div class="flex-1">
    <!-- Header -->
    <header class="bg-white shadow-md p-4 flex justify-between items-center sticky top-0 z-10">
      <h2 class="text-2xl font-bold text-green-800">Manage Users</h2>
      <div class="flex items-center gap-4">
        <a href="#" class="p-2 rounded-full hover:bg-gray-100 transition-colors">
          <i class="fa-regular fa-bell text-xl text-gray-600 cursor-pointer"></i>
        </a>
        <div class="flex items-center gap-3 cursor-pointer p-2 rounded-lg hover:bg-gray-100 transition-colors">
          <img src="https://placehold.co/40x40/4c7c50/ffffff?text=AD" class="w-10 h-10 rounded-full border-2 border-green-500" alt="Admin">
          <div>
            <p class="text-sm font-medium">Admin User</p>
            <p class="text-xs text-gray-500">admin@farmersmall.com</p>
          </div>
        </div>
      </div>
    </header>

    <!-- Content -->
    <main class="p-6 space-y-8">
      <!-- User Statistics -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-5 rounded-xl shadow-lg flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Total Users</p>
            <p id="statTotalUsers" class="text-3xl font-bold text-green-800"><?php echo $totalUsers; ?></p>
          </div>
          <i class="fa-solid fa-users text-4xl text-green-200"></i>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-lg flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Active Customers</p>
            <p id="statActiveCustomers" class="text-3xl font-bold text-green-600"><?php echo $activeCustomers; ?></p>
          </div>
          <i class="fa-solid fa-user-check text-4xl text-green-200"></i>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-lg flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Retailers</p>
            <p id="statRetailers" class="text-3xl font-bold text-yellow-600"><?php echo $retailers; ?></p>
          </div>
          <i class="fa-solid fa-store text-4xl text-green-200"></i>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-lg flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">New This Month</p>
            <p id="statNewUsers" class="text-3xl font-bold text-blue-600"><?php echo $newUsersThisMonth; ?></p>
          </div>
          <i class="fa-solid fa-user-plus text-4xl text-green-200"></i>
        </div>
      </div>

      <!-- User Table -->
      <div class="bg-white rounded-xl shadow-lg overflow-x-auto">
        <div id="loadingIndicator" class="p-8 text-center text-gray-500 hidden">
            <!-- This is hidden as PHP loads immediately. Only useful for JS fetching. -->
        </div>
        
        <?php if ($db_error): ?>
            <div class="p-8 text-center text-red-600 bg-red-50 border-t-4 border-red-500 rounded-b shadow-md">
                <p class="font-bold">Database Error:</p>
                <p><?php echo $db_error; ?></p>
            </div>
        <?php elseif (empty($users)): ?>
            <div class="p-8 text-center text-gray-500">
                <i class="fa-solid fa-info-circle text-2xl mb-2"></i>
                <p>No users found in the 'users' table.</p>
            </div>
        <?php else: ?>
            <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left border-b border-gray-200">
                <tr>
                <th class="p-4 font-semibold text-gray-600">User</th>
                <th class="p-4 font-semibold text-gray-600">Email</th>
                <th class="p-4 font-semibold text-gray-600">Role</th>
                <th class="p-4 font-semibold text-gray-600">Status</th>
                <th class="p-4 font-semibold text-gray-600">Joined Date</th>
                <th class="p-4 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody id="usersTableBody" class="divide-y divide-gray-100">
                <?php foreach ($users as $user): 
                    $statusClass = $user['status'] === 'Active' ? 'badge-active' : 'badge-inactive';
                    $roleColor = $user['role'] === 'Retailer' ? 'text-yellow-600' : ($user['role'] === 'Admin' ? 'text-red-600' : 'text-green-600');
                    $initial = strtoupper(substr($user['name'], 0, 1));
                    $placeholderColor = dechex(mt_rand(0, 0xFFFFFF)); // Generate a random color for the placeholder
                ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="p-4 flex items-center gap-3">
                        <img src="https://placehold.co/32x32/<?php echo $placeholderColor; ?>/ffffff?text=<?php echo $initial; ?>" class="w-8 h-8 rounded-full" alt="<?php echo htmlspecialchars($user['name']); ?>">
                        <span class="font-medium"><?php echo htmlspecialchars($user['name']); ?></span>
                    </td>
                    <td class="p-4 text-gray-600"><?php echo htmlspecialchars($user['email']); ?></td>
                    <td class="p-4">
                        <span class="font-semibold <?php echo $roleColor; ?>"><?php echo htmlspecialchars($user['role']); ?></span>
                    </td>
                    <td class="p-4">
                        <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full <?php echo $statusClass; ?>"><?php echo htmlspecialchars($user['status']); ?></span>
                    </td>
                    <td class="p-4 text-gray-500"><?php echo htmlspecialchars($user['joined_date']); ?></td>
                    <td class="p-4">
                        <div class="flex gap-2">
                            <button data-action="view" data-id="<?php echo $user['id']; ?>" data-name="<?php echo htmlspecialchars($user['name']); ?>" class="action-btn text-blue-600 hover:text-blue-800 p-1 rounded-full hover:bg-blue-100 transition-colors" title="View Details">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button data-action="edit" data-id="<?php echo $user['id']; ?>" data-name="<?php echo htmlspecialchars($user['name']); ?>" class="action-btn text-yellow-600 hover:text-yellow-800 p-1 rounded-full hover:bg-yellow-100 transition-colors" title="Edit User">
                                <i class="fa-solid fa-edit"></i>
                            </button>
                            <button data-action="delete" data-id="<?php echo $user['id']; ?>" data-name="<?php echo htmlspecialchars($user['name']); ?>" class="action-btn text-red-600 hover:text-red-800 p-1 rounded-full hover:bg-red-100 transition-colors" title="Delete User">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            </table>
        <?php endif; ?>
      </div>

      <!-- User Action Modal -->
      <div id="actionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 transition-opacity duration-300 opacity-0">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md transform scale-95 transition-transform duration-300">
          <h3 id="modalTitle" class="font-bold text-xl mb-4 text-green-800">User Action</h3>
          <p id="modalMessage" class="text-gray-600 mb-6"></p>
          <div class="flex justify-end gap-3">
            <button id="cancelAction" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">Cancel</button>
            <button id="confirmAction" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">Confirm</button>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Logout Confirmation Modal -->
  <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 transition-opacity duration-300 opacity-0">
    <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-sm text-center transform scale-95 transition-transform duration-300">
      <div class="text-red-500 text-5xl mb-4"><i class="fa-solid fa-triangle-exclamation"></i></div>
      <h3 class="font-bold text-2xl mb-2 text-gray-800">Confirm Logout</h3>
      <p class="text-gray-600 text-sm mb-8">Are you sure you want to log out of the admin panel?</p>
      <div class="flex justify-center gap-4">
        <button id="cancelLogout" class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors shadow-sm">Cancel</button>
        <a href="login.html" id="confirmLogout" class="px-6 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors shadow-md">Logout</a>
      </div>
    </div>
  </div>

  <script>
    // --- MODAL LOGIC (Logout and Action) ---

    // Generic Modal Handler
    function setupModal(triggerId, modalId, cancelId) {
      const trigger = document.getElementById(triggerId);
      const modal = document.getElementById(modalId);
      const cancel = document.getElementById(cancelId);

      // Show modal
      trigger.addEventListener('click', () => {
        modal.classList.remove('hidden');
        // Add a slight delay for transition effect
        setTimeout(() => {
          modal.classList.add('opacity-100');
          modal.querySelector(':first-child').classList.remove('scale-95');
        }, 10);
      });

      // Hide modal
      const hideModal = () => {
        modal.classList.remove('opacity-100');
        modal.querySelector(':first-child').classList.add('scale-95');
        setTimeout(() => {
          modal.classList.add('hidden');
        }, 300); // Match CSS transition duration
      };

      cancel.addEventListener('click', hideModal);

      // Close if clicking outside the modal content
      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          hideModal();
        }
      });

      return hideModal;
    }

    // Setup Logout Modal
    setupModal('logoutButton', 'logoutModal', 'cancelLogout');

    // Setup User Action Modal (for Edit/Delete/View)
    // Note: The action trigger is handled by the event listener below.
    const hideActionModal = (() => {
      const modal = document.getElementById('actionModal');
      const cancel = document.getElementById('cancelAction');
      
      const hide = () => {
        modal.classList.remove('opacity-100');
        modal.querySelector(':first-child').classList.add('scale-95');
        setTimeout(() => {
          modal.classList.add('hidden');
        }, 300);
      };

      cancel.addEventListener('click', hide);
      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          hide();
        }
      });
      return hide;
    })();


    // Handle Action Buttons (View, Edit, Delete)
    document.getElementById('usersTableBody').addEventListener('click', (e) => {
      const button = e.target.closest('.action-btn');
      if (!button) return;

      const action = button.dataset.action;
      const userId = button.dataset.id;
      const userName = button.dataset.name;
      
      const modal = document.getElementById('actionModal');
      const modalTitle = document.getElementById('modalTitle');
      const modalMessage = document.getElementById('modalMessage');
      const confirmButton = document.getElementById('confirmAction');

      // Show modal (using the same transition logic as in the PHP version)
      modal.classList.remove('hidden');
      setTimeout(() => {
          modal.classList.add('opacity-100');
          modal.querySelector(':first-child').classList.remove('scale-95');
      }, 10);

      switch (action) {
        case 'view':
          modalTitle.textContent = `View User: ${userName}`;
          modalMessage.innerHTML = `<p>Displaying full details for User ID <strong>${userId}</strong>.</p><p class="text-xs mt-2">In a real PHP application, this action would submit a form or fetch data from a server endpoint.</p>`;
          confirmButton.textContent = 'Close';
          confirmButton.classList.remove('bg-red-600', 'hover:bg-red-700');
          confirmButton.classList.add('bg-green-600', 'hover:bg-green-700');
          confirmButton.onclick = hideActionModal;
          break;
        case 'edit':
          modalTitle.textContent = `Edit User: ${userName}`;
          modalMessage.innerHTML = `<p>Preparing to edit details for User ID <strong>${userId}</strong>.</p><p class="text-xs mt-2">This would typically redirect to an edit form or display one here.</p>`;
          confirmButton.textContent = 'Go to Edit Page';
          confirmButton.classList.remove('bg-red-600', 'hover:bg-red-700');
          confirmButton.classList.add('bg-green-600', 'hover:bg-green-700');
          confirmButton.onclick = () => {
             // In a real PHP app, this would be a redirect: window.location.href = `edit-user.php?id=${userId}`;
             console.log(`Redirecting to edit page for user ${userId}`);
             hideActionModal();
          };
          break;
        case 'delete':
          modalTitle.textContent = `Delete User: ${userName}`;
          modalMessage.innerHTML = `<p class="font-bold text-red-600">WARNING: Are you sure you want to permanently delete User ID <strong>${userId}</strong>?</p><p class="text-sm mt-2">This action should trigger a PHP script to delete the record via POST request.</p>`;
          confirmButton.textContent = 'Delete Permanently';
          confirmButton.classList.remove('bg-green-600', 'hover:bg-green-700');
          confirmButton.classList.add('bg-red-600', 'hover:bg-red-700');
          confirmButton.onclick = () => {
             // **REAL IMPLEMENTATION NOTE:** Send a fetch/AJAX request to a delete.php endpoint 
             console.log(`Deleting user ${userId}...`);
             // You would add fetch logic here: 
             /*
             fetch('delete_user.php', {
                 method: 'POST',
                 headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                 body: `user_id=${userId}`
             }).then(() => location.reload()); // Reload page to see changes
             */
             hideActionModal();
          };
          break;
      }
    });

  </script>
</body>
</html>