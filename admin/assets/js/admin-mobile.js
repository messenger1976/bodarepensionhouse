(function () {
    'use strict';

    var DESKTOP_MIN = 992;
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
                toggleSidebar();
            });
        });

        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }

        if (sidebar) {
            sidebar.querySelectorAll('.nk-menu-link, .nk-menu-sub-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (isMobileViewport()) closeSidebar();
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
