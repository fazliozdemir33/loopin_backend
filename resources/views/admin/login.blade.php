<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loopin Admin — Giriş</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            background: #0a0a0f;
            background-image:
                radial-gradient(ellipse 80% 50% at 50% -20%, rgba(236,72,153,0.25) 0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 80% 80%, rgba(139,92,246,0.15) 0%, transparent 50%);
            min-height: 100vh;
        }
        .glass-card {
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .input-field {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            color: #fff;
            transition: all 0.2s;
        }
        .input-field::placeholder { color: rgba(255,255,255,0.3); }
        .input-field:focus {
            outline: none;
            border-color: rgba(236,72,153,0.6);
            background: rgba(255,255,255,0.08);
            box-shadow: 0 0 0 3px rgba(236,72,153,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #ec4899, #a855f7);
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(236,72,153,0.4);
        }
        .logo-glow {
            text-shadow: 0 0 40px rgba(236,72,153,0.5);
        }
        .dot-grid {
            background-image: radial-gradient(rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 28px 28px;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
        .float-blob-1 { animation: float 8s ease-in-out infinite; }
        .float-blob-2 { animation: float 10s ease-in-out infinite 2s; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen dot-grid overflow-hidden">

    <!-- Decorative blobs -->
    <div class="absolute top-1/4 left-1/4 w-64 h-64 rounded-full opacity-10 float-blob-1" style="background: radial-gradient(circle, #ec4899, transparent); filter: blur(40px);"></div>
    <div class="absolute bottom-1/4 right-1/4 w-80 h-80 rounded-full opacity-10 float-blob-2" style="background: radial-gradient(circle, #a855f7, transparent); filter: blur(50px);"></div>

    <div class="relative w-full max-w-md mx-4">
        <!-- Logo area -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-5" style="background: linear-gradient(135deg, #ec4899, #a855f7); box-shadow: 0 0 40px rgba(236,72,153,0.4);">
                <i class="fas fa-heart text-white text-2xl"></i>
            </div>
            <h1 class="text-4xl font-black tracking-widest text-white logo-glow">LOOPIN</h1>
            <p class="text-sm mt-2" style="color: rgba(255,255,255,0.4);">Yönetim Paneli</p>
        </div>

        <!-- Card -->
        <div class="glass-card rounded-2xl p-8 shadow-2xl">
            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl text-sm flex items-center gap-3" style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5;">
                    <i class="fas fa-circle-exclamation"></i>
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-widest mb-2" style="color: rgba(255,255,255,0.5);">Kullanıcı Adı</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-sm" style="color: rgba(255,255,255,0.3);"></i>
                        <input type="text" name="username" required autocomplete="username"
                            class="input-field w-full pl-11 pr-4 py-3.5 rounded-xl text-sm"
                            placeholder="admin">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-widest mb-2" style="color: rgba(255,255,255,0.5);">Şifre</label>
                    <div class="relative">
                        <i class="fas fa-key absolute left-4 top-1/2 -translate-y-1/2 text-sm" style="color: rgba(255,255,255,0.3);"></i>
                        <input type="password" name="password" required autocomplete="current-password"
                            class="input-field w-full pl-11 pr-12 py-3.5 rounded-xl text-sm"
                            placeholder="••••••••" id="pwInput">
                        <button type="button" onclick="togglePw()" class="absolute right-4 top-1/2 -translate-y-1/2 text-sm transition" style="color: rgba(255,255,255,0.3);" id="pwToggle">
                            <i class="fas fa-eye" id="pwIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full py-3.5 rounded-xl text-white font-bold text-sm flex items-center justify-center gap-2 mt-2">
                    <i class="fas fa-arrow-right-to-bracket"></i>
                    Giriş Yap
                </button>
            </form>
        </div>

        <p class="text-center mt-6 text-xs" style="color: rgba(255,255,255,0.2);">© 2026 Loopin — Tüm Hakları Saklıdır</p>
    </div>

    <script>
        function togglePw() {
            const input = document.getElementById('pwInput');
            const icon = document.getElementById('pwIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
