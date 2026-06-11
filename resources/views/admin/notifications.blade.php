@extends('admin.layout')

@section('title', 'Push Bildirimleri')
@section('header', 'Bildirim Gönder')
@section('breadcrumb', 'Sistem / Bildirimler')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="stat-icon" style="background: rgba(236,72,153,0.12);">
                <i class="fas fa-paper-plane" style="color: #ec4899;"></i>
            </div>
            <div>
                <h2 class="text-sm font-bold text-white">Yeni Push Bildirimi Oluştur</h2>
                <div class="text-xs" style="color: var(--text-muted);">Kullanıcılara doğrudan bildirim gönderin.</div>
            </div>
        </div>

        <form action="{{ route('admin.notifications.send') }}" method="POST" class="space-y-5">
            @csrf
            
            <div>
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: rgba(255,255,255,0.5);">Hedef Kitle</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <label class="relative flex items-center p-3 cursor-pointer rounded-xl border border-transparent" style="background: rgba(255,255,255,0.03); border-color: var(--border);">
                        <input type="radio" name="target" value="all" class="peer sr-only" checked onchange="toggleUserField(false)">
                        <div class="w-full text-center">
                            <i class="fas fa-users mb-2 block text-xl" style="color: var(--text-muted);"></i>
                            <div class="text-xs font-bold text-white">Herkes</div>
                        </div>
                        <div class="absolute inset-0 rounded-xl border-2 border-transparent peer-checked:border-pink-500 pointer-events-none transition-all"></div>
                    </label>

                    <label class="relative flex items-center p-3 cursor-pointer rounded-xl border border-transparent" style="background: rgba(255,255,255,0.03); border-color: var(--border);">
                        <input type="radio" name="target" value="women" class="peer sr-only" onchange="toggleUserField(false)">
                        <div class="w-full text-center">
                            <i class="fas fa-venus mb-2 block text-xl" style="color: #f472b6;"></i>
                            <div class="text-xs font-bold text-white">Sadece Kadınlar</div>
                        </div>
                        <div class="absolute inset-0 rounded-xl border-2 border-transparent peer-checked:border-pink-500 pointer-events-none transition-all"></div>
                    </label>

                    <label class="relative flex items-center p-3 cursor-pointer rounded-xl border border-transparent" style="background: rgba(255,255,255,0.03); border-color: var(--border);">
                        <input type="radio" name="target" value="men" class="peer sr-only" onchange="toggleUserField(false)">
                        <div class="w-full text-center">
                            <i class="fas fa-mars mb-2 block text-xl" style="color: #60a5fa;"></i>
                            <div class="text-xs font-bold text-white">Sadece Erkekler</div>
                        </div>
                        <div class="absolute inset-0 rounded-xl border-2 border-transparent peer-checked:border-pink-500 pointer-events-none transition-all"></div>
                    </label>

                    <label class="relative flex items-center p-3 cursor-pointer rounded-xl border border-transparent" style="background: rgba(255,255,255,0.03); border-color: var(--border);">
                        <input type="radio" name="target" value="user" class="peer sr-only" onchange="toggleUserField(true)">
                        <div class="w-full text-center">
                            <i class="fas fa-user-tag mb-2 block text-xl" style="color: #a855f7;"></i>
                            <div class="text-xs font-bold text-white">Belirli Kullanıcı</div>
                        </div>
                        <div class="absolute inset-0 rounded-xl border-2 border-transparent peer-checked:border-pink-500 pointer-events-none transition-all"></div>
                    </label>
                </div>
            </div>

            <div id="user_field_wrapper" style="display: none;">
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: rgba(255,255,255,0.5);">Kullanıcı ID</label>
                <input type="number" name="user_id" class="form-input" placeholder="Örn: 145" min="1">
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: rgba(255,255,255,0.5);">Bildirim Başlığı</label>
                <input type="text" name="title" required class="form-input" placeholder="Örn: Yeni Mesajın Var!" maxlength="100">
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: rgba(255,255,255,0.5);">Bildirim İçeriği</label>
                <textarea name="body" required class="form-input" rows="4" placeholder="Bildirim mesajı buraya gelecek..." maxlength="255"></textarea>
                <div class="text-[10px] mt-1 text-right" style="color: var(--text-muted);">Maks 255 karakter</div>
            </div>

            <button type="submit" class="btn btn-primary-sm w-full justify-center py-3 text-sm mt-4">
                <i class="fas fa-paper-plane mr-1"></i> Bildirimi Gönder
            </button>
        </form>
    </div>
</div>

<script>
    function toggleUserField(show) {
        document.getElementById('user_field_wrapper').style.display = show ? 'block' : 'none';
        const userIdInput = document.querySelector('input[name="user_id"]');
        if (show) {
            userIdInput.setAttribute('required', 'required');
        } else {
            userIdInput.removeAttribute('required');
        }
    }
</script>
@endsection
