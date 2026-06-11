@extends('admin.layout')

@section('title', 'Sahte Hesap Oluştur')
@section('header', 'Sahte Hesaplar')
@section('breadcrumb', 'Kullanıcılar / Sahte Hesaplar')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<div class="card p-6 max-w-4xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <div class="stat-icon" style="background: rgba(168,85,247,0.12);">
            <i class="fas fa-user-secret" style="color: #a855f7;"></i>
        </div>
        <div>
            <div class="text-sm font-bold text-white">Sahte Hesap Oluştur</div>
            <div class="text-xs" style="color: var(--text-muted);">Uygulamada görünecek manuel bot hesaplar yaratın</div>
        </div>
    </div>

    <form action="{{ route('admin.fake_accounts.store') }}" method="POST" class="space-y-5">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: rgba(255,255,255,0.5);">İsim</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color: var(--text-muted);"></i>
                    <input type="text" name="name" class="form-input pl-9" placeholder="Örn: Ayşe" required>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: rgba(255,255,255,0.5);">Yaş</label>
                <div class="relative">
                    <i class="fas fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color: var(--text-muted);"></i>
                    <input type="number" name="age" class="form-input pl-9" placeholder="Örn: 24" required min="18" max="99">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: rgba(255,255,255,0.5);">Boy (cm) (Opsiyonel)</label>
                <div class="relative">
                    <i class="fas fa-arrows-alt-v absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color: var(--text-muted);"></i>
                    <input type="number" name="height" class="form-input pl-9" placeholder="Örn: 175" min="100" max="250">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: rgba(255,255,255,0.5);">Cinsiyet</label>
                <select name="gender" class="form-input" required>
                    <option value="Kadın">Kadın</option>
                    <option value="Erkek">Erkek</option>
                    <option value="Diğer">Diğer</option>
                    <option value="Belirtmek İstemiyorum">Belirtmek İstemiyorum</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: rgba(255,255,255,0.5);">İlişki Hedefi (Opsiyonel)</label>
                <select name="relationship_goal" class="form-input">
                    <option value="">Seçiniz...</option>
                    <option value="Uzun soluklu bir hikaye">Uzun soluklu bir hikaye</option>
                    <option value="Ciddi ama akışta">Ciddi ama akışta</option>
                    <option value="Yeni arkadaşlıklar ve sohbet">Yeni arkadaşlıklar ve sohbet</option>
                    <option value="Sadece anı yaşamak istiyorum">Sadece anı yaşamak istiyorum</option>
                    <option value="Henüz emin değilim, keşfediyorum">Henüz emin değilim, keşfediyorum</option>
                    <option value="Flörtöz ve eğlenceli bir bağ">Flörtöz ve eğlenceli bir bağ</option>
                    <option value="Önce kahve, sonrasına bakarız">Önce kahve, sonrasına bakarız</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: rgba(255,255,255,0.5);">Burç (Opsiyonel)</label>
                <select name="zodiac_sign" class="form-input">
                    <option value="">Seçiniz...</option>
                    <option value="Koç">Koç</option>
                    <option value="Boğa">Boğa</option>
                    <option value="İkizler">İkizler</option>
                    <option value="Yengeç">Yengeç</option>
                    <option value="Aslan">Aslan</option>
                    <option value="Başak">Başak</option>
                    <option value="Terazi">Terazi</option>
                    <option value="Akrep">Akrep</option>
                    <option value="Yay">Yay</option>
                    <option value="Oğlak">Oğlak</option>
                    <option value="Kova">Kova</option>
                    <option value="Balık">Balık</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: rgba(255,255,255,0.5);">Avatar URL (Opsiyonel)</label>
                <div class="relative">
                    <i class="fas fa-image absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color: var(--text-muted);"></i>
                    <input type="url" name="avatar_url" class="form-input pl-9" placeholder="https://example.com/avatar.jpg">
                </div>
                <p class="text-[11px] mt-1.5" style="color: var(--text-muted);">Unsplash veya benzeri bir yerden resim linki koyabilirsiniz.</p>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: rgba(255,255,255,0.5);">Ekstra Fotoğraflar (Opsiyonel)</label>
                <textarea name="extra_photos" class="form-input font-mono text-[11px]" rows="3" placeholder="https://example.com/photo1.jpg&#10;https://example.com/photo2.jpg"></textarea>
                <p class="text-[11px] mt-1.5" style="color: var(--text-muted);">Her satıra bir fotoğraf linki gelecek şekilde ekleyin. Bu fotoğraflar kullanıcının profil galerisinde görünecektir.</p>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: rgba(255,255,255,0.5);">Biyografi (Opsiyonel)</label>
                <textarea name="bio" class="form-input" rows="3" placeholder="Kendinizden bahsedin..."></textarea>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: rgba(255,255,255,0.5);">Konum Seçin</label>
                <div id="map" class="w-full h-[300px] rounded-xl border" style="border-color: var(--border); z-index: 10;"></div>
                <input type="hidden" name="latitude" id="lat" value="41.0082">
                <input type="hidden" name="longitude" id="lng" value="28.9784">
                <p class="text-[11px] mt-1.5" style="color: var(--text-muted);">Harita üzerinden tıklayarak veya imleci sürükleyerek konumu belirleyin. (Varsayılan: İstanbul)</p>
            </div>
        </div>

        <button type="submit" class="btn btn-primary-sm w-full justify-center mt-4 py-3 text-sm">
            <i class="fas fa-plus"></i> Hesabı Oluştur
        </button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var map = L.map('map').setView([41.0082, 28.9784], 10);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        var marker = L.marker([41.0082, 28.9784], {draggable: true}).addTo(map);

        function updateInputs(lat, lng) {
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
        }

        marker.on('dragend', function (e) {
            var coords = e.target.getLatLng();
            updateInputs(coords.lat, coords.lng);
        });

        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateInputs(e.latlng.lat, e.latlng.lng);
        });
    });
</script>
@endsection
