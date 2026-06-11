@extends('admin.layout')

@section('title', 'Kullanıcılar')
@section('header', 'Kullanıcı Yönetimi')
@section('breadcrumb', 'Tüm Kullanıcılar')

@section('topbar-actions')
<form method="GET" action="{{ route('admin.users') }}" class="flex items-center gap-2">
    <div class="relative">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color: var(--text-muted);"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ad, e-posta, ID ara..."
            class="form-input pl-9 py-2 text-xs" style="width: 220px;">
    </div>
    <select name="status" class="form-input py-2 text-xs" style="width: 130px;">
        <option value="">Tüm Durumlar</option>
        <option value="active" {{ request('status')=='active'?'selected':'' }}>Aktif</option>
        <option value="suspended" {{ request('status')=='suspended'?'selected':'' }}>Askıda</option>
        <option value="banned" {{ request('status')=='banned'?'selected':'' }}>Yasaklı</option>
        <option value="incomplete" {{ request('status')=='incomplete'?'selected':'' }}>Eksik Profil</option>
    </select>
    <select name="provider" class="form-input py-2 text-xs" style="width: 120px;">
        <option value="">Tüm Giriş</option>
        <option value="google" {{ request('provider')=='google'?'selected':'' }}>Google</option>
        <option value="apple" {{ request('provider')=='apple'?'selected':'' }}>Apple</option>
    </select>
    <button type="submit" class="btn btn-primary-sm"><i class="fas fa-filter"></i></button>
    @if(request('search') || request('status') || request('provider'))
        <a href="{{ route('admin.users') }}" class="btn btn-ghost text-xs"><i class="fas fa-xmark"></i></a>
    @endif
</form>
@endsection

@section('content')

{{-- Summary bar --}}
<div class="flex items-center gap-2 mb-5 text-xs" style="color: var(--text-muted);">
    <i class="fas fa-users"></i>
    Toplam <strong class="text-white mx-1">{{ $users->total() }}</strong> kullanıcı &bull;
    <span class="text-white mx-1">{{ $users->firstItem() }}–{{ $users->lastItem() }}</span> gösteriliyor
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="data-table w-full">
            <thead>
                <tr>
                    <th class="text-left">Kullanıcı</th>
                    <th class="text-left">İletişim</th>
                    <th class="text-left">Profil</th>
                    <th class="text-left">Durum</th>
                    <th class="text-left">Anahtar</th>
                    <th class="text-left">Kayıt</th>
                    <th class="text-right">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    {{-- User --}}
                    <td>
                        <div class="flex items-center gap-3">
                            @if($user->getRawOriginal('avatar_url'))
                                <img src="{{ $user->getRawOriginal('avatar_url') }}" class="avatar">
                            @else
                                <div class="avatar-placeholder"><i class="fas fa-user text-xs"></i></div>
                            @endif
                            <div>
                                <div class="text-sm font-semibold text-white">{{ $user->getRawOriginal('name') ?: 'İsimsiz' }}</div>
                                <div class="text-xs" style="color: var(--text-muted);">ID: {{ $user->id }}</div>
                            </div>
                        </div>
                    </td>

                    {{-- Contact --}}
                    <td>
                        <div class="text-xs text-white">{{ $user->email ?: '—' }}</div>
                        <div class="text-xs mt-1 flex items-center gap-1" style="color: var(--text-muted);">
                            @if($user->provider == 'google')
                                <i class="fab fa-google text-red-400"></i> Google
                            @elseif($user->provider == 'apple')
                                <i class="fab fa-apple"></i> Apple
                            @else
                                <i class="fas fa-envelope"></i> Klasik
                            @endif
                        </div>
                    </td>

                    {{-- Profile info --}}
                    <td>
                        <div class="text-xs space-y-0.5">
                            @php $rawGender = $user->getRawOriginal('gender'); @endphp
                            @if($rawGender)
                                <div class="flex items-center gap-1" style="color: rgba(255,255,255,0.55);">
                                    <i class="fas fa-{{ $rawGender=='Erkek'?'mars':'venus' }}" style="color: {{ $rawGender=='Erkek'?'#60a5fa':'#f472b6' }};"></i>
                                    {{ $rawGender }}
                                    @if($user->age) &bull; {{ $user->age }} yaş @endif
                                </div>
                            @endif
                            @if($user->last_seen_at)
                                <div style="color: var(--text-muted);">
                                    @if($user->is_online)
                                        <span style="color: #4ade80;"><i class="fas fa-circle" style="font-size:7px;"></i> Online</span>
                                    @else
                                        <i class="fas fa-clock" style="font-size:9px;"></i> {{ $user->last_seen_at->diffForHumans() }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    </td>

                    {{-- Status --}}
                    <td>
                        @if($user->is_banned)
                            <span class="badge badge-red"><i class="fas fa-ban" style="font-size:9px;"></i> Yasaklı</span>
                        @elseif($user->is_suspended)
                            <span class="badge badge-yellow"><i class="fas fa-pause" style="font-size:9px;"></i> Askıda</span>
                        @elseif($user->getRawOriginal('avatar_url'))
                            <span class="badge badge-green"><i class="fas fa-circle" style="font-size:7px;"></i> Aktif</span>
                        @else
                            <span class="badge badge-yellow"><i class="fas fa-circle-half-stroke" style="font-size:9px;"></i> Eksik</span>
                        @endif
                    </td>

                    {{-- Wallet --}}
                    <td>
                        <form action="{{ route('admin.users.wallet', $user->id) }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            <input type="number" name="wallet_balance" value="{{ $user->wallet_balance ?? 0 }}"
                                min="0" class="form-input text-center text-xs py-1.5" style="width: 60px; padding: 6px 8px;">
                            <button type="submit" class="btn btn-success py-1.5 px-2.5"><i class="fas fa-check text-xs"></i></button>
                        </form>
                    </td>

                    {{-- Registration date --}}
                    <td>
                        @php
                            $createdTR = $user->created_at->setTimezone('Europe/Istanbul');
                        @endphp
                        <div class="text-xs text-white">{{ $createdTR->format('d.m.Y') }}</div>
                        <div class="text-xs" style="color: var(--text-muted);">{{ $createdTR->format('H:i') }}</div>
                    </td>

                    {{-- Actions --}}
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" class="btn btn-ghost py-1.5 text-xs text-blue-400"
                                onclick="document.getElementById('editModal-{{ $user->id }}').classList.remove('hidden')">
                                <i class="fas fa-edit"></i> Düzenle
                            </button>
                            
                            {{-- Edit Modal --}}
                            <div id="editModal-{{ $user->id }}" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center backdrop-blur-sm">
                                <div class="card w-full max-w-md p-6 relative text-left whitespace-normal">
                                    <button type="button" onclick="document.getElementById('editModal-{{ $user->id }}').classList.add('hidden')" 
                                        class="absolute top-4 right-4 text-gray-400 hover:text-white transition-colors">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <h3 class="text-lg font-semibold text-white mb-4">Kullanıcı Düzenle</h3>
                                    
                                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-4">
                                        @csrf
                                        <div>
                                            <label class="block text-xs font-medium text-gray-300 mb-1">İsim</label>
                                            <input type="text" name="name" value="{{ $user->getRawOriginal('name') }}" class="form-input w-full">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-300 mb-1">E-posta</label>
                                            <input type="email" name="email" value="{{ $user->getRawOriginal('email') }}" class="form-input w-full">
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-300 mb-1">Cinsiyet</label>
                                                <select name="gender" class="form-input w-full">
                                                    <option value="Kadın" {{ $user->gender == 'Kadın' ? 'selected' : '' }}>Kadın</option>
                                                    <option value="Erkek" {{ $user->gender == 'Erkek' ? 'selected' : '' }}>Erkek</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-300 mb-1">Yaş</label>
                                                <input type="number" name="age" value="{{ $user->age }}" class="form-input w-full">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-300 mb-1">Avatar URL</label>
                                            <input type="url" name="avatar_url" value="{{ $user->getRawOriginal('avatar_url') }}" class="form-input w-full">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-300 mb-1">Biyografi</label>
                                            <textarea name="bio" class="form-input w-full" rows="2">{{ $user->bio }}</textarea>
                                        </div>
                                        <div class="flex justify-end gap-2 mt-2">
                                            <button type="button" class="btn btn-ghost" onclick="document.getElementById('editModal-{{ $user->id }}').classList.add('hidden')">İptal</button>
                                            <button type="submit" class="btn btn-primary-sm">Kaydet</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <a href="{{ route('admin.user_messages', $user->id) }}" class="btn btn-ghost py-1.5 text-xs">
                                <i class="fas fa-comment-dots"></i> Mesajlar
                            </a>
                            @if($user->is_suspended)
                                <form action="{{ route('admin.users.toggle_suspend', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn py-1.5 text-xs btn-success"
                                        title="Askıyı Kaldır">
                                        <i class="fas fa-play"></i> Aktif Et
                                    </button>
                                </form>
                            @else
                                <button type="button" class="btn py-1.5 text-xs btn-ghost"
                                    title="Askıya Al"
                                    style="border-color: rgba(234,179,8,0.3); color: #facc15;"
                                    onclick="document.getElementById('suspendModal-{{ $user->id }}').classList.remove('hidden')">
                                    <i class="fas fa-pause"></i> Askıya Al
                                </button>
                                
                                {{-- Suspend Modal --}}
                                <div id="suspendModal-{{ $user->id }}" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center backdrop-blur-sm">
                                    <div class="card w-full max-w-md p-6 relative">
                                        <button onclick="document.getElementById('suspendModal-{{ $user->id }}').classList.add('hidden')" 
                                            class="absolute top-4 right-4 text-gray-400 hover:text-white transition-colors">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <h3 class="text-lg font-semibold text-white mb-4">Kullanıcıyı Askıya Al</h3>
                                        <div class="text-sm mb-4" style="color: var(--text-muted);">
                                            <strong class="text-white">{{ $user->name ?: 'İsimsiz' }}</strong> isimli kullanıcıyı askıya almak üzeresiniz. Lütfen bir sebep belirtin. Bu sebep kullanıcıya gösterilecektir.
                                        </div>
                                        <form action="{{ route('admin.users.toggle_suspend', $user->id) }}" method="POST">
                                            @csrf
                                            <div class="mb-4">
                                                <label class="block text-xs font-medium text-gray-300 mb-1">Askıya Alınma Sebebi</label>
                                                <textarea name="suspension_reason" rows="3" class="form-input w-full" required placeholder="Örn: Topluluk kurallarını ihlal eden davranışlar..."></textarea>
                                            </div>
                                            <div class="flex justify-end gap-2">
                                                <button type="button" class="btn btn-ghost" onclick="document.getElementById('suspendModal-{{ $user->id }}').classList.add('hidden')">İptal</button>
                                                <button type="submit" class="btn btn-danger">Askıya Al</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif
                            <form action="{{ route('admin.users.toggle_ban', $user->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn py-1.5 text-xs {{ $user->is_banned ? 'btn-success' : 'btn-danger' }}">
                                    <i class="fas {{ $user->is_banned ? 'fa-unlock' : 'fa-ban' }}"></i>
                                    {{ $user->is_banned ? 'Banı Kaldır' : 'Banla' }}
                                </button>
                            </form>

                            {{-- Delete Account Button --}}
                            <button type="button"
                                class="btn py-1.5 text-xs btn-danger"
                                style="background: rgba(220,38,38,0.15); border-color: rgba(220,38,38,0.4); color: #f87171;"
                                onclick="document.getElementById('deleteModal-{{ $user->id }}').classList.remove('hidden')">
                                <i class="fas fa-trash-alt"></i> Sil
                            </button>

                            {{-- Delete Confirmation Modal --}}
                            <div id="deleteModal-{{ $user->id }}" class="fixed inset-0 bg-black/60 hidden z-50 flex items-center justify-center backdrop-blur-sm">
                                <div class="card w-full max-w-sm p-6 relative text-left">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div style="width:40px;height:40px;border-radius:50%;background:rgba(220,38,38,0.15);display:flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-trash-alt" style="color:#f87171;"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-bold text-white">Hesabı Kalıcı Sil</h3>
                                            <p class="text-xs" style="color:var(--text-muted);">Bu işlem geri alınamaz!</p>
                                        </div>
                                    </div>
                                    <p class="text-sm mb-5" style="color:rgba(255,255,255,0.7);">
                                        <strong class="text-white">{{ $user->getRawOriginal('name') ?: 'Bu kullanıcı' }}</strong>
                                        adlı kullanıcı ve tüm mesajları, sohbetleri, raporları kalıcı olarak silinecek. Devam etmek istiyor musunuz?
                                    </p>
                                    <div class="flex justify-end gap-2">
                                        <button type="button" class="btn btn-ghost"
                                            onclick="document.getElementById('deleteModal-{{ $user->id }}').classList.add('hidden')">
                                            İptal
                                        </button>
                                        <form action="{{ route('admin.users.delete', $user->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash-alt"></i> Evet, Kalıcı Sil
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-16" style="color: var(--text-muted);">
                        <i class="fas fa-user-slash text-4xl mb-3 block opacity-30"></i>
                        Kullanıcı bulunamadı
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
    <div class="p-4 border-t flex items-center justify-between" style="border-color: var(--border);">
        <div class="text-xs" style="color: var(--text-muted);">
            Sayfa {{ $users->currentPage() }} / {{ $users->lastPage() }}
        </div>
        <div class="pagination">
            @if($users->onFirstPage())
                <span><i class="fas fa-chevron-left text-xs"></i></span>
            @else
                <a href="{{ $users->previousPageUrl() }}" class="flex items-center justify-center"><i class="fas fa-chevron-left text-xs"></i></a>
            @endif

            @foreach($users->getUrlRange(max(1, $users->currentPage()-2), min($users->lastPage(), $users->currentPage()+2)) as $page => $url)
                @if($page == $users->currentPage())
                    <span class="active-page">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach

            @if($users->hasMorePages())
                <a href="{{ $users->nextPageUrl() }}" class="flex items-center justify-center"><i class="fas fa-chevron-right text-xs"></i></a>
            @else
                <span><i class="fas fa-chevron-right text-xs"></i></span>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection
