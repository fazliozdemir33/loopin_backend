<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loopn Admin Paneli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Manrope', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-[#0F0E17] text-white flex flex-col h-full shrink-0">
        <div class="h-16 flex items-center px-6 border-b border-gray-800">
            <span class="text-2xl font-black tracking-widest bg-clip-text text-transparent bg-gradient-to-r from-pink-500 to-purple-500">LOOPN</span>
            <span class="ml-2 text-xs text-gray-400">ADMIN</span>
        </div>
        
        <nav class="flex-1 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center px-6 py-3 {{ request()->routeIs('admin.dashboard') ? 'bg-pink-600/10 text-pink-500 border-r-4 border-pink-500' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }} transition-colors">
                <i class="fas fa-home w-6"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            
            <a href="{{ route('admin.users') }}" class="flex items-center px-6 py-3 {{ request()->routeIs('admin.users*') ? 'bg-pink-600/10 text-pink-500 border-r-4 border-pink-500' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }} transition-colors">
                <i class="fas fa-users w-6"></i>
                <span class="font-medium">Kullanıcılar</span>
            </a>
        </nav>
        
        <div class="p-4 border-t border-gray-800">
            <a href="{{ route('admin.logout') }}" class="flex items-center px-2 py-2 text-red-400 hover:text-red-300 transition-colors mb-4">
                <i class="fas fa-sign-out-alt w-6"></i>
                <span class="font-medium">Çıkış Yap</span>
            </a>
            <div class="text-xs text-gray-500 text-center">Loopn App v1.0</div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Topbar -->
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8 shrink-0">
            <h1 class="text-xl font-bold text-gray-800">@yield('header')</h1>
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 font-bold">
                    A
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-8 bg-gray-50">
            @if(session('success'))
                <div class="bg-green-50 text-green-700 p-4 rounded-lg mb-6 border border-green-200 flex items-center shadow-sm">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 border border-red-200 flex items-center shadow-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

</body>
</html>
