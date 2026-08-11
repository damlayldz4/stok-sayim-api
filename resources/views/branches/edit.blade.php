@extends('layouts.app')

@section('title', 'Şube Düzenle')
@section('page_title', 'Şube Düzenle')

@section('content')

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 text-red-700 border border-red-200 px-4 py-3 text-sm max-w-lg">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 max-w-lg">
        <form method="POST" action="{{ route('branches.update', $branch) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-1">Şube ID</label>
                <input type="text" value="{{ $branch->branch_code }}" disabled
                       class="w-full rounded border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Şube Adı</label>
                <input type="text" name="name" value="{{ old('name', $branch->name) }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Adres</label>
                <textarea name="address" rows="2" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">{{ old('address', $branch->address) }}</textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-slate-900 text-white rounded px-4 py-2 text-sm font-medium hover:bg-slate-800">
                    Kaydet
                </button>
                <a href="{{ route('branches.index') }}" class="px-4 py-2 text-sm text-slate-600 hover:underline">Vazgeç</a>
            </div>
        </form>
    </div>

@endsection
