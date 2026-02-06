// simple_menu.js - Clean, simple menu functionality
console.log("Simple menu script loaded");

document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM loaded, initializing menu");
    
    const menuToggle = document.getElementById('menuToggle');
    const menuPanel = document.getElementById('menuPanel');
    const menuClose = document.getElementById('menuClose');
    
    console.log("Menu elements found:", {
        toggle: menuToggle,
        panel: menuPanel,
        close: menuClose
    });
    
    if (!menuToggle || !menuPanel) {
        console.error("Menu elements not found!");
        return;
    }
    
    // Show menu
    function showMenu() {
        console.log("Showing menu");
        menuPanel.classList.add('open');
        document.body.style.overflow = 'hidden'; // Prevent background scroll
    }
    
    // Hide menu
    function hideMenu() {
        console.log("Hiding menu");
        menuPanel.classList.remove('open');
        document.body.style.overflow = ''; // Restore scroll
    }
    
    // Toggle menu
    function toggleMenu() {
        console.log("Toggle menu clicked");
        if (menuPanel.classList.contains('open')) {
            hideMenu();
        } else {
            showMenu();
        }
    }
    
    // Event listeners
    menuToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log("Menu toggle button clicked");
        toggleMenu();
    });
    
    // Close button
    if (menuClose) {
        menuClose.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log("Menu close button clicked");
            hideMenu();
        });
    }
    
    // Close when clicking outside
    document.addEventListener('click', function(e) {
        if (menuPanel.classList.contains('open') && 
            !menuPanel.contains(e.target) && 
            !menuToggle.contains(e.target)) {
            console.log("Clicked outside menu, closing");
            hideMenu();
        }
    });
    
    // Close with ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && menuPanel.classList.contains('open')) {
            console.log("ESC key pressed, closing menu");
            hideMenu();
        }
    });
    
    console.log("Simple menu initialized successfully");
});
