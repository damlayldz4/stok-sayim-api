@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')

    @if ($view === 'system_admin')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl">
            <div class="bg-white rounded-lg shadow p-5">
                <div class="text-sm text-slate-500">Toplam Şirket</div>
                <div class="text-3xl font-semibold mt-1">{{ $stats['company_count'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-5">
                <div class="text-sm text-slate-500">Aktif Şirket</div>
                <div class="text-3xl font-semibold mt-1">{{ $stats['active_company_count'] }}</div>
            </div>
        </div>

        <p class="text-sm text-slate-500 mt-6">
            <a href="{{ route('companies.index') }}" class="underline">Şirketleri yönet</a>
        </p>

    @elseif ($view === 'company_admin')
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-5">
                <div class="text-sm text-slate-500">Şube</div>
                <div class="text-3xl font-semibold mt-1">{{ $stats['branch_count'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-5">
                <div class="text-sm text-slate-500">Depo</div>
                <div class="text-3xl font-semibold mt-1">{{ $stats['warehouse_count'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-5">
                <div class="text-sm text-slate-500">Aktif Ürün</div>
                <div class="text-3xl font-semibold mt-1">{{ $stats['product_count'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-5">
                <div class="text-sm text-slate-500">Devam Eden Sayım</div>
                <div class="text-3xl font-semibold mt-1">{{ $stats['active_stock_count'] }}</div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow mt-6">
            <div class="px-5 py-3 border-b font-medium text-sm">Son Sayımlar</div>

            @if ($stats['recent_stock_counts']->isEmpty())
                <div class="px-5 py-6 text-sm text-slate-500">Henüz sayım oluşturulmamış.</div>
            @else
                <table class="w-full text-sm">
                    <thead class="text-left text-slate-500 border-b">
                        <tr>
                            <th class="px-5 py-2 font-medium">Sayım</th>
                            <th class="px-5 py-2 font-medium">Şube / Depo</th>
                            <th class="px-5 py-2 font-medium">Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stats['recent_stock_counts'] as $count)
                            <tr class="border-b last:border-0">
                                <td class="px-5 py-2">
                                    <a href="{{ route('stock-counts.show', $count) }}" class="text-slate-900 hover:underline">{{ $count->name }}</a>
                                </td>
                                <td class="px-5 py-2 text-slate-500">{{ $count->branch->name }} / {{ $count->warehouse->name }}</td>
                                <td class="px-5 py-2">
                                    @php
                                        $label = ['draft' => 'Taslak', 'in_progress' => 'Devam Ediyor', 'completed' => 'Tamamlandı', 'cancelled' => 'İptal Edildi'][$count->status->value];
                                    @endphp
                                    {{ $label }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <p class="text-sm text-slate-500 mt-6">
            Tüm ekranlar hazır:
            <a href="{{ route('branches.index') }}" class="underline">Şubeler</a>,
            <a href="{{ route('users.index') }}" class="underline">Kullanıcılar</a>,
            <a href="{{ route('products.index') }}" class="underline">Ürünler</a>,
            <a href="{{ route('imports.index') }}" class="underline">CSV Yükleme</a> ve
            <a href="{{ route('stock-counts.index') }}" class="underline">Sayımlar</a>.
        </p>

    @else
        {{-- count_staff --}}
        <div class="bg-white rounded-lg shadow p-6 max-w-lg">
            <p class="text-slate-700">
                Hoş geldin, {{ auth()->user()->name }}. Sayım işlemlerini mobil
                uygulama üzerinden yapabilirsin — web paneli sadece şirket
                yöneticileri için tasarlandı.
            </p>
        </div>
    @endif

@endsection