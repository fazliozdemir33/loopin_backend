<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loopn Admin - Giriş</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Manrope', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center h-screen">

    <div class="bg-white p-10 rounded-2xl shadow-xl border border-gray-100 w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-pink-100 text-pink-600 mb-4">
                <i class="fas fa-lock text-2xl"></i>
            </div>
            <h1 class="text-3xl font-black tracking-widest bg-clip-text text-transparent bg-gradient-to-r from-pink-500 to-purple-500">LOOPN</h1>
            <p class="text-gray-500 text-sm mt-2">Yönetim Paneli Girişi</p>
        </div>

        @if(session('error'))
            <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 border border-red-200 flex items-center shadow-sm text-sm">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-5">
            @csrf
            
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Kullanıcı Adı</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400"></i>
                    </div>
                    <input type="text" name="username" required class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition" placeholder="Admin kullanıcı adı">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Şifre</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-key text-gray-400"></i>
                    </div>
                    <input type="password" name="password" required class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition" placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 mt-4 flex justify-center items-center">
                Giriş Yap <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </form>
        
        <div class="mt-8 text-center text-xs text-gray-400">
            &copy; 2026 Loopn Yönetim Sistemi
        </div>
    </div>

</body>
</html>
