@extends('layouts.app')

@section('title', 'Sayımlar')
@section('page_title', 'Stok Sayımları')

@php
    $statusLabels = [
        'draft' => 'Taslak',
        'in_progress' => 'Devam Ediyor',
        'completed' => 'Tamamlandı',
        'cancelled' => 'İptal Edildi',
    ];
    $statusColors = [
        'draft' => 'bg-slate-200 text-slate-600',
        'in_progress' => 'bg-amber-100 text-amber-700',
        'completed' => 'bg-emerald-100 text-emerald-700',
        'cancelled' => 'bg-red-100 text-red-700',
    ];
@endphp

@section('content')

    <div class="flex justify-between items-center mb-4">
        <div></div>
        <a href="{{ route('stock-counts.create') }}" class="bg-slate-900 text-white rounded px-4 py-2 text-sm font-medium hover:bg-slate-800">
            Yeni Sayım
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 border-b bg-slate-50">
                <tr>
                    <th class="px-4 py-2 font-medium">Sayım</th>
                    <th class="px-4 py-2 font-medium">Şube / Depo</th>
                    <th class="px-4 py-2 font-medium">Tarih Aralığı</th>
                    <th class="px-4 py-2 font-medium">Durum</th>
                    <th class="px-4 py-2 font-medium"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stockCounts as $count)
                    <tr class="border-b last:border-0">
                        <td class="px-4 py-2">{{ $count->name }}</td>
                        <td class="px-4 py-2 text-slate-500">{{ $count->branch->name }} / {{ $count->warehouse->name }}</td>
                        <td class="px-4 py-2 text-slate-500">{{ $count->start_date->format('d.m.Y') }} — {{ $count->end_date->format('d.m.Y') }}</td>
                        <td class="px-4 py-2">
                            <span class="inline-block rounded-full px-2 py-0.5 text-xs {{ $statusColors[$count->status->value] }}">
                                {{ $statusLabels[$count->status->value] }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route('stock-counts.show', $count) }}" class="text-sm text-slate-600 hover:underline">Detay</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">Henüz sayım oluşturulmamış.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $stockCounts->links() }}
    </div>

@endsection
