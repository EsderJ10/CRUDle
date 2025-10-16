/**
 * Theme Initialization and Toggle Script
 * Handles all theme switching logic in one place
 * Runs before page render to prevent theme flash
 * Must be placed in <head> before CSS loads
 * 
 * Checks for saved theme preference in localStorage
 * Falls back to system preference (prefers-color-scheme)
 * Applies dark-theme class to html element immediately
 */

(function() {
    'use strict';

    const htmlElement = document.documentElement;

    /**
     * Initialize theme on page load
     * Executes before content renders to prevent flash
     */
    function initializeTheme() {
        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        // Determine if dark theme should be active
        const isDarkTheme = savedTheme === 'dark' || (!savedTheme && prefersDark);

        // Apply theme class to html element
        if (isDarkTheme) {
            htmlElement.classList.add('dark-theme');
        } else {
            htmlElement.classList.remove('dark-theme');
        }
    }

    /**
     * Toggle theme and save preference
     */
    function toggleTheme() {
        htmlElement.classList.toggle('dark-theme');
        const isDark = htmlElement.classList.contains('dark-theme');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        console.log('Theme switched to:', isDark ? 'dark' : 'light');
    }

    /**
     * Setup theme toggle button listener
     */
    function setupThemeToggle() {
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Theme toggle clicked');
                toggleTheme();
            });
        }
    }

    // Run immediately on page load
    initializeTheme();

    // Setup toggle button when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupThemeToggle);
    } else {
        setupThemeToggle();
    }

    /**
     * Listen for system theme preference changes
     * Updates theme if user changes OS-level preference
     */
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function() {
        const savedTheme = localStorage.getItem('theme');
        
        // Only auto-switch if no manual preference is saved
        if (!savedTheme) {
            if (this.matches) {
                htmlElement.classList.add('dark-theme');
            } else {
                htmlElement.classList.remove('dark-theme');
            }
        }
    });
})();
