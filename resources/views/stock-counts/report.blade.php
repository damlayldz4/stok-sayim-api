@extends('layouts.app')

@section('title', 'Rapor')
@section('page_title', 'Rapor — ' . $stockCount->name)

@section('content')

    <a href="{{ route('stock-counts.show', $stockCount) }}" class="text-sm text-slate-500 hover:underline">&larr; Sayım Sonuçları</a>

    <div class="bg-white rounded-lg shadow p-6 mt-4 mb-6">
        <form method="GET" action="{{ route('stock-counts.report', $stockCount) }}" class="space-y-4">

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Raf</label>
                    <select name="shelf_id" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Tümü</option>
                        @foreach ($stockCount->shelves as $shelf)
                            <option value="{{ $shelf->id }}" @selected(($filters['shelf_id'] ?? null) == $shelf->id)>{{ $shelf->shelf_code }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Barkod</label>
                    <input type="text" name="barcode" value="{{ $filters['barcode'] ?? '' }}"
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Sayan Kullanıcı</label>
                    <select name="counted_by" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Tümü</option>
                        @foreach ($stockCount->assignedUsers as $person)
                            <option value="{{ $person->id }}" @selected(($filters['counted_by'] ?? null) == $person->id)>{{ $person->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Sonuç</label>
                    <div class="flex flex-wrap gap-2 text-sm pt-2">
                        @foreach (['eksik' => 'Eksik', 'fazla' => 'Fazla', 'eslesen' => 'Eşleşen', 'sayilmamis' => 'Sayılmamış'] as $key => $label)
                            <label class="flex items-center gap-1">
                                <input type="checkbox" name="result[]" value="{{ $key }}"
                                       @checked(collect($filters['result'] ?? [])->contains($key))>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 max-w-md">
                <div>
                    <label class="block text-sm font-medium mb-1">Tarih (başlangıç)</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Tarih (bitiş)</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Rapor Kolonları</label>
                <div class="flex flex-wrap gap-x-4 gap-y-2 text-sm">
                    @foreach ($availableColumns as $key => $label)
                        <label class="flex items-center gap-1">
                            <input type="checkbox" name="columns[]" value="{{ $key }}"
                                   @checked(in_array($key, $selectedColumns))>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-slate-900 text-white rounded px-4 py-2 text-sm font-medium hover:bg-slate-800">
                    Önizle
                </button>
                <a href="{{ route('stock-counts.report.export', $stockCount) }}?{{ http_build_query(request()->query()) }}"
                   class="bg-emerald-700 text-white rounded px-4 py-2 text-sm font-medium hover:bg-emerald-800">
                    Excel Olarak İndir
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 border-b bg-slate-50">
                <tr>
                    @foreach ($headings as $heading)
                        <th class="px-4 py-2 font-medium whitespace-nowrap">{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr class="border-b last:border-0">
                        @foreach ($row as $value)
                            <td class="px-4 py-2 whitespace-nowrap">{{ $value ?? '—' }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($headings) }}" class="px-4 py-6 text-center text-slate-500">
                            Bu filtrelerle eşleşen satır yok.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="text-xs text-slate-500 mt-2">{{ count($rows) }} satır — indirdiğinde bu ekrandaki filtre ve kolonlarla aynı Excel dosyası oluşur.</p>

@endsection
