@extends('layouts.app')

@section('title', 'Raf Düzenle')
@section('page_title', 'Raf Düzenle — ' . $branch->name . ' / ' . $warehouse->name)

@section('content')

    <a href="{{ route('shelves.index', [$branch, $warehouse]) }}" class="text-sm text-slate-500 hover:underline">&larr; Raflar</a>

    @if ($errors->any())
        <div class="mt-4 mb-4 rounded bg-red-50 text-red-700 border border-red-200 px-4 py-3 text-sm max-w-lg">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 max-w-lg mt-4">
        <form method="POST" action="{{ route('shelves.update', [$branch, $warehouse, $shelf]) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-1">Raf ID</label>
                <input type="text" name="shelf_code" value="{{ old('shelf_code', $shelf->shelf_code) }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-slate-900 text-white rounded px-4 py-2 text-sm font-medium hover:bg-slate-800">
                    Kaydet
                </button>
                <a href="{{ route('shelves.index', [$branch, $warehouse]) }}" class="px-4 py-2 text-sm text-slate-600 hover:underline">Vazgeç</a>
            </div>
        </form>
    </div>

@endsection
