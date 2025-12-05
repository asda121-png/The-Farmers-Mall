<?php
session_start();

// This page helps debug cart API issues
// Access it at: localhost:3000/test-cart-api.php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart API Debugger</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Cart API Debugger</h1>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Session Info</h2>
            <div class="bg-gray-50 p-4 rounded">
                <pre><?php print_r($_SESSION); ?></pre>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Test Cart API</h2>
            <button onclick="testCartAPI()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Fetch Cart Items
            </button>
            <button onclick="testAddToCart()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 ml-2">
                Add Test Item
            </button>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">API Response</h2>
            <div id="apiResponse" class="bg-gray-50 p-4 rounded min-h-[200px]">
                <p class="text-gray-500">Click a button above to test the API</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h2 class="text-xl font-semibold mb-4">Console Logs</h2>
            <div id="consoleLogs" class="bg-gray-900 text-green-400 p-4 rounded min-h-[200px] font-mono text-sm overflow-auto">
            </div>
        </div>
    </div>

    <script>
        const logsDiv = document.getElementById('consoleLogs');
        const originalLog = console.log;
        const originalError = console.error;

        // Capture console.log
        console.log = function(...args) {
            originalLog.apply(console, args);
            logsDiv.innerHTML += '<div class="mb-1">📝 ' + args.join(' ') + '</div>';
            logsDiv.scrollTop = logsDiv.scrollHeight;
        };

        // Capture console.error
        console.error = function(...args) {
            originalError.apply(console, args);
            logsDiv.innerHTML += '<div class="mb-1 text-red-400">❌ ' + args.join(' ') + '</div>';
            logsDiv.scrollTop = logsDiv.scrollHeight;
        };

        async function testCartAPI() {
            console.log('🔄 Testing cart API...');
            const responseDiv = document.getElementById('apiResponse');
            
            try {
                const response = await fetch('./api/cart.php');
                console.log('Status:', response.status);
                
                const data = await response.json();
                console.log('Response data:', JSON.stringify(data, null, 2));
                
                responseDiv.innerHTML = '<pre class="whitespace-pre-wrap">' + 
                    JSON.stringify(data, null, 2) + '</pre>';
                
                if (data.success) {
                    console.log('✅ Success! Found', data.items.length, 'items');
                } else {
                    console.error('❌ API returned error:', data.message);
                }
            } catch (error) {
                console.error('💥 Error:', error.message);
                responseDiv.innerHTML = '<div class="text-red-600">Error: ' + error.message + '</div>';
            }
        }

        async function testAddToCart() {
            console.log('➕ Testing add to cart...');
            const responseDiv = document.getElementById('apiResponse');
            
            try {
                const response = await fetch('./api/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: 1,
                        quantity: 1
                    })
                });
                
                console.log('Status:', response.status);
                const data = await response.json();
                console.log('Response data:', JSON.stringify(data, null, 2));
                
                responseDiv.innerHTML = '<pre class="whitespace-pre-wrap">' + 
                    JSON.stringify(data, null, 2) + '</pre>';
                
                if (data.success) {
                    console.log('✅ Item added successfully!');
                    setTimeout(testCartAPI, 500);
                } else {
                    console.error('❌ Failed to add item:', data.message);
                }
            } catch (error) {
                console.error('💥 Error:', error.message);
                responseDiv.innerHTML = '<div class="text-red-600">Error: ' + error.message + '</div>';
            }
        }

        console.log('🚀 Cart API Debugger Ready');
        console.log('Session logged in:', <?php echo isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? 'true' : 'false'; ?>);
        console.log('User ID:', <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>);
    </script>
</body>
</html>
