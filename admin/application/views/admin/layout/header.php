<?php
// Ensure URL helper is loaded before using base_url()
if (!function_exists('base_url') && isset($this) && is_object($this) && method_exists($this, 'load')) {
    $this->load->helper('url');
}
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo isset($title) ? $title . ' - ' : ''; ?>Admin Panel - BODARE Pension House</title>
    
    <!-- Dashlite CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <!-- Dashlite Style -->
    <style>
        :root {
            --bs-primary: #6576ff;
            --bs-secondary: #8b5cf6;
            --bs-success: #10b981;
            --bs-info: #06b6d4;
            --bs-warning: #f59e0b;
            --bs-danger: #ef4444;
            --bs-light: #f8f9fa;
            --bs-dark: #1e293b;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --header-height: 70px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #526484;
            background-color: #f1f5f9;
            overflow-x: hidden;
        }
        
        /* Sidebar Styles */
        .nk-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: #fff;
            box-shadow: 0 0 40px rgba(0, 0, 0, 0.08);
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        .nk-sidebar::-webkit-scrollbar {
            width: 4px;
        }
        
        .nk-sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .nk-sidebar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 2px;
        }
        
        .nk-sidebar-brand {
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .nk-sidebar-brand a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #1e293b;
            font-weight: 600;
            font-size: 1.25rem;
        }
        
        .nk-sidebar-brand a i {
            font-size: 1.5rem;
            color: var(--bs-primary);
            margin-right: 0.5rem;
        }
        
        .nk-menu {
            padding: 1rem 0;
        }
        
        .nk-menu-item {
            margin: 0.25rem 0.75rem;
        }
        
        .nk-menu-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #526484;
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nk-menu-link:hover {
            background-color: #f1f5f9;
            color: var(--bs-primary);
        }
        
        .nk-menu-link.active {
            background-color: rgba(101, 118, 255, 0.1);
            color: var(--bs-primary);
            font-weight: 500;
        }
        
        .nk-menu-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            background: var(--bs-primary);
            border-radius: 0 2px 2px 0;
        }
        
        .nk-menu-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }
        
        .nk-menu-text {
            flex: 1;
        }
        
        .nk-menu-badge {
            padding: 0.25rem 0.5rem;
            background: var(--bs-primary);
            color: #fff;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        /* Submenu Styles */
        .nk-menu-item.has-submenu {
            position: relative;
        }
        
        .nk-menu-sub {
            padding-left: 0;
            margin-top: 0.25rem;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .nk-menu-item.has-submenu.active .nk-menu-sub {
            max-height: 500px;
        }
        
        .nk-menu-sub-item {
            margin: 0.25rem 0.75rem 0.25rem 2.5rem;
        }
        
        .nk-menu-sub-link {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            color: #64748b;
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .nk-menu-sub-link:hover {
            background-color: #f1f5f9;
            color: var(--bs-primary);
        }
        
        .nk-menu-sub-link.active {
            background-color: rgba(101, 118, 255, 0.1);
            color: var(--bs-primary);
            font-weight: 500;
        }
        
        .nk-menu-toggle {
            margin-left: auto;
            transition: transform 0.3s ease;
        }
        
        .nk-menu-item.has-submenu.active .nk-menu-toggle {
            transform: rotate(90deg);
        }
        
        /* Header Styles */
        .nk-header {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--header-height);
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            transition: all 0.3s ease;
        }
        
        .nk-header-brand {
            display: none;
        }
        
        .nk-header-tools {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-left: auto;
        }
        
        .nk-header-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff8c00 0%, #ff6b00 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .user-avatar:hover {
            border-color: #ff8c00;
            transform: scale(1.05);
        }
        
        .user-dropdown {
            position: relative;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
            padding: 0.5rem 0;
            margin-top: 0.5rem;
            min-width: 200px;
        }
        
        .dropdown-item {
            padding: 0.75rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #526484;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background-color: #f1f5f9;
            color: var(--bs-primary);
        }
        
        .dropdown-item i {
            width: 20px;
            font-size: 1.1rem;
        }
        
        .dropdown-divider {
            margin: 0.5rem 0;
            border-top: 1px solid #e2e8f0;
        }
        
        .dark-mode-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }
        
        .form-check-input {
            cursor: pointer;
        }
        
        /* Dark Mode Styles */
        body.dark-mode {
            background-color: #0f172a;
            color: #e2e8f0;
        }
        
        body.dark-mode .nk-sidebar {
            background: #1e293b;
            box-shadow: 0 0 40px rgba(0, 0, 0, 0.3);
        }
        
        body.dark-mode .nk-sidebar-brand {
            border-bottom-color: #334155;
        }
        
        body.dark-mode .nk-menu-link {
            color: #cbd5e1;
        }
        
        body.dark-mode .nk-menu-link:hover {
            background-color: #334155;
            color: var(--bs-primary);
        }
        
        body.dark-mode .nk-menu-link.active {
            background-color: rgba(101, 118, 255, 0.2);
        }
        
        body.dark-mode .nk-header {
            background: #1e293b;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }
        
        body.dark-mode .nk-header-title {
            color: #e2e8f0;
        }
        
        body.dark-mode .nk-content {
            background-color: #0f172a;
        }
        
        body.dark-mode .card {
            background: #1e293b;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }
        
        body.dark-mode .card-header {
            border-bottom-color: #334155;
            color: #e2e8f0;
        }
        
        body.dark-mode .content-card {
            background: #1e293b;
        }
        
        body.dark-mode .table {
            color: #e2e8f0;
            background-color: #1e293b;
        }
        
        body.dark-mode .table thead {
            background: linear-gradient(180deg, #334155 0%, #1e293b 100%);
        }
        
        body.dark-mode .table thead th {
            border-bottom-color: #475569;
            color: #cbd5e1;
            background: transparent;
        }
        
        body.dark-mode .table tbody {
            background-color: #1e293b;
        }
        
        body.dark-mode .table tbody td {
            border-top-color: #334155;
            color: #cbd5e1;
        }
        
        body.dark-mode .table tbody tr:hover {
            background-color: #334155;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }
        
        body.dark-mode .table tbody tr:hover td {
            color: #e2e8f0;
        }
        
        body.dark-mode .table-striped tbody tr:nth-of-type(odd) {
            background-color: #0f172a;
        }
        
        body.dark-mode .table-striped tbody tr:nth-of-type(odd):hover {
            background-color: #334155;
        }
        
        body.dark-mode .table-bordered {
            border-color: #334155;
        }
        
        body.dark-mode .table-bordered thead th {
            border-color: #334155;
        }
        
        body.dark-mode .table-bordered tbody td {
            border-color: #334155;
        }
        
        body.dark-mode .table-responsive {
            background-color: #1e293b;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }
        
        body.dark-mode .table code {
            background-color: #334155;
            color: #a5b4fc;
        }
        
        body.dark-mode .table tbody tr td.text-center.text-muted {
            color: #64748b;
        }
        
        /* Enhanced Button Styles in Dark Mode */
        body.dark-mode .btn {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }
        
        body.dark-mode .btn:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
        }
        
        body.dark-mode .btn-light {
            background: #334155;
            color: #e2e8f0;
            border-color: #475569;
        }
        
        body.dark-mode .btn-light:hover {
            background: #475569;
            color: #fff;
            border-color: #64748b;
        }
        
        body.dark-mode .btn-outline-primary {
            border-color: var(--bs-primary);
            color: var(--bs-primary);
        }
        
        body.dark-mode .btn-outline-primary:hover {
            background: var(--bs-primary);
            color: #fff;
        }
        
        body.dark-mode .table .btn-sm {
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        
        body.dark-mode .table .btn-sm:hover {
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
        }
        
        body.dark-mode .btn:focus {
            box-shadow: 0 0 0 3px rgba(101, 118, 255, 0.4);
        }
        
        body.dark-mode .dropdown-menu {
            background: #1e293b;
            border: 1px solid #334155;
        }
        
        body.dark-mode .dropdown-item {
            color: #cbd5e1;
        }
        
        body.dark-mode .dropdown-item:hover {
            background-color: #334155;
            color: var(--bs-primary);
        }
        
        body.dark-mode .dropdown-divider {
            border-top-color: #334155;
        }
        
        body.dark-mode .text-muted {
            color: #94a3b8 !important;
        }
        
        body.dark-mode .alert {
            background-color: #1e293b;
            border: 1px solid #334155;
            color: #e2e8f0;
        }
        
        body.dark-mode .stat-card {
            background: #1e293b;
            border-color: #334155;
        }
        
        body.dark-mode .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
        }
        
        body.dark-mode .stat-card-icon {
            background: rgba(101, 118, 255, 0.15);
        }
        
        body.dark-mode .stat-card.warning .stat-card-icon {
            background: rgba(245, 158, 11, 0.15);
        }
        
        body.dark-mode .stat-card.secondary .stat-card-icon {
            background: rgba(16, 185, 129, 0.15);
        }
        
        body.dark-mode .stat-card.info .stat-card-icon {
            background: rgba(6, 182, 212, 0.15);
        }
        
        body.dark-mode .stat-card-value {
            color: #e2e8f0;
        }
        
        body.dark-mode .stat-card-label {
            color: #94a3b8;
        }
        
        body.dark-mode code {
            background-color: #334155;
            color: #a5b4fc;
        }
        
        body.dark-mode .table thead th {
            background-color: #334155;
        }
        
        /* DataTables Dark Mode Support */
        body.dark-mode .dataTables_wrapper .dataTables_length,
        body.dark-mode .dataTables_wrapper .dataTables_filter,
        body.dark-mode .dataTables_wrapper .dataTables_info,
        body.dark-mode .dataTables_wrapper .dataTables_paginate {
            color: #cbd5e1;
        }
        
        body.dark-mode .dataTables_wrapper .dataTables_length select,
        body.dark-mode .dataTables_wrapper .dataTables_filter input {
            background-color: #1e293b;
            border-color: #334155;
            color: #e2e8f0;
        }
        
        body.dark-mode .dataTables_wrapper .dataTables_length select:focus,
        body.dark-mode .dataTables_wrapper .dataTables_filter input:focus {
            background-color: #1e293b;
            border-color: var(--bs-primary);
            color: #e2e8f0;
        }
        
        body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #cbd5e1 !important;
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }
        
        body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background-color: #334155 !important;
            border-color: var(--bs-primary) !important;
            color: var(--bs-primary) !important;
        }
        
        body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-secondary) 100%) !important;
            border-color: var(--bs-primary) !important;
            color: #fff !important;
        }
        
        body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            color: #64748b !important;
            background-color: #0f172a !important;
            border-color: #334155 !important;
        }
        
        /* Forms Dark Mode */
        body.dark-mode .form-label {
            color: #e2e8f0;
        }
        
        body.dark-mode .form-label.small {
            color: #94a3b8;
        }
        
        body.dark-mode .form-control,
        body.dark-mode .form-select {
            background-color: #1e293b;
            border-color: #334155;
            color: #e2e8f0;
        }
        
        body.dark-mode .form-control:focus,
        body.dark-mode .form-select:focus {
            background-color: #1e293b;
            border-color: var(--bs-primary);
            color: #e2e8f0;
            box-shadow: 0 0 0 3px rgba(101, 118, 255, 0.2);
        }
        
        body.dark-mode .form-control:hover:not(:disabled):not([readonly]),
        body.dark-mode .form-select:hover:not(:disabled) {
            border-color: #475569;
        }
        
        body.dark-mode .form-control:disabled,
        body.dark-mode .form-select:disabled {
            background-color: #0f172a;
            opacity: 0.6;
        }
        
        body.dark-mode .form-control[readonly] {
            background-color: #0f172a;
        }
        
        body.dark-mode .form-text {
            color: #94a3b8;
        }
        
        body.dark-mode .input-group-text {
            background-color: #334155;
            border-color: #475569;
            color: #cbd5e1;
        }
        
        body.dark-mode .form-check-input {
            border-color: #475569;
            background-color: #1e293b;
        }
        
        body.dark-mode .form-check-input:checked {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }
        
        body.dark-mode .form-check-label {
            color: #cbd5e1;
        }
        
        body.dark-mode .alert-success {
            background-color: rgba(16, 185, 129, 0.15);
            color: #6ee7b7;
        }
        
        body.dark-mode .alert-danger {
            background-color: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
        }
        
        body.dark-mode .alert-warning {
            background-color: rgba(245, 158, 11, 0.15);
            color: #fcd34d;
        }
        
        body.dark-mode .alert-info {
            background-color: rgba(6, 182, 212, 0.15);
            color: #67e8f9;
        }
        
        body.dark-mode .alert-primary {
            background-color: rgba(101, 118, 255, 0.15);
            color: #a5b4fc;
        }
        
        body.dark-mode .form-switch .form-check-input {
            background-color: #475569;
        }
        
        body.dark-mode .form-control.is-valid {
            border-color: var(--bs-success);
        }
        
        body.dark-mode .form-control.is-invalid {
            border-color: var(--bs-danger);
        }
        
        /* Additional Components Dark Mode */
        body.dark-mode .page-link {
            background-color: #1e293b;
            border-color: #334155;
            color: #cbd5e1;
        }
        
        body.dark-mode .page-link:hover {
            background-color: #334155;
            border-color: var(--bs-primary);
            color: var(--bs-primary);
        }
        
        body.dark-mode .page-item.active .page-link {
            background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-secondary) 100%);
        }
        
        body.dark-mode .breadcrumb-item {
            color: #94a3b8;
        }
        
        body.dark-mode .breadcrumb-item.active {
            color: #e2e8f0;
        }
        
        body.dark-mode .modal-content {
            background-color: #1e293b;
            color: #e2e8f0;
            border: 1px solid #334155;
        }
        
        body.dark-mode .modal-header {
            border-bottom-color: #334155;
            background: linear-gradient(180deg, #334155 0%, #1e293b 100%);
        }
        
        body.dark-mode .modal-title {
            color: #e2e8f0;
        }
        
        body.dark-mode .modal-body {
            color: #cbd5e1;
        }
        
        body.dark-mode .modal-body h6 {
            color: #e2e8f0;
        }
        
        body.dark-mode .modal-body hr {
            border-top-color: #334155;
        }
        
        body.dark-mode .modal-footer {
            border-top-color: #334155;
            background: #334155;
        }
        
        body.dark-mode .modal-body .info-row {
            border-bottom-color: #334155;
        }
        
        body.dark-mode .modal-body .info-label {
            color: #e2e8f0;
        }
        
        body.dark-mode .modal-body .info-value {
            color: #cbd5e1;
        }
        
        body.dark-mode .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.7);
        }
        
        body.dark-mode .modal-confirm p {
            color: #94a3b8;
        }
        
        body.dark-mode .modal-header .btn-close {
            background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23cbd5e1'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") center/1em auto no-repeat;
        }
        
        body.dark-mode .list-group-item {
            background-color: #1e293b;
            border-color: #334155;
            color: #cbd5e1;
        }
        
        body.dark-mode .list-group-item:hover {
            background-color: #334155;
        }
        
        body.dark-mode .nav-tabs {
            border-bottom-color: #334155;
        }
        
        body.dark-mode .nav-tabs .nav-link {
            color: #94a3b8;
        }
        
        body.dark-mode .nav-tabs .nav-link:hover {
            color: #e2e8f0;
        }
        
        body.dark-mode .accordion-item {
            background-color: #1e293b;
            border-color: #334155;
        }
        
        body.dark-mode .accordion-button {
            background-color: #1e293b;
            color: #e2e8f0;
        }
        
        body.dark-mode .accordion-button:not(.collapsed) {
            background-color: #334155;
        }
        
        body.dark-mode .progress {
            background-color: #334155;
        }
        
        body.dark-mode .bg-light {
            background-color: #334155 !important;
        }
        
        body.dark-mode .border {
            border-color: #334155 !important;
        }
        
        body.dark-mode .text-muted {
            color: #94a3b8 !important;
        }
        
        body.dark-mode .fc-toolbar-title {
            color: #e2e8f0;
        }
        
        body.dark-mode .fc-button {
            background-color: #334155;
            border-color: #475569;
            color: #cbd5e1;
        }
        
        body.dark-mode .fc-button:hover {
            background-color: #475569;
            border-color: #64748b;
        }
        
        body.dark-mode .fc-daygrid-day {
            background-color: #1e293b;
        }
        
        body.dark-mode .fc-col-header-cell {
            background-color: #334155;
            color: #cbd5e1;
        }
        
        body.dark-mode .fc-daygrid-day-number {
            color: #cbd5e1;
        }
        
        body.dark-mode .fc-day-today {
            background-color: rgba(101, 118, 255, 0.1);
        }
        
        /* FullCalendar Dark Mode */
        body.dark-mode .fc-toolbar {
            border-bottom-color: #334155;
        }
        
        body.dark-mode .fc-button {
            background: #1e293b;
            border-color: #334155;
            color: #cbd5e1;
        }
        
        body.dark-mode .fc-button:hover {
            background: #334155;
            border-color: var(--bs-primary);
            color: var(--bs-primary);
        }
        
        body.dark-mode .fc-button-active {
            background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-secondary) 100%);
            border-color: var(--bs-primary);
            color: #fff;
        }
        
        body.dark-mode .fc-col-header-cell {
            background: linear-gradient(180deg, #334155 0%, #1e293b 100%);
            border-bottom-color: #475569;
        }
        
        body.dark-mode .fc-col-header-cell-cushion {
            color: #cbd5e1;
        }
        
        body.dark-mode .fc-daygrid-day {
            background: #1e293b;
            border-color: #334155;
        }
        
        body.dark-mode .fc-daygrid-day:hover {
            background: #334155;
        }
        
        body.dark-mode .fc-daygrid-day-number {
            color: #cbd5e1;
        }
        
        body.dark-mode .fc-day-today {
            background: rgba(101, 118, 255, 0.15) !important;
        }
        
        body.dark-mode .fc-day-today .fc-daygrid-day-number {
            color: var(--bs-primary);
        }
        
        body.dark-mode .fc-day-other .fc-daygrid-day-number {
            color: #475569;
        }
        
        body.dark-mode .fc-list-day-cushion {
            background: linear-gradient(180deg, #334155 0%, #1e293b 100%);
            color: #cbd5e1;
        }
        
        body.dark-mode .fc-list-event:hover td {
            background-color: #334155;
        }
        
        body.dark-mode .fc-timegrid-slot {
            border-color: #334155;
        }
        
        body.dark-mode .fc-timegrid-col {
            border-color: #334155;
        }
        
        body.dark-mode .fc-timegrid-axis {
            border-color: #334155;
            background: #334155;
        }
        
        body.dark-mode .fc-timegrid-axis-cushion {
            color: #94a3b8;
        }
        
        body.dark-mode .fc-scroller::-webkit-scrollbar-track {
            background: #0f172a;
        }
        
        body.dark-mode .fc-scroller::-webkit-scrollbar-thumb {
            background: #475569;
        }
        
        body.dark-mode .fc-scroller::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
        
        body.dark-mode .fc-popover {
            background: #1e293b;
            border: 1px solid #334155;
        }
        
        body.dark-mode .fc-popover-header {
            background: linear-gradient(180deg, #334155 0%, #1e293b 100%);
            border-bottom-color: #475569;
            color: #e2e8f0;
        }
        
        body.dark-mode .fc-popover-body {
            background: #1e293b;
        }
        
        body.dark-mode .availability-info {
            color: #cbd5e1;
        }
        
        body.dark-mode .availability-info strong {
            color: #e2e8f0;
        }
        
        body.dark-mode .room-details-popover h6 {
            color: #e2e8f0;
        }
        
        /* Room Calendar Dark Mode */
        body.dark-mode .card-inner h6 {
            color: #e2e8f0;
        }
        
        body.dark-mode .card-inner h6 i {
            color: var(--bs-primary);
        }
        
        body.dark-mode .text-base {
            color: #cbd5e1;
        }
        
        body.dark-mode .legend-badge {
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        
        /* Content Area */
        .nk-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 1.5rem;
            min-height: calc(100vh - var(--header-height));
            transition: all 0.3s ease;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
        }
        
        .content-card {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background: transparent;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            color: #1e293b;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Stat Cards */
        .nk-block-head {
            margin-bottom: 1.5rem;
        }
        
        .nk-block-head-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }
        
        .nk-block {
            margin-bottom: 2rem;
        }
        
        .card-bordered {
            border: 1px solid #e2e8f0;
        }
        
        /* Dashboard Stat Cards - Dashlite Style */
        .stat-card {
            padding: 1.75rem;
            border-radius: 0.75rem;
            background: #fff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--bs-primary) 0%, var(--bs-secondary) 100%);
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }
        
        .stat-card.warning::before {
            background: linear-gradient(90deg, var(--bs-warning) 0%, #f59e0b 100%);
        }
        
        .stat-card.secondary::before {
            background: linear-gradient(90deg, var(--bs-success) 0%, #10b981 100%);
        }
        
        .stat-card.info::before {
            background: linear-gradient(90deg, var(--bs-info) 0%, #06b6d4 100%);
        }
        
        .stat-card-icon {
            width: 56px;
            height: 56px;
            border-radius: 0.75rem;
            background: linear-gradient(135deg, rgba(101, 118, 255, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: var(--bs-primary);
            margin-bottom: 1rem;
        }
        
        .stat-card.warning .stat-card-icon {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.1) 100%);
            color: var(--bs-warning);
        }
        
        .stat-card.secondary .stat-card-icon {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%);
            color: var(--bs-success);
        }
        
        .stat-card.info .stat-card-icon {
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.1) 0%, rgba(8, 145, 178, 0.1) 100%);
            color: var(--bs-info);
        }
        
        .stat-card-value {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0.5rem 0;
            line-height: 1.2;
        }
        
        .stat-card-label {
            font-size: 0.875rem;
            color: #64748b;
            margin: 0;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Buttons - Dashlite Awesome Style */
        .btn {
            border-radius: 0.5rem;
            padding: 0.625rem 1.25rem;
            font-weight: 500;
            font-size: 0.875rem;
            line-height: 1.5;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            z-index: 1;
        }
        
        .btn > * {
            position: relative;
            z-index: 2;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
            z-index: 0;
        }
        
        .btn:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(101, 118, 255, 0.25);
        }
        
        .btn i {
            font-size: 1rem;
            line-height: 1;
        }
        
        /* Button Sizes */
        .btn-sm {
            padding: 0.375rem 0.875rem;
            font-size: 0.8125rem;
            border-radius: 0.375rem;
        }
        
        .btn-sm i {
            font-size: 0.875rem;
        }
        
        .btn-lg {
            padding: 0.875rem 1.75rem;
            font-size: 1rem;
            border-radius: 0.625rem;
        }
        
        .btn-lg i {
            font-size: 1.125rem;
        }
        
        /* Primary Button */
        .btn-primary {
            background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-secondary) 100%);
            color: #fff;
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5568e6 0%, #7c3aed 100%);
            color: #fff;
        }
        
        .btn-primary:active {
            background: linear-gradient(135deg, #4c5fd9 0%, #6d28d9 100%);
        }
        
        /* Secondary Button */
        .btn-secondary {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            color: #fff;
            border: none;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #475569 0%, #334155 100%);
            color: #fff;
        }
        
        /* Success Button */
        .btn-success {
            background: linear-gradient(135deg, var(--bs-success) 0%, #059669 100%);
            color: #fff;
            border: none;
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #fff;
        }
        
        /* Warning Button */
        .btn-warning {
            background: linear-gradient(135deg, var(--bs-warning) 0%, #d97706 100%);
            color: #fff;
            border: none;
        }
        
        .btn-warning:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: #fff;
        }
        
        /* Danger Button */
        .btn-danger {
            background: linear-gradient(135deg, var(--bs-danger) 0%, #dc2626 100%);
            color: #fff;
            border: none;
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: #fff;
        }
        
        /* Info Button */
        .btn-info {
            background: linear-gradient(135deg, var(--bs-info) 0%, #0891b2 100%);
            color: #fff;
            border: none;
        }
        
        .btn-info:hover {
            background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
            color: #fff;
        }
        
        /* Light Button */
        .btn-light {
            background: #fff;
            color: #526484;
            border: 1px solid #e2e8f0;
        }
        
        .btn-light:hover {
            background: #f8f9fa;
            color: #1e293b;
            border-color: #cbd5e1;
        }
        
        /* Outline Buttons */
        .btn-outline-primary {
            background: transparent;
            color: var(--bs-primary);
            border: 2px solid var(--bs-primary);
        }
        
        .btn-outline-primary:hover {
            background: var(--bs-primary);
            color: #fff;
            border-color: var(--bs-primary);
        }
        
        .btn-outline-secondary {
            background: transparent;
            color: #64748b;
            border: 2px solid #64748b;
        }
        
        .btn-outline-secondary:hover {
            background: #64748b;
            color: #fff;
            border-color: #64748b;
        }
        
        .btn-outline-success {
            background: transparent;
            color: var(--bs-success);
            border: 2px solid var(--bs-success);
        }
        
        .btn-outline-success:hover {
            background: var(--bs-success);
            color: #fff;
            border-color: var(--bs-success);
        }
        
        .btn-outline-warning {
            background: transparent;
            color: var(--bs-warning);
            border: 2px solid var(--bs-warning);
        }
        
        .btn-outline-warning:hover {
            background: var(--bs-warning);
            color: #fff;
            border-color: var(--bs-warning);
        }
        
        .btn-outline-danger {
            background: transparent;
            color: var(--bs-danger);
            border: 2px solid var(--bs-danger);
        }
        
        .btn-outline-danger:hover {
            background: var(--bs-danger);
            color: #fff;
            border-color: var(--bs-danger);
        }
        
        .btn-outline-info {
            background: transparent;
            color: var(--bs-info);
            border: 2px solid var(--bs-info);
        }
        
        .btn-outline-info:hover {
            background: var(--bs-info);
            color: #fff;
            border-color: var(--bs-info);
        }
        
        /* Disabled Buttons */
        .btn:disabled,
        .btn.disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }
        
        .btn:disabled:hover,
        .btn.disabled:hover {
            transform: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        /* Button Groups */
        .btn-group {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .btn-group .btn {
            box-shadow: none;
            border-radius: 0;
        }
        
        .btn-group .btn:first-child {
            border-top-left-radius: 0.5rem;
            border-bottom-left-radius: 0.5rem;
        }
        
        .btn-group .btn:last-child {
            border-top-right-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
        }
        
        .btn-group .btn:hover {
            z-index: 1;
        }
        
        /* Button Toolbar */
        .btn-toolbar {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            gap: 0.5rem;
        }
        
        /* Icon Button Variants */
        .btn-icon {
            width: 40px;
            height: 40px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-icon.btn-sm {
            width: 32px;
            height: 32px;
        }
        
        .btn-icon.btn-lg {
            width: 48px;
            height: 48px;
        }
        
        /* Link Buttons */
        .btn-link {
            background: transparent;
            color: var(--bs-primary);
            box-shadow: none;
            text-decoration: none;
        }
        
        .btn-link:hover {
            color: var(--bs-secondary);
            text-decoration: underline;
            transform: none;
            box-shadow: none;
        }
        
        .btn-link:focus {
            box-shadow: 0 0 0 2px rgba(101, 118, 255, 0.25);
        }
        
        /* Tables - Dashlite Awesome Style */
        .table {
            margin-bottom: 0;
            color: #526484;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background-color: #fff;
        }
        
        .table thead {
            background: linear-gradient(180deg, #f8f9fa 0%, #f1f5f9 100%);
        }
        
        .table thead th {
            border: none;
            border-bottom: 2px solid #e2e8f0;
            font-weight: 600;
            color: #1e293b;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.8px;
            padding: 1rem 1.25rem;
            background: transparent;
            position: relative;
            white-space: nowrap;
        }
        
        .table thead th:first-child {
            border-top-left-radius: 0.75rem;
        }
        
        .table thead th:last-child {
            border-top-right-radius: 0.75rem;
        }
        
        .table tbody {
            background-color: #fff;
        }
        
        .table tbody td {
            padding: 1.125rem 1.25rem;
            vertical-align: middle;
            border-top: 1px solid #e2e8f0;
            border-bottom: none;
            color: #526484;
            font-size: 0.875rem;
        }
        
        .table tbody tr {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        
        .table tbody tr:first-child td {
            border-top: none;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.001);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
        
        .table tbody tr:hover td {
            color: #1e293b;
        }
        
        .table tbody tr:last-child td:first-child {
            border-bottom-left-radius: 0.75rem;
        }
        
        .table tbody tr:last-child td:last-child {
            border-bottom-right-radius: 0.75rem;
        }
        
        /* Table Striped */
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #fafbfc;
        }
        
        .table-striped tbody tr:nth-of-type(odd):hover {
            background-color: #f1f5f9;
        }
        
        /* Table Bordered */
        .table-bordered {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            overflow: hidden;
        }
        
        .table-bordered thead th {
            border: 1px solid #e2e8f0;
        }
        
        .table-bordered tbody td {
            border: 1px solid #e2e8f0;
        }
        
        /* Table Small */
        .table-sm thead th {
            padding: 0.75rem 1rem;
            font-size: 0.7rem;
        }
        
        .table-sm tbody td {
            padding: 0.875rem 1rem;
            font-size: 0.8125rem;
        }
        
        /* Table Hover Enhanced */
        .table-hover tbody tr {
            cursor: pointer;
        }
        
        /* Table Responsive Wrapper */
        .table-responsive {
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            background-color: #fff;
            overflow: hidden;
        }
        
        .table-responsive .table {
            margin-bottom: 0;
        }
        
        /* Table Action Buttons - Enhanced */
        .table .btn-sm {
            padding: 0.375rem 0.625rem;
            font-size: 0.75rem;
            border-radius: 0.375rem;
            margin: 0 0.125rem;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            min-width: 32px;
        }
        
        .table .btn-sm:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }
        
        .table .btn-sm:active {
            transform: translateY(0);
        }
        
        .table .btn-sm i {
            font-size: 0.875rem;
            line-height: 1;
        }
        
        /* Icon-only buttons in tables */
        .table .btn-sm:not(:has(span)) {
            padding: 0.375rem;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Empty State */
        .table tbody tr td.text-center.text-muted {
            padding: 2rem 1rem;
            font-style: italic;
            color: #94a3b8;
        }
        
        /* Table Code Elements */
        .table code {
            background-color: #f1f5f9;
            color: var(--bs-primary);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.8125rem;
            font-weight: 500;
        }
        
        /* Badges - Dashlite Style */
        .badge {
            padding: 0.375rem 0.75rem;
            font-weight: 500;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            letter-spacing: 0.3px;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        /* Badge in Tables */
        .table .badge {
            font-size: 0.6875rem;
            padding: 0.25rem 0.625rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Badge Colors Enhanced */
        .badge.bg-primary {
            background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-secondary) 100%) !important;
        }
        
        .badge.bg-success {
            background: linear-gradient(135deg, var(--bs-success) 0%, #059669 100%) !important;
        }
        
        .badge.bg-warning {
            background: linear-gradient(135deg, var(--bs-warning) 0%, #d97706 100%) !important;
        }
        
        .badge.bg-danger {
            background: linear-gradient(135deg, var(--bs-danger) 0%, #dc2626 100%) !important;
        }
        
        .badge.bg-info {
            background: linear-gradient(135deg, var(--bs-info) 0%, #0891b2 100%) !important;
        }
        
        .badge.bg-secondary {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%) !important;
        }
        
        /* Dashboard Specific Styles */
        .nk-block {
            margin-bottom: 2rem;
        }
        
        .nk-block-head {
            margin-bottom: 1.5rem;
        }
        
        .nk-block-head-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }
        
        /* Code styling */
        code {
            background-color: #f1f5f9;
            color: var(--bs-primary);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        /* Forms - Dashlite Style */
        .form-label {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        
        .form-label.small {
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
        }
        
        .form-control,
        .form-select {
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
            color: #1e293b;
            background-color: #fff;
            transition: all 0.3s ease;
        }
        
        .form-control:focus,
        .form-select:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 3px rgba(101, 118, 255, 0.1);
            outline: none;
            background-color: #fff;
        }
        
        .form-control:hover:not(:disabled):not([readonly]),
        .form-select:hover:not(:disabled) {
            border-color: #cbd5e1;
        }
        
        .form-control:disabled,
        .form-select:disabled {
            background-color: #f8f9fa;
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .form-control[readonly] {
            background-color: #f8f9fa;
            cursor: default;
        }
        
        .form-text {
            font-size: 0.8125rem;
            color: #64748b;
            margin-top: 0.25rem;
        }
        
        /* Input Groups */
        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 0.875rem;
        }
        
        .input-group .form-control:first-child,
        .input-group .form-select:first-child {
            border-top-left-radius: 0.5rem;
            border-bottom-left-radius: 0.5rem;
        }
        
        .input-group .form-control:last-child,
        .input-group .form-select:last-child {
            border-top-right-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
        }
        
        /* Checkboxes and Radio Buttons */
        .form-check-input {
            width: 1.125rem;
            height: 1.125rem;
            border: 2px solid #cbd5e1;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .form-check-input:checked {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }
        
        .form-check-input:focus {
            box-shadow: 0 0 0 3px rgba(101, 118, 255, 0.1);
        }
        
        .form-check-input[type="radio"] {
            border-radius: 50%;
        }
        
        .form-check-label {
            margin-left: 0.5rem;
            color: #526484;
            font-size: 0.875rem;
            cursor: pointer;
        }
        
        .form-check {
            margin-bottom: 0.75rem;
        }
        
        /* Textarea */
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        /* Alerts - Dashlite Style */
        .alert {
            border: none;
            border-radius: 0.5rem;
            padding: 1rem 1.25rem;
            margin-bottom: 1rem;
            border-left: 4px solid;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        
        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: #047857;
            border-left-color: var(--bs-success);
        }
        
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: #b91c1c;
            border-left-color: var(--bs-danger);
        }
        
        .alert-warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: #b45309;
            border-left-color: var(--bs-warning);
        }
        
        .alert-info {
            background-color: rgba(6, 182, 212, 0.1);
            color: #0e7490;
            border-left-color: var(--bs-info);
        }
        
        .alert-primary {
            background-color: rgba(101, 118, 255, 0.1);
            color: #4c5fd9;
            border-left-color: var(--bs-primary);
        }
        
        .alert-dismissible .btn-close {
            padding: 0.75rem 1.25rem;
        }
        
        /* Content Card Enhancement */
        .content-card {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
        }
        
        /* Card Header Styles */
        .card-header {
            background: transparent;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            color: #1e293b;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .card-header i {
            font-size: 1.125rem;
            color: var(--bs-primary);
        }
        
        /* Spacing Utilities */
        .g-3 {
            --bs-gutter-x: 1rem;
            --bs-gutter-y: 1rem;
        }
        
        .g-3 > * {
            padding-right: calc(var(--bs-gutter-x) * 0.5);
            padding-left: calc(var(--bs-gutter-x) * 0.5);
            margin-top: var(--bs-gutter-y);
        }
        
        /* Badge in Forms */
        .form-control + .badge,
        .form-select + .badge {
            margin-top: 0.25rem;
        }
        
        /* Validation States */
        .form-control.is-valid {
            border-color: var(--bs-success);
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2310b981' d='m2.3 6.73.98-.98-.98-.98L1.05 4.85l.98.98-.98.98zm.89-1.01 4.25-4.25L8.22 2.8 3.97 7.05z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        
        .form-control.is-invalid {
            border-color: var(--bs-danger);
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23ef4444'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6.2.2.2-.2M6 8.2V6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        
        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: var(--bs-danger);
        }
        
        .valid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: var(--bs-success);
        }
        
        /* Select Dropdown Enhancement */
        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
            padding-right: 2.5rem;
        }
        
        /* Range Input */
        .form-range {
            height: 0.5rem;
        }
        
        .form-range::-webkit-slider-thumb {
            width: 1rem;
            height: 1rem;
            background-color: var(--bs-primary);
            border: none;
            border-radius: 50%;
        }
        
        .form-range::-moz-range-thumb {
            width: 1rem;
            height: 1rem;
            background-color: var(--bs-primary);
            border: none;
            border-radius: 50%;
        }
        
        /* File Input */
        .form-control[type="file"] {
            padding: 0.5rem;
        }
        
        .form-control[type="file"]::-webkit-file-upload-button {
            padding: 0.5rem 1rem;
            margin-right: 0.5rem;
            background: var(--bs-primary);
            color: #fff;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
            font-weight: 500;
        }
        
        .form-control[type="file"]::-webkit-file-upload-button:hover {
            background: #5568e6;
        }
        
        /* Switch Toggle */
        .form-switch .form-check-input {
            width: 2.5rem;
            height: 1.25rem;
            background-color: #cbd5e1;
            border: none;
        }
        
        .form-switch .form-check-input:checked {
            background-color: var(--bs-primary);
        }
        
        .form-switch .form-check-input:focus {
            box-shadow: 0 0 0 3px rgba(101, 118, 255, 0.1);
        }
        
        /* Pagination - Dashlite Style */
        .pagination {
            gap: 0.25rem;
        }
        
        .page-link {
            color: #526484;
            background-color: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            transition: all 0.2s ease;
        }
        
        .page-link:hover {
            color: var(--bs-primary);
            background-color: #f8f9fa;
            border-color: var(--bs-primary);
            transform: translateY(-1px);
        }
        
        .page-item.active .page-link {
            background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-secondary) 100%);
            border-color: var(--bs-primary);
            color: #fff;
        }
        
        .page-item.disabled .page-link {
            color: #94a3b8;
            background-color: #f8f9fa;
            border-color: #e2e8f0;
            cursor: not-allowed;
        }
        
        /* Breadcrumbs */
        .breadcrumb {
            background-color: transparent;
            padding: 0;
            margin-bottom: 1rem;
        }
        
        .breadcrumb-item {
            color: #64748b;
        }
        
        .breadcrumb-item a {
            color: var(--bs-primary);
            text-decoration: none;
        }
        
        .breadcrumb-item a:hover {
            text-decoration: underline;
        }
        
        .breadcrumb-item.active {
            color: #1e293b;
            font-weight: 500;
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            content: "›";
            color: #cbd5e1;
            padding: 0 0.5rem;
        }
        
        /* Modals - Dashlite Style */
        .modal {
            z-index: 1055;
        }
        
        .modal-dialog {
            margin: 1.75rem auto;
        }
        
        .modal-content {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            background: #fff;
            overflow: hidden;
        }
        
        .modal-header {
            border-bottom: 1px solid #e2e8f0;
            padding: 1.5rem;
            background: linear-gradient(180deg, #f8f9fa 0%, #fff 100%);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .modal-header .modal-title {
            font-weight: 600;
            color: #1e293b;
            font-size: 1.25rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .modal-header .modal-title i {
            color: var(--bs-primary);
            font-size: 1.375rem;
        }
        
        .modal-header .btn-close {
            margin: 0;
            padding: 0.5rem;
            opacity: 0.5;
            transition: all 0.2s ease;
        }
        
        .modal-header .btn-close:hover {
            opacity: 1;
            transform: rotate(90deg);
        }
        
        .modal-body {
            padding: 1.5rem;
            color: #526484;
            line-height: 1.6;
        }
        
        .modal-body h6 {
            color: #1e293b;
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1rem;
        }
        
        .modal-body hr {
            margin: 1rem 0;
            border-top: 1px solid #e2e8f0;
            opacity: 1;
        }
        
        .modal-footer {
            border-top: 1px solid #e2e8f0;
            padding: 1.25rem 1.5rem;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.75rem;
        }
        
        .modal-footer .btn {
            margin: 0;
        }
        
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(2px);
        }
        
        .modal-backdrop.show {
            opacity: 1;
        }
        
        /* Modal Sizes */
        .modal-dialog-centered {
            display: flex;
            align-items: center;
            min-height: calc(100% - 3.5rem);
        }
        
        .modal-sm {
            max-width: 400px;
        }
        
        .modal-lg {
            max-width: 800px;
        }
        
        .modal-xl {
            max-width: 1140px;
        }
        
        /* Modal Scroll */
        .modal-dialog-scrollable .modal-content {
            max-height: calc(100vh - 3.5rem);
        }
        
        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
        }
        
        /* Modal Fullscreen */
        .modal-fullscreen .modal-content {
            border-radius: 0;
            height: 100vh;
        }
        
        /* Modal Animation */
        .modal.fade .modal-dialog {
            transition: transform 0.3s ease-out;
            transform: translate(0, -50px);
        }
        
        .modal.show .modal-dialog {
            transform: none;
        }
        
        /* Modal Content Sections */
        .modal-body .card {
            margin-bottom: 1rem;
        }
        
        .modal-body .card:last-child {
            margin-bottom: 0;
        }
        
        /* Modal Info Display */
        .modal-body .info-row {
            display: flex;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .modal-body .info-row:last-child {
            border-bottom: none;
        }
        
        .modal-body .info-label {
            font-weight: 600;
            color: #1e293b;
            min-width: 140px;
            margin-right: 1rem;
        }
        
        .modal-body .info-value {
            color: #526484;
            flex: 1;
        }
        
        /* Modal Actions */
        .modal-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }
        
        /* Modal Icon */
        .modal-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
        }
        
        .modal-icon-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--bs-success);
        }
        
        .modal-icon-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--bs-danger);
        }
        
        .modal-icon-warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--bs-warning);
        }
        
        .modal-icon-info {
            background: rgba(6, 182, 212, 0.1);
            color: var(--bs-info);
        }
        
        .modal-icon-primary {
            background: rgba(101, 118, 255, 0.1);
            color: var(--bs-primary);
        }
        
        /* Modal Confirm Dialog Style */
        .modal-confirm {
            text-align: center;
        }
        
        .modal-confirm .modal-icon {
            margin-bottom: 1.5rem;
        }
        
        .modal-confirm .modal-title {
            margin-bottom: 0.5rem;
        }
        
        .modal-confirm .modal-body {
            padding: 2rem 1.5rem;
        }
        
        .modal-confirm p {
            margin-bottom: 0;
            color: #64748b;
        }
        
        /* Modal Form */
        .modal-form .form-group {
            margin-bottom: 1.25rem;
        }
        
        .modal-form .form-label {
            margin-bottom: 0.5rem;
        }
        
        /* Modal Table */
        .modal-body .table {
            margin-bottom: 0;
        }
        
        /* Modal List */
        .modal-body .list-group {
            margin-bottom: 0;
        }
        
        /* Modal Alert Integration */
        .modal-body .alert {
            margin-bottom: 1rem;
        }
        
        .modal-body .alert:last-child {
            margin-bottom: 0;
        }
        
        /* Modal Loading State */
        .modal-loading {
            text-align: center;
            padding: 3rem 1.5rem;
        }
        
        .modal-loading .spinner-border {
            width: 3rem;
            height: 3rem;
            margin-bottom: 1rem;
        }
        
        /* Modal Success/Error States */
        .modal-success .modal-icon {
            background: rgba(16, 185, 129, 0.1);
            color: var(--bs-success);
        }
        
        .modal-error .modal-icon {
            background: rgba(239, 68, 68, 0.1);
            color: var(--bs-danger);
        }
        
        .modal-warning .modal-icon {
            background: rgba(245, 158, 11, 0.1);
            color: var(--bs-warning);
        }
        
        /* Modal Close Button Enhancement */
        .modal-header .btn-close {
            background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23526484'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") center/1em auto no-repeat;
            opacity: 0.5;
        }
        
        .modal-header .btn-close:hover {
            opacity: 1;
        }
        
        /* Responsive Modal */
        @media (max-width: 575.98px) {
            .modal-dialog {
                margin: 0.5rem;
            }
            
            .modal-content {
                border-radius: 0.5rem;
            }
            
            .modal-header,
            .modal-body,
            .modal-footer {
                padding: 1rem;
            }
        }
        
        /* Dropdowns */
        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
            padding: 0.5rem 0;
            margin-top: 0.5rem;
        }
        
        .dropdown-item {
            padding: 0.75rem 1.25rem;
            color: #526484;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: var(--bs-primary);
        }
        
        .dropdown-item.active {
            background-color: rgba(101, 118, 255, 0.1);
            color: var(--bs-primary);
        }
        
        .dropdown-divider {
            margin: 0.5rem 0;
            border-top: 1px solid #e2e8f0;
        }
        
        /* Tooltips */
        .tooltip {
            font-size: 0.75rem;
        }
        
        .tooltip-inner {
            background-color: #1e293b;
            color: #fff;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
        }
        
        /* Popovers */
        .popover {
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
        }
        
        .popover-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            color: #1e293b;
        }
        
        /* Progress Bars */
        .progress {
            height: 0.75rem;
            background-color: #e2e8f0;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, var(--bs-primary) 0%, var(--bs-secondary) 100%);
            transition: width 0.6s ease;
        }
        
        /* Spinners */
        .spinner-border {
            border-width: 0.15em;
        }
        
        .spinner-border-primary {
            border-color: var(--bs-primary);
            border-right-color: transparent;
        }
        
        /* List Groups */
        .list-group-item {
            border: 1px solid #e2e8f0;
            padding: 0.875rem 1.25rem;
            transition: all 0.2s ease;
        }
        
        .list-group-item:hover {
            background-color: #f8f9fa;
            border-color: #cbd5e1;
        }
        
        .list-group-item.active {
            background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-secondary) 100%);
            border-color: var(--bs-primary);
            color: #fff;
        }
        
        /* Nav Tabs */
        .nav-tabs {
            border-bottom: 2px solid #e2e8f0;
        }
        
        .nav-tabs .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            color: #64748b;
            padding: 0.75rem 1.25rem;
            transition: all 0.2s ease;
        }
        
        .nav-tabs .nav-link:hover {
            border-bottom-color: #cbd5e1;
            color: #1e293b;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--bs-primary);
            border-bottom-color: var(--bs-primary);
            background-color: transparent;
        }
        
        /* Accordion */
        .accordion-item {
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .accordion-button {
            background-color: #fff;
            color: #1e293b;
            font-weight: 500;
            border: none;
        }
        
        .accordion-button:not(.collapsed) {
            background-color: #f8f9fa;
            color: var(--bs-primary);
        }
        
        .accordion-button:focus {
            box-shadow: 0 0 0 3px rgba(101, 118, 255, 0.1);
        }
        
        .accordion-body {
            padding: 1.25rem;
        }
        
        /* Close Button */
        .btn-close {
            opacity: 0.5;
            transition: opacity 0.2s ease;
        }
        
        .btn-close:hover {
            opacity: 1;
        }
        
        /* Utilities */
        .text-muted {
            color: #64748b !important;
        }
        
        .bg-light {
            background-color: #f8f9fa !important;
        }
        
        .border {
            border: 1px solid #e2e8f0 !important;
        }
        
        .rounded {
            border-radius: 0.5rem !important;
        }
        
        .shadow-sm {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08) !important;
        }
        
        .shadow {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
        }
        
        .shadow-lg {
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
        }
        
        /* Dashlite UI Components */
        .nk-block {
            margin-bottom: 2rem;
        }
        
        .nk-block-head {
            margin-bottom: 1.5rem;
        }
        
        .nk-block-between {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .nk-block-head-content {
            display: flex;
            align-items: center;
        }
        
        .nk-block-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nk-block-title.page-title {
            font-size: 1.75rem;
        }
        
        .nk-block-title i {
            color: var(--bs-primary);
            font-size: 1.5rem;
        }
        
        .nk-block-des {
            margin-top: 0.5rem;
        }
        
        .nk-block-des.text-soft {
            color: #64748b;
            font-size: 0.875rem;
        }
        
        .nk-block-des p {
            margin: 0;
        }
        
        .nk-block-tools {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .nk-block-tools li {
            margin: 0;
        }
        
        .card-inner {
            padding: 1.5rem;
        }
        
        .card-inner-group {
            padding: 1.5rem;
        }
        
        .card-inner-group + .card-inner-group {
            border-top: 1px solid #e2e8f0;
        }
        
        /* Page Header Alternative */
        .page-header {
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }
        
        .page-subtitle {
            color: #64748b;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
        
        /* Content Wrapper */
        .content-wrap {
            padding: 0;
        }
        
        /* Section Headers */
        .section-head {
            margin-bottom: 1.5rem;
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0 0 0.5rem 0;
        }
        
        .section-subtitle {
            color: #64748b;
            font-size: 0.875rem;
            margin: 0;
        }
        
        /* Divider */
        .divider {
            height: 1px;
            background: #e2e8f0;
            margin: 1.5rem 0;
        }
        
        /* Text Utilities */
        .text-soft {
            color: #64748b;
        }
        
        .text-base {
            color: #526484;
        }
        
        .text-head {
            color: #1e293b;
        }
        
        /* Spacing Helpers */
        .g-3 {
            --bs-gutter-x: 1rem;
            --bs-gutter-y: 1rem;
        }
        
        .g-3 > * {
            padding-right: calc(var(--bs-gutter-x) * 0.5);
            padding-left: calc(var(--bs-gutter-x) * 0.5);
            margin-top: var(--bs-gutter-y);
        }
        
        /* Toggle Wrap */
        .toggle-wrap {
            position: relative;
        }
        
        .toggle-expand-content {
            display: block;
        }
        
        /* Button Outline Light */
        .btn-outline-light {
            background: transparent;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }
        
        .btn-outline-light:hover {
            background: #f8f9fa;
            color: #1e293b;
            border-color: #cbd5e1;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 767px) {
            .nk-block-between {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .nk-block-head-content {
                width: 100%;
            }
            
            .nk-block-tools {
                width: 100%;
            }
            
            .nk-block-tools li {
                flex: 1;
            }
            
            .nk-block-tools .btn {
                width: 100%;
            }
        }
        
        /* Responsive */
        @media (max-width: 991px) {
            .nk-sidebar {
                transform: translateX(-100%);
            }
            
            .nk-sidebar.mobile-menu {
                transform: translateX(0);
            }
            
            .nk-header {
                left: 0;
            }
            
            .nk-content {
                margin-left: 0;
            }
        }
        
        /* FullCalendar - Dashlite Style */
        #booking-calendar,
        #availability-calendar {
            margin-top: 0;
        }
        
        /* Calendar Container */
        .fc {
            font-family: inherit;
            font-size: 0.875rem;
        }
        
        /* Calendar Toolbar */
        .fc-toolbar {
            padding: 1rem 0;
            margin-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .fc-toolbar-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }
        
        .fc-button-group {
            display: flex;
            gap: 0.5rem;
        }
        
        .fc-button {
            background: #fff;
            border: 1px solid #e2e8f0;
            color: #526484;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        
        .fc-button:hover {
            background: #f8f9fa;
            border-color: var(--bs-primary);
            color: var(--bs-primary);
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .fc-button:active,
        .fc-button:focus {
            background: #f8f9fa;
            border-color: var(--bs-primary);
            color: var(--bs-primary);
            box-shadow: 0 0 0 3px rgba(101, 118, 255, 0.1);
        }
        
        .fc-button-active {
            background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-secondary) 100%);
            border-color: var(--bs-primary);
            color: #fff;
        }
        
        .fc-button-active:hover {
            background: linear-gradient(135deg, #5568e6 0%, #7c3aed 100%);
            color: #fff;
        }
        
        .fc-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Calendar Header */
        .fc-col-header-cell {
            background: linear-gradient(180deg, #f8f9fa 0%, #f1f5f9 100%);
            border: none;
            border-bottom: 2px solid #e2e8f0;
            padding: 0.75rem 0.5rem;
        }
        
        .fc-col-header-cell-cushion {
            color: #1e293b;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-decoration: none;
        }
        
        /* Day Grid */
        .fc-daygrid-day {
            background: #fff;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }
        
        .fc-daygrid-day:hover {
            background: #f8f9fa;
        }
        
        .fc-daygrid-day-number {
            color: #526484;
            font-weight: 500;
            padding: 0.5rem;
            font-size: 0.875rem;
        }
        
        .fc-day-today {
            background: rgba(101, 118, 255, 0.05) !important;
        }
        
        .fc-day-today .fc-daygrid-day-number {
            color: var(--bs-primary);
            font-weight: 600;
        }
        
        /* Other Month Days */
        .fc-day-other .fc-daygrid-day-number {
            color: #cbd5e1;
        }
        
        /* Events */
        .fc-event {
            cursor: pointer;
            border: none;
            border-radius: 0.375rem;
            padding: 0.25rem 0.5rem;
            margin: 0.125rem 0;
            font-size: 0.75rem;
            font-weight: 500;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }
        
        .fc-event:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }
        
        .fc-event-title {
            font-weight: 500;
            padding: 0;
        }
        
        .fc-daygrid-event {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Event Colors */
        .fc-event.confirmed {
            background: linear-gradient(135deg, var(--bs-success) 0%, #059669 100%);
            color: #fff;
        }
        
        .fc-event.pending {
            background: linear-gradient(135deg, var(--bs-warning) 0%, #d97706 100%);
            color: #fff;
        }
        
        .fc-event.cancelled {
            background: linear-gradient(135deg, var(--bs-danger) 0%, #dc2626 100%);
            color: #fff;
        }
        
        /* More Link */
        .fc-more-link {
            color: var(--bs-primary);
            font-weight: 500;
            font-size: 0.75rem;
        }
        
        .fc-more-link:hover {
            text-decoration: underline;
        }
        
        /* Day Grid Event Dot */
        .fc-daygrid-event-dot {
            display: none;
        }
        
        /* List View */
        .fc-list-event:hover td {
            background-color: #f8f9fa;
        }
        
        .fc-list-day-cushion {
            background: linear-gradient(180deg, #f8f9fa 0%, #f1f5f9 100%);
            padding: 0.75rem 1rem;
            font-weight: 600;
            color: #1e293b;
        }
        
        /* Time Grid */
        .fc-timegrid-slot {
            border-color: #e2e8f0;
        }
        
        .fc-timegrid-col {
            border-color: #e2e8f0;
        }
        
        .fc-timegrid-axis {
            border-color: #e2e8f0;
            background: #f8f9fa;
        }
        
        .fc-timegrid-axis-cushion {
            color: #64748b;
            font-size: 0.75rem;
        }
        
        /* Scrollbar */
        .fc-scroller::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        .fc-scroller::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        .fc-scroller::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        
        .fc-scroller::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Popover/Tooltip */
        .fc-popover {
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
        }
        
        .fc-popover-header {
            background: linear-gradient(180deg, #f8f9fa 0%, #f1f5f9 100%);
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1rem;
            font-weight: 600;
            color: #1e293b;
        }
        
        .fc-popover-body {
            padding: 1rem;
        }
        
        /* Room Availability Styles */
        .room-availability-popover {
            max-width: 400px;
        }
        
        .availability-info {
            margin-bottom: 0.5rem;
            color: #526484;
        }
        
        .availability-info strong {
            display: inline-block;
            min-width: 120px;
            color: #1e293b;
            font-weight: 600;
        }
        
        .room-details-popover h6 {
            color: #1e293b;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        /* Room Calendar Event Classes */
        .room-event-available {
            background: linear-gradient(135deg, var(--bs-success) 0%, #059669 100%) !important;
            color: #fff !important;
            border: none !important;
        }
        
        .room-event-partial {
            background: linear-gradient(135deg, var(--bs-warning) 0%, #d97706 100%) !important;
            color: #fff !important;
            border: none !important;
        }
        
        .room-event-booked {
            background: linear-gradient(135deg, var(--bs-danger) 0%, #dc2626 100%) !important;
            color: #fff !important;
            border: none !important;
        }
        
        .room-event-inactive {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%) !important;
            color: #fff !important;
            border: none !important;
        }
        
        /* Legend Badges */
        .legend-badge {
            width: 32px;
            height: 20px;
            border-radius: 0.375rem;
            display: inline-block;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .legend-success {
            background: linear-gradient(135deg, var(--bs-success) 0%, #059669 100%);
        }
        
        .legend-warning {
            background: linear-gradient(135deg, var(--bs-warning) 0%, #d97706 100%);
        }
        
        .legend-danger {
            background: linear-gradient(135deg, var(--bs-danger) 0%, #dc2626 100%);
        }
        
        .legend-secondary {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
        }
        
        /* Calendar Filter Section */
        .card-inner .row.g-3 {
            margin: 0;
        }
        
        .card-inner .row.g-3 > * {
            padding-right: calc(var(--bs-gutter-x) * 0.5);
            padding-left: calc(var(--bs-gutter-x) * 0.5);
        }
        
        /* Calendar Card Header Enhancement */
        .card-inner h6 {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .card-inner h6 i {
            color: var(--bs-primary);
        }
        
        /* Alerts */
        .alert {
            border-radius: 0.5rem;
            border: none;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="nk-sidebar" id="sidebar">
        <div class="nk-sidebar-brand">
            <a href="<?php echo base_url('dashboard'); ?>">
                <i class="bi bi-building"></i>
                <span class="nk-menu-text">BODARE Admin</span>
            </a>
        </div>
        <nav class="nk-menu">
            <?php
            // Load Admin_model for permission checks
            $this->load->model('Admin_model');
            $admin_id = $this->session->userdata('admin_id');
            
            // Check super admin status
            $is_super_admin = false;
            if ($admin_id) {
                $is_super_admin = $this->Admin_model->is_super_admin($admin_id);
            }
            
            // Get current URI for active state
            $current_uri = uri_string();
            ?>
            
            <!-- Dashboard -->
            <div class="nk-menu-item">
                <a href="<?php echo base_url('dashboard'); ?>" class="nk-menu-link <?php echo ($current_uri == 'dashboard' || $current_uri == '' || $current_uri == 'login') ? 'active' : ''; ?>">
                    <span class="nk-menu-icon"><i class="bi bi-speedometer2"></i></span>
                    <span class="nk-menu-text">Dashboard</span>
                </a>
            </div>
            
            <!-- Bookings -->
            <?php if ($admin_id && $this->Admin_model->has_permission($admin_id, 'view_bookings')): ?>
            <div class="nk-menu-item">
                <a href="<?php echo base_url('bookings'); ?>" class="nk-menu-link <?php echo strpos($current_uri, 'bookings') !== false ? 'active' : ''; ?>">
                    <span class="nk-menu-icon"><i class="bi bi-calendar-check"></i></span>
                    <span class="nk-menu-text">Bookings</span>
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Rooms -->
            <?php if ($admin_id && $this->Admin_model->has_permission($admin_id, 'view_rooms')): ?>
            <div class="nk-menu-item">
                <a href="<?php echo base_url('rooms'); ?>" class="nk-menu-link <?php echo strpos($current_uri, 'rooms') !== false ? 'active' : ''; ?>">
                    <span class="nk-menu-icon"><i class="bi bi-door-open"></i></span>
                    <span class="nk-menu-text">Rooms</span>
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Inquiries -->
            <?php if ($admin_id && $this->Admin_model->has_permission($admin_id, 'view_inquiries')): ?>
            <div class="nk-menu-item">
                <a href="<?php echo base_url('inquiries'); ?>" class="nk-menu-link <?php echo strpos($current_uri, 'inquiries') !== false ? 'active' : ''; ?>">
                    <span class="nk-menu-icon"><i class="bi bi-envelope"></i></span>
                    <span class="nk-menu-text">Inquiries</span>
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Customers/Guests -->
            <?php if ($admin_id && $this->Admin_model->has_permission($admin_id, 'view_bookings')): ?>
            <div class="nk-menu-item">
                <a href="<?php echo base_url('customers'); ?>" class="nk-menu-link <?php echo strpos($current_uri, 'customers') !== false ? 'active' : ''; ?>">
                    <span class="nk-menu-icon"><i class="bi bi-person-badge"></i></span>
                    <span class="nk-menu-text">Customers/Guests</span>
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Reports -->
            <?php if ($admin_id && $this->Admin_model->has_permission($admin_id, 'view_reports')): ?>
            <?php 
            $is_reports_active = strpos($current_uri, 'reports') !== false;
            ?>
            <div class="nk-menu-item has-submenu <?php echo $is_reports_active ? 'active' : ''; ?>">
                <a href="#" class="nk-menu-link <?php echo $is_reports_active ? 'active' : ''; ?>" onclick="event.preventDefault(); this.closest('.nk-menu-item').classList.toggle('active');">
                    <span class="nk-menu-icon"><i class="bi bi-graph-up"></i></span>
                    <span class="nk-menu-text">Reports</span>
                    <span class="nk-menu-toggle"><i class="bi bi-chevron-right"></i></span>
                </a>
                <div class="nk-menu-sub">
                    <div class="nk-menu-sub-item">
                        <a href="<?php echo base_url('reports/daily_sales'); ?>" class="nk-menu-sub-link <?php echo strpos($current_uri, 'reports/daily_sales') !== false ? 'active' : ''; ?>">
                            <i class="bi bi-calendar-day me-2"></i> Daily Sales Report
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Users -->
            <?php if ($admin_id && ($this->Admin_model->has_permission($admin_id, 'view_users') || $this->Admin_model->has_permission($admin_id, 'manage_users'))): ?>
            <div class="nk-menu-item">
                <a href="<?php echo base_url('users'); ?>" class="nk-menu-link <?php echo strpos($current_uri, 'users') !== false ? 'active' : ''; ?>">
                    <span class="nk-menu-icon"><i class="bi bi-people"></i></span>
                    <span class="nk-menu-text">Users</span>
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Groups -->
            <?php if ($admin_id && $this->Admin_model->has_permission($admin_id, 'manage_groups')): ?>
            <div class="nk-menu-item">
                <a href="<?php echo base_url('groups'); ?>" class="nk-menu-link <?php echo strpos($current_uri, 'groups') !== false ? 'active' : ''; ?>">
                    <span class="nk-menu-icon"><i class="bi bi-people-fill"></i></span>
                    <span class="nk-menu-text">Groups</span>
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Roles -->
            <?php if ($admin_id && $this->Admin_model->has_permission($admin_id, 'manage_roles')): ?>
            <div class="nk-menu-item">
                <a href="<?php echo base_url('roles'); ?>" class="nk-menu-link <?php echo strpos($current_uri, 'roles') !== false ? 'active' : ''; ?>">
                    <span class="nk-menu-icon"><i class="bi bi-shield-check"></i></span>
                    <span class="nk-menu-text">Roles</span>
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Module Generator - super admin only -->
            <?php if ($is_super_admin): ?>
            <div class="nk-menu-item" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                <a href="<?php echo base_url('module_generator'); ?>" class="nk-menu-link <?php echo strpos($current_uri, 'module_generator') !== false ? 'active' : ''; ?>">
                    <span class="nk-menu-icon"><i class="bi bi-magic"></i></span>
                    <span class="nk-menu-text">Module Generator</span>
                    <span class="nk-menu-badge">Super Admin</span>
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Logout -->
            <div class="nk-menu-item" style="margin-top: auto; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                <a href="<?php echo base_url('logout'); ?>" class="nk-menu-link">
                    <span class="nk-menu-icon"><i class="bi bi-box-arrow-right"></i></span>
                    <span class="nk-menu-text">Logout</span>
                </a>
            </div>
        </nav>
    </div>
    
    <!-- Header -->
    <div class="nk-header">
        <div class="nk-header-brand">
            <a href="<?php echo base_url('dashboard'); ?>" class="logo-link">
                <i class="bi bi-building"></i>
                <span>BODARE</span>
            </a>
        </div>
        <h5 class="nk-header-title"><?php echo isset($title) ? $title : 'Admin Panel'; ?></h5>
        <div class="nk-header-tools">
            <span class="text-muted me-3 d-none d-md-inline">Welcome, <?php 
                $admin_name = $this->session->userdata('admin_name');
                $admin_username = $this->session->userdata('admin_username');
                // Use name if available, otherwise fallback to username
                $display_name = !empty($admin_name) ? $admin_name : (!empty($admin_username) ? $admin_username : 'Admin');
                echo htmlspecialchars($display_name);
            ?></span>
            <div class="user-dropdown">
                <button class="btn p-0 border-0" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-avatar">
                        <?php 
                        $admin_name = $this->session->userdata('admin_name');
                        $admin_username = $this->session->userdata('admin_username');
                        $admin_id = $this->session->userdata('admin_id');
                        
                        // Get admin data to check for avatar
                        $this->load->model('Admin_model');
                        $admin_data = $this->Admin_model->get_admin($admin_id);
                        
                        if (!empty($admin_data->avatar) && file_exists(FCPATH . $admin_data->avatar)): ?>
                            <img src="<?php echo base_url($admin_data->avatar); ?>" alt="Avatar" 
                                style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        <?php else: 
                            // Use name if available, otherwise fallback to username
                            $display_name = !empty($admin_name) ? $admin_name : (!empty($admin_username) ? $admin_username : 'Admin');
                            echo strtoupper(substr($display_name, 0, 1)); 
                        endif; ?>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li>
                        <a class="dropdown-item" href="<?php echo base_url('profile'); ?>">
                            <i class="bi bi-person-circle"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="bi bi-gear"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <div class="dropdown-item dark-mode-toggle" onclick="event.stopPropagation();">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <i class="bi bi-moon-stars"></i>
                                <span>Dark Mode</span>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="darkModeToggle" role="switch" onclick="event.stopPropagation();">
                            </div>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="<?php echo base_url('logout'); ?>">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
            </div>
        </div>

    <!-- Content -->
    <div class="nk-content">
