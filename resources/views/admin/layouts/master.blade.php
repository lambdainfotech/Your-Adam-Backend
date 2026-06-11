<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="auth-check-url" content="{{ route('admin.login') }}">
    <title>@yield('title', 'Admin Panel') - E-Commerce Admin</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        html, body { height: 100%; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; overflow: hidden; overscroll-behavior: none; }

        /* Sidebar Transitions */
        .sidebar {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overscroll-behavior: contain;
        }
        .sidebar-collapsed { width: 72px; }
        .sidebar-expanded { width: 264px; }

        /* Mobile Sidebar */
        @media (max-width: 767px) {
            .sidebar {
                position: fixed !important;
                left: 0;
                top: 0;
                height: 100vh;
                z-index: 50;
                transform: translateX(-100%);
                width: 264px !important;
            }
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            .sidebar.mobile-open .sidebar-text {
                display: block !important;
            }
        }

        /* Sidebar Scrollbar */
        .sidebar nav::-webkit-scrollbar { width: 4px; }
        .sidebar nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 2px; }
        .sidebar nav::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }

        /* Contain scroll on main content too */
        main { overscroll-behavior: contain; }

        /* Nav Item */
        .nav-item {
            position: relative;
            transition: all 0.2s ease;
            border-radius: 10px;
            margin: 2px 12px;
            padding: 10px 14px;
        }
        .nav-item:hover {
            background-color: rgba(99, 102, 241, 0.1);
            color: #c7d2fe;
        }
        .nav-item:hover .nav-icon { color: #818cf8; }
        .nav-item.active {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(168, 85, 247, 0.15) 100%);
            color: #fff;
            box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.3);
        }
        .nav-item.active .nav-icon { color: #a5b4fc; }
        .nav-item.active::before {
            content: '';
            position: absolute;
            left: -12px;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 20px;
            background: linear-gradient(180deg, #818cf8, #c084fc);
            border-radius: 0 3px 3px 0;
        }

        .submenu { display: none; }
        .submenu.show { display: block; }
        .has-submenu.active .submenu { display: block; }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        .toast {
            padding: 12px 20px;
            border-radius: 12px;
            color: white;
            font-weight: 500;
            margin-bottom: 10px;
            animation: slideIn 0.3s ease;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }
        .toast.success { background: linear-gradient(135deg, #10b981, #059669); }
        .toast.error { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .toast.warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .toast.info { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        .toast.hiding {
            animation: slideOut 0.3s ease forwards;
        }

        /* ============================================
           Select2 Custom Styles (Tailwind Match)
           ============================================ */
        .select2-container .select2-selection--single,
        .select2-container .select2-selection--multiple {
            height: auto;
            min-height: 42px;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            background-color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        .select2-container .select2-selection--single {
            padding: 0.5rem 2.5rem 0.5rem 1rem;
        }
        .select2-container .select2-selection--multiple {
            padding: 0.25rem 2.5rem 0.25rem 0.75rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.5;
            padding-left: 0;
            color: #111827;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            width: 2rem;
            right: 0.25rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #6b7280 transparent transparent transparent;
            border-width: 5px 5px 0 5px;
        }
        .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #6b7280 transparent;
            border-width: 0 5px 5px 5px;
        }
        .select2-container--default .select2-selection--single .select2-selection__clear {
            margin-right: 1.5rem;
            color: #9ca3af;
        }
        .select2-container .select2-selection--single:focus,
        .select2-container .select2-selection--multiple:focus,
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        .select2-dropdown {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            font-family: 'Inter', sans-serif;
            font-size: 0.875rem;
            overflow: hidden;
            z-index: 9999 !important;
        }
        .select2-container--default .select2-results__option {
            padding: 0.5rem 1rem;
            color: #374151;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #eff6ff;
            color: #1d4ed8;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            outline: none;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        .select2-results__options {
            max-height: 280px;
            overflow-y: auto;
        }
        /* Multiple selection tags */
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 0.375rem;
            color: #1e40af;
            padding: 0.125rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #3b82f6;
            margin-right: 0.25rem;
        }
        /* Fix for inline/modal display */
        .select2-container {
            display: block;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-100">
    <!-- Toast Container -->
    <div id="toastContainer" class="toast-container"></div>
    
    <div class="flex h-screen overflow-hidden" id="appContainer">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar sidebar-expanded bg-slate-900 text-slate-300 flex-shrink-0 flex flex-col h-full">
            <!-- Logo Header -->
            <div class="p-4 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3 sidebar-logo overflow-hidden">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20 shrink-0">
                        <i class="fas fa-shopping-bag text-white text-lg"></i>
                    </div>
                    <div class="sidebar-text whitespace-nowrap">
                        <span class="font-bold text-white text-lg tracking-tight">Admin Panel</span>
                    </div>
                </div>
                <button id="toggleSidebar" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white transition-colors shrink-0">
                    <i class="fas fa-bars-staggered text-sm"></i>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-3 pb-4">
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" title="Dashboard" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-house text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Dashboard</span>
                </a>

                <!-- Catalog Section -->
                <div class="mt-5 mb-2 px-3 sidebar-text">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Catalog</span>
                </div>
                <a href="{{ route('admin.products.index') }}" title="Products" class="nav-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-box text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Products</span>
                </a>
                <a href="{{ route('admin.categories.index') }}" title="Categories" class="nav-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-tags text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Categories</span>
                </a>
                <a href="{{ route('admin.attributes.index') }}" title="Attributes" class="nav-item {{ request()->routeIs('admin.attributes.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-sliders-h text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Attributes</span>
                </a>
                <a href="{{ route('admin.size-charts.index') }}" title="Size Charts" class="nav-item {{ request()->routeIs('admin.size-charts.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-ruler text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Size Charts</span>
                </a>
                <a href="{{ route('admin.predefined-descriptions.index') }}" title="Descriptions" class="nav-item {{ request()->routeIs('admin.predefined-descriptions.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-align-left text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Descriptions</span>
                </a>

                <!-- Sales Section -->
                <div class="mt-5 mb-2 px-3 sidebar-text">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Sales</span>
                </div>
                <a href="{{ route('admin.orders.index') }}" title="Orders" class="nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-shopping-cart text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Orders</span>
                </a>
                <a href="{{ route('admin.coupons.index') }}" title="Coupons" class="nav-item {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-ticket-alt text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Coupons</span>
                </a>
                <a href="{{ route('admin.campaigns.index') }}" title="Campaigns" class="nav-item {{ request()->routeIs('admin.campaigns.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-bullhorn text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Campaigns</span>
                </a>
                <a href="{{ route('admin.pos.index') }}" title="POS" class="nav-item {{ request()->routeIs('admin.pos.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-cash-register text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">POS</span>
                </a>
                <a href="{{ route('admin.sliders.index') }}" title="Hero Sliders" class="nav-item {{ request()->routeIs('admin.sliders.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-images text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Hero Sliders</span>
                </a>
                <a href="{{ route('admin.testimonials.index') }}" title="Testimonials" class="nav-item {{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-comments text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Testimonials</span>
                </a>
                <a href="{{ route('admin.brand-values.index') }}" title="Brand Values" class="nav-item {{ request()->routeIs('admin.brand-values.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-gem text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Brand Values</span>
                </a>

                <!-- Inventory Section -->
                <div class="mt-5 mb-2 px-3 sidebar-text">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Inventory</span>
                </div>
                <a href="{{ route('admin.stock-in.bulk') }}" title="Stock In" class="nav-item {{ request()->routeIs('admin.stock-in.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-plus-circle text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Stock In</span>
                </a>
                <a href="{{ route('admin.inventory.index') }}" title="Stock View" class="nav-item {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-warehouse text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Stock View</span>
                </a>
                <a href="{{ route('admin.couriers.index') }}" title="Couriers" class="nav-item {{ request()->routeIs('admin.couriers.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-shipping-fast text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Couriers</span>
                </a>
                <a href="{{ route('admin.districts.index') }}" title="Districts" class="nav-item {{ request()->routeIs('admin.districts.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-map-marker-alt text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Districts</span>
                </a>

                <!-- Users Section -->
                <div class="mt-5 mb-2 px-3 sidebar-text">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Users</span>
                </div>
                <a href="{{ route('admin.users.index') }}" title="Users" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-users text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Users</span>
                </a>
                <a href="{{ route('admin.guests.index') }}" title="Guests" class="nav-item {{ request()->routeIs('admin.guests.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-user-clock text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Guests</span>
                </a>
                <a href="{{ route('admin.roles.index') }}" title="Roles" class="nav-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-user-shield text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Roles</span>
                </a>
                <a href="{{ route('admin.permissions.index') }}" title="Permissions" class="nav-item {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-key text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Permissions</span>
                </a>

                <!-- System Section -->
                <div class="mt-5 mb-2 px-3 sidebar-text">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">System</span>
                </div>
                <a href="{{ route('admin.notifications.index') }}" title="Notifications" class="nav-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-bell text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Notifications</span>
                </a>
                <a href="{{ route('admin.activity-logs.index') }}" title="Activity Logs" class="nav-item {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-history text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Activity Logs</span>
                </a>
                <a href="{{ route('admin.settings.index') }}" title="Settings" class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-cog text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Settings</span>
                </a>
                <a href="{{ route('admin.contact-submissions.index') }}" title="Contact Submissions" class="nav-item {{ request()->routeIs('admin.contact-submissions.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-address-book text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Contact Submissions</span>
                </a>
                <a href="{{ route('admin.newsletter-subscribers.index') }}" title="Newsletter Subscribers" class="nav-item {{ request()->routeIs('admin.newsletter-subscribers.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-paper-plane text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Newsletter Subscribers</span>
                </a>
                <a href="{{ route('admin.faqs.index') }}" title="FAQs" class="nav-item {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-question-circle text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">FAQs</span>
                </a>
                <a href="{{ route('admin.faq-categories.index') }}" title="FAQ Categories" class="nav-item {{ request()->routeIs('admin.faq-categories.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium pl-10">
                    <i class="nav-icon fas fa-folder text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">FAQ Categories</span>
                </a>
                <a href="{{ route('admin.settings.about') }}" title="About" class="nav-item {{ request()->routeIs('admin.settings.about') || request()->routeIs('admin.team-members.*') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-info-circle text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">About</span>
                </a>
                <a href="{{ route('admin.settings.terms') }}" title="Terms & Conditions" class="nav-item {{ request()->routeIs('admin.settings.terms') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-file-contract text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Terms & Conditions</span>
                </a>
                <a href="{{ route('admin.settings.privacy') }}" title="Privacy Policy" class="nav-item {{ request()->routeIs('admin.settings.privacy') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-shield-alt text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Privacy Policy</span>
                </a>
                <a href="{{ route('admin.settings.chat') }}" title="Chat Settings" class="nav-item {{ request()->routeIs('admin.settings.chat') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-comments text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Chat Settings</span>
                </a>

                <!-- Reports Section -->
                <div class="mt-5 mb-2 px-3 sidebar-text">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Reports</span>
                </div>
                <a href="{{ route('admin.reports.sales') }}" title="Sales Report" class="nav-item {{ request()->routeIs('admin.reports.sales') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-chart-line text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Sales Report</span>
                </a>
                <a href="{{ route('admin.reports.products') }}" title="Products Report" class="nav-item {{ request()->routeIs('admin.reports.products') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-chart-bar text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Products Report</span>
                </a>
                <a href="{{ route('admin.reports.customers') }}" title="Customers Report" class="nav-item {{ request()->routeIs('admin.reports.customers') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-chart-pie text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Customers Report</span>
                </a>
                <a href="{{ route('admin.reports.inventory') }}" title="Inventory Report" class="nav-item {{ request()->routeIs('admin.reports.inventory') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-warehouse text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Inventory Report</span>
                </a>
                <a href="{{ route('admin.reports.profit') }}" title="Profit Report" class="nav-item {{ request()->routeIs('admin.reports.profit') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-coins text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Profit Report</span>
                </a>
                <a href="{{ route('admin.reports.expenses') }}" title="Expense Report" class="nav-item {{ request()->routeIs('admin.reports.expenses') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-chart-pie text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Expense Report</span>
                </a>

                <!-- Expenses Section -->
                <div class="mt-5 mb-2 px-3 sidebar-text">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Expenses</span>
                </div>
                <a href="{{ route('admin.expenses.index') }}" title="All Expenses" class="nav-item {{ request()->routeIs('admin.expenses.*') && !request()->routeIs('admin.expenses.categories') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-list text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">All Expenses</span>
                </a>
                <a href="{{ route('admin.expenses.create') }}" title="Add Expense" class="nav-item {{ request()->routeIs('admin.expenses.create') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-plus-circle text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Add Expense</span>
                </a>
                <a href="{{ route('admin.expenses.categories') }}" title="Categories" class="nav-item {{ request()->routeIs('admin.expenses.categories') ? 'active' : '' }} flex items-center gap-3 text-sm font-medium">
                    <i class="nav-icon fas fa-tags text-slate-500 w-5 text-center transition-colors"></i>
                    <span class="sidebar-text whitespace-nowrap">Categories</span>
                </a>
            </nav>
        </aside>
        
        <!-- Mobile Overlay -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden" onclick="closeMobileSidebar()"></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden min-w-0">
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 z-10">
                <div class="flex items-center justify-between px-4 md:px-6 py-3 md:py-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <!-- Mobile Hamburger -->
                        <button id="mobileMenuBtn" class="md:hidden w-9 h-9 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-600 transition-colors" onclick="toggleMobileSidebar()">
                            <i class="fas fa-bars text-lg"></i>
                        </button>
                        <h1 class="text-lg md:text-2xl font-semibold text-gray-800 truncate">@yield('page-title', 'Dashboard')</h1>
                    </div>
                    
                    <div class="flex items-center gap-2 md:gap-4">
                        <!-- Notifications -->
                        <button class="relative p-2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-500 rounded-full">3</span>
                        </button>
                        
                        <!-- User Dropdown -->
                        <div class="relative">
                            <button id="userMenuButton" class="flex items-center gap-2 md:gap-3 focus:outline-none">
                                <div class="w-8 h-8 md:w-10 md:h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white text-sm md:text-base font-semibold">
                                    {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                                </div>
                                <span class="text-gray-700 font-medium hidden sm:block">{{ auth()->user()->name ?? 'Admin' }}</span>
                                <i class="fas fa-chevron-down text-gray-400 text-xs hidden sm:block"></i>
                            </button>
                            
                            <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i> Profile
                                </a>
                                <a href="{{ route('admin.settings.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-2"></i> Settings
                                </a>
                                <hr class="my-1">
                                <form method="POST" action="{{ route('admin.logout') }}" class="block w-full m-0 p-0">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 <?php echo $__env->hasSection('no-padding') ? 'p-0' : 'p-6'; ?>">
                @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
    
    <script>
        // Desktop Sidebar Toggle
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarTexts = document.querySelectorAll('.sidebar-text');
            const icon = this.querySelector('i');

            if (sidebar.classList.contains('sidebar-expanded')) {
                sidebar.classList.remove('sidebar-expanded');
                sidebar.classList.add('sidebar-collapsed');
                sidebarTexts.forEach(text => text.style.display = 'none');
                icon.classList.remove('fa-bars-staggered');
                icon.classList.add('fa-bars');
            } else {
                sidebar.classList.remove('sidebar-collapsed');
                sidebar.classList.add('sidebar-expanded');
                sidebarTexts.forEach(text => text.style.display = '');
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-bars-staggered');
            }
        });

        // Mobile Sidebar
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('hidden');
            document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
        }
        function closeMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.remove('mobile-open');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }
        
        // User Dropdown Toggle
        document.getElementById('userMenuButton').addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('userDropdown').classList.toggle('hidden');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('userDropdown');
            const button = document.getElementById('userMenuButton');
            if (!button.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Toast Notification System
        const Toast = {
            container: document.getElementById('toastContainer'),
            
            show(message, type = 'info', duration = 3000) {
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                
                // Add icon based on type
                const icons = {
                    success: 'check-circle',
                    error: 'exclamation-circle',
                    warning: 'exclamation-triangle',
                    info: 'info-circle'
                };
                
                toast.innerHTML = `
                    <i class="fas fa-${icons[type]} mr-2"></i>
                    ${message}
                `;
                
                this.container.appendChild(toast);
                
                // Auto remove
                setTimeout(() => {
                    toast.classList.add('hiding');
                    setTimeout(() => toast.remove(), 300);
                }, duration);
            }
        };
        
        // Expose Toast globally
        window.Toast = Toast;
        
        // Check for session expired parameter
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('expired') === '1') {
            Toast.show('Your session has expired. Please login again.', 'warning', 5000);
            // Clean URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // Auto-scroll sidebar to keep active menu item visible
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const activeItem = sidebar.querySelector('.nav-item.active');
            if (activeItem) {
                activeItem.scrollIntoView({ behavior: 'auto', block: 'center' });
            }
        });

        // Prevent overscroll/bounce effect that shows blank space
        document.addEventListener('DOMContentLoaded', function() {
            const appContainer = document.getElementById('appContainer');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('main');

            // Helper: stop wheel from propagating when scrollable area is at boundary
            function containScroll(element) {
                if (!element) return;
                element.addEventListener('wheel', function(e) {
                    const isAtTop = element.scrollTop <= 0;
                    const isAtBottom = element.scrollTop + element.clientHeight >= element.scrollHeight - 1;
                    const hasScrollableContent = element.scrollHeight > element.clientHeight;

                    // If at boundary AND has scrollable content, stop propagation to prevent parent scroll
                    if (hasScrollableContent && ((e.deltaY < 0 && isAtTop) || (e.deltaY > 0 && isAtBottom))) {
                        e.stopPropagation();
                    }
                    // Never prevent default here - let the element scroll normally
                }, { passive: true });
            }

            // Apply to sidebar nav and main content
            if (sidebar) {
                const sidebarNav = sidebar.querySelector('nav');
                containScroll(sidebarNav);
            }
            containScroll(mainContent);
        });
    </script>
    
    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Select2 Global Initialization -->
    <script>
        window.initSelect2 = function(context) {
            var $context = context ? $(context) : $('body');
            $context.find('select').each(function() {
                var $select = $(this);
                
                // Skip if already initialized
                if ($select.hasClass('select2-hidden-accessible')) return;
                
                // Skip if explicitly disabled
                if ($select.data('no-select2')) return;
                
                // Detect placeholder from empty option
                var emptyOption = $select.find('option[value=""]').first();
                var placeholder = emptyOption.length ? emptyOption.text() : '';
                
                // Detect if multiple
                var isMultiple = $select.prop('multiple');
                
                // Detect if select is inside a visible modal/dialog for dropdown positioning
                var $modalParent = $select.closest('.fixed, [role="dialog"], .modal');
                
                // Build config
                var config = {
                    width: '100%',
                    minimumResultsForSearch: 8,
                    allowClear: !!placeholder && !isMultiple,
                    placeholder: placeholder || undefined
                };
                
                if ($modalParent.length && $modalParent.is(':visible')) {
                    config.dropdownParent = $modalParent;
                }
                
                // Fix for selects without empty option but with placeholder text
                if (placeholder && !$select.find('option[value=""]').length) {
                    $select.prepend('<option value=""></option>');
                }
                
                $select.select2(config);
                
                // Alpine.js compatibility: sync Select2 changes back to Alpine
                var isAlpine = $select.closest('[x-data]').length > 0 || $select.attr('x-model');
                if (isAlpine) {
                    $select.on('change.select2', function() {
                        // Dispatch input event for x-model
                        this.dispatchEvent(new Event('input', { bubbles: true }));
                        // Dispatch change event for @change handlers
                        this.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                    
                    // Bidirectional sync: when Alpine changes the select value programmatically,
                    // update Select2 to reflect the new value.
                    var valObserver = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            if (mutation.attributeName === 'value') {
                                var newVal = $select.val();
                                if ($select.val() !== $select.data('select2-last-val')) {
                                    $select.data('select2-last-val', newVal);
                                    $select.trigger('change.select2');
                                }
                            }
                        });
                    });
                    valObserver.observe($select[0], { attributes: true });
                }
            });
        };
        
        // Initialize on page load
        $(function() {
            window.initSelect2();
            
            // Watch for dynamically added selects (e.g., after AJAX, modal open)
            var observer = new MutationObserver(function(mutations) {
                var shouldInit = false;
                mutations.forEach(function(mutation) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) {
                            if ($(node).is('select') || $(node).find('select').length > 0) {
                                shouldInit = true;
                            }
                        }
                    });
                });
                if (shouldInit) {
                    window.initSelect2();
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
        });
        
        // Prevent Select2 dropdown clicks from triggering Alpine @click.away on modals
        $(document).on('mousedown click', '.select2-container', function(e) {
            e.stopPropagation();
        });
    </script>
    
    <!-- JWT Authentication Helper -->
    <script src="{{ asset('js/jwt-auth.js') }}"></script>
    
    @stack('scripts')
</body>
</html>
