/**
 * Search Auto-Suggestion
 * Provides real-time search suggestions as user types
 */

(function() {
    'use strict';
    
    let searchTimeout;
    let currentSearchInput;
    let suggestions = [];
    
    // Create autocomplete dropdown
    function createAutocompleteDropdown() {
        const existingDropdown = document.getElementById('searchAutocomplete');
        if (existingDropdown) return;
        
        const dropdown = document.createElement('div');
        dropdown.id = 'searchAutocomplete';
        dropdown.className = 'search-autocomplete hidden';
        document.body.appendChild(dropdown);
        
        // Add CSS styles
        if (!document.getElementById('searchAutocompleteStyles')) {
            const style = document.createElement('style');
            style.id = 'searchAutocompleteStyles';
            style.textContent = `
                .search-autocomplete {
                    position: absolute;
                    background: white;
                    border: 1px solid #e5e7eb;
                    border-radius: 12px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                    max-height: 400px;
                    overflow-y: auto;
                    z-index: 1000;
                    min-width: 300px;
                }
                
                .search-autocomplete-item {
                    padding: 12px 16px;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    border-bottom: 1px solid #f3f4f6;
                    transition: background-color 0.2s;
                }
                
                .search-autocomplete-item:last-child {
                    border-bottom: none;
                }
                
                .search-autocomplete-item:hover,
                .search-autocomplete-item.active {
                    background-color: #f0f9ff;
                }
                
                .search-autocomplete-icon {
                    width: 40px;
                    height: 40px;
                    object-fit: cover;
                    border-radius: 6px;
                    flex-shrink: 0;
                }
                
                .search-autocomplete-icon.fa {
                    width: auto;
                    font-size: 20px;
                    color: #6b7280;
                }
                
                .search-autocomplete-text {
                    flex: 1;
                }
                
                .search-autocomplete-name {
                    font-weight: 500;
                    color: #1f2937;
                    font-size: 14px;
                }
                
                .search-autocomplete-category {
                    font-size: 12px;
                    color: #6b7280;
                }
                
                .search-autocomplete-price {
                    font-weight: 600;
                    color: #059669;
                    font-size: 14px;
                }
                
                .search-autocomplete-header {
                    padding: 12px 16px;
                    font-size: 12px;
                    color: #6b7280;
                    background-color: #f9fafb;
                    border-bottom: 1px solid #e5e7eb;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                
                .search-autocomplete-footer {
                    padding: 12px 16px;
                    text-align: center;
                    border-top: 1px solid #e5e7eb;
                    background-color: #f9fafb;
                }
                
                .search-autocomplete-footer a {
                    color: #059669;
                    font-weight: 600;
                    font-size: 14px;
                    text-decoration: none;
                }
                
                .search-autocomplete-footer a:hover {
                    text-decoration: underline;
                }
                
                .search-autocomplete-empty {
                    padding: 24px 16px;
                    text-align: center;
                    color: #6b7280;
                    font-size: 14px;
                }
                
                .search-autocomplete::-webkit-scrollbar {
                    width: 6px;
                }
                
                .search-autocomplete::-webkit-scrollbar-track {
                    background: #f3f4f6;
                }
                
                .search-autocomplete::-webkit-scrollbar-thumb {
                    background: #d1d5db;
                    border-radius: 3px;
                }
                
                .search-autocomplete::-webkit-scrollbar-thumb:hover {
                    background: #9ca3af;
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    // Fetch search suggestions
    async function fetchSuggestions(query) {
        if (!query || query.length < 2) {
            hideSuggestions();
            return;
        }
        
        try {
            const response = await fetch(`../api/search-suggestions.php?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success && data.suggestions) {
                suggestions = data.suggestions;
                displaySuggestions(query);
            } else {
                hideSuggestions();
            }
        } catch (error) {
            console.error('Error fetching suggestions:', error);
            // Fallback: search in cached products
            searchCachedProducts(query);
        }
    }
    
    // Fallback: Search in locally cached products
    function searchCachedProducts(query) {
        const lowerQuery = query.toLowerCase();
        
        // Try to get products from sessionStorage or fetch them
        let products = [];
        const cachedProducts = sessionStorage.getItem('allProducts');
        
        if (cachedProducts) {
            try {
                products = JSON.parse(cachedProducts);
            } catch (e) {
                console.error('Error parsing cached products:', e);
            }
        }
        
        if (products.length === 0) {
            // No cached products, hide suggestions
            hideSuggestions();
            return;
        }
        
        // Filter products by query
        const filtered = products.filter(product => {
            const name = (product.name || product.product_name || '').toLowerCase();
            const category = (product.category || '').toLowerCase();
            return name.includes(lowerQuery) || category.includes(lowerQuery);
        }).slice(0, 8);
        
        if (filtered.length > 0) {
            suggestions = filtered;
            displaySuggestions(query);
        } else {
            hideSuggestions();
        }
    }
    
    // Display suggestions
    function displaySuggestions(query) {
        const dropdown = document.getElementById('searchAutocomplete');
        if (!dropdown || !currentSearchInput) return;
        
        if (suggestions.length === 0) {
            dropdown.innerHTML = `
                <div class="search-autocomplete-empty">
                    <i class="fas fa-search text-2xl mb-2"></i>
                    <p>No results found for "${query}"</p>
                </div>
            `;
        } else {
            const highlightQuery = (text, query) => {
                const regex = new RegExp(`(${query})`, 'gi');
                return text.replace(regex, '<strong>$1</strong>');
            };
            
            // Helper function to resolve image path
            function resolveImagePath(img) {
                if (!img) return '../images/products/placeholder.png';
                // If already has http/https, use as is
                if (img.startsWith('http://') || img.startsWith('https://')) return img;
                // If already has ../, use as is
                if (img.startsWith('../')) return img;
                // If starts with images/, add ../
                if (img.startsWith('images/')) return '../' + img;
                // Otherwise return as is
                return img;
            }
            
            dropdown.innerHTML = `
                <div class="search-autocomplete-header">Suggested Products</div>
                ${suggestions.map(item => {
                    const name = item.name || item.product_name || 'Product';
                    const category = item.category || '';
                    const price = parseFloat(item.price || item.amount || 0);
                    const image = resolveImagePath(item.image || item.image_url || item.product_image);
                    
                    return `
                        <div class="search-autocomplete-item" data-name="${name}" data-url="products.php?search=${encodeURIComponent(name)}">
                            <img src="${image}" alt="${name}" class="search-autocomplete-icon" onerror="this.src='../images/products/placeholder.png'">
                            <div class="search-autocomplete-text">
                                <div class="search-autocomplete-name">${highlightQuery(name, query)}</div>
                                ${category ? `<div class="search-autocomplete-category">${category}</div>` : ''}
                            </div>
                        </div>
                    `;
                }).join('')}
                <div class="search-autocomplete-footer">
                    <a href="products.php?search=${encodeURIComponent(query)}">View all results for "${query}"</a>
                </div>
            `;
            
            // Add click handlers
            dropdown.querySelectorAll('.search-autocomplete-item').forEach(item => {
                item.addEventListener('click', function() {
                    const url = this.dataset.url;
                    if (url) window.location.href = url;
                });
            });
        }
        
        // Position dropdown below search input
        positionDropdown();
        dropdown.classList.remove('hidden');
    }
    
    // Position dropdown
    function positionDropdown() {
        if (!currentSearchInput) return;
        
        const dropdown = document.getElementById('searchAutocomplete');
        const rect = currentSearchInput.getBoundingClientRect();
        
        dropdown.style.position = 'fixed';
        dropdown.style.top = `${rect.bottom + 5}px`;
        dropdown.style.left = `${rect.left}px`;
        dropdown.style.width = `${rect.width}px`;
    }
    
    // Hide suggestions
    function hideSuggestions() {
        const dropdown = document.getElementById('searchAutocomplete');
        if (dropdown) {
            dropdown.classList.add('hidden');
        }
    }
    
    // Initialize autocomplete
    document.addEventListener('DOMContentLoaded', function() {
        createAutocompleteDropdown();
        
        // Find all search inputs
        const searchInputs = document.querySelectorAll('input[name="search"], input[placeholder*="Search"]');
        
        searchInputs.forEach(input => {
            // Input event for typing
            input.addEventListener('input', function(e) {
                currentSearchInput = this;
                const query = this.value.trim();
                
                clearTimeout(searchTimeout);
                
                if (query.length < 2) {
                    hideSuggestions();
                    return;
                }
                
                searchTimeout = setTimeout(() => {
                    fetchSuggestions(query);
                }, 300);
            });
            
            // Focus event
            input.addEventListener('focus', function() {
                currentSearchInput = this;
                const query = this.value.trim();
                if (query.length >= 2 && suggestions.length > 0) {
                    displaySuggestions(query);
                }
            });
            
            // Keyboard navigation
            input.addEventListener('keydown', function(e) {
                const dropdown = document.getElementById('searchAutocomplete');
                if (!dropdown || dropdown.classList.contains('hidden')) return;
                
                const items = dropdown.querySelectorAll('.search-autocomplete-item');
                const activeItem = dropdown.querySelector('.search-autocomplete-item.active');
                let currentIndex = activeItem ? Array.from(items).indexOf(activeItem) : -1;
                
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    currentIndex = (currentIndex + 1) % items.length;
                    items.forEach((item, idx) => {
                        item.classList.toggle('active', idx === currentIndex);
                    });
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    currentIndex = currentIndex <= 0 ? items.length - 1 : currentIndex - 1;
                    items.forEach((item, idx) => {
                        item.classList.toggle('active', idx === currentIndex);
                    });
                } else if (e.key === 'Enter' && activeItem) {
                    e.preventDefault();
                    activeItem.click();
                } else if (e.key === 'Escape') {
                    hideSuggestions();
                }
            });
        });
        
        // Hide when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('searchAutocomplete');
            if (dropdown && !dropdown.contains(e.target) && !e.target.matches('input[name="search"]')) {
                hideSuggestions();
            }
        });
        
        // Reposition on scroll/resize
        window.addEventListener('scroll', positionDropdown);
        window.addEventListener('resize', positionDropdown);
    });
    
})();
