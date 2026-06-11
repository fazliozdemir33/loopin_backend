@extends('admin.layout')

@section('title', 'Sohbet Et')
@section('header', 'Bot ile Sohbet')
@section('breadcrumb', 'Kullanıcılar / Bot Sohbetleri / Sohbet #'.$conversation->id)

@section('content')

@php
    $isUser1Fake = in_array($conversation->user1_id, $fakeUserIds);
    $botUser = $isUser1Fake ? $conversation->user1 : $conversation->user2;
    $realUser = $isUser1Fake ? $conversation->user2 : $conversation->user1;
@endphp

<div class="max-w-4xl mx-auto flex flex-col h-[calc(100vh-140px)]">
    
    <!-- Chat Header -->
    <div class="card p-4 flex items-center justify-between flex-shrink-0 z-10 border-b border-[var(--border)] rounded-b-none shadow-sm">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-3">
                @if($realUser && $realUser->avatar_url)
                    <img src="{{ $realUser->avatar_url }}" class="w-10 h-10 rounded-full object-cover">
                @else
                    <div class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-800 text-white font-bold">{{ substr($realUser->name ?? '?', 0, 1) }}</div>
                @endif
                <div>
                    <div class="font-bold text-white">{{ $realUser->name ?? 'Bilinmeyen' }}</div>
                    <div class="text-[11px] text-green-400">Gerçek Kullanıcı</div>
                </div>
            </div>

            <i class="fas fa-arrows-left-right text-gray-500"></i>

            <div class="flex items-center gap-3 text-right">
                <div>
                    <div class="font-bold text-blue-400">{{ $botUser->name ?? 'Bot' }}</div>
                    <div class="text-[11px] text-blue-300">Bot Hesap (Sen)</div>
                </div>
                @if($botUser && $botUser->avatar_url)
                    <img src="{{ $botUser->avatar_url }}" class="w-10 h-10 rounded-full object-cover">
                @else
                    <div class="w-10 h-10 rounded-full flex items-center justify-center bg-blue-900 text-white font-bold">{{ substr($botUser->name ?? '?', 0, 1) }}</div>
                @endif
            </div>
        </div>
        
        <div>
            <a href="{{ route('admin.fake_conversations') }}" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i> Geri Dön
            </a>
        </div>
    </div>

    <!-- Chat Messages -->
    <div class="flex-1 overflow-y-auto p-6 bg-[var(--bg-surface)] space-y-4" id="chat-container">
        @forelse($conversation->messages as $msg)
            @php
                $isMe = $msg->sender_id == $botUser->id;
            @endphp
            <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                <div class="max-w-[75%] rounded-2xl px-4 py-2.5 {{ $isMe ? 'bg-blue-600 text-white rounded-tr-sm' : 'bg-[var(--bg-card)] border border-[var(--border)] text-gray-200 rounded-tl-sm' }}">
                    @if($msg->type == 'audio')
                        <div class="flex items-center gap-2">
                            <i class="fas fa-play-circle text-xl"></i>
                            <span class="text-sm italic">Sesli mesaj (Admin panelinden dinlenemez)</span>
                        </div>
                    @else
                        <div class="text-sm whitespace-pre-wrap">{{ $msg->text }}</div>
                    @endif
                </div>
                <div class="text-[10px] mt-1 text-gray-500">
                    {{ $msg->created_at ? $msg->created_at->format('d M H:i') : '' }} 
                    @if($msg->is_locked) <i class="fas fa-lock ml-1 text-red-400"></i> @endif
                </div>
            </div>
        @empty
            <div class="h-full flex flex-col items-center justify-center opacity-50">
                <i class="fas fa-comments text-4xl mb-3"></i>
                <div class="text-sm">Henüz mesaj yok. İlk mesajı sen gönder!</div>
            </div>
        @endforelse
    </div>

    <!-- Chat Input -->
    <div class="card p-3 rounded-t-none border-t border-[var(--border)] flex-shrink-0">
        <form action="{{ route('admin.fake_conversations.reply', $conversation->id) }}" method="POST" class="flex items-end gap-3">
            @csrf
            <div class="flex-1">
                <textarea name="text" rows="1" class="form-input w-full resize-none py-3" placeholder="Mesajınızı yazın... (Bot hesap olarak gidecek)" required oninput="this.style.height = ''; this.style.height = Math.min(this.scrollHeight, 120) + 'px'"></textarea>
            </div>
            <button type="submit" class="btn bg-blue-600 hover:bg-blue-500 text-white h-11 px-6 rounded-xl flex items-center justify-center font-bold transition-all">
                <i class="fas fa-paper-plane mr-2"></i> Gönder
            </button>
        </form>
    </div>

</div>

<script>
    // Scroll to bottom
    const container = document.getElementById('chat-container');
    container.scrollTop = container.scrollHeight;
</script>

@endsection
