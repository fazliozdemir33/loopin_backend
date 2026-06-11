@extends('admin.layout')

@section('title', 'Dashboard')
@section('header', 'Dashboard')
@section('breadcrumb', 'Genel Bakış')

@section('content')

{{-- ====== STAT CARDS ====== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <div class="flex items-start justify-between mb-4">
            <div class="stat-icon" style="background: rgba(236,72,153,0.12);">
                <i class="fas fa-users" style="color: #ec4899;"></i>
            </div>
            <span class="badge badge-green text-[10px]">+{{ $newUsersToday }} bugün</span>
        </div>
        <div class="text-2xl font-black text-white">{{ number_format($userCount) }}</div>
        <div class="text-xs mt-1" style="color: rgba(255,255,255,0.4);">Toplam Kullanıcı</div>
    </div>

    <div class="stat-card">
        <div class="flex items-start justify-between mb-4">
            <div class="stat-icon" style="background: rgba(168,85,247,0.12);">
                <i class="fas fa-comments" style="color: #a855f7;"></i>
            </div>
            <span class="badge badge-purple text-[10px]">Sohbet</span>
        </div>
        <div class="text-2xl font-black text-white">{{ number_format($convCount) }}</div>
        <div class="text-xs mt-1" style="color: rgba(255,255,255,0.4);">Toplam Eşleşme</div>
    </div>

    <div class="stat-card">
        <div class="flex items-start justify-between mb-4">
            <div class="stat-icon" style="background: rgba(59,130,246,0.12);">
                <i class="fas fa-message" style="color: #60a5fa;"></i>
            </div>
            <span class="badge badge-blue text-[10px]">Mesaj</span>
        </div>
        <div class="text-2xl font-black text-white">{{ number_format($messageCount) }}</div>
        <div class="text-xs mt-1" style="color: rgba(255,255,255,0.4);">Toplam Mesaj</div>
    </div>

    <div class="stat-card">
        <div class="flex items-start justify-between mb-4">
            <div class="stat-icon" style="background: rgba(239,68,68,0.12);">
                <i class="fas fa-ban" style="color: #f87171;"></i>
            </div>
            <span class="badge badge-red text-[10px]">Engel</span>
        </div>
        <div class="text-2xl font-black text-white">{{ number_format($bannedCount) }}</div>
        <div class="text-xs mt-1" style="color: rgba(255,255,255,0.4);">Yasaklı Kullanıcı</div>
    </div>
</div>

{{-- Suspended quick-info banner --}}
@if($suspendedCount > 0)
<div class="mb-6 flex items-center gap-3 p-4 rounded-xl text-sm" style="background: rgba(234,179,8,0.06); border: 1px solid rgba(234,179,8,0.2); color: #facc15;">
    <i class="fas fa-pause-circle text-lg"></i>
    <span>Şu anda <strong>{{ $suspendedCount }}</strong> kullanıcı askıya alınmış durumda — keşfette görünmüyorlar.</span>
    <a href="{{ route('admin.users') }}?status=suspended" class="ml-auto btn text-xs" style="background: rgba(234,179,8,0.15); color: #facc15; border-color: rgba(234,179,8,0.3);">
        Görüntüle <i class="fas fa-arrow-right"></i>
    </a>
</div>
@endif

{{-- ====== SECOND ROW ====== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <div class="stat-icon mb-3" style="background: rgba(34,197,94,0.12);">
            <i class="fas fa-circle" style="color: #4ade80; font-size: 10px;"></i>
        </div>
        <div class="text-2xl font-black text-white">{{ number_format($onlineCount) }}</div>
        <div class="text-xs mt-1" style="color: rgba(255,255,255,0.4);">Şu An Online</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon mb-3" style="background: rgba(234,179,8,0.12);">
            <i class="fas fa-key" style="color: #facc15;"></i>
        </div>
        <div class="text-2xl font-black text-white">{{ number_format($totalKeys) }}</div>
        <div class="text-xs mt-1" style="color: rgba(255,255,255,0.4);">Dağıtılan Anahtar</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon mb-3" style="background: rgba(236,72,153,0.12);">
            <i class="fas fa-lock-open" style="color: #ec4899;"></i>
        </div>
        <div class="text-2xl font-black text-white">{{ number_format($unlockedConvs) }}</div>
        <div class="text-xs mt-1" style="color: rgba(255,255,255,0.4);">Kilidi Açık Sohbet</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon mb-3" style="background: rgba(239,68,68,0.12);">
            <i class="fas fa-flag" style="color: #f87171;"></i>
        </div>
        <div class="text-2xl font-black text-white">{{ number_format($openReports) }}</div>
        <div class="text-xs mt-1" style="color: rgba(255,255,255,0.4);">Açık Şikayet</div>
    </div>
</div>

{{-- ====== BOTTOM GRID: Recent Users + Genders + Quick Settings ====== --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Recent Users --}}
    <div class="card lg:col-span-2">
        <div class="flex items-center justify-between p-5 border-b" style="border-color: var(--border);">
            <div class="text-sm font-bold text-white">Son Kayıtlar</div>
            <a href="{{ route('admin.users') }}" class="btn btn-ghost text-xs">Tümünü Gör</a>
        </div>
        <div class="divide-y" style="border-color: var(--border);">
            @foreach($recentUsers as $u)
            <div class="flex items-center gap-3 p-4">
                @if($u->getRawOriginal('avatar_url'))
                    <img src="{{ $u->getRawOriginal('avatar_url') }}" class="avatar">
                @else
                    <div class="avatar-placeholder"><i class="fas fa-user text-xs"></i></div>
                @endif
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-white truncate">{{ $u->getRawOriginal('name') ?: 'İsimsiz' }}</div>
                    <div class="text-xs truncate" style="color: var(--text-muted);">{{ $u->email ?: 'E-posta yok' }}</div>
                </div>
                <div class="text-right flex-shrink-0">
                    @if($u->is_banned)
                        <span class="badge badge-red"><i class="fas fa-ban" style="font-size:9px;"></i> Yasaklı</span>
                    @elseif($u->getRawOriginal('avatar_url'))
                        <span class="badge badge-green"><i class="fas fa-circle" style="font-size:7px;"></i> Aktif</span>
                    @else
                        <span class="badge badge-yellow">Eksik</span>
                    @endif
                    <div class="text-[10px] mt-1" style="color: var(--text-muted);">{{ $u->created_at->diffForHumans() }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Right column: Gender breakdown + Quick Settings --}}
    <div class="space-y-4">
        {{-- Gender / Provider Breakdown --}}
        <div class="card p-5">
            <div class="text-sm font-bold text-white mb-4">Cinsiyet Dağılımı</div>
            @php
                $total = $genderStats->sum('count') ?: 1;
            @endphp
            @foreach($genderStats as $g)
            @php
                $label = $g->gender;
                $pct = round($g->count / $total * 100);
                $color = match($g->gender) { 'Erkek' => '#60a5fa', 'Kadın' => '#f472b6', default => '#9ca3af' };
            @endphp
            <div class="mb-3">
                <div class="flex justify-between text-xs mb-1">
                    <span style="color: rgba(255,255,255,0.6);">{{ $label }}</span>
                    <span class="font-semibold text-white">{{ number_format($g->count) }} <span style="color: var(--text-muted);">({{ $pct }}%)</span></span>
                </div>
                <div class="h-1.5 rounded-full" style="background: rgba(255,255,255,0.06);">
                    <div class="h-1.5 rounded-full" style="width: {{ $pct }}%; background: {{ $color }};"></div>
                </div>
            </div>
            @endforeach

            <div class="border-t mt-4 pt-4" style="border-color: var(--border);">
                <div class="text-sm font-bold text-white mb-3">Giriş Yöntemi</div>
                @foreach($providerStats as $p)
                @php
                    $icon = match($p->provider) { 'google' => 'fab fa-google', 'apple' => 'fab fa-apple', default => 'fas fa-envelope' };
                    $label = match($p->provider) { 'google' => 'Google', 'apple' => 'Apple', default => 'Klasik' };
                @endphp
                <div class="flex items-center justify-between text-xs py-1.5">
                    <span class="flex items-center gap-2" style="color: rgba(255,255,255,0.6);">
                        <i class="{{ $icon }}" style="width:14px;"></i> {{ $label }}
                    </span>
                    <span class="font-semibold text-white">{{ number_format($p->count) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Quick Settings --}}
        <div class="card p-5">
            <div class="text-sm font-bold text-white mb-4">Hızlı Ayarlar</div>
            <form action="{{ route('admin.dashboard') }}" method="POST" class="space-y-3">
                @csrf
                @foreach($settings as $setting)
                <div>
                    <label class="block text-xs font-medium mb-1.5" style="color: rgba(255,255,255,0.5);">
                        {{ str_replace('_', ' ', ucwords($setting->key, '_')) }}
                    </label>
                    <input type="text" name="{{ $setting->key }}" value="{{ $setting->value }}" class="form-input">
                </div>
                @endforeach
                <button type="submit" class="btn btn-primary-sm w-full justify-center mt-1">
                    <i class="fas fa-floppy-disk"></i> Kaydet
                </button>
            </form>
        </div>
    </div>
</div>

@endsection
