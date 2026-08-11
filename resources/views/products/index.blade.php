@extends('layouts.app')

@section('title', 'Ürünler')
@section('page_title', 'Ürünler')

@section('content')

    <form method="GET" class="flex flex-wrap gap-3 mb-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Ürün adı, kod veya barkod ara"
               class="rounded border border-slate-300 px-3 py-2 text-sm w-64">

        <select name="branch_id" class="rounded border border-slate-300 px-3 py-2 text-sm">
            <option value="">Tüm Şubeler</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>

        <select name="status" class="rounded border border-slate-300 px-3 py-2 text-sm">
            <option value="">Tüm Durumlar</option>
            <option value="active" @selected(request('status') === 'active')>Aktif</option>
            <option value="passive" @selected(request('status') === 'passive')>Pasif</option>
        </select>

        <button class="bg-slate-900 text-white rounded px-4 py-2 text-sm">Filtrele</button>

        @if (request()->hasAny(['q', 'branch_id', 'status']))
            <a href="{{ route('products.index') }}" class="px-4 py-2 text-sm text-slate-500 hover:underline">Temizle</a>
        @endif
    </form>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 border-b bg-slate-50">
                <tr>
                    <th class="px-4 py-2 font-medium">Ürün Adı</th>
                    <th class="px-4 py-2 font-medium">Kod</th>
                    <th class="px-4 py-2 font-medium">Barkod</th>
                    <th class="px-4 py-2 font-medium">Şube / Depo / Raf</th>
                    <th class="px-4 py-2 font-medium text-right">Beklenen Miktar</th>
                    <th class="px-4 py-2 font-medium">Durum</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr class="border-b last:border-0">
                        <td class="px-4 py-2">{{ $product->product_name }}</td>
                        <td class="px-4 py-2 text-slate-500">{{ $product->product_code ?? '—' }}</td>
                        <td class="px-4 py-2 font-mono">{{ $product->barcode }}</td>
                        <td class="px-4 py-2 text-slate-500">
                            {{ $product->branch->name }} / {{ $product->warehouse->name }}{{ $product->shelf ? ' / '.$product->shelf->shelf_code : '' }}
                        </td>
                        <td class="px-4 py-2 text-right">{{ $product->expected_quantity }}</td>
                        <td class="px-4 py-2">
                            @if ($product->is_active)
                                <span class="inline-block rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 text-xs">Aktif</span>
                            @else
                                <span class="inline-block rounded-full bg-slate-200 text-slate-600 px-2 py-0.5 text-xs">Pasif</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">
                            Ürün bulunamadı.
                            <a href="{{ route('imports.index') }}" class="text-slate-900 underline">CSV ile ürün yükle</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>

@endsection
