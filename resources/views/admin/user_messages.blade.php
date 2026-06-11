@extends('admin.layout')

@section('title', 'Sohbetler — ' . ($user->getRawOriginal('name') ?: 'İsimsiz'))
@section('header', ($user->getRawOriginal('name') ?: 'İsimsiz') . ' — Sohbetler')
@section('breadcrumb', 'Kullanıcılar / Sohbet Geçmişi')

@section('topbar-actions')
<a href="{{ route('admin.users') }}" class="btn btn-ghost text-xs">
    <i class="fas fa-arrow-left"></i> Kullanıcılara Dön
</a>
@endsection

@section('content')

{{-- User profile card --}}
<div class="card p-5 mb-5 flex items-center gap-4">
    @if($user->getRawOriginal('avatar_url'))
        <img src="{{ $user->getRawOriginal('avatar_url') }}" class="w-14 h-14 rounded-full object-cover ring-2 ring-pink-500/30">
    @else
        <div class="w-14 h-14 rounded-full avatar-placeholder text-lg"><i class="fas fa-user"></i></div>
    @endif
    <div class="flex-1">
        <div class="font-bold text-white text-base">{{ $user->getRawOriginal('name') ?: 'İsimsiz' }}</div>
        <div class="text-xs mt-0.5" style="color: var(--text-muted);">{{ $user->email ?: 'E-posta yok' }}</div>
        <div class="flex items-center gap-3 mt-2">
            @if($user->is_banned)
                <span class="badge badge-red"><i class="fas fa-ban" style="font-size:9px;"></i> Yasaklı</span>
            @else
                <span class="badge badge-green"><i class="fas fa-circle" style="font-size:7px;"></i> Aktif</span>
            @endif
            @if($user->gender)
                <span class="badge badge-blue">
                    <i class="fas fa-{{ $user->gender=='male'?'mars':'venus' }}"></i>
                    {{ $user->gender=='male'?'Erkek':'Kadın' }}
                    @if($user->age) — {{ $user->age }} yaş @endif
                </span>
            @endif
            <span class="badge badge-yellow"><i class="fas fa-key"></i> {{ $user->wallet_balance ?? 0 }} Anahtar</span>
            <span class="badge badge-purple"><i class="fas fa-comments"></i> {{ $conversations->count() }} Sohbet</span>
        </div>
    </div>
</div>

{{-- Conversations --}}
<div class="space-y-4">
    @forelse($conversations as $conv)
        @php
            $otherUser = $conv->user1_id == $user->id ? $conv->user2 : $conv->user1;
        @endphp

        <div class="card overflow-hidden">
            {{-- Conversation header --}}
            <div class="flex items-center justify-between p-4 border-b" style="border-color: var(--border); background: rgba(255,255,255,0.02);">
                <div class="flex items-center gap-3">
                    @if($otherUser && $otherUser->getRawOriginal('avatar_url'))
                        <img src="{{ $otherUser->getRawOriginal('avatar_url') }}" class="avatar">
                    @else
                        <div class="avatar-placeholder"><i class="fas fa-user text-xs"></i></div>
                    @endif
                    <div>
                        <div class="text-sm font-semibold text-white">
                            {{ $otherUser ? ($otherUser->getRawOriginal('name') ?: 'İsimsiz') : 'Silinmiş Kullanıcı' }}
                        </div>
                        <div class="text-xs" style="color: var(--text-muted);">
                            Son güncelleme: {{ $conv->updated_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($conv->is_unlocked)
                        <span class="badge badge-green"><i class="fas fa-lock-open" style="font-size:9px;"></i> Kilit Açık</span>
                    @else
                        <span class="badge badge-yellow"><i class="fas fa-lock" style="font-size:9px;"></i> Kilitli</span>
                    @endif
                    <span class="badge badge-blue"><i class="fas fa-message" style="font-size:9px;"></i> {{ $conv->messages->count() }} / {{ $conv->message_count }}</span>
                </div>
            </div>

            {{-- Messages --}}
            <div class="p-4 space-y-3 max-h-80 overflow-y-auto" style="background: rgba(0,0,0,0.2);">
                @if($conv->messages->count() > 0)
                    @foreach($conv->messages->reverse() as $msg)
                        @php $isOwner = $msg->sender_id == $user->id; @endphp
                        <div class="flex {{ $isOwner ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[70%] rounded-2xl px-4 py-2.5 {{ $isOwner ? 'rounded-tr-sm' : 'rounded-tl-sm' }}"
                                style="{{ $isOwner ? 'background: linear-gradient(135deg, #ec4899, #a855f7); color: #fff;' : 'background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.08); color: #e2e2e8;' }}">
                                @if($msg->type == 'voice')
                                    <div class="flex items-center gap-2 text-xs">
                                        <i class="fas fa-microphone"></i>
                                        <span>Sesli Mesaj</span>
                                        @if($msg->voice_duration)
                                            <span class="opacity-70">{{ $msg->voice_duration }}s</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-sm leading-snug">{{ $msg->text ?: '—' }}</div>
                                @endif
                                <div class="text-[10px] mt-1 text-right opacity-60">{{ $msg->created_at->format('H:i') }}</div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-8" style="color: var(--text-muted);">
                        <i class="fas fa-comment-slash text-2xl mb-2 block opacity-30"></i>
                        Henüz mesaj yok
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="card p-16 text-center">
            <i class="fas fa-inbox text-4xl block mb-3 opacity-20 text-white"></i>
            <div class="text-sm font-semibold text-white mb-1">Sohbet Bulunamadı</div>
            <div class="text-xs" style="color: var(--text-muted);">Bu kullanıcının henüz hiçbir eşleşmesi yok.</div>
        </div>
    @endforelse
</div>

@endsection
