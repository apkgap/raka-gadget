/**
 * Standalone Hero Interactions & Controls
 * (Theme switcher, Account dropdown, Fullscreen Mega Navigation Overlay)
 *
 * @package raka-preorder-theme
 */

(function () {
  'use strict';

  // 1. Theme Toggle Helper
  window.toggleRakaTheme = function (event) {
    if (event) {
      event.stopPropagation();
    }
    var html = document.documentElement;
    var isDark = html.classList.contains('dark') || !html.classList.contains('light');
    var targetTheme = isDark ? 'light' : 'dark';

    var sunIcon = document.getElementById('theme-icon-sun');
    var moonIcon = document.getElementById('theme-icon-moon');
    var overlaySun = document.getElementById('overlay-theme-sun');
    var overlayMoon = document.getElementById('overlay-theme-moon');

    if (targetTheme === 'light') {
      html.classList.remove('dark');
      html.classList.add('light');
      if (sunIcon) { sunIcon.classList.remove('hidden'); sunIcon.classList.add('block'); }
      if (moonIcon) { moonIcon.classList.remove('block'); moonIcon.classList.add('hidden'); }
      if (overlaySun) { overlaySun.classList.remove('hidden'); overlaySun.classList.add('block'); }
      if (overlayMoon) { overlayMoon.classList.remove('block'); overlayMoon.classList.add('hidden'); }
    } else {
      html.classList.remove('light');
      html.classList.add('dark');
      if (sunIcon) { sunIcon.classList.remove('block'); sunIcon.classList.add('hidden'); }
      if (moonIcon) { moonIcon.classList.remove('hidden'); moonIcon.classList.add('block'); }
      if (overlaySun) { overlaySun.classList.remove('block'); overlaySun.classList.add('hidden'); }
      if (overlayMoon) { overlayMoon.classList.remove('hidden'); overlayMoon.classList.add('block'); }
    }
    try {
      localStorage.setItem('raka_theme', targetTheme);
      localStorage.setItem('obsidian_theme', targetTheme);
    } catch (e) {}
    window.dispatchEvent(new CustomEvent('raka-theme-changed', { detail: { theme: targetTheme } }));
  };
  window.toggleObsidianTheme = window.toggleRakaTheme;

  // 2. Account Dropdown Helper
  window.toggleRakaAccountMenu = function (event) {
    if (event) {
      event.stopPropagation();
      event.preventDefault();
    }
    var dropdown = document.getElementById('raka-account-dropdown');
    var btn = document.getElementById('raka-account-btn');
    if (!dropdown) return;

    var isClosed = dropdown.classList.contains('opacity-0') || dropdown.classList.contains('invisible');
    if (isClosed) {
      dropdown.classList.remove('opacity-0', 'invisible', 'translate-y-2', 'pointer-events-none');
      dropdown.classList.add('opacity-100', 'visible', 'translate-y-0', 'pointer-events-auto');
      if (btn) btn.setAttribute('aria-expanded', 'true');
    } else {
      dropdown.classList.add('opacity-0', 'invisible', 'translate-y-2', 'pointer-events-none');
      dropdown.classList.remove('opacity-100', 'visible', 'translate-y-0', 'pointer-events-auto');
      if (btn) btn.setAttribute('aria-expanded', 'false');
    }
  };

  // Close account dropdown on outside click
  document.addEventListener('click', function (e) {
    var dropdown = document.getElementById('raka-account-dropdown');
    var btn = document.getElementById('raka-account-btn');
    if (dropdown && !dropdown.contains(e.target) && (!btn || !btn.contains(e.target))) {
      dropdown.classList.add('opacity-0', 'invisible', 'translate-y-2', 'pointer-events-none');
      dropdown.classList.remove('opacity-100', 'visible', 'translate-y-0', 'pointer-events-auto');
      if (btn) btn.setAttribute('aria-expanded', 'false');
    }
  });

  // 3. Fullscreen Mega Menu Overlay Controls
  function initMegaMenuOverlay() {
    var menuBtn = document.getElementById('raka-menu-btn');
    var overlay = document.getElementById('raka-menu-overlay');
    var closeBtn = document.getElementById('raka-menu-close-btn');
    var rootPanel = document.getElementById('menu-panel-root');
    var megaTriggers = document.querySelectorAll('.mega-menu-trigger');
    var megaPanels = document.querySelectorAll('.mega-panel');
    var backButtons = document.querySelectorAll('.btn-mega-back');

    if (!overlay) return;

    var isOverlayOpen = false;

    function openOverlay() {
      isOverlayOpen = true;
      overlay.style.clipPath = 'inset(0% 0% 0% 0%)';
      overlay.classList.remove('pointer-events-none');
      overlay.classList.add('pointer-events-auto');
      document.body.style.overflow = 'hidden';
    }

    function closeOverlay() {
      isOverlayOpen = false;
      overlay.style.clipPath = 'inset(0% 0% 100% 0%)';
      overlay.classList.add('pointer-events-none');
      overlay.classList.remove('pointer-events-auto');
      document.body.style.overflow = '';
      resetToRootPanel();
    }

    function resetToRootPanel() {
      if (rootPanel) {
        rootPanel.classList.remove('hidden', 'opacity-0', 'pointer-events-none');
        rootPanel.classList.add('flex', 'opacity-100');
      }
      megaPanels.forEach(function (panel) {
        panel.classList.add('hidden', 'opacity-0', 'pointer-events-none', 'invisible');
        panel.classList.remove('flex', 'opacity-100', 'visible');
      });
    }

    if (menuBtn) {
      menuBtn.addEventListener('click', function (e) {
        e.preventDefault();
        openOverlay();
      });
    }

    if (closeBtn) {
      closeBtn.addEventListener('click', function (e) {
        e.preventDefault();
        closeOverlay();
      });
    }

    // Mega panel triggers
    megaTriggers.forEach(function (trigger) {
      trigger.addEventListener('click', function (e) {
        e.preventDefault();
        var targetId = trigger.getAttribute('data-mega-target');
        var targetPanel = document.getElementById(targetId);
        if (!targetPanel) return;

        if (rootPanel) {
          rootPanel.classList.add('hidden', 'opacity-0', 'pointer-events-none');
          rootPanel.classList.remove('flex', 'opacity-100');
        }

        megaPanels.forEach(function (p) {
          p.classList.add('hidden', 'opacity-0', 'pointer-events-none', 'invisible');
          p.classList.remove('flex', 'opacity-100', 'visible');
        });

        targetPanel.classList.remove('hidden', 'opacity-0', 'pointer-events-none', 'invisible', 'translate-y-8');
        targetPanel.classList.add('flex', 'opacity-100', 'visible', 'translate-y-0');
      });
    });

    // Back buttons
    backButtons.forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        resetToRootPanel();
      });
    });

    // ESC Key listener
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && isOverlayOpen) {
        closeOverlay();
      }
    });
  }

  // 4. Initial Theme Icon Sync
  function syncThemeIcons() {
    var isDark = document.documentElement.classList.contains('dark') || !document.documentElement.classList.contains('light');
    var sunIcon = document.getElementById('theme-icon-sun');
    var moonIcon = document.getElementById('theme-icon-moon');
    var overlaySun = document.getElementById('overlay-theme-sun');
    var overlayMoon = document.getElementById('overlay-theme-moon');

    if (isDark) {
      if (sunIcon) { sunIcon.classList.remove('block'); sunIcon.classList.add('hidden'); }
      if (moonIcon) { moonIcon.classList.remove('hidden'); moonIcon.classList.add('block'); }
      if (overlaySun) { overlaySun.classList.remove('block'); overlaySun.classList.add('hidden'); }
      if (overlayMoon) { overlayMoon.classList.remove('hidden'); overlayMoon.classList.add('block'); }
    } else {
      if (sunIcon) { sunIcon.classList.remove('hidden'); sunIcon.classList.add('block'); }
      if (moonIcon) { moonIcon.classList.remove('block'); moonIcon.classList.add('hidden'); }
      if (overlaySun) { overlaySun.classList.remove('hidden'); overlaySun.classList.add('block'); }
      if (overlayMoon) { overlayMoon.classList.remove('block'); overlayMoon.classList.add('hidden'); }
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      initMegaMenuOverlay();
      syncThemeIcons();
    });
  } else {
    initMegaMenuOverlay();
    syncThemeIcons();
  }
})();
