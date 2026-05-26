@extends('admin.layout')

@section('header', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
        <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-2xl mr-4">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <div class="text-gray-500 text-sm font-semibold">Toplam Kullanıcı</div>
            <div class="text-3xl font-bold text-gray-800">{{ number_format($userCount) }}</div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
        <div class="w-14 h-14 rounded-full bg-pink-50 text-pink-500 flex items-center justify-center text-2xl mr-4">
            <i class="fas fa-heart"></i>
        </div>
        <div>
            <div class="text-gray-500 text-sm font-semibold">Eşleşme / Sohbet</div>
            <div class="text-3xl font-bold text-gray-800">{{ number_format($convCount) }}</div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
        <div class="w-14 h-14 rounded-full bg-purple-50 text-purple-500 flex items-center justify-center text-2xl mr-4">
            <i class="fas fa-comment-dots"></i>
        </div>
        <div>
            <div class="text-gray-500 text-sm font-semibold">Toplam Mesaj</div>
            <div class="text-3xl font-bold text-gray-800">{{ number_format($messageCount) }}</div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 max-w-3xl">
    <h2 class="text-xl font-bold mb-6 text-gray-800 border-b pb-4">Uygulama Ayarları</h2>

    <form action="{{ route('admin.dashboard') }}" method="POST" class="space-y-6">
        @csrf

        @foreach($settings as $setting)
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2 capitalize">
                    {{ str_replace('_', ' ', $setting->key) }}
                </label>
                <input type="text" name="{{ $setting->key }}" value="{{ $setting->value }}" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 border focus:ring-pink-500 focus:border-pink-500 transition outline-none text-lg">
                
                @if($setting->key == 'max_distance_km')
                    <p class="text-sm text-gray-500 mt-2"><i class="fas fa-info-circle mr-1"></i> Keşfet ekranında kullanıcıların görebileceği maksimum mesafe (km cinsinden).</p>
                @endif
            </div>
        @endforeach

        <div class="pt-4">
            <button type="submit" class="bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 px-8 rounded-lg transition shadow-md flex items-center">
                <i class="fas fa-save mr-2"></i> Ayarları Kaydet
            </button>
        </div>
    </form>
</div>
@endsection
