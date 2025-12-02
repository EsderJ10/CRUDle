/*
 * Theme initialization and toggle management script
 * Handles theme switching logic in one place
 * Runs before page render to avoid theme flicker
 * Must be placed in <head> before CSS loads
 * Checks saved theme preference in localStorage
 * Fallbacks to system preference (prefers-color-scheme)
 * Applies dark-theme class to html element immediately
 * Presents a smooth wave animation when changing theme
 * Author: José Antonio Cortés Ferre.
 */

// Use IIFE to avoid polluting global scope.

(function () {
    'use strict';

    const htmlElement = document.documentElement;

    /**
     * Creates a wave effect on the button when changing theme
     */
    function createWaveEffect(event) {
        const button = event.target.closest('button');
        if (!button) return;

        const rect = button.getBoundingClientRect();
        const startX = rect.left + rect.width / 2;
        const startY = rect.top + rect.height / 2;

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

        const maxDistance = Math.sqrt(
            Math.max(startX, window.innerWidth - startX) ** 2 +
            Math.max(startY, window.innerHeight - startY) ** 2
        );

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
     * Initializes theme on page load
     * Runs before page render to avoid theme flicker
     */
    function initializeTheme() {
        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const isDarkTheme = savedTheme === 'dark' || (!savedTheme && prefersDark);

        if (isDarkTheme) {
            htmlElement.classList.add('dark-theme');
        } else {
            htmlElement.classList.remove('dark-theme');
        }
    }

    /**
     * Handles theme toggle on button click
     */
    function toggleTheme(event) {
        createWaveEffect(event);

        setTimeout(() => {
            htmlElement.classList.toggle('dark-theme');
            const isDark = htmlElement.classList.contains('dark-theme');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }, 50);
    }

    /**
     * Sets up listener for theme toggle button
     */
    function setupThemeToggle() {
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', function (e) {
                e.preventDefault();
                toggleTheme(e);
            });
        }
    }

    initializeTheme();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupThemeToggle);
    } else {
        setupThemeToggle();
    }

    /**
     * Listens for system preference changes
     * Updates theme if user changes system-level preference
     */
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
        const savedTheme = localStorage.getItem('theme');

        // Only change if no saved preference
        if (!savedTheme) {
            if (this.matches) {
                htmlElement.classList.add('dark-theme');
            } else {
                htmlElement.classList.remove('dark-theme');
            }
        }
    });
})();
