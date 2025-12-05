/**
 * Universal Profile Picture Sync
 * Include this script on all user pages to keep profile pictures synchronized
 * across tabs and pages in real-time
 */

(function() {
    'use strict';
    
    // Function to update all profile images on the page
    function updateProfileImages() {
        const profilePicture = sessionStorage.getItem('profile_picture');
        
        // Find all profile image elements (various selectors used across pages)
        const selectors = [
            '#navProfilePic img',
            '#headerProfilePic img', 
            '.profile-image',
            'img[alt="Profile"]',
            '[data-profile-image]'
        ];
        
        selectors.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(img => {
                if (profilePicture) {
                    img.src = profilePicture;
                    img.style.display = 'block';
                } else {
                    // No profile picture - handle gracefully
                    console.log('No profile picture in session storage');
                }
            });
        });
        
        // Also update parent containers that might have full HTML
        const containerSelectors = [
            '#navProfilePic',
            '#headerProfilePic',
            '.profile-picture-container'
        ];
        
        containerSelectors.forEach(selector => {
            const container = document.querySelector(selector);
            if (container && profilePicture) {
                // Only update if it's currently showing a default icon
                if (container.querySelector('.fa-user') || !container.querySelector('img')) {
                    container.innerHTML = `<img src="${profilePicture}" alt="Profile" class="w-8 h-8 rounded-full cursor-pointer object-cover">`;
                } else {
                    // Update existing image src
                    const img = container.querySelector('img');
                    if (img) img.src = profilePicture;
                }
            }
        });
    }
    
    // Check for updates when page loads
    window.addEventListener('DOMContentLoaded', function() {
        updateProfileImages();
    });
    
    // Listen for storage events (updates from other tabs)
    window.addEventListener('storage', function(e) {
        if (e.key === 'profile_updated' || e.key === 'profile_picture') {
            console.log('Profile picture updated in another tab/page');
            updateProfileImages();
        }
    });
    
    // Poll for changes every 2 seconds (for same-tab updates)
    let lastUpdateTime = sessionStorage.getItem('profile_updated');
    setInterval(function() {
        const currentUpdateTime = sessionStorage.getItem('profile_updated');
        if (currentUpdateTime && currentUpdateTime !== lastUpdateTime) {
            console.log('Profile picture updated in this tab');
            lastUpdateTime = currentUpdateTime;
            updateProfileImages();
        }
    }, 2000);
    
    // Expose function globally for manual triggering
    window.updateProfileImages = updateProfileImages;
})();
