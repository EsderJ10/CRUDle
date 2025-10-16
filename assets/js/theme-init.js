/**
 * Theme Initialization and Toggle Script
 * Handles all theme switching logic in one place
 * Runs before page render to prevent theme flash
 * Must be placed in <head> before CSS loads
 * 
 * Checks for saved theme preference in localStorage
 * Falls back to system preference (prefers-color-scheme)
 * Applies dark-theme class to html element immediately
 * 
 * Features smooth wave animation when switching themes
 */

(function() {
    'use strict';

    const htmlElement = document.documentElement;

    /**
     * Create wave effect from button position
     */
    function createWaveEffect(event) {
        // Get button position
        const button = event.target.closest('button');
        if (!button) return;

        const rect = button.getBoundingClientRect();
        const startX = rect.left + rect.width / 2;
        const startY = rect.top + rect.height / 2;

        // Create wave container
        const wave = document.createElement('div');
        wave.className = 'theme-wave';
        wave.style.cssText = `
            position: fixed;
            top: ${startY}px;
            left: ${startX}px;
            width: 0;
            height: 0;
            border-radius: 50%;
            pointer-events: none;
            z-index: 10000;
            background: radial-gradient(circle, 
                ${htmlElement.classList.contains('dark-theme') ? 'rgba(255,255,255,0.2)' : 'rgba(0,0,0,0.1)'} 0%, 
                transparent 70%
            );
            transform: translate(-50%, -50%);
        `;

        document.body.appendChild(wave);

        // Calculate distance to farthest corner
        const maxDistance = Math.sqrt(
            Math.max(startX, window.innerWidth - startX) ** 2 +
            Math.max(startY, window.innerHeight - startY) ** 2
        );

        // Animate wave
        let progress = 0;
        const duration = 600; // ms
        const startTime = performance.now();

        function animate(currentTime) {
            progress = (currentTime - startTime) / duration;

            if (progress < 1) {
                const size = maxDistance * 2 * progress;
                wave.style.width = size + 'px';
                wave.style.height = size + 'px';
                requestAnimationFrame(animate);
            } else {
                wave.remove();
            }
        }

        requestAnimationFrame(animate);
    }

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
    function toggleTheme(event) {
        // Create wave effect
        createWaveEffect(event);

        // Small delay to show wave before theme change
        setTimeout(() => {
            htmlElement.classList.toggle('dark-theme');
            const isDark = htmlElement.classList.contains('dark-theme');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            console.log('Theme switched to:', isDark ? 'dark' : 'light');
        }, 50);
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
                toggleTheme(e);
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
