/*
 * Script de inicialización del tema y gestión del cambio de tema
 * Maneja la lógica de cambio de tema en un solo lugar
 * Se ejecuta antes de que se renderice la página para evitar parpadeos de tema
 * Debe colocarse en <head> antes de que se cargue CSS
 * Verifica la preferencia de tema guardada en localStorage
 * Recurre a la preferencia del sistema (prefers-color-scheme)
 * Aplica la clase dark-theme al elemento html de inmediato
 * Presenta una suave animación de ola al cambiar de tema
 * Autor: José Antonio Cortés Ferre.
 */
 
// Se usa IIFE para evitar contaminar el scope global.

(function() {
    'use strict';

    const htmlElement = document.documentElement;

    /**
     * Crea un efecto de ola en el botón al cambiar de tema
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
     * Inicializa el tema al cargar la página
     * Se ejecuta antes de que se renderice la página para evitar parpadeos de tema
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
     * Maneja el cambio de tema al hacer clic en el botón
     */
    function toggleTheme(event) {
        createWaveEffect(event);

        setTimeout(() => {
            htmlElement.classList.toggle('dark-theme');
            const isDark = htmlElement.classList.contains('dark-theme');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            console.log('Theme switched to:', isDark ? 'dark' : 'light');
        }, 50);
    }

    /**
     * Configura el listener para el botón de cambio de tema
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

    initializeTheme();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupThemeToggle);
    } else {
        setupThemeToggle();
    }

    /**
     * Escucha cambios en la preferencia del sistema
     * Actualiza el tema si el usuario cambia la preferencia a nivel de sistema
     */
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function() {
        const savedTheme = localStorage.getItem('theme');
        
        // Solo hace el cambio si no hay una preferencia guardada
        if (!savedTheme) {
            if (this.matches) {
                htmlElement.classList.add('dark-theme');
            } else {
                htmlElement.classList.remove('dark-theme');
            }
        }
    });
})();
