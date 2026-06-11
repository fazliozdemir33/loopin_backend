@extends('admin.layout')

@section('title', 'Sohbetler')
@section('header', 'Sohbet Yönetimi')
@section('breadcrumb', 'Aktivite / Sohbetler')

@section('topbar-actions')
<form method="GET" action="{{ route('admin.conversations') }}" class="flex items-center gap-2">
    <select name="status" class="form-input py-2 text-xs" style="width: 140px;">
        <option value="">Tüm Sohbetler</option>
        <option value="unlocked" {{ request('status')=='unlocked'?'selected':'' }}>Kilit Açık</option>
        <option value="locked" {{ request('status')=='locked'?'selected':'' }}>Kilitli</option>
    </select>
    <button type="submit" class="btn btn-primary-sm"><i class="fas fa-filter"></i></button>
</form>
@endsection

@section('content')

<div class="mb-5 text-xs" style="color: var(--text-muted);">
    Toplam <strong class="text-white mx-1">{{ $conversations->total() }}</strong> sohbet
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="data-table w-full">
            <thead>
                <tr>
                    <th class="text-left">Kullanıcı 1</th>
                    <th class="text-left">Kullanıcı 2</th>
                    <th class="text-left">Mesaj Sayısı</th>
                    <th class="text-left">Kilit Durumu</th>
                    <th class="text-left">Son Aktivite</th>
                    <th class="text-right">İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($conversations as $conv)
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            @if($conv->user1 && $conv->user1->getRawOriginal('avatar_url'))
                                <img src="{{ $conv->user1->getRawOriginal('avatar_url') }}" class="avatar">
                            @else
                                <div class="avatar-placeholder"><i class="fas fa-user text-xs"></i></div>
                            @endif
                            <div>
                                <div class="text-xs font-semibold text-white">{{ $conv->user1 ? ($conv->user1->getRawOriginal('name') ?: 'İsimsiz') : 'Silinmiş' }}</div>
                                @if($conv->user1)<div class="text-[10px]" style="color: var(--text-muted);">ID: {{ $conv->user1_id }}</div>@endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            @if($conv->user2 && $conv->user2->getRawOriginal('avatar_url'))
                                <img src="{{ $conv->user2->getRawOriginal('avatar_url') }}" class="avatar">
                            @else
                                <div class="avatar-placeholder"><i class="fas fa-user text-xs"></i></div>
                            @endif
                            <div>
                                <div class="text-xs font-semibold text-white">{{ $conv->user2 ? ($conv->user2->getRawOriginal('name') ?: 'İsimsiz') : 'Silinmiş' }}</div>
                                @if($conv->user2)<div class="text-[10px]" style="color: var(--text-muted);">ID: {{ $conv->user2_id }}</div>@endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-blue">
                            <i class="fas fa-message" style="font-size:9px;"></i>
                            {{ $conv->message_count ?? 0 }} Mesaj
                        </span>
                    </td>
                    <td>
                        @if($conv->is_unlocked)
                            <span class="badge badge-green"><i class="fas fa-lock-open" style="font-size:9px;"></i> Açık</span>
                        @else
                            <span class="badge badge-yellow"><i class="fas fa-lock" style="font-size:9px;"></i> Kilitli</span>
                        @endif
                    </td>
                    <td class="text-xs" style="color: var(--text-muted);">{{ $conv->updated_at->diffForHumans() }}</td>
                    <td class="text-right">
                        @if($conv->user1)
                        <a href="{{ route('admin.user_messages', $conv->user1_id) }}" class="btn btn-ghost py-1.5 text-xs">
                            <i class="fas fa-eye"></i> Görüntüle
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-16" style="color: var(--text-muted);">
                        <i class="fas fa-comments text-4xl mb-3 block opacity-20"></i>
                        Sohbet bulunamadı
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($conversations->hasPages())
    <div class="p-4 border-t flex items-center justify-between" style="border-color: var(--border);">
        <div class="text-xs" style="color: var(--text-muted);">Sayfa {{ $conversations->currentPage() }} / {{ $conversations->lastPage() }}</div>
        <div class="pagination">
            @if(!$conversations->onFirstPage()) <a href="{{ $conversations->previousPageUrl() }}"><i class="fas fa-chevron-left text-xs"></i></a> @else <span><i class="fas fa-chevron-left text-xs"></i></span> @endif
            @foreach($conversations->getUrlRange(max(1,$conversations->currentPage()-2), min($conversations->lastPage(),$conversations->currentPage()+2)) as $page => $url)
                @if($page == $conversations->currentPage()) <span class="active-page">{{ $page }}</span>
                @else <a href="{{ $url }}">{{ $page }}</a> @endif
            @endforeach
            @if($conversations->hasMorePages()) <a href="{{ $conversations->nextPageUrl() }}"><i class="fas fa-chevron-right text-xs"></i></a> @else <span><i class="fas fa-chevron-right text-xs"></i></span> @endif
        </div>
    </div>
    @endif
</div>

@endsection
