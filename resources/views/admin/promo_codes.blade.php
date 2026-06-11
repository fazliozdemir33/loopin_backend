@extends('admin.layout')

@section('title', 'Promosyon Kodları')
@section('header', 'Promosyon Kodları')
@section('breadcrumb', 'Sistem / Promosyon Kodları')

@section('content')
<div class="space-y-6">

    {{-- Create new code --}}
    <div class="card p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="stat-icon" style="background: rgba(168,85,247,0.12);">
                <i class="fas fa-ticket" style="color: #a855f7;"></i>
            </div>
            <div>
                <h2 class="text-sm font-bold text-white">Yeni Promosyon Kodu Oluştur</h2>
                <div class="text-xs" style="color: var(--text-muted);">Kullanıcılara ücretsiz anahtar kazandıracak kodlar oluşturun.</div>
            </div>
        </div>

        <form action="{{ route('admin.promo.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: rgba(255,255,255,0.5);">Kod <span class="text-pink-400">*</span></label>
                    <div class="flex gap-2">
                        <input type="text" name="code" id="codeInput" required maxlength="30"
                            class="form-input uppercase" placeholder="Örn: LOOPN2026"
                            style="text-transform:uppercase; letter-spacing: 2px; font-family: monospace;"
                            oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')">
                        <button type="button" onclick="generateCode()"
                            class="btn btn-primary-sm px-3 whitespace-nowrap" title="Otomatik Oluştur">
                            <i class="fas fa-dice"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: rgba(255,255,255,0.5);">Açıklama</label>
                    <input type="text" name="description" class="form-input" placeholder="Örn: Yaz kampanyası">
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: rgba(255,255,255,0.5);">Ödül (Anahtar) <span class="text-pink-400">*</span></label>
                    <input type="number" name="reward_keys" required min="1" max="999" value="1" class="form-input">
                    <p class="text-[11px] mt-1" style="color: var(--text-muted);">Kodu kullanan kişiye verilecek anahtar sayısı</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: rgba(255,255,255,0.5);">Maks. Kullanım</label>
                    <input type="number" name="max_uses" min="0" value="1" class="form-input">
                    <p class="text-[11px] mt-1" style="color: var(--text-muted);">0 = sınırsız</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: rgba(255,255,255,0.5);">Son Kullanım Tarihi</label>
                    <input type="datetime-local" name="expires_at" class="form-input">
                    <p class="text-[11px] mt-1" style="color: var(--text-muted);">Boş bırakılırsa süresiz</p>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="btn btn-primary-sm w-full justify-center py-3">
                        <i class="fas fa-plus mr-1"></i> Kodu Oluştur
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Codes list --}}
    <div class="card">
        <div class="p-5 border-b" style="border-color: var(--border);">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-white text-sm flex items-center gap-2">
                    <i class="fas fa-list" style="color: var(--text-muted);"></i>
                    Tüm Kodlar ({{ $codes->total() }})
                </h3>
            </div>
        </div>

        @if($codes->isEmpty())
        <div class="p-10 text-center" style="color: var(--text-muted);">
            <i class="fas fa-ticket text-3xl mb-3 block opacity-30"></i>
            <div class="text-sm">Henüz promosyon kodu oluşturulmamış.</div>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <th class="text-left p-4 text-xs font-semibold uppercase tracking-wide" style="color: var(--text-muted);">Kod</th>
                        <th class="text-left p-4 text-xs font-semibold uppercase tracking-wide" style="color: var(--text-muted);">Açıklama</th>
                        <th class="text-center p-4 text-xs font-semibold uppercase tracking-wide" style="color: var(--text-muted);">Ödül</th>
                        <th class="text-center p-4 text-xs font-semibold uppercase tracking-wide" style="color: var(--text-muted);">Kullanım</th>
                        <th class="text-center p-4 text-xs font-semibold uppercase tracking-wide" style="color: var(--text-muted);">Son Tarih</th>
                        <th class="text-center p-4 text-xs font-semibold uppercase tracking-wide" style="color: var(--text-muted);">Durum</th>
                        <th class="text-center p-4 text-xs font-semibold uppercase tracking-wide" style="color: var(--text-muted);">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($codes as $code)
                    @php
                        $isExpired = $code->expires_at && \Carbon\Carbon::parse($code->expires_at)->isPast();
                        $isFull = $code->max_uses > 0 && $code->used_count >= $code->max_uses;
                        $isEffective = $code->is_active && !$isExpired && !$isFull;
                    @endphp
                    <tr style="border-bottom: 1px solid var(--border-subtle);" class="hover:bg-white/[0.02] transition-colors">
                        <td class="p-4">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-sm tracking-widest px-3 py-1 rounded-lg"
                                    style="background: rgba(168,85,247,0.12); color: #a855f7; letter-spacing: 2px;">
                                    {{ $code->code }}
                                </span>
                                <button onclick="navigator.clipboard.writeText('{{ $code->code }}')"
                                    class="text-gray-500 hover:text-white transition-colors" title="Kopyala">
                                    <i class="fas fa-copy text-xs"></i>
                                </button>
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="text-sm" style="color: var(--text-secondary);">{{ $code->description ?: '—' }}</span>
                        </td>
                        <td class="p-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-bold"
                                style="background: rgba(236,72,153,0.12); color: #ec4899;">
                                🗝️ {{ $code->reward_keys }} Anahtar
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <div class="text-sm font-semibold text-white">
                                {{ $code->used_count }}
                                @if($code->max_uses > 0)
                                <span style="color: var(--text-muted);">/ {{ $code->max_uses }}</span>
                                @else
                                <span style="color: var(--text-muted);">/ ∞</span>
                                @endif
                            </div>
                            @if($code->max_uses > 0)
                            <div class="w-20 mx-auto mt-1 h-1 rounded-full overflow-hidden" style="background: rgba(255,255,255,0.08);">
                                <div class="h-full rounded-full"
                                    style="width: {{ min(100, ($code->used_count / $code->max_uses) * 100) }}%; background: #ec4899;"></div>
                            </div>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            @if($code->expires_at)
                                <span class="text-xs {{ $isExpired ? 'text-red-400' : '' }}" style="{{ $isExpired ? '' : 'color: var(--text-muted);' }}">
                                    {{ \Carbon\Carbon::parse($code->expires_at)->format('d.m.Y H:i') }}
                                </span>
                            @else
                                <span class="text-xs" style="color: var(--text-muted);">Süresiz</span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            @if($isEffective)
                                <span class="badge-success">Aktif</span>
                            @elseif(!$code->is_active)
                                <span class="badge-danger">Pasif</span>
                            @elseif($isExpired)
                                <span class="badge-warning">Süresi Doldu</span>
                            @elseif($isFull)
                                <span class="badge-warning">Doldu</span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <form action="{{ route('admin.promo.toggle', $code->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" title="{{ $code->is_active ? 'Pasife Al' : 'Aktife Al' }}"
                                        class="text-xs px-2 py-1 rounded-lg transition-colors"
                                        style="{{ $code->is_active ? 'background: rgba(239,68,68,0.12); color: #f87171;' : 'background: rgba(34,197,94,0.12); color: #4ade80;' }}">
                                        <i class="fas {{ $code->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.promo.delete', $code->id) }}" method="POST"
                                    onsubmit="return confirm('Bu kodu silmek istediğinizden emin misiniz?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs px-2 py-1 rounded-lg transition-colors"
                                        style="background: rgba(239,68,68,0.12); color: #f87171;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($codes->hasPages())
        <div class="p-4 border-t" style="border-color: var(--border);">
            {{ $codes->links() }}
        </div>
        @endif
        @endif
    </div>
</div>

<script>
function generateCode() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    let code = '';
    for (let i = 0; i < 8; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('codeInput').value = code;
}
</script>
@endsection
