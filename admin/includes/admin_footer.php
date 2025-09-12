        </div> <!-- Close enterprise-main -->
    </div> <!-- Close enterprise-layout -->

    <!-- Enterprise JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>
    
    <!-- Chart Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.0/dist/apexcharts.min.js"></script>
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    
    <!-- UI Enhancement Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js@1.12.0/src/toastify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Force Sidebar Visibility Script -->
    <script>
        // ULTRA AGGRESSIVE sidebar visibility fix
        (function() {
            function forceSidebarVisible() {
                const sidebar = document.querySelector('.enterprise-sidebar');
                const content = document.querySelector('.enterprise-content');
                
                if (sidebar) {
                    // Remove any classes that might hide the sidebar
                    sidebar.classList.remove('enterprise-collapsed', 'hidden', 'd-none');
                    
                    // Force all styles
                    sidebar.style.cssText = `
                        display: block !important;
                        visibility: visible !important;
                        transform: translateX(0) !important;
                        position: fixed !important;
                        left: 0 !important;
                        top: 0 !important;
                        width: 320px !important;
                        height: 100vh !important;
                        z-index: 9999 !important;
                        opacity: 1 !important;
                        pointer-events: auto !important;
                        background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%) !important;
                    `;
                    
                    console.log('ULTRA AGGRESSIVE: Sidebar forced visible');
                    console.log('Sidebar element:', sidebar);
                    console.log('Sidebar computed styles:', window.getComputedStyle(sidebar));
                } else {
                    console.error('Sidebar element not found!');
                }
                
                if (content) {
                    content.style.marginLeft = '320px';
                    content.style.marginLeft = '320px !important';
                }
            }
            
            // Run immediately
            forceSidebarVisible();
            
            // Run when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', forceSidebarVisible);
            } else {
                forceSidebarVisible();
            }
            
            // Run when window loads
            window.addEventListener('load', forceSidebarVisible);
            
            // Run every 100ms for the first 5 seconds
            let attempts = 0;
            const interval = setInterval(() => {
                forceSidebarVisible();
                attempts++;
                if (attempts >= 50) { // 5 seconds
                    clearInterval(interval);
                }
            }, 100);
        })();
    </script>

    <!-- Enterprise Admin System JavaScript -->
    <script>
        // Enterprise Admin System Core
        class EnterpriseAdminSystem {
            constructor() {
                this.initializeSystem();
                this.setupEventListeners();
                this.startRealTimeUpdates();
            }

            initializeSystem() {
                // Initialize DataTables with enterprise styling
                this.initializeDataTables();
                
                // Initialize charts
                this.initializeCharts();
                
                // Initialize form validation
                this.initializeFormValidation();
                
                // Initialize real-time search
                this.initializeRealTimeSearch();
                
                // Initialize keyboard shortcuts
                this.initializeKeyboardShortcuts();
                
                // Initialize auto-refresh
                this.initializeAutoRefresh();
            }

            initializeDataTables() {
                $('.enterprise-datatable').DataTable({
                    responsive: true,
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
                    },
                    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                         '<"row"<"col-sm-12"tr>>' +
                         '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                    buttons: [
                        {
                            extend: 'copy',
                            className: 'enterprise-btn enterprise-secondary'
                        },
                        {
                            extend: 'csv',
                            className: 'enterprise-btn enterprise-secondary'
                        },
                        {
                            extend: 'excel',
                            className: 'enterprise-btn enterprise-secondary'
                        },
                        {
                            extend: 'pdf',
                            className: 'enterprise-btn enterprise-secondary'
                        },
                        {
                            extend: 'print',
                            className: 'enterprise-btn enterprise-secondary'
                        }
                    ],
                    select: true,
                    pageLength: 25,
                    order: [[0, 'desc']]
                });
            }

            initializeCharts() {
                // Initialize ApexCharts with enterprise theme
                Apex.theme = {
                    mode: 'light',
                    palette: 'palette1',
                    monochrome: {
                        enabled: false,
                        color: '#255aee',
                        shadeTo: 'light',
                        shadeIntensity: 0.65
                    }
                };
            }

            initializeFormValidation() {
                // Bootstrap form validation
                const forms = document.querySelectorAll('.needs-validation');
                Array.from(forms).forEach(form => {
                    form.addEventListener('submit', event => {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }

            initializeRealTimeSearch() {
                // Global search functionality
                $('#globalSearch').on('input', function() {
                    const searchTerm = $(this).val().toLowerCase();
                    $('.enterprise-card').each(function() {
                        const cardText = $(this).text().toLowerCase();
                        if (cardText.includes(searchTerm)) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                });
            }

            initializeKeyboardShortcuts() {
                // Keyboard shortcuts
                $(document).on('keydown', function(e) {
                    // Ctrl+K for global search
                    if (e.ctrlKey && e.key === 'k') {
                        e.preventDefault();
                        $('#globalSearch').focus();
                    }
                    
                    // Ctrl+B for sidebar toggle
                    if (e.ctrlKey && e.key === 'b') {
                        e.preventDefault();
                        toggleSidebar();
                    }
                    
                    // Escape to close modals
                    if (e.key === 'Escape') {
                        $('.modal').modal('hide');
                    }
                });
            }

            initializeAutoRefresh() {
                // Auto-refresh dashboard data every 30 seconds
                if (window.location.pathname.includes('dashboard')) {
                    setInterval(() => {
                        this.refreshDashboardData();
                    }, 30000);
                }
            }

            setupEventListeners() {
                // Sidebar toggle
                $('#sidebarToggle').on('click', function() {
                    toggleSidebar();
                });

                // Notification system
                this.setupNotificationSystem();
                
                // Loading states
                this.setupLoadingStates();
                
                // Confirmation dialogs
                this.setupConfirmationDialogs();
            }

            setupNotificationSystem() {
                // Success notifications
                window.showSuccessNotification = function(message) {
                    Toastify({
                        text: message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#10b981",
                        stopOnFocus: true
                    }).showToast();
                };

                // Error notifications
                window.showErrorNotification = function(message) {
                    Toastify({
                        text: message,
                        duration: 5000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#ef4444",
                        stopOnFocus: true
                    }).showToast();
                };

                // Warning notifications
                window.showWarningNotification = function(message) {
                    Toastify({
                        text: message,
                        duration: 4000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#f59e0b",
                        stopOnFocus: true
                    }).showToast();
                };
            }

            setupLoadingStates() {
                // Show loading state
                window.showLoading = function(element) {
                    $(element).addClass('enterprise-loading');
                    $(element).append('<div class="enterprise-loading-spinner"></div>');
                };

                // Hide loading state
                window.hideLoading = function(element) {
                    $(element).removeClass('enterprise-loading');
                    $(element).find('.enterprise-loading-spinner').remove();
                };
            }

            setupConfirmationDialogs() {
                // Confirmation dialog
                window.showConfirmation = function(title, message, callback) {
                    Swal.fire({
                        title: title,
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#2563eb',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Oui, continuer',
                        cancelButtonText: 'Annuler'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            callback();
                        }
                    });
                };

                // Delete confirmation
                window.showDeleteConfirmation = function(itemName, callback) {
                    Swal.fire({
                        title: 'Êtes-vous sûr ?',
                        text: `Voulez-vous vraiment supprimer "${itemName}" ? Cette action est irréversible.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Oui, supprimer',
                        cancelButtonText: 'Annuler'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            callback();
                        }
                    });
                };
            }

            startRealTimeUpdates() {
                // Real-time data updates
                setInterval(() => {
                    this.updateLiveIndicators();
                }, 5000);
            }

            updateLiveIndicators() {
                // Update live indicators
                $('.enterprise-live-indicator').each(function() {
                    $(this).toggleClass('enterprise-pulse');
                });
            }

            refreshDashboardData() {
                // Refresh dashboard statistics
                $.ajax({
                    url: 'dashboard_ajax.php',
                    method: 'GET',
                    success: function(data) {
                        if (data.success) {
                            // Update dashboard stats
                            $('.enterprise-stat-number').each(function() {
                                const statId = $(this).data('stat-id');
                                if (data.stats[statId]) {
                                    $(this).text(data.stats[statId]);
                                }
                            });
                        }
                    }
                });
            }
        }

        // Utility Functions
        function toggleSidebar() {
            const sidebar = $('.enterprise-sidebar');
            const content = $('.enterprise-content');
            
            if (sidebar.hasClass('enterprise-collapsed')) {
                // Show sidebar
                sidebar.removeClass('enterprise-collapsed').show();
                content.css('margin-left', '320px');
            } else {
                // Hide sidebar
                sidebar.addClass('enterprise-collapsed');
                content.css('margin-left', '0');
            }
        }

        function refreshData() {
            location.reload();
        }

        function exportTable(tableId, format) {
            const table = $(tableId).DataTable();
            switch(format) {
                case 'csv':
                    table.button('.buttons-csv').trigger();
                    break;
                case 'excel':
                    table.button('.buttons-excel').trigger();
                    break;
                case 'pdf':
                    table.button('.buttons-pdf').trigger();
                    break;
                case 'print':
                    table.button('.buttons-print').trigger();
                    break;
            }
        }

        function submitFormAjax(formId, successCallback) {
            const form = $(formId);
            const formData = new FormData(form[0]);
            
            showLoading(form);
            
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoading(form);
                    if (response.success) {
                        showSuccessNotification(response.message);
                        if (successCallback) successCallback(response);
                    } else {
                        showErrorNotification(response.message);
                    }
                },
                error: function() {
                    hideLoading(form);
                    showErrorNotification('Une erreur est survenue. Veuillez réessayer.');
                }
            });
        }

        // Initialize Enterprise Admin System when DOM is ready
        $(document).ready(function() {
            // Ensure sidebar is visible on page load
            $('.enterprise-sidebar').removeClass('enterprise-collapsed').show();
            $('.enterprise-content').css('margin-left', '320px');
            
            // Force sidebar visibility with all properties
            $('.enterprise-sidebar').css({
                'transform': 'translateX(0)',
                'display': 'block',
                'visibility': 'visible',
                'position': 'fixed',
                'left': '0',
                'top': '0',
                'width': '320px',
                'height': '100vh',
                'z-index': '1050'
            });
            
            // Debug: Log sidebar status
            console.log('Sidebar element found:', $('.enterprise-sidebar').length);
            console.log('Sidebar display:', $('.enterprise-sidebar').css('display'));
            console.log('Sidebar transform:', $('.enterprise-sidebar').css('transform'));
            
            new EnterpriseAdminSystem();
            
            // Initialize Select2
            $('.enterprise-select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
            
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Auto-hide alerts
            $('.alert-dismissible').delay(5000).fadeOut();
            
            // Smooth scrolling
            $('a[href^="#"]').on('click', function(event) {
                const target = $(this.getAttribute('href'));
                if (target.length) {
                    event.preventDefault();
                    $('html, body').stop().animate({
                        scrollTop: target.offset().top - 100
                    }, 1000);
                }
            });
        });

        // Mobile sidebar toggle
        function toggleMobileSidebar() {
            $('.enterprise-sidebar').toggleClass('enterprise-mobile-show');
        }

        // Close mobile sidebar when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.enterprise-sidebar, .enterprise-sidebar-toggle').length) {
                $('.enterprise-sidebar').removeClass('enterprise-mobile-show');
            }
        });
        
        // Ensure sidebar is visible after window load
        $(window).on('load', function() {
            $('.enterprise-sidebar').css({
                'transform': 'translateX(0)',
                'display': 'block',
                'visibility': 'visible',
                'position': 'fixed',
                'left': '0',
                'top': '0',
                'width': '320px',
                'height': '100vh',
                'z-index': '1050'
            });
            $('.enterprise-content').css('margin-left', '320px');
            
            // Debug: Log sidebar status after window load
            console.log('Window loaded - Sidebar element found:', $('.enterprise-sidebar').length);
            console.log('Window loaded - Sidebar display:', $('.enterprise-sidebar').css('display'));
        });
    </script>

    <!-- Enterprise Loading Spinner CSS -->
    <style>
        .enterprise-loading {
            position: relative;
            pointer-events: none;
            opacity: 0.7;
        }

        .enterprise-loading-spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            border: 4px solid #e5e7eb;
            border-top: 4px solid #2563eb;
            border-radius: 50%;
            animation: enterprise-spin 1s linear infinite;
            z-index: 1000;
        }

        @keyframes enterprise-spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        .enterprise-live-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            margin-right: 8px;
        }

        .enterprise-pulse {
            animation: enterprise-pulse 2s infinite;
        }

        @keyframes enterprise-pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
            100% { opacity: 1; transform: scale(1); }
        }

        /* Ensure sidebar is visible by default - ULTRA AGGRESSIVE */
        .enterprise-sidebar {
            transform: translateX(0) !important;
            display: block !important;
            visibility: visible !important;
            position: fixed !important;
            left: 0 !important;
            top: 0 !important;
            width: 320px !important;
            height: 100vh !important;
            z-index: 9999 !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }
        
        /* Override any conflicting styles */
        nav.enterprise-sidebar {
            transform: translateX(0) !important;
            display: block !important;
            visibility: visible !important;
            position: fixed !important;
            left: 0 !important;
            top: 0 !important;
            width: 320px !important;
            height: 100vh !important;
            z-index: 9999 !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }
        
        /* Sidebar collapsed state */
        .enterprise-sidebar.enterprise-collapsed {
            transform: translateX(-100%) !important;
        }
        
        /* Ensure main content has proper margin for sidebar */
        .enterprise-content {
            margin-left: 320px !important;
            transition: margin-left 0.3s ease;
        }
        
        /* Force sidebar visibility on larger screens - ULTRA AGGRESSIVE */
        @media (min-width: 769px) {
            .enterprise-sidebar {
                transform: translateX(0) !important;
                display: block !important;
                visibility: visible !important;
                position: fixed !important;
                left: 0 !important;
                top: 0 !important;
                width: 320px !important;
                height: 100vh !important;
                z-index: 9999 !important;
                opacity: 1 !important;
                pointer-events: auto !important;
            }
            
            nav.enterprise-sidebar {
                transform: translateX(0) !important;
                display: block !important;
                visibility: visible !important;
                position: fixed !important;
                left: 0 !important;
                top: 0 !important;
                width: 320px !important;
                height: 100vh !important;
                z-index: 9999 !important;
                opacity: 1 !important;
                pointer-events: auto !important;
            }
            
            .enterprise-content {
                margin-left: 320px !important;
            }
        }
        
        /* Force sidebar visibility on all screens except mobile */
        @media (min-width: 1025px) {
            .enterprise-sidebar {
                transform: translateX(0) !important;
                display: block !important;
                visibility: visible !important;
                position: fixed !important;
                left: 0 !important;
                top: 0 !important;
                width: 320px !important;
                height: 100vh !important;
                z-index: 1050 !important;
            }
            
            .enterprise-content {
                margin-left: 320px !important;
            }
        }
        
        /* Responsive fixes */
        @media (max-width: 768px) {
            .enterprise-sidebar {
                transform: translateX(-100%) !important;
                transition: transform 0.3s ease;
            }
            
            .enterprise-sidebar.enterprise-mobile-show {
                transform: translateX(0) !important;
            }
            
            .enterprise-content {
                margin-left: 0 !important;
            }
        }

        /* Fix for content zooming out */
        .enterprise-layout {
            min-height: 100vh;
            overflow-x: hidden;
        }

        .enterprise-main {
            min-height: 100vh;
            overflow-x: auto;
        }

        .enterprise-content {
            max-width: 100%;
            overflow-x: hidden;
        }

        /* Enterprise button enhancements */
        .enterprise-btn {
            transition: all 0.3s ease;
            border-radius: 8px;
            font-weight: 500;
            text-transform: none;
            letter-spacing: 0.025em;
        }

        .enterprise-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .enterprise-btn:active {
            transform: translateY(0);
        }

        /* Enterprise card enhancements */
        .enterprise-card {
            transition: all 0.3s ease;
            border-radius: 12px;
            border: 1px solid var(--enterprise-neutral-200);
            background: var(--enterprise-bg-primary);
        }

        .enterprise-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--enterprise-shadow-lg);
        }

        /* Enterprise table enhancements */
        .enterprise-table {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--enterprise-shadow-sm);
        }

        .enterprise-table thead th {
            background: var(--enterprise-neutral-50);
            border-bottom: 2px solid var(--enterprise-neutral-200);
            font-weight: 600;
            color: var(--enterprise-text-primary);
        }

        .enterprise-table tbody tr:hover {
            background: var(--enterprise-neutral-50);
        }
    </style>
</body>
</html>
