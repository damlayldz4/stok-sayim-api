@extends('layouts.app')

@section('title', 'Sayım Sonuçları')
@section('page_title', $stockCount->name)

@php
    $statusLabels = ['draft' => 'Taslak', 'in_progress' => 'Devam Ediyor', 'completed' => 'Tamamlandı', 'cancelled' => 'İptal Edildi'];
    $statusColors = [
        'draft' => 'bg-slate-200 text-slate-600',
        'in_progress' => 'bg-amber-100 text-amber-700',
        'completed' => 'bg-emerald-100 text-emerald-700',
        'cancelled' => 'bg-red-100 text-red-700',
    ];
    $resultColors = [
        'Eşleşiyor' => 'bg-emerald-100 text-emerald-700',
        'Fazla' => 'bg-sky-100 text-sky-700',
        'Eksik' => 'bg-red-100 text-red-700',
        'Sayılmamış' => 'bg-slate-200 text-slate-600',
    ];
@endphp

@section('content')

    <a href="{{ route('stock-counts.index') }}" class="text-sm text-slate-500 hover:underline">&larr; Sayımlar</a>

    <div class="bg-white rounded-lg shadow p-5 my-4">
        <div class="flex justify-between items-start">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold">{{ $stockCount->name }}</h2>
                    <span class="inline-block rounded-full px-2 py-0.5 text-xs {{ $statusColors[$stockCount->status->value] }}">
                        {{ $statusLabels[$stockCount->status->value] }}
                    </span>
                </div>
                <div class="text-sm text-slate-500 mt-1">
                    {{ $stockCount->branch->name }} / {{ $stockCount->warehouse->name }}
                    @if ($stockCount->shelves->isNotEmpty())
                        — Raflar: {{ $stockCount->shelves->pluck('shelf_code')->join(', ') }}
                    @endif
                </div>
                <div class="text-sm text-slate-500">
                    {{ $stockCount->start_date->format('d.m.Y') }} — {{ $stockCount->end_date->format('d.m.Y') }}
                </div>
                <div class="text-sm text-slate-500">
                    Personel: {{ $stockCount->assignedUsers->pluck('name')->join(', ') ?: '—' }}
                </div>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('stock-counts.report', $stockCount) }}" class="px-4 py-2 text-sm border border-slate-300 rounded hover:bg-slate-50">
                    Rapor
                </a>

                @if ($stockCount->status->value === 'draft')
                    <form method="POST" action="{{ route('stock-counts.start', $stockCount) }}">
                        @csrf
                        <button type="submit" class="bg-slate-900 text-white rounded px-4 py-2 text-sm font-medium hover:bg-slate-800">
                            Sayımı Başlat
                        </button>
                    </form>
                @elseif ($stockCount->status->value === 'in_progress')
                    <form method="POST" action="{{ route('stock-counts.complete', $stockCount) }}"
                          onsubmit="return confirm('Sayımı tamamlamak istediğine emin misin? Tamamlandıktan sonra yeni kayıt girilemez.');">
                        @csrf
                        <button type="submit" class="bg-emerald-700 text-white rounded px-4 py-2 text-sm font-medium hover:bg-emerald-800">
                            Sayımı Tamamla
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 mb-4 items-center">
        <input type="text" name="barcode" value="{{ $filters['barcode'] ?? '' }}" placeholder="Barkod ara"
               class="rounded border border-slate-300 px-3 py-2 text-sm w-48">

        @foreach (['eksik' => 'Eksik', 'fazla' => 'Fazla', 'eslesen' => 'Eşleşen', 'sayilmamis' => 'Sayılmamış'] as $key => $label)
            <label class="flex items-center gap-1 text-sm">
                <input type="checkbox" name="result[]" value="{{ $key }}"
                       @checked(collect($filters['result'] ?? [])->contains($key))>
                {{ $label }}
            </label>
        @endforeach

        <button class="bg-slate-900 text-white rounded px-4 py-2 text-sm">Filtrele</button>
        @if (! empty($filters['barcode']) || ! empty($filters['result']))
            <a href="{{ route('stock-counts.show', $stockCount) }}" class="text-sm text-slate-500 hover:underline">Temizle</a>
        @endif
    </form>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 border-b bg-slate-50">
                <tr>
                    <th class="px-4 py-2 font-medium">Ürün</th>
                    <th class="px-4 py-2 font-medium">Barkod</th>
                    <th class="px-4 py-2 font-medium">Raf</th>
                    <th class="px-4 py-2 font-medium text-right">Beklenen</th>
                    <th class="px-4 py-2 font-medium text-right">Sayılan</th>
                    <th class="px-4 py-2 font-medium text-right">Fark</th>
                    <th class="px-4 py-2 font-medium">Durum</th>
                    <th class="px-4 py-2 font-medium">Sayan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr class="border-b last:border-0">
                        <td class="px-4 py-2">{{ $row['product_name'] }}</td>
                        <td class="px-4 py-2 font-mono">{{ $row['barcode'] }}</td>
                        <td class="px-4 py-2 text-slate-500">{{ $row['shelf_code'] ?? '—' }}</td>
                        <td class="px-4 py-2 text-right">{{ $row['expected_quantity'] }}</td>
                        <td class="px-4 py-2 text-right">{{ $row['counted_quantity'] ?? '—' }}</td>
                        <td class="px-4 py-2 text-right font-medium
                            @if (($row['difference'] ?? 0) < 0) text-red-600
                            @elseif (($row['difference'] ?? 0) > 0) text-emerald-600
                            @endif">
                            {{ $row['difference'] ?? '—' }}
                        </td>
                        <td class="px-4 py-2">
                            <span class="inline-block rounded-full px-2 py-0.5 text-xs {{ $resultColors[$row['status']] }}">
                                {{ $row['status'] }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-slate-500">{{ $row['counted_by_name'] ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-slate-500">Bu filtrelerle eşleşen ürün yok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
