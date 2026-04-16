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
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar { transition: all 0.3s; }
        .sidebar-collapsed { width: 64px; }
        .sidebar-expanded { width: 260px; }
        .nav-item:hover { background-color: rgba(255,255,255,0.1); }
        .nav-item.active { background-color: rgba(255,255,255,0.15); border-left: 4px solid #3b82f6; }
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
            border-radius: 8px;
            color: white;
            font-weight: 500;
            margin-bottom: 10px;
            animation: slideIn 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .toast.success { background-color: #10b981; }
        .toast.error { background-color: #ef4444; }
        .toast.warning { background-color: #f59e0b; }
        .toast.info { background-color: #3b82f6; }
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
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-100">
    <!-- Toast Container -->
    <div id="toastContainer" class="toast-container"></div>
    
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar sidebar-expanded bg-gray-900 text-white flex-shrink-0 overflow-y-auto">
            <div class="p-4 flex items-center justify-between border-b border-gray-800">
                <div class="flex items-center space-x-3 sidebar-logo">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shopping-bag text-xl"></i>
                    </div>
                    <span class="font-bold text-xl sidebar-text">Admin Panel</span>
                </div>
                <button id="toggleSidebar" class="text-gray-400 hover:text-white focus:outline-none">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <nav class="mt-4">
                <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-tachometer-alt w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Dashboard</span>
                </a>
                
                <div class="mt-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider sidebar-text">Catalog</div>
                
                <a href="{{ route('admin.products.index') }}" class="nav-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-box w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Products</span>
                </a>
                
                <a href="{{ route('admin.categories.index') }}" class="nav-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-tags w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Categories</span>
                </a>
                
                <a href="{{ route('admin.attributes.index') }}" class="nav-item {{ request()->routeIs('admin.attributes.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-sliders-h w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Attributes</span>
                </a>
                
                <a href="{{ route('admin.size-charts.index') }}" class="nav-item {{ request()->routeIs('admin.size-charts.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-ruler w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Size Charts</span>
                </a>
                <a href="{{ route('admin.predefined-descriptions.index') }}" class="nav-item {{ request()->routeIs('admin.predefined-descriptions.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-align-left w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Descriptions</span>
                </a>
                
                <div class="mt-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider sidebar-text">Sales</div>
                
                <a href="{{ route('admin.orders.index') }}" class="nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-shopping-cart w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Orders</span>
                </a>
                
                <a href="{{ route('admin.coupons.index') }}" class="nav-item {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-ticket-alt w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Coupons</span>
                </a>
                
                <a href="{{ route('admin.campaigns.index') }}" class="nav-item {{ request()->routeIs('admin.campaigns.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-bullhorn w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Campaigns</span>
                </a>
                
                <a href="{{ route('admin.pos.index') }}" class="nav-item {{ request()->routeIs('admin.pos.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-cash-register w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">POS</span>
                </a>
                
                <a href="{{ route('admin.sliders.index') }}" class="nav-item {{ request()->routeIs('admin.sliders.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-images w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Hero Sliders</span>
                </a>
                
                <a href="{{ route('admin.testimonials.index') }}" class="nav-item {{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-comments w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Testimonials</span>
                </a>
                
                <a href="{{ route('admin.brand-values.index') }}" class="nav-item {{ request()->routeIs('admin.brand-values.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-gem w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Brand Values</span>
                </a>
                
                <div class="mt-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider sidebar-text">Inventory</div>
                
                <a href="{{ route('admin.stock-in.bulk') }}" class="nav-item {{ request()->routeIs('admin.stock-in.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-plus-circle w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Stock In</span>
                </a>
                
                <a href="{{ route('admin.inventory.index') }}" class="nav-item {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-warehouse w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Stock View</span>
                </a>
                
                <a href="{{ route('admin.couriers.index') }}" class="nav-item {{ request()->routeIs('admin.couriers.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-shipping-fast w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Couriers</span>
                </a>
                
                <div class="mt-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider sidebar-text">Users</div>
                
                <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-users w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Customers</span>
                </a>
                
                <a href="{{ route('admin.roles.index') }}" class="nav-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-user-shield w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Roles</span>
                </a>
                
                <a href="{{ route('admin.permissions.index') }}" class="nav-item {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-key w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Permissions</span>
                </a>
                
                <div class="mt-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider sidebar-text">System</div>
                
                <a href="{{ route('admin.notifications.index') }}" class="nav-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-bell w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Notifications</span>
                </a>
                
                <a href="{{ route('admin.activity-logs.index') }}" class="nav-item {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-history w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Activity Logs</span>
                </a>
                
                <a href="{{ route('admin.settings.index') }}" class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-cog w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Settings</span>
                </a>
                
                <div class="mt-4 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider sidebar-text">Reports</div>
                
                <a href="{{ route('admin.reports.sales') }}" class="nav-item {{ request()->routeIs('admin.reports.sales') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-chart-line w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Sales Report</span>
                </a>
                
                <a href="{{ route('admin.reports.products') }}" class="nav-item {{ request()->routeIs('admin.reports.products') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-chart-bar w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Products Report</span>
                </a>
                
                <a href="{{ route('admin.reports.customers') }}" class="nav-item {{ request()->routeIs('admin.reports.customers') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-chart-pie w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Customers Report</span>
                </a>
                
                <a href="{{ route('admin.reports.inventory') }}" class="nav-item {{ request()->routeIs('admin.reports.inventory') ? 'active' : '' }} flex items-center px-4 py-3 text-gray-300 hover:text-white">
                    <i class="fas fa-warehouse w-6 text-center"></i>
                    <span class="ml-3 sidebar-text">Inventory Report</span>
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 z-10">
                <div class="flex items-center justify-between px-6 py-4">
                    <h1 class="text-2xl font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h1>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <button class="relative p-2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-500 rounded-full">3</span>
                        </button>
                        
                        <!-- User Dropdown -->
                        <div class="relative">
                            <button id="userMenuButton" class="flex items-center space-x-3 focus:outline-none">
                                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                                    {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                                </div>
                                <span class="text-gray-700 font-medium">{{ auth()->user()->name ?? 'Admin' }}</span>
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </button>
                            
                            <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i> Profile
                                </a>
                                <a href="{{ route('admin.settings.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-2"></i> Settings
                                </a>
                                <hr class="my-1">
                                <button onclick="JWTAuth.logout()" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
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
        // Sidebar Toggle
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarTexts = document.querySelectorAll('.sidebar-text');
            const sidebarLogo = document.querySelector('.sidebar-logo');
            
            if (sidebar.classList.contains('sidebar-expanded')) {
                sidebar.classList.remove('sidebar-expanded');
                sidebar.classList.add('sidebar-collapsed');
                sidebarTexts.forEach(text => text.style.display = 'none');
            } else {
                sidebar.classList.remove('sidebar-collapsed');
                sidebar.classList.add('sidebar-expanded');
                sidebarTexts.forEach(text => text.style.display = 'block');
            }
        });
        
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
    </script>
    
    <!-- JWT Authentication Helper -->
    <script src="{{ asset('js/jwt-auth.js') }}"></script>
    
    @stack('scripts')
</body>
</html>
