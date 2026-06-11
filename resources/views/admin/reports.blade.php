@extends('admin.layout')

@section('title', 'Şikayetler')
@section('header', 'Şikayet Yönetimi')
@section('breadcrumb', 'Aktivite / Şikayetler')

@section('content')

<div class="mb-5 text-xs" style="color: var(--text-muted);">
    Toplam <strong class="text-white mx-1">{{ $reports->total() }}</strong> şikayet
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="data-table w-full">
            <thead>
                <tr>
                    <th class="text-left">Şikayet Eden</th>
                    <th class="text-left">Şikayet Edilen</th>
                    <th class="text-left">Sebep</th>
                    <th class="text-left">Tarih</th>
                    <th class="text-right">İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="avatar-placeholder"><i class="fas fa-user text-xs"></i></div>
                            <div>
                                <div class="text-xs font-semibold text-white">{{ $report->reporter ? ($report->reporter->getRawOriginal('name') ?: 'İsimsiz') : 'Silinmiş' }}</div>
                                <div class="text-[10px]" style="color: var(--text-muted);">ID: {{ $report->reporter_id }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="avatar-placeholder" style="background: rgba(239,68,68,0.2);"><i class="fas fa-flag text-xs text-red-400"></i></div>
                            <div>
                                <div class="text-xs font-semibold text-white">{{ $report->reported ? ($report->reported->getRawOriginal('name') ?: 'İsimsiz') : 'Silinmiş' }}</div>
                                <div class="text-[10px]" style="color: var(--text-muted);">ID: {{ $report->reported_id }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-red text-[10px]">{{ $report->reason ?: 'Belirtilmemiş' }}</span>
                    </td>
                    <td class="text-xs" style="color: var(--text-muted);">{{ \Carbon\Carbon::parse($report->created_at)->format('d.m.Y H:i') }}</td>
                    <td class="text-right">
                        @if($report->reported && !$report->reported->is_banned)
                        <form action="{{ route('admin.users.toggle_ban', $report->reported_id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger py-1.5 text-xs">
                                <i class="fas fa-ban"></i> Banla
                            </button>
                        </form>
                        @elseif($report->reported && $report->reported->is_banned)
                            <span class="badge badge-red">Zaten Yasaklı</span>
                        @else
                            <span style="color: var(--text-muted);">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-16" style="color: var(--text-muted);">
                        <i class="fas fa-shield-check text-4xl mb-3 block text-green-500 opacity-30"></i>
                        Açık şikayet yok
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($reports->hasPages())
    <div class="p-4 border-t flex items-center justify-between" style="border-color: var(--border);">
        <div class="text-xs" style="color: var(--text-muted);">Sayfa {{ $reports->currentPage() }} / {{ $reports->lastPage() }}</div>
        <div class="pagination">
            @if(!$reports->onFirstPage()) <a href="{{ $reports->previousPageUrl() }}"><i class="fas fa-chevron-left text-xs"></i></a> @else <span><i class="fas fa-chevron-left text-xs"></i></span> @endif
            @foreach($reports->getUrlRange(max(1,$reports->currentPage()-2), min($reports->lastPage(),$reports->currentPage()+2)) as $page => $url)
                @if($page == $reports->currentPage()) <span class="active-page">{{ $page }}</span>
                @else <a href="{{ $url }}">{{ $page }}</a> @endif
            @endforeach
            @if($reports->hasMorePages()) <a href="{{ $reports->nextPageUrl() }}"><i class="fas fa-chevron-right text-xs"></i></a> @else <span><i class="fas fa-chevron-right text-xs"></i></span> @endif
        </div>
    </div>
    @endif
</div>

@endsection
