<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loopn Admin Paneli</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 p-8">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-100">
        <h1 class="text-2xl font-bold mb-6 text-pink-600">Loopn Yönetim Paneli</h1>

        @if(session('success'))
            <div class="bg-green-50 text-green-700 p-4 rounded-lg mb-6 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <form action="/admin" method="POST" class="space-y-6">
            @csrf

            @foreach($settings as $setting)
                <div>
                    <label class="block text-sm font-semibold mb-2 capitalize">
                        {{ str_replace('_', ' ', $setting->key) }}
                    </label>
                    <input type="text" name="{{ $setting->key }}" value="{{ $setting->value }}" class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-2 border focus:ring-pink-500 focus:border-pink-500">
                    
                    @if($setting->key == 'max_distance_km')
                        <p class="text-xs text-gray-500 mt-1">Keşfet ekranında kullanıcıların görebileceği maksimum mesafe (km cinsinden).</p>
                    @endif
                </div>
            @endforeach

            <button type="submit" class="bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 px-6 rounded-lg w-full transition">
                Ayarları Kaydet
            </button>
        </form>
    </div>
</body>
</html>
