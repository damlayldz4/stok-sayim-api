@extends('layouts.app')

@section('title', 'CSV Yükleme')
@section('page_title', 'CSV ile Ürün Aktarımı')

@section('content')

    @if (session('import_errors') && count(session('import_errors')))
        <div class="mb-4 rounded bg-amber-50 text-amber-800 border border-amber-200 px-4 py-3 text-sm">
            <div class="font-medium mb-2">Hatalı satırlar ({{ count(session('import_errors')) }})</div>
            <ul class="list-disc list-inside space-y-1 max-h-40 overflow-y-auto">
                @foreach (session('import_errors') as $error)
                    <li>Satır {{ $error['line'] }}: {{ $error['reason'] }} ({{ $error['barcode'] ?? $error['product_name'] ?? '—' }})</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 text-red-700 border border-red-200 px-4 py-3 text-sm">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 max-w-xl mb-6">
        <form method="POST" action="{{ route('imports.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1">CSV Dosyası</label>
                <input type="file" name="file" accept=".csv,text/csv" required class="text-sm">
                <p class="text-xs text-slate-500 mt-1">
                    Zorunlu kolonlar: product_name, barcode, expected_quantity, branch_code, warehouse_code
                    (opsiyonel: product_code, shelf_code)
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Mod</label>
                <select name="mode" class="rounded border border-slate-300 px-3 py-2 text-sm w-full">
                    <option value="upsert">Upsert — mevcut ürünlere dokunma, yeni/güncel olanları ekle</option>
                    <option value="replace">Replace — dosyada olmayan eski ürünleri pasif yap</option>
                </select>
            </div>

            <button type="submit" class="bg-slate-900 text-white rounded px-4 py-2 text-sm font-medium hover:bg-slate-800">
                Yükle
            </button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-5 py-3 border-b font-medium text-sm">Geçmiş İçe Aktarımlar</div>

        @if ($imports->isEmpty())
            <div class="px-5 py-6 text-sm text-slate-500">Henüz içe aktarım yapılmamış.</div>
        @else
            <table class="w-full text-sm">
                <thead class="text-left text-slate-500 border-b">
                    <tr>
                        <th class="px-5 py-2 font-medium">Dosya</th>
                        <th class="px-5 py-2 font-medium">Mod</th>
                        <th class="px-5 py-2 font-medium text-right">Toplam</th>
                        <th class="px-5 py-2 font-medium text-right">Başarılı</th>
                        <th class="px-5 py-2 font-medium text-right">Hata</th>
                        <th class="px-5 py-2 font-medium">Yükleyen</th>
                        <th class="px-5 py-2 font-medium">Tarih</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($imports as $import)
                        <tr class="border-b last:border-0">
                            <td class="px-5 py-2">{{ $import->file_name }}</td>
                            <td class="px-5 py-2">{{ $import->mode->value }}</td>
                            <td class="px-5 py-2 text-right">{{ $import->total_rows }}</td>
                            <td class="px-5 py-2 text-right text-emerald-700">{{ $import->success_rows }}</td>
                            <td class="px-5 py-2 text-right text-red-700">{{ $import->error_rows }}</td>
                            <td class="px-5 py-2 text-slate-500">{{ $import->importedBy->name }}</td>
                            <td class="px-5 py-2 text-slate-500">{{ $import->created_at->format('d.m.Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="p-4">{{ $imports->links() }}</div>
        @endif
    </div>

@endsection
