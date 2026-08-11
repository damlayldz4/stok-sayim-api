@extends('layouts.app')

@section('title', 'Raflar')
@section('page_title', 'Raflar — ' . $branch->name . ' / ' . $warehouse->name)

@section('content')

    <a href="{{ route('warehouses.index', $branch) }}" class="text-sm text-slate-500 hover:underline">&larr; Depolar</a>

    @if ($errors->any())
        <div class="mt-4 rounded bg-red-50 text-red-700 border border-red-200 px-4 py-3 text-sm max-w-lg">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-4 mt-4 mb-6 max-w-lg">
        <form method="POST" action="{{ route('shelves.store', [$branch, $warehouse]) }}" class="flex gap-3">
            @csrf
            <input type="text" name="shelf_code" placeholder="Raf ID (örn. A-01, 101, RAF-A-01)" required
                   class="flex-1 rounded border border-slate-300 px-3 py-2 text-sm">
            <button type="submit" class="bg-slate-900 text-white rounded px-4 py-2 text-sm font-medium hover:bg-slate-800">
                Ekle
            </button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden max-w-lg">
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 border-b bg-slate-50">
                <tr>
                    <th class="px-4 py-2 font-medium">Raf ID</th>
                    <th class="px-4 py-2 font-medium">Durum</th>
                    <th class="px-4 py-2 font-medium"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($shelves as $shelf)
                    <tr class="border-b last:border-0">
                        <td class="px-4 py-2 font-mono">{{ $shelf->shelf_code }}</td>
                        <td class="px-4 py-2">
                            @if ($shelf->is_active)
                                <span class="inline-block rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 text-xs">Aktif</span>
                            @else
                                <span class="inline-block rounded-full bg-slate-200 text-slate-600 px-2 py-0.5 text-xs">Pasif</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right whitespace-nowrap">
                            <a href="{{ route('shelves.edit', [$branch, $warehouse, $shelf]) }}" class="text-sm text-slate-600 hover:underline mr-3">Düzenle</a>
                            <form method="POST" action="{{ route('shelves.toggle', [$branch, $warehouse, $shelf]) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-sm text-slate-600 hover:underline">
                                    {{ $shelf->is_active ? 'Pasif Yap' : 'Aktif Yap' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-slate-500">Bu depoda henüz raf yok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
