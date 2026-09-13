    </div>
    <!-- End Content -->
    
    <?php $this->load->view('admin/layout/mobile_nav'); ?>
    
    <!-- Dashlite JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="<?php echo base_url('assets/js/timezone.js'); ?>?v=<?php echo @filemtime(FCPATH . 'assets/js/timezone.js') ?: time(); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 (delete / confirm prompts) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Auto-hide flash messages after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        document.addEventListener('DOMContentLoaded', function() {
            // Dark Mode Toggle
            const darkModeToggle = document.getElementById('darkModeToggle');
            const body = document.body;
            
            // Check for saved dark mode preference or default to light mode
            const darkMode = localStorage.getItem('darkMode') === 'true';
            
            // Apply dark mode if it was previously enabled
            if (darkMode) {
                body.classList.add('dark-mode');
                if (darkModeToggle) {
                    darkModeToggle.checked = true;
                }
            }
            
            // Toggle dark mode when switch is clicked
            if (darkModeToggle) {
                darkModeToggle.addEventListener('change', function() {
                    if (this.checked) {
                        body.classList.add('dark-mode');
                        localStorage.setItem('darkMode', 'true');
                    } else {
                        body.classList.remove('dark-mode');
                        localStorage.setItem('darkMode', 'false');
                    }
                });
            }
        });
        
        // Dashlite Confirmation Modal System
        function showConfirmModal(options) {
            const {
                title = 'Confirm Action',
                message = 'Are you sure you want to proceed?',
                confirmText = 'Confirm',
                cancelText = 'Cancel',
                confirmClass = 'btn-primary',
                icon = 'bi-exclamation-triangle',
                iconClass = 'modal-icon-warning',
                onConfirm = null,
                onCancel = null
            } = options;
            
            const modalId = 'confirmModal_' + Date.now();
            const modalHtml = `
                <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="${modalId}Label" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-sm">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="${modalId}Label">${title}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body modal-confirm">
                                <div class="modal-icon ${iconClass}">
                                    <i class="bi ${icon}"></i>
                                </div>
                                <p>${message}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle"></i> ${cancelText}
                                </button>
                                <button type="button" class="btn ${confirmClass} confirm-btn">
                                    <i class="bi bi-check-circle"></i> ${confirmText}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing confirm modal if any
            const existingModal = document.querySelector('[id^="confirmModal_"]');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            const modalElement = document.getElementById(modalId);
            const modal = new bootstrap.Modal(modalElement);
            
            // Handle confirm button
            const confirmBtn = modalElement.querySelector('.confirm-btn');
            confirmBtn.addEventListener('click', function() {
                modal.hide();
                if (onConfirm && typeof onConfirm === 'function') {
                    onConfirm();
                }
            });
            
            // Handle cancel/close
            modalElement.addEventListener('hidden.bs.modal', function() {
                if (onCancel && typeof onCancel === 'function') {
                    onCancel();
                }
                this.remove();
            });
            
            modal.show();
        }
        
        /**
         * SweetAlert2 confirmation helper. Returns Promise<boolean>.
         * Falls back to the native confirm() when SweetAlert is unavailable.
         */
        function swalConfirm(message, options) {
            options = options || {};
            if (typeof Swal === 'undefined') {
                return Promise.resolve(window.confirm(message));
            }
            return Swal.fire({
                title: options.title || 'Confirm Action',
                text: message,
                icon: options.icon || 'warning',
                showCancelButton: true,
                confirmButtonText: options.confirmText || 'Confirm',
                cancelButtonText: options.cancelText || 'Cancel',
                confirmButtonColor: options.confirmColor || '#dc3545',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: options.focusCancel !== false
            }).then(function(result) {
                return !!(result && result.isConfirmed);
            });
        }

        // Helper function for delete confirmations with callback (SweetAlert2)
        function confirmDelete(message, onConfirm, onCancel) {
            swalConfirm(message || 'Are you sure you want to delete this item? This action cannot be undone.', {
                title: 'Confirm Delete',
                confirmText: 'Delete'
            }).then(function(confirmed) {
                if (confirmed) {
                    if (typeof onConfirm === 'function') { onConfirm(); }
                } else if (typeof onCancel === 'function') {
                    onCancel();
                }
            });
        }

        // Confirm helper used for inline confirm() links. Returns Promise<boolean>.
        function dashliteConfirm(message, title) {
            return swalConfirm(message, { title: title || 'Confirm Action' });
        }
        
        // Make functions globally available
        window.showConfirmModal = showConfirmModal;
        window.confirmDelete = confirmDelete;
        window.dashliteConfirm = dashliteConfirm;
        window.swalConfirm = swalConfirm;
        
        // Replace inline confirm() links with SweetAlert2. Uses event delegation so
        // rows rendered later (e.g. DataTables pagination) are covered too.
        document.addEventListener('click', function(e) {
            var link = e.target && e.target.closest ? e.target.closest('a[onclick]') : null;
            if (!link) {
                return;
            }

            var originalOnclick = link.getAttribute('onclick') || '';
            if (originalOnclick.indexOf('confirm(') === -1) {
                return;
            }

            var match = originalOnclick.match(/confirm\(\s*(?:'([^']*)'|"([^"]*)")\s*\)/);
            if (!match) {
                return;
            }

            var message = (match[1] !== undefined ? match[1] : match[2]) || 'Are you sure you want to proceed?';
            var href = link.getAttribute('href');

            // Cancel the inline onclick + navigation, then ask via SweetAlert.
            e.preventDefault();
            e.stopPropagation();

            dashliteConfirm(message, 'Confirm Action').then(function(confirmed) {
                if (confirmed && href && href !== '#') {
                    window.location.href = href;
                }
            });
        }, true);

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTables on all tables with pagination
            if (typeof $.fn.DataTable !== 'undefined') {
                // Initialize on all tables that are inside table-responsive or card-inner
                $('.table-responsive table.table, .card-inner table.table, .table.table-hover, .table.table-sm').each(function() {
                    // Skip if already initialized
                    if ($.fn.DataTable.isDataTable(this)) {
                        return;
                    }
                    
                    // Skip tables that are inside modals or have specific classes that shouldn't be paginated
                    if ($(this).closest('.modal').length > 0 || $(this).hasClass('no-datatables')) {
                        return;
                    }
                    
                    // Get the table element
                    const $table = $(this);
                    
                    // Check if table has thead (required for DataTables)
                    if ($table.find('thead').length === 0) {
                        return;
                    }
                    
                    // Skip tables that are key-value display tables (they have th in tbody rows)
                    if ($table.find('tbody tr th').length > 0) {
                        return;
                    }
                    
                    // Check if thead has proper structure (at least one th)
                    const theadThs = $table.find('thead th');
                    if (theadThs.length === 0) {
                        return;
                    }
                    
                    // Check if tbody has rows (skip if empty or only has colspan rows)
                    const tbodyRows = $table.find('tbody tr');
                    if (tbodyRows.length === 0) {
                        return;
                    }
                    
                    // Check for problematic rows with colspan in tbody (tfoot is okay)
                    let hasColspanRowsInTbody = false;
                    tbodyRows.each(function() {
                        if ($(this).find('td[colspan]').length > 0 || $(this).find('th[colspan]').length > 0) {
                            hasColspanRowsInTbody = true;
                            return false;
                        }
                    });
                    
                    // If table has colspan rows in tbody, skip it to avoid errors
                    // (tfoot with colspan is fine and will be handled by DataTables)
                    if (hasColspanRowsInTbody) {
                        return;
                    }
                    
                    // Determine default sort column (try to find ID column first, otherwise first column)
                    let defaultSortColumn = 0;
                    theadThs.each(function(index) {
                        const headerText = $(this).text().toLowerCase();
                        if (headerText.includes('id') || headerText.includes('#')) {
                            defaultSortColumn = index;
                            return false;
                        }
                    });
                    
                    // Initialize DataTables with error handling
                    try {
                        const fitWidth = $table.hasClass('dt-fit-width');
                        $table.DataTable({
                            pageLength: 10,
                            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                            order: [[defaultSortColumn, 'desc']],
                            autoWidth: !fitWidth,
                            language: {
                                search: "Search:",
                                lengthMenu: "Show _MENU_ entries",
                                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                                infoEmpty: "Showing 0 to 0 of 0 entries",
                                infoFiltered: "(filtered from _MAX_ total entries)",
                                paginate: {
                                    first: "First",
                                    last: "Last",
                                    next: "Next",
                                    previous: "Previous"
                                },
                                emptyTable: "No data available in table"
                            },
                            responsive: !fitWidth,
                            scrollX: false,
                            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                            drawCallback: function() {
                                // Re-initialize Bootstrap tooltips after table redraw
                                if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                                    $('[data-bs-toggle="tooltip"]').each(function() {
                                        const tooltipInstance = bootstrap.Tooltip.getInstance(this);
                                        if (tooltipInstance) {
                                            tooltipInstance.dispose();
                                        }
                                        new bootstrap.Tooltip(this);
                                    });
                                }
                            }
                        });
                    } catch (e) {
                        // Silently fail if DataTables can't initialize on this table
                        console.warn('DataTables initialization failed for table:', $table[0], e);
                    }
                });
            }
        });
    </script>
    <?php
    $poll_admin_id = $this->session->userdata('admin_id');
    if ($poll_admin_id && method_exists($this->Admin_model, 'has_permission') && $this->Admin_model->has_permission($poll_admin_id, 'view_inquiries')):
    ?>
    <script>window.INQUIRY_POLL_URL = <?php echo json_encode(base_url('inquiries/poll')); ?>;</script>
    <script src="<?php echo base_url('assets/js/inquiry-poll.js'); ?>?v=<?php echo @filemtime(FCPATH . 'assets/js/inquiry-poll.js') ?: time(); ?>"></script>
    <?php endif; ?>
    <script src="<?php echo base_url('assets/js/admin-mobile.js'); ?>?v=<?php echo time(); ?>"></script>
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register(<?php echo json_encode(base_url('sw.js')); ?>, { scope: <?php echo json_encode(rtrim(base_url(), '/') . '/'); ?> })
                .catch(function (err) { console.warn('SW registration failed:', err); });
        });
    }
    </script>
</body>
</html>
