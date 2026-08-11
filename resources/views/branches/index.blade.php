@extends('layouts.app')

@section('title', 'Şubeler')
@section('page_title', 'Şubeler')

@section('content')

    <div class="flex justify-between items-center mb-4">
        <div></div>
        <a href="{{ route('branches.create') }}" class="bg-slate-900 text-white rounded px-4 py-2 text-sm font-medium hover:bg-slate-800">
            Yeni Şube
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 border-b bg-slate-50">
                <tr>
                    <th class="px-4 py-2 font-medium">Şube ID</th>
                    <th class="px-4 py-2 font-medium">Ad</th>
                    <th class="px-4 py-2 font-medium">Adres</th>
                    <th class="px-4 py-2 font-medium text-right">Depo Sayısı</th>
                    <th class="px-4 py-2 font-medium">Durum</th>
                    <th class="px-4 py-2 font-medium"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($branches as $branch)
                    <tr class="border-b last:border-0">
                        <td class="px-4 py-2 font-mono">{{ $branch->branch_code }}</td>
                        <td class="px-4 py-2">{{ $branch->name }}</td>
                        <td class="px-4 py-2 text-slate-500">{{ $branch->address ?? '—' }}</td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route('warehouses.index', $branch) }}" class="text-slate-900 underline">
                                {{ $branch->warehouses_count }}
                            </a>
                        </td>
                        <td class="px-4 py-2">
                            @if ($branch->is_active)
                                <span class="inline-block rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 text-xs">Aktif</span>
                            @else
                                <span class="inline-block rounded-full bg-slate-200 text-slate-600 px-2 py-0.5 text-xs">Pasif</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right whitespace-nowrap">
                            <a href="{{ route('branches.edit', $branch) }}" class="text-sm text-slate-600 hover:underline mr-3">Düzenle</a>
                            <form method="POST" action="{{ route('branches.toggle', $branch) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-sm text-slate-600 hover:underline">
                                    {{ $branch->is_active ? 'Pasif Yap' : 'Aktif Yap' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">Henüz şube oluşturulmamış.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $branches->links() }}
    </div>

@endsection
