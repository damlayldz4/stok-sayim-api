@extends('layouts.app')

@section('title', 'Depolar')
@section('page_title', 'Depolar — ' . $branch->name)

@section('content')

    <a href="{{ route('branches.index') }}" class="text-sm text-slate-500 hover:underline">&larr; Şubeler</a>

    <div class="flex justify-between items-center my-4">
        <div></div>
        <a href="{{ route('warehouses.create', $branch) }}" class="bg-slate-900 text-white rounded px-4 py-2 text-sm font-medium hover:bg-slate-800">
            Yeni Depo
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 border-b bg-slate-50">
                <tr>
                    <th class="px-4 py-2 font-medium">Depo ID</th>
                    <th class="px-4 py-2 font-medium">Ad</th>
                    <th class="px-4 py-2 font-medium">Açıklama</th>
                    <th class="px-4 py-2 font-medium text-right">Raf Sayısı</th>
                    <th class="px-4 py-2 font-medium">Durum</th>
                    <th class="px-4 py-2 font-medium"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($warehouses as $warehouse)
                    <tr class="border-b last:border-0">
                        <td class="px-4 py-2 font-mono">{{ $warehouse->warehouse_code }}</td>
                        <td class="px-4 py-2">{{ $warehouse->name }}</td>
                        <td class="px-4 py-2 text-slate-500">{{ $warehouse->description ?? '—' }}</td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route('shelves.index', [$branch, $warehouse]) }}" class="text-slate-900 underline">
                                {{ $warehouse->shelves_count }}
                            </a>
                        </td>
                        <td class="px-4 py-2">
                            @if ($warehouse->is_active)
                                <span class="inline-block rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 text-xs">Aktif</span>
                            @else
                                <span class="inline-block rounded-full bg-slate-200 text-slate-600 px-2 py-0.5 text-xs">Pasif</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right whitespace-nowrap">
                            <a href="{{ route('warehouses.edit', [$branch, $warehouse]) }}" class="text-sm text-slate-600 hover:underline mr-3">Düzenle</a>
                            <form method="POST" action="{{ route('warehouses.toggle', [$branch, $warehouse]) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-sm text-slate-600 hover:underline">
                                    {{ $warehouse->is_active ? 'Pasif Yap' : 'Aktif Yap' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">Bu şubede henüz depo yok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection