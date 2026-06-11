<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loopin Admin — @yield('title', 'Panel')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }

        :root {
            --bg-base:     #0a0a0f;
            --bg-surface:  #111118;
            --bg-card:     #16161f;
            --border:      rgba(255,255,255,0.07);
            --text-primary: #f0f0f5;
            --text-muted:   rgba(255,255,255,0.38);
            --pink:        #ec4899;
            --purple:      #a855f7;
        }

        body {
            background: var(--bg-base);
            color: var(--text-primary);
        }

        /* Sidebar */
        #sidebar {
            background: var(--bg-surface);
            border-right: 1px solid var(--border);
            width: 240px;
            flex-shrink: 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            border-radius: 10px;
            color: var(--text-muted);
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.18s;
            margin-bottom: 2px;
            position: relative;
        }
        .nav-link:hover {
            background: rgba(255,255,255,0.05);
            color: #fff;
        }
        .nav-link.active {
            background: linear-gradient(135deg, rgba(236,72,153,0.15), rgba(168,85,247,0.12));
            color: #fff;
            border: 1px solid rgba(236,72,153,0.2);
        }
        .nav-link.active .nav-icon {
            background: linear-gradient(135deg, var(--pink), var(--purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .nav-icon {
            width: 18px;
            text-align: center;
            font-size: 14px;
        }
        .nav-badge {
            margin-left: auto;
            background: linear-gradient(135deg, var(--pink), var(--purple));
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 20px;
        }

        /* Cards */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
        }

        /* Stat cards */
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px;
            transition: all 0.2s;
        }
        .stat-card:hover {
            border-color: rgba(236,72,153,0.2);
            transform: translateY(-2px);
        }
        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        /* Table */
        .data-table th {
            background: rgba(255,255,255,0.03);
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            padding: 12px 18px;
        }
        .data-table td {
            padding: 14px 18px;
            font-size: 13.5px;
            border-bottom: 1px solid var(--border);
        }
        .data-table tbody tr {
            transition: background 0.15s;
        }
        .data-table tbody tr:hover {
            background: rgba(255,255,255,0.025);
        }
        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-green  { background: rgba(34,197,94,0.12);  color: #4ade80; border: 1px solid rgba(34,197,94,0.2); }
        .badge-red    { background: rgba(239,68,68,0.12);  color: #f87171; border: 1px solid rgba(239,68,68,0.2); }
        .badge-yellow { background: rgba(234,179,8,0.12);  color: #facc15; border: 1px solid rgba(234,179,8,0.2); }
        .badge-blue   { background: rgba(59,130,246,0.12); color: #60a5fa; border: 1px solid rgba(59,130,246,0.2); }
        .badge-purple { background: rgba(168,85,247,0.12); color: #c084fc; border: 1px solid rgba(168,85,247,0.2); }
        .badge-pink   { background: rgba(236,72,153,0.12); color: #f472b6; border: 1px solid rgba(236,72,153,0.2); }

        /* Buttons */
        .btn { padding: 7px 14px; border-radius: 8px; font-size: 12.5px; font-weight: 600; transition: all 0.18s; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; border: 1px solid transparent; }
        .btn-primary-sm { background: linear-gradient(135deg, var(--pink), var(--purple)); color: #fff; }
        .btn-primary-sm:hover { opacity: 0.85; transform: translateY(-1px); }
        .btn-danger { background: rgba(239,68,68,0.1); color: #f87171; border-color: rgba(239,68,68,0.2); }
        .btn-danger:hover { background: rgba(239,68,68,0.2); }
        .btn-success { background: rgba(34,197,94,0.1); color: #4ade80; border-color: rgba(34,197,94,0.2); }
        .btn-success:hover { background: rgba(34,197,94,0.2); }
        .btn-ghost { background: rgba(255,255,255,0.06); color: var(--text-primary); border-color: var(--border); }
        .btn-ghost:hover { background: rgba(255,255,255,0.1); }

        /* Form inputs */
        .form-input {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            color: #fff;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 13px;
            transition: all 0.18s;
            width: 100%;
        }
        .form-input:focus { outline: none; border-color: rgba(236,72,153,0.5); background: rgba(255,255,255,0.07); }
        .form-input::placeholder { color: var(--text-muted); }

        /* Alert banners */
        .alert-success { background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.2); color: #4ade80; border-radius: 10px; padding: 12px 16px; font-size: 13.5px; display: flex; align-items: center; gap: 10px; }
        .alert-error   { background: rgba(239,68,68,0.08);  border: 1px solid rgba(239,68,68,0.2);  color: #f87171; border-radius: 10px; padding: 12px 16px; font-size: 13.5px; display: flex; align-items: center; gap: 10px; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 10px; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* Avatar */
        .avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
        .avatar-placeholder { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, rgba(236,72,153,0.3), rgba(168,85,247,0.3)); display: flex; align-items: center; justify-content: center; font-size: 13px; color: rgba(255,255,255,0.6); flex-shrink: 0; }

        /* Topbar */
        #topbar {
            height: 56px;
            background: var(--bg-surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 16px;
            flex-shrink: 0;
        }

        /* Logo pill */
        .logo-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
        }
        .logo-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--pink), var(--purple));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #fff;
            flex-shrink: 0;
        }

        /* Pagination */
        .pagination { display: flex; gap: 4px; align-items: center; }
        .pagination a, .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 8px;
            border-radius: 7px;
            font-size: 12.5px;
            font-weight: 500;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            color: var(--text-muted);
            transition: all 0.15s;
        }
        .pagination a:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .pagination .active-page {
            background: linear-gradient(135deg, var(--pink), var(--purple));
            border-color: transparent;
            color: #fff;
        }

        /* Section label */
        .section-label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted); padding: 8px 16px 4px; }
    </style>
</head>
<body class="flex h-screen overflow-hidden">
    <!-- Mobile Sidebar Backdrop -->
    <div id="sidebar-backdrop" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="hidden md:flex flex-col h-full overflow-y-auto fixed md:relative z-50">
        <!-- Logo -->
        <div class="logo-pill">
            <div class="logo-icon"><i class="fas fa-heart"></i></div>
            <div>
                <div class="text-sm font-black text-white tracking-widest">LOOPIN</div>
                <div class="text-[10px]" style="color: var(--text-muted);">Admin v2.0</div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 p-3 space-y-0.5 mt-2">
            <div class="section-label">Genel</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-pie nav-icon"></i>
                Dashboard
            </a>

            <div class="section-label mt-3">Kullanıcılar</div>
            <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                <i class="fas fa-users nav-icon"></i>
                Tüm Kullanıcılar
            </a>
            <a href="{{ route('admin.users.banned') }}" class="nav-link {{ request()->routeIs('admin.users.banned') ? 'active' : '' }}">
                <i class="fas fa-ban nav-icon"></i>
                Yasaklılar
            </a>
            <a href="{{ route('admin.fake_accounts') }}" class="nav-link {{ request()->routeIs('admin.fake_accounts') ? 'active' : '' }}">
                <i class="fas fa-user-secret nav-icon"></i>
                Sahte Hesap Oluştur
            </a>
            <a href="{{ route('admin.fake_conversations') }}" class="nav-link {{ request()->routeIs('admin.fake_conversations*') ? 'active' : '' }}">
                <i class="fas fa-robot nav-icon"></i>
                Bot Sohbetleri
            </a>

            <div class="section-label mt-3">Aktivite</div>
            <a href="{{ route('admin.conversations') }}" class="nav-link {{ request()->routeIs('admin.conversations*') ? 'active' : '' }}">
                <i class="fas fa-comments nav-icon"></i>
                Sohbetler
            </a>
            <a href="{{ route('admin.reports') }}" class="nav-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                <i class="fas fa-flag nav-icon"></i>
                Şikayetler
            </a>
            <a href="{{ route('admin.notifications') }}" class="nav-link {{ request()->routeIs('admin.notifications*') ? 'active' : '' }}">
                <i class="fas fa-bell nav-icon"></i>
                Push Bildirim
            </a>
            <a href="{{ route('admin.support') }}" class="nav-link {{ request()->routeIs('admin.support*') ? 'active' : '' }}">
                <i class="fas fa-headset nav-icon"></i>
                Destek Talepleri
            </a>

            <div class="section-label mt-3">Sistem</div>
            <a href="{{ route('admin.packages') }}" class="nav-link {{ request()->routeIs('admin.packages*') ? 'active' : '' }}">
                <i class="fas fa-box-open nav-icon"></i>
                Paketler
            </a>
            <a href="{{ route('admin.promo') }}" class="nav-link {{ request()->routeIs('admin.promo*') ? 'active' : '' }}">
                <i class="fas fa-ticket nav-icon"></i>
                Promo Kodları
            </a>
            <a href="{{ route('admin.settings') }}" class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                <i class="fas fa-sliders nav-icon"></i>
                Ayarlar
            </a>
        </nav>

        <!-- User / Logout -->
        <div class="p-3 border-t" style="border-color: var(--border);">
            <a href="{{ route('admin.logout') }}" class="nav-link text-red-400 hover:text-red-300 hover:bg-red-500/10">
                <i class="fas fa-right-from-bracket nav-icon"></i>
                Çıkış Yap
            </a>
        </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Topbar -->
        <div id="topbar" class="flex justify-between items-center gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <button class="md:hidden text-white text-lg shrink-0" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <div class="min-w-0">
                    <h1 class="text-sm font-bold text-white truncate">@yield('header')</h1>
                    @hasSection('breadcrumb')
                        <div class="text-[10px] sm:text-xs mt-0.5 truncate" style="color: var(--text-muted);">@yield('breadcrumb')</div>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0 overflow-x-auto hide-scrollbar">
                @yield('topbar-actions')
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center text-[10px] sm:text-xs font-bold text-white shrink-0" style="background: linear-gradient(135deg, #ec4899, #a855f7);">A</div>
            </div>
        </div>

        <!-- Content -->
        <main class="flex-1 overflow-y-auto p-6" style="background: var(--bg-base);">
            @if(session('success'))
                <div class="alert-success mb-5">
                    <i class="fas fa-circle-check"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert-error mb-5">
                    <i class="fas fa-circle-exclamation"></i>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('hidden');
            document.getElementById('sidebar-backdrop').classList.toggle('hidden');
        }
    </script>
</body>
</html>
