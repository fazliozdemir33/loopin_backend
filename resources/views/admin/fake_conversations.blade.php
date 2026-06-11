@extends('admin.layout')

@section('title', 'Bot Sohbetleri')
@section('header', 'Sahte Hesap Sohbetleri')
@section('breadcrumb', 'Kullanıcılar / Bot Sohbetleri')

@section('content')

<div class="card overflow-hidden">
    <div class="p-5 border-b flex flex-wrap gap-4 items-center justify-between" style="border-color: var(--border);">
        <div class="flex items-center gap-3">
            <div class="stat-icon" style="background: rgba(59,130,246,0.12);">
                <i class="fas fa-robot" style="color: #60a5fa;"></i>
            </div>
            <div>
                <div class="text-sm font-bold text-white">Bot Etkileşimleri</div>
                <div class="text-xs" style="color: var(--text-muted);">Kullanıcıların sahte hesaplarla olan konuşmaları</div>
            </div>
        </div>
    </div>

    @if($conversations->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse data-table">
                <thead>
                    <tr>
                        <th>Gerçek Kullanıcı</th>
                        <th>Bot Hesap</th>
                        <th>Son Mesaj</th>
                        <th>Durum</th>
                        <th class="text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($conversations as $conv)
                        @php
                            $isUser1Fake = $fakeUserIds->contains($conv->user1_id);
                            $botUser = $isUser1Fake ? $conv->user1 : $conv->user2;
                            $realUser = $isUser1Fake ? $conv->user2 : $conv->user1;
                            $lastMsg = $conv->messages->first();
                        @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if($realUser && $realUser->avatar_url)
                                        <img src="{{ $realUser->avatar_url }}" class="avatar">
                                    @else
                                        <div class="avatar-placeholder">{{ substr($realUser->name ?? '?', 0, 1) }}</div>
                                    @endif
                                    <div>
                                        <div class="font-semibold text-white">{{ $realUser->name ?? 'Bilinmeyen Kullanıcı' }}</div>
                                        <div class="text-[11px]" style="color: var(--text-muted);">#{{ $realUser->id ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if($botUser && $botUser->avatar_url)
                                        <img src="{{ $botUser->avatar_url }}" class="avatar">
                                    @else
                                        <div class="avatar-placeholder" style="background: linear-gradient(135deg, rgba(59,130,246,0.3), rgba(168,85,247,0.3));">{{ substr($botUser->name ?? '?', 0, 1) }}</div>
                                    @endif
                                    <div>
                                        <div class="font-semibold text-blue-400">{{ $botUser->name ?? 'Bot' }}</div>
                                        <div class="text-[11px]" style="color: var(--text-muted);">Sahte Hesap</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($lastMsg)
                                    <div class="text-xs text-white max-w-[200px] truncate" title="{{ $lastMsg->text }}">
                                        @if($lastMsg->sender_id == $botUser->id)
                                            <i class="fas fa-reply text-[10px]" style="color: var(--text-muted);"></i>
                                        @endif
                                        {{ $lastMsg->type == 'audio' ? '🎵 Sesli Mesaj' : $lastMsg->text }}
                                    </div>
                                    <div class="text-[10px] mt-1" style="color: var(--text-muted);">
                                        {{ $lastMsg->created_at ? $lastMsg->created_at->diffForHumans() : '' }}
                                    </div>
                                @else
                                    <div class="text-xs" style="color: var(--text-muted);">Henüz mesaj yok</div>
                                @endif
                            </td>
                            <td>
                                @if($conv->is_unlocked)
                                    <span class="badge badge-green"><i class="fas fa-unlock"></i> Açık</span>
                                @else
                                    <span class="badge badge-red"><i class="fas fa-lock"></i> Kilitli</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.fake_conversations.show', $conv->id) }}" class="btn btn-primary-sm">
                                    <i class="fas fa-comment-dots"></i> Sohbet Et
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t" style="border-color: var(--border);">
            {{ $conversations->links() }}
        </div>
    @else
        <div class="p-10 text-center">
            <i class="fas fa-ghost text-4xl mb-4" style="color: var(--text-muted); opacity: 0.5;"></i>
            <h3 class="text-sm font-bold text-white mb-1">Sohbet Bulunamadı</h3>
            <p class="text-xs" style="color: var(--text-muted);">Sahte hesaplarla yapılmış bir konuşma yok.</p>
        </div>
    @endif
</div>

@endsection
