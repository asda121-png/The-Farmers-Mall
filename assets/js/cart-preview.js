/**
 * Cart Hover Preview
 * Shows a preview of cart items when hovering over the cart icon
 */

(function() {
    'use strict';
    
    let cartPreviewTimeout;
    let isCartPreviewVisible = false;
    
    // Create cart preview HTML
    function createCartPreview() {
        const existingPreview = document.getElementById('cartPreview');
        if (existingPreview) return;
        
        const cartPreview = document.createElement('div');
        cartPreview.id = 'cartPreview';
        cartPreview.className = 'cart-preview hidden';
        cartPreview.innerHTML = `
            <div class="cart-preview-header">
                <h3 class="font-semibold text-gray-800">Shopping Cart</h3>
                <span id="cartPreviewCount" class="text-sm text-gray-500">0 items</span>
            </div>
            <div id="cartPreviewItems" class="cart-preview-items">
                <div class="text-center py-8 text-gray-400">
                    <i class="fas fa-shopping-cart text-4xl mb-2"></i>
                    <p>Your cart is empty</p>
                </div>
            </div>
            <div class="cart-preview-footer">
                <div class="flex justify-between items-center mb-3">
                    <span class="font-semibold">Total:</span>
                    <span id="cartPreviewTotal" class="font-bold text-green-700 text-lg">₱0.00</span>
                </div>
                <a href="cart.php" class="block w-full bg-green-600 text-white text-center py-2 rounded-lg hover:bg-green-700 transition">
                    View Cart
                </a>
            </div>
        `;
        
        document.body.appendChild(cartPreview);
        
        // Add CSS styles
        if (!document.getElementById('cartPreviewStyles')) {
            const style = document.createElement('style');
            style.id = 'cartPreviewStyles';
            style.textContent = `
                .cart-preview {
                    position: absolute;
                    top: 100%;
                    right: 0;
                    margin-top: 8px;
                    width: 350px;
                    max-width: 90vw;
                    background: white;
                    border-radius: 12px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                    z-index: 1000;
                    opacity: 0;
                    transform: translateY(-10px);
                    transition: opacity 0.3s ease, transform 0.3s ease;
                    pointer-events: none;
                }
                
                .cart-preview.visible {
                    opacity: 1;
                    transform: translateY(0);
                    pointer-events: auto;
                }
                
                .cart-preview-header {
                    padding: 16px;
                    border-bottom: 1px solid #e5e7eb;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                
                .cart-preview-items {
                    max-height: 300px;
                    overflow-y: auto;
                }
                
                .cart-preview-item {
                    display: flex;
                    gap: 12px;
                    padding: 12px 16px;
                    border-bottom: 1px solid #f3f4f6;
                    transition: background-color 0.2s;
                }
                
                .cart-preview-item:hover {
                    background-color: #f9fafb;
                }
                
                .cart-preview-item img {
                    width: 60px;
                    height: 60px;
                    object-fit: cover;
                    border-radius: 8px;
                }
                
                .cart-preview-item-details {
                    flex: 1;
                }
                
                .cart-preview-item-name {
                    font-weight: 600;
                    color: #1f2937;
                    font-size: 14px;
                    margin-bottom: 4px;
                }
                
                .cart-preview-item-quantity {
                    font-size: 12px;
                    color: #6b7280;
                }
                
                .cart-preview-item-price {
                    font-weight: 600;
                    color: #059669;
                    font-size: 14px;
                }
                
                .cart-preview-footer {
                    padding: 16px;
                    border-top: 1px solid #e5e7eb;
                }
                
                .cart-preview-items::-webkit-scrollbar {
                    width: 6px;
                }
                
                .cart-preview-items::-webkit-scrollbar-track {
                    background: #f3f4f6;
                }
                
                .cart-preview-items::-webkit-scrollbar-thumb {
                    background: #d1d5db;
                    border-radius: 3px;
                }
                
                .cart-preview-items::-webkit-scrollbar-thumb:hover {
                    background: #9ca3af;
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    // Fetch and display cart items
    async function loadCartPreview() {
        try {
            const response = await fetch('../api/cart.php');
            const data = await response.json();
            
            const previewItems = document.getElementById('cartPreviewItems');
            const previewCount = document.getElementById('cartPreviewCount');
            const previewTotal = document.getElementById('cartPreviewTotal');
            
            if (!previewItems || !previewCount || !previewTotal) {
                console.error('Cart preview elements not found');
                return;
            }
            
            if (!data.success || !data.items || data.items.length === 0) {
                previewItems.innerHTML = `
                    <div class="text-center py-8 text-gray-400">
                        <i class="fas fa-shopping-cart text-4xl mb-2"></i>
                        <p>Your cart is empty</p>
                    </div>
                `;
                previewCount.textContent = '0 items';
                previewTotal.textContent = '₱0.00';
                return;
            }
            
            const items = data.items;
            const totalItems = items.reduce((sum, item) => sum + parseInt(item.quantity || 0), 0);
            const totalAmount = items.reduce((sum, item) => {
                const price = parseFloat(item.price || 0);
                const quantity = parseInt(item.quantity || 0);
                return sum + (price * quantity);
            }, 0);
            
            previewCount.textContent = `${totalItems} item${totalItems !== 1 ? 's' : ''}`;
            previewTotal.textContent = `₱${totalAmount.toFixed(2)}`;
            
            // Helper function to resolve image path
            function resolveImagePath(img) {
                if (!img) return '../images/products/placeholder.png';
                // If already has http/https, use as is
                if (img.startsWith('http://') || img.startsWith('https://')) return img;
                // If already has ../, use as is
                if (img.startsWith('../')) return img;
                // If starts with images/, add ../
                if (img.startsWith('images/')) return '../' + img;
                // Otherwise assume it's relative and add ../images/products/
                return img;
            }
            
            previewItems.innerHTML = items.map(item => {
                const itemName = item.name || item.product_name || 'Product';
                const itemImage = resolveImagePath(item.image || item.image_url);
                const itemPrice = parseFloat(item.price || 0);
                const itemQty = parseInt(item.quantity || 1);
                
                return `
                    <div class="cart-preview-item">
                        <img src="${itemImage}" 
                             alt="${itemName}"
                             onerror="this.src='../images/products/placeholder.png'">
                        <div class="cart-preview-item-details">
                            <div class="cart-preview-item-name">${itemName}</div>
                            <div class="cart-preview-item-quantity">Qty: ${itemQty}</div>
                        </div>
                        <div class="cart-preview-item-price">
                            ₱${(itemPrice * itemQty).toFixed(2)}
                        </div>
                    </div>
                `;
            }).join('');
            
        } catch (error) {
            console.error('Error loading cart preview:', error);
            const previewItems = document.getElementById('cartPreviewItems');
            if (previewItems) {
                previewItems.innerHTML = `
                    <div class="text-center py-8 text-gray-400">
                        <i class="fas fa-exclamation-circle text-4xl mb-2"></i>
                        <p>Error loading cart</p>
                    </div>
                `;
            }
        }
    }
    
    // Show cart preview
    function showCartPreview() {
        const preview = document.getElementById('cartPreview');
        if (preview) {
            loadCartPreview();
            preview.classList.remove('hidden');
            setTimeout(() => preview.classList.add('visible'), 10);
            isCartPreviewVisible = true;
        }
    }
    
    // Hide cart preview
    function hideCartPreview() {
        const preview = document.getElementById('cartPreview');
        if (preview) {
            preview.classList.remove('visible');
            setTimeout(() => preview.classList.add('hidden'), 300);
            isCartPreviewVisible = false;
        }
    }
    
    // Initialize cart preview on DOM load
    document.addEventListener('DOMContentLoaded', function() {
        createCartPreview();
        
        // Find cart icon link
        const cartLinks = document.querySelectorAll('a[href*="cart.php"]');
        
        cartLinks.forEach(cartLink => {
            const cartContainer = cartLink.closest('.relative') || cartLink.parentElement;
            
            // Make sure the container is positioned relatively
            if (cartContainer && !cartContainer.classList.contains('relative')) {
                cartContainer.classList.add('relative');
            }
            
            // Attach cart preview to the container
            const preview = document.getElementById('cartPreview');
            if (preview && cartContainer) {
                cartContainer.appendChild(preview);
            }
            
            // Add hover events
            cartLink.addEventListener('mouseenter', function() {
                clearTimeout(cartPreviewTimeout);
                showCartPreview();
            });
            
            cartLink.addEventListener('mouseleave', function(e) {
                clearTimeout(cartPreviewTimeout);
                cartPreviewTimeout = setTimeout(() => {
                    const preview = document.getElementById('cartPreview');
                    if (preview && !preview.matches(':hover')) {
                        hideCartPreview();
                    }
                }, 200);
            });
            
            // Keep preview open when hovering over it
            const preview = document.getElementById('cartPreview');
            if (preview) {
                preview.addEventListener('mouseenter', function() {
                    clearTimeout(cartPreviewTimeout);
                });
                
                preview.addEventListener('mouseleave', function() {
                    cartPreviewTimeout = setTimeout(hideCartPreview, 200);
                });
            }
        });
    });
    
    // Listen for cart updates
    window.addEventListener('cartUpdated', function() {
        if (isCartPreviewVisible) {
            loadCartPreview();
        }
    });
    
})();
