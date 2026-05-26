@extends('admin.layout')

@section('header', 'Kullanıcılar')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kullanıcı</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">İletişim</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Durum</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Anahtar</th>
                    <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">İşlem</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $user)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                @if($user->getRawOriginal('avatar_url'))
                                    <img class="h-10 w-10 rounded-full object-cover" src="{{ $user->getRawOriginal('avatar_url') }}" alt="">
                                @else
                                    <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                                        <i class="fas fa-user"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-bold text-gray-900">{{ $user->getRawOriginal('name') ?: 'İsimsiz' }}</div>
                                <div class="text-sm text-gray-500">ID: {{ $user->id }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $user->email ?? 'Bilinmiyor' }}</div>
                        <div class="text-xs text-gray-500 flex items-center mt-1">
                            @if($user->provider == 'google')
                                <i class="fab fa-google text-red-500 mr-1"></i> Google
                            @elseif($user->provider == 'apple')
                                <i class="fab fa-apple text-gray-800 mr-1"></i> Apple
                            @elseif($user->provider)
                                <i class="fas fa-sign-in-alt mr-1"></i> {{ ucfirst($user->provider) }}
                            @else
                                <i class="fas fa-envelope mr-1"></i> Klasik
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($user->is_banned)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Yasaklı</span>
                        @elseif($user->getRawOriginal('avatar_url'))
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Eksik Profil</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <form action="{{ route('admin.users.wallet', $user->id) }}" method="POST" class="flex items-center">
                            @csrf
                            <input type="number" name="wallet_balance" value="{{ $user->wallet_balance ?? 0 }}" class="w-16 border-gray-300 rounded shadow-sm px-2 py-1 text-center border focus:ring-pink-500 focus:border-pink-500 mr-2 outline-none">
                            <button type="submit" class="text-green-600 hover:text-green-800 bg-green-50 hover:bg-green-100 px-2 py-1 rounded transition" title="Kaydet">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="{{ route('admin.user_messages', $user->id) }}" class="text-pink-600 hover:text-pink-900 bg-pink-50 hover:bg-pink-100 px-3 py-2 rounded-md transition font-semibold">
                                <i class="fas fa-comment-dots mr-1"></i> Mesajlar
                            </a>
                            <form action="{{ route('admin.users.toggle_ban', $user->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-2 rounded-md transition font-semibold {{ $user->is_banned ? 'bg-green-50 text-green-600 hover:bg-green-100 hover:text-green-900' : 'bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-900' }}">
                                    <i class="fas {{ $user->is_banned ? 'fa-unlock' : 'fa-ban' }} mr-1"></i> 
                                    {{ $user->is_banned ? 'Banı Kaldır' : 'Banla' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    @if($users->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
