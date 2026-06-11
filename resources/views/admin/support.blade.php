@extends('admin.layout')

@section('title', 'Destek Talepleri')
@section('header', 'Destek Talepleri')
@section('breadcrumb', 'Aktivite / Destek')

@section('content')

<div class="mb-5 text-xs" style="color: var(--text-muted);">
    Toplam <strong class="text-white mx-1">{{ $tickets->total() }}</strong> destek talebi
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="data-table w-full">
            <thead>
                <tr>
                    <th class="text-left">#</th>
                    <th class="text-left">Kullanıcı</th>
                    <th class="text-left">Konu</th>
                    <th class="text-left">Mesaj</th>
                    <th class="text-left">Durum</th>
                    <th class="text-left">Tarih</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                <tr>
                    <td class="text-xs font-mono" style="color: var(--text-muted);">#{{ $ticket->id }}</td>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="avatar-placeholder"><i class="fas fa-user text-xs"></i></div>
                            <div>
                                <div class="text-xs font-semibold text-white">
                                    {{ $ticket->user ? ($ticket->user->getRawOriginal('name') ?: 'İsimsiz') : 'Silinmiş' }}
                                </div>
                                <div class="text-[10px]" style="color: var(--text-muted);">ID: {{ $ticket->user_id }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="text-xs font-semibold text-white">{{ $ticket->subject ?: '—' }}</div>
                    </td>
                    <td>
                        <div class="text-xs max-w-xs truncate" style="color: rgba(255,255,255,0.6);" title="{{ $ticket->message }}">
                            {{ Str::limit($ticket->message, 60) }}
                        </div>
                    </td>
                    <td>
                        @php $status = $ticket->status ?? 'open'; @endphp
                        @if($status == 'open')
                            <span class="badge badge-yellow"><i class="fas fa-circle" style="font-size:7px;"></i> Açık</span>
                        @elseif($status == 'closed')
                            <span class="badge badge-green"><i class="fas fa-check" style="font-size:9px;"></i> Kapalı</span>
                        @else
                            <span class="badge badge-blue">{{ ucfirst($status) }}</span>
                        @endif
                    </td>
                    <td class="text-xs" style="color: var(--text-muted);">{{ $ticket->created_at->format('d.m.Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-16" style="color: var(--text-muted);">
                        <i class="fas fa-headset text-4xl mb-3 block opacity-20"></i>
                        Destek talebi yok
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tickets->hasPages())
    <div class="p-4 border-t flex items-center justify-between" style="border-color: var(--border);">
        <div class="text-xs" style="color: var(--text-muted);">Sayfa {{ $tickets->currentPage() }} / {{ $tickets->lastPage() }}</div>
        <div class="pagination">
            @if(!$tickets->onFirstPage()) <a href="{{ $tickets->previousPageUrl() }}"><i class="fas fa-chevron-left text-xs"></i></a> @else <span><i class="fas fa-chevron-left text-xs"></i></span> @endif
            @foreach($tickets->getUrlRange(max(1,$tickets->currentPage()-2), min($tickets->lastPage(),$tickets->currentPage()+2)) as $page => $url)
                @if($page == $tickets->currentPage()) <span class="active-page">{{ $page }}</span>
                @else <a href="{{ $url }}">{{ $page }}</a> @endif
            @endforeach
            @if($tickets->hasMorePages()) <a href="{{ $tickets->nextPageUrl() }}"><i class="fas fa-chevron-right text-xs"></i></a> @else <span><i class="fas fa-chevron-right text-xs"></i></span> @endif
        </div>
    </div>
    @endif
</div>

@endsection
