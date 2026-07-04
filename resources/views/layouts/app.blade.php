<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'EventStock') — EventStock</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <!-- CSS Libraries -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <!-- Custom Tailwind Configuration -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c4b5fd',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                            950: '#0f0b21',
                        },
                        darkbg: {
                            900: '#090d16',
                            800: '#0f172a',
                            700: '#1e293b',
                            600: '#334155',
                        }
                    },
                    boxShadow: {
                        'modern-sm': '0 2px 8px -1px rgba(0, 0, 0, 0.025), 0 1px 3px -1px rgba(0, 0, 0, 0.025)',
                        'modern': '0 4px 20px -2px rgba(0, 0, 0, 0.03), 0 2px 8px -1px rgba(0, 0, 0, 0.025)',
                        'modern-md': '0 10px 25px -3px rgba(0, 0, 0, 0.03), 0 4px 12px -2px rgba(0, 0, 0, 0.025)',
                        'modern-lg': '0 20px 35px -5px rgba(0, 0, 0, 0.04), 0 8px 20px -3px rgba(0, 0, 0, 0.03)',
                    }
                }
            }
        }
    </script>

    <!-- JS Libraries (dimuat awal agar tersedia saat Alpine init) -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Konfigurasi App -->
    <script>
        window.APP_CONFIG = {
            apiBaseUrl: '/api',
        };
        // console.log('✅ APP_CONFIG loaded:', window.APP_CONFIG);
    </script>
    <!-- api.js dimuat sebelum Alpine agar fungsi tersedia -->
    <script src="{{ asset('js/api.js') }}"></script>

    <!-- Alpine.js dimuat terakhir dengan defer -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>

    @stack('head')
</head>

<body class="bg-[#f8fafc] font-sans antialiased text-slate-800" x-data="layoutApp()" x-init="init()">
    <!-- Sidebar -->
    <aside
        class="fixed left-0 top-0 h-full w-64 bg-[#0a0e17] text-slate-300 z-30 border-r border-slate-900/60
                  transition-transform duration-300 shadow-xl flex flex-col"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        <!-- Logo -->
        <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-900/60">
            <div class="w-8 h-8 bg-gradient-to-tr from-indigo-600 to-violet-500 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-600/30">
                <i class="fas fa-boxes-stacked text-white text-sm"></i>
            </div>
            <span class="font-bold text-white text-lg tracking-tight">EventStock</span>
        </div>

        <!-- User info -->
        <div class="px-6 py-4 border-b border-slate-900/60 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center text-indigo-400 font-bold shadow-inner"
                 x-text="currentUser?.name ? currentUser.name.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase() : 'ES'">
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[10px] text-slate-500 uppercase font-semibold tracking-wider mb-0.5">Pengguna</p>
                <p class="text-sm font-semibold text-white truncate" x-text="currentUser?.name || '...'"></p>
                <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider border"
                    :class="{
                        'bg-indigo-500/10 text-indigo-400 border-indigo-500/20': currentUser?.role === 'super_admin',
                        'bg-emerald-500/10 text-emerald-400 border-emerald-500/20': currentUser?.role === 'warehouse_manager',
                        'bg-sky-500/10 text-sky-400 border-sky-500/20': currentUser?.role === 'field_crew'
                    }"
                    x-text="roleLabel(currentUser?.role)">
                </span>
            </div>
        </div>

        <!-- Navigasi -->
        <nav class="px-3 py-4 space-y-1 overflow-y-auto flex-1">
            <a href="/dashboard" class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
                <i class="fas fa-gauge-high w-5"></i>
                <span>Dashboard</span>
            </a>

            <div class="pt-4 pb-1">
                <p class="text-xs text-gray-500 uppercase tracking-wider px-3">
                    Inventaris
                </p>
            </div>

            <a href="/items" class="nav-item {{ request()->is('items') ? 'active' : '' }}">
                <i class="fas fa-box w-5"></i>
                <span>Barang</span>
            </a>

            <a href="/categories" class="nav-item {{ request()->is('categories') ? 'active' : '' }}">
                <i class="fas fa-tags w-5"></i>
                <span>Kategori</span>
            </a>

            <div class="pt-4 pb-1">
                <p class="text-xs text-gray-500 uppercase tracking-wider px-3">
                    Logistik
                </p>
            </div>

            <a href="/manifests" class="nav-item {{ request()->is('manifests') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list w-5"></i>
                <span>Semua Manifest</span>
            </a>

            <a href="/manifests/outbound" class="nav-item {{ request()->is('manifests/outbound') ? 'active' : '' }}">
                <i class="fas fa-arrow-right-from-bracket w-5"></i>
                <span>Barang Keluar</span>
            </a>

            <a href="/manifests/inbound" class="nav-item {{ request()->is('manifests/inbound') ? 'active' : '' }}">
                <i class="fas fa-arrow-right-to-bracket w-5"></i>
                <span>Barang Kembali</span>
            </a>

            <div class="pt-4 pb-1">
                <p class="text-xs text-gray-500 uppercase tracking-wider px-3">
                    Stok
                </p>
            </div>

            <a href="/stock" class="nav-item {{ request()->is('stock') ? 'active' : '' }}">
                <i class="fas fa-warehouse w-5"></i>
                <span>Kelola Stok</span>
            </a>

            <a href="/stock/history" class="nav-item {{ request()->is('stock/history') ? 'active' : '' }}">
                <i class="fas fa-clock-rotate-left w-5"></i>
                <span>Riwayat Stok</span>
            </a>

            <div class="pt-4 pb-1">
                <p class="text-xs text-gray-500 uppercase tracking-wider px-3">
                    Monitoring
                </p>
            </div>

            <a href="/incidents" class="nav-item {{ request()->is('incidents') ? 'active' : '' }}">
                <i class="fas fa-triangle-exclamation w-5"></i>
                <span>Insiden</span>
            </a>

            <a href="/events" class="nav-item {{ request()->is('events') ? 'active' : '' }}">
                <i class="fas fa-calendar-days w-5"></i>
                <span>Events</span>
            </a>

            <!-- Hanya tampil untuk super_admin -->
            <template x-if="currentUser?.role === 'super_admin'">
                <div>
                    <div class="pt-4 pb-1">
                        <p class="text-xs text-gray-500 uppercase tracking-wider px-3">
                            Admin
                        </p>
                    </div>
                    <a href="/users" class="nav-item {{ request()->is('users') ? 'active' : '' }}">
                        <i class="fas fa-users-gear w-5"></i>
                        <span>Pengguna</span>
                    </a>
                </div>
            </template>
        </nav>

        <!-- Logout -->
        <div class="p-4 border-t border-slate-900/60 bg-[#0a0e17] mt-auto">
            <button @click="logout()"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl
                           text-slate-400 hover:text-white hover:bg-slate-900 transition-all text-sm font-medium">
                <i class="fas fa-arrow-right-from-bracket w-5 text-slate-500"></i>
                <span>Logout</span>
            </button>
        </div>
    </aside>

    <!-- Overlay sidebar (mobile) -->
    <div x-show="sidebarOpen && isMobile" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false" 
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-20 lg:hidden"
         style="display: none;">
    </div>

    <!-- Main content area -->
    <div class="transition-all duration-300 min-h-screen flex flex-col" :class="sidebarOpen && !isMobile ? 'ml-64' : 'ml-0'">
        <!-- Navbar atas -->
        <header
            class="sticky top-0 z-10 backdrop-blur-md bg-white/80 border-b border-slate-100
                       flex items-center justify-between px-6 h-16 transition-all duration-300">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-xl text-slate-500 hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-all">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="text-base font-bold text-slate-800 tracking-tight">
                    @yield('page-title', 'Dashboard')
                </h1>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-50 border border-slate-100/80">
                    <div class="w-6 h-6 rounded-full bg-indigo-50 text-indigo-600 border border-indigo-100/50 flex items-center justify-center text-xs font-bold" 
                         x-text="currentUser?.name ? currentUser.name[0].toUpperCase() : 'U'">
                    </div>
                    <span class="text-xs font-medium text-slate-600 max-w-[150px] truncate" x-text="currentUser?.name || ''"></span>
                </div>
            </div>
        </header>

        <!-- Konten halaman -->
        <main class="p-6 flex-1 bg-[#f8fafc]">@yield('content')</main>
    </div>

    <!-- Tailwind custom utility untuk nav-item -->
    <style>
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            color: #94a3b8; /* slate-400 */
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-item:hover {
            background-color: rgba(15, 23, 42, 0.5); /* slate-900/50 */
            color: #f8fafc; /* slate-50 */
            padding-left: 18px; /* micro interaction */
        }

        .nav-item.active {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); /* Indigo gradient */
            color: #ffffff;
            box-shadow: 0 4px 14px -2px rgba(99, 102, 241, 0.35);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid transparent;
        }

        .status-available {
            background: #ecfdf5;
            color: #059669;
            border-color: #d1fae5;
        }

        .status-on-duty {
            background: #eff6ff;
            color: #2563eb;
            border-color: #dbeafe;
        }

        .status-maintenance {
            background: #fffbeb;
            color: #d97706;
            border-color: #fef3c7;
        }

        .status-lost {
            background: #fff5f5;
            color: #e11d48;
            border-color: #fee2e2;
        }

        .type-in {
            background: #ecfdf5;
            color: #059669;
            border-color: #d1fae5;
        }

        .type-out {
            background: #fff5f5;
            color: #e11d48;
            border-color: #fee2e2;
        }

        .type-adjustment {
            background: #fffbeb;
            color: #d97706;
            border-color: #fef3c7;
        }

        .type-correction {
            background: #e0e7ff;
            color: #4f46e5;
            border-color: #e0e7ff;
        }

        /* Custom scrollbar untuk navigasi */
        ::-webkit-scrollbar {
            width: 4px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #334155;
        }
    </style>

    @stack('scripts')

    <!-- Script layout Alpine.js -->
    <script>
        function layoutApp() {
            return {
                sidebarOpen: window.innerWidth >= 1024,
                isMobile: window.innerWidth < 1024,
                currentUser: null,

                async init() {
                    requireAuth(); // redirect ke /login jika tidak ada token

                    window.addEventListener("resize", () => {
                        this.isMobile = window.innerWidth < 1024;
                        if (!this.isMobile) this.sidebarOpen = true;
                    });

                    try {
                        const res = await apiRequest("GET", "/profile");
                        this.currentUser = res.data;
                        setUser(res.data);
                    } catch (e) {
                        // Token mungkin sudah tidak valid
                    }
                },

                roleLabel(role) {
                    const labels = {
                        super_admin: "Super Admin",
                        warehouse_manager: "Manajer Gudang",
                        field_crew: "Tim Lapangan",
                    };
                    return labels[role] || role;
                },

                async logout() {
                    confirmAction(
                        async () => {
                                try {
                                    await apiRequest("POST", "/logout");
                                } finally {
                                    clearToken();
                                    window.location.href = "/login";
                                }
                            },
                            "Keluar dari sistem?",
                            "Sesi Anda akan diakhiri.",
                    );
                },
            };
        }
    </script>
</body>

</html>
