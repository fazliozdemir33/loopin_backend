@extends('admin.layout')

@section('title', 'Ayarlar')
@section('header', 'Sistem Ayarları')
@section('breadcrumb', 'Sistem / Ayarlar')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- App Settings --}}
    <div class="card p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="stat-icon" style="background: rgba(168,85,247,0.12);">
                <i class="fas fa-sliders" style="color: #a855f7;"></i>
            </div>
            <div>
                <div class="text-sm font-bold text-white">Uygulama Ayarları</div>
                <div class="text-xs" style="color: var(--text-muted);">Temel parametre değerleri</div>
            </div>
        </div>

        <form action="{{ route('admin.settings') }}" method="POST" class="space-y-4">
            @csrf
            @foreach($settings as $setting)
            <div>
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: rgba(255,255,255,0.5);">
                    {{ str_replace('_', ' ', $setting->key) }}
                </label>
                
                @if($setting->key == 'firebase_service_account_json')
                    <textarea name="{{ $setting->key }}" class="form-input font-mono text-[10px]" rows="6" placeholder='{"type": "service_account", ...}'>{{ $setting->value }}</textarea>
                    <p class="text-[11px] mt-1.5 flex items-center gap-1" style="color: var(--text-muted);">
                        <i class="fas fa-key"></i> Firebase Console > Service Accounts > Generate New Private Key JSON içeriğini buraya yapıştırın.
                    </p>
                @elseif($setting->key == 'banned_keywords')
                    <textarea name="{{ $setting->key }}" class="form-input text-xs" rows="3" placeholder="instagram, wp, 0555, vb.">{{ $setting->value }}</textarea>
                    <p class="text-[11px] mt-1.5 flex items-center gap-1" style="color: var(--text-muted);">
                        <i class="fas fa-filter"></i> İlk 5 mesajda filtrelenecek kelimeler (virgülle ayırın). Telefon no yakalama ve link engelleme otomatiktir.
                    </p>
                @else
                    <input type="text" name="{{ $setting->key }}" value="{{ $setting->value }}" class="form-input">
                @endif
                
                @if($setting->key == 'max_distance_km')
                    <p class="text-[11px] mt-1.5 flex items-center gap-1" style="color: var(--text-muted);">
                        <i class="fas fa-location-dot"></i> Keşfet ekranındaki maksimum mesafe (km)
                    </p>
                @endif
            </div>
            @endforeach
            <button type="submit" class="btn btn-primary-sm w-full justify-center">
                <i class="fas fa-floppy-disk"></i> Ayarları Kaydet
            </button>
        </form>
    </div>

    {{-- Admin Credentials --}}
    <div class="card p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="stat-icon" style="background: rgba(236,72,153,0.12);">
                <i class="fas fa-shield-halved" style="color: #ec4899;"></i>
            </div>
            <div>
                <div class="text-sm font-bold text-white">Admin Kimlik Bilgileri</div>
                <div class="text-xs" style="color: var(--text-muted);">Kullanıcı adı ve şifre güncelle</div>
            </div>
        </div>

        <form action="{{ route('admin.settings') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: rgba(255,255,255,0.5);">Yeni Kullanıcı Adı</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color: var(--text-muted);"></i>
                    <input type="text" name="admin_username" class="form-input pl-9" placeholder="Yeni kullanıcı adı">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: rgba(255,255,255,0.5);">Yeni Şifre</label>
                <div class="relative">
                    <i class="fas fa-key absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color: var(--text-muted);"></i>
                    <input type="password" name="admin_password" class="form-input pl-9" placeholder="Yeni şifre">
                </div>
            </div>
            <div class="p-3 rounded-lg text-xs flex items-start gap-2" style="background: rgba(234,179,8,0.06); border: 1px solid rgba(234,179,8,0.15); color: #facc15;">
                <i class="fas fa-triangle-exclamation mt-0.5"></i>
                <span>Boş bırakılan alanlar güncellenmez. Mevcut değer korunur.</span>
            </div>
            <button type="submit" class="btn btn-primary-sm w-full justify-center">
                <i class="fas fa-lock"></i> Kimlik Bilgilerini Güncelle
            </button>
        </form>
    </div>

    {{-- System Info --}}
    <div class="card p-6 lg:col-span-2">
        <div class="flex items-center gap-3 mb-5">
            <div class="stat-icon" style="background: rgba(59,130,246,0.12);">
                <i class="fas fa-circle-info" style="color: #60a5fa;"></i>
            </div>
            <div>
                <div class="text-sm font-bold text-white">Sistem Bilgisi</div>
                <div class="text-xs" style="color: var(--text-muted);">Sunucu ve uygulama durumu</div>
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php $info = [
                ['label' => 'Laravel', 'value' => app()->version(), 'icon' => 'fab fa-laravel', 'color' => '#f87171'],
                ['label' => 'PHP', 'value' => PHP_VERSION, 'icon' => 'fab fa-php', 'color' => '#818cf8'],
                ['label' => 'Ortam', 'value' => config('app.env'), 'icon' => 'fas fa-server', 'color' => '#4ade80'],
                ['label' => 'DB', 'value' => config('database.default'), 'icon' => 'fas fa-database', 'color' => '#facc15'],
            ]; @endphp
            @foreach($info as $item)
            <div class="p-4 rounded-xl" style="background: rgba(255,255,255,0.03); border: 1px solid var(--border);">
                <i class="{{ $item['icon'] }} text-lg mb-2 block" style="color: {{ $item['color'] }};"></i>
                <div class="text-xs font-bold text-white">{{ $item['value'] }}</div>
                <div class="text-[10px] mt-0.5" style="color: var(--text-muted);">{{ $item['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

</div>

@endsection
