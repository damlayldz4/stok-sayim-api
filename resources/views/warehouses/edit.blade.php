@extends('layouts.app')

@section('title', 'Depo Düzenle')
@section('page_title', 'Depo Düzenle — ' . $branch->name)

@section('content')

    <a href="{{ route('warehouses.index', $branch) }}" class="text-sm text-slate-500 hover:underline">&larr; Depolar</a>

    @if ($errors->any())
        <div class="mt-4 mb-4 rounded bg-red-50 text-red-700 border border-red-200 px-4 py-3 text-sm max-w-lg">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 max-w-lg mt-4">
        <form method="POST" action="{{ route('warehouses.update', [$branch, $warehouse]) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-1">Depo ID</label>
                <input type="text" value="{{ $warehouse->warehouse_code }}" disabled
                       class="w-full rounded border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Depo Adı</label>
                <input type="text" name="name" value="{{ old('name', $warehouse->name) }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Açıklama</label>
                <textarea name="description" rows="2" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">{{ old('description', $warehouse->description) }}</textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-slate-900 text-white rounded px-4 py-2 text-sm font-medium hover:bg-slate-800">
                    Kaydet
                </button>
                <a href="{{ route('warehouses.index', $branch) }}" class="px-4 py-2 text-sm text-slate-600 hover:underline">Vazgeç</a>
            </div>
        </form>
    </div>

@endsection
