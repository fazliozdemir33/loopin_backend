@extends('admin.layout')

@section('title', 'Yasaklı Kullanıcılar')
@section('header', 'Yasaklı Kullanıcılar')
@section('breadcrumb', 'Kullanıcılar / Yasaklılar')

@section('content')

<div class="mb-5 flex items-center gap-2 text-xs" style="color: var(--text-muted);">
    <i class="fas fa-ban"></i>
    Toplam <strong class="text-white mx-1">{{ $users->total() }}</strong> yasaklı kullanıcı
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="data-table w-full">
            <thead>
                <tr>
                    <th class="text-left">Kullanıcı</th>
                    <th class="text-left">E-posta</th>
                    <th class="text-left">Cihaz ID</th>
                    <th class="text-left">Kayıt Tarihi</th>
                    <th class="text-right">İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="avatar-placeholder opacity-50"><i class="fas fa-user-slash text-xs"></i></div>
                            <div>
                                <div class="text-sm font-semibold text-white">{{ $user->getRawOriginal('name') ?: 'İsimsiz' }}</div>
                                <div class="text-xs" style="color: var(--text-muted);">ID: {{ $user->id }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="text-xs text-white">{{ $user->email ?: '—' }}</td>
                    <td>
                        @if($user->device_id)
                            <span class="badge badge-purple text-[10px] font-mono">{{ Str::limit($user->device_id, 16) }}</span>
                        @else
                            <span style="color: var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td class="text-xs text-white">{{ $user->created_at->format('d.m.Y H:i') }}</td>
                    <td class="text-right">
                        <form action="{{ route('admin.users.toggle_ban', $user->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success py-1.5 text-xs">
                                <i class="fas fa-unlock"></i> Banı Kaldır
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-16" style="color: var(--text-muted);">
                        <i class="fas fa-check-circle text-4xl mb-3 block text-green-500 opacity-50"></i>
                        Yasaklı kullanıcı yok
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="p-4 border-t flex items-center justify-between" style="border-color: var(--border);">
        <div class="text-xs" style="color: var(--text-muted);">Sayfa {{ $users->currentPage() }} / {{ $users->lastPage() }}</div>
        <div class="pagination">
            @if(!$users->onFirstPage()) <a href="{{ $users->previousPageUrl() }}"><i class="fas fa-chevron-left text-xs"></i></a> @else <span><i class="fas fa-chevron-left text-xs"></i></span> @endif
            @foreach($users->getUrlRange(max(1,$users->currentPage()-2), min($users->lastPage(),$users->currentPage()+2)) as $page => $url)
                @if($page == $users->currentPage()) <span class="active-page">{{ $page }}</span>
                @else <a href="{{ $url }}">{{ $page }}</a> @endif
            @endforeach
            @if($users->hasMorePages()) <a href="{{ $users->nextPageUrl() }}"><i class="fas fa-chevron-right text-xs"></i></a> @else <span><i class="fas fa-chevron-right text-xs"></i></span> @endif
        </div>
    </div>
    @endif
</div>

@endsection
