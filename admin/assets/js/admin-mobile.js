(function () {
    'use strict';

    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');

    function isMobileViewport() {
        return window.matchMedia('(max-width: 991.98px)').matches;
    }

    function openSidebar() {
        if (!sidebar || !isMobileViewport()) return;
        sidebar.classList.add('mobile-menu', 'is-open');
        if (overlay) overlay.classList.add('is-visible');
        document.body.classList.add('sidebar-open');
    }

    function closeSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('mobile-menu', 'is-open');
        if (overlay) overlay.classList.remove('is-visible');
        document.body.classList.remove('sidebar-open');
    }

    function toggleSidebar() {
        if (!sidebar) return;
        if (sidebar.classList.contains('mobile-menu') || sidebar.classList.contains('is-open')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.menu-toggle').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                toggleSidebar();
            });
        });

        if (overlay) {
            overlay.addEventListener('click', function (e) {
                e.preventDefault();
                closeSidebar();
            });
        }

        // Clicks inside the drawer must not bubble to the overlay
        if (sidebar) {
            sidebar.addEventListener('click', function (e) {
                e.stopPropagation();
            });

            sidebar.querySelectorAll('.nk-menu-link, .nk-menu-sub-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (!isMobileViewport()) return;
                    var href = link.getAttribute('href');
                    // Keep drawer open for submenu expanders (Billing, Reports)
                    if (!href || href === '#' || href.indexOf('javascript:') === 0) {
                        return;
                    }
                    closeSidebar();
                });
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeSidebar();
        });

        window.addEventListener('resize', function () {
            if (!isMobileViewport()) closeSidebar();
        });
    });
})();
