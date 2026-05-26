@extends('admin.layout')

@section('header')
<div class="flex items-center">
    <a href="{{ route('admin.users') }}" class="text-gray-500 hover:text-gray-700 mr-4">
        <i class="fas fa-arrow-left"></i>
    </a>
    {{ $user->name ?: 'İsimsiz' }} - Sohbetleri
</div>
@endsection

@section('content')
<div class="space-y-6">
    @forelse($conversations as $conv)
        @php
            $otherUser = $conv->user1_id == $user->id ? $conv->user2 : $conv->user1;
        @endphp
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Conversation Header -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <div class="flex items-center">
                    @if($otherUser && $otherUser->avatar_url)
                        <img class="h-10 w-10 rounded-full object-cover border-2 border-white shadow-sm" src="{{ $otherUser->avatar_url }}" alt="">
                    @else
                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 border-2 border-white shadow-sm">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif
                    <div class="ml-3">
                        <div class="text-sm font-bold text-gray-900">Sohbet: {{ $otherUser ? $otherUser->name : 'Silinmiş Kullanıcı' }}</div>
                        <div class="text-xs text-gray-500">Son güncelleme: {{ $conv->updated_at->diffForHumans() }}</div>
                    </div>
                </div>
                <div>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $conv->is_unlocked ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $conv->is_unlocked ? 'Kilit Açık' : 'Kilitli' }}
                    </span>
                    <span class="ml-2 px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ $conv->messages->count() }} / {{ $conv->message_count }} Mesaj
                    </span>
                </div>
            </div>
            
            <!-- Conversation Messages -->
            <div class="p-6 bg-gray-50/30">
                @if($conv->messages->count() > 0)
                    <div class="space-y-4">
                        @foreach($conv->messages->reverse() as $msg)
                            @php
                                $isOwner = $msg->sender_id == $user->id;
                            @endphp
                            
                            <div class="flex {{ $isOwner ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-[70%] rounded-2xl px-4 py-2 {{ $isOwner ? 'bg-pink-600 text-white rounded-tr-sm' : 'bg-white text-gray-800 border border-gray-100 shadow-sm rounded-tl-sm' }}">
                                    <div class="text-[15px]">{{ $msg->text }}</div>
                                    <div class="text-[10px] mt-1 {{ $isOwner ? 'text-pink-200' : 'text-gray-400' }} text-right">
                                        {{ $msg->created_at->format('H:i') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-comment-slash text-3xl mb-2 text-gray-300"></i>
                        <p>Henüz mesaj yok</p>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300 text-3xl">
                <i class="fas fa-inbox"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-1">Sohbet Bulunamadı</h3>
            <p class="text-gray-500">Bu kullanıcının henüz hiçbir eşleşmesi veya mesajlaşması yok.</p>
        </div>
    @endforelse
</div>
@endsection
